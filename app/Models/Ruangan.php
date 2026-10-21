<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ruangan extends Model
{
    protected $table = 'ruangan';

    protected $fillable = [
        'nama_ruangan',
        'id_departemen',
    ];

    public function departemen()
    {
        return $this->belongsTo(Departemen::class, 'id_departemen');
    }

    public function aset()
    {
        return $this->hasMany(Aset::class, 'id_ruangan');
    }
}
