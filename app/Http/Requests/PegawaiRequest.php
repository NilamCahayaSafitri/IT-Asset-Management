<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PegawaiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'kode_pegawai' => trim((string) $this->input('kode_pegawai')),
            'nama'         => trim((string) $this->input('nama')),
        ]);
    }

    public function rules(): array
    {
        return [
            'kode_pegawai' => [
                'required', 'string', 'max:30',
                Rule::unique('pegawai', 'kode_pegawai')->ignore($this->route('pegawai')?->id),
            ],
            'nama'          => ['required', 'string', 'max:255'],
            'jabatan'       => ['nullable', 'string', 'max:255'],
            'id_departemen' => ['required', 'exists:departemen,id'],
            'email'         => ['nullable', 'email', 'max:255'],
            'kontak'        => ['nullable', 'string', 'max:30'],
            'room_no'       => ['nullable', 'string', 'max:50'],
        ];
    }

    public function attributes(): array
    {
        return [
            'kode_pegawai'  => 'User ID',
            'nama'          => 'Nama',
            'jabatan'       => 'Jabatan',
            'id_departemen' => 'Departemen',
            'email'         => 'Email',
            'kontak'        => 'Phone No',
            'room_no'       => 'Room No',
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
