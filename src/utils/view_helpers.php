<?php

/**
 * @author  Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */

function format_number(float $number, int $precision = 2, string $thousands_separator = '&nbsp;'): string
{
    return number_format($number, $precision, '.', $thousands_separator);
}

function format_bytes(int $size, int $precision = 2): string
{
    $base = log($size, 1024);
    $suffixes = array('', 'K', 'M', 'G', 'T');

    return format_number(pow(1024, $base - floor($base)), $precision) . '&nbsp;' . $suffixes[floor($base)];
}

function locale_to_bcp_47(string $locale): string
{
    $splitted_locale = explode('_', $locale, 2);

    if (count($splitted_locale) === 1) {
        return $splitted_locale[0];
    }

    return $splitted_locale[0] . '-' . strtoupper($splitted_locale[1]);
}
