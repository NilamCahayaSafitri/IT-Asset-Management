<x-app-layout>
    <x-page-head :title="$aset->nama_aset" :sub="$aset->kode_aset.' · '.($aset->kategori?->nama_kategori ?? '-')">
        <a href="{{ route('aset.index') }}" class="sh-back">&larr; Data Aset</a>
    </x-page-head>

    @php
        $detailAktif = $pemegang['detail'] ?? null;
        $batas = $detailAktif?->batas_kembali;
        $dipinjam = $aset->status === \App\Enums\StatusAset::Borrowed;
        $telat = $dipinjam && $batas && now()->startOfDay()->gt($batas);
        $hariTelat = $telat ? (int) $batas->diffInDays(now()->startOfDay(), true) : 0;
        $bolehSurat = auth()->user()->can('kelola-transaksi');
        $rp = fn ($v) => $v !== null ? 'Rp '.number_format((float) $v, 0, ',', '.') : 'N/A';
        $isi = fn ($v) => filled($v) ? $v : 'N/A';
    @endphp

    <style>
        .sh-back{display:inline-flex;align-items:center;border:1px solid #e4e7ec;background:#fff;border-radius:8px;padding:8px 16px;font-size:13px;font-weight:600;color:#16283f;text-decoration:none}
        .sh-grid{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1.4fr);gap:16px;align-items:start}
        @media (max-width:900px){.sh-grid{grid-template-columns:1fr}}
        .sh-card{background:#fff;border:1px solid #e4e7ec;border-radius:10px;padding:18px;margin-bottom:16px}
        .sh-title{font-weight:700;font-size:14px;margin-bottom:12px}
        .sh-id{display:flex;gap:16px;flex-wrap:wrap;align-items:flex-start}
        .sh-qr{background:#f5f6f8;border-radius:10px;padding:12px;min-width:156px;min-height:156px;display:flex;align-items:center;justify-content:center;font-size:12px;color:#66707d;text-align:center}
        .sh-kode{font-family:'IBM Plex Mono',monospace;font-size:20px;font-weight:600;color:#2c4a6e;margin-bottom:8px}
        .sh-url{font-size:11px;color:#66707d;word-break:break-all;margin:10px 0}
        .sh-warn{display:none;font-size:12px;color:#9b2f25;background:#fbe4e1;border-radius:8px;padding:8px 10px;margin-bottom:10px}
        .sh-btn{border:1px solid #e4e7ec;background:#fff;border-radius:8px;padding:8px 14px;font-size:12.5px;font-weight:600;cursor:pointer;color:#16283f;text-decoration:none;display:inline-block;font-family:inherit}
        .sh-kv{display:grid;grid-template-columns:140px 1fr;gap:8px;padding:8px 0;border-bottom:1px solid #e4e7ec;font-size:13px}
        .sh-kv:last-child{border-bottom:none}
        .sh-k{color:#66707d}
        .sh-late{color:#9b2f25;font-weight:600}
        .sh-tbl{width:100%;border-collapse:collapse;font-size:12.5px}
        .sh-tbl th{text-align:left;font-size:11px;text-transform:uppercase;color:#66707d;padding:7px 8px;border-bottom:1px solid #e4e7ec;white-space:nowrap}
        .sh-tbl td{padding:9px 8px;border-bottom:1px solid #e4e7ec;vertical-align:top}
        .sh-empty{color:#66707d;font-size:13px}
    </style>

    <div class="sh-grid">
        <div>
            {{-- Identitas, status, dan QR --}}
            <div class="sh-card">
                <div class="sh-id">
                    <div class="sh-qr" id="sh-qr">Memuat QR...</div>
                    <div>
                        <div class="sh-kode">{{ $aset->kode_aset }}</div>
                        <x-status-badge :status="$aset->status" />
                    </div>
                </div>
                <div class="sh-url" id="sh-url">{{ $aset->urlScan() }}</div>
                <div class="sh-warn" id="sh-warn">Alamat QR masih localhost, jadi tidak bisa dibuka dari HP. Ubah <b>APP_URL</b> di file .env ke alamat server yang bisa dijangkau dari jaringan kantor, lalu muat ulang halaman ini.</div>
                <button type="button" class="sh-btn" id="sh-print">Cetak Label QR</button>
                <a href="{{ route('aset.index', ['q' => $aset->kode_aset]) }}" class="sh-btn">Ubah di Data Aset</a>
            </div>

            {{-- Pemegang saat ini --}}
            <div class="sh-card">
                <div class="sh-title">Pemegang saat ini</div>
                @if ($pemegang && $pemegang['pegawai'])
                    @php $p = $pemegang['pegawai']; $t = $pemegang['transaksi']; @endphp
                    <div class="sh-kv"><div class="sh-k">Nama</div><div>{{ $p->nama }} <span style="color:#66707d">({{ $p->kode_pegawai }})</span></div></div>
                    <div class="sh-kv"><div class="sh-k">Departemen</div><div>{{ $isi($p->departemen?->nama_departemen) }}</div></div>
                    <div class="sh-kv"><div class="sh-k">Ruangan</div><div>{{ $isi($t?->room_no) }}</div></div>
                    <div class="sh-kv"><div class="sh-k">Sejak</div><div>{{ $t?->tanggal_transaksi?->format('d/m/Y') }}</div></div>
                    @if ($dipinjam)
                        <div class="sh-kv"><div class="sh-k">Batas kembali</div>
                            <div>
                                @if ($batas)
                                    {{ $batas->format('d/m/Y') }}
                                    @if ($telat)<span class="sh-late"> &middot; terlambat {{ $hariTelat }} hari</span>@endif
                                @else
                                    <span style="color:#66707d">Tidak ditentukan</span>
                                @endif
                            </div>
                        </div>
                    @endif
                @elseif ($aset->status?->sedangDipakai())
                    <div class="sh-empty">Aset berstatus {{ $aset->status->label() }}, tetapi pemegangnya belum tercatat di transaksi.</div>
                @else
                    <div class="sh-empty">Aset tidak sedang dipakai ({{ $aset->status?->label() }}).</div>
                @endif
            </div>
        </div>

        {{-- Informasi aset --}}
        <div class="sh-card">
            <div class="sh-title">Informasi aset</div>
            <div class="sh-kv"><div class="sh-k">Nama</div><div>{{ $aset->nama_aset }}</div></div>
            <div class="sh-kv"><div class="sh-k">Kategori</div><div>{{ $isi($aset->kategori?->nama_kategori) }}</div></div>
            <div class="sh-kv"><div class="sh-k">Serial number</div><div><span class="mono">{{ $isi($aset->serial_number) }}</span></div></div>
            <div class="sh-kv"><div class="sh-k">Merk</div><div>{{ $isi($aset->merk) }}</div></div>
            <div class="sh-kv"><div class="sh-k">Model</div><div>{{ $isi($aset->model) }}</div></div>
            <div class="sh-kv"><div class="sh-k">Memory</div><div>{{ $isi($aset->memory) }}</div></div>
            <div class="sh-kv"><div class="sh-k">Hard disk</div><div>{{ $isi($aset->hard_disk) }}</div></div>
            <div class="sh-kv"><div class="sh-k">Lokasi</div><div>{{ $isi($aset->ruangan?->nama_ruangan) }}</div></div>
            <div class="sh-kv"><div class="sh-k">Vendor</div><div>{{ $isi($aset->asetMasukDetail?->asetMasuk?->vendor?->nama_vendor) }}</div></div>
            <div class="sh-kv"><div class="sh-k">No. PO / Kontrak</div><div>{{ $isi($aset->asetMasukDetail?->asetMasuk?->no_po) }}</div></div>
            <div class="sh-kv"><div class="sh-k">Tgl perolehan</div><div>{{ $aset->tanggal_perolehan?->format('d/m/Y') ?? 'N/A' }}</div></div>
            <div class="sh-kv"><div class="sh-k">Harga</div><div>{{ $rp($aset->harga) }}</div></div>
            <div class="sh-kv"><div class="sh-k">Umur ekonomis</div><div>{{ $aset->umur !== null ? $aset->umur.' tahun' : 'N/A' }}</div></div>
        </div>
    </div>

    {{-- Riwayat transaksi --}}
    <div class="sh-card">
        <div class="sh-title">Riwayat transaksi</div>
        @if ($riwayat->isEmpty())
            <div class="sh-empty">Belum ada riwayat transaksi untuk aset ini.</div>
        @else
            <div style="overflow-x:auto">
                <table class="sh-tbl">
                    <thead><tr><th>Tanggal</th><th>No. Surat</th><th>Jenis</th><th>Pegawai</th><th>Keterangan</th></tr></thead>
                    <tbody>
                        @foreach ($riwayat as $r)
                            @php
                                $t = $r->transaksi;
                                if ($r->bagian === 'deploy') {
                                    $ket = $r->asset_function?->value;
                                    if ($r->asset_function === \App\Enums\AssetFunction::Other && filled($r->asset_function_lain)) {
                                        $ket .= ': '.$r->asset_function_lain;
                                    }
                                    if ($r->is_pinjaman) {
                                        $ket .= ' · Pinjaman sementara'.($r->batas_kembali ? ' s.d. '.$r->batas_kembali->format('d/m/Y') : '');
                                    }
                                } else {
                                    $ket = $r->status_return ? 'Status: '.$r->status_return->value : null;
                                }
                            @endphp
                            <tr>
                                <td>{{ $t?->tanggal_transaksi?->format('d/m/Y') }}</td>
                                <td>
                                    @if ($bolehSurat && $t)
                                        <a href="{{ route('transaksi.show', $t) }}" class="mono" style="color:#1d9e75">{{ $t->no_referensi }}</a>
                                    @else
                                        <span class="mono">{{ $t?->no_referensi }}</span>
                                    @endif
                                </td>
                                <td>{{ $r->bagian === 'deploy' ? 'Deploy / Move' : 'Return' }}</td>
                                <td>{{ $t?->pegawai?->nama ?? '-' }}</td>
                                <td>{{ $isi($ket) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        (function () {
            var url  = {{ Js::from($aset->urlScan()) }};
            var kode = {{ Js::from($aset->kode_aset) }};
            var nama = {{ Js::from($aset->nama_aset) }};
            var box  = document.getElementById('sh-qr');

            box.textContent = '';
            if (typeof QRCode === 'undefined') {
                box.textContent = 'QR tidak dapat dimuat. Periksa koneksi internet.';
            } else {
                new QRCode(box, { text: url, width: 132, height: 132, colorDark: '#16283f', colorLight: '#ffffff' });
            }

            // QR dengan alamat localhost tidak bisa dibuka dari HP
            var host = '';
            try { host = new URL(url).hostname; } catch (e) {}
            if (['localhost', '127.0.0.1', '[::1]', '::1'].indexOf(host) > -1) {
                document.getElementById('sh-warn').style.display = 'block';
            }

            document.getElementById('sh-print').addEventListener('click', function () {
                var canvas = box.querySelector('canvas'), img = box.querySelector('img');
                var src = canvas ? canvas.toDataURL('image/png') : (img ? img.src : '');
                var w = window.open('', '_blank', 'width=420,height=520');
                if (!w) { alert('Pop-up diblokir. Izinkan pop-up untuk mencetak label.'); return; }
                var d = w.document;
                d.title = 'Label ' + kode;
                d.body.style.cssText = 'font-family:sans-serif;text-align:center;padding:24px';
                var im = d.createElement('img'); im.src = src; im.width = 180; im.height = 180;
                var n = d.createElement('div'); n.style.cssText = 'font-weight:600;margin-top:10px'; n.textContent = nama;
                var c = d.createElement('div'); c.style.cssText = 'font-family:monospace;margin-top:4px'; c.textContent = kode;
                d.body.appendChild(im); d.body.appendChild(n); d.body.appendChild(c);
                setTimeout(function () { w.focus(); w.print(); }, 300);
            });
        })();
    </script>
</x-app-layout>
