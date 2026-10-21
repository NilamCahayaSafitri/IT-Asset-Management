<x-app-layout>
    <x-page-head title="Aset Masuk" sub="Pencatatan penerimaan aset baru dari vendor">
        <button type="button" class="am-btn primary" id="am-toggle">+ Tambah Aset Masuk</button>
    </x-page-head>

    <style>
        .am-btn{border:none;border-radius:8px;padding:8px 16px;font-size:13px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;gap:6px;color:#fff;font-family:inherit}
        .am-btn.primary{background:#1d9e75}
        .am-btn.line{background:#fff;color:#16283f;border:1px solid #e4e7ec}
        .am-box{background:#fff;border:1px solid #e4e7ec;border-radius:10px;padding:18px;margin-bottom:16px}
        .am-title{font-weight:600;font-size:14px;margin-bottom:12px}
        .am-head-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;margin-bottom:16px}
        .am-lbl{display:block;font-size:11.5px;font-weight:600;margin-bottom:5px;color:#16283f}
        .am-req{color:#dc3545}
        .am-in{width:100%;border:1px solid #e4e7ec;border-radius:8px;padding:8px 10px;font-size:13px;background:#fbfbfc;font-family:inherit}
        .am-in:focus{outline:none;border-color:#1d9e75;box-shadow:0 0 0 3px #dcf1e8}
        .am-in:disabled{background:#f1f3f5;color:#66707d}
        .am-in.bad{border-color:#dc3545}
        .am-err{color:#dc3545;font-size:12px;margin-top:8px}
        .am-note{font-size:12px;color:#66707d;margin:0 0 12px}
        .am-row{position:relative;border:1px solid #e4e7ec;border-radius:10px;padding:14px;margin-bottom:10px;background:#fcfcfd}
        .am-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:10px;padding-right:24px}
        .am-wide{grid-column:span 2}
        @media (max-width:560px){.am-wide{grid-column:span 1}}
        .am-subtotal{font-size:12px;color:#66707d;margin-top:8px;text-align:right}
        .am-mhead.danger{background:#dc3545;color:#fff;border-bottom:none;border-radius:10px 10px 0 0}
        .am-mhead.danger .am-x{color:#fff}
        .am-btn.sq{width:40px;height:36px;padding:0}
        .am-btn.gray{background:#6c757d}
        .am-btn.red{background:#dc3545}
        .am-btn:disabled{opacity:.4;cursor:not-allowed}
        .am-btn svg{width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2}
        .am-remove{position:absolute;top:6px;right:8px;background:none;border:none;color:#dc3545;font-size:22px;line-height:1;cursor:pointer}
        .am-remove[disabled]{opacity:.25;cursor:not-allowed}
        .am-add{background:none;border:none;color:#1d9e75;font-weight:600;font-size:12.5px;cursor:pointer;padding:4px 0;font-family:inherit}
        .am-sum{display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:10px;margin-top:14px;padding-top:14px;border-top:1px solid #e4e7ec}
        .am-sum strong{font-size:14px}
        .am-search{display:flex;gap:8px;max-width:340px;margin-bottom:14px}
        .am-search input{flex:1;min-width:0;border:1px solid #e4e7ec;border-radius:8px;padding:8px 12px;font-size:13px;background:#fff}
        .am-search button{border:1px solid #e4e7ec;background:#fff;border-radius:8px;padding:8px 14px;font-size:13px;font-weight:600;cursor:pointer}

        .am-bg{display:none;position:fixed;inset:0;background:rgba(16,28,46,.5);z-index:2000;align-items:center;justify-content:center;padding:16px}
        .am-bg.open{display:flex}
        .am-modal{background:#fff;border-radius:10px;width:100%;max-width:760px;max-height:92vh;overflow-y:auto;box-shadow:0 20px 50px rgba(0,0,0,.3)}
        .am-mhead{display:flex;justify-content:space-between;align-items:center;padding:14px 18px;font-weight:600;font-size:15px;border-bottom:1px solid #e4e7ec}
        .am-x{background:none;border:none;font-size:22px;line-height:1;cursor:pointer;color:#66707d}
        .am-mbody{padding:18px}
        .am-kv{display:grid;grid-template-columns:130px 1fr;padding:7px 0;border-bottom:1px solid #e4e7ec;font-size:12.5px;gap:8px}
        .am-k{color:#66707d}
        .am-mono{font-family:'IBM Plex Mono',monospace;font-size:11.5px;color:#2c4a6e}
        .am-dt{width:100%;border-collapse:collapse;font-size:12.5px;margin-top:6px}
        .am-dt th{text-align:left;font-size:11px;text-transform:uppercase;color:#66707d;padding:7px 8px;border-bottom:1px solid #e4e7ec;white-space:nowrap}
        .am-dt td{padding:8px;border-bottom:1px solid #e4e7ec;vertical-align:top}
    </style>

    {{-- ============ Form Aset Masuk ============ --}}
    <div class="am-box" id="am-form-box" style="{{ $errors->any() ? '' : 'display:none' }}">
        <div class="am-title">Form Aset Masuk</div>

        <form method="POST" action="{{ route('aset-masuk.store') }}" id="am-form">
            @csrf

            <div class="am-head-grid">
                <div>
                    <label class="am-lbl">No. Aset Masuk</label>
                    <input class="am-in" value="{{ $nextNomor }}" disabled>
                </div>
                <div>
                    <label class="am-lbl" for="tanggal_masuk">Tanggal Masuk <span class="am-req">*</span></label>
                    <input class="am-in {{ $errors->has('tanggal_masuk') ? 'bad' : '' }}" type="date" id="tanggal_masuk" name="tanggal_masuk"
                           value="{{ old('tanggal_masuk', now()->format('Y-m-d')) }}">
                    @error('tanggal_masuk')<div class="am-err">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="am-lbl" for="id_vendor">Vendor <span class="am-req">*</span></label>
                    <select class="am-in {{ $errors->has('id_vendor') ? 'bad' : '' }}" id="id_vendor" name="id_vendor">
                        <option value="">Pilih vendor...</option>
                        @foreach ($vendorOptions as $id => $nama)
                            <option value="{{ $id }}" @selected((string) old('id_vendor') === (string) $id)>{{ $nama }}</option>
                        @endforeach
                    </select>
                    @error('id_vendor')<div class="am-err">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="am-lbl" for="no_po">No. PO / Kontrak</label>
                    <input class="am-in {{ $errors->has('no_po') ? 'bad' : '' }}" id="no_po" name="no_po" maxlength="50" placeholder="PO-2026-xxxx" value="{{ old('no_po') }}">
                    @error('no_po')<div class="am-err">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="am-lbl" for="afe_cc">AFE / CC</label>
                    <input class="am-in {{ $errors->has('afe_cc') ? 'bad' : '' }}" id="afe_cc" name="afe_cc" maxlength="50" placeholder="Kosongkan jika tidak ada" value="{{ old('afe_cc') }}">
                    @error('afe_cc')<div class="am-err">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="am-lbl" for="tahun">Year</label>
                    <input class="am-in {{ $errors->has('tahun') ? 'bad' : '' }}" type="number" id="tahun" name="tahun" min="2000" max="2100" value="{{ old('tahun', now()->format('Y')) }}">
                    @error('tahun')<div class="am-err">{{ $message }}</div>@enderror
                </div>
            </div>

            <p class="am-note" style="margin-top:-6px">
                PO / Kontrak, AFE / CC, dan Year tersimpan di penerimaan ini dan akan terisi otomatis di bagian
                Procurement Information pada surat transaksi saat aset ini di-Deploy. Kosongkan yang tidak ada.
            </p>

            <div class="am-title" style="margin-bottom:4px">Rincian Aset</div>
            <p class="am-note">
                Isi jumlah sesuai barang yang diterima. Misalnya 3 laptop dibuat 1 baris dengan jumlah 3, lalu otomatis dipecah
                menjadi 3 unit aset di Data Aset. Serial number diisi belakangan lewat Edit di Data Aset.
                Kategori boleh berbeda: tambahkan baris baru untuk tiap jenis barang.
            </p>

            @error('items')<div class="am-err" style="margin:0 0 10px">{{ $message }}</div>@enderror

            <div id="am-rows">
                @php $oldItems = old('items', [[]]); @endphp
                @foreach ($oldItems as $i => $row)
                    @include('aset-masuk._row', ['i' => $i, 'row' => $row])
                @endforeach
            </div>

            <button type="button" class="am-add" id="am-add-row">+ Tambah baris aset</button>

            <div class="am-sum">
                <div>Total: <strong id="am-total-unit">0</strong> unit &middot; <strong id="am-total-nilai">Rp 0</strong></div>
                <div style="display:flex;gap:8px">
                    <button type="button" class="am-btn line" id="am-cancel">Batal</button>
                    <button type="submit" class="am-btn primary">Simpan</button>
                </div>
            </div>
        </form>
    </div>

    <template id="am-row-template">
        @include('aset-masuk._row', ['i' => '__INDEX__', 'row' => []])
    </template>

    {{-- ============ Daftar Aset Masuk ============ --}}
    <form method="GET" action="{{ route('aset-masuk.index') }}" class="am-search">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari no. aset masuk, vendor, PO..." aria-label="Cari aset masuk">
        <button type="submit">Cari</button>
    </form>

    <div class="card card-flush">
        <div class="table-responsive">
            <table class="table table-hover table-itam tbl">
                <thead>
                    <tr>
                        <th>No. Aset Masuk</th>
                        <th>Tanggal</th>
                        <th>Vendor</th>
                        <th class="text-end num">Jumlah Unit</th>
                        <th class="text-end num">Total Nilai</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($asetMasuk as $item)
                        @php
                            $unitItem = $item->detail->flatMap(fn ($d) => $d->aset->pluck('id'));
                            $kodeSemua = $item->detail->flatMap(fn ($d) => $d->aset->pluck('kode_aset'))->sort()->values();
                            $rentang = $kodeSemua->isEmpty() ? '-' : ($kodeSemua->count() > 1 ? $kodeSemua->first().' s.d. '.$kodeSemua->last() : $kodeSemua->first());
                            $terpakai = $unitItem->intersect($asetTerpakai)->count();
                            $blockedHapus = $terpakai > 0
                                ? "{$terpakai} unit sudah punya riwayat transaksi, jadi aset masuk ini tidak bisa dihapus. Ubah status unit yang tidak dipakai menjadi Inactive lewat Data Aset."
                                : '';

                            $info = [
                                'no'      => $item->no_aset_masuk,
                                'tanggal' => $item->tanggal_masuk?->format('d/m/Y'),
                                'vendor'  => $item->vendor?->nama_vendor,
                                'po'      => $item->no_po,
                                'afe'     => $item->afe_cc,
                                'tahun'   => $item->tahun,
                                'admin'   => $item->admin?->name,
                                'unit'    => (int) $item->total_unit,
                                'total'   => 'Rp '.number_format((float) $item->total_nilai, 0, ',', '.'),
                                'items'   => $item->detail->map(function ($d) {
                                    $kode = $d->aset->pluck('kode_aset')->sort()->values();
                                    return [
                                        'nama'     => $d->nama_aset,
                                        'kategori' => $d->kategori?->nama_kategori,
                                        'merk'     => trim(($d->merk ?? '').' '.($d->model ?? '')),
                                        'spek'     => collect([$d->memory, $d->hard_disk])->filter()->implode(' / '),
                                        'jumlah'   => $d->jumlah,
                                        'harga'    => 'Rp '.number_format((float) $d->harga_satuan, 0, ',', '.'),
                                        'subtotal' => 'Rp '.number_format((float) $d->total_harga, 0, ',', '.'),
                                        'kode'     => $kode->isEmpty() ? '-' : ($kode->count() > 1 ? $kode->first().' - '.$kode->last() : $kode->first()),
                                    ];
                                })->values()->all(),
                            ];
                        @endphp
                        <tr data-info="{{ json_encode($info) }}">
                            <td><span class="mono">{{ $item->no_aset_masuk }}</span></td>
                            <td>{{ $item->tanggal_masuk?->format('d/m/Y') }}</td>
                            <td>{{ $item->vendor?->nama_vendor ?? '-' }}</td>
                            <td class="text-end num">{{ (int) $item->total_unit }}</td>
                            <td class="text-end num">Rp {{ number_format((float) $item->total_nilai, 0, ',', '.') }}</td>
                            <td>
                                <div class="action-icons">
                                    <button type="button" class="btn-icon js-detail" title="Detail" aria-label="Detail {{ $item->no_aset_masuk }}">
                                        <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </button>
                                    <button type="button" class="btn-icon danger js-delete" title="Hapus" aria-label="Hapus {{ $item->no_aset_masuk }}"
                                            data-action="{{ route('aset-masuk.destroy', $item) }}"
                                            data-nama="{{ $item->no_aset_masuk }}"
                                            data-unit="{{ $unitItem->count() }}"
                                            data-rentang="{{ $rentang }}"
                                            data-blocked="{{ $blockedHapus }}">
                                        <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="empty" style="text-align:center;padding:28px 10px;color:#66707d">
                            {{ request()->filled('q') ? 'Tidak ada aset masuk yang cocok dengan "'.request('q').'".' : 'Belum ada aset masuk. Klik "Tambah Aset Masuk" untuk mencatat penerimaan pertama.' }}
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div style="margin-top:14px">{{ $asetMasuk->links('pagination::bootstrap-5') }}</div>

    {{-- Modal detail --}}
    <div class="am-bg" id="am-detail-modal">
        <div class="am-modal" role="dialog" aria-modal="true" aria-labelledby="am-detail-title">
            <div class="am-mhead">
                <span id="am-detail-title">Detail Aset Masuk</span>
                <button type="button" class="am-x" id="am-detail-close" aria-label="Tutup">&times;</button>
            </div>
            <div class="am-mbody" id="am-detail-body"></div>
        </div>
    </div>

    {{-- Modal konfirmasi hapus --}}
    <div class="am-bg" id="am-del-modal">
        <div class="am-modal" style="max-width:420px" role="alertdialog" aria-modal="true" aria-labelledby="am-del-title">
            <div class="am-mhead danger">
                <span id="am-del-title">Konfirmasi Hapus</span>
                <button type="button" class="am-x" data-del-close aria-label="Tutup">&times;</button>
            </div>
            <form method="POST" id="am-del-form" action="#">
                @csrf
                @method('DELETE')
                <div class="am-mbody">
                    <p style="margin:0">Apakah Anda yakin ingin menghapus aset masuk <strong id="am-del-no"></strong>?</p>
                    <p style="margin:8px 0 0;font-size:12.5px;color:#66707d" id="am-del-info"></p>
                    <p class="am-err" id="am-del-warn" hidden></p>
                </div>
                <div style="display:flex;justify-content:flex-end;gap:8px;padding:0 18px 18px">
                    <button type="button" class="am-btn sq gray" data-del-close title="Batal" aria-label="Batal">
                        <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                    <button type="submit" class="am-btn sq red" id="am-del-submit" title="Hapus" aria-label="Hapus">
                        <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
            var formBox   = document.getElementById('am-form-box');
            var rowsEl    = document.getElementById('am-rows');
            var template  = document.getElementById('am-row-template');
            var nextIndex = rowsEl.querySelectorAll('[data-row]').length;

            // Year mengikuti tahun Tanggal Masuk selama belum diubah manual
            var tglMasuk = document.getElementById('tanggal_masuk'), tahunIn = document.getElementById('tahun'), tahunDirty = false;
            tahunIn.addEventListener('input', function () { tahunDirty = true; });
            tglMasuk.addEventListener('change', function () {
                if (!tahunDirty && tglMasuk.value) tahunIn.value = tglMasuk.value.slice(0, 4);
            });

            function rupiah(n) { return 'Rp ' + Math.round(n).toLocaleString('id-ID'); }

            function recalc() {
                var units = 0, nilai = 0;
                rowsEl.querySelectorAll('[data-row]').forEach(function (row) {
                    var q = parseInt(row.querySelector('.am-qty').value, 10) || 0;
                    var p = parseFloat(row.querySelector('.am-price').value) || 0;
                    row.querySelector('[data-sub]').textContent = rupiah(q * p);
                    units += q; nilai += q * p;
                });
                document.getElementById('am-total-unit').textContent = units;
                document.getElementById('am-total-nilai').textContent = rupiah(nilai);

                var removable = rowsEl.querySelectorAll('[data-row]').length > 1;
                rowsEl.querySelectorAll('[data-remove]').forEach(function (b) { b.disabled = !removable; });
            }

            function addRow() {
                var html = template.innerHTML.replace(/__INDEX__/g, nextIndex++);
                var holder = document.createElement('div');
                holder.innerHTML = html.trim();
                rowsEl.appendChild(holder.firstElementChild);
                recalc();
            }

            document.getElementById('am-toggle').addEventListener('click', function () {
                formBox.style.display = formBox.style.display === 'none' ? 'block' : 'none';
                if (formBox.style.display === 'block') formBox.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
            document.getElementById('am-cancel').addEventListener('click', function () { formBox.style.display = 'none'; });
            document.getElementById('am-add-row').addEventListener('click', addRow);

            rowsEl.addEventListener('click', function (e) {
                var btn = e.target.closest('[data-remove]');
                if (!btn || btn.disabled) return;
                btn.closest('[data-row]').remove();
                recalc();
            });
            // Harga: tampilkan dengan titik ribuan, simpan angka murni di input hidden
            rowsEl.addEventListener('input', function (e) {
                if (e.target.classList.contains('am-price-view')) {
                    var digits = e.target.value.replace(/\D/g, '').replace(/^0+(?=\d)/, '');
                    e.target.value = digits ? Number(digits).toLocaleString('id-ID') : '';
                    e.target.closest('.am-cell').querySelector('.am-price').value = digits;
                }
                recalc();
            });
            recalc();

            // ---------- Modal detail ----------
            var modal = document.getElementById('am-detail-modal');
            var body  = document.getElementById('am-detail-body');

            function el(tag, cls, text) {
                var e = document.createElement(tag);
                if (cls) e.className = cls;
                if (text !== undefined) e.textContent = (text === null || text === '') ? '-' : text;
                return e;
            }

            function kv(label, value, mono) {
                var r = el('div', 'am-kv');
                r.appendChild(el('div', 'am-k', label));
                r.appendChild(el('div', mono ? 'am-mono' : '', value));
                return r;
            }

            function openDetail(info) {
                body.innerHTML = '';
                body.appendChild(kv('No. Aset Masuk', info.no, true));
                body.appendChild(kv('Tanggal Masuk', info.tanggal));
                body.appendChild(kv('Vendor', info.vendor));
                body.appendChild(kv('No. PO / Kontrak', info.po));
                body.appendChild(kv('AFE / CC', info.afe));
                body.appendChild(kv('Year', info.tahun));
                body.appendChild(kv('Dicatat oleh', info.admin));
                body.appendChild(kv('Jumlah Unit', info.unit + ' unit'));
                body.appendChild(kv('Total Nilai', info.total));

                var h = el('div', '', 'Rincian Aset');
                h.style.cssText = 'font-weight:600;font-size:13px;margin:18px 0 4px';
                body.appendChild(h);

                var wrap = el('div'); wrap.style.overflowX = 'auto';
                var t = el('table', 'am-dt');
                var thead = el('thead'); var hr = el('tr');
                ['Nama', 'Kategori', 'Merk / Model', 'Spesifikasi', 'Jml', 'Harga Satuan', 'Subtotal', 'Kode Aset'].forEach(function (c) { hr.appendChild(el('th', '', c)); });
                thead.appendChild(hr); t.appendChild(thead);
                var tb = el('tbody');
                info.items.forEach(function (d) {
                    var tr = el('tr');
                    [d.nama, d.kategori, d.merk, d.spek, d.jumlah, d.harga, d.subtotal].forEach(function (v) { tr.appendChild(el('td', '', v)); });
                    tr.appendChild(el('td', 'am-mono', d.kode));
                    tb.appendChild(tr);
                });
                t.appendChild(tb); wrap.appendChild(t); body.appendChild(wrap);

                modal.classList.add('open');
            }

            document.querySelectorAll('.js-detail').forEach(function (b) {
                b.addEventListener('click', function () { openDetail(JSON.parse(b.closest('tr').dataset.info)); });
            });
            document.getElementById('am-detail-close').addEventListener('click', function () { modal.classList.remove('open'); });
            modal.addEventListener('click', function (e) { if (e.target === modal) modal.classList.remove('open'); });
            document.addEventListener('keydown', function (e) { if (e.key === 'Escape') modal.classList.remove('open'); });

            // ---------- Modal hapus ----------
            var delModal  = document.getElementById('am-del-modal');
            var delForm   = document.getElementById('am-del-form');
            var delNo     = document.getElementById('am-del-no');
            var delInfo   = document.getElementById('am-del-info');
            var delWarn   = document.getElementById('am-del-warn');
            var delSubmit = document.getElementById('am-del-submit');

            document.querySelectorAll('.js-delete').forEach(function (b) {
                b.addEventListener('click', function () {
                    var blocked = b.dataset.blocked || '';
                    delForm.action = b.dataset.action;
                    delNo.textContent = b.dataset.nama;
                    delInfo.textContent = blocked ? '' : (b.dataset.unit + ' unit aset (' + b.dataset.rentang + ') yang dibuat dari penerimaan ini akan ikut dihapus.');
                    delWarn.hidden = blocked === '';
                    delWarn.textContent = blocked;
                    delSubmit.disabled = blocked !== '';
                    delModal.classList.add('open');
                });
            });
            document.querySelectorAll('[data-del-close]').forEach(function (b) {
                b.addEventListener('click', function () { delModal.classList.remove('open'); });
            });
            delModal.addEventListener('click', function (e) { if (e.target === delModal) delModal.classList.remove('open'); });
            document.addEventListener('keydown', function (e) { if (e.key === 'Escape') delModal.classList.remove('open'); });
        })();
    </script>
</x-app-layout>
