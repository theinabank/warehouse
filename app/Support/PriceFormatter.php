<?php

namespace App\Support;

final class PriceFormatter
{
    public static function formatInEur(int $amount): string
    {
        return number_format(
            $amount / 100,
            2,
            '.',
            ''
        );
    }
}
