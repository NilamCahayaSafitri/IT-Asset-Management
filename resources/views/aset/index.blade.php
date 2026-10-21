<x-app-layout>
    <x-page-head title="Data Aset" sub="Master data seluruh aset ICT. Aset baru dibuat lewat menu Aset Masuk." />

    <style>

        .as-toolbar{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:14px;align-items:center}
        .as-toolbar input,.as-toolbar select{border:1px solid #e4e7ec;border-radius:8px;padding:8px 11px;font-size:13px;background:#fff;font-family:inherit}
        .as-toolbar input[type=search]{width:240px;max-width:100%}
        .as-toolbar button{border:1px solid #e4e7ec;background:#fff;border-radius:8px;padding:8px 14px;font-size:13px;font-weight:600;cursor:pointer}
        .as-toolbar a{font-size:12.5px;color:#66707d}

        .as-drawer-bg{display:none;position:fixed;inset:0;background:rgba(16,28,46,.4);z-index:1990}
        .as-drawer-bg.open{display:block}
        .as-drawer{position:fixed;top:0;right:0;height:100%;width:380px;max-width:100%;background:#fff;box-shadow:-8px 0 24px rgba(16,28,46,.15);z-index:1991;transform:translateX(100%);transition:transform .25s;overflow-y:auto;padding:20px}
        .as-drawer.open{transform:translateX(0)}
        .as-drawer-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px}
        .as-drawer-title{font-weight:700;font-size:16px}
        .as-qr{background:#f5f6f8;border-radius:10px;padding:16px;display:flex;justify-content:center;margin-bottom:16px}
        .as-kv{display:grid;grid-template-columns:120px 1fr;padding:8px 0;border-bottom:1px solid #e4e7ec;font-size:12.5px;gap:8px}
        .as-k{color:#66707d}
        .as-mono{font-family:'IBM Plex Mono',monospace;font-size:11.5px;color:#2c4a6e}
        .as-sub{font-weight:600;font-size:13px;margin:18px 0 6px}
        .as-hist{font-size:12.5px;padding:6px 0;border-bottom:1px solid #e4e7ec;display:flex;justify-content:space-between;gap:8px}
        .as-actions{display:flex;gap:8px;margin-top:18px}
        .as-actions .km-btn{flex:1}
        .as-qr-center{text-align:center}
    </style>

    {{-- Pencarian dan filter --}}
    <form method="GET" action="{{ route('aset.index') }}" class="as-toolbar">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama / kode / serial..." aria-label="Cari aset">

        <select name="kategori" onchange="this.form.submit()" aria-label="Filter kategori">
            <option value="">Semua Kategori</option>
            @foreach ($kategoriOptions as $id => $nama)
                <option value="{{ $id }}" @selected(request('kategori') == $id)>{{ $nama }}</option>
            @endforeach
        </select>

        <select name="ruangan" onchange="this.form.submit()" aria-label="Filter lokasi">
            <option value="">Semua Lokasi</option>
            @foreach ($ruanganOptions as $id => $nama)
                <option value="{{ $id }}" @selected(request('ruangan') == $id)>{{ $nama }}</option>
            @endforeach
        </select>

        <select name="status" onchange="this.form.submit()" aria-label="Filter status">
            <option value="">Semua Status</option>
            @foreach ($statusOptions as $value => $label)
                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
            @endforeach
        </select>

        <button type="submit">Cari</button>
        @if (request()->hasAny(['q', 'kategori', 'ruangan', 'status']))
            <a href="{{ route('aset.index') }}">Reset</a>
        @endif
    </form>

    <div class="card card-flush">
        <div class="table-responsive">
            <table class="table table-hover table-itam tbl">
                <thead>
                    <tr>
                        <th>Kode Aset</th>
                        <th>Nama Aset</th>
                        <th>Kategori</th>
                        <th>Lokasi</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($aset as $item)
                        @php
                            $manualValues = array_keys($statusManual);

                            $blockedHapus = $item->status?->sedangDipakai()
                                ? 'Aset ini sedang '.$item->status->label().'. Return dulu lewat transaksi sebelum dihapus.'
                                : ($item->transaksiDetail->isNotEmpty()
                                    ? 'Aset ini punya riwayat transaksi, jadi tidak bisa dihapus. Ubah statusnya menjadi Inactive jika sudah tidak dipakai.'
                                    : '');
                            $riwayat = $item->transaksiDetail
                                ->filter(fn ($d) => $d->transaksi)
                                ->sortByDesc(fn ($d) => (string) $d->transaksi->tanggal_transaksi)
                                ->take(5)
                                ->map(fn ($d) => [
                                    'tanggal' => \Illuminate\Support\Carbon::parse($d->transaksi->tanggal_transaksi)->format('d/m/Y'),
                                    'ref'     => $d->transaksi->no_referensi,
                                    'bagian'  => $d->bagian === 'deploy' ? 'Deploy' : 'Return',
                                ])->values()->all();

                            [$sBg, $sFg] = $item->status?->warna() ?? ['#e7e9ec', '#5b6572'];

                            $info = [
                                'kode'     => $item->kode_aset,
                                'nama'     => $item->nama_aset,
                                'kategori' => $item->kategori?->nama_kategori,
                                'serial'   => $item->serial_number,
                                'merk'     => $item->merk,
                                'model'    => $item->model,
                                'memory'   => $item->memory,
                                'hard_disk'=> $item->hard_disk,
                                'vendor'   => $item->asetMasukDetail?->asetMasuk?->vendor?->nama_vendor,
                                'tgl'      => $item->tanggal_perolehan?->format('d/m/Y'),
                                'harga'    => $item->harga !== null ? 'Rp '.number_format((float) $item->harga, 0, ',', '.') : null,
                                'umur'     => $item->umur !== null ? $item->umur.' tahun' : null,
                                'lokasi'   => $item->ruangan?->nama_ruangan,
                                'status'   => $item->status?->label(),
                                'status_bg'=> $sBg,
                                'status_fg'=> $sFg,
                                'riwayat'  => $riwayat,
                            ];

                            $values = [
                                'kode_view'         => $item->kode_aset,
                                'nama_aset'         => $item->nama_aset,
                                'id_kategori'       => $item->id_kategori,
                                'merk'              => $item->merk,
                                'model'             => $item->model,
                                'serial_number'     => $item->serial_number,
                                'memory'            => $item->memory,
                                'hard_disk'         => $item->hard_disk,
                                'tanggal_perolehan' => $item->tanggal_perolehan?->format('Y-m-d'),
                                'harga'             => $item->harga !== null ? (float) $item->harga : null,
                                'umur'              => $item->umur,
                                'id_ruangan'        => $item->id_ruangan,
                                // hanya status yang boleh diubah manual; selain itu dikosongkan = tidak diubah
                                'status'            => in_array($item->status?->value, $manualValues, true) ? $item->status->value : '',
                            ];
                        @endphp
                        <tr data-info="{{ json_encode($info) }}">
                            <td><span class="mono">{{ $item->kode_aset }}</span></td>
                            <td>{{ $item->nama_aset }}</td>
                            <td>{{ $item->kategori?->nama_kategori ?? '-' }}</td>
                            <td>{{ $item->ruangan?->nama_ruangan ?? '-' }}</td>
                            <td><x-status-badge :status="$item->status" /></td>
                            <td>
                                <div class="action-icons">
                                    <button type="button" class="btn-icon js-detail" title="Detail" aria-label="Detail {{ $item->kode_aset }}">
                                        <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </button>
                                    <button type="button" class="btn-icon js-edit" title="Edit" aria-label="Ubah {{ $item->kode_aset }}"
                                            data-action="{{ route('aset.update', $item) }}"
                                            data-values="{{ json_encode($values) }}">
                                        <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </button>
                                    <button type="button" class="btn-icon js-qr" title="QR Code" aria-label="QR {{ $item->kode_aset }}">
                                        <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><line x1="14" y1="14" x2="21" y2="14"/><line x1="14" y1="21" x2="21" y2="21"/><line x1="21" y1="14" x2="21" y2="21"/></svg>
                                    </button>
                                    <button type="button" class="btn-icon danger js-delete" title="Hapus" aria-label="Hapus {{ $item->kode_aset }}"
                                            data-action="{{ route('aset.destroy', $item) }}"
                                            data-nama="{{ $item->kode_aset }} - {{ $item->nama_aset }}"
                                            data-blocked="{{ $blockedHapus }}">
                                        <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="empty" style="text-align:center;padding:28px 10px;color:#66707d">
                            {{ request()->hasAny(['q', 'kategori', 'ruangan', 'status']) ? 'Tidak ada aset yang cocok dengan pencarian atau filter.' : 'Belum ada aset. Aset dibuat lewat menu Aset Masuk.' }}
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div style="margin-top:14px">{{ $aset->links('pagination::bootstrap-5') }}</div>

    {{-- Modal tambah / ubah (komponen bersama dengan Master Data) --}}
    <x-master-modals :store-url="'#'" entity="Aset" :wide="true">
        <div class="km-field">
            <label class="km-label" for="f-kode_view">Kode Aset</label>
            <input id="f-kode_view" name="kode_view" class="km-input" readonly value="{{ old('kode_view') }}" style="background:#f1f3f5">
        </div>
        <x-master-field name="nama_aset" label="Nama Aset" :required="true" maxlength="255" placeholder="Contoh: Laptop Dell Latitude" />
        <div class="km-row">
            <x-master-field name="id_kategori" label="Kategori" type="select" :required="true" :options="$kategoriOptions" />
            <x-master-field name="id_ruangan" label="Lokasi" type="select" :options="$ruanganOptions" />
        </div>
        <div class="km-row">
            <x-master-field name="merk" label="Merk" maxlength="100" />
            <x-master-field name="model" label="Model" maxlength="100" />
        </div>
        <x-master-field name="serial_number" label="Serial Number" maxlength="100" />
        <div class="km-row">
            <x-master-field name="memory" label="Memory" maxlength="50" placeholder="16GB" />
            <x-master-field name="hard_disk" label="Hard Disk" maxlength="50" placeholder="512GB SSD" />
        </div>
        <div class="km-row">
            <x-master-field name="tanggal_perolehan" label="Tanggal Perolehan" type="date" />
            <x-master-field name="umur" label="Umur Ekonomis (tahun)" type="number" min="0" max="100" step="1" />
        </div>
        <x-master-field name="harga" label="Harga (Rp)" type="number" min="0" step="0.01"
                        hint="Ketik angka saja, tanpa titik atau koma pemisah ribuan (contoh: 25000000)." />
        <x-master-field name="status" label="Status" type="select" :options="$statusManual"
                        hint="Kosongkan jika tidak diubah. Aset yang sedang Deployed / Borrowed diubah lewat transaksi Return." />
    </x-master-modals>

    {{-- Drawer detail --}}
    <div class="as-drawer-bg" id="as-drawer-bg"></div>
    <aside class="as-drawer" id="as-drawer" aria-label="Detail aset">
        <div class="as-drawer-head">
            <div class="as-drawer-title">Detail Aset</div>
            <button type="button" class="km-x" id="as-drawer-close" aria-label="Tutup">&times;</button>
        </div>
        <div class="as-qr" id="as-drawer-qr"></div>
        <div id="as-drawer-body"></div>
        <div class="as-actions">
            <button type="button" class="km-btn line" id="as-drawer-edit">Edit</button>
            <button type="button" class="km-btn primary" id="as-drawer-qrbtn">Cetak Label QR</button>
        </div>
    </aside>

    {{-- Modal QR --}}
    <div class="km-backdrop" id="as-qr-modal">
        <div class="km-box" role="dialog" aria-modal="true" aria-labelledby="as-qr-title">
            <div class="km-head plain">
                <span id="as-qr-title">QR Code Aset</span>
                <button type="button" class="km-x" data-as-close aria-label="Tutup">&times;</button>
            </div>
            <div class="km-body as-qr-center">
                <div class="as-qr" id="as-qr-box"></div>
                <div style="font-weight:600;font-size:13px" id="as-qr-name"></div>
                <div class="as-mono" id="as-qr-code"></div>
            </div>
            <div class="km-foot">
                <button type="button" class="km-btn line" data-as-close>Tutup</button>
                <button type="button" class="km-btn primary" id="as-qr-print">Cetak Label</button>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        (function () {
            var drawer    = document.getElementById('as-drawer');
            var drawerBg  = document.getElementById('as-drawer-bg');
            var drawerBody = document.getElementById('as-drawer-body');
            var drawerQr  = document.getElementById('as-drawer-qr');
            var qrModal   = document.getElementById('as-qr-modal');
            var qrBox     = document.getElementById('as-qr-box');
            var current   = null; // { info, row }

            function makeQR(el, text, size) {
                el.innerHTML = '';
                if (typeof QRCode === 'undefined') {
                    el.textContent = 'QR tidak dapat dimuat. Periksa koneksi internet.';
                    return;
                }
                new QRCode(el, { text: text, width: size, height: size, colorDark: '#16283f', colorLight: '#ffffff' });
            }

            function kv(label, value, mono) {
                var row = document.createElement('div'); row.className = 'as-kv';
                var k = document.createElement('div'); k.className = 'as-k'; k.textContent = label;
                var v = document.createElement('div'); if (mono) v.className = 'as-mono';
                v.textContent = (value === null || value === undefined || value === '') ? '-' : value;
                row.appendChild(k); row.appendChild(v);
                return row;
            }

            function infoOf(btn) {
                var row = btn.closest('tr');
                return { info: JSON.parse(row.dataset.info), row: row };
            }

            function openDrawer(ctx) {
                current = ctx;
                var i = ctx.info;
                drawerBody.innerHTML = '';
                [['Kode Aset', i.kode, true], ['Nama', i.nama], ['Kategori', i.kategori], ['Serial Number', i.serial, true],
                 ['Merk', i.merk], ['Model', i.model], ['Memory', i.memory], ['Hard Disk', i.hard_disk],
                 ['Vendor', i.vendor], ['Tgl Perolehan', i.tgl], ['Harga', i.harga], ['Umur Ekonomis', i.umur],
                 ['Lokasi', i.lokasi]].forEach(function (f) { drawerBody.appendChild(kv(f[0], f[1], f[2])); });

                var st = document.createElement('div'); st.className = 'as-kv';
                var sk = document.createElement('div'); sk.className = 'as-k'; sk.textContent = 'Status';
                var sv = document.createElement('div');
                var badge = document.createElement('span');
                badge.textContent = i.status || '-';
                badge.style.cssText = 'display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;background:' + i.status_bg + ';color:' + i.status_fg;
                sv.appendChild(badge); st.appendChild(sk); st.appendChild(sv);
                drawerBody.appendChild(st);

                var h = document.createElement('div'); h.className = 'as-sub'; h.textContent = 'Riwayat Transaksi';
                drawerBody.appendChild(h);
                if (!i.riwayat.length) {
                    var none = document.createElement('div'); none.className = 'as-k'; none.style.fontSize = '12.5px';
                    none.textContent = 'Belum ada riwayat transaksi.';
                    drawerBody.appendChild(none);
                } else {
                    i.riwayat.forEach(function (r) {
                        var line = document.createElement('div'); line.className = 'as-hist';
                        var a = document.createElement('span'); a.textContent = r.tanggal + '  ' + r.bagian;
                        var b = document.createElement('span'); b.className = 'as-mono'; b.textContent = r.ref;
                        line.appendChild(a); line.appendChild(b);
                        drawerBody.appendChild(line);
                    });
                }

                makeQR(drawerQr, 'ITAM:' + i.kode, 120);
                drawer.classList.add('open'); drawerBg.classList.add('open');
            }

            function closeDrawer() { drawer.classList.remove('open'); drawerBg.classList.remove('open'); }

            function openQr(ctx) {
                current = ctx;
                document.getElementById('as-qr-name').textContent = ctx.info.nama;
                document.getElementById('as-qr-code').textContent = ctx.info.kode;
                makeQR(qrBox, 'ITAM:' + ctx.info.kode, 150);
                qrModal.classList.add('open');
            }

            function printLabel(info, container) {
                var canvas = container.querySelector('canvas');
                var img = container.querySelector('img');
                var src = canvas ? canvas.toDataURL('image/png') : (img ? img.src : '');
                var w = window.open('', '_blank', 'width=420,height=520');
                if (!w) { alert('Pop-up diblokir. Izinkan pop-up untuk mencetak label.'); return; }
                var d = w.document;
                d.title = 'Label ' + info.kode;
                d.body.style.cssText = 'font-family:sans-serif;text-align:center;padding:24px';
                var im = d.createElement('img'); im.src = src; im.width = 180; im.height = 180;
                var n = d.createElement('div'); n.style.cssText = 'font-weight:600;margin-top:10px'; n.textContent = info.nama;
                var c = d.createElement('div'); c.style.cssText = 'font-family:monospace;margin-top:4px'; c.textContent = info.kode;
                d.body.appendChild(im); d.body.appendChild(n); d.body.appendChild(c);
                setTimeout(function () { w.focus(); w.print(); }, 300);
            }

            document.querySelectorAll('.js-detail').forEach(function (b) { b.addEventListener('click', function () { openDrawer(infoOf(b)); }); });
            document.querySelectorAll('.js-qr').forEach(function (b) { b.addEventListener('click', function () { openQr(infoOf(b)); }); });

            document.getElementById('as-drawer-close').addEventListener('click', closeDrawer);
            drawerBg.addEventListener('click', closeDrawer);
            document.getElementById('as-drawer-edit').addEventListener('click', function () {
                closeDrawer();
                if (current) current.row.querySelector('.js-edit').click();
            });
            document.getElementById('as-drawer-qrbtn').addEventListener('click', function () {
                var ctx = current; closeDrawer(); if (ctx) openQr(ctx);
            });

            document.querySelectorAll('[data-as-close]').forEach(function (b) {
                b.addEventListener('click', function () { qrModal.classList.remove('open'); });
            });
            qrModal.addEventListener('click', function (e) { if (e.target === qrModal) qrModal.classList.remove('open'); });
            document.getElementById('as-qr-print').addEventListener('click', function () { if (current) printLabel(current.info, qrBox); });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') { closeDrawer(); qrModal.classList.remove('open'); }
            });
        })();
    </script>
</x-app-layout>
