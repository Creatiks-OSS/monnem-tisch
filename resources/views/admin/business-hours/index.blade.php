@extends('admin.layouts.app')
@section('title','Çalışma saatleri')
@section('heading','Çalışma saatleri')
@section('content')
<form method="POST" action="{{ route('admin.business-hours.update') }}" class="max-w-2xl rounded-2xl border border-stone-200 bg-white p-6 shadow-sm space-y-3">@csrf @method('PUT')
@foreach($days as $day => $label)
@php $h = $hours[$day]; @endphp
<div class="grid grid-cols-12 gap-3 items-center text-sm">
<div class="col-span-3 font-medium">{{ $label }}</div>
<label class="col-span-2 flex items-center gap-2"><input type="checkbox" name="hours[{{ $day }}][is_open]" value="1" @checked($h->is_open)> Offen</label>
<input type="time" name="hours[{{ $day }}][open_time]" value="{{ substr((string)$h->open_time,0,5) }}" class="col-span-3 rounded-lg border border-stone-200 px-2 py-1.5">
<input type="time" name="hours[{{ $day }}][close_time]" value="{{ substr((string)$h->close_time,0,5) }}" class="col-span-3 rounded-lg border border-stone-200 px-2 py-1.5">
</div>
@endforeach
<button class="mt-4 px-4 py-2 rounded-lg bg-[var(--admin-sidebar)] text-white text-sm">Kaydet</button>
</form>
@endsection