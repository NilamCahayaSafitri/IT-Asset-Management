<?php

namespace App\Http\Controllers;

use App\Enums\StatusAset;
use App\Http\Requests\AsetMasukRequest;
use App\Models\Aset;
use App\Models\AsetMasuk;
use App\Models\KategoriAset;
use App\Models\TransaksiDetail;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AsetMasukController extends Controller
{
    public function index(Request $request)
    {
        $asetMasuk = AsetMasuk::query()
            ->with(['vendor', 'admin', 'detail.kategori', 'detail.aset:id,id_aset_masuk_detail,kode_aset'])
            ->withSum('detail as total_unit', 'jumlah')
            ->when($request->filled('q'), function ($query) use ($request) {
                $like = '%'.$request->q.'%';
                $query->where(fn ($w) => $w->where('no_aset_masuk', 'like', $like)
                    ->orWhere('no_po', 'like', $like)
                    ->orWhereHas('vendor', fn ($v) => $v->where('nama_vendor', 'like', $like)));
            })
            ->orderByDesc('tanggal_masuk')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        // id unit aset (di halaman ini) yang sudah punya riwayat transaksi: penerimaan terkait tidak boleh dihapus
        $unitIds = $asetMasuk->getCollection()->flatMap(fn ($m) => $m->detail->flatMap(fn ($d) => $d->aset->pluck('id')));
        $asetTerpakai = TransaksiDetail::whereIn('id_aset', $unitIds)->pluck('id_aset')->unique()->values();

        return view('aset-masuk.index', [
            'asetMasuk'       => $asetMasuk,
            'asetTerpakai'    => $asetTerpakai,
            'nextNomor'       => AsetMasuk::nextNomor(),
            'vendorOptions'   => Vendor::orderBy('nama_vendor')->pluck('nama_vendor', 'id'),
            'kategoriOptions' => KategoriAset::orderBy('nama_kategori')->pluck('nama_kategori', 'id'),
        ]);
    }

    /**
     * Simpan penerimaan aset. Setiap baris rincian dengan jumlah N otomatis
     * dipecah menjadi N unit aset terpisah di Data Aset (serial number dikosongkan,
     * diisi belakangan lewat Edit di Data Aset). Satu penerimaan boleh berisi
     * banyak baris dengan kategori berbeda.
     */
    public function store(AsetMasukRequest $request)
    {
        $data = $request->validated();

        [$masuk, $jumlahUnit, $kodePertama, $kodeTerakhir] = DB::transaction(function () use ($data) {
            $items = $data['items'];

            $total = round(collect($items)->sum(fn ($r) => (int) $r['jumlah'] * (float) $r['harga_satuan']), 2);

            $masuk = AsetMasuk::create([
                'no_aset_masuk' => AsetMasuk::nextNomor(),
                'tanggal_masuk' => $data['tanggal_masuk'],
                'id_vendor'     => $data['id_vendor'],
                'no_po'         => $data['no_po'] ?? null,
                'afe_cc'        => $data['afe_cc'] ?? null,
                'tahun'         => $data['tahun'] ?? null,
                'total_nilai'   => $total,
                'id_users'      => auth()->id(),
            ]);

            $nomor = Aset::nextNumber();
            $now = now();
            $unit = [];

            foreach ($items as $r) {
                $jumlah = (int) $r['jumlah'];
                $harga = (float) $r['harga_satuan'];

                $detail = $masuk->detail()->create([
                    'id_kategori'  => $r['id_kategori'],
                    'nama_aset'    => $r['nama_aset'],
                    'merk'         => $r['merk'] ?? null,
                    'model'        => $r['model'] ?? null,
                    'memory'       => $r['memory'] ?? null,
                    'hard_disk'    => $r['hard_disk'] ?? null,
                    'jumlah'       => $jumlah,
                    'harga_satuan' => $harga,
                    'total_harga'  => round($jumlah * $harga, 2),
                ]);

                for ($i = 0; $i < $jumlah; $i++) {
                    $unit[] = [
                        'kode_aset'            => sprintf('ITM-%04d', $nomor++),
                        'nama_aset'            => $r['nama_aset'],
                        'id_kategori'          => $r['id_kategori'],
                        'merk'                 => $r['merk'] ?? null,
                        'model'                => $r['model'] ?? null,
                        'serial_number'        => null,   // diisi belakangan di Data Aset
                        'memory'               => $r['memory'] ?? null,
                        'hard_disk'            => $r['hard_disk'] ?? null,
                        'tanggal_perolehan'    => $data['tanggal_masuk'],
                        'harga'                => $harga,
                        'status'               => StatusAset::Available->value,
                        'id_aset_masuk_detail' => $detail->id,
                        'id_ruangan'           => null,
                        'created_at'           => $now,
                        'updated_at'           => $now,
                    ];
                }
            }

            foreach (array_chunk($unit, 200) as $chunk) {
                Aset::insert($chunk);
            }

            return [$masuk, count($unit), $unit[0]['kode_aset'], end($unit)['kode_aset']];
        });

        $rentang = $jumlahUnit > 1 ? "{$kodePertama} s.d. {$kodeTerakhir}" : $kodePertama;

        return redirect()
            ->route('aset-masuk.index')
            ->with('success', "Aset masuk {$masuk->no_aset_masuk} disimpan. {$jumlahUnit} unit aset dibuat ({$rentang}). Lengkapi serial number lewat Edit di Data Aset.");
    }

    /**
     * Batalkan penerimaan: hapus aset masuk beserta SEMUA unit aset yang dibuat darinya.
     * Ditolak jika ada unit yang sudah punya riwayat transaksi.
     */
    public function destroy(AsetMasuk $asetMasuk)
    {
        $asetMasuk->load('detail.aset:id,id_aset_masuk_detail,kode_aset');
        $unitIds = $asetMasuk->detail->flatMap(fn ($d) => $d->aset->pluck('id'));

        if (TransaksiDetail::whereIn('id_aset', $unitIds)->exists()) {
            return back()->with('error', "Aset masuk {$asetMasuk->no_aset_masuk} tidak bisa dihapus karena sebagian unitnya sudah punya riwayat transaksi. Ubah status unit yang tidak dipakai menjadi Inactive lewat Data Aset.");
        }

        $no = $asetMasuk->no_aset_masuk;
        $jumlah = $unitIds->count();

        DB::transaction(function () use ($asetMasuk, $unitIds) {
            // aset menunjuk ke aset_masuk_detail tanpa cascade, jadi hapus unit dulu;
            // baris detail ikut terhapus bersama aset_masuk (cascade)
            Aset::whereIn('id', $unitIds)->delete();
            $asetMasuk->delete();
        });

        return redirect()->route('aset-masuk.index')
            ->with('success', "Aset masuk {$no} dihapus beserta {$jumlah} unit asetnya.");
    }
}
