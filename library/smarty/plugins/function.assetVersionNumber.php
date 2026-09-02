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
    // Fall back to 1 so a missing version still produces a usable URL.
    return attr_url(OEGlobalsBag::getInstance()->getString('v_js_includes', '1'));
}
