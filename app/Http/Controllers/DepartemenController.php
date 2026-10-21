<?php

namespace App\Http\Controllers;

use App\Http\Requests\DepartemenRequest;
use App\Models\Departemen;
use Illuminate\Http\Request;

class DepartemenController extends Controller
{
    public function index(Request $request)
    {
        $departemen = Departemen::query()
            ->withCount(['ruangan', 'pegawai'])
            ->when($request->filled('q'), fn ($q) => $q->where('nama_departemen', 'like', '%'.$request->q.'%'))
            ->orderBy('nama_departemen')
            ->paginate(10)
            ->withQueryString();

        return view('master.departemen.index', compact('departemen'));
    }

    public function store(DepartemenRequest $request)
    {
        Departemen::create($request->validated());

        return redirect()->route('master.departemen.index')->with('success', 'Departemen berhasil ditambahkan.');
    }

    public function update(DepartemenRequest $request, Departemen $departemen)
    {
        $departemen->update($request->validated());

        return redirect()->route('master.departemen.index')->with('success', 'Departemen berhasil diperbarui.');
    }

    public function destroy(Departemen $departemen)
    {
        if ($departemen->ruangan()->exists() || $departemen->pegawai()->exists()) {
            return back()->with('error', "Departemen \"{$departemen->nama_departemen}\" masih dipakai oleh ruangan atau pegawai, jadi tidak bisa dihapus.");
        }

        $departemen->delete();

        return redirect()->route('master.departemen.index')->with('success', 'Departemen berhasil dihapus.');
    }
}
