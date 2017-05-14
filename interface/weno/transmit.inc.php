<?php
/**
 *
 * Copyright (C) 2016 Sherwin Gaddis <sherwingaddis@gmail.com> Open Med Practice
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
 * @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 * @link    http://www.open-emr.org
 **/

class transmitData {
	
	public function __construct(){}
	
	public function getDrugList($pid, $date) { 

		$sql = "SELECT * FROM prescriptions WHERE patient_id = ? AND ntx = 1 AND txDate = ? ";
		$res = sqlStatement($sql, array($pid, $date));
		return $res;
	  }

	public function checkList($send){
		$list = sqlFetchArray($send);
		if(empty($list['id'])){
			return "No Rx Selected ";
		}
		
	}

	public function getProviderFacility($uid){
		
		$sql = "SELECT a.fname, a.lname, a.npi, a.weno_prov_id, b.name, b.phone, b.fax, b.street, b.city, b.state, 
				b.postal_code FROM `users` AS a, facility AS b WHERE a.id = ? AND 
				a.facility_id = b.id ";

				$pFinfo = sqlQuery($sql, array($uid));

		 return $pFinfo;
		 
	}

}

?>