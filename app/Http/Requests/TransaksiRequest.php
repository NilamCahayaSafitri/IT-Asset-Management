<?php

namespace App\Http\Requests;

use App\Enums\AssetFunction;
use App\Enums\StatusReturn;
use App\Models\Transaksi;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransaksiRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Izin akses dijaga middleware permission di routes/web.php
        return true;
    }

    /** Type of Allocation -> bagian mana yang tampil (aturan sama dengan prototype) */
    public function showDeploy(): bool
    {
        return array_intersect((array) $this->input('types', []), ['Deploy', 'Move', 'Replace/Swap']) !== [];
    }

    public function showReturn(): bool
    {
        return array_intersect((array) $this->input('types', []), ['Return', 'Replace/Swap']) !== [];
    }

    protected function prepareForValidation(): void
    {
        // Buang baris aset yang belum memilih aset (baris kosong)
        $bersih = fn ($rows) => collect(is_array($rows) ? $rows : [])
            ->filter(fn ($r) => is_array($r) && filled($r['id_aset'] ?? null))
            ->values()
            ->all();

        $this->merge([
            'deploy' => $bersih($this->input('deploy')),
            'return' => $bersih($this->input('return')),
            'types'  => array_values((array) $this->input('types', [])),
        ]);
    }

    public function rules(): array
    {
        $showDeploy = $this->showDeploy();
        $showReturn = $this->showReturn();

        return [
            'types'   => ['required', 'array', 'min:1'],
            'types.*' => [Rule::in(Transaksi::TYPES)],

            'id_pegawai' => ['required', 'exists:pegawai,id'],
            'cc'         => ['nullable', 'string', 'max:50'],
            'room_no'    => ['nullable', 'string', 'max:50'],

            // Procurement Information
            'po_contract_no' => ['nullable', 'string', 'max:50'],
            'afe_cc'         => ['nullable', 'string', 'max:50'],
            'tahun'          => ['nullable', 'digits:4'],

            'deploy_note' => ['nullable', 'string', 'max:255'],
            'is_pinjaman' => ['nullable', 'boolean'],
            'lama_pinjam' => ['nullable', 'integer', 'min:1', 'max:365'],

            'return_ref'    => ['nullable', 'string', 'max:40'],
            'return_date'   => ['nullable', 'date', 'before_or_equal:today'],
            'alasan_return' => ['nullable', 'string', 'max:255'],
            'remarks'       => ['nullable', 'string', 'max:2000'],

            'deploy'                       => $showDeploy ? ['required', 'array', 'min:1'] : ['nullable', 'array'],
            'deploy.*.id_aset'             => ['required', 'exists:aset,id', 'distinct'],
            'deploy.*.asset_function'      => ['required', Rule::in(array_column(AssetFunction::cases(), 'value'))],
            'deploy.*.asset_function_lain' => ['nullable', 'string', 'max:255'],

            'return'                  => $showReturn ? ['required', 'array', 'min:1'] : ['nullable', 'array'],
            'return.*.id_aset'        => ['required', 'exists:aset,id', 'distinct'],
            'return.*.status_return'  => ['required', Rule::in(array_column(StatusReturn::cases(), 'value'))],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            // Asset Function "Other" wajib dijelaskan
            foreach ((array) $this->input('deploy', []) as $i => $row) {
                if (($row['asset_function'] ?? null) === 'Other' && blank($row['asset_function_lain'] ?? null)) {
                    $v->errors()->add("deploy.$i.asset_function_lain", 'Fungsi aset "Other" wajib dijelaskan.');
                }
            }

            // Aset yang sama tidak boleh ada di Deploy/Move sekaligus Return
            $deploy = collect($this->input('deploy', []))->pluck('id_aset')->map(fn ($x) => (int) $x);
            $return = collect($this->input('return', []))->pluck('id_aset')->map(fn ($x) => (int) $x);

            if ($this->showDeploy() && $this->showReturn() && $deploy->intersect($return)->isNotEmpty()) {
                $v->errors()->add('aset', 'Aset yang sama tidak boleh ada di tabel Deploy/Move sekaligus Return.');
            }
        });
    }

    public function attributes(): array
    {
        return [
            'types'                        => 'Type of Allocation',
            'id_pegawai'                   => 'User Name',
            'cc'                           => 'CC',
            'room_no'                      => 'Room No',
            'po_contract_no'               => 'PO / Contract No',
            'afe_cc'                       => 'AFE / CC',
            'tahun'                        => 'Year',
            'deploy_note'                  => 'Keperluan / Catatan',
            'lama_pinjam'                  => 'Lama pinjam',
            'return_date'                  => 'Tanggal Kembali',
            'alasan_return'                => 'Reason',
            'remarks'                      => 'Additional Remarks',
            'deploy'                       => 'Tabel Deploy / Move Equipment',
            'return'                       => 'Tabel Return Equipment',
            'deploy.*.id_aset'             => 'Aset (Deploy / Move)',
            'deploy.*.asset_function'      => 'Asset Function',
            'deploy.*.asset_function_lain' => 'Penjelasan Asset Function',
            'return.*.id_aset'             => 'Aset (Return)',
            'return.*.status_return'       => 'Status',
        ];
    }

    public function messages(): array
    {
        return [
            'required'        => ':attribute wajib diisi.',
            'exists'          => ':attribute tidak valid.',
            'max'             => ':attribute terlalu panjang.',
            'digits'          => ':attribute harus 4 digit (contoh 2026).',
            'date'            => 'Format :attribute tidak valid.',
            'before_or_equal' => ':attribute tidak boleh lebih dari hari ini.',
            'distinct'        => 'Aset yang sama dipilih lebih dari sekali.',
            'integer'         => ':attribute harus berupa bilangan bulat.',
            'min'             => ':attribute tidak valid.',
            'in'              => ':attribute tidak valid.',
            'types.required'  => 'Pilih minimal satu Type of Allocation.',
            'types.min'       => 'Pilih minimal satu Type of Allocation.',
            'deploy.required' => 'Isi minimal satu aset di tabel Deploy / Move Equipment.',
            'deploy.min'      => 'Isi minimal satu aset di tabel Deploy / Move Equipment.',
            'return.required' => 'Isi minimal satu aset di tabel Return Equipment.',
            'return.min'      => 'Isi minimal satu aset di tabel Return Equipment.',
        ];
    }
}
