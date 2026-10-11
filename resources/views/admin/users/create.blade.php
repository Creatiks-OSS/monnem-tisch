@extends('admin.layouts.app')
@section('title','Kullanıcı oluştur')
@section('heading','Kullanıcı oluştur')
@section('content')
@php $user = new \App\Models\User(["is_active"=>true]); $user->setRelation("roles", collect()); @endphp
<form method="POST" action="{{ route('admin.users.store') }}" class="max-w-2xl space-y-4 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">@csrf
@include('admin.partials.errors')
<div class="grid sm:grid-cols-2 gap-4">
<div><label class="block text-sm mb-1">Ad</label><input name="name" value="{{ old('name', $user->name ?? '') }}" required class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm"></div>
<div><label class="block text-sm mb-1">E-posta</label><input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" required class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm"></div>
<div><label class="block text-sm mb-1">Ad</label><input name="first_name" value="{{ old('first_name', $user->first_name ?? '') }}" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm"></div>
<div><label class="block text-sm mb-1">Soyad</label><input name="last_name" value="{{ old('last_name', $user->last_name ?? '') }}" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm"></div>
<div><label class="block text-sm mb-1">Telefon</label><input name="phone" value="{{ old('phone', $user->phone ?? '') }}" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm"></div>
<div><label class="block text-sm mb-1">Şifre</label><input type="password" name="password" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm"></div>
<div><label class="block text-sm mb-1">Şifreyi doğrula</label><input type="password" name="password_confirmation" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm"></div>
</div>
<div><label class="block text-sm mb-2">Roller</label><div class="flex flex-wrap gap-3">@foreach($roles as $role)<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="roles[]" value="{{ $role->name }}" @checked(collect(old('roles', isset($user)?$user->roles->pluck('name'):[]))->contains($role->name))> {{ $role->name }}</label>@endforeach</div></div>
<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active ?? true))> Aktif</label>
<button class="px-4 py-2 rounded-lg bg-[var(--admin-sidebar)] text-white text-sm">Kaydet</button></form>
@endsection