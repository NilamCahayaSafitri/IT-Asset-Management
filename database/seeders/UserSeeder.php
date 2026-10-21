<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::updateOrCreate(
            ['username' => 'superadmin'],
            [
                'name' => 'Super Admin ICT',
                'username' => 'superadmin',
                'email' => 'superadmin@itam.test',
                'kontak' => null,
                'status' => 'aktif',
                'password' => Hash::make('password'),
            ]
        );
        $superAdmin->syncRoles('Super Admin');

        $admin = User::updateOrCreate(
            ['username' => 'admin1'],
            [
                'name' => 'Admin ICT',
                'email' => 'admin1@itam.test',
                'kontak' => null,
                'status' => 'aktif',
                'password' => Hash::make('password'),
            ]
        );
        $admin->syncRoles('Admin');
    }
}
