@extends('admin.layouts.app')
@section('title','Sayfa oluştur')
@section('heading','Sayfa oluştur')
@section('content')
@php $page = new \App\Models\Page(); $page->setRelation("sections", collect()); @endphp
<form method="POST" action="{{ route('admin.pages.store') }}" enctype="multipart/form-data" class="max-w-3xl space-y-4 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">@csrf
@include('admin.partials.errors')
<div class="grid sm:grid-cols-2 gap-4">
<div class="sm:col-span-2"><label class="block text-sm mb-1">Başlık</label><input name="title" value="{{ old('title', $page->title ?? '') }}" required class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm"></div>
<div><label class="block text-sm mb-1">Slug</label><input name="slug" value="{{ old('slug', $page->slug ?? '') }}" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm"></div>
<div><label class="block text-sm mb-1">Typ</label><input name="type" value="{{ old('type', $page->type ?? 'page') }}" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm"></div>
<div><label class="block text-sm mb-1">Üst öğe</label><select name="parent_id" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm"><option value="">—</option>@foreach($parents as $par)<option value="{{ $par->id }}" @selected(old('parent_id', $page->parent_id ?? '')==$par->id)>{{ $par->title }}</option>@endforeach</select></div>
<div><label class="block text-sm mb-1">Bild</label><input type="file" name="featured_image" accept="image/*" class="w-full text-sm"></div>
</div>
<div><label class="block text-sm mb-1">Excerpt</label><textarea name="excerpt" rows="2" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm">{{ old('excerpt', $page->excerpt ?? '') }}</textarea></div>
<div><label class="block text-sm mb-1">Inhalt</label><textarea name="content" rows="8" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm">{{ old('content', $page->content ?? '') }}</textarea></div>
<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_published" value="1" @checked(old('is_published', $page->is_published ?? false))> Yayında</label>
<div x-data="{ sections: {{ json_encode(old('sections', isset($page) ? $page->sections->map(fn($s)=>['id'=>$s->id,'type'=>$s->type,'title'=>$s->title,'subtitle'=>$s->subtitle,'content'=>$s->content,'is_active'=>$s->is_active])->values() : [['type'=>'text','title'=>'','subtitle'=>'','content'=>'','is_active'=>true]])) }} }">
<h3 class="font-medium mb-2">Sektionen</h3>
<template x-for="(section, i) in sections" :key="i">
<div class="border border-stone-200 rounded-xl p-3 mb-2 space-y-2">
<input type="hidden" :name="'sections['+i+'][id]'" :value="section.id || ''">
<input :name="'sections['+i+'][type]'" x-model="section.type" placeholder="Typ" class="w-full rounded-lg border border-stone-200 px-3 py-2 text-sm">
<input :name="'sections['+i+'][title]'" x-model="section.title" placeholder="Başlık" class="w-full rounded-lg border border-stone-200 px-3 py-2 text-sm">
<textarea :name="'sections['+i+'][content]'" x-model="section.content" rows="2" placeholder="Inhalt" class="w-full rounded-lg border border-stone-200 px-3 py-2 text-sm"></textarea>
<label class="flex items-center gap-2 text-sm"><input type="checkbox" :name="'sections['+i+'][is_active]'" value="1" x-model="section.is_active"> Aktif</label>
</div>
</template>
<button type="button" class="text-sm text-[#c4a35a]" @click="sections.push({type:'text',title:'',content:'',is_active:true})">+ Sektion</button>
</div>
@include('admin.partials.seo-geo')
<button class="px-4 py-2 rounded-lg bg-[var(--admin-sidebar)] text-white text-sm">Kaydet</button></form>
@endsection