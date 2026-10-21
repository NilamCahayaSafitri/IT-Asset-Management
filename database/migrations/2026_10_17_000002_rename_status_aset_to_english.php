<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Indonesia => Inggris. "Maintenance" dan "Surplus" sudah sama.
    private array $map = [
        'Tersedia'             => 'Available',
        'Dialokasikan'         => 'Deployed',
        'Dipinjam'             => 'Borrowed',
        'Rusak'                => 'Broken',
        'Menunggu Penghapusan' => 'Waiting for Disposal',
        'Nonaktif'             => 'Inactive',
    ];

    public function up(): void
    {
        foreach ($this->map as $lama => $baru) {
            DB::table('aset')->where('status', $lama)->update(['status' => $baru]);
        }

        Schema::table('aset', function (Blueprint $table) {
            $table->string('status', 30)->default('Available')->change();
        });
    }

    public function down(): void
    {
        foreach ($this->map as $lama => $baru) {
            DB::table('aset')->where('status', $baru)->update(['status' => $lama]);
        }

        Schema::table('aset', function (Blueprint $table) {
            $table->string('status', 30)->default('Tersedia')->change();
        });
    }
};
