<?php

namespace App\Http\Controllers;

use App\Enums\StatusAset;
use App\Models\Aset;
use App\Models\KategoriAset;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        return view('dashboard', $this->data($request->user()));
    }

    /**
     * Seluruh data dashboard untuk satu pengguna. Dipisah dari __invoke supaya tampilan
     * tetap bisa memuat datanya sendiri bila route /dashboard belum memakai controller ini.
     */
    public function data(User $user): array
    {
        $data = [
            'bolehAset'      => $user->can('kelola-aset'),
            'bolehTransaksi' => $user->can('kelola-transaksi'),
        ];

        if ($data['bolehAset']) {
            $perStatus = Aset::query()->selectRaw('status, COUNT(*) as jumlah')->groupBy('status')->pluck('jumlah', 'status')->all();
            $hitung = fn (StatusAset $s) => (int) ($perStatus[$s->value] ?? 0);

            $data['stat'] = [
                // "Total Aset" tidak menghitung aset Inactive (sudah keluar dari inventaris)
                'total'       => array_sum($perStatus) - $hitung(StatusAset::Inactive),
                'available'   => $hitung(StatusAset::Available),
                'borrowed'    => $hitung(StatusAset::Borrowed),
                'deployed'    => $hitung(StatusAset::Deployed),
                'maintenance' => $hitung(StatusAset::Maintenance),
                'broken'      => $hitung(StatusAset::Broken),
                'disposal'    => $hitung(StatusAset::WaitingForDisposal),
            ];

            $kategori = KategoriAset::withCount(['aset' => fn ($q) => $q->where('status', '!=', StatusAset::Inactive->value)])
                ->get()
                ->map(fn ($k) => ['nama' => $k->nama_kategori, 'jumlah' => (int) $k->aset_count])
                ->all();
            $data['donut'] = $this->susunDonut($kategori);

            $pinjam = Aset::where('status', StatusAset::Borrowed->value)
                ->with(['transaksiDetail' => fn ($q) => $q->where('bagian', 'deploy')->orderByDesc('id')->with('transaksi.pegawai')])
                ->get()
                ->map(function (Aset $a) {
                    $d = $a->transaksiDetail->first();

                    return [
                        'aset'    => $a,
                        'pegawai' => $d?->transaksi?->pegawai?->nama,
                        'trx'     => $d?->id_transaksi,
                        'batas'   => $d?->batas_kembali?->toDateString(),
                    ];
                })
                ->all();
            $data['terlambat'] = $this->susunTerlambat($pinjam, now()->toDateString());
        }

        if ($data['bolehTransaksi']) {
            $data['menungguTtd'] = Transaksi::where('status_dokumen', 'Menunggu TTD')->count();

            $tanggal = Transaksi::where('tanggal_transaksi', '>=', now()->startOfMonth()->subMonths(5)->toDateString())
                ->pluck('tanggal_transaksi')
                ->map(fn ($t) => substr((string) $t, 0, 10))
                ->all();
            $data['volume'] = $this->susunVolume($tanggal, now());

            $data['terbaru'] = Transaksi::with(['pegawai', 'detail.aset'])
                ->orderByDesc('tanggal_transaksi')
                ->orderByDesc('id')
                ->limit(6)
                ->get()
                ->map(function (Transaksi $t) {
                    $nama = $t->detail->map(fn ($d) => $d->aset?->nama_aset)->filter()->unique()->values();

                    return [
                        'id'      => $t->id,
                        'ref'     => $t->no_referensi,
                        'jenis'   => $t->types(),
                        'aset'    => $nama->take(2)->implode(', ').($nama->count() > 2 ? ' +'.($nama->count() - 2).' lainnya' : ''),
                        'pegawai' => $t->pegawai?->nama,
                        'tanggal' => $t->tanggal_transaksi?->format('d/m/Y'),
                        'selesai' => $t->status_dokumen === 'Selesai',
                    ];
                })
                ->all();
        }

        return $data;
    }

    /**
     * Komposisi aset per kategori: 4 kategori terbesar + "Lainnya".
     *
     * @param  array<int, array{nama: string, jumlah: int}>  $kategori
     * @return array{total: int, segmen: array<int, array{nama: string, jumlah: int, persen: int, warna: string}>}
     */
    private function susunDonut(array $kategori): array
    {
        $warna = ['#16283f', '#1d9e75', '#2c4a6e', '#c8631b', '#8fa9c4'];

        $kategori = array_values(array_filter($kategori, fn ($k) => $k['jumlah'] > 0));
        usort($kategori, fn ($a, $b) => [$b['jumlah'], $a['nama']] <=> [$a['jumlah'], $b['nama']]);

        $total = array_sum(array_column($kategori, 'jumlah'));
        if ($total === 0) {
            return ['total' => 0, 'segmen' => []];
        }

        $segmen = array_slice($kategori, 0, 4);
        $sisa = array_sum(array_column(array_slice($kategori, 4), 'jumlah'));
        if ($sisa > 0) {
            $segmen[] = ['nama' => 'Lainnya', 'jumlah' => $sisa];
        }

        foreach ($segmen as $i => $s) {
            $segmen[$i]['persen'] = (int) round($s['jumlah'] / $total * 100);
            $segmen[$i]['warna'] = $warna[$i];
        }

        return ['total' => $total, 'segmen' => $segmen];
    }

    /**
     * Jumlah transaksi per bulan untuk 6 bulan terakhir (termasuk bulan ini).
     *
     * @param  array<int, string>  $tanggal  tanggal transaksi berformat Y-m-d
     * @return array<int, array{label: string, jumlah: int}>
     */
    private function susunVolume(array $tanggal, \DateTimeInterface $sekarang): array
    {
        $nama = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $awal = (new \DateTimeImmutable($sekarang->format('Y-m-01')))->modify('-5 months');

        $hitung = [];
        foreach ($tanggal as $t) {
            $bulan = substr($t, 0, 7);
            $hitung[$bulan] = ($hitung[$bulan] ?? 0) + 1;
        }

        $hasil = [];
        for ($i = 0; $i < 6; $i++) {
            $bulan = $awal->modify("+{$i} months");
            $hasil[] = ['label' => $nama[(int) $bulan->format('n') - 1], 'jumlah' => $hitung[$bulan->format('Y-m')] ?? 0];
        }

        return $hasil;
    }

    /**
     * Pinjaman yang melewati batas kembali, paling lama terlambat di atas.
     *
     * @param  array<int, array<string, mixed>>  $baris  setiap baris punya 'batas' (Y-m-d atau null)
     * @return array<int, array<string, mixed>>  baris yang terlambat, ditambah 'hari' (jumlah hari terlambat)
     */
    private function susunTerlambat(array $baris, string $hariIni): array
    {
        $hasil = [];

        foreach ($baris as $b) {
            if (empty($b['batas']) || $b['batas'] >= $hariIni) {
                continue;
            }

            $b['hari'] = (int) (new \DateTimeImmutable($b['batas']))->diff(new \DateTimeImmutable($hariIni))->days;
            $hasil[] = $b;
        }

        usort($hasil, fn ($x, $y) => strcmp($x['batas'], $y['batas']));

        return $hasil;
    }
}
