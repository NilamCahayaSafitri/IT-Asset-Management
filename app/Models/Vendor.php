<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    protected $table = 'vendor';

    protected $fillable = [
        'nama_vendor',
        'alamat',
        'kontak',
        'email',
    ];

    public function asetMasuk()
    {
        return $this->hasMany(AsetMasuk::class, 'id_vendor');
    }
}
