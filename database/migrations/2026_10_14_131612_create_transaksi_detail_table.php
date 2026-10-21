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
        Schema::create('transaksi_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_transaksi')->constrained('transaksi')->cascadeOnDelete();
            $table->foreignId('id_aset')->constrained('aset');
            $table->string('bagian', 10);                      // 'deploy' atau 'return'
            $table->string('asset_function', 20)->nullable();  // Primary, Shared, Training, Other
            $table->string('asset_function_lain')->nullable(); // penjelasan kalau Other
            $table->string('status_return', 30)->nullable();   // Deployable, Surplus, Waiting for Disposal, Broken
            $table->timestamps();

            $table->unique(['id_transaksi', 'id_aset', 'bagian']); // aset tidak boleh dobel di satu bagian
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_detail');
    }
};
