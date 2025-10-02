<?php

function csv_like_join($array, $quote_all = false)
{
    $result = '';
    $first = true;

    foreach ($array as $value) {
        if ($first) {
            $first = false;
        } else {
            $result .= ',';
        }

        if ($quote_all) {
            $result .= csv_quote($value);
        } else {
            $result .= maybe_csv_quote($value);
        }
    }

    return $result;
}

function csv_quote($string)
{
    return '"' . str_replace($string, '"', '""') . '"';
}

function maybe_csv_quote($string)
{
    if (need_csv_quote($string)) {
        return csv_quote($string);
    }

    return $string;
}

function need_csv_quote($string)
{
    if (
        !str_contains((string) $string, ',')
        && !str_contains((string) $string, '"')
        && !str_contains((string) $string, "\r")
        && !str_contains((string) $string, "\n")
    ) {
        return false;
    }

    return true;
}

function split_csv_line($record)
{
    $first = null;

    if (strlen((string) $record) == 0) {
        return [''];
    }

    if ($record[0] === '"') {
        $first = '';
        $start = 1;

        while (
            $start < strlen((string) $record)
            && ($end = strpos((string) $record, '"', $start)) !== false
            && $end < strlen((string) $record) - 1
            && $record[$end + 1] !== ','
        ) {
            if ($record[$end + 1] !== '"') {
                die("Found characters between double-quoted field and comma.");
            }

            $first .= substr((string) $record, $start, $end - $start - 1);
            $start = $end + 2;
        }

        if ($start < strlen((string) $record) || $end === false) {
            die("Could not find end-quote for double-quoted field");
        }

        $first .= substr((string) $record, $start, $end - $start - 1);

        if ($end >= strlen((string) $record) - 1) {
            return [$first];
        }

        /* Assertion: $record[$end + 1] == ',' */
        $rest = substr((string) $record, $end + 2);
    } else {
        $end = strpos((string) $record, ',');

        if ($end === false) {
            return [$record];
        }

        /* Assertion: $end < strlen($record) */

        $first = substr((string) $record, 0, $end);
        $rest = substr((string) $record, $end + 1);
    }

    $fields = split_csv_line($rest);
    array_unshift($fields, $first);
    return $fields;
}
