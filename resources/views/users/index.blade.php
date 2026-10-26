<x-app-layout>
    <x-page-head title="Kelola User" sub="Manajemen akun pengguna sistem" />

    <x-master-toolbar :action="route('users.index')" placeholder="Cari nama, username, atau email..." add-label="Tambah User" />

    @php
        $pill = fn ($teks, $bg, $fg) => '<span style="display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;white-space:nowrap;background:'.$bg.';color:'.$fg.'">'.e($teks).'</span>';
        $warnaRole = ['Super Admin' => ['#dbe6f4', '#2c5aa0'], 'Admin' => ['#e1f0e6', '#3c8558']];
        $idSaya = auth()->id();
    @endphp

    <div class="card card-flush">
        <div class="table-responsive">
            <table class="table table-hover table-itam tbl">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $item)
                        @php
                            $role = $item->roles->first()?->name;
                            $aktif = $item->status === 'aktif';
                            $saya = $item->id === $idSaya;
                            $terakhir = $role === 'Super Admin' && $aktif && $superAdminAktif <= 1;
                            $riwayat = $item->aset_masuk_count + $item->transaksi_count;

                            // alasan akun tidak boleh dinonaktifkan / dihapus
                            $blokNonaktif = $saya ? 'Akun yang sedang Anda pakai tidak bisa dinonaktifkan.'
                                : ($terakhir ? 'Satu-satunya Super Admin aktif tidak bisa dinonaktifkan.' : '');
                            $blokHapus = $saya ? 'Akun yang sedang Anda pakai tidak bisa dihapus.'
                                : ($terakhir ? 'Satu-satunya Super Admin aktif tidak bisa dihapus.'
                                : ($riwayat > 0 ? "User ini sudah punya riwayat ({$item->aset_masuk_count} aset masuk, {$item->transaksi_count} transaksi), jadi tidak bisa dihapus. Nonaktifkan saja akunnya." : ''));
                        @endphp
                        <tr>
                            <td>
                                {{ $item->name }}
                                @if ($saya)<span style="font-size:11px;color:#66707d"> (Anda)</span>@endif
                            </td>
                            <td><span class="mono">{{ $item->username }}</span></td>
                            <td>{{ $item->email }}</td>
                            <td>{!! $role ? $pill($role, ...($warnaRole[$role] ?? ['#e7e9ec', '#5b6572'])) : '-' !!}</td>
                            <td>{!! $aktif ? $pill('Aktif', '#e1f0e6', '#3c8558') : $pill('Nonaktif', '#e7e9ec', '#5b6572') !!}</td>
                            <td>
                                <div class="action-icons">
                                    <button type="button" class="btn-icon js-edit" title="Edit" aria-label="Ubah {{ $item->name }}"
                                            data-action="{{ route('users.update', $item) }}"
                                            data-values="{{ json_encode(['name' => $item->name, 'username' => $item->username, 'email' => $item->email, 'kontak' => $item->kontak, 'role' => $role, 'status' => $item->status]) }}">
                                        <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </button>

                                    <button type="button" class="btn-icon js-reset" title="Reset Password" aria-label="Reset password {{ $item->name }}"
                                            data-action="{{ route('users.password', $item) }}" data-nama="{{ $item->name }}">
                                        <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                    </button>

                                    @if ($aktif && $blokNonaktif)
                                        <button type="button" class="btn-icon" disabled title="{{ $blokNonaktif }}" style="opacity:.4;cursor:not-allowed">
                                            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="4.9" y1="4.9" x2="19.1" y2="19.1"/></svg>
                                        </button>
                                    @else
                                        <form method="POST" action="{{ route('users.status', $item) }}"
                                              onsubmit="return confirm({{ Js::from(($aktif ? 'Nonaktifkan akun ' : 'Aktifkan akun ').$item->name.'?') }})">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn-icon {{ $aktif ? 'danger' : '' }}" title="{{ $aktif ? 'Nonaktifkan' : 'Aktifkan' }}" aria-label="{{ $aktif ? 'Nonaktifkan' : 'Aktifkan' }} {{ $item->name }}">
                                                @if ($aktif)
                                                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="4.9" y1="4.9" x2="19.1" y2="19.1"/></svg>
                                                @else
                                                    <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                                                @endif
                                            </button>
                                        </form>
                                    @endif

                                    <button type="button" class="btn-icon danger js-delete" title="Hapus" aria-label="Hapus {{ $item->name }}"
                                            data-action="{{ route('users.destroy', $item) }}"
                                            data-nama="{{ $item->name }}"
                                            data-blocked="{{ $blokHapus }}">
                                        <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="empty" style="text-align:center;padding:28px 10px;color:#66707d">
                            {{ request()->filled('q') ? 'Tidak ada user yang cocok dengan "'.request('q').'".' : 'Belum ada user.' }}
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div style="margin-top:14px">{{ $users->links('pagination::bootstrap-5') }}</div>

    {{-- Modal Reset Password (diletakkan sebelum komponen modal agar ikut ditutup oleh tombol/Esc/klik latar yang sama) --}}
    <div class="km-backdrop {{ $errors->reset->any() ? 'open' : '' }}" id="usr-reset-modal">
        <div class="km-box" role="dialog" aria-modal="true" aria-labelledby="usr-reset-title">
            <div class="km-head plain">
                <span id="usr-reset-title">Reset Password</span>
                <button type="button" class="km-x" data-km-close aria-label="Tutup">&times;</button>
            </div>

            <form method="POST" id="usr-reset-form" action="{{ old('_reset_action', '#') }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="_reset_action" id="usr-reset-action" value="{{ old('_reset_action') }}">
                <input type="hidden" name="_reset_nama" id="usr-reset-nama" value="{{ old('_reset_nama') }}">

                <div class="km-body">
                    <p style="margin-bottom:12px">Atur password baru untuk <strong id="usr-reset-name">{{ old('_reset_nama') }}</strong>. Pengguna akan diminta login ulang.</p>

                    <div class="km-field">
                        <label class="km-label" for="usr-pw1">Password baru</label>
                        <input class="km-input @if ($errors->reset->has('new_password')) invalid @endif" type="password" id="usr-pw1" name="new_password" minlength="8" maxlength="100" autocomplete="new-password" required>
                        @if ($errors->reset->has('new_password'))<div class="km-err">{{ $errors->reset->first('new_password') }}</div>@endif
                    </div>
                    <div class="km-field">
                        <label class="km-label" for="usr-pw2">Ulangi password baru</label>
                        <input class="km-input" type="password" id="usr-pw2" name="new_password_confirmation" minlength="8" maxlength="100" autocomplete="new-password" required>
                    </div>
                    <button type="button" id="usr-gen" style="background:none;border:none;color:#1d9e75;font-weight:600;font-size:12.5px;cursor:pointer;padding:0">Buat password acak</button>
                </div>

                <div class="km-foot">
                    <button type="button" class="km-btn line" data-km-close>Batal</button>
                    <button type="submit" class="km-btn primary">Simpan Password</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal tambah / ubah / hapus --}}
    <x-master-modals :store-url="route('users.store')" entity="User" :wide="true">
        <div class="km-row">
            <x-master-field name="name" label="Nama Lengkap" :required="true" maxlength="255" />
            <x-master-field name="username" label="Username" :required="true" maxlength="50" placeholder="username login" />
        </div>
        <div class="km-row">
            <x-master-field name="email" label="Email" type="email" :required="true" maxlength="255" placeholder="nama@pge.co.id" />
            <x-master-field name="kontak" label="Kontak" maxlength="30" />
        </div>
        <div class="km-row">
            <x-master-field name="role" label="Role" type="select" :required="true" :options="$roleOptions" />
            <x-master-field name="status" label="Status" type="select" :required="true" :options="['aktif' => 'Aktif', 'nonaktif' => 'Nonaktif']" />
        </div>
        <div id="usr-pass">
            <div class="km-row">
                <x-master-field name="password" label="Password awal" type="password" :required="true" maxlength="100" hint="Minimal 8 karakter." />
                <x-master-field name="password_confirmation" label="Ulangi password" type="password" :required="true" maxlength="100" />
            </div>
        </div>
    </x-master-modals>

    <script>
        (function () {
            var formModal = document.getElementById('km-form-modal');
            var passGroup = document.getElementById('usr-pass');

            // Password hanya untuk akun baru; ubah password lewat "Reset Password"
            function setMode(mode) {
                var edit = mode === 'edit';
                passGroup.style.display = edit ? 'none' : 'block';
                passGroup.querySelectorAll('input').forEach(function (i) { i.disabled = edit; });
            }

            formModal.addEventListener('km:open', function (e) {
                setMode(e.detail.mode);
                if (e.detail.mode === 'create') {
                    document.getElementById('f-status').value = 'aktif';
                    var role = document.getElementById('f-role');
                    var ada = Array.prototype.some.call(role.options, function (o) { return o.value === 'Admin'; });
                    if (ada) role.value = 'Admin';
                }
            });
            setMode({{ Js::from(old('_method') === 'PUT' ? 'edit' : 'create') }});

            // ---------- Reset Password ----------
            var resetModal = document.getElementById('usr-reset-modal');
            var resetForm = document.getElementById('usr-reset-form');
            var pw1 = document.getElementById('usr-pw1'), pw2 = document.getElementById('usr-pw2');

            document.querySelectorAll('.js-reset').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    resetForm.action = btn.dataset.action;
                    document.getElementById('usr-reset-action').value = btn.dataset.action;
                    document.getElementById('usr-reset-nama').value = btn.dataset.nama;
                    document.getElementById('usr-reset-name').textContent = btn.dataset.nama;
                    [pw1, pw2].forEach(function (i) { i.value = ''; i.type = 'password'; i.classList.remove('invalid'); });
                    resetModal.querySelectorAll('.km-err').forEach(function (e) { e.remove(); });
                    resetModal.classList.add('open');
                    setTimeout(function () { pw1.focus(); }, 30);
                });
            });

            document.getElementById('usr-gen').addEventListener('click', function () {
                var huruf = 'ABCDEFGHJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
                var acak = new Uint32Array(10);
                (window.crypto || window.msCrypto).getRandomValues(acak);
                var sandi = Array.prototype.map.call(acak, function (n) { return huruf.charAt(n % huruf.length); }).join('');
                pw1.value = sandi; pw2.value = sandi;
                pw1.type = 'text'; pw2.type = 'text';          // tampilkan agar bisa disalin
            });
        })();
    </script>
</x-app-layout>
