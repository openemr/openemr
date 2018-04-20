<?php

/**
 * @file
 * Convenience file that registers autoload handler for HTML Purifier.
 *
 * @warning
 *      This autoloader does not contain the compatibility code seen in
 *      HTMLPurifier_Bootstrap; the user is expected to make any necessary
 *      changes to use this library.
 */

if (function_exists('spl_autoload_register')) {
    spl_autoload_register(array('HTMLPurifierExtras', 'autoload'));
    if (function_exists('__autoload')) {
        // Be polite and ensure that userland autoload gets retained
        spl_autoload_register('__autoload');
    }
} elseif (!function_exists('__autoload')) {
    require dirname(__FILE__) . '/HTMLPurifierExtras.autoload-legacy.php';
}

// vim: et sw=4 sts=4
