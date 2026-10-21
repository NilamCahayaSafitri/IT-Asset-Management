<?php

namespace App\Http\Controllers;

use App\Http\Requests\KategoriAsetRequest;
use App\Models\KategoriAset;
use Illuminate\Http\Request;

class KategoriAsetController extends Controller
{
    public function index(Request $request)
    {
        $kategori = KategoriAset::query()
            ->withCount('aset')
            ->when($request->filled('q'), function ($query) use ($request) {
                $query->where('nama_kategori', 'like', '%'.$request->q.'%');
            })
            ->orderBy('nama_kategori')
            ->paginate(10)
            ->withQueryString();

        return view('master.kategori-aset.index', compact('kategori'));
    }

    public function store(KategoriAsetRequest $request)
    {
        KategoriAset::create($request->validated());

        return redirect()
            ->route('master.kategori-aset.index')
            ->with('success', 'Kategori aset berhasil ditambahkan.');
    }

    public function update(KategoriAsetRequest $request, KategoriAset $kategoriAset)
    {
        $kategoriAset->update($request->validated());

        return redirect()
            ->route('master.kategori-aset.index')
            ->with('success', 'Kategori aset berhasil diperbarui.');
    }

    public function destroy(KategoriAset $kategoriAset)
    {
        // aset & aset_masuk_detail memakai FK ke kategori ini; cek dulu
        // supaya tidak muncul error database.
        if ($kategoriAset->aset()->exists() || $kategoriAset->asetMasukDetail()->exists()) {
            return back()->with('error', "Kategori \"{$kategoriAset->nama_kategori}\" masih dipakai data aset, jadi tidak bisa dihapus.");
        }

        $kategoriAset->delete();

        return redirect()
            ->route('master.kategori-aset.index')
            ->with('success', 'Kategori aset berhasil dihapus.');
    }
}
