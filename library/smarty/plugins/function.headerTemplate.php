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
 * Type:     function<br>
 * Name:     headerTemplate<br>
 * Purpose:  headerTemplate in OpenEMR - Smarty templates<br>
 *
 * @param array
 * @param Smarty
 */
function smarty_function_headerTemplate($params, &$smarty)
{
    $assets = [];
    if (!empty($params['assets'])) {
        $assets = explode('|', $params['assets']);
    }

    return Header::setupHeader($assets);
}
