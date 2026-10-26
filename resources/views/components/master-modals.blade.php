@props(['storeUrl', 'entity', 'wide' => false])

{{-- Gaya + modal tambah/ubah + modal konfirmasi hapus + JS, dipakai semua tab Master Data.
     Tidak bergantung pada Alpine / Bootstrap JS / @stack di layout. --}}
<style>
    .km-bar{display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:10px;margin-bottom:14px}
    .km-search{display:flex;gap:8px;width:100%;max-width:320px}
    .km-search input{flex:1;min-width:0;border:1px solid #e4e7ec;border-radius:8px;padding:8px 12px;font-size:13px;background:#fff}
    .km-search button{border:1px solid #e4e7ec;background:#fff;border-radius:8px;padding:8px 14px;font-size:13px;font-weight:600;cursor:pointer}

    .km-backdrop{display:none;position:fixed;inset:0;background:rgba(16,28,46,.5);z-index:2000;align-items:center;justify-content:center;padding:16px}
    .km-backdrop.open{display:flex}
    .km-box{background:#fff;border-radius:10px;width:100%;max-width:420px;max-height:92vh;overflow-y:auto;box-shadow:0 20px 50px rgba(0,0,0,.3)}
    .km-head{display:flex;justify-content:space-between;align-items:center;padding:14px 18px;font-weight:600;font-size:15px}
    .km-head.plain{border-bottom:1px solid #e4e7ec}
    .km-head.danger{background:#dc3545;color:#fff}
    .km-x{background:none;border:none;font-size:22px;line-height:1;cursor:pointer;color:inherit;opacity:.85;padding:0 2px}
    .km-body{padding:18px}
    .km-body p{margin:0}
    .km-foot{display:flex;justify-content:flex-end;gap:8px;padding:0 18px 18px}
    .km-field{margin-bottom:14px}
    .km-row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
    @media (max-width:560px){.km-row{grid-template-columns:1fr}}
    .km-label{display:block;font-size:12px;font-weight:600;margin-bottom:6px}
    .km-input{width:100%;border:1px solid #e4e7ec;border-radius:8px;padding:9px 11px;font-size:13px;background:#fbfbfc;font-family:inherit}
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
    .km-muted{color:#66707d}
</style>

{{-- ============ Modal tambah / ubah ============ --}}
<div class="km-backdrop {{ $errors->any() ? 'open' : '' }}" id="km-form-modal">
    <div class="km-box" style="max-width:{{ $wide ? '580px' : '420px' }}" role="dialog" aria-modal="true" aria-labelledby="km-form-title">
        <div class="km-head plain">
            <span id="km-form-title">{{ old('_method') === 'PUT' ? 'Ubah' : 'Tambah' }} {{ $entity }}</span>
            <button type="button" class="km-x" data-km-close aria-label="Tutup">&times;</button>
        </div>

        <form method="POST" id="km-form" action="{{ old('_action', $storeUrl) }}">
            @csrf
            <input type="hidden" name="_method" id="km-method" value="{{ old('_method', 'POST') }}">
            <input type="hidden" name="_action" id="km-action" value="{{ old('_action', $storeUrl) }}">

            <div class="km-body">
                {{ $slot }}
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
                <p>Apakah Anda yakin ingin menghapus {{ \Illuminate\Support\Str::lower($entity) }} <strong id="km-del-name"></strong>?</p>
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
        var storeUrl  = @json($storeUrl);
        var entity    = @json($entity);
        var formModal = document.getElementById('km-form-modal');
        var delModal  = document.getElementById('km-del-modal');
        var form      = document.getElementById('km-form');
        var method    = document.getElementById('km-method');
        var actionIn  = document.getElementById('km-action');
        var title     = document.getElementById('km-form-title');
        var delForm   = document.getElementById('km-del-form');
        var delName   = document.getElementById('km-del-name');
        var delWarn   = document.getElementById('km-del-warn');
        var delSubmit = document.getElementById('km-del-submit');

        function openModal(m)  { m.classList.add('open'); }
        function closeModal(m) { m.classList.remove('open'); }

        function formFields() {
            return form.querySelectorAll('[name]:not([name="_token"]):not([name="_method"]):not([name="_action"])');
        }

        function openForm(mode, url, values) {
            form.action = url;
            actionIn.value = url;
            method.value = mode === 'edit' ? 'PUT' : 'POST';
            title.textContent = (mode === 'edit' ? 'Ubah ' : 'Tambah ') + entity;

            formFields().forEach(function (el) {
                var v = values && values[el.name] != null ? values[el.name] : '';
                el.value = v;
                el.classList.remove('invalid');
            });
            formModal.querySelectorAll('.km-err').forEach(function (e) { e.remove(); });

            // memberi tahu halaman yang memakai komponen ini (mis. Kelola User menyembunyikan kolom password saat ubah)
            formModal.dispatchEvent(new CustomEvent('km:open', { detail: { mode: mode } }));

            openModal(formModal);
            var first = formFields()[0];
            if (first) setTimeout(function () { first.focus(); }, 30);
        }

        document.querySelectorAll('[data-km-add]').forEach(function (btn) {
            btn.addEventListener('click', function () { openForm('create', storeUrl, null); });
        });

        document.querySelectorAll('.js-edit').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var values = {};
                try { values = JSON.parse(btn.dataset.values || '{}'); } catch (e) {}
                openForm('edit', btn.dataset.action, values);
            });
        });

        document.querySelectorAll('.js-delete').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var blocked = btn.dataset.blocked || '';
                delForm.action = btn.dataset.action;
                delName.textContent = btn.dataset.nama;
                delWarn.hidden = blocked === '';
                delWarn.textContent = blocked;
                delSubmit.disabled = blocked !== '';
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
