<?php

namespace App\Services;

use App\Models\CateringPackage;
use App\Models\PackageSlot;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class PackageBuilderService
{
    /**
     * @param  array<int, array{slot_id?: int, product_ids?: array<int>}>  $selections
     * @return array{valid: bool, errors: array<string, string>, selected: array<int, array{slot_id: int, slot: string, products: array}>}
     */
    public function validateSelections(CateringPackage $package, array $selections, int $persons): array
    {
        $package->loadMissing(['slots.category']);
        $errors = [];
        $selected = [];

        if ($persons < (int) $package->min_persons) {
            $errors['persons'] = 'Mindestens '.$package->min_persons.' Personen.';
        }
        if ($package->max_persons && $persons > (int) $package->max_persons) {
            $errors['persons'] = 'Maximal '.$package->max_persons.' Personen.';
        }

        $bySlot = collect($selections)->keyBy(fn ($row) => (int) ($row['slot_id'] ?? 0));

        foreach ($package->slots as $slot) {
            $ids = array_values(array_unique(array_map('intval', $bySlot[$slot->id]['product_ids'] ?? [])));
            $count = count($ids);
            $required = (int) $slot->required_count;

            if ($count !== $required) {
                $errors['slot_'.$slot->id] = $slot->name.': bitte genau '.$required.' Speisen wählen ('.$count.'/'.$required.').';
            }

            $allowed = $slot->selectableProducts()->pluck('id')->all();
            $products = Product::query()->whereIn('id', $ids)->get();

            foreach ($products as $product) {
                if ($allowed && ! in_array($product->id, $allowed, true)) {
                    $errors['slot_'.$slot->id] = $product->name.' gehört nicht zu '.$slot->name.'.';
                }
            }

            $selected[] = [
                'slot_id' => $slot->id,
                'slot' => $slot->name,
                'products' => $products->map(fn (Product $p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'image' => $p->displayImage(),
                    'is_vegan' => (bool) $p->is_vegan,
                    'is_vegetarian' => (bool) $p->is_vegetarian,
                ])->values()->all(),
            ];
        }

        return [
            'valid' => $errors === [],
            'errors' => $errors,
            'selected' => $selected,
        ];
    }

    public function assertValid(CateringPackage $package, array $selections, int $persons): array
    {
        $result = $this->validateSelections($package, $selections, $persons);
        if (! $result['valid']) {
            throw ValidationException::withMessages($result['errors']);
        }

        return $result;
    }

    public function lineTotal(CateringPackage $package, int $persons): float
    {
        return round((float) $package->price_per_person * max(1, $persons), 2);
    }

    public function slotsPayload(CateringPackage $package): Collection
    {
        $package->loadMissing(['slots.category']);

        return $package->slots->map(function (PackageSlot $slot) {
            return [
                'id' => $slot->id,
                'name' => $slot->name,
                'required' => (int) $slot->required_count,
                'products' => $slot->selectableProducts()->map(function (Product $p) {
                    $desc = trim((string) $p->short_description);
                    $descLower = mb_strtolower($desc);
                    if (
                        $desc === ''
                        || str_contains($descLower, 'freien auswahl')
                        || str_contains($descLower, 'zur freien')
                        || str_contains($descLower, 'hochwertiges catering-angebot')
                    ) {
                        $desc = '';
                    }

                    return [
                        'id' => $p->id,
                        'name' => $p->name,
                        'image' => $p->displayImage(),
                        'calories' => $p->calories ? (int) $p->calories : null,
                        'is_vegan' => (bool) $p->is_vegan,
                        'is_vegetarian' => (bool) $p->is_vegetarian,
                        'allergens' => $p->allergenCodes(),
                        'allergen_title' => collect($p->allergenBadges())->map(fn ($b) => $b['code'].' — '.$b['label'])->implode(', '),
                        'short_description' => $desc,
                    ];
                })->values()->all(),
            ];
        });
    }
}
