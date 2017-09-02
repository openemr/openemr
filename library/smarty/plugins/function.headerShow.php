<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 * headerShow() version for smarty templates
 *
 * Copyright (C) 2017 Brady Miller <brady.g.miller@gmail.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 */

/**
 * Smarty {headerShow} function plugin.
 *
 * Type:     function<br>
 * Name:     headerShow<br>
 * Purpose:  headerShow in OpenEMR - Smarty templates<br>
 *
 * @param Smarty
 */
function smarty_function_headerShow($params, &$smarty)
{
    return html_header_show();
}
