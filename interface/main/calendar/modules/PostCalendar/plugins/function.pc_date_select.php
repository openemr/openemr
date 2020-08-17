<?php

/**
 *  $Id$
 *
 *  PostCalendar::PostNuke Events Calendar Module
 *  Copyright (C) 2002  The PostCalendar Team
 *  http://postcalendar.tv
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 *  To read the license please read the docs/license.txt or visit
 *  http://www.gnu.org/copyleft/gpl.html
 *
 */
function smarty_function_pc_date_select($args)
{
    $print = pnVarCleanFromInput('print');
    $tplview = pnVarCleanFromInput('tplview');
    $viewtype = pnVarCleanFromInput('viewtype');
    if (!isset($viewtype)) {
        $viewtype = _SETTING_DEFAULT_VIEW;
    }

    $Date = postcalendar_getDate();

    if (!isset($y)) {
        $y = substr($Date, 0, 4);
    }

    if (!isset($m)) {
        $m = substr($Date, 4, 2);
    }

    if (!isset($d)) {
        $d = substr($Date, 6, 2);
    }

    if (!isset($args['day']) || strtolower($args['day']) == 'on') {
        $args['day'] = true;
        @define('_PC_FORM_DATE', true);
    } else {
        $args['day'] = false;
    }

    if (!isset($args['month']) || strtolower($args['month']) == 'on') {
        $args['month'] = true;
        @define('_PC_FORM_DATE', true);
    } else {
        $args['month'] = false;
    }

    if (!isset($args['year']) || strtolower($args['year']) == 'on') {
        $args['year'] = true;
        @define('_PC_FORM_DATE', true);
    } else {
        $args['year'] = false;
    }

    if (!isset($args['view']) || strtolower($args['view']) == 'on') {
        $args['view'] = true;
        @define('_PC_FORM_VIEW_TYPE', true);
    } else {
        $args['view'] = false;
    }

    $dayselect = $monthselect = $yearselect = $viewselect = '';
    $output = new pnHTML();
    $output->SetOutputMode(_PNH_RETURNOUTPUT);
    if ($args['day'] === true) {
        $sel_data = pnModAPIFunc(__POSTCALENDAR__, 'user', 'buildDaySelect', array('pc_day' => $d));
        $dayselect = $output->FormSelectMultiple('jumpday', $sel_data);
    }

    if ($args['month'] === true) {
        $sel_data = pnModAPIFunc(__POSTCALENDAR__, 'user', 'buildMonthSelect', array('pc_month' => $m));
        $monthselect = $output->FormSelectMultiple('jumpmonth', $sel_data);
    }

    if ($args['year'] === true) {
        $sel_data = pnModAPIFunc(__POSTCALENDAR__, 'user', 'buildYearSelect', array('pc_year' => $y));
        $yearselect = $output->FormSelectMultiple('jumpyear', $sel_data);
    }

    if ($args['view'] === true) {
        $sel_data = array();
        $sel_data[0]['id']         = 'day';
        $sel_data[0]['selected']   = $viewtype == 'day';
        $sel_data[0]['name']       = _CAL_DAYVIEW;
        $sel_data[1]['id']         = 'week';
        $sel_data[1]['selected']   = $viewtype == 'week';
        $sel_data[1]['name']       = _CAL_WEEKVIEW;
        $sel_data[2]['id']         = 'month';
        $sel_data[2]['selected']   = $viewtype == 'month';
        $sel_data[2]['name']       = _CAL_MONTHVIEW;
        $sel_data[3]['id']         = 'year';
        $sel_data[3]['selected']   = $viewtype == 'year';
        $sel_data[3]['name']       = _CAL_YEARVIEW;
        $viewselect = $output->FormSelectMultiple('viewtype', $sel_data);
    }

    if (!isset($args['label'])) {
        $args['label'] = _PC_JUMP_MENU_SUBMIT;
    }

        $jumpsubmit = '<input type="submit" class="btn btn-primary align-middle" name="submit" value="' . $args['label'] . '" />';
    $output->SetOutputMode(_PNH_KEEPOUTPUT);

    $orderArray = array('day' => $dayselect,
                        'month' => $monthselect,
                        'year' => $yearselect,
                        'view' => $viewselect,
                        'jump' => $jumpsubmit);

    if (isset($args['order'])) {
        $newOrder = array();
        $order = explode(',', $args['order']);
        foreach ($order as $tmp_order) {
            array_push($newOrder, $orderArray[$tmp_order]);
        }

        foreach ($orderArray as $key => $old_order) {
            if (!in_array($key, $newOrder)) {
                array_push($newOrder, $orderArray[$old_order]);
            }
        }

        $order = $newOrder;
    } else {
        $order = $orderArray;
    }

    foreach ($order as $element) {
        echo $element;
    }
}
