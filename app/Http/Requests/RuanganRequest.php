<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RuanganRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['nama_ruangan' => trim((string) $this->input('nama_ruangan'))]);
    }

    public function rules(): array
    {
        return [
            'nama_ruangan' => [
                'required', 'string', 'max:255',
                // Nama boleh sama di departemen berbeda, tapi tidak kembar dalam satu departemen
                Rule::unique('ruangan', 'nama_ruangan')
                    ->where('id_departemen', $this->input('id_departemen'))
                    ->ignore($this->route('ruangan')?->id),
            ],
            'id_departemen' => ['required', 'exists:departemen,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'nama_ruangan'  => 'Nama ruangan',
            'id_departemen' => 'Departemen',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'unique'   => 'Ruangan ini sudah ada di departemen tersebut.',
            'max'      => ':attribute terlalu panjang (maksimal :max karakter).',
            'exists'   => ':attribute tidak valid.',
        ];
    }
}
