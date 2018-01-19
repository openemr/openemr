<?php

/** Copyright (C) 2016 Sherwin Gaddis <sherwingaddis@gmail.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 * @link    http://www.open-emr.org
 */


require_once('../../../interface/globals.php');


if (!empty($_GET['term'])) {
    $term = $_GET['term'];
    $return_arr = array();

    $sql = "SELECT DISTINCT lot_number FROM immunizations WHERE lot_number LIKE ?";
    $res = sqlstatement($sql, array("%".$term."%"));
    while ($row = sqlFetchArray($res)) {
        $return_arr[] =  $row['lot_number'] ;
    }
    /* Toss back results as json encoded array. */
    echo json_encode($return_arr);
}
