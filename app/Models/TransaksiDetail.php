<?php

namespace App\Models;

use App\Enums\AssetFunction;
use App\Enums\StatusReturn;
use Illuminate\Database\Eloquent\Model;

class TransaksiDetail extends Model
{
    protected $table = 'transaksi_detail';

    protected $fillable = [
        'id_transaksi',
        'id_aset',
        'bagian',               // 'deploy' atau 'return'
        'asset_function',       // Primary, Shared, Training, Other
        'asset_function_lain',
        'is_pinjaman',          // true = pinjaman sementara (hanya baris deploy)
        'batas_kembali',        // pengingat di sistem, tidak dicetak di surat
        'status_return',        // Deployable, Surplus, Waiting for Disposal, Broken
    ];

    protected $casts = [
        'asset_function' => AssetFunction::class,
        'is_pinjaman'    => 'boolean',
        'batas_kembali'  => 'date',
        'status_return'  => StatusReturn::class,
    ];

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'id_transaksi');
    }

    public function aset()
    {
        return $this->belongsTo(Aset::class, 'id_aset');
    }
}
