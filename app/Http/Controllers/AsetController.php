<?php

namespace App\Http\Controllers;

use App\Enums\StatusAset;
use App\Http\Requests\AsetRequest;
use App\Models\Aset;
use App\Models\KategoriAset;
use App\Models\Ruangan;
use Illuminate\Http\Request;

class AsetController extends Controller
{
    // Aset TIDAK dibuat di sini: unit aset lahir dari menu Aset Masuk.
    // Halaman ini untuk melihat, melengkapi (serial number, dll), dan mengubah data aset.

    public function index(Request $request)
    {
        $filters = $request->only(['q', 'kategori', 'ruangan', 'status']);

        $aset = Aset::query()
            ->with(['kategori', 'ruangan', 'asetMasukDetail.asetMasuk.vendor', 'transaksiDetail.transaksi'])
            ->filter($filters)
            ->orderBy('kode_aset')
            ->paginate(10)
            ->withQueryString();

        return view('aset.index', [
            'aset'            => $aset,
            'kategoriOptions' => KategoriAset::orderBy('nama_kategori')->pluck('nama_kategori', 'id'),
            'ruanganOptions'  => Ruangan::orderBy('nama_ruangan')->pluck('nama_ruangan', 'id'),
            'statusOptions'   => StatusAset::options(),
            'statusManual'    => StatusAset::options(StatusAset::manual()),
        ]);
    }

    /** Halaman detail satu aset: info lengkap, pemegang saat ini, dan riwayat transaksi */
    public function show(Aset $aset)
    {
        $aset->load(['kategori', 'ruangan', 'asetMasukDetail.asetMasuk.vendor']);

        $riwayat = $aset->transaksiDetail()
            ->with('transaksi.pegawai.departemen')
            ->orderByDesc('id')
            ->get();

        // pemegang saat ini = pegawai pada transaksi Deploy/Move terakhir, selama aset masih dipakai
        $pemegang = null;
        if ($aset->status?->sedangDipakai()) {
            $terakhir = $riwayat->firstWhere('bagian', 'deploy');
            if ($terakhir) {
                $pemegang = ['pegawai' => $terakhir->transaksi?->pegawai, 'transaksi' => $terakhir->transaksi, 'detail' => $terakhir];
            }
        }

        return view('aset.show', compact('aset', 'riwayat', 'pemegang'));
    }

    /**
     * Tujuan QR: /a/ITM-0001
     * Siapa pun yang memindai (login atau tidak) langsung melihat informasi umum aset, tanpa login.
     * Detail lengkap (pemegang, riwayat, dll) hanya ada di dalam aplikasi lewat menu Data Aset.
     */
    public function scan(string $kode)
    {
        $aset = Aset::with(['kategori', 'asetMasukDetail.asetMasuk.vendor'])->where('kode_aset', $kode)->first();

        abort_if(! $aset, 404, "Aset dengan kode {$kode} tidak ditemukan.");

        return view('aset.publik', compact('aset'));
    }

    public function update(AsetRequest $request, Aset $aset)
    {
        $data = $request->validated();

        // Aset yang sedang dipakai pegawai harus di-Return lewat transaksi dulu;
        // status kosong berarti "tidak diubah".
        if ($aset->status?->sedangDipakai() || empty($data['status'])) {
            unset($data['status']);
        }

        $aset->update($data);

        return redirect()
            ->route('aset.index')
            ->with('success', "Aset {$aset->kode_aset} berhasil diperbarui.");
    }

    /**
     * Hapus permanen hanya untuk aset yang belum punya riwayat transaksi
     * (mis. salah input atau dobel). Aset yang pernah dipakai tidak dihapus supaya
     * surat/transaksi lama tetap utuh; ubah statusnya menjadi Inactive.
     */
    public function destroy(Aset $aset)
    {
        if ($aset->status?->sedangDipakai()) {
            return back()->with('error', "Aset {$aset->kode_aset} sedang {$aset->status->label()}. Return dulu lewat transaksi sebelum dihapus.");
        }

        if ($aset->transaksiDetail()->exists()) {
            return back()->with('error', "Aset {$aset->kode_aset} punya riwayat transaksi, jadi tidak bisa dihapus. Ubah statusnya menjadi Inactive jika sudah tidak dipakai.");
        }

        $kode = $aset->kode_aset;
        $aset->delete();

        return redirect()->route('aset.index')->with('success', "Aset {$kode} berhasil dihapus.");
    }
}
