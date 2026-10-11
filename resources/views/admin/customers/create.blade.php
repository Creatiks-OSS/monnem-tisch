@extends('admin.layouts.app')
@section('title','Müşteri oluştur')
@section('heading','Müşteri oluştur')
@section('content')
@include('admin.partials.errors')
<form method="POST" action="{{ route('admin.customers.store') }}" class="max-w-2xl space-y-4 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">@csrf
<div class="grid sm:grid-cols-2 gap-4">
@foreach([['first_name','Ad'],['last_name','Soyad'],['company','Firma'],['email','E-posta'],['phone','Telefon'],['source','Kaynak']] as [$n,$l])
<div><label class="block text-sm mb-1">{{ $l }}</label><input name="{{ $n }}" value="{{ old($n) }}" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm"></div>
@endforeach
</div>
<div><label class="block text-sm mb-1">Notlar</label><textarea name="notes" rows="3" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm">{{ old('notes') }}</textarea></div>
<button class="px-4 py-2 rounded-lg bg-[var(--admin-sidebar)] text-white text-sm">Kaydet</button>
</form>
@endsection