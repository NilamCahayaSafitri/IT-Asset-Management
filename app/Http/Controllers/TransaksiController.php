<?php

namespace App\Http\Controllers;

use App\Enums\AssetFunction;
use App\Enums\StatusAset;
use App\Enums\StatusReturn;
use App\Http\Requests\TransaksiRequest;
use App\Models\Aset;
use App\Models\Pegawai;
use App\Models\Ruangan;
use App\Models\Transaksi;
use App\Services\TransaksiService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class TransaksiController extends Controller
{
    public function index(Request $request)
    {
        $transaksi = Transaksi::query()
            ->with(['pegawai', 'detail.aset'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $like = '%'.$request->q.'%';
                $query->where(fn ($w) => $w->where('no_referensi', 'like', $like)
                    ->orWhereHas('pegawai', fn ($p) => $p->where('nama', 'like', $like)->orWhere('kode_pegawai', 'like', $like))
                    ->orWhereHas('detail.aset', fn ($a) => $a->where('nama_aset', 'like', $like)->orWhere('kode_aset', 'like', $like)));
            })
            ->orderByDesc('tanggal_transaksi')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('transaksi.index', [
            'transaksi' => $transaksi,
            'formData'  => $this->formData(),
        ]);
    }

    public function store(TransaksiRequest $request, TransaksiService $service)
    {
        // Pelanggaran aturan status aset dilempar sebagai ValidationException (kembali ke form dengan pesan)
        $transaksi = $service->create($request->validated(), auth()->id());

        return redirect()
            ->route('transaksi.show', $transaksi)
            ->with('success', "Transaksi {$transaksi->no_referensi} disimpan. Surat siap dicetak.");
    }

    public function show(Transaksi $transaksi)
    {
        return view('transaksi.show', [
            'transaksi' => $transaksi,
            'd'         => $transaksi->suratData(),
        ]);
    }

    /** Halaman cetak polos (tanpa menu) yang langsung membuka dialog cetak / Simpan sebagai PDF */
    public function cetak(Transaksi $transaksi)
    {
        return view('transaksi.cetak', [
            'transaksi' => $transaksi,
            'd'         => $transaksi->suratData(),
        ]);
    }

    /** Unduh surat sebagai file PDF (A4), mis. EAF-2026-ICT-0232.pdf */
    public function pdf(Transaksi $transaksi)
    {
        $pdf = Pdf::loadView('transaksi.pdf', ['d' => $transaksi->suratData()])
            ->setPaper('a4', 'portrait');

        return $pdf->download(str_replace('/', '-', $transaksi->no_referensi).'.pdf');
    }

    public function selesai(Transaksi $transaksi)
    {
        $transaksi->update(['status_dokumen' => 'Selesai']);

        return back()->with('success', "Surat {$transaksi->no_referensi} ditandai Selesai (sudah ditandatangani).");
    }

    /** Data untuk form transaksi di halaman daftar */
    private function formData(): array
    {
        $pegawai = Pegawai::with('departemen')->orderBy('nama')->get()->mapWithKeys(fn ($p) => [
            $p->id => [
                'id'    => $p->id,
                'kode'  => $p->kode_pegawai,
                'nama'  => $p->nama,
                'dept'  => $p->departemen?->nama_departemen,
                'phone' => $p->kontak,
                'room'  => $p->room_no,
            ],
        ]);

        $baris = fn (Aset $a) => [
            'id'     => $a->id,
            'kode'   => $a->kode_aset,
            'nama'   => $a->nama_aset,
            'brand'  => $a->merk,
            'model'  => $a->model,
            'sn'     => $a->serial_number,
            'mem'    => $a->memory,
            'hdd'    => $a->hard_disk,
            'status' => $a->status?->value,
            // Procurement Information dari Aset Masuk asal aset ini
            'po'     => $a->asetMasukDetail?->asetMasuk?->no_po,
            'afe'    => $a->asetMasukDetail?->asetMasuk?->afe_cc,
            'year'   => $a->asetMasukDetail?->asetMasuk?->tahun,
        ];

        $tersedia = Aset::where('status', StatusAset::Available->value)->with('asetMasukDetail.asetMasuk')->orderBy('kode_aset')->get();

        // aset yang sedang dipakai + siapa pemegangnya (dari transaksi deploy/move terakhir)
        $dipakai = Aset::whereIn('status', [StatusAset::Deployed->value, StatusAset::Borrowed->value])
            ->with(['asetMasukDetail.asetMasuk', 'transaksiDetail' => fn ($q) => $q->where('bagian', 'deploy')->orderByDesc('id')->with('transaksi.pegawai')])
            ->orderBy('kode_aset')
            ->get();

        $aset = [];
        foreach ($tersedia as $a) {
            $aset[$a->id] = $baris($a) + ['holder' => null, 'holder_id' => null];
        }

        $refs = [];
        foreach ($dipakai as $a) {
            $trx = $a->transaksiDetail->first()?->transaksi;
            $aset[$a->id] = $baris($a) + ['holder' => $trx?->pegawai?->nama, 'holder_id' => $trx?->id_pegawai];

            if ($trx) {
                $refs[$trx->id]['ref'] = $trx->no_referensi;
                $refs[$trx->id]['peg'] = $trx->id_pegawai;
                $refs[$trx->id]['nama'] = $trx->pegawai?->nama;
                $refs[$trx->id]['assets'][] = $a->id;
                $refs[$trx->id]['names'][] = $a->nama_aset;
            }
        }

        $refs = collect($refs)->map(fn ($r) => [
            'ref'    => $r['ref'],
            'peg'    => $r['peg'],
            'assets' => $r['assets'],
            'label'  => "{$r['ref']} - {$r['nama']} (".implode(', ', $r['names']).')',
        ])->values()->all();

        return [
            'pegawai'      => $pegawai,
            'aset'         => $aset,
            'tersediaIds'  => $tersedia->pluck('id')->all(),
            'dipakaiIds'   => $dipakai->pluck('id')->all(),
            'refs'         => $refs,
            'ruangan'      => Ruangan::orderBy('nama_ruangan')->pluck('nama_ruangan')->all(),
            'afOptions'    => AssetFunction::options(),
            'stOptions'    => StatusReturn::options(),
            'stMap'        => collect(StatusReturn::cases())->mapWithKeys(fn ($s) => [$s->value => $s->toStatusAset()->value])->all(),
            'old'          => [
                'types'  => old('types', []),
                'peg'    => old('id_pegawai'),
                'deploy' => old('deploy', []),
                'return' => old('return', []),
                'ref'    => old('return_ref'),
            ],
            'nextRef'      => Transaksi::nextReferensi(),
        ];
    }
}
