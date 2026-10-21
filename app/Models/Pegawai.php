<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    protected $table = 'pegawai';

    protected $fillable = [
        'kode_pegawai',   // "User ID" di form surat
        'nama',
        'jabatan',
        'id_departemen',
        'email',
        'kontak',         // "Phone No" di form surat
        'room_no',        // "Room No" di form surat
    ];

    public function departemen()
    {
        return $this->belongsTo(Departemen::class, 'id_departemen');
    }

    public function transaksi()
    {
        return $this->hasMany(Transaksi::class, 'id_pegawai');
    }
}
