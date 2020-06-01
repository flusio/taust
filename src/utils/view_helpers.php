<?php

function format_number($number)
{
    return number_format($number, 2, '.', '&nbsp;');
}

function format_bytes($size, $precision = 2)
{
    $base = log($size, 1024);
    $suffixes = array('', 'K', 'M', 'G', 'T');

    return round(pow(1024, $base - floor($base)), $precision) . '&nbsp;' . $suffixes[floor($base)];
}
