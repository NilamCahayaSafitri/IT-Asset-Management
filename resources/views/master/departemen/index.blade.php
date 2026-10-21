<x-master-layout>
    <x-master-toolbar :action="route('master.departemen.index')" placeholder="Cari nama departemen..." add-label="Tambah Departemen" />

    <div class="card card-flush">
        <div class="table-responsive">
            <table class="table table-hover table-itam tbl">
                <thead>
                    <tr>
                        <th>Nama Departemen</th>
                        <th class="text-end num">Ruangan</th>
                        <th class="text-end num">Pegawai</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($departemen as $item)
                        @php
                            $blocked = ($item->ruangan_count + $item->pegawai_count) > 0
                                ? "Departemen ini masih dipakai oleh {$item->ruangan_count} ruangan dan {$item->pegawai_count} pegawai, jadi tidak bisa dihapus."
                                : '';
                        @endphp
                        <tr>
                            <td>{{ $item->nama_departemen }}</td>
                            <td class="text-end num">{{ $item->ruangan_count }}</td>
                            <td class="text-end num">{{ $item->pegawai_count }}</td>
                            <td>
                                <div class="action-icons">
                                    <button type="button" class="btn-icon js-edit" title="Edit" aria-label="Ubah {{ $item->nama_departemen }}"
                                            data-action="{{ route('master.departemen.update', $item) }}"
                                            data-values="{{ json_encode(['nama_departemen' => $item->nama_departemen]) }}"><svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
                                    <button type="button" class="btn-icon danger js-delete" title="Hapus" aria-label="Hapus {{ $item->nama_departemen }}"
                                            data-action="{{ route('master.departemen.destroy', $item) }}"
                                            data-nama="{{ $item->nama_departemen }}"
                                            data-blocked="{{ $blocked }}"><svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg></button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="empty" style="text-align:center;padding:28px 10px;color:#66707d">
                            {{ request()->filled('q') ? 'Tidak ada departemen yang cocok dengan "'.request('q').'".' : 'Belum ada departemen. Klik "Tambah Departemen" untuk membuat yang pertama.' }}
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div style="margin-top:14px">{{ $departemen->links('pagination::bootstrap-5') }}</div>

    <x-master-modals :store-url="route('master.departemen.store')" entity="Departemen">
        <x-master-field name="nama_departemen" label="Nama Departemen" :required="true" maxlength="255" placeholder="Contoh: ICT" />
    </x-master-modals>
</x-master-layout>
