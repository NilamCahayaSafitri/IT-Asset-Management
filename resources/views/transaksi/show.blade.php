<x-app-layout>
    <x-page-head title="Surat Transaksi Aset" :sub="'Equipment Allocation Form - '.$transaksi->no_referensi">
        <a href="{{ route('transaksi.index') }}" class="tx-noprint" style="display:inline-flex;align-items:center;gap:6px;border:1px solid #e4e7ec;background:#fff;border-radius:8px;padding:8px 16px;font-size:13px;font-weight:600;color:#16283f;text-decoration:none">&larr; Kembali</a>
    </x-page-head>

    @include('transaksi._surat-style')

    <style>
        .tx-actions{max-width:794px;margin:14px auto 0;display:flex;justify-content:flex-end;gap:10px;flex-wrap:wrap}
        .tx-btn{border:none;border-radius:8px;padding:9px 18px;font-size:13px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:6px;color:#fff;font-family:inherit;text-decoration:none}
        .tx-btn.primary{background:#1d9e75}
        .tx-btn.line{background:#fff;color:#16283f;border:1px solid #e4e7ec}
        .tx-btn.warn{background:#c8631b}
        @media print{
            .sidebar,.sidebar-overlay,.mobilebar,.tx-noprint,.tx-actions,.toast-container{display:none !important}
            .app{display:block !important;height:auto !important;overflow:visible !important}
            .main{height:auto !important;overflow:visible !important}
            .page{overflow:visible !important;padding:0 !important}
            .page-head{display:none !important}
        }
    </style>

    <div class="ea-scale"><div class="ea-page printable" id="ea-txn">@include('transaksi._surat', ['d' => $d])</div></div>

    <div class="tx-actions">
        @if ($transaksi->status_dokumen !== 'Selesai')
            <form method="POST" action="{{ route('transaksi.selesai', $transaksi) }}"
                  onsubmit="return confirm('Tandai surat ini Selesai (sudah ditandatangani)?')">
                @csrf
                @method('PATCH')
                <button type="submit" class="tx-btn warn">Tandai Selesai (sudah TTD)</button>
            </form>
        @else
            <span style="align-self:center;font-size:12.5px;color:#3c8558;font-weight:600">&#10003; Selesai</span>
        @endif
        <button type="button" class="tx-btn line" onclick="window.print()">Cetak</button>
        <a href="{{ route('transaksi.pdf', $transaksi) }}" class="tx-btn primary">Unduh PDF</a>
    </div>

    <script>
        // Kecilkan pratinjau agar muat di layar (lebar kertas A4 = 794px)
        (function () {
            var page = document.getElementById('ea-txn');
            var host = page.closest('.page');
            function fit() {
                var cs = getComputedStyle(host);
                var avail = host.clientWidth - (parseFloat(cs.paddingLeft) || 0) - (parseFloat(cs.paddingRight) || 0);
                page.style.zoom = Math.min(1, avail / 794);
            }
            window.addEventListener('resize', fit);
            window.addEventListener('beforeprint', function () { page.style.zoom = 1; });
            window.addEventListener('afterprint', fit);
            fit();
        })();
    </script>
</x-app-layout>
