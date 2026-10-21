<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Departemen extends Model
{
    protected $table = 'departemen';

    protected $fillable = [
        'nama_departemen',
    ];

    public function ruangan()
    {
        return $this->hasMany(Ruangan::class, 'id_departemen');
    }

    public function pegawai()
    {
        return $this->hasMany(Pegawai::class, 'id_departemen');
    }
}
