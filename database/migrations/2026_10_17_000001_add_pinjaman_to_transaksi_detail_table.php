<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksi_detail', function (Blueprint $table) {
            // Hanya relevan untuk baris Deploy.
            // Peminjaman sementara: surat tetap memakai SATU tanggal (hari pinjam);
            // pengembalian dibuat lewat surat Return baru pada hari pengembalian.
            $table->boolean('is_pinjaman')->default(false)->after('asset_function_lain');

            // Batas kembali hanya untuk pengingat di sistem (tidak dicetak di surat)
            $table->date('batas_kembali')->nullable()->after('is_pinjaman');
        });
    }

    public function down(): void
    {
        Schema::table('transaksi_detail', function (Blueprint $table) {
            $table->dropColumn(['is_pinjaman', 'batas_kembali']);
        });
    }
};
