<?php

function format_number($number, $precision = 2, $thousands_separator = '&nbsp;')
{
    return number_format($number, $precision, '.', $thousands_separator);
}

function format_bytes($size, $precision = 2)
{
    $base = log($size, 1024);
    $suffixes = array('', 'K', 'M', 'G', 'T');

    return format_number(pow(1024, $base - floor($base)), $precision) . '&nbsp;' . $suffixes[floor($base)];
}

function locale_to_bcp_47($locale)
{
    $splitted_locale = explode('_', $locale, 2);
    if (!$splitted_locale) {
        // This is line is virtually inaccessible
        return $locale;
    }

    if (count($splitted_locale) === 1) {
        return $splitted_locale[0];
    }

    return $splitted_locale[0] . '-' . strtoupper($splitted_locale[1]);
}
