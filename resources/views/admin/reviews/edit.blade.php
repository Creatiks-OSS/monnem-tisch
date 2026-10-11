@extends('admin.layouts.app')
@section('title','Yorumu düzenle')
@section('heading','Yorumu düzenle')
@section('content')
<form method="POST" action="{{ route('admin.reviews.update',$review) }}" class="max-w-2xl space-y-4 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">@csrf @method('PUT')
@include('admin.partials.errors')
<div class="grid sm:grid-cols-2 gap-4">
<div><label class="block text-sm mb-1">Ad</label><input name="name" value="{{ old('name',$review->name) }}" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm"></div>
<div><label class="block text-sm mb-1">Firma</label><input name="company" value="{{ old('company',$review->company) }}" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm"></div>
<div><label class="block text-sm mb-1">Rating</label><input type="number" min="1" max="5" name="rating" value="{{ old('rating',$review->rating) }}" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm"></div>
<div><label class="block text-sm mb-1">Başlık</label><input name="title" value="{{ old('title',$review->title) }}" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm"></div>
</div>
<div><label class="block text-sm mb-1">Text</label><textarea name="body" rows="4" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm">{{ old('body',$review->body) }}</textarea></div>
<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_approved" value="1" @checked(old('is_approved',$review->is_approved))> Freigegeben</label>
<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured',$review->is_featured))> Öne çıkan</label>
<button class="px-4 py-2 rounded-lg bg-[var(--admin-sidebar)] text-white text-sm">Kaydet</button>
</form>
@endsection