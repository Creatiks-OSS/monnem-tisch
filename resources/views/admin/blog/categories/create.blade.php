@extends('admin.layouts.app')
@section('title','Blog kategorisi')
@section('heading','Blog kategorisi oluştur')
@section('content')
<form method="POST" action="{{ route('admin.blog-categories.store') }}" class="max-w-xl space-y-4 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">@csrf
@include('admin.partials.errors')
<div><label class="block text-sm mb-1">Ad</label><input name="name" value="{{ old('name') }}" required class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm"></div>
<div><label class="block text-sm mb-1">Açıklama</label><textarea name="description" rows="3" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm">{{ old('description') }}</textarea></div>
<button class="px-4 py-2 rounded-lg bg-[var(--admin-sidebar)] text-white text-sm">Kaydet</button>
</form>
@endsection