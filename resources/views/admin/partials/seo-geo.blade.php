@php
    $schemaValue = old($name ?? 'schema_json', $schemaJson ?? '');
@endphp
<section class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm space-y-2">
    <h2 class="text-base font-semibold">SEO / GEO ayarları</h2>
    <p class="text-xs text-stone-500">Bu içeriğe özel JSON-LD. Boş bırakılırsa sayfa kendi şemasını kullanır. Organization ve WebSite yalnızca anasayfada yer alır.</p>
    <label class="mb-1 block text-sm font-medium" for="{{ $name ?? 'schema_json' }}">JSON-LD</label>
    <textarea
        id="{{ $name ?? 'schema_json' }}"
        name="{{ $name ?? 'schema_json' }}"
        rows="8"
        class="w-full rounded-xl border border-stone-200 px-3 py-2 font-mono text-xs leading-relaxed"
        placeholder='{"@type":"FAQPage","mainEntity":[]}'
    >{{ $schemaValue }}</textarea>
</section>
