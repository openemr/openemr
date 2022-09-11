<?php

/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 * datetimepickerSupport() version for smarty templates
 *
 * Examples:
 *  {datetimepickerSupport}
 *  {datetimepickerSupport picker="time" seconds="show" input="format"}
 *
 * Copyright (C) 2017 Brady Miller <brady.g.miller@gmail.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 */

/**
 * Smarty {datetimepickerSupport} function plugin.
 *
 * Type:     function<br />
 * Name:     datetimepickerSupport<br />
 * Purpose:  datetimepickerSupport in OpenEMR - Smarty templates<br />
 *
 * @param array
 * @param Smarty
 */
function smarty_function_datetimepickerSupport($params, &$smarty)
{
    if (!empty($params['picker']) && $params['picker'] == 'time') {
        echo "$('.datetimepicker').datetimepicker({ ";
        $datetimepicker_timepicker = true;
    } else {
        echo "$('.datepicker').datetimepicker({";
        $datetimepicker_timepicker = false;
    }

    if (!empty($params['seconds']) && $params['seconds'] == 'show') {
        $datetimepicker_showseconds = true;
    } else {
        $datetimepicker_showseconds = false;
    }

    if (!empty($params['input']) && $params['input'] == 'format') {
        $datetimepicker_formatInput = true;
    } else {
        $datetimepicker_formatInput = false;
    }

    require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php');

    echo " });";

    return;
}
