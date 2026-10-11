<?php

namespace App\Services;

use App\Models\CateringPackage;
use App\Models\PackageSlot;
use App\Models\Product;
use App\Models\Quote;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QuoteProposalService
{
    public function __construct(
        protected OpenAiService $openai,
        protected PricingService $pricing,
        protected ActivityLogService $activity
    ) {}

    public function generate(Quote $quote): Quote
    {
        if (function_exists('set_time_limit')) {
            @set_time_limit(120);
        }

        if (! $this->openai->isConfigured()) {
            throw new \RuntimeException('OpenAI API anahtarı yok. Ayarlar → Yapay zekâ bölümünden gpt-4o-mini anahtarını kaydedin.');
        }

        $quote->loadMissing(['eventType', 'items']);
        $persons = max(1, (int) ($quote->persons ?: 25));
        $catalog = $this->catalog();

        if ($catalog['packages'] === []) {
            throw new \RuntimeException('Aktif menü paketi bulunamadı. Önce admin’den paket ekleyin.');
        }

        $proposal = $this->askModel($quote, $persons, $catalog);
        $built = $this->buildOffer($quote, $persons, $catalog, $proposal);

        return DB::transaction(function () use ($quote, $persons, $built) {
            $quote->items()->delete();

            foreach ($built['items'] as $item) {
                $quote->items()->create($item);
            }

            $quote->update([
                'persons' => $quote->persons ?: $persons,
                'subtotal' => $built['subtotal'],
                'delivery_fee' => 0,
                'discount' => 0,
                'tax_amount' => $built['tax_amount'],
                'total' => $built['total'],
                'valid_until' => $quote->valid_until ?: now()->addDays(14)->toDateString(),
                'notes' => $built['summary'],
                'status' => 'angebot_erstellt',
            ]);

            $this->activity->log('KI-Angebot erstellt', $quote, [
                'package' => $built['package_name'],
                'total' => $built['total'],
            ]);

            return $quote->fresh(['items', 'eventType', 'customer']);
        });
    }

    /**
     * @param  array{packages: list<array<string, mixed>>}  $catalog
     * @return array<string, mixed>
     */
    protected function askModel(Quote $quote, int $persons, array $catalog): array
    {
        $budget = $quote->budget !== null ? (float) $quote->budget : null;
        $payload = [
            'request' => [
                'persons' => $persons,
                'budget_gross_eur' => $budget,
                'has_budget' => $budget !== null && $budget > 0,
                'event_type' => $quote->eventType?->name,
                'event_date' => optional($quote->event_date)->format('d.m.Y'),
                'event_time' => $quote->event_time,
                'location' => $quote->location,
                'company' => $quote->company,
                'message' => $quote->message,
                'catering_wunsch' => $quote->catering_wunsch,
            ],
            'catalog' => $catalog,
        ];

        $system = <<<'TXT'
Du bist der Angebotsplaner von Monnemtisch Catering Mannheim.
Antworte NUR mit JSON.
Regeln:
- Nutze ausschließlich IDs aus dem Katalog. Keine neuen Preise erfinden.
- Wähle genau EIN Menüpaket. Nur Paket + Gerichte, niemals Extras.
- Keine Getränke, kein Geschirr, kein Besteck, kein Personal, keine Service-Positionen.
- Paketpreis ist pro Person und enthält die gewählten Speisen. Speisen kosten extra nichts.
- Jeder Slot braucht genau required_count Gerichte aus seiner dishes-Liste.
- Katalogpreise sind Netto. Gesamtsumme = Netto + 7% MwSt.
- Wenn budget_gross_eur gesetzt ist: Gesamtsumme brutto möglichst an oder leicht unter dem Budget (nur über die Paketwahl).
- Wenn kein Budget: passendes Paket zur Personenanzahl (mittleres Paket).
- Diätwünsche aus message/catering_wunsch beachten (vegan, vegetarisch, glutenfrei).
JSON-Form:
{"package_id":1,"dishes":[{"slot_id":1,"product_ids":[1,2]}],"summary_de":"Kurzer Angebotstext auf Deutsch für den Kunden."}
TXT;

        try {
            return $this->openai->chatJson([
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => json_encode($payload, JSON_UNESCAPED_UNICODE)],
            ], 4000, 0.35);
        } catch (\Throwable $e) {
            Log::warning('Quote AI failed', ['quote_id' => $quote->id, 'error' => $e->getMessage()]);

            throw new \RuntimeException('Yapay zekâ teklif üretemedi: '.$e->getMessage());
        }
    }

    /**
     * @return array{packages: list<array<string, mixed>>}
     */
    protected function catalog(): array
    {
        $packages = CateringPackage::query()
            ->active()
            ->where('menu_group', '!=', 'extras')
            ->with(['slots.items.product'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $categoryIds = $packages->flatMap->slots->pluck('product_category_id')->filter()->unique()->values();
        $productsByCategory = $categoryIds->isEmpty()
            ? collect()
            : Product::query()->active()->whereIn('category_id', $categoryIds)->orderBy('sort_order')->orderBy('name')->get()->groupBy('category_id');

        $packageRows = [];
        foreach ($packages as $package) {
            $slots = [];
            foreach ($package->slots as $slot) {
                $dishes = $this->slotDishPool($slot, $productsByCategory)
                    ->take(24)
                    ->map(fn (Product $p) => [
                        'id' => $p->id,
                        'n' => $p->name,
                        'vegan' => (bool) $p->is_vegan,
                        'veg' => (bool) $p->is_vegetarian,
                    ])
                    ->values()
                    ->all();

                $slots[] = [
                    'id' => $slot->id,
                    'name' => $slot->name,
                    'required_count' => (int) $slot->required_count,
                    'dishes' => $dishes,
                ];
            }

            $packageRows[] = [
                'id' => $package->id,
                'name' => $package->name,
                'menu' => $package->menu_group,
                'ppp' => (float) $package->price_per_person,
                'min' => (int) ($package->min_persons ?: 1),
                'max' => $package->max_persons ? (int) $package->max_persons : null,
                'slots' => $slots,
            ];
        }

        return ['packages' => $packageRows];
    }

    /**
     * @param  Collection<int|string, Collection<int, Product>>  $productsByCategory
     * @return Collection<int, Product>
     */
    protected function slotDishPool(PackageSlot $slot, Collection $productsByCategory): Collection
    {
        $assigned = $slot->items->pluck('product')->filter()->where('is_active', true)->values();
        if ($assigned->isNotEmpty()) {
            return $assigned;
        }

        if (! $slot->product_category_id) {
            return collect();
        }

        return $productsByCategory->get($slot->product_category_id, collect())->values();
    }

    /**
     * @param  array{packages: list<array<string, mixed>>}  $catalog
     * @param  array<string, mixed>  $proposal
     * @return array{items: list<array<string, mixed>>, subtotal: float, tax_amount: float, total: float, summary: string, package_name: string}
     */
    protected function buildOffer(Quote $quote, int $persons, array $catalog, array $proposal): array
    {
        $packages = CateringPackage::query()
            ->with(['slots.items.product'])
            ->whereIn('id', collect($catalog['packages'])->pluck('id'))
            ->get()
            ->keyBy('id');

        $packageId = (int) ($proposal['package_id'] ?? 0);
        $package = $packages->get($packageId) ?: $this->pickPackage($packages, $persons, $quote->budget);

        if (! $package) {
            throw new \RuntimeException('Uygun menü paketi seçilemedi.');
        }

        $categoryIds = $package->slots->pluck('product_category_id')->filter()->unique()->values();
        $productsByCategory = $categoryIds->isEmpty()
            ? collect()
            : Product::query()->active()->whereIn('category_id', $categoryIds)->orderBy('sort_order')->get()->groupBy('category_id');

        $dishChoices = collect($proposal['dishes'] ?? [])->keyBy(fn ($row) => (int) ($row['slot_id'] ?? 0));
        $included = [];
        foreach ($package->slots as $slot) {
            $pool = $this->slotDishPool($slot, $productsByCategory);
            $want = array_map('intval', $dishChoices->get($slot->id)['product_ids'] ?? []);
            $picked = $this->fillSlot($pool, $want, (int) $slot->required_count);
            if ($picked->isNotEmpty()) {
                $included[] = $slot->name.': '.$picked->pluck('name')->implode(', ');
            }
        }

        $ppp = (float) $package->price_per_person;
        $packageNet = round($ppp * $persons, 2);
        $menuLabel = CateringPackage::menuMetaFor($package->menu_group)['label'] ?? $package->menu_group;
        $packageName = trim($menuLabel.' · '.$package->name);

        $items = [[
            'product_id' => null,
            'name' => $packageName.( $included ? "\n".implode("\n", $included) : ''),
            'sku' => 'package:'.$package->id,
            'quantity' => $persons,
            'unit_price' => $ppp,
            'persons' => $persons,
            'line_total' => $packageNet,
            'options' => [
                'kind' => 'package',
                'package_id' => $package->id,
                'tax_rate' => shop_tax_rate(),
            ],
        ]];

        $subtotal = round($packageNet, 2);
        $tax = round($this->pricing->calculateTax($subtotal, shop_tax_rate()), 2);
        $total = round($subtotal + $tax, 2);

        $summary = trim((string) ($proposal['summary_de'] ?? ''));
        if ($summary === '') {
            $summary = sprintf(
                'Angebot für %d Personen: %s. Gesamt %s inkl. MwSt.',
                $persons,
                $packageName,
                number_format($total, 2, ',', '.').' €'
            );
        }

        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'tax_amount' => $tax,
            'total' => $total,
            'summary' => $summary,
            'package_name' => $packageName,
        ];
    }

    /**
     * @param  Collection<int, CateringPackage>  $packages
     */
    protected function pickPackage(Collection $packages, int $persons, mixed $budget): ?CateringPackage
    {
        $fit = $packages->filter(function (CateringPackage $p) use ($persons) {
            $min = (int) ($p->min_persons ?: 1);
            $max = $p->max_persons ? (int) $p->max_persons : 9999;

            return $persons >= $min && $persons <= $max;
        });
        if ($fit->isEmpty()) {
            $fit = $packages;
        }

        $budget = $budget !== null && $budget !== '' ? (float) $budget : 0;
        if ($budget > 0) {
            $targetNet = $budget / (1 + shop_tax_rate() / 100);
            $chosen = $fit
                ->sortBy(fn (CateringPackage $p) => abs(((float) $p->price_per_person * $persons) - $targetNet))
                ->first();

            return $chosen;
        }

        return $fit->firstWhere('is_popular', true)
            ?: $fit->firstWhere('is_featured', true)
            ?: $fit->sortBy('price_per_person')->values()->get((int) floor($fit->count() / 2))
            ?: $fit->first();
    }

    /**
     * @param  Collection<int, Product>  $pool
     * @param  list<int>  $want
     * @return Collection<int, Product>
     */
    protected function fillSlot(Collection $pool, array $want, int $need): Collection
    {
        $need = max(0, $need);
        $byId = $pool->keyBy('id');
        $picked = collect();
        foreach ($want as $id) {
            if ($picked->count() >= $need) {
                break;
            }
            $product = $byId->get($id);
            if ($product && ! $picked->contains('id', $product->id)) {
                $picked->push($product);
            }
        }
        foreach ($pool as $product) {
            if ($picked->count() >= $need) {
                break;
            }
            if (! $picked->contains('id', $product->id)) {
                $picked->push($product);
            }
        }

        return $picked->take($need)->values();
    }
}
