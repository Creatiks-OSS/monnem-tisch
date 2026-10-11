@props(['headers' => []])
<div class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-stone-50 text-left text-xs uppercase tracking-wide text-stone-500">
                <tr>
                    @foreach($headers as $header)
                        <th class="px-4 py-3 font-medium whitespace-nowrap">{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                {{ $slot }}
            </tbody>
        </table>
    </div>
</div>