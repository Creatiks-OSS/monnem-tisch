<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admins = [
            [
                'email' => 'admin@example.com',
                'name' => 'Admin',
                'first_name' => 'Admin',
                'last_name' => 'Monnemtisch',
            ],
            [
                'email' => 'admin@velora-catering.de',
                'name' => 'Super Admin',
                'first_name' => 'Super',
                'last_name' => 'Admin',
            ],
        ];

        foreach ($admins as $data) {
            $user = User::query()->updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'password' => Hash::make('password'),
                    'is_active' => true,
                    'must_change_password' => true,
                    'locale' => 'de',
                ]
            );

            if (! $user->hasRole('Super Admin')) {
                $user->assignRole('Super Admin');
            }
        }
    }
}
