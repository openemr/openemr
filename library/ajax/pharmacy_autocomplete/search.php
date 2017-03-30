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
 * Sherwin Gaddis <sherwingaddis@gmail.com>
 * @link    http://www.open-emr.org
 */
$fake_register_globals=false;
$sanitize_all_escapes=true;	
require_once('../../../interface/globals.php');


if (isset($_GET['term'])){
  $return_arr = array();
  
  $term    = filter_input(INPUT_GET, "term");
  $city    = filter_input(INPUT_GET, "city");
  $address = filter_input(INPUT_GET, "address");

	try {
		$sql = "SELECT id, Store_name, address_line_1, city, state FROM pharmacies_weno WHERE Store_name LIKE ? AND city LIKE ? ";
		$sql .= " AND address_line_1 LIKE ? ";
		$stm = array('%'.$term.'%','%'.$city.'%','%'.$address.'%');
		$res = sqlstatement($sql,$stm);
		while($row = sqlFetchArray($res)){
			$return_arr[] =  $row['id'] . " - ".$row['Store_name'] . " ".$row['address_line_1']." ". $row['city'] ." " . $row['state'];
		}

	} catch(PDOException $e) {
	    echo xlt('ERROR: ') . text($e->getMessage());
	}


    /* Toss back results as json encoded array. */
    echo json_encode($return_arr);
}

?>