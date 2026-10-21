<x-master-layout>
    {{-- Gaya modal & toolbar sengaja ditulis di sini supaya halaman ini tidak bergantung
         pada Alpine / Bootstrap JS / @stack di layout. --}}
    <style>
        .km-bar{display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:10px;margin-bottom:14px}
        .km-search{display:flex;gap:8px;width:100%;max-width:320px}
        .km-search input{flex:1;min-width:0;border:1px solid #e4e7ec;border-radius:8px;padding:8px 12px;font-size:13px;background:#fff}
        .km-search button{border:1px solid #e4e7ec;background:#fff;border-radius:8px;padding:8px 14px;font-size:13px;font-weight:600;cursor:pointer}

        .km-backdrop{display:none;position:fixed;inset:0;background:rgba(16,28,46,.5);z-index:2000;align-items:center;justify-content:center;padding:16px}
        .km-backdrop.open{display:flex}
        .km-box{background:#fff;border-radius:10px;width:100%;max-width:420px;overflow:hidden;box-shadow:0 20px 50px rgba(0,0,0,.3)}
        .km-head{display:flex;justify-content:space-between;align-items:center;padding:14px 18px;font-weight:600;font-size:15px}
        .km-head.plain{border-bottom:1px solid #e4e7ec}
        .km-head.danger{background:#dc3545;color:#fff}
        .km-x{background:none;border:none;font-size:22px;line-height:1;cursor:pointer;color:inherit;opacity:.85;padding:0 2px}
        .km-body{padding:18px}
        .km-body p{margin:0}
        .km-foot{display:flex;justify-content:flex-end;gap:8px;padding:0 18px 18px}
        .km-label{display:block;font-size:12px;font-weight:600;margin-bottom:6px}
        .km-input{width:100%;border:1px solid #e4e7ec;border-radius:8px;padding:9px 11px;font-size:13px;background:#fbfbfc}
        .km-input:focus{outline:none;border-color:#1d9e75;box-shadow:0 0 0 3px #dcf1e8}
        .km-input.invalid{border-color:#dc3545}
        .km-err{color:#dc3545;font-size:12px;margin-top:6px}
        .km-btn{border:none;border-radius:8px;padding:8px 16px;font-size:13px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;gap:6px;color:#fff}
        .km-btn.primary{background:#1d9e75}
        .km-btn.line{background:#fff;color:#16283f;border:1px solid #e4e7ec}
        .km-btn.sq{width:40px;height:36px;padding:0}
        .km-btn.gray{background:#6c757d}
        .km-btn.red{background:#dc3545}
        .km-btn:disabled{opacity:.4;cursor:not-allowed}
        .km-btn svg{width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2}
    </style>

    <div class="km-bar">
        <form method="GET" action="{{ route('master.kategori-aset.index') }}" class="km-search">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama kategori..." aria-label="Cari kategori">
            <button type="submit">Cari</button>
        </form>

        <button type="button" id="km-add" class="km-btn primary">+ Tambah Kategori</button>
    </div>

    <div class="card card-flush">
        <div class="table-responsive">
            <table class="table table-hover table-itam tbl">
                <thead>
                    <tr>
                        <th>Nama Kategori</th>
                        <th class="text-end num">Jumlah Aset</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($kategori as $item)
                        <tr>
                            <td>{{ $item->nama_kategori }}</td>
                            <td class="text-end num">{{ $item->aset_count }}</td>
                            <td>
                                <div class="action-icons">
                                    <button type="button" class="btn-icon js-edit" title="Edit" aria-label="Ubah {{ $item->nama_kategori }}"
                                            data-action="{{ route('master.kategori-aset.update', $item) }}"
                                            data-nama="{{ $item->nama_kategori }}">
                                        <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </button>
                                    <button type="button" class="btn-icon danger js-delete" title="Hapus" aria-label="Hapus {{ $item->nama_kategori }}"
                                            data-action="{{ route('master.kategori-aset.destroy', $item) }}"
                                            data-nama="{{ $item->nama_kategori }}"
                                            data-used="{{ $item->aset_count }}">
                                        <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="empty" style="text-align:center;padding:28px 10px;color:#66707d">
                                @if (request()->filled('q'))
                                    Tidak ada kategori yang cocok dengan "{{ request('q') }}".
                                @else
                                    Belum ada kategori aset. Klik "Tambah Kategori" untuk membuat yang pertama.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div style="margin-top:14px">{{ $kategori->links('pagination::bootstrap-5') }}</div>

    {{-- ============ Modal tambah / ubah ============ --}}
    <div class="km-backdrop {{ $errors->has('nama_kategori') ? 'open' : '' }}" id="km-form-modal">
        <div class="km-box" role="dialog" aria-modal="true" aria-labelledby="km-form-title">
            <div class="km-head plain">
                <span id="km-form-title">{{ old('_method') === 'PUT' ? 'Ubah Kategori Aset' : 'Tambah Kategori Aset' }}</span>
                <button type="button" class="km-x" data-km-close aria-label="Tutup">&times;</button>
            </div>

            <form method="POST" id="km-form" action="{{ old('_action', route('master.kategori-aset.store')) }}">
                @csrf
                <input type="hidden" name="_method" id="km-method" value="{{ old('_method', 'POST') }}">
                <input type="hidden" name="_action" id="km-action" value="{{ old('_action', route('master.kategori-aset.store')) }}">

                <div class="km-body">
                    <label class="km-label" for="km-nama">Nama Kategori</label>
                    <input type="text" id="km-nama" name="nama_kategori" maxlength="255" required
                           placeholder="Contoh: Laptop" value="{{ old('nama_kategori') }}"
                           class="km-input {{ $errors->has('nama_kategori') ? 'invalid' : '' }}">
                    @error('nama_kategori')<div class="km-err">{{ $message }}</div>@enderror
                </div>

                <div class="km-foot">
                    <button type="button" class="km-btn line" data-km-close>Batal</button>
                    <button type="submit" class="km-btn primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ============ Modal konfirmasi hapus ============ --}}
    <div class="km-backdrop" id="km-del-modal">
        <div class="km-box" role="alertdialog" aria-modal="true" aria-labelledby="km-del-title">
            <div class="km-head danger">
                <span id="km-del-title">Konfirmasi Hapus</span>
                <button type="button" class="km-x" data-km-close aria-label="Tutup">&times;</button>
            </div>

            <form method="POST" id="km-del-form" action="#">
                @csrf
                @method('DELETE')

                <div class="km-body">
                    <p>Apakah Anda yakin ingin menghapus kategori <strong id="km-del-name"></strong>?</p>
                    <p class="km-err" id="km-del-warn" hidden></p>
                </div>

                <div class="km-foot">
                    <button type="button" class="km-btn sq gray" data-km-close title="Batal" aria-label="Batal">
                        <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                    <button type="submit" class="km-btn sq red" id="km-del-submit" title="Hapus" aria-label="Hapus">
                        <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
            var storeUrl  = @json(route('master.kategori-aset.store'));
            var formModal = document.getElementById('km-form-modal');
            var delModal  = document.getElementById('km-del-modal');
            var form      = document.getElementById('km-form');
            var method    = document.getElementById('km-method');
            var actionIn  = document.getElementById('km-action');
            var title     = document.getElementById('km-form-title');
            var nama      = document.getElementById('km-nama');
            var delForm   = document.getElementById('km-del-form');
            var delName   = document.getElementById('km-del-name');
            var delWarn   = document.getElementById('km-del-warn');
            var delSubmit = document.getElementById('km-del-submit');

            function openModal(m)  { m.classList.add('open'); }
            function closeModal(m) { m.classList.remove('open'); }

            function openForm(mode, url, value) {
                form.action     = url;
                actionIn.value  = url;
                method.value    = mode === 'edit' ? 'PUT' : 'POST';
                title.textContent = mode === 'edit' ? 'Ubah Kategori Aset' : 'Tambah Kategori Aset';
                nama.value      = value;
                nama.classList.remove('invalid');
                var err = formModal.querySelector('.km-err');
                if (err) err.remove();
                openModal(formModal);
                setTimeout(function () { nama.focus(); nama.select(); }, 30);
            }

            document.getElementById('km-add').addEventListener('click', function () {
                openForm('create', storeUrl, '');
            });

            document.querySelectorAll('.js-edit').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    openForm('edit', btn.dataset.action, btn.dataset.nama);
                });
            });

            document.querySelectorAll('.js-delete').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var used = parseInt(btn.dataset.used || '0', 10);
                    delForm.action = btn.dataset.action;
                    delName.textContent = btn.dataset.nama;
                    delWarn.hidden = used === 0;
                    delWarn.textContent = used > 0
                        ? 'Kategori ini masih dipakai oleh ' + used + ' aset, jadi tidak bisa dihapus.'
                        : '';
                    delSubmit.disabled = used > 0;
                    openModal(delModal);
                });
            });

            // Tutup: tombol silang/batal, klik latar gelap, atau tombol Esc
            document.querySelectorAll('[data-km-close]').forEach(function (b) {
                b.addEventListener('click', function () { closeModal(b.closest('.km-backdrop')); });
            });
            document.querySelectorAll('.km-backdrop').forEach(function (m) {
                m.addEventListener('click', function (e) { if (e.target === m) closeModal(m); });
            });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    document.querySelectorAll('.km-backdrop.open').forEach(closeModal);
                }
            });
        })();
    </script>
</x-master-layout>
