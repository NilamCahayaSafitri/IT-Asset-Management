{{-- $i = indeks baris (atau __INDEX__ untuk template), $row = data lama (array) --}}
@php
    $bad = fn ($f) => $errors->has("items.$i.$f") ? 'bad' : '';
    $rowErrors = collect(['nama_aset', 'id_kategori', 'merk', 'model', 'memory', 'hard_disk', 'jumlah', 'harga_satuan'])
        ->map(fn ($f) => $errors->first("items.$i.$f"))->filter()->unique();
@endphp

<div class="am-row" data-row>
    <button type="button" class="am-remove" data-remove title="Hapus baris" aria-label="Hapus baris">&times;</button>

    <div class="am-grid">
        <div class="am-cell am-wide">
            <label class="am-lbl">Nama Aset <span class="am-req">*</span></label>
            <input class="am-in {{ $bad('nama_aset') }}" name="items[{{ $i }}][nama_aset]" value="{{ $row['nama_aset'] ?? '' }}" maxlength="255" placeholder="Contoh: Laptop Dell Latitude">
        </div>
        <div class="am-cell">
            <label class="am-lbl">Kategori <span class="am-req">*</span></label>
            <select class="am-in {{ $bad('id_kategori') }}" name="items[{{ $i }}][id_kategori]">
                <option value="">Pilih...</option>
                @foreach ($kategoriOptions as $id => $nama)
                    <option value="{{ $id }}" @selected((string) ($row['id_kategori'] ?? '') === (string) $id)>{{ $nama }}</option>
                @endforeach
            </select>
        </div>
        <div class="am-cell">
            <label class="am-lbl">Merk</label>
            <input class="am-in {{ $bad('merk') }}" name="items[{{ $i }}][merk]" value="{{ $row['merk'] ?? '' }}" maxlength="100">
        </div>
        <div class="am-cell">
            <label class="am-lbl">Model</label>
            <input class="am-in {{ $bad('model') }}" name="items[{{ $i }}][model]" value="{{ $row['model'] ?? '' }}" maxlength="100">
        </div>
        <div class="am-cell">
            <label class="am-lbl">Memory</label>
            <input class="am-in {{ $bad('memory') }}" name="items[{{ $i }}][memory]" value="{{ $row['memory'] ?? '' }}" maxlength="50" placeholder="16GB">
        </div>
        <div class="am-cell">
            <label class="am-lbl">Hard Disk</label>
            <input class="am-in {{ $bad('hard_disk') }}" name="items[{{ $i }}][hard_disk]" value="{{ $row['hard_disk'] ?? '' }}" maxlength="50" placeholder="512GB SSD">
        </div>
        <div class="am-cell">
            <label class="am-lbl">Jumlah <span class="am-req">*</span></label>
            <input class="am-in am-qty {{ $bad('jumlah') }}" type="number" min="1" max="500" step="1" name="items[{{ $i }}][jumlah]" value="{{ $row['jumlah'] ?? '' }}">
        </div>
        <div class="am-cell">
            <label class="am-lbl">Harga Satuan (Rp) <span class="am-req">*</span></label>
            {{-- Kolom tampil memakai titik ribuan (25.000.000); angka murninya dikirim lewat input hidden --}}
            <input type="text" inputmode="numeric" autocomplete="off" placeholder="0"
                   class="am-in am-price-view {{ $bad('harga_satuan') }}"
                   value="{{ isset($row['harga_satuan']) && $row['harga_satuan'] !== '' ? number_format((float) $row['harga_satuan'], 0, ',', '.') : '' }}">
            <input type="hidden" class="am-price" name="items[{{ $i }}][harga_satuan]" value="{{ $row['harga_satuan'] ?? '' }}">
        </div>
    </div>

    <div class="am-subtotal">Subtotal: <strong data-sub>Rp 0</strong></div>

    @foreach ($rowErrors as $msg)
        <div class="am-err">{{ $msg }}</div>
    @endforeach
</div>
