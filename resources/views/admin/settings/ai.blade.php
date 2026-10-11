@php
    $on = fn (string $key, bool $default = true) => filter_var(old('settings.'.$key, $values[$key] ?? ($default ? '1' : '0')), FILTER_VALIDATE_BOOLEAN);
    $hasKey = filled($values['api_key'] ?? '');
    $model = old('settings.model', $values['model'] ?? 'gpt-4o-mini');
@endphp

<div class="max-w-3xl space-y-6">
    <form method="POST" action="{{ route('admin.settings.update') }}" class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm space-y-5">
        @csrf @method('PUT')
        <input type="hidden" name="group" value="ai">

        <div>
            <h2 class="text-base font-semibold">OpenAI (GPT-4o mini)</h2>
            <p class="mt-1 text-sm text-[var(--admin-muted)]">
                GPT-4o mini: sipariş malzeme analizi ve teklif taleplerinde menü/yemek önerisi.
                API anahtarı kaydedildikten sonra her iki işlem de çalışır.
            </p>
        </div>

        <label class="flex items-start gap-3">
            <input type="hidden" name="settings[enabled]" value="0">
            <input type="checkbox" name="settings[enabled]" value="1" class="mt-1 rounded border-stone-300" @checked($on('enabled'))>
            <span>
                <span class="block text-sm font-medium">Malzeme analizini aç</span>
                <span class="block text-xs text-stone-500">API anahtarı kaydedildikten sonra yeni siparişlerde çalışır.</span>
            </span>
        </label>

        <div>
            <label class="block text-sm font-medium mb-1">API anahtarı</label>
            <input type="password" name="settings[api_key]" value="" autocomplete="new-password" placeholder="{{ $hasKey ? 'Kayıtlı anahtar gizli – değiştirmek için yeni anahtar yazın' : 'sk-...' }}" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm">
            @if($hasKey)
                <p class="mt-1 text-xs text-emerald-700">API anahtarı kayıtlı. Boş kaydederseniz mevcut anahtar korunur.</p>
            @endif
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Model</label>
            <input type="text" name="settings[model]" value="{{ $model }}" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm">
            <p class="mt-1 text-xs text-stone-500">Varsayılan: <code>gpt-4o-mini</code></p>
        </div>

        <button class="px-4 py-2 rounded-lg bg-[var(--admin-sidebar)] text-white text-sm">Kaydet</button>
    </form>

    <form method="POST" action="{{ route('admin.settings.ai-test') }}" class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        @csrf
        <p class="text-sm text-stone-600">Kayıtlı anahtarla OpenAI bağlantısını dener.</p>
        <button class="shrink-0 px-4 py-2 rounded-lg border border-stone-200 bg-white text-sm font-medium hover:border-[var(--admin-gold)]">Bağlantıyı test et</button>
    </form>
</div>
