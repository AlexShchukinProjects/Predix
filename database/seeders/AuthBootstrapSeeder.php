<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AuthBootstrapSeeder extends Seeder
{
    /**
     * Минимальные роли и учётная запись для первого входа (смените пароль после входа).
     */
    public function run(): void
    {
        $superAdmin = Role::query()->firstOrCreate(
            ['name' => Role::SUPER_ADMIN_NAME],
            ['slug' => 'super-admin', 'sort_order' => 0],
        );

        $user = User::query()->firstOrCreate(
            ['login' => 'Admin'],
            [
                'name'     => 'Administrator',
                'email'    => 'admin@example.local',
                'password' => Hash::make('password'),
                'status'   => 'active',
            ],
        );

        if (! $user->roles()->where('roles.id', $superAdmin->id)->exists()) {
            $user->roles()->attach($superAdmin->id);
        }
    }
}
