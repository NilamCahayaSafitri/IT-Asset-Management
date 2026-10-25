<?php

namespace App\Services;

use App\Enums\AssetFunction;
use App\Enums\StatusAset;
use App\Enums\StatusReturn;
use App\Models\Aset;
use App\Models\Pegawai;
use App\Models\Ruangan;
use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Menyimpan satu surat transaksi (Equipment Allocation Form) beserta efeknya pada aset.
 *
 * Aturan:
 *  - Baris "Deploy / Move":
 *      aset Available      -> di-Deploy (butuh Type Deploy atau Replace/Swap)  => Deployed (atau Borrowed jika pinjaman sementara)
 *      aset Deployed/Borrowed -> di-Move (butuh Type Move)                      => status tidak berubah
 *  - Baris "Return": aset harus Deployed/Borrowed; status baru mengikuti status_return (StatusReturn::toStatusAset)
 *  - Room No yang cocok dengan nama Ruangan di Master Data ikut memperbarui lokasi aset.
 */
class TransaksiService
{
    public function create(array $data, int $adminId): Transaksi
    {
        return DB::transaction(function () use ($data, $adminId) {
            $types = $data['types'];
            $isDeploy = in_array('Deploy', $types, true);
            $isReturn = in_array('Return', $types, true);
            $isSwap   = in_array('Replace/Swap', $types, true);
            $isMove   = in_array('Move', $types, true);

            $showDeploy = $isDeploy || $isMove || $isSwap;
            $showReturn = $isReturn || $isSwap;

            $deployRows = $showDeploy ? array_values($data['deploy'] ?? []) : [];
            $returnRows = $showReturn ? array_values($data['return'] ?? []) : [];

            // Kunci baris aset yang terlibat supaya dua admin tidak memproses aset yang sama bersamaan
            $ids = collect($deployRows)->pluck('id_aset')->merge(collect($returnRows)->pluck('id_aset'))->unique()->values();
            $aset = Aset::whereIn('id', $ids)->lockForUpdate()->get()->keyBy('id');

            $this->periksaStatus($aset, $deployRows, $returnRows, $isDeploy || $isSwap, $isMove);
            $this->periksaPemegang($aset, $returnRows, (int) $data['id_pegawai']);

            $tanggal = now();
            $pinjaman = $showDeploy && ! empty($data['is_pinjaman']);
            $lama = (int) ($data['lama_pinjam'] ?? 0);

            $transaksi = Transaksi::create([
                'no_referensi'      => Transaksi::nextReferensi($tanggal),
                'tanggal_transaksi' => $tanggal->toDateString(),
                'is_deploy'         => $isDeploy,
                'is_return'         => $isReturn,
                'is_replace_swap'   => $isSwap,
                'is_move'           => $isMove,
                'id_pegawai'        => $data['id_pegawai'],
                'cc'                => $data['cc'] ?? null,
                'room_no'           => $data['room_no'] ?? null,
                // Procurement Information: khusus initial deployment
                'po_contract_no'    => $isDeploy ? ($data['po_contract_no'] ?? null) : null,
                'afe_cc'            => $isDeploy ? ($data['afe_cc'] ?? null) : null,
                'tahun'             => $isDeploy ? ($data['tahun'] ?? null) : null,
                'alasan_return'     => $showReturn ? ($data['alasan_return'] ?? null) : null,
                'catatan'           => $this->susunCatatan($data, $showDeploy, $showReturn),
                'status_dokumen'    => 'Menunggu TTD',
                'id_users'          => $adminId,
            ]);

            $ruangan = $this->cariRuangan($data['room_no'] ?? null);

            foreach ($deployRows as $row) {
                $a = $aset[$row['id_aset']];
                $deployBaru = $a->status === StatusAset::Available;
                $function = $row['asset_function'];

                TransaksiDetail::create([
                    'id_transaksi'        => $transaksi->id,
                    'id_aset'             => $a->id,
                    'bagian'              => 'deploy',
                    'asset_function'      => $function,
                    'asset_function_lain' => $function === AssetFunction::Other->value ? ($row['asset_function_lain'] ?? null) : null,
                    'is_pinjaman'         => $deployBaru && $pinjaman,
                    'batas_kembali'       => ($deployBaru && $pinjaman && $lama > 0) ? $tanggal->copy()->addDays($lama)->toDateString() : null,
                ]);

                if ($deployBaru) {
                    $a->status = $pinjaman ? StatusAset::Borrowed : StatusAset::Deployed;
                }
                if ($ruangan) {
                    $a->id_ruangan = $ruangan->id;
                }
                $a->save();
            }

            foreach ($returnRows as $row) {
                $a = $aset[$row['id_aset']];
                $status = StatusReturn::from($row['status_return']);

                TransaksiDetail::create([
                    'id_transaksi'  => $transaksi->id,
                    'id_aset'       => $a->id,
                    'bagian'        => 'return',
                    'status_return' => $status->value,
                ]);

                $a->status = $status->toStatusAset();
                $a->save();
            }

            return $transaksi;
        });
    }

    /** @throws ValidationException */
    private function periksaStatus($aset, array $deployRows, array $returnRows, bool $bolehDeploy, bool $bolehMove): void
    {
        $error = [];

        foreach ($deployRows as $row) {
            $a = $aset->get($row['id_aset']);
            if (! $a) {
                $error[] = 'Aset tidak ditemukan.';
                continue;
            }

            $tersedia = $a->status === StatusAset::Available;
            $dipakai = (bool) $a->status?->sedangDipakai();

            if ($tersedia && $bolehDeploy) {
                continue;
            }
            if ($dipakai && $bolehMove) {
                continue;
            }

            if ($tersedia) {
                $error[] = "Aset {$a->kode_aset} berstatus Available. Move hanya untuk aset yang sedang dipakai; centang Deploy untuk mengalokasikannya.";
            } elseif ($dipakai) {
                $error[] = "Aset {$a->kode_aset} sedang {$a->status->label()}. Centang Move untuk memindahkannya, atau Return untuk mengembalikannya.";
            } else {
                $error[] = "Aset {$a->kode_aset} berstatus {$a->status?->label()}, tidak bisa dialokasikan (hanya aset Available).";
            }
        }

        foreach ($returnRows as $row) {
            $a = $aset->get($row['id_aset']);
            if (! $a) {
                $error[] = 'Aset tidak ditemukan.';
                continue;
            }
            if (! $a->status?->sedangDipakai()) {
                $error[] = "Aset {$a->kode_aset} berstatus {$a->status?->label()}, tidak sedang dipakai sehingga tidak bisa di-Return.";
            }
        }

        if ($error) {
            throw ValidationException::withMessages(['aset' => $error]);
        }
    }

    /**
     * Aset yang di-Return harus sedang dipegang oleh User Name pada surat.
     * Pemegang = pegawai pada transaksi Deploy/Move terakhir untuk aset itu.
     * Aset yang pemegangnya belum tercatat (mis. data awal tanpa transaksi) dibolehkan.
     *
     * @throws ValidationException
     */
    private function periksaPemegang($aset, array $returnRows, int $idPegawai): void
    {
        if (! $returnRows) {
            return;
        }

        $pemegang = TransaksiDetail::whereIn('id_aset', collect($returnRows)->pluck('id_aset'))
            ->where('bagian', 'deploy')
            ->with('transaksi:id,id_pegawai')
            ->orderByDesc('id')
            ->get()
            ->groupBy('id_aset')
            ->map(fn ($grup) => $grup->first()->transaksi?->id_pegawai);

        $error = [];
        foreach ($returnRows as $row) {
            $idAset = (int) $row['id_aset'];
            $holder = $pemegang->get($idAset);

            if ($holder !== null && (int) $holder !== $idPegawai) {
                $nama = Pegawai::find($holder)?->nama ?? 'pegawai lain';
                $error[] = "Aset {$aset[$idAset]->kode_aset} saat ini dipegang {$nama}, bukan pegawai yang dipilih. Pilih User Name pemegangnya.";
            }
        }

        if ($error) {
            throw ValidationException::withMessages(['aset' => $error]);
        }
    }

    /** Menyusun "Additional Remarks" persis seperti prototype */
    private function susunCatatan(array $data, bool $showDeploy, bool $showReturn): ?string
    {
        $bagian = [];

        if ($showDeploy) {
            // Catatan hanya berisi yang diketik pengguna; status pinjaman sementara tidak ikut dicetak di surat
            $note = trim((string) ($data['deploy_note'] ?? ''));
            if ($note !== '') {
                $bagian[] = $note.'.';
            }
        }

        if ($showReturn) {
            if (filled($data['return_ref'] ?? null)) {
                $bagian[] = 'Ref. '.$data['return_ref'].'.';
            }
            if (filled($data['return_date'] ?? null)) {
                $bagian[] = 'Dikembalikan '.Transaksi::tanggalIndo(\Illuminate\Support\Carbon::parse($data['return_date'])).'.';
            }
            if (filled($data['alasan_return'] ?? null)) {
                $bagian[] = 'Alasan: '.$data['alasan_return'].'.';
            }
        }

        $catatan = collect([implode(' ', $bagian), trim((string) ($data['remarks'] ?? ''))])->filter()->implode(' ');

        return $catatan === '' ? null : $catatan;
    }

    private function cariRuangan(?string $roomNo): ?Ruangan
    {
        $roomNo = trim((string) $roomNo);

        return $roomNo === '' ? null : Ruangan::whereRaw('LOWER(nama_ruangan) = ?', [mb_strtolower($roomNo)])->first();
    }
}
