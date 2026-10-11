<?php

namespace App\Services;

use App\Jobs\AnalyzeOrderIngredientsJob;
use App\Models\Order;
use App\Models\OrderIngredientAnalysis;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class IngredientAnalysisService
{
    public function __construct(
        protected OpenAiService $openai,
        protected SettingService $settings
    ) {}

    public function queue(Order $order, bool $force = false): OrderIngredientAnalysis
    {
        $analysis = OrderIngredientAnalysis::query()->firstOrCreate(
            ['order_id' => $order->id],
            ['status' => 'pending']
        );

        if (! $force && in_array($analysis->status, ['completed', 'failed'], true)) {
            return $analysis;
        }

        if (! $force && $analysis->status === 'processing' && $analysis->updated_at?->gt(now()->subMinutes(3))) {
            return $analysis;
        }

        if ($force) {
            $analysis->update([
                'status' => 'pending',
                'error_message' => null,
            ]);
        }

        AnalyzeOrderIngredientsJob::dispatch($order->id)->afterResponse();

        return $analysis->fresh();
    }

    public function analyze(int $orderId): OrderIngredientAnalysis
    {
        ignore_user_abort(true);
        if (function_exists('set_time_limit')) {
            @set_time_limit(120);
        }

        $order = Order::query()->with(['items', 'extras'])->findOrFail($orderId);

        $analysis = OrderIngredientAnalysis::query()->firstOrCreate(
            ['order_id' => $order->id],
            ['status' => 'pending']
        );

        $analysis->update([
            'status' => 'processing',
            'error_message' => null,
        ]);

        if (! $this->openai->isEnabled()) {
            $analysis->update([
                'status' => 'failed',
                'error_message' => 'Yapay zekâ kapalı veya API anahtarı yok. Ayarlar → Yapay zekâ.',
            ]);

            return $analysis->fresh();
        }

        $dishes = $this->collectDishes($order);
        if ($dishes === []) {
            $analysis->update([
                'status' => 'completed',
                'items' => [],
                'totals' => [],
                'warnings' => ['Siparişte analiz edilecek yemek bulunamadı.'],
                'analyzed_at' => now(),
            ]);

            return $analysis->fresh();
        }

        try {
            $parsed = $this->openai->chatJson([
                ['role' => 'system', 'content' => $this->systemPrompt()],
                ['role' => 'user', 'content' => json_encode([
                    'order_number' => $order->order_number,
                    'persons' => (int) $order->persons,
                    'dishes' => $dishes,
                ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)],
            ]);

            $normalized = $this->normalizeResult($parsed, $dishes);

            $analysis->update([
                'status' => 'completed',
                'items' => $normalized['items'],
                'totals' => $normalized['totals'],
                'warnings' => $normalized['warnings'],
                'raw_response' => json_encode($parsed, JSON_UNESCAPED_UNICODE),
                'error_message' => null,
                'analyzed_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Ingredient analysis failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            $analysis->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }

        return $analysis->fresh();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function collectDishes(Order $order): array
    {
        $dishes = [];
        $persons = max(1, (int) $order->persons);

        foreach ($order->items as $item) {
            $kind = $item->options['kind'] ?? $item->sku;
            if (in_array($kind, ['delivery', 'tip', 'DELIVERY', 'TIP'], true)) {
                continue;
            }

            $portions = max(1, (int) ($item->persons ?: $persons)) * max(1, (int) $item->quantity);
            $selections = $item->options['selections'] ?? [];

            if (! is_array($selections) || $selections === []) {
                $dishes[] = $this->dishPayload((string) $item->name, $portions, $item->name, null);

                continue;
            }

            foreach ($selections as $slot) {
                $slotName = (string) ($slot['slot'] ?? $slot['name'] ?? '');
                foreach ($slot['products'] ?? [] as $row) {
                    $name = trim((string) ($row['name'] ?? ''));
                    if ($name === '') {
                        continue;
                    }
                    $dishes[] = $this->dishPayload($name, $portions, $item->name, $slotName);
                }
            }
        }

        foreach ($order->extras as $extra) {
            $qty = max(1, (int) $extra->quantity);
            $dishes[] = $this->dishPayload((string) $extra->name, $qty, 'Extra', null);
        }

        return $dishes;
    }

    /**
     * @return array<string, mixed>
     */
    protected function dishPayload(string $name, int $portions, ?string $package, ?string $slot): array
    {
        return [
            'name' => $name,
            'portions' => $portions,
            'package' => $package,
            'category' => $slot,
        ];
    }

    protected function systemPrompt(): string
    {
        return <<<'PROMPT'
You are a professional catering kitchen planner for Monnemtisch Catering in Mannheim.
Analyze every dish by name and return JSON with realistic ingredient quantities for the given portion counts (Personen). Do not ask for recipes.

Rules:
- Estimate a standard German catering preparation from the dish name.
- Include ALL ingredients needed to cook the dish: salt, pepper, spices, oil, butter, sauces, stock, garnishes, herbs. Never omit small amounts.
- Quantities only in g, kg, ml, l, or Stück.
- Prefer g and ml. Use kg or l only when the amount is >= 1000 of the smaller unit.
- Scale exactly to the portion count of each dish.
- Normalize synonyms to one canonical German name, for example:
  Speiseöl (Sonnenblumenöl, Pflanzenöl, Öl, Sıvı yağ, Ayçiçek yağı)
  Salz (Tuz, salt)
  Schwarzer Pfeffer (Pfeffer, Karabiber, black pepper)
  Butter (Tereyağı)
  Zwiebel (Soğan, Zwiebeln)
  Möhre (Havuç, Karotte, Karotten)
  Kartoffel (Patates, Kartoffeln)
  Reis (Pirinç)
  Hähnchen (Tavuk, Huhn, Chicken)
- totals must SUM the same canonical ingredient across all dishes after converting units.
- Keep dish names exactly as provided.
- Ingredient display names in German.
- amount must be a number, not a string.

Return JSON only:
{
  "items": [
    {
      "name": "Hähnchen Curry",
      "quantity_label": "10 Personen",
      "ingredients": [
        {"name": "Hähnchenbrust", "canonical": "hähnchenbrust", "amount": 1500, "unit": "g"}
      ]
    }
  ],
  "totals": [
    {"name": "Hähnchenbrust", "canonical": "hähnchenbrust", "amount": 1500, "unit": "g"}
  ]
}
PROMPT;
    }

    /**
     * @param  array<string, mixed>  $parsed
     * @param  list<array<string, mixed>>  $dishes
     * @return array{items: list<array<string, mixed>>, totals: list<array<string, mixed>>, warnings: list<string>}
     */
    protected function normalizeResult(array $parsed, array $dishes): array
    {
        $byName = [];
        foreach ($parsed['items'] ?? [] as $item) {
            if (! is_array($item)) {
                continue;
            }
            $byName[Str::lower(trim((string) ($item['name'] ?? '')))] = $item;
        }

        $items = [];
        $merged = [];
        $warnings = [];

        foreach ($dishes as $dish) {
            $name = (string) $dish['name'];
            $ai = $byName[Str::lower($name)] ?? null;
            $ingredients = [];

            foreach ($ai['ingredients'] ?? [] as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $normalized = $this->normalizeIngredient($row);
                if ($normalized === null) {
                    continue;
                }
                $ingredients[] = $normalized;
                $this->addToTotals($merged, $normalized);
            }

            $items[] = [
                'name' => $name,
                'quantity_label' => (string) ($ai['quantity_label'] ?? ($dish['portions'].' Personen')),
                'portions' => (int) $dish['portions'],
                'ingredients' => $ingredients,
            ];
        }

        $totals = [];
        foreach ($merged as $row) {
            $row['display'] = $this->formatQuantity($row['amount'], $row['unit']);
            $totals[] = $row;
        }

        usort($totals, fn ($a, $b) => strcasecmp((string) $a['name'], (string) $b['name']));

        return [
            'items' => $items,
            'totals' => $totals,
            'warnings' => $warnings,
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array{name: string, canonical: string, amount: float, unit: string, display: string}|null
     */
    protected function normalizeIngredient(array $row): ?array
    {
        $name = trim((string) ($row['name'] ?? ''));
        if ($name === '') {
            return null;
        }

        $amount = (float) str_replace(',', '.', (string) ($row['amount'] ?? 0));
        if ($amount <= 0) {
            return null;
        }

        [$amount, $unit] = $this->toBase($amount, (string) ($row['unit'] ?? 'g'));
        $canonical = $this->canonicalKey((string) ($row['canonical'] ?? $name));

        return [
            'name' => $name,
            'canonical' => $canonical,
            'amount' => $amount,
            'unit' => $unit,
            'display' => $this->formatQuantity($amount, $unit),
        ];
    }

    /**
     * @param  array<string, array{name: string, canonical: string, amount: float, unit: string}>  $merged
     * @param  array{name: string, canonical: string, amount: float, unit: string, display: string}  $ingredient
     */
    protected function addToTotals(array &$merged, array $ingredient): void
    {
        $key = $ingredient['canonical'].'|'.$ingredient['unit'];
        if (! isset($merged[$key])) {
            $merged[$key] = [
                'name' => $ingredient['name'],
                'canonical' => $ingredient['canonical'],
                'amount' => 0.0,
                'unit' => $ingredient['unit'],
            ];
        }

        $merged[$key]['amount'] += $ingredient['amount'];
    }

    /**
     * @return array{0: float, 1: string}
     */
    protected function toBase(float $amount, string $unit): array
    {
        $unit = Str::lower(trim(str_replace(['ä', 'ö', 'ü', 'ß'], ['ae', 'oe', 'ue', 'ss'], $unit)));
        $unit = rtrim($unit, '.');

        return match ($unit) {
            'kg', 'kilogram', 'kilogramm' => [$amount * 1000, 'g'],
            'g', 'gr', 'gram', 'gramm' => [$amount, 'g'],
            'l', 'lt', 'liter', 'litre' => [$amount * 1000, 'ml'],
            'ml', 'milliliter', 'millilitre' => [$amount, 'ml'],
            'el', 'essloeffel', 'essloffel', 'tbsp' => [$amount * 15, 'ml'],
            'tl', 'teeloeffel', 'teeloffel', 'tsp' => [$amount * 5, 'ml'],
            'stueck', 'stuck', 'stk', 'pcs', 'stück' => [$amount, 'stk'],
            default => [$amount, $unit !== '' ? $unit : 'g'],
        };
    }

    public function formatQuantity(float $amount, string $unit): string
    {
        if ($unit === 'g') {
            if ($amount >= 1000) {
                return $this->number($amount / 1000).' kg';
            }

            return $this->number($amount).' g';
        }

        if ($unit === 'ml') {
            if ($amount >= 1000) {
                return $this->number($amount / 1000).' l';
            }

            return $this->number($amount).' ml';
        }

        if ($unit === 'stk') {
            return $this->number($amount).' Stück';
        }

        return $this->number($amount).' '.$unit;
    }

    protected function number(float $amount): string
    {
        if (abs($amount - round($amount)) < 0.05) {
            return number_format((int) round($amount), 0, ',', '.');
        }

        return rtrim(rtrim(number_format($amount, 2, ',', '.'), '0'), ',');
    }

    protected function canonicalKey(string $name): string
    {
        $key = Str::lower(trim($name));
        $key = str_replace(['ä', 'ö', 'ü', 'ß', 'ı', 'ş', 'ç', 'ğ'], ['ae', 'oe', 'ue', 'ss', 'i', 's', 'c', 'g'], $key);
        $key = (string) preg_replace('/[^a-z0-9]+/i', ' ', $key);
        $key = trim((string) preg_replace('/\s+/', ' ', $key));

        $aliases = [
            'sivi yag' => 'speiseol',
            'sivı yag' => 'speiseol',
            'aycicek yagi' => 'speiseol',
            'aycicek yag' => 'speiseol',
            'sonnenblumenol' => 'speiseol',
            'pflanzenol' => 'speiseol',
            'speiseol' => 'speiseol',
            'ol' => 'speiseol',
            'oil' => 'speiseol',
            'tuz' => 'salz',
            'salz' => 'salz',
            'salt' => 'salz',
            'karabiber' => 'schwarzer pfeffer',
            'pfeffer' => 'schwarzer pfeffer',
            'schwarzer pfeffer' => 'schwarzer pfeffer',
            'black pepper' => 'schwarzer pfeffer',
            'tereyagi' => 'butter',
            'butter' => 'butter',
            'sogan' => 'zwiebel',
            'zwiebeln' => 'zwiebel',
            'zwiebel' => 'zwiebel',
            'havuc' => 'moehre',
            'karotte' => 'moehre',
            'karotten' => 'moehre',
            'moehre' => 'moehre',
            'moehrren' => 'moehre',
            'patates' => 'kartoffel',
            'kartoffeln' => 'kartoffel',
            'kartoffel' => 'kartoffel',
            'pirinc' => 'reis',
            'reis' => 'reis',
            'tavuk' => 'haehnchen',
            'haehnchen' => 'haehnchen',
            'huhn' => 'haehnchen',
            'chicken' => 'haehnchen',
            'kirmizi mercimek' => 'rote linsen',
            'rote linsen' => 'rote linsen',
        ];

        return $aliases[$key] ?? $key;
    }
}
