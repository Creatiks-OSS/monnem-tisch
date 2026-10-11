@php
    $hasPassword = filled($values['password'] ?? '');
    $encryption = old('settings.encryption', $values['encryption'] ?? 'tls');
@endphp

<div class="max-w-3xl space-y-6">
    <form method="POST" action="{{ route('admin.settings.update') }}" class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm space-y-5">
        @csrf @method('PUT')
        <input type="hidden" name="group" value="mail">

        <div>
            <h2 class="text-base font-semibold">Gönderen</h2>
            <p class="mt-1 text-sm text-[var(--admin-muted)]">Müşteri ve işletme maillerinde görünen gönderen bilgisi.</p>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Gönderen adı</label>
            <input type="text" name="settings[from_name]" value="{{ old('settings.from_name', $values['from_name'] ?? 'Monnemtisch Catering') }}" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Gönderen e-posta</label>
            <input type="email" name="settings[from_address]" value="{{ old('settings.from_address', $values['from_address'] ?? 'info@monnemtisch.de') }}" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Reply-To (isteğe bağlı)</label>
            <input type="email" name="settings[reply_to]" value="{{ old('settings.reply_to', $values['reply_to'] ?? '') }}" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm">
        </div>

        <div class="border-t border-stone-100 pt-5">
            <h2 class="text-base font-semibold">SMTP sunucu</h2>
            <p class="mt-1 text-sm text-[var(--admin-muted)]">Host doldurulunca mailler .env yerine buradan gönderilir. Hosting panelinden SMTP bilgilerini alın.</p>
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium mb-1">SMTP host</label>
                <input type="text" name="settings[host]" value="{{ old('settings.host', $values['host'] ?? '') }}" placeholder="smtp.ionos.de veya mail.monnemtisch.de" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Port</label>
                <input type="text" name="settings[port]" value="{{ old('settings.port', $values['port'] ?? '587') }}" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Şifreleme</label>
                <select name="settings[encryption]" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm">
                    <option value="tls" @selected($encryption === 'tls')>TLS (587)</option>
                    <option value="ssl" @selected($encryption === 'ssl')>SSL (465)</option>
                    <option value="" @selected($encryption === '' || $encryption === 'none')>Yok</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Kullanıcı adı</label>
                <input type="text" name="settings[username]" value="{{ old('settings.username', $values['username'] ?? '') }}" autocomplete="off" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Şifre</label>
                <input type="password" name="settings[password]" value="" autocomplete="new-password" placeholder="{{ $hasPassword ? 'Kayıtlı şifre gizli – değiştirmek için yazın' : 'SMTP şifresi' }}" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm">
                @if($hasPassword)
                    <p class="mt-1 text-xs text-emerald-700">Şifre kayıtlı. Boş kaydederseniz mevcut şifre korunur.</p>
                @endif
            </div>
        </div>

        <button class="px-4 py-2 rounded-lg bg-[var(--admin-sidebar)] text-white text-sm">Kaydet</button>
    </form>

    <form method="POST" action="{{ route('admin.settings.mail-test') }}" class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        @csrf
        <p class="text-sm text-stone-600">Kayıtlı SMTP ile test maili gönderir. Alıcı: Bildirimler sekmesindeki adres, yoksa gönderen e-posta.</p>
        <button class="shrink-0 px-4 py-2 rounded-lg border border-stone-200 bg-white text-sm font-medium hover:border-[var(--admin-gold)]">Test maili gönder</button>
    </form>
</div>
