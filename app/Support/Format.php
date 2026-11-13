<?php

namespace App\Support;

class Format
{
    /** Format angka menjadi Rupiah, mis. 50000 -> "Rp 50.000". */
    public static function rupiah(int|float|null $amount): string
    {
        return 'Rp ' . number_format((float) ($amount ?? 0), 0, ',', '.');
    }
}
