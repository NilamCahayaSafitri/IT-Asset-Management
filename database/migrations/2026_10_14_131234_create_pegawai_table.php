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
        Schema::create('pegawai', function (Blueprint $table) {
            $table->id();
            $table->string('kode_pegawai', 30)->unique(); // "User ID" di form, contoh EMP-00231
            $table->string('nama');
            $table->string('jabatan')->nullable();
            $table->foreignId('id_departemen')->constrained('departemen');
            $table->string('email')->nullable();
            $table->string('kontak', 30)->nullable();     // "Phone No" di form
            $table->string('room_no', 50)->nullable();    // "Room No" di form
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pegawai');
    }
};
