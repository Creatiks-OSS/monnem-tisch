@extends('admin.layouts.app')
@section('title','İşlem kaydı')
@section('heading','İşlem kaydı')
@section('content')
<form method="GET" class="mb-6"><input type="search" name="q" value="{{ request('q') }}" placeholder="Ara…" class="rounded-xl border border-stone-200 px-3 py-2 text-sm w-64"></form>
<div class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm"><table class="min-w-full text-sm">
<thead class="bg-stone-50 text-xs uppercase text-stone-500 text-left"><tr><th class="px-4 py-3">Zeit</th><th class="px-4 py-3">Kullanıcılar</th><th class="px-4 py-3">Açıklama</th><th class="px-4 py-3">IP</th></tr></thead>
<tbody class="divide-y divide-stone-100">@forelse($logs as $log)
<tr><td class="px-4 py-3 whitespace-nowrap">{{ $log->created_at->format('d.m.Y H:i') }}</td><td class="px-4 py-3">{{ $log->user?->name ?? '—' }}</td><td class="px-4 py-3">{{ $log->description }}</td><td class="px-4 py-3">{{ $log->ip_address }}</td></tr>
@empty<tr><td colspan="4" class="px-4 py-12 text-center text-stone-500">Kayıt yok.</td></tr>@endforelse</tbody></table></div>
<div class="mt-4">{{ $logs->links() }}</div>
@endsection