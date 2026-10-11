<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    public function toggle(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
        ], [
            'required' => 'Das Feld :attribute ist erforderlich.',
            'exists' => 'Die ausgewählte :attribute ist ungültig.',
        ], [
            'product_id' => 'Produkt',
        ]);

        $productId = (int) $validated['product_id'];
        Product::query()->active()->findOrFail($productId);

        $userId = Auth::id();
        $sessionId = $request->session()->getId();

        $query = Favorite::query()->where('product_id', $productId);

        if ($userId) {
            $query->where('user_id', $userId);
        } else {
            $query->where('session_id', $sessionId)->whereNull('user_id');
        }

        $existing = $query->first();

        if ($existing) {
            $existing->delete();

            return response()->json([
                'success' => true,
                'favorited' => false,
                'message' => 'Produkt aus Favoriten entfernt.',
                'count' => $this->countForVisitor($userId, $sessionId),
            ]);
        }

        Favorite::query()->create([
            'user_id' => $userId,
            'session_id' => $userId ? null : $sessionId,
            'product_id' => $productId,
        ]);

        return response()->json([
            'success' => true,
            'favorited' => true,
            'message' => 'Produkt zu Favoriten hinzugefügt.',
            'count' => $this->countForVisitor($userId, $sessionId),
        ]);
    }

    protected function countForVisitor(?int $userId, string $sessionId): int
    {
        $query = Favorite::query();

        if ($userId) {
            $query->where('user_id', $userId);
        } else {
            $query->where('session_id', $sessionId)->whereNull('user_id');
        }

        return $query->count();
    }
}
