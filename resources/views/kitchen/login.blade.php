@extends('layouts.kitchen')
@section('title', 'Mutfak girişi')
@section('body')
<div class="flex min-h-screen items-center justify-center px-6">
    <div class="w-full max-w-md">
        <h1 class="k-brand text-5xl text-[var(--k-ink)] sm:text-6xl">Mutfak</h1>
        <p class="mt-2 text-base text-[var(--k-muted)]">Personel girişi</p>

        @unless($pinConfigured)
            <p class="mt-8 text-sm text-[var(--k-muted)]">PIN henüz tanımlı değil. Admin → Ayarlar → Mutfak.</p>
        @endunless

        @if($errors->any())
            <p class="mt-8 text-sm text-[var(--k-terracotta)]">{{ $errors->first() }}</p>
        @endif

        <form method="POST" action="{{ route('kitchen.login.store') }}" class="mt-12"
              onsubmit="try{sessionStorage.setItem('kitchen_audio','1')}catch(e){}">
            @csrf
            <label for="pin" class="mb-3 block text-[11px] font-medium uppercase tracking-[0.22em] text-[var(--k-sage)]">PIN</label>
            <input
                id="pin"
                type="password"
                name="pin"
                maxlength="4"
                inputmode="numeric"
                autocomplete="off"
                autofocus
                required
                class="w-full rounded-full border border-[var(--k-sage)] bg-[var(--k-paper)] px-6 py-3.5 text-center text-lg tracking-[0.7em] text-[var(--k-ink)] outline-none focus:border-[var(--k-terracotta)]"
                @unless($pinConfigured) disabled @endunless
            >
            <div class="mt-8 text-center">
                <button
                    type="submit"
                    class="text-base font-medium text-[var(--k-sage)] disabled:opacity-40"
                    @unless($pinConfigured) disabled @endunless
                >Giriş</button>
            </div>
        </form>
    </div>
</div>
@endsection
