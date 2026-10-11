@extends('admin.layouts.app')
@section('title','Ödeme sistemleri')
@section('heading','Ödeme sistemleri')
@section('subheading', 'Checkout’taki kredi kartı ve PayPal kutularını buradan gösterin veya gizleyin')
@section('content')
@include('admin.partials.errors')

<form method="POST" action="{{ route('admin.payments.update') }}" class="space-y-6">
    @csrf @method('PUT')

    <section class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
        <h2 class="text-xl font-semibold">Checkout görünürlüğü</h2>
        <p class="mt-1 text-sm text-stone-500">Kapalı yöntem /checkout sayfasında görünmez. Anahtarlar kayıtlı kalır.</p>
        <div class="mt-5 grid gap-4 sm:grid-cols-2">
            <label class="flex cursor-pointer items-center justify-between gap-4 rounded-2xl border border-stone-200 px-4 py-4 has-[:checked]:border-[#c45c26] has-[:checked]:bg-[#fde8e4]/50">
                <span>
                    <span class="block font-semibold">Kreditkarte</span>
                    <span class="mt-0.5 block text-xs text-stone-500">Visa, Mastercard, Amex</span>
                </span>
                <span class="inline-flex items-center gap-2 text-sm">
                    <input type="hidden" name="stripe_enabled" value="0">
                    <input type="checkbox" name="stripe_enabled" value="1" @checked(old('stripe_enabled', $stripe['enabled'] ?? false)) class="rounded border-stone-300 text-[#c45c26] focus:ring-[#c45c26]">
                    Göster
                </span>
            </label>
            <label class="flex cursor-pointer items-center justify-between gap-4 rounded-2xl border border-stone-200 px-4 py-4 has-[:checked]:border-[#003087] has-[:checked]:bg-[#e8eef7]/50">
                <span>
                    <span class="block font-semibold">PayPal</span>
                    <span class="mt-0.5 block text-xs text-stone-500">PayPal hesabı veya kart</span>
                </span>
                <span class="inline-flex items-center gap-2 text-sm">
                    <input type="hidden" name="paypal_enabled" value="0">
                    <input type="checkbox" name="paypal_enabled" value="1" @checked(old('paypal_enabled', $paypal['enabled'] ?? false)) class="rounded border-stone-300 text-[#003087] focus:ring-[#003087]">
                    Göster
                </span>
            </label>
        </div>
    </section>

    <div class="grid gap-6 xl:grid-cols-2">
        <section class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-[#635BFF]">Stripe</p>
                <h2 class="mt-1 text-xl font-semibold">Kredi kartı anahtarları</h2>
                <p class="mt-1 text-sm text-stone-500">Visa, Mastercard ve Amex Stripe üzerinden tahsil edilir.</p>
            </div>

            <div class="mt-5 space-y-4">
                <div>
                    <label class="admin-label">Mod</label>
                    <select name="stripe_mode" class="admin-select">
                        <option value="test" @selected(old('stripe_mode', $stripe['mode'] ?? 'test') === 'test')>Test (sandbox)</option>
                        <option value="live" @selected(old('stripe_mode', $stripe['mode'] ?? 'test') === 'live')>Canlı</option>
                    </select>
                </div>
                <div>
                    <label class="admin-label">Publishable key</label>
                    <input type="text" name="stripe_public_key" value="{{ old('stripe_public_key', $stripe['public_key'] ?? '') }}" placeholder="pk_test_…" class="admin-input" autocomplete="off">
                </div>
                <div>
                    <label class="admin-label">Secret key</label>
                    <input type="password" name="stripe_secret_key" value="" placeholder="{{ !empty($stripe['secret_key']) ? 'Kayıtlı · değiştirmek için yeni anahtar yazın' : 'sk_test_…' }}" class="admin-input" autocomplete="new-password">
                </div>
                <div>
                    <label class="admin-label">Webhook secret</label>
                    <input type="password" name="stripe_webhook_secret" value="" placeholder="{{ !empty($stripe['webhook_secret']) ? 'Kayıtlı · değiştirmek için yeni secret yazın' : 'whsec_…' }}" class="admin-input" autocomplete="new-password">
                    <p class="mt-1.5 text-xs text-stone-500">Webhook URL (Stripe Dashboard’a yapıştırın, tarayıcıda açmayın): <code class="rounded bg-stone-100 px-1.5 py-0.5">{{ $stripeWebhook }}</code></p>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-[#003087]">PayPal</p>
                <h2 class="mt-1 text-xl font-semibold">PayPal anahtarları</h2>
                <p class="mt-1 text-sm text-stone-500">Müşteri PayPal hesabı veya kart ile öder.</p>
            </div>

            <div class="mt-5 space-y-4">
                <div>
                    <label class="admin-label">Mod</label>
                    <select name="paypal_mode" class="admin-select">
                        <option value="sandbox" @selected(old('paypal_mode', $paypal['mode'] ?? 'sandbox') === 'sandbox')>Sandbox</option>
                        <option value="live" @selected(old('paypal_mode', $paypal['mode'] ?? 'sandbox') === 'live')>Canlı</option>
                    </select>
                </div>
                <div>
                    <label class="admin-label">Client ID</label>
                    <input type="text" name="paypal_client_id" value="{{ old('paypal_client_id', $paypal['client_id'] ?? '') }}" class="admin-input" autocomplete="off">
                </div>
                <div>
                    <label class="admin-label">Secret</label>
                    <input type="password" name="paypal_secret" value="" placeholder="{{ !empty($paypal['secret']) ? 'Kayıtlı · değiştirmek için yeni secret yazın' : 'Secret' }}" class="admin-input" autocomplete="new-password">
                </div>
                <div>
                    <label class="admin-label">Webhook ID (isteğe bağlı)</label>
                    <input type="text" name="paypal_webhook_id" value="{{ old('paypal_webhook_id', $paypal['webhook_id'] ?? '') }}" class="admin-input" autocomplete="off">
                    <p class="mt-1.5 text-xs text-stone-500">Webhook URL (PayPal Developer’a yapıştırın, tarayıcıda açmayın): <code class="rounded bg-stone-100 px-1.5 py-0.5">{{ $paypalWebhook }}</code></p>
                </div>
            </div>
        </section>
    </div>

    <button type="submit" class="admin-btn">Ayarları kaydet</button>
</form>

<section class="mt-10">
    <h2 class="mb-4 text-xl font-semibold">Son ödemeler</h2>
    <div class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
        <table class="min-w-full text-sm">
            <thead class="bg-stone-50 text-left text-xs uppercase text-stone-500">
                <tr>
                    <th class="px-4 py-3">Sipariş</th>
                    <th class="px-4 py-3">Yöntem</th>
                    <th class="px-4 py-3">Durum</th>
                    <th class="px-4 py-3">Tutar</th>
                    <th class="px-4 py-3">İşlem no</th>
                    <th class="px-4 py-3">Tarih</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
            @forelse($payments as $payment)
                <tr class="hover:bg-stone-50">
                    <td class="px-4 py-3">
                        @if($payment->order)
                            <a href="{{ route('admin.orders.show', $payment->order) }}" class="text-[#c4a35a]">{{ $payment->order->order_number }}</a>
                        @else
                            —
                        @endif
                    </td>
                    <td class="px-4 py-3 uppercase">{{ $payment->method }}</td>
                    <td class="px-4 py-3">{{ admin_label($payment->status) }}</td>
                    <td class="px-4 py-3">{{ number_format((float) $payment->amount, 2, ',', '.') }} {{ $payment->currency }}</td>
                    <td class="px-4 py-3 text-xs text-stone-500">{{ $payment->transaction_id ?: '—' }}</td>
                    <td class="px-4 py-3">{{ optional($payment->paid_at ?: $payment->created_at)->format('d.m.Y H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-12 text-center text-stone-500">Henüz ödeme kaydı yok.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $payments->links() }}</div>
</section>
@endsection
