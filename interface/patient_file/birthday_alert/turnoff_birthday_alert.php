<?php
/**
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Sharon Cohen<sharonco@matrix.co.il>
 * @link    http://www.open-emr.org
 */

include_once("../../globals.php");

if($_POST['turnOff'] == "true"){
    $date = date('Y-m-d',strtotime("now"));
}else{

    $date  = date('Y-m-d',strtotime("-1 year"));

}

$sql = "REPLACE INTO patient_birthday_alert VALUES(?,?)";
$res = sqlQuery($sql, array($_POST['pid'],$date));
