@extends('admin.layouts.app')
@section('title', 'Kategori metinleri')
@section('heading', 'Kategori metinleri')
@section('subheading', 'Menü listesinin altında görünen uzun açıklama')
@section('content')
@include('admin.partials.errors')

<p class="mb-6 max-w-3xl text-sm text-[var(--admin-muted)]">
    Bu metinler /buffets, /fingerfood ve diğer kategori sayfalarında paketlerin altında görünür. SEO için her metin en az 700 karakter olsun. Boş bırakırsanız hazır örnek metin kullanılır.
</p>

<form method="POST" action="{{ route('admin.menu-pages.update') }}" class="max-w-3xl space-y-6">
    @csrf
    @method('PUT')

    @foreach ($menus as $menu)
        @php $value = old('text.'.$menu['key'], $pages->text($menu['key'])); @endphp
        <section class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
            <div class="mb-2 flex items-center justify-between gap-3">
                <label class="text-sm font-semibold" for="text-{{ $menu['key'] }}">{{ $menu['label'] }}</label>
                <span class="text-xs text-stone-500" data-count-for="text-{{ $menu['key'] }}">{{ mb_strlen($value) }} karakter</span>
            </div>
            <textarea
                id="text-{{ $menu['key'] }}"
                name="text[{{ $menu['key'] }}]"
                rows="10"
                class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm leading-relaxed"
            >{{ $value }}</textarea>
            <div class="mt-4">
                @include('admin.partials.seo-geo', [
                    'name' => 'schema['.$menu['key'].']',
                    'schemaJson' => old('schema.'.$menu['key'], $pages->schema($menu['key'])),
                ])
            </div>
        </section>
    @endforeach

    <button class="rounded-full bg-[var(--admin-sidebar)] px-5 py-2.5 text-sm text-white">Kaydet</button>
</form>

<script>
document.querySelectorAll('textarea[id^="text-"]').forEach((field) => {
    const label = document.querySelector('[data-count-for="' + field.id + '"]');
    const refresh = () => {
        if (label) label.textContent = field.value.trim().length + ' karakter';
    };
    field.addEventListener('input', refresh);
});
</script>
@endsection
