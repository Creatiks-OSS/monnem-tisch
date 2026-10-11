@extends('admin.layouts.app')
@section('title','SSS düzenle')
@section('heading','SSS düzenle')
@section('content')
<form method="POST" action="{{ route('admin.faqs.update', $faq) }}" class="max-w-2xl space-y-4 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">@csrf @method('PUT')
@include('admin.partials.errors')
<div><label class="block text-sm mb-1">Kategori</label><input name="category" value="{{ old('category', $faq->category ?? '') }}" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm"></div>
<div><label class="block text-sm mb-1">Frage</label><input name="question" value="{{ old('question', $faq->question ?? '') }}" required class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm"></div>
<div><label class="block text-sm mb-1">Antwort</label><textarea name="answer" rows="5" required class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm">{{ old('answer', $faq->answer ?? '') }}</textarea></div>
<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $faq->is_active ?? true))> Aktif</label>
<button class="px-4 py-2 rounded-lg bg-[var(--admin-sidebar)] text-white text-sm">Kaydet</button></form>
@endsection