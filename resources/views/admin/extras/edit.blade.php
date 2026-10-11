@extends('admin.layouts.app')
@section('title', 'Ekstrayı düzenle')
@section('heading', 'Ekstrayı düzenle')
@section('content')
<form method="POST" action="{{ route('admin.extras.update', $extra) }}" enctype="multipart/form-data" class="max-w-2xl space-y-4 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
    @csrf
    @method('PUT')
    @include('admin.extras.form')
    <div class="flex flex-wrap items-center gap-3">
        <button class="px-4 py-2 rounded-lg bg-[var(--admin-sidebar)] text-white text-sm">Kaydet</button>
        <button type="submit" form="extra-delete" class="px-4 py-2 rounded-lg border border-red-200 text-sm text-red-700">Sil</button>
    </div>
</form>
<form id="extra-delete" method="POST" action="{{ route('admin.extras.destroy', $extra) }}" onsubmit="return confirm({{ json_encode($extra->name.' silinsin mi?') }})" class="hidden">
    @csrf
    @method('DELETE')
</form>
@endsection
