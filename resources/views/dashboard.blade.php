<x-app-layout>
    <x-page-head title="Dashboard" sub="Pusat informasi seluruh aset ICT" />

    @php
        // Jaga-jaga bila route /dashboard masih memakai closure lama (tanpa DashboardController):
        // muat sendiri datanya agar halaman tidak error / tampil kosong.
        if (! isset($bolehAset)) {
            extract(app(\App\Http\Controllers\DashboardController::class)->data(auth()->user()));
        }

        $stat      = $stat      ?? [];
        $donut     = $donut     ?? ['total' => 0, 'segmen' => []];
        $terlambat = $terlambat ?? [];
        $volume    = $volume    ?? [];
        $terbaru   = $terbaru   ?? [];
        $menungguTtd = $menungguTtd ?? 0;

        $angka = fn ($n) => number_format((int) $n, 0, ',', '.');
        $pill = fn ($teks, $bg, $fg) => '<span style="display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;white-space:nowrap;background:'.$bg.';color:'.$fg.'">'.e($teks).'</span>';
        $warnaJenis = ['Deploy' => ['#dbe6f4', '#2c5aa0'], 'Return' => ['#e7e9ec', '#5b6572'], 'Replace/Swap' => ['#fdead9', '#c8631b'], 'Move' => ['#e1f0e6', '#3c8558']];
        $idTerlambat = array_column($terlambat, 'trx');

        // butir "Perlu Perhatian": hanya yang jumlahnya lebih dari nol
        $perhatian = [];
        if ($bolehAset) {
            if (count($terlambat) > 0) {
                $perhatian[] = ['teks' => 'Pinjaman melewati batas kembali', 'jumlah' => count($terlambat), 'url' => route('aset.index', ['status' => 'Borrowed']), 'warna' => '#c4463a'];
            }
            if (($stat['broken'] ?? 0) > 0) {
                $perhatian[] = ['teks' => 'Aset Broken', 'jumlah' => $stat['broken'], 'url' => route('aset.index', ['status' => 'Broken']), 'warna' => '#9b2f25'];
            }
            if (($stat['disposal'] ?? 0) > 0) {
                $perhatian[] = ['teks' => 'Aset Waiting for Disposal', 'jumlah' => $stat['disposal'], 'url' => route('aset.index', ['status' => 'Waiting for Disposal']), 'warna' => '#b4540a'];
            }
        }
        if ($bolehTransaksi && $menungguTtd > 0) {
            $perhatian[] = ['teks' => 'Surat menunggu tanda tangan', 'jumlah' => $menungguTtd, 'url' => route('transaksi.index'), 'warna' => '#c8631b'];
        }
    @endphp

    <style>
        .db-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;margin-bottom:16px}
        .db-stat{background:#fff;border:1px solid #e4e7ec;border-radius:10px;padding:14px 16px}
        .db-stat-l{display:flex;align-items:center;gap:7px;font-size:12px;color:#66707d;font-weight:500}
        .db-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0}
        .db-stat-v{font-family:'Space Grotesk',sans-serif;font-size:28px;font-weight:700;margin-top:6px;color:#16283f;line-height:1.1}
        .db-grid2{display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:16px;margin-bottom:16px}
        .db-card{background:#fff;border:1px solid #e4e7ec;border-radius:10px;padding:18px;margin-bottom:16px}
        .db-grid2 .db-card{margin-bottom:0}
        .db-title{font-family:'Space Grotesk',sans-serif;font-weight:600;font-size:14px;margin-bottom:14px}
        .db-donut{display:flex;align-items:center;gap:22px;flex-wrap:wrap}
        .db-legend{display:flex;flex-direction:column;gap:8px;font-size:13px}
        .db-legend span.t{display:inline-block;width:10px;height:10px;border-radius:3px;margin-right:8px}
        .db-chart{display:flex;align-items:flex-end;gap:12px;height:170px;padding-top:6px}
        .db-col{flex:1;display:flex;flex-direction:column;justify-content:flex-end;align-items:center;height:100%}
        .db-bar{width:100%;max-width:46px;background:#1d9e75;border-radius:6px 6px 0 0;min-height:3px}
        .db-bar.nol{background:#e4e7ec}
        .db-n{font-size:12px;font-weight:600;color:#16283f;margin-bottom:4px}
        .db-lbl{font-size:12px;color:#66707d;margin-top:7px}
        .db-empty{color:#66707d;font-size:13px}
        .db-att{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid #e4e7ec;font-size:13.5px;text-decoration:none;color:inherit}
        .db-att:last-of-type{border-bottom:none}
        .db-att b{font-family:'Space Grotesk',sans-serif;font-size:18px}
        .db-late{font-size:12.5px;padding:7px 0;border-bottom:1px solid #f0f1f4;display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap}
        .db-late a{color:#16283f;font-weight:600;text-decoration:none}
        .db-tbl{width:100%;border-collapse:collapse;font-size:12.5px}
        .db-tbl th{text-align:left;font-size:11px;text-transform:uppercase;color:#66707d;padding:8px;border-bottom:1px solid #e4e7ec;white-space:nowrap}
        .db-tbl td{padding:10px 8px;border-bottom:1px solid #e4e7ec;vertical-align:top}
        .db-tbl tr:last-child td{border-bottom:none}
        .db-mono{font-family:'IBM Plex Mono',monospace;font-size:11.5px;color:#2c4a6e;text-decoration:none}
    </style>

    @if (! $bolehAset && ! $bolehTransaksi)
        <div class="db-card">
            <div class="db-title">Selamat datang, {{ auth()->user()->name }}</div>
            <div class="db-empty">Akun Anda belum memiliki akses ke data aset maupun transaksi. Hubungi administrator.</div>
        </div>
    @endif

    @if ($bolehAset)
        {{-- Kartu statistik --}}
        <div class="db-stats">
            <div class="db-stat"><div class="db-stat-l">Total Aset</div><div class="db-stat-v">{{ $angka($stat['total'] ?? 0) }}</div></div>
            <div class="db-stat"><div class="db-stat-l"><span class="db-dot" style="background:#3c8558"></span>Available</div><div class="db-stat-v">{{ $angka($stat['available'] ?? 0) }}</div></div>
            <div class="db-stat"><div class="db-stat-l"><span class="db-dot" style="background:#c8631b"></span>Borrowed</div><div class="db-stat-v">{{ $angka($stat['borrowed'] ?? 0) }}</div></div>
            <div class="db-stat"><div class="db-stat-l"><span class="db-dot" style="background:#2c5aa0"></span>Deployed</div><div class="db-stat-v">{{ $angka($stat['deployed'] ?? 0) }}</div></div>
            <div class="db-stat"><div class="db-stat-l"><span class="db-dot" style="background:#c4463a"></span>Maintenance</div><div class="db-stat-v">{{ $angka($stat['maintenance'] ?? 0) }}</div></div>
        </div>
    @endif

    {{-- Perlu perhatian --}}
    @if (count($perhatian) > 0)
        <div class="db-card">
            <div class="db-title">Perlu Perhatian</div>
            @foreach ($perhatian as $p)
                <a class="db-att" href="{{ $p['url'] }}">
                    <span><span class="db-dot" style="background:{{ $p['warna'] }};display:inline-block;margin-right:8px"></span>{{ $p['teks'] }}</span>
                    <b style="color:{{ $p['warna'] }}">{{ $angka($p['jumlah']) }}</b>
                </a>
            @endforeach

            @if (count($terlambat) > 0)
                <div style="margin-top:14px;font-size:12px;font-weight:600;color:#66707d">Pinjaman terlambat</div>
                @foreach (array_slice($terlambat, 0, 5) as $t)
                    <div class="db-late">
                        <span><a href="{{ route('aset.show', $t['aset']) }}">{{ $t['aset']->nama_aset }}</a> <span class="db-mono">{{ $t['aset']->kode_aset }}</span></span>
                        <span style="color:#66707d">{{ $t['pegawai'] ?? 'pemegang belum tercatat' }} &middot; batas {{ date('d/m/Y', strtotime($t['batas'])) }} &middot; <b style="color:#9b2f25">terlambat {{ $t['hari'] }} hari</b></span>
                    </div>
                @endforeach
            @endif
        </div>
    @endif

    @if ($bolehAset || $bolehTransaksi)
        <div class="db-grid2">
            @if ($bolehAset)
                <div class="db-card">
                    <div class="db-title">Komposisi Aset per Kategori</div>
                    @if ($donut['total'] > 0)
                        @php $keliling = 2 * M_PI * 54; $geser = 0; @endphp
                        <div class="db-donut">
                            <svg viewBox="0 0 140 140" width="150" height="150" role="img" aria-label="Komposisi aset per kategori">
                                <circle cx="70" cy="70" r="54" fill="none" stroke="#eef0f3" stroke-width="22"/>
                                @foreach ($donut['segmen'] as $s)
                                    @php $garis = $s['jumlah'] / $donut['total'] * $keliling; @endphp
                                    <circle cx="70" cy="70" r="54" fill="none" stroke="{{ $s['warna'] }}" stroke-width="22"
                                            stroke-dasharray="{{ round($garis, 2) }} {{ round($keliling - $garis, 2) }}" stroke-dashoffset="{{ round(-$geser, 2) }}"
                                            transform="rotate(-90 70 70)"/>
                                    @php $geser += $garis; @endphp
                                @endforeach
                                <text x="70" y="68" text-anchor="middle" font-size="22" font-weight="700" fill="#16283f">{{ $angka($donut['total']) }}</text>
                                <text x="70" y="86" text-anchor="middle" font-size="11" fill="#66707d">aset</text>
                            </svg>
                            <div class="db-legend">
                                @foreach ($donut['segmen'] as $s)
                                    <div><span class="t" style="background:{{ $s['warna'] }}"></span>{{ $s['nama'] }} &middot; {{ $s['persen'] }}%</div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="db-empty">Belum ada aset. Aset dibuat lewat menu Aset Masuk.</div>
                    @endif
                </div>
            @endif

            @if ($bolehTransaksi)
                <div class="db-card">
                    <div class="db-title">Volume Transaksi 6 Bulan Terakhir</div>
                    @php $maks = max(1, ...array_column($volume, 'jumlah') ?: [0]); $adaVolume = array_sum(array_column($volume, 'jumlah')) > 0; @endphp
                    @if ($adaVolume)
                        <div class="db-chart">
                            @foreach ($volume as $v)
                                <div class="db-col">
                                    <div class="db-n">{{ $v['jumlah'] }}</div>
                                    <div class="db-bar{{ $v['jumlah'] === 0 ? ' nol' : '' }}" style="height:{{ $v['jumlah'] === 0 ? 3 : max(6, round($v['jumlah'] / $maks * 100)) }}%"></div>
                                    <div class="db-lbl">{{ $v['label'] }}</div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="db-empty">Belum ada transaksi dalam 6 bulan terakhir.</div>
                    @endif
                </div>
            @endif
        </div>
    @endif

    {{-- Transaksi terbaru --}}
    @if ($bolehTransaksi)
        <div class="db-card">
            <div class="db-title">Aktivitas Transaksi Terbaru</div>
            @if (count($terbaru) > 0)
                <div style="overflow-x:auto">
                    <table class="db-tbl">
                        <thead><tr><th>Ref</th><th>Jenis</th><th>Aset</th><th>Pegawai</th><th>Tanggal</th><th>Status</th></tr></thead>
                        <tbody>
                            @foreach ($terbaru as $t)
                                <tr>
                                    <td><a class="db-mono" href="{{ route('transaksi.show', $t['id']) }}">{{ $t['ref'] }}</a></td>
                                    <td>@foreach ($t['jenis'] as $j){!! $pill($j, ...($warnaJenis[$j] ?? ['#e7e9ec', '#5b6572'])) !!} @endforeach</td>
                                    <td>{{ $t['aset'] !== '' ? $t['aset'] : '-' }}</td>
                                    <td>{{ $t['pegawai'] ?? '-' }}</td>
                                    <td>{{ $t['tanggal'] }}</td>
                                    <td>
                                        {!! $t['selesai'] ? $pill('Selesai', '#e1f0e6', '#3c8558') : $pill('Menunggu TTD', '#fdead9', '#c8631b') !!}
                                        @if (in_array($t['id'], $idTerlambat)){!! $pill('Terlambat', '#fbe4e1', '#c4463a') !!}@endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="db-empty">Belum ada transaksi.</div>
            @endif
        </div>
    @endif
</x-app-layout>
