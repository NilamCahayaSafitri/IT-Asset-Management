<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Urutan penting: role dulu, lalu user, lalu master data, terakhir aset.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            UserSeeder::class,
            MasterDataSeeder::class,
            AsetSeeder::class,
        ]);
    }
}
