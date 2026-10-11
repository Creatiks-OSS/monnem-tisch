<?php

namespace App\Http\Middleware;

use App\Services\KitchenService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureKitchenAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->session()->get(KitchenService::SESSION_KEY) === true) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Mutfak oturumu yok.'], 401);
        }

        return redirect()->route('kitchen.login');
    }
}
