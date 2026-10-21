<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DepartemenRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Izin akses dijaga middleware permission di routes/web.php
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['nama_departemen' => trim((string) $this->input('nama_departemen'))]);
    }

    public function rules(): array
    {
        return [
            'nama_departemen' => [
                'required', 'string', 'max:255',
                Rule::unique('departemen', 'nama_departemen')->ignore($this->route('departemen')?->id),
            ],
        ];
    }

    public function attributes(): array
    {
        return ['nama_departemen' => 'Nama departemen'];
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
