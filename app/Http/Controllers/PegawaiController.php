<?php

namespace App\Http\Controllers;

use App\Http\Requests\PegawaiRequest;
use App\Models\Departemen;
use App\Models\Pegawai;
use Illuminate\Http\Request;

class PegawaiController extends Controller
{
    public function index(Request $request)
    {
        $pegawai = Pegawai::query()
            ->with('departemen')
            ->withCount('transaksi')
            ->when($request->filled('q'), function ($q) use ($request) {
                $s = '%'.$request->q.'%';
                $q->where(fn ($w) => $w->where('nama', 'like', $s)
                    ->orWhere('kode_pegawai', 'like', $s)
                    ->orWhere('jabatan', 'like', $s)
                    ->orWhere('email', 'like', $s));
            })
            ->orderBy('nama')
            ->paginate(10)
            ->withQueryString();

        $departemenOptions = Departemen::orderBy('nama_departemen')->pluck('nama_departemen', 'id');

        return view('master.pegawai.index', compact('pegawai', 'departemenOptions'));
    }

    public function store(PegawaiRequest $request)
    {
        Pegawai::create($request->validated());

        return redirect()->route('master.pegawai.index')->with('success', 'Pegawai berhasil ditambahkan.');
    }

    public function update(PegawaiRequest $request, Pegawai $pegawai)
    {
        $pegawai->update($request->validated());

        return redirect()->route('master.pegawai.index')->with('success', 'Pegawai berhasil diperbarui.');
    }

    public function destroy(Pegawai $pegawai)
    {
        if ($pegawai->transaksi()->exists()) {
            return back()->with('error', "Pegawai \"{$pegawai->nama}\" sudah punya riwayat transaksi, jadi tidak bisa dihapus.");
        }

        $pegawai->delete();

        return redirect()->route('master.pegawai.index')->with('success', 'Pegawai berhasil dihapus.');
    }
}
