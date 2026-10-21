<?php

namespace App\Enums;

/** Fungsi aset di baris Deploy surat EAF: a. Primary b. Shared c. Training d. Other (explain) */
enum AssetFunction: string
{
    case Primary = 'Primary';
    case Shared = 'Shared';
    case Training = 'Training';
    case Other = 'Other';

    public function huruf(): string
    {
        return match ($this) {
            self::Primary  => 'a',
            self::Shared   => 'b',
            self::Training => 'c',
            self::Other    => 'd',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $s) => [$s->value => $s->huruf().'. '.$s->value])->all();
    }
}
