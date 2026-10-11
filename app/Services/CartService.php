<?php

namespace App\Services;

use App\Models\CateringPackage;
use App\Models\Coupon;
use App\Models\Extra;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Support\Facades\Session;

class CartService
{
    public const SESSION_KEY = 'cart';

    protected ?array $cachedCart = null;

    public function __construct(
        protected PricingService $pricing,
        protected CouponService $coupons,
        protected DeliveryService $delivery
    ) {}

    public function getCart(): array
    {
        if ($this->cachedCart !== null) {
            return $this->cachedCart;
        }

        $cart = Session::get(self::SESSION_KEY, $this->emptyCart());
        $cart = array_merge($this->emptyCart(), is_array($cart) ? $cart : []);
        $hydrated = $this->hydrateImages($cart);

        if ($hydrated !== $cart) {
            Session::put(self::SESSION_KEY, $hydrated);
        }

        return $this->cachedCart = $hydrated;
    }

    public function addPackage(\App\Models\CateringPackage $package, int $persons, array $selected): void
    {
        $persons = max((int) $package->min_persons, $persons);
        $unit = (float) $package->price_per_person;
        $line = round($unit * $persons, 2);
        $key = 'pkg-'.$package->id.'-'.md5(json_encode($selected));

        $cart = $this->getCart();
        $cart['items'][$key] = [
            'key' => $key,
            'type' => 'package',
            'package_id' => $package->id,
            'product_id' => null,
            'name' => $package->name,
            'slug' => $package->slug,
            'image' => $package->imageUrl(),
            'price_type' => 'person',
            'quantity' => 1,
            'persons' => $persons,
            'unit_price' => $unit,
            'line_total' => $line,
            'options' => [
                'package_id' => $package->id,
                'package_image' => $package->imageUrl(),
                'selections' => $selected,
            ],
        ];
        $cart['event']['persons'] = max((int) ($cart['event']['persons'] ?? 1), $persons);
        $this->save($cart);
    }

    public function addProduct(int $productId, int $quantity, int $persons = 1, array $options = []): void
    {
        $product = Product::query()->with('priceTiers')->findOrFail($productId);
        $quantity = max(1, $quantity);
        $persons = max(1, $persons);

        $unitPrice = method_exists($product, 'getPriceForPersons')
            ? (float) $product->getPriceForPersons($persons)
            : (float) $product->price;

        $lineTotal = $this->pricing->calculateProductPrice($product, $persons, $quantity);
        $optionModifier = 0.0;
        foreach ($options as $option) {
            if (is_array($option) && isset($option['price_modifier'])) {
                $optionModifier += (float) $option['price_modifier'];
            }
        }
        $lineTotal = round($lineTotal + ($optionModifier * $quantity), 2);

        $key = $this->itemKey($productId, $persons, $options);
        $cart = $this->getCart();

        if (isset($cart['items'][$key])) {
            $cart['items'][$key]['quantity'] += $quantity;
            $cart['items'][$key]['line_total'] = $this->recalculateItemLine($cart['items'][$key], $product);
        } else {
            $cart['items'][$key] = [
                'key' => $key,
                'product_id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug ?? null,
                'image' => $product->displayImage(),
                'price_type' => $product->price_type ?? 'person',
                'quantity' => $quantity,
                'persons' => $persons,
                'unit_price' => $unitPrice,
                'options' => $options,
                'line_total' => $lineTotal,
            ];
        }

        $this->save($cart);
    }

    public function updateItem(string $key, int $quantity, ?int $persons = null): void
    {
        $cart = $this->getCart();

        if (! isset($cart['items'][$key])) {
            return;
        }

        $quantity = max(1, $quantity);
        $item = $cart['items'][$key];
        $item['quantity'] = $quantity;

        if ($persons !== null) {
            $item['persons'] = max(1, $persons);
        }

        if (($item['type'] ?? '') === 'package') {
            $item['line_total'] = round((float) $item['unit_price'] * (int) $item['persons'] * (int) $item['quantity'], 2);
            $cart['items'][$key] = $item;
            $this->save($cart);

            return;
        }

        $product = Product::query()->with('priceTiers')->find($item['product_id']);
        if ($product) {
            $item['unit_price'] = method_exists($product, 'getPriceForPersons')
                ? (float) $product->getPriceForPersons((int) $item['persons'])
                : (float) $product->price;
            $item['line_total'] = $this->recalculateItemLine($item, $product);

            // Re-key if persons changed
            $newKey = $this->itemKey((int) $item['product_id'], (int) $item['persons'], $item['options'] ?? []);
            unset($cart['items'][$key]);
            $item['key'] = $newKey;
            $cart['items'][$newKey] = $item;
        } else {
            $cart['items'][$key] = $item;
        }

        $this->save($cart);
    }

    public function removeItem(string $key): void
    {
        $cart = $this->getCart();
        unset($cart['items'][$key]);
        $this->save($cart);
    }

    public function replaceExtras(array $extraIds, int $persons): void
    {
        $persons = max(1, $persons);
        $cart = $this->getCart();
        $cart['event']['persons'] = $persons;
        $cart['extras'] = [];
        $this->save($cart);

        $extras = Extra::query()
            ->active()
            ->whereIn('id', $extraIds)
            ->get();

        foreach ($extras as $extra) {
            $quantity = $extra->price_type === 'stück' ? $persons : 1;
            $this->setExtraQuantity((int) $extra->id, $quantity);
        }
    }

    public function addExtra(int $extraId, int $quantity): void
    {
        $this->setExtraQuantity(
            $extraId,
            $this->currentExtraQuantity($extraId) + max(1, $quantity)
        );
    }

    public function setExtraQuantity(int $extraId, int $quantity): void
    {
        if ($quantity < 1) {
            $this->removeExtra($extraId);

            return;
        }

        $extra = Extra::query()->findOrFail($extraId);
        $cart = $this->getCart();
        $persons = max(1, (int) ($cart['event']['persons'] ?? 1));
        $id = (string) $extraId;

        $cart['extras'][$id] = [
            'extra_id' => $extra->id,
            'name' => $extra->name,
            'kind' => $extra->kind ?? 'service',
            'price_type' => $extra->price_type ?? 'stück',
            'unit_price' => (float) $extra->price,
            'quantity' => $quantity,
            'image' => $extra->displayImage(),
            'line_total' => $this->pricing->calculateExtraPrice($extra, $quantity, $persons),
        ];

        $this->save($cart);
    }

    protected function currentExtraQuantity(int $extraId): int
    {
        $cart = $this->getCart();

        return (int) ($cart['extras'][(string) $extraId]['quantity'] ?? 0);
    }

    public function removeExtra(int $extraId): void
    {
        $cart = $this->getCart();
        unset($cart['extras'][(string) $extraId]);
        $this->save($cart);
    }

    public function setEvent(array $data): void
    {
        $cart = $this->getCart();
        $cart['event'] = array_merge($cart['event'] ?? [], [
            'event_type_id' => $data['event_type_id'] ?? ($cart['event']['event_type_id'] ?? null),
            'date' => $data['date'] ?? ($cart['event']['date'] ?? null),
            'time' => $data['time'] ?? ($cart['event']['time'] ?? null),
            'persons' => isset($data['persons']) ? max(1, (int) $data['persons']) : ($cart['event']['persons'] ?? 1),
        ]);

        $this->save($cart);
        $this->recalculateAllLines();
    }

    public function setDelivery(array $data): void
    {
        $cart = $this->getCart();
        $cart['delivery'] = array_merge($cart['delivery'] ?? [], $data);
        $this->save($cart);
    }

    public function setTip(float $amount, float $percent = 0): void
    {
        $cart = $this->getCart();
        $cart['tip'] = [
            'amount' => round(max(0, $amount), 2),
            'percent' => max(0, $percent),
        ];
        $this->save($cart);
    }

    public function applyCoupon(string $code): bool
    {
        $cart = $this->getCart();
        $totalsPreview = $this->subtotalOnly($cart);
        $result = $this->coupons->validate($code, $totalsPreview);

        if (! $result['valid'] || ! $result['coupon']) {
            return false;
        }

        /** @var Coupon $coupon */
        $coupon = $result['coupon'];
        $cart['coupon'] = [
            'code' => $coupon->code,
            'type' => $coupon->type,
            'value' => (float) $coupon->value,
            'discount' => $result['discount'],
        ];
        $this->save($cart);

        return true;
    }

    public function appliedCouponCode(): ?string
    {
        $cart = $this->getCart();
        $code = is_array($cart['coupon'] ?? null) ? trim((string) ($cart['coupon']['code'] ?? '')) : '';
        if ($code === '') {
            return null;
        }

        $result = $this->coupons->validate($code, $this->subtotalOnly($cart));
        if (! $result['valid']) {
            $this->removeCoupon();

            return null;
        }

        return $code;
    }

    public function removeCoupon(): void
    {
        $cart = $this->getCart();
        $cart['coupon'] = null;
        $this->save($cart);
    }

    public function clear(): void
    {
        Session::forget(self::SESSION_KEY);
        $this->cachedCart = null;
    }

    /**
     * @return array{
     *     subtotal: float,
     *     delivery_fee: float,
     *     service_fee: float,
     *     tip_amount: float,
     *     discount: float,
     *     tax_rate: float,
     *     tax_amount: float,
     *     total: float,
     *     item_count: int
     * }
     */
    public function totals(): array
    {
        $cart = $this->getCart();
        $items = array_values($cart['items'] ?? []);
        $extras = array_values($cart['extras'] ?? []);
        $postal = $cart['delivery']['postal_code'] ?? ($cart['delivery']['plz'] ?? null);
        $couponCode = is_array($cart['coupon'] ?? null) ? ($cart['coupon']['code'] ?? null) : null;
        $deliveryOverride = array_key_exists('cost', $cart['delivery'] ?? [])
            ? (float) $cart['delivery']['cost']
            : 0.0;
        $tipAmount = (float) ($cart['tip']['amount'] ?? 0);

        $persons = (int) ($cart['event']['persons'] ?? 1);
        $preparedExtras = array_map(function (array $row) use ($persons) {
            $row['persons'] = $persons;

            return $row;
        }, $extras);

        return $this->pricing->calculateOrderTotals($items, $preparedExtras, $postal, $couponCode, $deliveryOverride, $tipAmount);
    }

    protected function emptyCart(): array
    {
        return [
            'items' => [],
            'extras' => [],
            'event' => [
                'event_type_id' => null,
                'date' => null,
                'time' => null,
                'persons' => 1,
            ],
            'delivery' => [],
            'tip' => ['amount' => 0, 'percent' => 0],
            'coupon' => null,
        ];
    }

    protected function save(array $cart): void
    {
        Session::put(self::SESSION_KEY, $cart);
        $this->cachedCart = null;
    }

    protected function hydrateImages(array $cart): array
    {
        $packageIds = [];
        $productIds = [];
        $extraIds = [];

        foreach ($cart['items'] ?? [] as $item) {
            if (($item['type'] ?? '') === 'package') {
                $packageId = (int) ($item['package_id'] ?? $item['options']['package_id'] ?? 0);
                if ($packageId > 0) {
                    $packageIds[] = $packageId;
                }
                foreach ($item['options']['selections'] ?? [] as $sel) {
                    foreach ($sel['products'] ?? [] as $product) {
                        if (! empty($product['id'])) {
                            $productIds[] = (int) $product['id'];
                        }
                    }
                }
            } elseif (! empty($item['product_id'])) {
                $productIds[] = (int) $item['product_id'];
            }
        }

        foreach ($cart['extras'] ?? [] as $row) {
            $extraId = (int) ($row['extra_id'] ?? 0);
            if ($extraId > 0) {
                $extraIds[] = $extraId;
            }
        }

        $packages = $packageIds === []
            ? collect()
            : CateringPackage::query()->whereIn('id', array_unique($packageIds))->get()->keyBy('id');
        $products = $productIds === []
            ? collect()
            : Product::query()->with('category')->whereIn('id', array_unique($productIds))->get()->keyBy('id');
        $extras = $extraIds === []
            ? collect()
            : Extra::query()->whereIn('id', array_unique($extraIds))->get()->keyBy('id');

        foreach ($cart['items'] as $key => $item) {
            if (($item['type'] ?? '') === 'package') {
                $package = $packages->get((int) ($item['package_id'] ?? $item['options']['package_id'] ?? 0));
                $item['image'] = $package
                    ? $package->imageUrl()
                    : velora_media_url($item['image'] ?? null, $item['name'] ?? 'buffet', (int) ($item['package_id'] ?? 0));
                $item['options']['package_image'] = $item['image'];

                foreach ($item['options']['selections'] ?? [] as $slotIndex => $sel) {
                    foreach ($sel['products'] ?? [] as $productIndex => $row) {
                        $product = $products->get((int) ($row['id'] ?? 0));
                        $item['options']['selections'][$slotIndex]['products'][$productIndex]['image'] = $product
                            ? $product->displayImage()
                            : velora_media_url($row['image'] ?? null, $row['name'] ?? '', (int) ($row['id'] ?? 0));
                    }
                }
            } else {
                $product = $products->get((int) ($item['product_id'] ?? 0));
                $item['image'] = $product
                    ? $product->displayImage()
                    : velora_media_url($item['image'] ?? null, $item['name'] ?? '', (int) ($item['product_id'] ?? 0));
            }

            $cart['items'][$key] = $item;
        }

        foreach ($cart['extras'] ?? [] as $id => $row) {
            $extra = $extras->get((int) ($row['extra_id'] ?? $id));
            $cart['extras'][$id]['image'] = $extra
                ? $extra->displayImage()
                : velora_media_url($row['image'] ?? null, $row['name'] ?? '', (int) ($row['extra_id'] ?? 0));
        }

        return $cart;
    }

    protected function itemKey(int $productId, int $persons, array $options): string
    {
        return md5($productId.'|'.$persons.'|'.json_encode($options, JSON_THROW_ON_ERROR));
    }

    protected function recalculateItemLine(array $item, Product $product): float
    {
        $line = $this->pricing->calculateProductPrice(
            $product,
            (int) ($item['persons'] ?? 1),
            (int) ($item['quantity'] ?? 1)
        );

        $modifier = 0.0;
        foreach ($item['options'] ?? [] as $option) {
            if (is_array($option) && isset($option['price_modifier'])) {
                $modifier += (float) $option['price_modifier'];
            }
        }

        return round($line + ($modifier * (int) ($item['quantity'] ?? 1)), 2);
    }

    protected function recalculateAllLines(): void
    {
        $cart = $this->getCart();
        $persons = (int) ($cart['event']['persons'] ?? 1);

        foreach ($cart['items'] as $key => $item) {
            if (($item['type'] ?? '') === 'package') {
                $pax = (int) ($item['persons'] ?? 1);
                $cart['items'][$key]['line_total'] = round((float) $item['unit_price'] * $pax * (int) ($item['quantity'] ?? 1), 2);
                continue;
            }

            $product = Product::query()->with('priceTiers')->find($item['product_id'] ?? 0);
            if (! $product) {
                continue;
            }
            // Keep item persons unless event persons should override; keep stored persons
            $cart['items'][$key]['unit_price'] = method_exists($product, 'getPriceForPersons')
                ? (float) $product->getPriceForPersons((int) $item['persons'])
                : (float) $product->price;
            $cart['items'][$key]['line_total'] = $this->recalculateItemLine($cart['items'][$key], $product);
        }

        foreach ($cart['extras'] as $id => $extraRow) {
            $extra = Extra::query()->find($extraRow['extra_id']);
            if (! $extra) {
                continue;
            }
            $cart['extras'][$id]['image'] = $extra->displayImage();
            $cart['extras'][$id]['line_total'] = $this->pricing->calculateExtraPrice(
                $extra,
                (int) $extraRow['quantity'],
                $persons
            );
        }

        $this->save($cart);
    }

    protected function subtotalOnly(array $cart): float
    {
        $sum = 0.0;
        foreach ($cart['items'] ?? [] as $item) {
            $sum += (float) ($item['line_total'] ?? 0);
        }
        foreach ($cart['extras'] ?? [] as $extra) {
            $sum += (float) ($extra['line_total'] ?? 0);
        }

        return round($sum, 2);
    }
}
