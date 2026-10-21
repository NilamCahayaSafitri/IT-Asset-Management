<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aset_masuk_detail', function (Blueprint $table) {
            // Diwariskan ke setiap unit aset yang dibuat dari baris ini (kosong untuk non-laptop/PC)
            $table->string('memory', 50)->nullable()->after('model');
            $table->string('hard_disk', 50)->nullable()->after('memory');
        });
    }

    public function down(): void
    {
        Schema::table('aset_masuk_detail', function (Blueprint $table) {
            $table->dropColumn(['memory', 'hard_disk']);
        });
    }
};
