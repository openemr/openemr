<?php

/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 * xlj() version for smarty templates
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
 * Smarty {xlj} function plugin
 *
 * Type:     function<br />
 * Name:     xlj<br />
 * Purpose:  translate in OpenEMR and escape for js - Smarty templates<br />
 *
 * Examples:
 *
 * {xlj t="some words"}
 *
 * @param array
 * @param Smarty
 */


function smarty_function_xlj($params, &$smarty)
{
    if (empty($params['t'])) {
        $smarty->trigger_error("xk: missing 't' parameter");
        return;
    } else {
        $translate = $params['t'];
    }

    echo xlj($translate);
}
