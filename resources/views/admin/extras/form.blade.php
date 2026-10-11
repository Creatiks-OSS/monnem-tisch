@php
    $isDrink = (bool) old('is_drink', ($extra->kind ?? 'service') === 'product');
@endphp
@include('admin.partials.errors')
<div class="grid sm:grid-cols-2 gap-4">
    <div class="sm:col-span-2">
        <label class="block text-sm mb-1">Ad</label>
        <input name="name" value="{{ old('name', $extra->name ?? '') }}" required class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm">
    </div>
    <div>
        <label class="block text-sm mb-1">Fiyat</label>
        <input type="number" step="0.01" name="price" value="{{ old('price', $extra->price ?? '') }}" required class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm">
    </div>
    <div>
        <label class="block text-sm mb-1">Fiyat tipi</label>
        <select name="price_type" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm">
            @foreach (\App\Models\Extra::PRICE_TYPES as $value => $label)
                <option value="{{ $value }}" @selected(old('price_type', $extra->price_type ?? 'fixed') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm mb-1">Görsel</label>
        @if ($extra->exists)
            <img
                src="{{ $extra->displayImage() }}"
                alt=""
                class="mb-2 h-24 w-24 rounded-xl bg-[#f3ece3] object-cover"
                referrerpolicy="no-referrer"
            >
        @endif
        <input type="file" name="image" accept="image/*" class="w-full text-sm">
    </div>
</div>
<div>
    <label class="block text-sm mb-1">Açıklama</label>
    <textarea name="description" rows="3" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm">{{ old('description', $extra->description ?? '') }}</textarea>
</div>
<label class="flex items-center gap-2 text-sm">
    <input type="checkbox" name="is_drink" value="1" @checked($isDrink)>
    Getränk — 19% MwSt (sonst 7%)
</label>
<label class="flex items-center gap-2 text-sm">
    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $extra->is_active ?? true))>
    Aktif
</label>
