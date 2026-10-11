@php
    $on = fn (string $key, bool $default = true) => filter_var(old('settings.'.$key, $values[$key] ?? ($default ? '1' : '0')), FILTER_VALIDATE_BOOLEAN);
    $hasToken = filled($values['telegram_bot_token'] ?? '');
@endphp

<div class="max-w-3xl space-y-6">
    <form method="POST" action="{{ route('admin.settings.update') }}" class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm space-y-5">
        @csrf @method('PUT')
        <input type="hidden" name="group" value="notifications">

        <div>
            <h2 class="text-base font-semibold">E-posta bildirimleri</h2>
            <p class="mt-1 text-sm text-[var(--admin-muted)]">Ödeme sonrası müşteriye sipariş özeti gider. Yeni sipariş, teklif ve iletişim mesajları sizin adresinize düşer.</p>
            @if(config('mail.default') === 'log' || config('mail.mailers.'.config('mail.default').'.transport') === 'log')
                <p class="mt-2 rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-800">Şu an e-postalar gerçekten gönderilmiyor (<code>MAIL_MAILER=log</code>). Canlı mail için <code>.env</code> içinde SMTP bilgilerini doldurun.</p>
            @endif
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Bildirim e-postası</label>
            <input type="email" name="settings[admin_email]" value="{{ old('settings.admin_email', $values['admin_email'] ?? '') }}" placeholder="{{ setting('email', 'info@monnemtisch.de', 'contact') }}" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm">
            <p class="mt-1 text-xs text-stone-500">Boş bırakırsanız iletişim ayarlarındaki e-posta kullanılır (şu an: {{ setting('email', 'info@monnemtisch.de', 'contact') }}).</p>
        </div>

        @php
            $toggles = [
                'email_customer_order_paid' => ['Müşteriye ödeme onayı', 'Ödeme başarılı olunca menü, ürünler, tutar, KDV ve adres içeren mail gider.'],
                'email_admin_order' => ['Yeni sipariş (size)', 'Sipariş oluşunca işletme mailine “yeni sipariş” gider.'],
                'email_admin_order_paid' => ['Ödeme alındı (size)', 'Müşteri ödediğinde ikinci bir işletme maili gider.'],
                'email_admin_quote' => ['Teklif talepleri', 'Angebot formu doldurulunca size mail gider.'],
                'email_admin_contact' => ['İletişim mesajları', 'Kontakt formu doldurulunca size mail gider.'],
            ];
        @endphp
        <div class="divide-y divide-stone-100 rounded-xl border border-stone-100">
            @foreach($toggles as $key => [$label, $help])
                <label class="flex items-start gap-3 px-4 py-3">
                    <input type="hidden" name="settings[{{ $key }}]" value="0">
                    <input type="checkbox" name="settings[{{ $key }}]" value="1" class="mt-1 rounded border-stone-300" @checked($on($key))>
                    <span>
                        <span class="block text-sm font-medium">{{ $label }}</span>
                        <span class="block text-xs text-stone-500">{{ $help }}</span>
                    </span>
                </label>
            @endforeach
        </div>

        <div class="border-t border-stone-100 pt-5">
            <h2 class="text-base font-semibold">Telegram</h2>
            <p class="mt-1 text-sm text-[var(--admin-muted)]">Aynı olaylar Telegram grubunuza veya hesabınıza da düşer.</p>
        </div>

        <label class="flex items-start gap-3">
            <input type="hidden" name="settings[telegram_enabled]" value="0">
            <input type="checkbox" name="settings[telegram_enabled]" value="1" class="mt-1 rounded border-stone-300" @checked($on('telegram_enabled', false))>
            <span>
                <span class="block text-sm font-medium">Telegram bildirimlerini aç</span>
                <span class="block text-xs text-stone-500">Bot token ve Chat ID kaydedildikten sonra aktif olur.</span>
            </span>
        </label>

        <div>
            <label class="block text-sm font-medium mb-1">Bot token</label>
            <input type="password" name="settings[telegram_bot_token]" value="" autocomplete="new-password" placeholder="{{ $hasToken ? 'Kayıtlı token gizli – değiştirmek için yeni token yazın' : '123456:ABC-DEF...' }}" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm">
            @if($hasToken)
                <p class="mt-1 text-xs text-emerald-700">Token kayıtlı. Boş kaydederseniz mevcut token korunur.</p>
            @endif
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Chat ID</label>
            <input type="text" name="settings[telegram_chat_id]" value="{{ old('settings.telegram_chat_id', $values['telegram_chat_id'] ?? '') }}" placeholder="-1001234567890 veya kişisel ID" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm">
        </div>

        <div class="rounded-xl bg-stone-50 px-4 py-3 text-xs text-stone-600 leading-relaxed space-y-1">
            <p class="font-medium text-stone-800">Kurulum</p>
            <p>1. Telegram’da <strong>@BotFather</strong> ile bot oluşturun, token’ı kopyalayın.</p>
            <p>2. Bota (veya botu eklediğiniz gruba) bir mesaj yazın.</p>
            <p>3. Chat ID için <strong>@userinfobot</strong> kullanın. Gruplar için ID genelde <code>-100...</code> ile başlar.</p>
        </div>

        @php
            $tgToggles = [
                'telegram_on_order' => 'Yeni sipariş',
                'telegram_on_order_paid' => 'Ödeme alındı',
                'telegram_on_quote' => 'Teklif talebi',
                'telegram_on_contact' => 'İletişim mesajı',
            ];
        @endphp
        <div class="grid gap-2 sm:grid-cols-2">
            @foreach($tgToggles as $key => $label)
                <label class="flex items-center gap-2 rounded-xl border border-stone-100 px-3 py-2 text-sm">
                    <input type="hidden" name="settings[{{ $key }}]" value="0">
                    <input type="checkbox" name="settings[{{ $key }}]" value="1" class="rounded border-stone-300" @checked($on($key))>
                    {{ $label }}
                </label>
            @endforeach
        </div>

        <button class="px-4 py-2 rounded-lg bg-[var(--admin-sidebar)] text-white text-sm">Kaydet</button>
    </form>

    <form method="POST" action="{{ route('admin.settings.telegram-test') }}" class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        @csrf
        <p class="text-sm text-stone-600">Kayıtlı bot ile Telegram’a test mesajı gönderin.</p>
        <button class="shrink-0 px-4 py-2 rounded-lg border border-stone-200 bg-white text-sm font-medium hover:border-[var(--admin-gold)]">Test mesajı gönder</button>
    </form>
</div>
