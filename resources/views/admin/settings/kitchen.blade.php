@php
    $hasPin = filled($values['pin_hash'] ?? '');
@endphp

<div class="max-w-3xl space-y-6">
    <form method="POST" action="{{ route('admin.settings.update') }}" class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm space-y-5">
        @csrf @method('PUT')
        <input type="hidden" name="group" value="kitchen">

        <div>
            <h2 class="text-base font-semibold">Mutfak paneli</h2>
            <p class="mt-1 text-sm text-[var(--admin-muted)]">
                Mutfak ekranı <a href="{{ url('/mutfak') }}" class="text-[var(--admin-terracotta)] underline" target="_blank" rel="noopener">/mutfak</a> adresinde.
                Giriş yalnızca sizin belirlediğiniz 4 karakterli PIN ile yapılır. Siparişler etkinliğe 2 gün kala otomatik düşer; buradan da “Mutfağa gönder” ile öne alabilirsiniz.
            </p>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">4 karakterli PIN</label>
            <input type="password" name="settings[pin]" value="" maxlength="4" autocomplete="new-password" inputmode="numeric" class="w-36 rounded-xl border border-stone-200 px-3 py-2 text-sm tracking-[0.4em]" placeholder="••••">
            <p class="mt-1 text-xs text-stone-500">Harf veya rakam. PIN tarayıcıda saklanmaz, yalnızca hash kaydedilir.</p>
            @if($hasPin)
                <p class="mt-2 text-xs text-emerald-700">PIN kayıtlı. Boş kaydederseniz mevcut PIN korunur.</p>
            @else
                <p class="mt-2 text-xs text-amber-700">Henüz PIN yok — mutfak paneli kilitli kalır.</p>
            @endif
        </div>

        <button class="px-4 py-2 rounded-lg bg-[var(--admin-sidebar)] text-white text-sm">Kaydet</button>
    </form>
</div>
