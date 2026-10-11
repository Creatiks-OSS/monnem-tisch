@extends('admin.layouts.app')
@section('title','E-posta şablonu')
@section('heading',$template->name)
@section('content')
<form method="POST" action="{{ route('admin.email-templates.update',$template) }}" class="max-w-3xl space-y-4 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">@csrf @method('PUT')
<div><label class="block text-sm mb-1">Ad</label><input name="name" value="{{ old('name',$template->name) }}" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm"></div>
<div><label class="block text-sm mb-1">Betreff</label><input name="subject" value="{{ old('subject',$template->subject) }}" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm"></div>
<div><label class="block text-sm mb-1">HTML Body</label><textarea name="body_html" rows="12" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm font-mono">{{ old('body_html',$template->body_html) }}</textarea></div>
<div><label class="block text-sm mb-1">Text Body</label><textarea name="body_text" rows="6" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm">{{ old('body_text',$template->body_text) }}</textarea></div>
<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active',$template->is_active))> Aktif</label>
<button class="px-4 py-2 rounded-lg bg-[var(--admin-sidebar)] text-white text-sm">Kaydet</button>
</form>
@endsection