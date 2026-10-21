<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AsetMasukRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Izin akses dijaga middleware permission di routes/web.php
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Buang baris rincian yang benar-benar kosong (mis. baris tambahan yang tidak dipakai)
        $items = collect($this->input('items', []))
            ->filter(fn ($row) => is_array($row) && collect($row)->filter(fn ($v) => filled($v))->isNotEmpty())
            ->values()
            ->all();

        $this->merge(['items' => $items]);
    }

    public function rules(): array
    {
        return [
            'tanggal_masuk' => ['required', 'date', 'before_or_equal:today'],
            'id_vendor'     => ['required', 'exists:vendor,id'],
            'no_po'         => ['nullable', 'string', 'max:50'],
            'afe_cc'        => ['nullable', 'string', 'max:50'],
            'tahun'         => ['nullable', 'digits:4'],

            'items'                 => ['required', 'array', 'min:1'],
            'items.*.nama_aset'     => ['required', 'string', 'max:255'],
            'items.*.id_kategori'   => ['required', 'exists:kategori_aset,id'],
            'items.*.merk'          => ['nullable', 'string', 'max:100'],
            'items.*.model'         => ['nullable', 'string', 'max:100'],
            'items.*.memory'        => ['nullable', 'string', 'max:50'],
            'items.*.hard_disk'     => ['nullable', 'string', 'max:50'],
            'items.*.jumlah'        => ['required', 'integer', 'min:1', 'max:500'],
            'items.*.harga_satuan'  => ['required', 'numeric', 'min:0', 'max:9999999999999'],
        ];
    }

    public function attributes(): array
    {
        return [
            'tanggal_masuk'        => 'Tanggal masuk',
            'id_vendor'            => 'Vendor',
            'no_po'                => 'No. PO / Kontrak',
            'afe_cc'               => 'AFE / CC',
            'tahun'                => 'Year',
            'items'                => 'Rincian aset',
            'items.*.nama_aset'    => 'Nama aset',
            'items.*.id_kategori'  => 'Kategori',
            'items.*.merk'         => 'Merk',
            'items.*.model'        => 'Model',
            'items.*.memory'       => 'Memory',
            'items.*.hard_disk'    => 'Hard disk',
            'items.*.jumlah'       => 'Jumlah',
            'items.*.harga_satuan' => 'Harga satuan',
        ];
    }

    public function messages(): array
    {
        return [
            'required'        => ':attribute wajib diisi.',
            'digits'          => ':attribute harus 4 digit (contoh 2026).',
            'exists'          => ':attribute tidak valid.',
            'date'            => 'Format :attribute tidak valid.',
            'before_or_equal' => ':attribute tidak boleh lebih dari hari ini.',
            'max'             => ':attribute terlalu besar atau terlalu panjang.',
            'min'             => ':attribute tidak boleh kurang dari :min.',
            'integer'         => ':attribute harus berupa bilangan bulat.',
            'numeric'         => ':attribute harus berupa angka.',
            'items.required'  => 'Tambahkan minimal satu baris rincian aset.',
            'items.min'       => 'Tambahkan minimal satu baris rincian aset.',
        ];
    }
}
