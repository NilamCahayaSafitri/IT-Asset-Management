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
        Schema::create('aset', function (Blueprint $table) {
            $table->id();
            $table->string('kode_aset', 30)->unique();    // ITM-0001, tampil sebagai "Asset No." di surat
            $table->string('nama_aset');
            $table->foreignId('id_kategori')->constrained('kategori_aset');
            $table->string('merk', 100)->nullable();
            $table->string('model', 100)->nullable();
            $table->string('serial_number', 100)->nullable()->unique();
            $table->string('memory', 50)->nullable();     // kosong untuk aset non-laptop/PC
            $table->string('hard_disk', 50)->nullable();
            $table->date('tanggal_perolehan')->nullable();
            $table->decimal('harga', 15, 2)->nullable();
            $table->unsignedTinyInteger('umur')->nullable(); // umur ekonomis, dalam tahun
            $table->string('status', 30)->default('Tersedia')->index();
            $table->foreignId('id_aset_masuk_detail')->nullable()->constrained('aset_masuk_detail');
            $table->foreignId('id_ruangan')->nullable()->constrained('ruangan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aset');
    }
};
