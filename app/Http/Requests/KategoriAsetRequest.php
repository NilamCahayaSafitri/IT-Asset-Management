<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KategoriAsetRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Izin akses sudah dijaga middleware permission di routes/web.php
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nama_kategori' => trim((string) $this->input('nama_kategori')),
        ]);
    }

    public function rules(): array
    {
        // Saat edit, abaikan baris milik sendiri pada cek unique
        $kategori = $this->route('kategoriAset');

        return [
            'nama_kategori' => [
                'required',
                'string',
                'max:255',
                Rule::unique('kategori_aset', 'nama_kategori')->ignore($kategori?->id),
            ],
        ];
    }

    public function attributes(): array
    {
        return ['nama_kategori' => 'nama kategori'];
    }

    public function messages(): array
    {
        return [
            'nama_kategori.required' => 'Nama kategori wajib diisi.',
            'nama_kategori.max'      => 'Nama kategori maksimal 255 karakter.',
            'nama_kategori.unique'   => 'Nama kategori ini sudah ada.',
        ];
    }
}
