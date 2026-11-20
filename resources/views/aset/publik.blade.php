<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $aset->kode_aset }} - IT Asset Management</title>
    {{-- Halaman ini sengaja mandiri (tanpa Vite / CDN) supaya tampil benar di HP, termasuk saat server Vite (npm run dev) hanya jalan di komputer. --}}
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{min-height:100vh;font-family:Inter,system-ui,-apple-system,"Segoe UI",Roboto,Arial,sans-serif;background:linear-gradient(160deg,#101c2e,#20344f);color:#1a1f29;display:flex;justify-content:center;align-items:flex-start;padding:22px 14px;-webkit-text-size-adjust:100%}
        .kartu{background:#fff;width:100%;max-width:420px;border-radius:16px;padding:24px 22px 20px;box-shadow:0 20px 50px rgba(0,0,0,.25)}
        .brand{text-align:center;margin-bottom:22px}
        .brand img{display:block;margin:0 auto 10px;height:46px;width:auto;max-width:100%}
        .brand b{font-size:15px;letter-spacing:.2px}
        .kode{font-family:"IBM Plex Mono",ui-monospace,Menlo,Consolas,monospace;font-size:22px;font-weight:600;color:#2c4a6e;text-align:center;letter-spacing:.3px}
        .nama{font-size:18px;font-weight:700;text-align:center;line-height:1.3;margin:6px 0 20px}
        .baris{display:flex;justify-content:space-between;gap:18px;padding:12px 0;border-top:1px solid #e4e7ec;font-size:14px}
        .baris:last-of-type{border-bottom:1px solid #e4e7ec}
        .baris .k{color:#66707d;flex-shrink:0}
        .baris .v{text-align:right;font-weight:500;word-break:break-word}
        .mono{font-family:"IBM Plex Mono",ui-monospace,Menlo,Consolas,monospace;font-size:13px}
        .kaki{margin-top:18px;text-align:center;font-size:11.5px;color:#8a929d;line-height:1.7}
    </style>
</head>
<body>
<main class="kartu">
    <div class="brand">
        <img src="{{ asset('images/logo-pge.png') }}" alt="PEMA Global Energi">
        <b>IT Asset Management</b>
    </div>

    <div class="kode">{{ $aset->kode_aset }}</div>
    <div class="nama">{{ $aset->nama_aset }}</div>

    @php
        $bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $tgl = $aset->tanggal_perolehan
            ? $aset->tanggal_perolehan->day.' '.$bulan[$aset->tanggal_perolehan->month - 1].' '.$aset->tanggal_perolehan->year
            : null;
        $harga = $aset->harga !== null ? 'Rp '.number_format((float) $aset->harga, 0, ',', '.') : null;

        $baris = [
            'Kategori'        => [$aset->kategori?->nama_kategori, false],
            'Serial Number'   => [$aset->serial_number, true],
            'Merk'            => [$aset->merk, false],
            'Model'           => [$aset->model, false],
            'Memory'          => [$aset->memory, false],
            'Hard Disk'       => [$aset->hard_disk, false],
            'Vendor'          => [$aset->asetMasukDetail?->asetMasuk?->vendor?->nama_vendor, false],
            'Tgl Perolehan'   => [$tgl, false],
            'Harga'           => [$harga, false],
        ];
    @endphp

    @foreach ($baris as $label => $isi)
        <div class="baris">
            <span class="k">{{ $label }}</span>
            <span class="v{{ $isi[1] ? ' mono' : '' }}">{{ filled($isi[0]) ? $isi[0] : 'N/A' }}</span>
        </div>
    @endforeach

    <div class="kaki">PGE ICT Area Operations &copy; {{ date('Y') }}</div>
</main>
</body>
</html>
