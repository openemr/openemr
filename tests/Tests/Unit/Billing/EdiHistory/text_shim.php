<?php

/**
 * Stand-in for the legacy OpenEMR text() helper. X12File only calls it on
 * error paths, which valid fixtures never trigger, but the shim keeps the
 * reader usable here without the rest of the OpenEMR bootstrap.
 */
if (!function_exists('text')) {
    function text($value): string
    {
        return is_string($value) ? $value : '';
    }
}
