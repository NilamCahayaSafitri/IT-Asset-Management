<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $transaksi->no_referensi }}</title>
    @include('transaksi._surat-style')
    <style>
        body{margin:0;background:#e9ecef}
        .tx-bar{font:13px/1.4 system-ui,sans-serif;text-align:center;padding:12px;background:#16283f;color:#fff}
        .tx-bar button{margin-left:10px;border:none;border-radius:6px;padding:6px 14px;font-weight:600;cursor:pointer;background:#1d9e75;color:#fff}
        .ea-page{margin:16px auto}
        @media print{
            body{background:#fff}
            .tx-bar{display:none}
            .ea-page{margin:0}
        }
    </style>
</head>
<body>
    <div class="tx-bar">Pada dialog cetak, pilih printer atau <b>Simpan sebagai PDF</b>. <button type="button" onclick="window.print()">Cetak</button></div>
    <div class="ea-page printable">@include('transaksi._surat', ['d' => $d])</div>
    <script>window.addEventListener('load', function () { setTimeout(function () { window.print(); }, 400); });</script>
</body>
</html>
