<?php

namespace App\Http\Requests;

use App\Enums\StatusAset;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AsetRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Izin akses dijaga middleware permission di routes/web.php
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['nama_aset' => trim((string) $this->input('nama_aset'))]);
    }

    public function rules(): array
    {
        $manual = array_map(fn (StatusAset $s) => $s->value, StatusAset::manual());

        return [
            'nama_aset'         => ['required', 'string', 'max:255'],
            'id_kategori'       => ['required', 'exists:kategori_aset,id'],
            'merk'              => ['nullable', 'string', 'max:100'],
            'model'             => ['nullable', 'string', 'max:100'],
            'serial_number'     => ['nullable', 'string', 'max:100',
                                    Rule::unique('aset', 'serial_number')->ignore($this->route('aset')?->id)],
            'memory'            => ['nullable', 'string', 'max:50'],
            'hard_disk'         => ['nullable', 'string', 'max:50'],
            'tanggal_perolehan' => ['nullable', 'date', 'before_or_equal:today'],
            'harga'             => ['nullable', 'numeric', 'min:0', 'max:9999999999999'],
            'umur'              => ['nullable', 'integer', 'min:0', 'max:100'],
            'id_ruangan'        => ['nullable', 'exists:ruangan,id'],
            // Kosong = Tersedia (tambah) / tidak diubah (ubah). Dialokasikan & Dipinjam hanya lewat transaksi.
            'status'            => ['nullable', Rule::in($manual)],
        ];
    }

    public function attributes(): array
    {
        return [
            'nama_aset'         => 'Nama aset',
            'id_kategori'       => 'Kategori',
            'merk'              => 'Merk',
            'model'             => 'Model',
            'serial_number'     => 'Serial number',
            'memory'            => 'Memory',
            'hard_disk'         => 'Hard disk',
            'tanggal_perolehan' => 'Tanggal perolehan',
            'harga'             => 'Harga',
            'umur'              => 'Umur ekonomis',
            'id_ruangan'        => 'Lokasi',
            'status'            => 'Status',
        ];
    }

    public function messages(): array
    {
        return [
            'required'              => ':attribute wajib diisi.',
            'unique'                => ':attribute sudah dipakai aset lain.',
            'max'                   => ':attribute terlalu panjang atau terlalu besar.',
            'min'                   => ':attribute tidak boleh kurang dari :min.',
            'exists'                => ':attribute tidak valid.',
            'date'                  => 'Format :attribute tidak valid.',
            'numeric'               => ':attribute harus berupa angka.',
            'integer'               => ':attribute harus berupa bilangan bulat.',
            'in'                    => ':attribute tidak valid.',
            'before_or_equal'       => ':attribute tidak boleh lebih dari hari ini.',
        ];
    }
}
