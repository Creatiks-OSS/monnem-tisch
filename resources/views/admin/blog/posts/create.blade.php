@extends('admin.layouts.app')
@section('title','Yazı oluştur')
@section('heading','Yazı oluştur')
@section('content')
@php $post = new \App\Models\BlogPost(); $post->setRelation("tags", collect()); @endphp
<form method="POST" action="{{ route('admin.blog-posts.store') }}" enctype="multipart/form-data" class="max-w-3xl space-y-4 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">@csrf
@include('admin.partials.errors')
<div><label class="block text-sm mb-1">Başlık</label><input name="title" value="{{ old('title', $post->title ?? '') }}" required class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm"></div>
<div><label class="block text-sm mb-1">Slug</label><input name="slug" value="{{ old('slug', $post->slug ?? '') }}" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm"></div>
<div><label class="block text-sm mb-1">Kategori</label><select name="blog_category_id" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm"><option value="">—</option>@foreach($categories as $c)<option value="{{ $c->id }}" @selected(old('blog_category_id', $post->blog_category_id ?? '')==$c->id)>{{ $c->name }}</option>@endforeach</select></div>
<div><label class="block text-sm mb-1">Excerpt</label><textarea name="excerpt" rows="2" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm">{{ old('excerpt', $post->excerpt ?? '') }}</textarea></div>
<div><x-admin.rich-editor name="content" :value="$post->content ?? ''" label="İçerik" /></div>
<div><label class="block text-sm mb-1">Yayın tarihi</label><input type="datetime-local" name="published_at" value="{{ old('published_at') }}" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm"><p class="mt-1 text-xs text-stone-500">Yeni yazıları kısa aralıklarla peş peşe yayınlamayın – 10–14 gün ara daha doğal durur.</p></div>
<div><label class="block text-sm mb-1">Bild</label><input type="file" name="featured_image" accept="image/jpeg,image/png,image/webp" class="w-full text-sm"><p class="mt-1 text-xs text-stone-500">Özgün mutfak/event fotoğrafı kullanın, stok görsel değil.</p></div>
<div><label class="block text-sm mb-1">Tags (kommagetrennt)</label><input name="tags[]" value="{{ old('tags.0', isset($post) ? $post->tags->pluck('name')->implode(',') : '') }}" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm" placeholder="Catering, Etkinlikler"></div>
<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_published" value="1" @checked(old('is_published', $post->is_published ?? false))> Yayında</label>
@include('admin.partials.seo-geo')
<div class="flex flex-wrap gap-3">
    <button class="px-4 py-2 rounded-lg bg-[var(--admin-sidebar)] text-white text-sm">Kaydet</button>
    @include('admin.blog.posts.preview-button')
</div>
</form>
@endsection