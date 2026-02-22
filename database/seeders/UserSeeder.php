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
            ['email' => 'super_admin@blog.com'],
            [
                'name' => 'Super',
                'surname' => 'Admin',
                'username' => 'super_admin',
                'password' => 'GokhaN2635!',
                'email_verified_at' => now(),
                'status' => true,
            ]
        );

        $superAdmin->assignRole('super_admin');

        $author = User::firstOrCreate(
            ['email' => 'author@blog.com'],
            [
                'name' => 'Author',
                'surname' => 'User',
                'username' => 'author_user',
                'password' => 'GokhaN2635!',
                'email_verified_at' => now(),
                'status' => true,
            ]
        );

        $author->assignRole('author');
    }
}
