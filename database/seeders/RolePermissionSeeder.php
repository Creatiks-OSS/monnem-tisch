<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $modules = [
            'dashboard' => ['view'],
            'products' => ['view', 'create', 'update', 'delete'],
            'categories' => ['view', 'create', 'update', 'delete'],
            'orders' => ['view', 'create', 'update', 'delete'],
            'quotes' => ['view', 'create', 'update', 'delete'],
            'customers' => ['view', 'create', 'update', 'delete'],
            'extras' => ['view', 'create', 'update', 'delete'],
            'packages' => ['view', 'create', 'update', 'delete'],
            'events' => ['view', 'create', 'update', 'delete'],
            'reviews' => ['view', 'update', 'delete'],
            'galleries' => ['view', 'create', 'update', 'delete'],
            'blog' => ['view', 'create', 'update', 'delete'],
            'pages' => ['view', 'create', 'update', 'delete'],
            'faqs' => ['view', 'create', 'update', 'delete'],
            'delivery-zones' => ['view', 'create', 'update', 'delete'],
            'cities' => ['view', 'create', 'update', 'delete'],
            'coupons' => ['view', 'create', 'update', 'delete'],
            'media' => ['view', 'create', 'delete'],
            'settings' => ['view', 'update'],
            'seo' => ['view', 'update'],
            'email-templates' => ['view', 'update'],
            'users' => ['view', 'create', 'update', 'delete'],
            'roles' => ['view', 'update'],
            'activity-logs' => ['view'],
            'contact-messages' => ['view', 'update', 'delete'],
            'newsletter' => ['view', 'export', 'delete'],
            'business-hours' => ['view', 'update'],
            'blocked-dates' => ['view', 'create', 'update', 'delete'],
            'invoices' => ['view', 'create', 'update'],
            'notifications' => ['view', 'update'],
        ];

        $allPermissions = [];
        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                $name = "{$module}.{$action}";
                Permission::findOrCreate($name, 'web');
                $allPermissions[] = $name;
            }
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $roles = [
            'Super Admin' => $allPermissions,
            'Admin' => $allPermissions,
            'Manager' => array_values(array_filter(
                $allPermissions,
                fn ($p) => ! str_starts_with($p, 'settings.')
                    && ! str_starts_with($p, 'users.')
                    && ! str_starts_with($p, 'roles.')
            )),
            'Editor' => array_values(array_filter(
                $allPermissions,
                fn ($p) => preg_match('/^(products|categories|extras|packages|events|galleries|blog|pages|faqs|media|seo|reviews|quotes)\./', $p)
            )),
            'Sales' => array_values(array_filter(
                $allPermissions,
                fn ($p) => preg_match('/^(dashboard|orders|quotes|customers|coupons|invoices|notifications|contact-messages|newsletter)\./', $p)
            )),
            'Kitchen' => ['orders.view', 'orders.update'],
        ];

        foreach ($roles as $roleName => $perms) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions(
                Permission::query()->where('guard_name', 'web')->whereIn('name', $perms)->get()
            );
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
