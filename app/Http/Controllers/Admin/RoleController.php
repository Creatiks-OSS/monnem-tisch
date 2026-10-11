<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct(protected ActivityLogService $activity) {}

    public function index(): View
    {
        $roles = Role::query()->withCount('permissions')->orderBy('name')->get();

        return view('admin.roles.index', compact('roles'));
    }

    public function edit(Role $role): View
    {
        $permissions = Permission::query()->orderBy('name')->get()->groupBy(function ($p) {
            return explode('.', $p->name)[0] ?? 'other';
        });
        $role->load('permissions');

        return view('admin.roles.edit', compact('role', 'permissions'));
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        if ($role->name === 'Super Admin') {
            return back()->with('error', 'Süper Yönetici yetkileri değiştirilemez.');
        }

        $data = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role->syncPermissions($data['permissions'] ?? []);
        $this->activity->log('Rolle güncellendi', $role);

        return redirect()->route('admin.roles.index')->with('success', 'Rol kaydedildi.');
    }
}