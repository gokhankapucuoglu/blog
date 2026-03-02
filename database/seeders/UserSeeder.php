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

        $admin1 = User::firstOrCreate(
            ['email' => 'admin@blog.com'],
            [
                'name' => 'Admin',
                'surname' => 'User',
                'username' => 'admin_user',
                'password' => 'GokhaN2635!',
                'email_verified_at' => now(),
                'status' => true,
            ]
        );

        $admin1->assignRole('admin');

        $admin2 = User::firstOrCreate(
            ['email' => 'admin2@blog.com'],
            [
                'name' => 'Admin2',
                'surname' => 'User',
                'username' => 'admin2_user',
                'password' => 'GokhaN2635!',
                'email_verified_at' => now(),
                'status' => true,
            ]
        );

        $admin2->assignRole('admin');

        $author = User::firstOrCreate(
            ['email' => 'author@blog.com'],
            [
                'name' => 'Author1',
                'surname' => 'User',
                'username' => 'author1_user',
                'password' => 'GokhaN2635!',
                'email_verified_at' => now(),
                'status' => true,
            ]
        );

        $author->assignRole('author');

        $author2 = User::firstOrCreate(
            ['email' => 'author2@blog.com'],
            [
                'name' => 'Author2',
                'surname' => 'User',
                'username' => 'author2_user',
                'password' => 'GokhaN2635!',
                'email_verified_at' => now(),
                'status' => true,
            ]
        );

        $author2->assignRole('author');
    }
}
