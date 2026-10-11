@extends('admin.layouts.app')
@section('title','SEO düzenle')
@section('heading','SEO: '.( $model->title ?? $model->name ))
@section('content')
<form method="POST" action="{{ route('admin.seo.update') }}" class="max-w-2xl space-y-4 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">@csrf @method('PUT')
<input type="hidden" name="type" value="{{ $type }}"><input type="hidden" name="id" value="{{ $model->id }}">
@foreach([['title','Başlık'],['description','Açıklama'],['keywords','Keywords'],['canonical','Canonical'],['og_title','OG Başlık'],['og_description','OG Açıklama'],['og_image','OG Image'],['focus_keyword','Focus Keyword'],['robots','Robots']] as [$k,$l])
<div><label class="block text-sm mb-1">{{ $l }}</label><input name="{{ $k }}" value="{{ old($k, $seo->$k ?? '') }}" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm"></div>
@endforeach
@include('admin.partials.seo-geo', ['schemaJson' => $seo->schema_json ?? ''])
<button class="px-4 py-2 rounded-lg bg-[var(--admin-sidebar)] text-white text-sm">Kaydet</button>
</form>
@endsection