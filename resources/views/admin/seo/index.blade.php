@extends('admin.layouts.app')
@section('title','SEO')
@section('heading','SEO standartları')
@section('content')
<form method="POST" action="{{ route('admin.seo.defaults') }}" class="max-w-2xl space-y-4 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">@csrf @method('PUT')
@foreach([['meta_title','Meta Başlık'],['meta_description','Meta Açıklama'],['meta_keywords','Keywords'],['og_image','OG Image URL'],['robots','Robots']] as [$k,$l])
<div><label class="block text-sm mb-1">{{ $l }}</label><input name="{{ $k }}" value="{{ old($k, $defaults[$k] ?? '') }}" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm"></div>
@endforeach
<button class="px-4 py-2 rounded-lg bg-[var(--admin-sidebar)] text-white text-sm">Kaydet</button>
</form>
@endsection