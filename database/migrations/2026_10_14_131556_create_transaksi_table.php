<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transaksi', function (Blueprint $table) {
            $table->id();
            $table->string('no_referensi', 40)->unique();  // EAF-2026/ICT/0232
            $table->date('tanggal_transaksi');             // jadi Date di blok PGE-Only
            // 4 checkbox "Type of Allocation", boleh lebih dari satu
            $table->boolean('is_deploy')->default(false);
            $table->boolean('is_return')->default(false);
            $table->boolean('is_replace_swap')->default(false);
            $table->boolean('is_move')->default(false);
            $table->foreignId('id_pegawai')->constrained('pegawai');
            $table->string('cc', 50)->nullable();
            $table->string('room_no', 50)->nullable();
            // Procurement Information (khusus initial deployment)
            $table->string('po_contract_no', 50)->nullable();
            $table->string('afe_cc', 50)->nullable();
            $table->year('tahun')->nullable();
            $table->string('alasan_return')->nullable();   // "Reason" di bagian Return
            $table->text('catatan')->nullable();           // Additional Remarks
            $table->string('status_dokumen', 20)->default('Menunggu TTD');
            $table->foreignId('id_users')->constrained('users'); // admin yang memproses
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi');
    }
};
