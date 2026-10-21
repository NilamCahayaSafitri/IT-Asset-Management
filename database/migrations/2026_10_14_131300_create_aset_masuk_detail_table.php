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
        Schema::create('aset_masuk_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_aset_masuk')->constrained('aset_masuk')->cascadeOnDelete();
            $table->foreignId('id_kategori')->constrained('kategori_aset');
            $table->string('nama_aset');
            $table->string('merk', 100)->nullable();
            $table->string('model', 100)->nullable();
            $table->unsignedInteger('jumlah');
            $table->decimal('harga_satuan', 15, 2);
            $table->decimal('total_harga', 15, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aset_masuk_detail');
    }
};
