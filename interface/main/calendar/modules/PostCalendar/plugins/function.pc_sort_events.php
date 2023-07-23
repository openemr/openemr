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
function smarty_function_pc_sort_events($params, &$smarty)
{
    extract($params);

    if (empty($var)) {
        trigger_error("sort_array: missing 'var' parameter", E_USER_WARNING);
        return;
    }

    if (!in_array('value', array_keys($params))) {
        trigger_error("sort_array: missing 'value' parameter", E_USER_WARNING);
        return;
    }

    if (!in_array('sort', array_keys($params))) {
        trigger_error("sort_array: missing 'sort' parameter", E_USER_WARNING);
        return;
    }

    if (!in_array('order', array_keys($params))) {
        $order = 'asc';
    }

    switch ($sort) {
        case 'category':
            if (strtolower($order) == 'asc') {
                $function = 'sort_byCategoryA';
            }

            if (strtolower($order) == 'desc') {
                $function = 'sort_byCategoryD';
            }
            break;

        case 'title':
            if (strtolower($order) == 'asc') {
                $function = 'sort_byTitleA';
            }

            if (strtolower($order) == 'desc') {
                $function = 'sort_byTitleD';
            }
            break;

        case 'time':
            if (strtolower($order) == 'asc') {
                $function = 'sort_byTimeA';
            }

            if (strtolower($order) == 'desc') {
                $function = 'sort_byTimeD';
            }
            break;
    }

    $newArray = array();
    foreach ($value as $date => $events) {
        usort($events, $function);
        $newArray[$date] = array();
        $newArray[$date] = $events;
    }

    $smarty->assign_by_ref($var, $newArray);
}
