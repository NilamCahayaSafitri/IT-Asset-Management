<?php

namespace App\Enums;

/**
 * Kondisi aset SAAT INI (kolom aset.status). Memakai istilah Inggris agar sejalan dengan surat EAF.
 *
 * Diubah otomatis oleh sistem:
 *   Aset Masuk              -> Available
 *   Deploy                  -> Deployed (atau Borrowed jika ditandai pinjaman sementara)
 *   Return + status_return  -> lihat StatusReturn::toStatusAset()
 *   Move                    -> status tidak berubah, hanya ruangan
 * Diubah manual dari Data Aset: lihat manual().
 */
enum StatusAset: string
{
    case Available = 'Available';
    case Deployed = 'Deployed';
    case Borrowed = 'Borrowed';
    case Maintenance = 'Maintenance';
    case Broken = 'Broken';
    case Surplus = 'Surplus';
    case WaitingForDisposal = 'Waiting for Disposal';
    case Inactive = 'Inactive';

    public function label(): string
    {
        return $this->value;
    }

    /** [latar, teks] untuk badge; dipakai inline supaya tidak bergantung pada CSS tertentu */
    public function warna(): array
    {
        return match ($this) {
            self::Available          => ['#e1f0e6', '#3c8558'],
            self::Deployed           => ['#dbe6f4', '#2c5aa0'],
            self::Borrowed           => ['#fdead9', '#c8631b'],
            self::Maintenance        => ['#fbe4e1', '#c4463a'],
            self::Broken             => ['#f6d2cd', '#9b2f25'],
            self::Surplus            => ['#f3eadb', '#8a6a1f'],
            self::WaitingForDisposal => ['#fde7d3', '#b4540a'],
            self::Inactive           => ['#e7e9ec', '#5b6572'],
        };
    }

    /** Hanya aset Available yang boleh di-deploy / dipinjamkan */
    public function bisaDialokasikan(): bool
    {
        return $this === self::Available;
    }

    /** Aset yang sedang berada di pegawai: harus di-Return dulu sebelum status lain */
    public function sedangDipakai(): bool
    {
        return in_array($this, [self::Deployed, self::Borrowed], true);
    }

    /** Status yang boleh dipilih manual dari Data Aset (Deployed/Borrowed hanya lewat transaksi) */
    public static function manual(): array
    {
        return [
            self::Available,
            self::Maintenance,
            self::Broken,
            self::Surplus,
            self::WaitingForDisposal,
            self::Inactive,
        ];
    }

    /** [value => label] untuk dropdown filter / form */
    public static function options(?array $cases = null): array
    {
        return collect($cases ?? self::cases())->mapWithKeys(fn (self $s) => [$s->value => $s->label()])->all();
    }
}
