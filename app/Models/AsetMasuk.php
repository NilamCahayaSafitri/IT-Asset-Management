<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsetMasuk extends Model
{
    protected $table = 'aset_masuk';

    protected $fillable = [
        'no_aset_masuk',
        'tanggal_masuk',
        'id_vendor',
        'no_po',
        'afe_cc',
        'tahun',
        'total_nilai',
        'id_users',
    ];

    protected $casts = [
        'tanggal_masuk' => 'date',
        'total_nilai' => 'decimal:2',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'id_vendor');
    }

    // admin yang mencatat penerimaan
    public function admin()
    {
        return $this->belongsTo(User::class, 'id_users');
    }

    public function detail()
    {
        return $this->hasMany(AsetMasukDetail::class, 'id_aset_masuk');
    }

    /** Nomor berikutnya: AM-0001, AM-0002, ... */
    public static function nextNomor(): string
    {
        $max = static::query()
            ->where('no_aset_masuk', 'like', 'AM-%')
            ->pluck('no_aset_masuk')
            ->map(fn ($no) => (int) substr($no, 3))
            ->max() ?? 0;

        return sprintf('AM-%04d', $max + 1);
    }
}
