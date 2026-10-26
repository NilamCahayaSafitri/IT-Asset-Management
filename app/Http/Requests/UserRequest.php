<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Izin akses dijaga middleware permission:kelola-user di routes/web.php
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name'     => trim((string) $this->input('name')),
            'username' => mb_strtolower(trim((string) $this->input('username'))),
            'email'    => mb_strtolower(trim((string) $this->input('email'))),
        ]);
    }

    public function rules(): array
    {
        $user = $this->route('user');

        $rules = [
            'name'     => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('users', 'username')->ignore($user?->id)],
            'email'    => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'kontak'   => ['nullable', 'string', 'max:30'],
            'role'     => ['required', Rule::exists('roles', 'name')->where('guard_name', 'web')],
            'status'   => ['required', Rule::in(['aktif', 'nonaktif'])],
        ];

        // Password hanya diisi saat membuat akun; ubah password lewat "Reset Password"
        if ($this->isMethod('POST')) {
            $rules['password'] = ['required', 'confirmed', Password::min(8)];
        }

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'name'     => 'Nama lengkap',
            'username' => 'Username',
            'email'    => 'Email',
            'kontak'   => 'Kontak',
            'role'     => 'Role',
            'status'   => 'Status',
            'password' => 'Password',
        ];
    }

    public function messages(): array
    {
        return [
            'required'        => ':attribute wajib diisi.',
            'unique'          => ':attribute sudah dipakai akun lain.',
            'max'             => ':attribute terlalu panjang (maksimal :max karakter).',
            'email'           => 'Format email tidak valid.',
            'alpha_dash'      => 'Username hanya boleh berisi huruf, angka, strip (-), dan garis bawah (_).',
            'exists'          => ':attribute tidak valid.',
            'in'              => ':attribute tidak valid.',
            'confirmed'       => 'Konfirmasi password tidak sama.',
            'password.min'    => 'Password minimal 8 karakter.',
        ];
    }
}
