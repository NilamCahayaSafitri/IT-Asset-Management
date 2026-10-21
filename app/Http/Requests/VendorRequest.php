<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VendorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['nama_vendor' => trim((string) $this->input('nama_vendor'))]);
    }

    public function rules(): array
    {
        return [
            'nama_vendor' => [
                'required', 'string', 'max:255',
                Rule::unique('vendor', 'nama_vendor')->ignore($this->route('vendor')?->id),
            ],
            'alamat' => ['nullable', 'string', 'max:1000'],
            'kontak' => ['nullable', 'string', 'max:30'],
            'email'  => ['nullable', 'email', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'nama_vendor' => 'Nama vendor',
            'alamat'      => 'Alamat',
            'kontak'      => 'Kontak',
            'email'       => 'Email',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'unique'   => ':attribute sudah ada.',
            'max'      => ':attribute terlalu panjang (maksimal :max karakter).',
            'email'    => 'Format :attribute tidak valid.',
            'exists'   => ':attribute tidak valid.',
        ];
    }
}
