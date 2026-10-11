<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\CartAddRequest;
use App\Models\Extra;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        protected CartService $cart
    ) {}

    public function index(): RedirectResponse
    {
        $cart = $this->cart->getCart();

        if (empty($cart['items']) && empty($cart['extras'])) {
            return redirect()
                ->route('packages.index', 'buffets')
                ->with('error', 'Bitte wählen Sie zuerst ein Menü.');
        }

        return redirect()->route('checkout.index');
    }

    public function add(CartAddRequest $request): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Einzelne Gerichte können nicht direkt bestellt werden. Bitte wählen Sie ein Menüpaket.',
        ], 422);
    }

    public function update(Request $request, string $key): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:999'],
            'persons' => ['nullable', 'integer', 'min:1', 'max:5000'],
        ], [
            'required' => 'Das Feld :attribute ist erforderlich.',
            'integer' => 'Das Feld :attribute muss eine ganze Zahl sein.',
            'min' => 'Das Feld :attribute muss mindestens :min sein.',
        ], [
            'quantity' => 'Menge',
            'persons' => 'Personenanzahl',
        ]);

        $this->cart->updateItem(
            $key,
            (int) $validated['quantity'],
            isset($validated['persons']) ? (int) $validated['persons'] : null
        );

        return $this->cartJson('Warenkorb wurde aktualisiert.');
    }

    public function remove(string $key): JsonResponse
    {
        $this->cart->removeItem($key);

        return $this->cartJson('Position wurde entfernt.');
    }

    public function addExtra(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'extra_id' => ['required', 'integer', 'exists:extras,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:999'],
        ], [
            'required' => 'Das Feld :attribute ist erforderlich.',
            'exists' => 'Die ausgewählte :attribute ist ungültig.',
        ], [
            'extra_id' => 'Extra',
            'quantity' => 'Menge',
        ]);

        $this->cart->addExtra(
            (int) $validated['extra_id'],
            (int) ($validated['quantity'] ?? 1)
        );

        return $this->cartJson('Extra wurde hinzugefügt.');
    }

    public function updateExtra(Request $request, int $extraId): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:999'],
        ]);

        $this->cart->setExtraQuantity($extraId, (int) $validated['quantity']);

        return $this->cartJson('Menge wurde aktualisiert.');
    }

    public function removeExtra(int $extraId): JsonResponse
    {
        $this->cart->removeExtra($extraId);

        return $this->cartJson('Extra wurde entfernt.');
    }

    public function setEvent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event_type_id' => ['nullable', 'integer', 'exists:event_types,id'],
            'date' => ['nullable', 'date'],
            'time' => ['nullable', 'date_format:H:i'],
            'persons' => ['nullable', 'integer', 'min:1', 'max:5000'],
        ]);

        $this->cart->setEvent($validated);

        return $this->cartJson('Event-Daten gespeichert.');
    }

    public function setDelivery(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'postal_code' => ['nullable', 'string', 'max:20'],
            'city' => ['nullable', 'string', 'max:100'],
            'street' => ['nullable', 'string', 'max:255'],
            'house_number' => ['nullable', 'string', 'max:20'],
        ]);

        $this->cart->setDelivery($validated);

        return $this->cartJson('Lieferdaten gespeichert.');
    }

    public function applyCoupon(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50'],
        ], [
            'required' => 'Bitte geben Sie einen Gutscheincode ein.',
        ], [
            'code' => 'Gutscheincode',
        ]);

        $ok = $this->cart->applyCoupon($validated['code']);

        if (! $ok) {
            return response()->json([
                'success' => false,
                'message' => 'Der Gutscheincode ist ungültig oder nicht anwendbar.',
                'cart' => $this->cart->getCart(),
                'totals' => $this->cart->totals(),
            ], 422);
        }

        return $this->cartJson('Gutschein wurde angewendet.');
    }

    public function removeCoupon(): JsonResponse
    {
        $this->cart->removeCoupon();

        return $this->cartJson('Gutschein wurde entfernt.');
    }

    public function clear(): RedirectResponse
    {
        $this->cart->clear();

        return redirect()
            ->route('packages.index', 'buffets')
            ->with('success', 'Ihr Warenkorb wurde geleert.');
    }

    protected function cartJson(string $message): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'cart' => $this->cart->getCart(),
            'totals' => $this->cart->totals(),
        ]);
    }
}
