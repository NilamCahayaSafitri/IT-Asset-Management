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
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'kelola-user',
            'kelola-master',
            'kelola-aset',
            'kelola-transaksi',
            'lihat-laporan',
        ];

        foreach ($permissions as $nama) {
            Permission::firstOrCreate(['name' => $nama, 'guard_name' => 'web']);
        }

        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        // Admin: semua kecuali Kelola User
        $admin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $admin->syncPermissions([
            'kelola-master',
            'kelola-aset',
            'kelola-transaksi',
            'lihat-laporan',
        ]);
    }
}
