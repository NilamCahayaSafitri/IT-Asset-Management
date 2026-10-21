<x-master-layout>
    <x-master-toolbar :action="route('master.vendor.index')" placeholder="Cari nama, kontak, atau email..." add-label="Tambah Vendor" />

    <div class="card card-flush">
        <div class="table-responsive">
            <table class="table table-hover table-itam tbl">
                <thead>
                    <tr>
                        <th>Nama Vendor</th>
                        <th>Alamat</th>
                        <th>Kontak</th>
                        <th>Email</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($vendor as $item)
                        @php
                            $blocked = $item->aset_masuk_count > 0
                                ? "Vendor ini sudah punya {$item->aset_masuk_count} riwayat aset masuk, jadi tidak bisa dihapus."
                                : '';
                        @endphp
                        <tr>
                            <td>{{ $item->nama_vendor }}</td>
                            <td class="km-muted">{{ \Illuminate\Support\Str::limit($item->alamat, 50) ?: '-' }}</td>
                            <td>{{ $item->kontak ?: '-' }}</td>
                            <td>{{ $item->email ?: '-' }}</td>
                            <td>
                                <div class="action-icons">
                                    <button type="button" class="btn-icon js-edit" title="Edit" aria-label="Ubah {{ $item->nama_vendor }}"
                                            data-action="{{ route('master.vendor.update', $item) }}"
                                            data-values="{{ json_encode(['nama_vendor' => $item->nama_vendor, 'alamat' => $item->alamat, 'kontak' => $item->kontak, 'email' => $item->email]) }}"><svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
                                    <button type="button" class="btn-icon danger js-delete" title="Hapus" aria-label="Hapus {{ $item->nama_vendor }}"
                                            data-action="{{ route('master.vendor.destroy', $item) }}"
                                            data-nama="{{ $item->nama_vendor }}"
                                            data-blocked="{{ $blocked }}"><svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg></button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="empty" style="text-align:center;padding:28px 10px;color:#66707d">
                            {{ request()->filled('q') ? 'Tidak ada vendor yang cocok dengan "'.request('q').'".' : 'Belum ada vendor. Klik "Tambah Vendor" untuk membuat yang pertama.' }}
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div style="margin-top:14px">{{ $vendor->links('pagination::bootstrap-5') }}</div>

    <x-master-modals :store-url="route('master.vendor.store')" entity="Vendor">
        <x-master-field name="nama_vendor" label="Nama Vendor" :required="true" maxlength="255" />
        <x-master-field name="alamat" label="Alamat" type="textarea" maxlength="1000" />
        <div class="km-row">
            <x-master-field name="kontak" label="Kontak" maxlength="30" placeholder="08xxxxxxxxxx" />
            <x-master-field name="email" label="Email" type="email" maxlength="255" />
        </div>
    </x-master-modals>
</x-master-layout>
