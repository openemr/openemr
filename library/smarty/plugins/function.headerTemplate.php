<?php

/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 * headerTemplate() version for smarty templates
 *
 * Copyright (C) 2017 Brady Miller <brady.g.miller@gmail.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 */

use OpenEMR\Core\Header;

/**
 * Smarty {headerTemplate} function plugin.
 *
 * Type:     function<br />
 * Name:     headerTemplate<br />
 * Purpose:  headerTemplate in OpenEMR - Smarty templates<br />
 *
 * @param array $params
 * @param mixed $smarty
 */
function smarty_function_headerTemplate($params, &$smarty)
{
    $assets = [];
    if (!empty($params['assets'])) {
        $assets = explode('|', (string) $params['assets']);
    }

    // Pass $echoOutput = false so setupHeader() only returns the markup. As a Smarty
    // function plugin, the value returned here is printed at the {headerTemplate} tag.
    // With the default $echoOutput = true, setupHeader() would also echo the markup,
    // emitting the entire header (jQuery, Bootstrap, etc.) twice from a single tag --
    // double-binding Bootstrap's data-api handlers (e.g. dropdowns open then instantly
    // close). See OpenEMR\Core\Header::setupHeader().
    return Header::setupHeader($assets, false);
}
