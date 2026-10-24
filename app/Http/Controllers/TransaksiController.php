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
        $d = $transaksi->suratData();

        // Tahap 1: render apa adanya. Jika lebih dari satu halaman, coba mode rapat.
        $rapat = false;
        [$pdf, $akhir] = $this->renderPdf($d, 0, false);

        if ($pdf->getDomPDF()->getCanvas()->get_page_count() > 1) {
            $rapat = true;
            [$pdf, $akhir] = $this->renderPdf($d, 0, true);
        }

        // Tahap 2: panjangkan ruang tanda tangan sebesar sisa halaman di bawah footer,
        // sehingga footer kecil ("Select appropriate one" / "Supersedes ...") tepat di margin bawah.
        $sisa = $this->sisaRuang($pdf, $akhir);
        if ($sisa > 6) {
            [$pdf] = $this->renderPdf($d, $sisa - 4, $rapat);   // 4 pt cadangan agar tidak melompat ke halaman 2
        }

        return $pdf->download(str_replace('/', '-', $transaksi->no_referensi).'.pdf');
    }

    /**
     * Render surat ke PDF dan catat posisi bawah footer surat (pt dari tepi atas kertas).
     * Posisi dicatat lewat callback saat elemen digambar, karena pohon elemen dompdf
     * sudah dikosongkan setelah render() selesai.
     *
     * @return array{0: \Barryvdh\DomPDF\PDF, 1: float|null}
     */
    private function renderPdf(array $d, float $tambahan, bool $rapat): array
    {
        $akhir = null;

        $pdf = Pdf::loadView('transaksi.pdf', ['d' => $d, 'sigExtra' => $tambahan, 'compact' => $rapat])
            ->setPaper('a4', 'portrait');

        $pdf->getDomPDF()->setCallbacks([[
            'event' => 'end_frame',
            'f' => function ($frame) use (&$akhir) {
                $frame = is_array($frame) ? $frame['frame'] : $frame;   // dompdf 2 mengirim array, dompdf 3 mengirim frame
                $node = $frame->get_node();

                if ($node instanceof \DOMElement && $node->getAttribute('class') === 'ea-foot-t') {
                    $kotak = $frame->get_border_box();
                    $akhir = $kotak['y'] + $kotak['h'];
                }
            },
        ]]);

        $pdf->output();   // menjalankan render

        return [$pdf, $akhir];
    }

    /** Sisa tinggi (pt) antara akhir footer surat dan margin bawah; 0 jika surat lebih dari satu halaman */
    private function sisaRuang($pdf, ?float $akhir): float
    {
        $canvas = $pdf->getDomPDF()->getCanvas();

        if ($akhir === null || $canvas->get_page_count() > 1) {
            return 0;
        }

        $margin = 9.9 / 25.4 * 72;   // margin halaman 9,9 mm dalam pt

        return max(0, $canvas->get_height() - $margin - $akhir);
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
