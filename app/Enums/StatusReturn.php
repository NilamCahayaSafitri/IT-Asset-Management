<?php

namespace App\Enums;

/**
 * Kondisi barang saat dikembalikan (kolom transaksi_detail.status_return).
 * Pilihan persis seperti di surat EAF: "Status: a. Deployable b. Surplus c. Waiting for Disposal d. Broken".
 */
enum StatusReturn: string
{
    case Deployable = 'Deployable';
    case Surplus = 'Surplus';
    case WaitingForDisposal = 'Waiting for Disposal';
    case Broken = 'Broken';

    /** Huruf pilihan di surat (a-d) */
    public function huruf(): string
    {
        return match ($this) {
            self::Deployable         => 'a',
            self::Surplus            => 'b',
            self::WaitingForDisposal => 'c',
            self::Broken             => 'd',
        };
    }

    /** Status aset setelah barang dikembalikan */
    public function toStatusAset(): StatusAset
    {
        return match ($this) {
            self::Deployable         => StatusAset::Available,
            self::Surplus            => StatusAset::Surplus,
            self::WaitingForDisposal => StatusAset::WaitingForDisposal,
            self::Broken             => StatusAset::Broken,
        };
    }

    /** [value => "a. Deployable"] untuk dropdown di form Return */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $s) => [$s->value => $s->huruf().'. '.$s->value])->all();
    }
}
