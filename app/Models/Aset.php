<?php

namespace App\Models;

use App\Enums\StatusAset;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Aset extends Model
{
    protected $table = 'aset';

    protected $fillable = [
        'kode_aset',
        'nama_aset',
        'id_kategori',
        'merk',
        'model',
        'serial_number',
        'memory',
        'hard_disk',
        'tanggal_perolehan',
        'harga',
        'umur',
        'status',
        'id_aset_masuk_detail',
        'id_ruangan',
    ];

    protected $casts = [
        'tanggal_perolehan' => 'date',
        'harga' => 'decimal:2',
        'status' => StatusAset::class,
    ];

    public function kategori()
    {
        return $this->belongsTo(KategoriAset::class, 'id_kategori');
    }

    public function ruangan()
    {
        return $this->belongsTo(Ruangan::class, 'id_ruangan');
    }

    public function asetMasukDetail()
    {
        return $this->belongsTo(AsetMasukDetail::class, 'id_aset_masuk_detail');
    }

    // riwayat aset ini di surat/transaksi (deploy, return, dst)
    public function transaksiDetail()
    {
        return $this->hasMany(TransaksiDetail::class, 'id_aset');
    }

    /** Angka urut berikutnya untuk kode aset (ITM-0001, ITM-0002, ...) */
    public static function nextNumber(): int
    {
        $max = static::query()
            ->where('kode_aset', 'like', 'ITM-%')
            ->pluck('kode_aset')
            ->map(fn ($kode) => (int) substr($kode, 4))
            ->max() ?? 0;

        return $max + 1;
    }

    public static function nextKode(): string
    {
        return sprintf('ITM-%04d', static::nextNumber());
    }

    /** Filter daftar aset: q (nama/kode/serial), kategori, ruangan, status */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['q'] ?? null, function ($q, $s) {
                $like = '%'.$s.'%';
                $q->where(fn ($w) => $w->where('nama_aset', 'like', $like)
                    ->orWhere('kode_aset', 'like', $like)
                    ->orWhere('serial_number', 'like', $like));
            })
            ->when($filters['kategori'] ?? null, fn ($q, $v) => $q->where('id_kategori', $v))
            ->when($filters['ruangan'] ?? null, fn ($q, $v) => $q->where('id_ruangan', $v))
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v));
    }
}
