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
        Schema::create('aset_masuk', function (Blueprint $table) {
            $table->id();
            $table->string('no_aset_masuk', 30)->unique(); // AM-0141
            $table->date('tanggal_masuk');
            $table->foreignId('id_vendor')->constrained('vendor');
            $table->string('no_po', 50)->nullable();
            $table->decimal('total_nilai', 15, 2)->default(0);
            $table->foreignId('id_users')->constrained('users'); // admin yang mencatat
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aset_masuk');
    }
};
