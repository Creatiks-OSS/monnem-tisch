<?php

namespace App\Http\Controllers\Kitchen;

use App\Http\Controllers\Controller;
use App\Services\KitchenService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KitchenAuthController extends Controller
{
    public function __construct(
        protected KitchenService $kitchen
    ) {}

    public function showLogin(Request $request): View|RedirectResponse
    {
        if ($request->session()->get(KitchenService::SESSION_KEY) === true) {
            return redirect()->route('kitchen.board');
        }

        return view('kitchen.login', [
            'pinConfigured' => $this->kitchen->pinIsConfigured(),
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'pin' => ['required', 'string', 'size:4', 'regex:/^[A-Za-z0-9]{4}$/'],
        ], [
            'pin.required' => 'PIN girin.',
            'pin.size' => 'PIN tam 4 karakter olmalı.',
            'pin.regex' => 'PIN yalnızca harf ve rakam olabilir.',
        ]);

        if (! $this->kitchen->pinIsConfigured()) {
            return back()->withErrors(['pin' => 'Mutfak PIN’i henüz ayarlanmadı. Admin → Ayarlar → Mutfak.']);
        }

        if (! $this->kitchen->verifyPin($data['pin'])) {
            return back()->withErrors(['pin' => 'PIN hatalı.']);
        }

        $request->session()->put(KitchenService::SESSION_KEY, true);

        if (! $request->user()) {
            $request->session()->regenerate();
        }

        return redirect()->route('kitchen.board');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget(KitchenService::SESSION_KEY);

        if (! $request->user()) {
            $request->session()->regenerate();
        }

        return redirect()->route('kitchen.login');
    }
}
