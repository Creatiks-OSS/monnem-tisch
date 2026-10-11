@extends('admin.layouts.app')
@section('title','Manuel fatura')
@section('heading','Manuel fatura')
@section('content')
<form method="POST" action="{{ route('admin.invoices.store-manual') }}" class="max-w-4xl space-y-6">
    @csrf
    @include('admin.partials.errors')

    <div class="space-y-4 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
        <h2 class="text-sm font-semibold uppercase tracking-wide text-stone-500">Müşteri</h2>
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm">Ad</label>
                <input name="billing_first_name" value="{{ old('billing_first_name') }}" required class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm">Soyad</label>
                <input name="billing_last_name" value="{{ old('billing_last_name') }}" required class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm">Firma</label>
                <input name="billing_company" value="{{ old('billing_company') }}" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm">E-posta</label>
                <input type="email" name="billing_email" value="{{ old('billing_email') }}" required class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm">Telefon</label>
                <input name="billing_phone" value="{{ old('billing_phone') }}" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm">
            </div>
        </div>
        <div class="grid gap-4 sm:grid-cols-4">
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm">Sokak</label>
                <input name="billing_street" value="{{ old('billing_street') }}" required class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm">No</label>
                <input name="billing_house_number" value="{{ old('billing_house_number') }}" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm">Posta kodu</label>
                <input name="billing_postal_code" value="{{ old('billing_postal_code') }}" required class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm">
            </div>
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm">Şehir</label>
                <input name="billing_city" value="{{ old('billing_city') }}" required class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm">
            </div>
        </div>
    </div>

    <div class="space-y-4 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-stone-500">Kalemler</h2>
            <div class="flex flex-wrap gap-3">
                <label class="text-sm">İndirimli KDV %
                    <input name="tax_rate" value="{{ old('tax_rate', rtrim(rtrim(number_format($reducedRate, 2, ',', ''), '0'), ',')) }}" class="ml-1 w-20 rounded-xl border border-stone-200 px-3 py-2 text-sm" data-tax="reduced">
                </label>
                <label class="text-sm">Normal KDV %
                    <input name="tax_standard_rate" value="{{ old('tax_standard_rate', rtrim(rtrim(number_format($standardRate, 2, ',', ''), '0'), ',')) }}" class="ml-1 w-20 rounded-xl border border-stone-200 px-3 py-2 text-sm" data-tax="standard">
                </label>
            </div>
        </div>
        <p class="text-xs text-stone-500">Fiyatlar nettir. KDV, seçilen orana göre eklenir. Virgül kullanabilirsiniz.</p>
        <div class="space-y-3" id="invoice-items">
            @php
                $oldItems = old('items', [[
                    'name' => '',
                    'quantity' => '1',
                    'unit_price' => '',
                    'tax' => 'reduced',
                ]]);
            @endphp
            @foreach($oldItems as $index => $item)
                <div class="invoice-item grid gap-3 rounded-xl border border-stone-100 p-3 sm:grid-cols-12">
                    <div class="sm:col-span-5">
                        <label class="mb-1 block text-xs text-stone-500">Açıklama</label>
                        <input name="items[{{ $index }}][name]" value="{{ $item['name'] ?? '' }}" required class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-xs text-stone-500">Adet</label>
                        <input name="items[{{ $index }}][quantity]" value="{{ $item['quantity'] ?? '1' }}" required class="item-qty w-full rounded-xl border border-stone-200 px-3 py-2 text-sm">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-xs text-stone-500">Birim net</label>
                        <input name="items[{{ $index }}][unit_price]" value="{{ $item['unit_price'] ?? '' }}" required class="item-price w-full rounded-xl border border-stone-200 px-3 py-2 text-sm">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-xs text-stone-500">KDV</label>
                        <select name="items[{{ $index }}][tax]" class="item-tax w-full rounded-xl border border-stone-200 px-3 py-2 text-sm">
                            <option value="reduced" @selected(($item['tax'] ?? 'reduced') === 'reduced')>İndirimli</option>
                            <option value="standard" @selected(($item['tax'] ?? '') === 'standard')>Normal</option>
                        </select>
                    </div>
                    <div class="flex items-end sm:col-span-1">
                        <button type="button" class="remove-item w-full rounded-lg border border-stone-200 px-2 py-2 text-xs text-stone-500">Sil</button>
                    </div>
                </div>
            @endforeach
        </div>
        <button type="button" id="add-item" class="rounded-lg border border-stone-300 px-3 py-2 text-sm">Kalem ekle</button>
        <p class="text-sm text-stone-600">Net <strong id="sum-net">0,00 €</strong> · KDV <strong id="sum-tax">0,00 €</strong> · Toplam <strong id="sum-total">0,00 €</strong></p>
    </div>

    <div class="space-y-4 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
        <label class="mb-1 block text-sm">Not</label>
        <textarea name="notes" rows="3" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm">{{ old('notes') }}</textarea>
        <p class="text-sm text-stone-600">
            Ödeme bilgisi faturada ve e-postada şöyle yer alır:<br>
            <strong>{{ \App\Models\Invoice::MANUAL_ACCOUNT_HOLDER }}</strong><br>
            IBAN {{ \App\Models\Invoice::MANUAL_IBAN }}
        </p>
        <button class="rounded-lg bg-[var(--admin-sidebar)] px-4 py-2 text-sm text-white">Oluştur ve gönder</button>
    </div>
</form>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const list = document.getElementById('invoice-items');
    const add = document.getElementById('add-item');
    const parse = (value) => {
        let raw = String(value || '').trim().replace(/\s/g, '');
        if (raw.includes(',')) raw = raw.replace(/\./g, '').replace(',', '.');
        const number = parseFloat(raw);
        return Number.isFinite(number) ? number : 0;
    };
    const money = (value) => value.toLocaleString('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' €';
    const reindex = () => {
        list.querySelectorAll('.invoice-item').forEach((row, index) => {
            row.querySelectorAll('[name]').forEach((field) => {
                field.name = field.name.replace(/items\[\d+]/, 'items[' + index + ']');
            });
        });
    };
    const totals = () => {
        const reducedRate = parse(document.querySelector('[data-tax="reduced"]').value);
        const standardRate = parse(document.querySelector('[data-tax="standard"]').value);
        let reduced = 0;
        let standard = 0;
        list.querySelectorAll('.invoice-item').forEach((row) => {
            const line = Math.round(parse(row.querySelector('.item-qty').value) * parse(row.querySelector('.item-price').value) * 100) / 100;
            if (row.querySelector('.item-tax').value === 'standard') standard += line;
            else reduced += line;
        });
        const tax = Math.round(reduced * reducedRate) / 100 + Math.round(standard * standardRate) / 100;
        const net = reduced + standard;
        document.getElementById('sum-net').textContent = money(net);
        document.getElementById('sum-tax').textContent = money(tax);
        document.getElementById('sum-total').textContent = money(net + tax);
    };
    add.addEventListener('click', () => {
        const row = list.querySelector('.invoice-item').cloneNode(true);
        row.querySelectorAll('input').forEach((field) => { field.value = field.classList.contains('item-qty') ? '1' : ''; });
        row.querySelector('select').value = 'reduced';
        list.appendChild(row);
        reindex();
        totals();
    });
    list.addEventListener('click', (event) => {
        if (!event.target.classList.contains('remove-item')) return;
        if (list.querySelectorAll('.invoice-item').length === 1) return;
        event.target.closest('.invoice-item').remove();
        reindex();
        totals();
    });
    document.querySelector('form').addEventListener('input', totals);
    totals();
});
</script>
@endsection
