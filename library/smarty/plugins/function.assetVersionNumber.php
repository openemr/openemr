<?php

/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 *
 * Copyright (C) 2007 Christian Navalici
 * Copyright (C) 2019 Brady Miller <brady.g.miller@gmail.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 */

use OpenEMR\Core\OEGlobalsBag;

/**
 * Smarty {assetVersionNumber} function plugin.
 *
 * Type:     function<br />
 * Name:     assetVersionNumber<br />
 * Purpose:  Return the version number to be used in a script or style asset include ie script?v={assetVersionNumber}<br />
 *
 * Examples:
 *
 * {assetVersionNumber}
 *
 * @param array $params
 * @param mixed $smarty
 */
function smarty_function_assetVersionNumber($params, &$smarty): string
{
    // version.php sets v_js_includes to an int for a release and to an md5
    // string in dev, but interface/globals.php stores null when version.php was
    // already included in another scope. Narrow the raw value rather than
    // calling getString(): the key is present-but-null in that case, so
    // getString() would skip its default and throw UnexpectedValueException.
    $configured = OEGlobalsBag::getInstance()->get('v_js_includes');

    // Any unusable value falls back to the current timestamp. A constant
    // fallback would be cached by the browser indefinitely, so a stale asset
    // would survive until a usable version came back AND its value changed. A
    // per-request value guarantees a cache miss instead, which is the safe way
    // for a cache buster to fail.
    $version = match (true) {
        is_string($configured) && $configured !== '' => $configured,
        is_int($configured) => (string) $configured,
        default => (string) time(),
    };

    return attr_url($version);
}
