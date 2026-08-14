<?php

namespace App\Support;

/**
 * Formats WooCommerce price meta (raw numeric strings) as the site displays
 * them: thousands-separated, no decimals, "Ks" suffix (Myanmar Kyat) —
 * matching the reference markup's woocommerce-Price-amount output.
 */
class Money
{
    public static function format(string|float|int|null $amount): string
    {
        if ($amount === null || $amount === '') {
            return '';
        }

        return number_format((float) $amount) . 'Ks';
    }

    /**
     * @param  array{min: string, max: string, is_range: bool}|null  $price
     */
    public static function range(?array $price): string
    {
        if (!$price) {
            return '';
        }

        if (!$price['is_range']) {
            return self::format($price['min']);
        }

        return self::format($price['min']) . ' – ' . self::format($price['max']);
    }
}
