<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aset_masuk', function (Blueprint $table) {
            // Procurement Information di surat EAF: PO/Contract No (sudah ada: no_po), AFE/CC, dan Year.
            // Diisi sekali di Aset Masuk, lalu terisi otomatis di surat Transaksi.
            $table->string('afe_cc', 50)->nullable()->after('no_po');
            $table->year('tahun')->nullable()->after('afe_cc');
        });

        // Data lama: Year diisi dari tahun tanggal masuk
        DB::table('aset_masuk')->select('id', 'tanggal_masuk')->orderBy('id')->get()->each(function ($r) {
            DB::table('aset_masuk')->where('id', $r->id)->update(['tahun' => (int) substr((string) $r->tanggal_masuk, 0, 4)]);
        });
    }

    public function down(): void
    {
        Schema::table('aset_masuk', function (Blueprint $table) {
            $table->dropColumn(['afe_cc', 'tahun']);
        });
    }
};
