@if(session('success'))
    <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-800 px-4 py-3 text-sm" x-data x-init="setTimeout(() => $el.remove(), 5000)">
        {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 text-red-800 px-4 py-3 text-sm">
        {{ session('error') }}
    </div>
@endif