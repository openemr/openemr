<?php

/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 * xla() version for smarty templates
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
 * Smarty {xla} function plugin
 *
 * Type:     function<br />
 * Name:     xla<br />
 * Purpose:  translate in OpenEMR and escape for html attr - Smarty templates<br />
 *
 * Examples:
 *
 * {xla t="some words"}
 *
 * @param array
 * @param Smarty
 */


function smarty_function_xla($params, &$smarty)
{
    if (empty($params['t'])) {
        trigger_error("xla: missing 't' parameter", E_USER_WARNING);
        return;
    } else {
        $translate = $params['t'];
    }

    echo xla($translate);
}
