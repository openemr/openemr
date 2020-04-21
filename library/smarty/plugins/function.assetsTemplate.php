<?php

/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 * assetsTemplate() version for smarty templates
 *
 * Copyright (C) 2020 Brady Miller <brady.g.miller@gmail.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 */

use OpenEMR\Core\Header;

/**
 * Smarty {assetsTemplate} function plugin.
 *
 *   Basically is headerTemplate without any autoloaded assets
 *
 * Type:     function<br />
 * Name:     assetsTemplate<br />
 * Purpose:  assetsTemplate in OpenEMR - Smarty templates<br />
 *
 * @param array
 * @param Smarty
 */
function smarty_function_assetsTemplate($params, &$smarty)
{
    $assets = [];
    if (!empty($params['assets'])) {
        $assets = explode('|', $params['assets']);
    }

    return Header::setupAssets($assets);
}
