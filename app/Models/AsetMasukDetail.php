<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsetMasukDetail extends Model
{
    protected $table = 'aset_masuk_detail';

    protected $fillable = [
        'id_aset_masuk',
        'id_kategori',
        'nama_aset',
        'merk',
        'model',
        'memory',
        'hard_disk',
        'jumlah',
        'harga_satuan',
        'total_harga',
    ];

    protected $casts = [
        'harga_satuan' => 'decimal:2',
        'total_harga' => 'decimal:2',
    ];

    public function asetMasuk()
    {
        return $this->belongsTo(AsetMasuk::class, 'id_aset_masuk');
    }

    public function kategori()
    {
        return $this->belongsTo(KategoriAset::class, 'id_kategori');
    }

    // unit-unit aset fisik yang lahir dari baris penerimaan ini
    public function aset()
    {
        return $this->hasMany(Aset::class, 'id_aset_masuk_detail');
    }
}
