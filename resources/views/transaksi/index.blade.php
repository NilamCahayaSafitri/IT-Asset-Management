<x-app-layout>
    <x-page-head title="Transaksi">
        <button type="button" class="tx-btn primary" id="tx-toggle">+ Buat Transaksi</button>
    </x-page-head>

    <style>
        .tx-btn{border:none;border-radius:8px;padding:9px 18px;font-size:13px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:6px;color:#fff;font-family:inherit;text-decoration:none}
        .tx-btn.primary{background:#1d9e75}
        .tx-btn.line{background:#fff;color:#16283f;border:1px solid #e4e7ec}
        .tx-box{background:#fff;border:1px solid #e4e7ec;border-radius:10px;padding:20px;margin-bottom:16px}
        .tx-title{font-weight:700;font-size:15px;margin-bottom:8px}
        .tx-info{font-size:12.5px;color:#66707d;background:#f5f6f8;border-radius:8px;padding:10px 12px;margin-bottom:16px;line-height:1.5}
        .tx-alert{border:1px solid #f0b4ad;background:#fbe4e1;color:#9b2f25;border-radius:8px;padding:10px 14px;margin-bottom:16px;font-size:12.5px}
        .tx-alert ul{margin:6px 0 0;padding-left:18px}
        .tx-sec{border:1px solid #e4e7ec;border-radius:10px;padding:14px;margin-top:14px}
        .tx-sec-title{font-weight:700;font-size:13px;margin-bottom:10px;color:#16283f}
        .tx-sec-title em{font-weight:400;color:#66707d;font-size:12px}
        .tx-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px}
        .tx-grid.two{grid-template-columns:1fr 1fr}
        @media (max-width:900px){.tx-grid,.tx-grid.two{grid-template-columns:1fr}}
        .tx-field label{display:block;font-size:11.5px;font-weight:600;margin-bottom:5px;color:#16283f}
        .tx-field .hint{font-weight:400;color:#66707d}
        .tx-in{width:100%;border:1px solid #e4e7ec;border-radius:8px;padding:8px 10px;font-size:13px;background:#fbfbfc;font-family:inherit}
        .tx-in:focus{outline:none;border-color:#1d9e75;box-shadow:0 0 0 3px #dcf1e8}
        .tx-in.ro{background:#f1f3f5;color:#444}
        .tx-in.mono{font-family:'IBM Plex Mono',monospace;font-size:12px}
        .tx-pills{display:flex;flex-wrap:wrap;gap:8px}
        .tx-pill{display:inline-flex;align-items:center;gap:6px;border:1px solid #e4e7ec;border-radius:20px;padding:6px 14px;font-size:13px;font-weight:600;cursor:pointer;background:#fff}
        .tx-pill input{accent-color:#1d9e75}
        .tx-combo{position:relative}
        .tx-list{display:none;position:absolute;left:0;right:0;top:100%;z-index:50;background:#fff;border:1px solid #e4e7ec;border-radius:8px;box-shadow:0 8px 24px rgba(16,28,46,.12);max-height:240px;overflow-y:auto;margin-top:4px}
        .tx-list.open{display:block}
        .tx-item{padding:8px 12px;cursor:pointer;font-size:13px;display:flex;flex-direction:column}
        .tx-item:hover{background:#f5f6f8}
        .tx-item span{font-size:11.5px;color:#66707d}
        .tx-empty,.tx-add{padding:9px 12px;font-size:12.5px;color:#66707d;display:block}
        .tx-add{color:#1d9e75;font-weight:600;border-top:1px solid #e4e7ec;text-decoration:none}
        .tx-eqwrap{overflow-x:auto;margin-top:10px}
        .tx-eq{border-collapse:collapse;width:100%;min-width:1040px}
        .tx-eq th{background:#f5f6f8;border:1px solid #e4e7ec;font-size:11px;padding:7px 6px;text-align:left;color:#66707d;white-space:nowrap}
        .tx-eq td{border:1px solid #e4e7ec;padding:5px;vertical-align:top}
        .tx-eq td.del{width:30px;text-align:center;vertical-align:middle}
        .tx-eq .x{cursor:pointer;color:#dc3545;font-size:20px;line-height:1}
        .tx-sthint{font-size:11px;color:#66707d;margin-top:4px}
        .tx-addrow{background:none;border:none;color:#1d9e75;font-weight:600;font-size:12.5px;cursor:pointer;padding:8px 0 0;font-family:inherit}
        .tx-foot{font-size:11px;color:#66707d;margin-top:6px;font-style:italic}
        .tx-emptyhint{margin-top:14px;padding:16px;text-align:center;color:#66707d;background:#f5f6f8;border-radius:8px;font-size:12.5px}
        .tx-actions{margin-top:16px;display:flex;justify-content:flex-end;gap:10px}
        .tx-search{display:flex;gap:8px;max-width:360px;margin-bottom:14px}
        .tx-search input{flex:1;min-width:0;border:1px solid #e4e7ec;border-radius:8px;padding:8px 12px;font-size:13px;background:#fff}
        .tx-search button{border:1px solid #e4e7ec;background:#fff;border-radius:8px;padding:8px 14px;font-size:13px;font-weight:600;cursor:pointer}
    </style>

    {{-- ============ Form transaksi ============ --}}
    <div class="tx-box" id="tx-form-box" style="{{ $errors->any() ? '' : 'display:none' }}">
        <div class="tx-title">Form Transaksi Aset</div>
        <div class="tx-info">
            Form ini mengikuti kolom pada <b>Equipment Allocation Form</b>. Centang satu atau lebih <b>Type of Allocation</b>:
            tabel aset yang sesuai muncul otomatis, dan semuanya tercetak dalam satu surat yang sama.
        </div>

        @if ($errors->any())
            <div class="tx-alert" role="alert">
                <b>Transaksi belum disimpan:</b>
                <ul>
                    @foreach (collect($errors->all())->unique() as $pesan)
                        <li>{{ $pesan }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('transaksi.store') }}" id="tx-form" autocomplete="off">
            @csrf

            <div class="tx-grid two">
                <div class="tx-field">
                    <label>Reference No.</label>
                    <input class="tx-in ro" value="{{ $formData['nextRef'] }}" disabled>
                </div>
                <div class="tx-field">
                    <label>Type of Allocation <span class="hint">(bisa pilih lebih dari satu)</span></label>
                    <div class="tx-pills">
                        @foreach (\App\Models\Transaksi::TYPES as $t)
                            <label class="tx-pill"><input type="checkbox" name="types[]" value="{{ $t }}" @checked(in_array($t, (array) old('types', [])))>{{ $t }}</label>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- User Information --}}
            <div class="tx-sec">
                <div class="tx-sec-title">User Information</div>
                <div class="tx-grid">
                    <div class="tx-field">
                        <label for="tx-peg-text">User Name <span class="hint">(cari nama / User ID)</span></label>
                        <div class="tx-combo">
                            <input type="text" class="tx-in" id="tx-peg-text" placeholder="Ketik untuk mencari pegawai..." autocomplete="off">
                            <input type="hidden" name="id_pegawai" id="tx-peg" value="{{ old('id_pegawai') }}">
                            <div class="tx-list" id="tx-peg-list"></div>
                        </div>
                    </div>
                    <div class="tx-field"><label>User ID</label><input class="tx-in ro" id="tx-uid" readonly placeholder="Otomatis"></div>
                    <div class="tx-field"><label for="tx-cc">CC <span class="hint">(cost center)</span></label><input class="tx-in" id="tx-cc" name="cc" maxlength="50" placeholder="Cost center" value="{{ old('cc') }}"></div>
                    <div class="tx-field"><label>Department</label><input class="tx-in ro" id="tx-dept" readonly placeholder="Otomatis"></div>
                    <div class="tx-field"><label>Phone No</label><input class="tx-in ro" id="tx-phone" readonly placeholder="Otomatis"></div>
                    <div class="tx-field">
                        <label for="tx-room">Room No</label>
                        <input class="tx-in" id="tx-room" name="room_no" maxlength="50" list="tx-rooms" placeholder="Ruangan" value="{{ old('room_no') }}">
                        <datalist id="tx-rooms">
                            @foreach ($formData['ruangan'] as $r)
                                <option value="{{ $r }}"></option>
                            @endforeach
                        </datalist>
                    </div>
                </div>
            </div>

            {{-- Procurement Information (hanya jika Deploy dicentang) --}}
            <div class="tx-sec" id="tx-proc" style="display:none">
                <div class="tx-sec-title">Procurement Information <em>(for initial deployment only)</em></div>
                <div class="tx-grid">
                    <div class="tx-field"><label>PO / Contract No</label><input class="tx-in" id="tx-po" name="po_contract_no" maxlength="50" placeholder="N/A jika tidak ada" value="{{ old('po_contract_no') }}"></div>
                    <div class="tx-field"><label>AFE / CC</label><input class="tx-in" id="tx-afe" name="afe_cc" maxlength="50" placeholder="N/A jika tidak ada" value="{{ old('afe_cc') }}"></div>
                    <div class="tx-field"><label>Year</label><input class="tx-in" type="number" id="tx-tahun" name="tahun" min="2000" max="2100" placeholder="{{ now()->year }}" value="{{ old('tahun') }}"></div>
                </div>
                <div class="tx-foot" style="font-style:normal">
                    Terisi otomatis dari Aset Masuk (PO, AFE/CC, Year) saat aset dipilih di tabel Deploy, dan tetap bisa diubah.
                    Bagian yang tidak diisi di Aset Masuk dibiarkan kosong. Bila aset berasal dari beberapa PO, nomornya digabung; Year dikosongkan bila tahunnya berbeda.
                </div>
            </div>

            {{-- Deploy / Move Equipment --}}
            <div class="tx-sec" id="tx-deploy-wrap" style="display:none">
                <div class="tx-sec-title">Deploy / Move Equipment</div>
                <div class="tx-grid two">
                    <div class="tx-field" style="grid-column:1/-1">
                        <label>Keperluan / Catatan <span class="hint">(opsional, ikut tercetak di Additional Remarks)</span></label>
                        <input class="tx-in" name="deploy_note" maxlength="255" placeholder="Contoh: kebutuhan device baru staff Finance" value="{{ old('deploy_note') }}">
                    </div>
                    <div class="tx-field">
                        <label class="tx-pill" style="width:fit-content"><input type="checkbox" name="is_pinjaman" value="1" id="tx-pinjam" @checked(old('is_pinjaman'))>Pinjaman sementara</label>
                        <div class="hint" style="font-size:11.5px;color:#66707d;margin-top:5px">Aset berstatus Borrowed. Saat dikembalikan, buat surat Return baru.</div>
                    </div>
                    <div class="tx-field" id="tx-lama-wrap" style="display:none">
                        <label for="tx-lama">Lama pinjam (hari) <span class="hint">(opsional, hanya pengingat; tidak dicetak)</span></label>
                        <input class="tx-in" type="number" id="tx-lama" name="lama_pinjam" min="1" max="365" placeholder="Contoh: 10" value="{{ old('lama_pinjam') }}">
                    </div>
                </div>
                <div class="tx-eqwrap">
                    <table class="tx-eq">
                        <thead><tr><th style="min-width:230px">Equipment Name</th><th>Brand</th><th>Model</th><th>Serial No</th><th>Memory</th><th>Hard Disk</th><th>Asset No.</th><th style="min-width:170px">Asset Function</th><th></th></tr></thead>
                        <tbody id="tx-deploy-body"></tbody>
                    </table>
                </div>
                <button type="button" class="tx-addrow" data-add="deploy">+ Tambah baris</button>
                <div class="tx-foot">Asset Function: a. Primary &nbsp; b. Shared &nbsp; c. Training &nbsp; d. Other (explain)</div>
            </div>

            {{-- Return Equipment --}}
            <div class="tx-sec" id="tx-return-wrap" style="display:none">
                <div class="tx-sec-title">Return Equipment?</div>
                <div class="tx-grid">
                    <div class="tx-field">
                        <label>Referensi Peminjaman / Alokasi Sebelumnya <span class="hint">(opsional)</span></label>
                        <select class="tx-in" name="return_ref" id="tx-return-ref">
                            <option value="">Tidak terkait transaksi sebelumnya</option>
                            @foreach ($formData['refs'] as $ref)
                                <option value="{{ $ref['ref'] }}" @selected(old('return_ref') === $ref['ref'])>{{ $ref['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="tx-field"><label>Tanggal Kembali</label><input class="tx-in" type="date" name="return_date" value="{{ old('return_date') }}"></div>
                    <div class="tx-field"><label>Reason (alasan pengembalian)</label><input class="tx-in" name="alasan_return" maxlength="255" placeholder="Contoh: selesai dinas, pergantian perangkat" value="{{ old('alasan_return') }}"></div>
                </div>
                <div class="tx-eqwrap">
                    <table class="tx-eq">
                        <thead><tr><th style="min-width:230px">Equipment Name</th><th>Brand</th><th>Model</th><th>Serial No</th><th>Memory</th><th>Hard Disk</th><th>Asset No.</th><th style="min-width:200px">Status</th><th></th></tr></thead>
                        <tbody id="tx-return-body"></tbody>
                    </table>
                </div>
                <button type="button" class="tx-addrow" data-add="return">+ Tambah baris</button>
                <div class="tx-foot">Status: a. Deployable &nbsp; b. Surplus &nbsp; c. Waiting for Disposal &nbsp; d. Broken</div>
            </div>

            <div class="tx-emptyhint" id="tx-empty">Pilih minimal satu <b>Type of Allocation</b> di atas untuk menampilkan tabel aset.</div>

            <div class="tx-field" style="margin-top:14px">
                <label for="tx-remarks">Additional Remarks</label>
                <textarea class="tx-in" id="tx-remarks" name="remarks" rows="2" maxlength="2000" placeholder="Isi N/A jika tidak ada catatan">{{ old('remarks') }}</textarea>
            </div>

            <div class="tx-actions">
                <button type="button" class="tx-btn line" id="tx-cancel">Batal</button>
                <button type="submit" class="tx-btn primary">Simpan &amp; Buat Surat</button>
            </div>
        </form>
    </div>

    {{-- ============ Daftar transaksi ============ --}}
    <form method="GET" action="{{ route('transaksi.index') }}" class="tx-search">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari no. referensi, pegawai, atau aset..." aria-label="Cari transaksi">
        <button type="submit">Cari</button>
    </form>

    @php
        $pill = fn ($teks, $bg, $fg) => '<span style="display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;white-space:nowrap;background:'.$bg.';color:'.$fg.'">'.e($teks).'</span>';
        $warnaJenis = [
            'Deploy'       => ['#dbe6f4', '#2c5aa0'],
            'Return'       => ['#e7e9ec', '#5b6572'],
            'Replace/Swap' => ['#fdead9', '#c8631b'],
            'Move'         => ['#e1f0e6', '#3c8558'],
        ];
    @endphp

    <div class="card card-flush">
        <div class="table-responsive">
            <table class="table table-hover table-itam tbl">
                <thead>
                    <tr>
                        <th>Ref</th>
                        <th>Pegawai</th>
                        <th>Jenis</th>
                        <th>Aset Terlibat</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transaksi as $item)
                        @php
                            $nama = $item->detail->map(fn ($d) => $d->aset?->nama_aset)->filter()->unique()->values();
                            $asetText = $nama->take(2)->implode(', ').($nama->count() > 2 ? ' +'.($nama->count() - 2).' lainnya' : '');
                            $batas = $item->detail->first(fn ($d) => $d->batas_kembali)?->batas_kembali;
                            $selesai = $item->status_dokumen === 'Selesai';
                        @endphp
                        <tr>
                            <td><span class="mono">{{ $item->no_referensi }}</span></td>
                            <td>{{ $item->pegawai?->nama ?? '-' }}</td>
                            <td>
                                @foreach ($item->types() as $t)
                                    {!! $pill($t, ...$warnaJenis[$t]) !!}
                                @endforeach
                            </td>
                            <td>
                                {{ $asetText ?: '-' }}
                                @if ($batas)
                                    <div style="font-size:11.5px;color:#c8631b">Pinjam s.d. {{ $batas->format('d/m/Y') }}</div>
                                @endif
                            </td>
                            <td>{{ $item->tanggal_transaksi?->format('d/m/Y') }}</td>
                            <td>{!! $selesai ? $pill('Selesai', '#e1f0e6', '#3c8558') : $pill('Menunggu TTD', '#fdead9', '#c8631b') !!}</td>
                            <td>
                                <div class="action-icons">
                                    <a href="{{ route('transaksi.show', $item) }}" class="btn-icon" title="Lihat Surat" aria-label="Lihat surat {{ $item->no_referensi }}">
                                        <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                    </a>
                                    @unless ($selesai)
                                        <form method="POST" action="{{ route('transaksi.selesai', $item) }}"
                                              onsubmit="return confirm('Tandai surat {{ $item->no_referensi }} Selesai (sudah ditandatangani)?')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn-icon" title="Tandai Selesai" aria-label="Tandai selesai {{ $item->no_referensi }}">
                                                <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                                            </button>
                                        </form>
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="empty" style="text-align:center;padding:28px 10px;color:#66707d">
                            {{ request()->filled('q') ? 'Tidak ada transaksi yang cocok dengan "'.request('q').'".' : 'Belum ada transaksi. Klik "Buat Transaksi" untuk membuat surat pertama.' }}
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div style="margin-top:14px">{{ $transaksi->links('pagination::bootstrap-5') }}</div>

    <script>
        (function () {
            var TX = {{ Js::from($formData) }};
            var $ = function (id) { return document.getElementById(id); };
            var toArr = function (x) { return Array.isArray(x) ? x : Object.keys(x || {}).map(function (k) { return x[k]; }); };

            // ---------- buka / tutup form ----------
            var box = $('tx-form-box');
            $('tx-toggle').addEventListener('click', function () {
                box.style.display = box.style.display === 'none' ? 'block' : 'none';
                if (box.style.display === 'block') box.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
            $('tx-cancel').addEventListener('click', function () { box.style.display = 'none'; });

            // ---------- pegawai (combo cari) ----------
            var pegText = $('tx-peg-text'), pegHid = $('tx-peg'), pegList = $('tx-peg-list');

            function setPeg(p, keepRoom) {
                $('tx-uid').value = p ? p.kode : '';
                $('tx-dept').value = p ? (p.dept || '') : '';
                $('tx-phone').value = p ? (p.phone || '') : '';
                if (!keepRoom) $('tx-room').value = p ? (p.room || '') : '';
                if (p) pegText.value = p.nama;
            }

            function pickPeg(id) {
                pegHid.value = id;
                setPeg(TX.pegawai[id], false);
                pegList.classList.remove('open');
                refreshReturn(); refreshRefs();
            }

            function pegSearch() {
                var raw = pegText.value.trim(), q = raw.toLowerCase();
                var cur = TX.pegawai[pegHid.value];
                if (cur && cur.nama !== pegText.value) { pegHid.value = ''; setPeg(null, true); pegText.value = raw; refreshReturn(); refreshRefs(); }

                pegList.innerHTML = '';
                var hits = toArr(TX.pegawai).filter(function (p) {
                    return !q || p.nama.toLowerCase().indexOf(q) > -1 || String(p.kode).toLowerCase().indexOf(q) > -1;
                });

                hits.slice(0, 30).forEach(function (p) {
                    var d = document.createElement('div'); d.className = 'tx-item';
                    var b = document.createElement('b'); b.textContent = p.nama;
                    var s = document.createElement('span'); s.textContent = p.kode + ' · ' + (p.dept || '-');
                    d.appendChild(b); d.appendChild(s);
                    d.addEventListener('mousedown', function () { pickPeg(p.id); });
                    pegList.appendChild(d);
                });

                if (!hits.length) {
                    var e = document.createElement('div'); e.className = 'tx-empty';
                    e.textContent = 'Pegawai "' + raw + '" belum ada di Master Data.';
                    pegList.appendChild(e);
                }

                var a = document.createElement('a'); a.className = 'tx-add';
                a.href = {{ Js::from(route('master.pegawai.index')) }}; a.target = '_blank'; a.rel = 'noopener';
                a.textContent = '+ Tambah pegawai baru (Master Data)';
                pegList.appendChild(a);
                pegList.classList.add('open');
            }

            pegText.addEventListener('input', pegSearch);
            pegText.addEventListener('focus', pegSearch);
            document.addEventListener('click', function (e) {
                if (!pegList.parentNode.contains(e.target)) pegList.classList.remove('open');
            });

            // ---------- baris aset ----------
            var rowIdx = { deploy: 0, 'return': 0 };

            function checkedTypes() {
                return Array.prototype.map.call(document.querySelectorAll('input[name="types[]"]:checked'), function (i) { return i.value; });
            }

            // Aset yang boleh dipilih di tabel Deploy / Move sesuai Type yang dicentang
            function deployIds() {
                var t = checkedTypes(), ids = [];
                if (t.indexOf('Deploy') > -1 || t.indexOf('Replace/Swap') > -1) ids = ids.concat(TX.tersediaIds);
                if (t.indexOf('Move') > -1) ids = ids.concat(TX.dipakaiIds);
                return ids;
            }

            function fillOptions(select, ids, selected, emptyText) {
                select.innerHTML = '';
                var o = document.createElement('option'); o.value = ''; o.textContent = emptyText || 'Pilih aset...';
                select.appendChild(o);
                ids.forEach(function (id) {
                    var a = TX.aset[id]; if (!a) return;
                    var inUse = a.status === 'Deployed' || a.status === 'Borrowed';
                    var op = document.createElement('option');
                    op.value = a.id;
                    op.textContent = a.nama + ' (' + a.kode + ')' + (inUse ? ' - ' + a.status + ', ' + (a.holder || 'pemegang belum tercatat') : '');
                    if (String(a.id) === String(selected)) op.selected = true;
                    select.appendChild(op);
                });
            }

            // Return: hanya aset yang sedang dipegang User Name terpilih (Deployed / Borrowed / hasil Move).
            // Aset yang pemegangnya belum tercatat tetap ditampilkan.
            function returnIds() {
                var peg = pegHid.value;
                if (!peg) return [];
                return TX.dipakaiIds.filter(function (id) {
                    var h = TX.aset[id].holder_id;
                    return h === null || h === undefined || String(h) === String(peg);
                });
            }

            function fillReturnOptions(select, selected) {
                var ids = returnIds();
                var empty = !pegHid.value ? 'Pilih User Name dulu...' : (ids.length ? 'Pilih aset...' : 'Pegawai ini tidak memegang aset');
                fillOptions(select, ids, ids.indexOf(Number(selected)) > -1 ? selected : '', empty);
            }

            function refreshReturn() {
                document.querySelectorAll('#tx-return-body .eq-pick').forEach(function (sel) {
                    fillReturnOptions(sel, sel.value);
                    fillRow(sel.closest('tr'));
                });
            }

            // Referensi sebelumnya: hanya milik pegawai terpilih
            var refSel = $('tx-return-ref');
            function refreshRefs() {
                var peg = pegHid.value, cur = refSel.value;
                refSel.innerHTML = '';
                var o0 = document.createElement('option'); o0.value = ''; o0.textContent = 'Tidak terkait transaksi sebelumnya';
                refSel.appendChild(o0);
                TX.refs.filter(function (r) { return !peg || String(r.peg) === String(peg); }).forEach(function (r) {
                    var o = document.createElement('option'); o.value = r.ref; o.textContent = r.label;
                    if (r.ref === cur) o.selected = true;
                    refSel.appendChild(o);
                });
            }

            function fillRow(tr) {
                var a = TX.aset[tr.querySelector('.eq-pick').value];
                var map = { brand: 'brand', model: 'model', sn: 'sn', mem: 'mem', hdd: 'hdd' };
                Object.keys(map).forEach(function (f) { tr.querySelector('[data-f="' + f + '"]').value = a ? (a[map[f]] || '') : ''; });
                tr.querySelector('[data-f="kode"]').value = a ? a.kode : '';
            }

            function fillSelect(select, optionsObj, selected) {
                Object.keys(optionsObj).forEach(function (v) {
                    var op = document.createElement('option'); op.value = v; op.textContent = optionsObj[v];
                    if (v === selected) op.selected = true;
                    select.appendChild(op);
                });
            }

            function addRow(kind, old) {
                old = old || {};
                var i = rowIdx[kind]++;
                var tr = document.createElement('tr');
                var last = kind === 'deploy'
                    ? '<td><select class="tx-in af" name="deploy[' + i + '][asset_function]"></select><input class="tx-in af-other" name="deploy[' + i + '][asset_function_lain]" maxlength="255" placeholder="Jelaskan fungsi aset..." style="display:none;margin-top:5px"></td>'
                    : '<td><select class="tx-in st" name="return[' + i + '][status_return]"></select><div class="tx-sthint">Status di Data Aset: <b></b></div></td>';

                tr.innerHTML = '<td><select class="tx-in eq-pick" name="' + kind + '[' + i + '][id_aset]"></select></td>'
                    + ['brand', 'model', 'sn', 'mem', 'hdd', 'kode'].map(function (f) {
                        return '<td><input class="tx-in ro' + (f === 'sn' || f === 'kode' ? ' mono' : '') + '" data-f="' + f + '" readonly placeholder="-"></td>';
                    }).join('')
                    + last
                    + '<td class="del"><span class="x" title="Hapus baris">&times;</span></td>';

                var pick = tr.querySelector('.eq-pick');
                if (kind === 'deploy') fillOptions(pick, deployIds(), old.id_aset); else fillReturnOptions(pick, old.id_aset);
                pick.addEventListener('change', function () { fillRow(tr); syncProc(); });

                if (kind === 'deploy') {
                    var af = tr.querySelector('.af'), other = tr.querySelector('.af-other');
                    fillSelect(af, TX.afOptions, old.asset_function || 'Primary');
                    other.value = old.asset_function_lain || '';
                    var sync = function () { other.style.display = af.value === 'Other' ? 'block' : 'none'; };
                    af.addEventListener('change', sync); sync();
                } else {
                    var st = tr.querySelector('.st'), hint = tr.querySelector('.tx-sthint b');
                    fillSelect(st, TX.stOptions, old.status_return || 'Deployable');
                    var syncSt = function () { hint.textContent = TX.stMap[st.value] || ''; };
                    st.addEventListener('change', syncSt); syncSt();
                }

                fillRow(tr);
                $('tx-' + kind + '-body').appendChild(tr);
            }

            document.querySelectorAll('[data-add]').forEach(function (b) {
                b.addEventListener('click', function () { addRow(b.getAttribute('data-add')); });
            });

            ['deploy', 'return'].forEach(function (kind) {
                $('tx-' + kind + '-body').addEventListener('click', function (e) {
                    var x = e.target.closest('.x'); if (!x) return;
                    var body = $('tx-' + kind + '-body');
                    if (body.children.length > 1) { x.closest('tr').remove(); syncProc(); }
                    else alert('Minimal satu baris aset');
                });
            });

            // ---------- Type of Allocation -> bagian yang tampil (aturan sama dengan prototype) ----------
            function toggleWrap(el, show) {
                el.style.display = show ? 'block' : 'none';
                // kolom di bagian tersembunyi tidak ikut terkirim
                el.querySelectorAll('input, select, textarea').forEach(function (c) { c.disabled = !show; });
            }

            function typeChange() {
                var t = checkedTypes();
                var has = function (v) { return t.indexOf(v) > -1; };
                var showDeploy = has('Deploy') || has('Move') || has('Replace/Swap');
                var showReturn = has('Return') || has('Replace/Swap');

                toggleWrap($('tx-deploy-wrap'), showDeploy);
                toggleWrap($('tx-return-wrap'), showReturn);
                toggleWrap($('tx-proc'), has('Deploy'));
                $('tx-empty').style.display = (showDeploy || showReturn) ? 'none' : 'block';

                if (showDeploy) {
                    var allowed = deployIds();
                    document.querySelectorAll('#tx-deploy-body .eq-pick').forEach(function (sel) {
                        var cur = sel.value;
                        fillOptions(sel, allowed, allowed.indexOf(Number(cur)) > -1 ? cur : '');
                        fillRow(sel.closest('tr'));
                    });
                }
                if (showReturn) refreshReturn();
                syncProc();
            }

            document.querySelectorAll('input[name="types[]"]').forEach(function (c) { c.addEventListener('change', typeChange); });

            // ---------- pinjaman sementara ----------
            var pinjam = $('tx-pinjam');
            function syncPinjam() { $('tx-lama-wrap').style.display = pinjam.checked ? 'block' : 'none'; }
            pinjam.addEventListener('change', syncPinjam);

            // ---------- referensi return -> isi pegawai & aset otomatis ----------
            refSel.addEventListener('change', function () {
                var ref = TX.refs.filter(function (r) { return r.ref === this.value; }, this)[0];
                $('tx-return-body').innerHTML = ''; rowIdx['return'] = 0;
                if (!ref) { addRow('return'); return; }
                pickPeg(ref.peg);
                ref.assets.forEach(function (id) { addRow('return', { id_aset: id }); });
            });

            // ---------- Procurement Information: terisi otomatis dari Aset Masuk ----------
            var procFields = { po: $('tx-po'), afe: $('tx-afe'), year: $('tx-tahun') };
            Object.keys(procFields).forEach(function (k) {
                var el = procFields[k];
                if (el.value !== '') el.dataset.dirty = '1';                  // isian lama / manual jangan ditimpa
                el.addEventListener('input', function () { el.dataset.dirty = '1'; });
            });

            function distinct(list) {
                return list.filter(function (v, i) { return v !== '' && list.indexOf(v) === i; });
            }

            function syncProc() {
                var po = [], afe = [], yr = [];
                document.querySelectorAll('#tx-deploy-body .eq-pick').forEach(function (sel) {
                    var a = TX.aset[sel.value];
                    if (!a || a.status !== 'Available') return;               // hanya aset yang baru di-deploy (initial deployment)
                    po.push(a.po || ''); afe.push(a.afe || ''); yr.push(a.year ? String(a.year) : '');
                });
                po = distinct(po); afe = distinct(afe); yr = distinct(yr);
                if (!procFields.po.dataset.dirty)   procFields.po.value = po.join(', ');
                if (!procFields.afe.dataset.dirty)  procFields.afe.value = afe.join(', ');
                if (!procFields.year.dataset.dirty) procFields.year.value = yr.length === 1 ? yr[0] : '';
            }

            // ---------- inisialisasi (termasuk pemulihan isian setelah validasi gagal) ----------
            var oldDeploy = toArr(TX.old.deploy), oldReturn = toArr(TX.old.return);
            if (oldDeploy.length) oldDeploy.forEach(function (r) { addRow('deploy', r); }); else addRow('deploy');
            if (oldReturn.length) oldReturn.forEach(function (r) { addRow('return', r); }); else addRow('return');

            if (TX.old.peg && TX.pegawai[TX.old.peg]) setPeg(TX.pegawai[TX.old.peg], true);
            syncPinjam();
            refreshRefs();
            typeChange();
        })();
    </script>
</x-app-layout>
