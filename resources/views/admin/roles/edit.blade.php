@extends('admin.layouts.app')
@section('title','Rolü düzenle')
@section('heading','Rol: '.$role->name)
@section('content')
<form method="POST" action="{{ route('admin.roles.update',$role) }}" class="max-w-4xl rounded-2xl border border-stone-200 bg-white p-6 shadow-sm space-y-6">@csrf @method('PUT')
@foreach($permissions as $module => $perms)
<div>
<h3 class="font-medium mb-2 capitalize">{{ $module }}</h3>
<div class="flex flex-wrap gap-3">@foreach($perms as $perm)
<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="permissions[]" value="{{ $perm->name }}" @checked($role->permissions->contains('name',$perm->name))> {{ $perm->name }}</label>
@endforeach</div>
</div>
@endforeach
<button class="px-4 py-2 rounded-lg bg-[var(--admin-sidebar)] text-white text-sm">Kaydet</button>
</form>
@endsection