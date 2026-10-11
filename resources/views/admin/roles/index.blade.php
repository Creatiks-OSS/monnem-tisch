@extends('admin.layouts.app')
@section('title','Roller')
@section('heading','Roller')
@section('content')
<div class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm"><table class="min-w-full text-sm">
<thead class="bg-stone-50 text-xs uppercase text-stone-500 text-left"><tr><th class="px-4 py-3">Rolle</th><th class="px-4 py-3">Rechte</th><th class="px-4 py-3"></th></tr></thead>
<tbody class="divide-y divide-stone-100">@foreach($roles as $role)
<tr><td class="px-4 py-3 font-medium">{{ $role->name }}</td><td class="px-4 py-3">{{ $role->permissions_count }}</td>
<td class="px-4 py-3 text-right"><a href="{{ route('admin.roles.edit',$role) }}" class="text-[#c4a35a]">Düzenle</a></td></tr>
@endforeach</tbody></table></div>
@endsection