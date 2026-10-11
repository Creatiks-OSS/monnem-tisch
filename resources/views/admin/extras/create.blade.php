@extends('admin.layouts.app')
@section('title', 'Ekstra oluştur')
@section('heading', 'Ekstra oluştur')
@section('content')
@php
    $extra = new \App\Models\Extra(['is_active' => true, 'kind' => 'service', 'price_type' => 'fixed']);
@endphp
<form method="POST" action="{{ route('admin.extras.store') }}" enctype="multipart/form-data" class="max-w-2xl space-y-4 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
    @csrf
    @include('admin.extras.form')
    <button class="px-4 py-2 rounded-lg bg-[var(--admin-sidebar)] text-white text-sm">Kaydet</button>
</form>
@endsection
