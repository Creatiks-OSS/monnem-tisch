@extends('admin.layouts.app')
@section('title', 'Anasayfa')
@section('heading', 'Anasayfa ayarları')
@section('content')
@include('admin.partials.errors')

<p class="mb-6 max-w-3xl text-sm text-[var(--admin-muted)]">
    Ana sayfadaki Almanca metinleri ve fotoğrafları buradan değiştirin. Bir metni boş bırakırsanız sitedeki varsayılan metin kullanılır.
    Galeri fotoğrafları için <a href="{{ route('admin.galleries.index') }}" class="underline">Galeri</a> menüsünü kullanın.
</p>

<form method="POST" action="{{ route('admin.homepage.update') }}" enctype="multipart/form-data" class="max-w-3xl space-y-6">
    @csrf
    @method('PUT')

    @foreach ($sections as $section)
        <section class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm space-y-4">
            <div>
                <h2 class="text-base font-semibold">{{ $section['title'] }}</h2>
                @if (! empty($section['hint']))
                    <p class="mt-1 text-xs text-stone-500">{{ $section['hint'] }}</p>
                @endif
            </div>

            @foreach ($section['fields'] as $field)
                <div>
                    <label class="mb-1 block text-sm font-medium" for="text-{{ $field['key'] }}">{{ $field['label'] }}</label>
                    @if ($field['type'] === 'textarea')
                        <textarea
                            id="text-{{ $field['key'] }}"
                            name="text[{{ $field['key'] }}]"
                            rows="{{ str_contains($field['key'], '_body') ? 6 : 3 }}"
                            class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm"
                        >{{ old('text.'.$field['key'], $homepage->text($field['key'])) }}</textarea>
                    @else
                        <input
                            id="text-{{ $field['key'] }}"
                            type="text"
                            name="text[{{ $field['key'] }}]"
                            value="{{ old('text.'.$field['key'], $homepage->text($field['key'])) }}"
                            class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm"
                        >
                    @endif
                </div>
            @endforeach

            @foreach ($section['images'] ?? [] as $image)
                @php
                    $current = $homepage->image($image['key'], velora_photo($image['fallback']));
                    $custom = $homepage->hasCustomImage($image['key']);
                @endphp
                <div class="rounded-xl border border-stone-100 bg-[#f7f5f1] p-3">
                    <p class="mb-2 text-sm font-medium">{{ $image['label'] }}</p>
                    <img src="{{ $current }}" alt="" class="mb-3 h-28 w-full rounded-lg object-cover">
                    <p class="mb-2 text-xs text-stone-500">{{ $custom ? 'Özel görsel yüklü.' : 'Şu an varsayılan görsel kullanılıyor.' }} JPG, PNG veya WebP, en fazla 8 MB.</p>
                    <input type="file" name="images[{{ $image['key'] }}]" accept="image/jpeg,image/png,image/webp" class="w-full text-sm">
                    @if ($custom)
                        <label class="mt-2 flex items-center gap-2 text-sm text-stone-600">
                            <input type="checkbox" name="remove_image[{{ $image['key'] }}]" value="1" class="rounded border-stone-300">
                            Özel görseli kaldır, varsayılana dön
                        </label>
                    @endif
                </div>
            @endforeach
        </section>
    @endforeach

    @include('admin.partials.seo-geo', ['schemaJson' => \App\Models\Setting::get('schema_json', '', 'homepage')])

    <div class="sticky bottom-4 flex justify-end">
        <button class="rounded-lg bg-[var(--admin-sidebar)] px-5 py-2.5 text-sm font-semibold text-white shadow-lg">Kaydet</button>
    </div>
</form>
@endsection
