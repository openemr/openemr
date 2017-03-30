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
$fake_register_globals=false;

$sanitize_all_escapes=true;	

require_once('../../../interface/globals.php');


if (isset($_GET['term'])){
	
	$return_arr = array();
    $term    = filter_input(INPUT_GET, "term");
    
	try {
		$sql = "SELECT drug_label_name, price_per_unit FROM erx_drug_paid WHERE drug_label_name LIKE ? ";
		$val = array('%'.$term.'%');
		$res = sqlstatement($sql, $val);
		while($row = sqlFetchArray($res)){
			$return_arr[] =  $row['drug_label_name'] . " - ". $row['price_per_unit'];
		}

	
	} catch(PDOException $e) {
	    echo 'ERROR: ' . text($e->getMessage());
	}


    /* Toss back results as json encoded array. */
    echo json_encode($return_arr);
}


?>