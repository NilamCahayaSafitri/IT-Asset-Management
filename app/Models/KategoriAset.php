<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KategoriAset extends Model
{
    protected $table = 'kategori_aset';

    protected $fillable = [
        'nama_kategori',
    ];

    public function aset()
    {
        return $this->hasMany(Aset::class, 'id_kategori');
    }

    public function asetMasukDetail()
    {
        return $this->hasMany(AsetMasukDetail::class, 'id_kategori');
    }
}
