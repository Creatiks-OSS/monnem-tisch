@extends('admin.layouts.app')
@section('title','Dolu günler')
@section('heading','Dolu günler')
@section('content')
<div class="grid lg:grid-cols-3 gap-6">
<form method="POST" action="{{ route('admin.blocked-dates.store') }}" class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm space-y-3">@csrf
<label class="block text-sm font-medium">Tarihi kapat</label>
<input type="date" name="date" required class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm">
<select name="type" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm">
    <option value="blocked">Kapalı</option>
    <option value="booked">Rezerve</option>
    <option value="holiday">Tatil</option>
</select>
<input type="text" name="note" placeholder="Not" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm">
<button class="w-full rounded-lg bg-[var(--admin-sidebar)] text-white py-2 text-sm">Kaydet</button>
</form>
<div class="lg:col-span-2 overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
<table class="min-w-full text-sm">
<thead class="bg-stone-50 text-xs uppercase text-stone-500 text-left">
<tr>
    <th class="px-4 py-3">Tarih</th>
    <th class="px-4 py-3">Tür</th>
    <th class="px-4 py-3">Not</th>
    <th class="px-4 py-3"></th>
</tr>
</thead>
<tbody class="divide-y divide-stone-100">
@forelse($all as $d)
<tr>
    <td class="px-4 py-3">{{ $d->date->format('d.m.Y') }}</td>
    <td class="px-4 py-3">{{ $d->typeLabel() }}</td>
    <td class="px-4 py-3">{{ $d->note }}</td>
    <td class="px-4 py-3 text-right">
        <form method="POST" action="{{ route('admin.blocked-dates.destroy', $d) }}" onsubmit="return confirm('Bu tarih açılsın mı?')">
            @csrf
            @method('DELETE')
            <button class="text-red-600">Sil</button>
        </form>
    </td>
</tr>
@empty
<tr><td colspan="4" class="px-4 py-12 text-center text-stone-500">Dolu gün yok.</td></tr>
@endforelse
</tbody>
</table>
</div>
</div>
<div class="mt-4">{{ $all->links() }}</div>
@endsection
