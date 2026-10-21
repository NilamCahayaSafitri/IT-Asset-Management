<?php

namespace App\Http\Controllers;

use App\Http\Requests\RuanganRequest;
use App\Models\Departemen;
use App\Models\Ruangan;
use Illuminate\Http\Request;

class RuanganController extends Controller
{
    public function index(Request $request)
    {
        $ruangan = Ruangan::query()
            ->with('departemen')
            ->withCount('aset')
            ->when($request->filled('q'), function ($q) use ($request) {
                $s = '%'.$request->q.'%';
                $q->where(fn ($w) => $w->where('nama_ruangan', 'like', $s)
                    ->orWhereHas('departemen', fn ($d) => $d->where('nama_departemen', 'like', $s)));
            })
            ->orderBy('nama_ruangan')
            ->paginate(10)
            ->withQueryString();

        $departemenOptions = Departemen::orderBy('nama_departemen')->pluck('nama_departemen', 'id');

        return view('master.ruangan.index', compact('ruangan', 'departemenOptions'));
    }

    public function store(RuanganRequest $request)
    {
        Ruangan::create($request->validated());

        return redirect()->route('master.ruangan.index')->with('success', 'Ruangan berhasil ditambahkan.');
    }

    public function update(RuanganRequest $request, Ruangan $ruangan)
    {
        $ruangan->update($request->validated());

        return redirect()->route('master.ruangan.index')->with('success', 'Ruangan berhasil diperbarui.');
    }

    public function destroy(Ruangan $ruangan)
    {
        if ($ruangan->aset()->exists()) {
            return back()->with('error', "Ruangan \"{$ruangan->nama_ruangan}\" masih berisi aset, jadi tidak bisa dihapus.");
        }

        $ruangan->delete();

        return redirect()->route('master.ruangan.index')->with('success', 'Ruangan berhasil dihapus.');
    }
}
