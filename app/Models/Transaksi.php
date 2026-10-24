<?php

namespace App\Models;

use App\Enums\AssetFunction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Transaksi extends Model
{
    protected $table = 'transaksi';

    public const TYPES = ['Deploy', 'Return', 'Replace/Swap', 'Move'];

    protected $fillable = [
        'no_referensi',
        'tanggal_transaksi',
        'is_deploy',
        'is_return',
        'is_replace_swap',
        'is_move',
        'id_pegawai',
        'cc',
        'room_no',
        'po_contract_no',
        'afe_cc',
        'tahun',
        'alasan_return',
        'catatan',
        'status_dokumen',
        'id_users',
    ];

    protected $casts = [
        'tanggal_transaksi' => 'date',
        'is_deploy' => 'boolean',
        'is_return' => 'boolean',
        'is_replace_swap' => 'boolean',
        'is_move' => 'boolean',
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'id_pegawai');
    }

    // admin yang memproses transaksi
    public function admin()
    {
        return $this->belongsTo(User::class, 'id_users');
    }

    public function detail()
    {
        return $this->hasMany(TransaksiDetail::class, 'id_transaksi');
    }

    // bagian "Deploy / Move Equipment" di surat
    public function detailDeploy()
    {
        return $this->hasMany(TransaksiDetail::class, 'id_transaksi')->where('bagian', 'deploy');
    }

    // bagian "Return Equipment" di surat
    public function detailReturn()
    {
        return $this->hasMany(TransaksiDetail::class, 'id_transaksi')->where('bagian', 'return');
    }

    /** Nomor referensi berikutnya: EAF-2026/ICT/0001, nomor urut per tahun */
    public static function nextReferensi(?Carbon $tanggal = null): string
    {
        $prefix = 'EAF-'.($tanggal ?? now())->format('Y').'/ICT/';

        $max = static::query()
            ->where('no_referensi', 'like', $prefix.'%')
            ->pluck('no_referensi')
            ->map(fn ($no) => (int) substr($no, strlen($prefix)))
            ->max() ?? 0;

        return $prefix.str_pad((string) ($max + 1), 4, '0', STR_PAD_LEFT);
    }

    /** Type of Allocation yang dicentang, mis. ['Deploy', 'Return'] */
    public function types(): array
    {
        return array_values(array_filter([
            $this->is_deploy ? 'Deploy' : null,
            $this->is_return ? 'Return' : null,
            $this->is_replace_swap ? 'Replace/Swap' : null,
            $this->is_move ? 'Move' : null,
        ]));
    }

    /** 24 Agustus 2026 (nama bulan Indonesia lengkap) */
    public static function tanggalIndo(Carbon $tanggal): string
    {
        $bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        return $tanggal->day.' '.$bulan[$tanggal->month - 1].' '.$tanggal->year;
    }

    /** Data untuk mencetak surat (struktur sama dengan prototype: collectAlloc) */
    public function suratData(): array
    {
        $this->loadMissing(['pegawai.departemen', 'admin', 'detail.aset']);

        $baris = fn (TransaksiDetail $d) => [
            'name'  => $d->aset?->nama_aset,
            'brand' => $d->aset?->merk,
            'model' => $d->aset?->model,
            'sn'    => $d->aset?->serial_number,
            'mem'   => $d->aset?->memory,
            'hdd'   => $d->aset?->hard_disk,
            'code'  => $d->aset?->kode_aset,
        ];

        $detail = $this->detail->sortBy('id');

        $deploy = $detail->where('bagian', 'deploy')->map(function (TransaksiDetail $d) use ($baris) {
            $fn = $d->asset_function?->value;
            if ($d->asset_function === AssetFunction::Other && filled($d->asset_function_lain)) {
                $fn = 'Other: '.$d->asset_function_lain;
            }

            return $baris($d) + ['fn' => $fn];
        })->values()->all();

        $return = $detail->where('bagian', 'return')->map(
            fn (TransaksiDetail $d) => $baris($d) + ['status' => $d->status_return?->value]
        )->values()->all();

        return [
            'ref'      => $this->no_referensi,
            'types'    => $this->types(),
            'userName' => $this->pegawai?->nama,
            'userId'   => $this->pegawai?->kode_pegawai,
            'cc'       => $this->cc,
            'dept'     => $this->pegawai?->departemen?->nama_departemen,
            'phone'    => $this->pegawai?->kontak,
            'room'     => $this->room_no,
            'po'       => $this->po_contract_no,
            'afe'      => $this->afe_cc,
            'year'     => $this->tahun,
            'deploy'   => $deploy,
            'ret'      => $return,
            'reason'   => $this->alasan_return,
            'remarks'  => $this->catatan,
            'pgeName'  => $this->admin?->name,
            'pgeDate'  => self::tanggalIndo($this->tanggal_transaksi),
        ];
    }
}
