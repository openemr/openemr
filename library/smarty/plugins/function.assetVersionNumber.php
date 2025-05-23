<?php

/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 * xl() version for smarty templates
 *
 * Copyright (C) 2007 Christian Navalici
 * Copyright (C) 2019 Brady Miller <brady.g.miller@gmail.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 */


/**
 * Smarty {xl} function plugin
 *
 * Type:     function<br />
 * Name:     assetVersionNumber<br />
 * Purpose:  Return the version number to be used in a script or style asset include ie script?v={jsVersionNumber}<br />
 *
 * Examples:
 *
 * {jsVersionNumber}
 *
 * @param array
 * @param Smarty
 */


function smarty_function_assetVersionNumber($params, &$smarty)
{
    echo $GLOBALS['v_js_includes'] ?? 1; // if for some reason we don't have a version we just return one
}
