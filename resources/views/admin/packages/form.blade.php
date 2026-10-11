@extends('admin.layouts.app')
@php $editing = (bool) ($package?->exists); @endphp
@section('title', $editing ? 'Paketi düzenle' : 'Yeni paket')
@section('heading', $editing ? 'Paketi düzenle' : 'Yeni paket')
@section('subheading', 'Fiyat pakette. Yemekler seçim kotalarından gelir — tekli satış yok.')
@section('content')
<form method="POST" action="{{ $editing ? route('admin.packages.update', $package) : route('admin.packages.store') }}" enctype="multipart/form-data" class="max-w-5xl space-y-6" x-data="slotEditor({{ Js::from($package?->slots ?? []) }})">
    @csrf
    @if($editing) @method('PUT') @endif
    @include('admin.partials.errors')

    <div class="admin-card p-6 space-y-5">
        <div>
            <p class="text-[11px] uppercase tracking-[0.2em] text-[var(--admin-terracotta)]">Genel</p>
            <h2 class="mt-1 text-2xl">Paket kartı</h2>
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label class="admin-label">Paket adı</label>
                <input name="name" value="{{ old('name', $package->name ?? '') }}" required class="admin-input" placeholder="Buffet Mega L">
            </div>
            <div class="sm:col-span-2">
                <label class="admin-label">Alt başlık</label>
                <input name="subtitle" value="{{ old('subtitle', $package->subtitle ?? 'Freie Auswahl von der Speisekarte') }}" class="admin-input">
            </div>
            <div>
                <label class="admin-label">Menü grubu</label>
                <select name="menu_group" required class="admin-select">
                    @foreach (\App\Models\CateringPackage::MENUS as $key => $item)
                        <option value="{{ $key }}" @selected(old('menu_group', $package->menu_group ?? 'buffets') === $key)>{{ $item['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="admin-label">Etkinlik türü</label>
                <select name="event_type_id" class="admin-select">
                    <option value="">—</option>
                    @foreach($eventTypes as $et)
                        <option value="{{ $et->id }}" @selected(old('event_type_id', $package->event_type_id ?? '') == $et->id)>{{ $et->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="admin-label">Kişi başı fiyat (€)</label>
                <input type="number" step="0.01" name="price_per_person" value="{{ old('price_per_person', $package->price_per_person ?? '') }}" class="admin-input">
            </div>
            <div>
                <label class="admin-label">Eski fiyat (€)</label>
                <input type="number" step="0.01" name="compare_price" value="{{ old('compare_price', $package->compare_price ?? '') }}" class="admin-input">
            </div>
            <div>
                <label class="admin-label">İndirim etiketi</label>
                <input name="discount_label" value="{{ old('discount_label', $package->discount_label ?? '') }}" class="admin-input" placeholder="Aktion -20%">
            </div>
            <div>
                <label class="admin-label">Rozet</label>
                <input name="badge" value="{{ old('badge', $package->badge ?? '') }}" class="admin-input" placeholder="POPULÄR">
            </div>
            <div>
                <label class="admin-label">Min. kişi</label>
                <input type="number" name="min_persons" value="{{ old('min_persons', $package->min_persons ?? 10) }}" class="admin-input">
            </div>
            <div>
                <label class="admin-label">Maks. kişi</label>
                <input type="number" name="max_persons" value="{{ old('max_persons', $package->max_persons ?? '') }}" class="admin-input">
            </div>
            <div>
                <label class="admin-label">Buton metni</label>
                <input name="cta_label" value="{{ old('cta_label', $package->cta_label ?? 'Jetzt erstellen') }}" class="admin-input">
            </div>
            <div>
                <label class="admin-label">Sıra</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $package->sort_order ?? 0) }}" class="admin-input">
            </div>
            <div class="sm:col-span-2">
                <label class="admin-label">Kapak görseli</label>
                <input type="file" name="image" accept="image/*" class="admin-input">
            </div>
        </div>
        <div>
            <label class="admin-label">Açıklama</label>
            <textarea name="description" rows="4" class="admin-textarea">{{ old('description', $package->description ?? '') }}</textarea>
        </div>
        <div>
            <label class="admin-label">SEO metni</label>
            <p class="mb-2 text-xs text-stone-500">Menü sayfasının altında görünür. Buffet Mega M, Buffet Mega L ve diğer tüm paketler için ayrı ayrı yazılır. Paragrafları boş satırla ayırın.</p>
            <textarea name="seo_text" rows="10" class="admin-textarea" placeholder="Bu Menü eignet sich für Firmenfeiern in Mannheim …">{{ old('seo_text', $package->seo_text ?? '') }}</textarea>
        </div>
        <div class="grid sm:grid-cols-3 gap-3">
            @foreach (range(0, 2) as $i)
                <div>
                    <label class="admin-label">Güven satırı {{ $i+1 }}</label>
                    <input name="highlights[]" value="{{ old('highlights.'.$i, $package->highlights[$i] ?? '') }}" class="admin-input" placeholder="Frisch zubereitet">
                </div>
            @endforeach
        </div>
        <div class="flex flex-wrap gap-5 text-sm">
            <label class="flex items-center gap-2"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $package->is_active ?? true))> Aktif</label>
            <label class="flex items-center gap-2"><input type="checkbox" name="is_popular" value="1" @checked(old('is_popular', $package->is_popular ?? false))> Popüler</label>
            <label class="flex items-center gap-2"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $package->is_featured ?? false))> Öne çıkan</label>
        </div>
    </div>

    <div class="admin-card p-6 space-y-4">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-[11px] uppercase tracking-[0.2em] text-[var(--admin-sage)]">Seçim</p>
                <h2 class="mt-1 text-2xl">Yemek kotaları</h2>
                <p class="mt-1 text-sm text-[var(--admin-muted)]">Örn. 4 Vorspeisen. Müşteri o gruptan tam bu kadar yemek işaretler. Yemekler “Yemekler” sayfasından eklenir.</p>
            </div>
            <button type="button" class="admin-btn-ghost admin-btn shrink-0" @click="add()">+ Kota</button>
        </div>
        <template x-for="(slot, index) in slots" :key="index">
            <div class="grid sm:grid-cols-12 gap-3 items-end rounded-2xl border border-[#efe6d8] bg-[#faf6f0] p-4">
                <input type="hidden" :name="'slots['+index+'][id]'" :value="slot.id || ''">
                <div class="sm:col-span-4">
                    <label class="admin-label">Görünen ad</label>
                    <input :name="'slots['+index+'][name]'" x-model="slot.name" class="admin-input" placeholder="Vorspeisen">
                </div>
                <div class="sm:col-span-4">
                    <label class="admin-label">Yemek grubu</label>
                    <select :name="'slots['+index+'][product_category_id]'" x-model="slot.product_category_id" class="admin-select">
                        <option value="">—</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->parent?->name ? $cat->parent->name.' · ' : '' }}{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="admin-label">Adet</label>
                    <input type="number" min="1" :name="'slots['+index+'][required_count]'" x-model="slot.required_count" class="admin-input">
                </div>
                <div class="sm:col-span-2">
                    <button type="button" class="admin-btn-ghost admin-btn w-full text-red-700" @click="remove(index)">Sil</button>
                </div>
            </div>
        </template>
        <p class="text-sm text-[var(--admin-muted)]" x-show="slots.length === 0">Henüz kota yok. Müşteri bu pakette yemek seçemez.</p>
    </div>

    @include('admin.partials.seo-geo', ['schemaJson' => $package?->seo?->schema_json])

    <button class="admin-btn admin-btn-gold px-8">Kaydet</button>
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('slotEditor', (initial) => ({
        slots: (initial || []).map((s) => ({
            id: s.id,
            name: s.name,
            product_category_id: s.product_category_id ? String(s.product_category_id) : '',
            required_count: s.required_count || 1,
        })),
        add() {
            this.slots.push({ id: '', name: '', product_category_id: '', required_count: 1 });
        },
        remove(i) {
            this.slots.splice(i, 1);
        },
    }));
});
</script>
@endpush
