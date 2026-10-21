<x-master-layout>
    <x-master-toolbar :action="route('master.pegawai.index')" placeholder="Cari nama, User ID, jabatan, email..." add-label="Tambah Pegawai" />

    <div class="card card-flush">
        <div class="table-responsive">
            <table class="table table-hover table-itam tbl">
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Nama</th>
                        <th>Jabatan</th>
                        <th>Departemen</th>
                        <th>Email</th>
                        <th>Phone No</th>
                        <th>Room No</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pegawai as $item)
                        @php
                            $blocked = $item->transaksi_count > 0
                                ? "Pegawai ini sudah punya {$item->transaksi_count} riwayat transaksi, jadi tidak bisa dihapus."
                                : '';
                        @endphp
                        <tr>
                            <td><span class="mono">{{ $item->kode_pegawai }}</span></td>
                            <td>{{ $item->nama }}</td>
                            <td>{{ $item->jabatan ?: '-' }}</td>
                            <td>{{ $item->departemen?->nama_departemen ?? '-' }}</td>
                            <td>{{ $item->email ?: '-' }}</td>
                            <td>{{ $item->kontak ?: '-' }}</td>
                            <td>{{ $item->room_no ?: '-' }}</td>
                            <td>
                                <div class="action-icons">
                                    <button type="button" class="btn-icon js-edit" title="Edit" aria-label="Ubah {{ $item->nama }}"
                                            data-action="{{ route('master.pegawai.update', $item) }}"
                                            data-values="{{ json_encode([
                                                'kode_pegawai' => $item->kode_pegawai, 'nama' => $item->nama, 'jabatan' => $item->jabatan,
                                                'id_departemen' => $item->id_departemen, 'email' => $item->email,
                                                'kontak' => $item->kontak, 'room_no' => $item->room_no,
                                            ]) }}"><svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
                                    <button type="button" class="btn-icon danger js-delete" title="Hapus" aria-label="Hapus {{ $item->nama }}"
                                            data-action="{{ route('master.pegawai.destroy', $item) }}"
                                            data-nama="{{ $item->nama }}"
                                            data-blocked="{{ $blocked }}"><svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg></button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="empty" style="text-align:center;padding:28px 10px;color:#66707d">
                            {{ request()->filled('q') ? 'Tidak ada pegawai yang cocok dengan "'.request('q').'".' : 'Belum ada pegawai. Klik "Tambah Pegawai" untuk membuat yang pertama.' }}
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div style="margin-top:14px">{{ $pegawai->links('pagination::bootstrap-5') }}</div>

    <x-master-modals :store-url="route('master.pegawai.store')" entity="Pegawai" :wide="true">
        <div class="km-row">
            <x-master-field name="kode_pegawai" label="User ID" :required="true" maxlength="30" placeholder="EMP-00231" />
            <x-master-field name="nama" label="Nama" :required="true" maxlength="255" />
        </div>
        <div class="km-row">
            <x-master-field name="jabatan" label="Jabatan" maxlength="255" />
            <x-master-field name="id_departemen" label="Departemen" type="select" :required="true" :options="$departemenOptions" />
        </div>
        <div class="km-row">
            <x-master-field name="email" label="Email" type="email" maxlength="255" />
            <x-master-field name="kontak" label="Phone No" maxlength="30" />
        </div>
        <x-master-field name="room_no" label="Room No" maxlength="50" />
    </x-master-modals>
</x-master-layout>
