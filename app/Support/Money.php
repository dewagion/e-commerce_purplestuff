<?php

namespace App\Support;

class Money
{
    public static function rupiah($amount): string
    {
        $amount = (float) $amount;
        return 'Rp. ' . number_format($amount, 0, ',', '.');
    }
}