<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $superAdmin = User::firstOrCreate(
            ['email' => 'super_admin@okulumukodluyorum.com'],
            [
                'name' => 'Super',
                'surname' => 'Admin',
                'username' => 'super_admin',
                'password' => 'GokhaN2635!',
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        $superAdmin->assignRole('super_admin');
    }
}
