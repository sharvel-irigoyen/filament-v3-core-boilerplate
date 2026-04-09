<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use BezhanSalleh\FilamentShield\Support\Utils;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Manually create super_admin role to avoid interactive prompt
        $superAdminName = Utils::getSuperAdminName() ?? 'super_admin';
        $role = Role::firstOrCreate(['name' => $superAdminName, 'guard_name' => 'web']);

        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        if (! $user->hasRole($role)) {
            $user->assignRole($role);
        }
    }
}
