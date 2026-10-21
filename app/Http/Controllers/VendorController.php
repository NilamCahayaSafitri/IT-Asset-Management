<?php

namespace App\Http\Controllers;

use App\Http\Requests\VendorRequest;
use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index(Request $request)
    {
        $vendor = Vendor::query()
            ->withCount('asetMasuk')
            ->when($request->filled('q'), function ($q) use ($request) {
                $s = '%'.$request->q.'%';
                $q->where(fn ($w) => $w->where('nama_vendor', 'like', $s)
                    ->orWhere('kontak', 'like', $s)
                    ->orWhere('email', 'like', $s));
            })
            ->orderBy('nama_vendor')
            ->paginate(10)
            ->withQueryString();

        return view('master.vendor.index', compact('vendor'));
    }

    public function store(VendorRequest $request)
    {
        Vendor::create($request->validated());

        return redirect()->route('master.vendor.index')->with('success', 'Vendor berhasil ditambahkan.');
    }

    public function update(VendorRequest $request, Vendor $vendor)
    {
        $vendor->update($request->validated());

        return redirect()->route('master.vendor.index')->with('success', 'Vendor berhasil diperbarui.');
    }

    public function destroy(Vendor $vendor)
    {
        if ($vendor->asetMasuk()->exists()) {
            return back()->with('error', "Vendor \"{$vendor->nama_vendor}\" sudah punya riwayat aset masuk, jadi tidak bisa dihapus.");
        }

        $vendor->delete();

        return redirect()->route('master.vendor.index')->with('success', 'Vendor berhasil dihapus.');
    }
}
