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

		$sql = "SELECT * FROM prescriptions WHERE patient_id = ? AND ntx = 1 AND txDate = ?";
		$res = sqlStatement($sql, array($pid, $date));
		return $res;
	  }

	public function checkList($send){
		$list = sqlFetchArray($send);
		if(empty($list['id'])){
			return xl("No Rx Selected ");
		}
		
	}

	public function getProviderFacility($uid){
		
		$sql = "SELECT a.fname, a.lname, a.npi, a.weno_prov_id, b.name, b.phone, b.fax, b.street, b.city, b.state, 
				b.postal_code FROM `users` AS a, facility AS b WHERE a.id = ? AND 
				a.facility_id = b.id ";

				$pFinfo = sqlQuery($sql, array($uid));
			$wenoInfo = array();
        			
        $wsql = "SELECT gl_value FROM `globals` WHERE `gl_name` LIKE '%weno%'";
		$wFinfo = sqlStatement($wsql);
		while($row = sqlFetchArray($wFinfo)){
			$wenoInfo[] = $row;
		}
		 return array($wenoInfo, $pFinfo);
		 
	}

	public function findPharmacy($id){
		
		//$sql = "SELECT store_name, NCPDP, NPI, Pharmacy_Phone, Pharmacy_Fax FROM erx_pharmacies WHERE id = ?";
		$sql = "SELECT name, ncpdp, npi FROM pharmacies WHERE id = ? ";
		$find = sqlQuery($sql, array($id));
		
		$nSql = "SELECT area_code, prefix, type, number FROM phone_numbers WHERE foreign_id = ?";
		$numbers = sqlStatement($nSql, array($id));

		$numberArray = array();
		while($row = sqlFetchArray($numbers)){
			$numberArray[] = $row;
		}
      
		return array($find, $numberArray);
	}
	
	public function oneDrug($id){
		$sql = "SELECT date_Added,date_Modified,drug,drug_id,dosage,refills,quantity,note FROM prescriptions WHERE id = ?";
		$res = sqlQuery($sql, array($id));
		return $res;		
	}

	public function active(){
		$sql = "SELECT gl_value FROM `globals` WHERE `gl_name` LIKE 'weno_rx_enable'";
		$res = sqlQuery($sql);
		return $res;
	}

	public function patientPharmacyInfo($pid){
		$sql = "SELECT a.pharmacy_id, b.name FROM patient_data AS a, pharmacies AS b WHERE a.pid = ? AND a.pharmacy_id = b.id";
        $res = sqlQuery($sql, $pid);
        return $res;
	}

	public function mailOrderPharmacy(){
		$sql = "SELECT id FROM pharmacies WHERE name LIKE ?";
		$res = sqlQuery($sql, 'CCS Medical');
		return $res;
	}

	public function validateWeno(){

         $wenoInfo = "SELECT gl_value FROM globals WHERE gl_name LIKE ? ";
         $val = array('%weno%');

         $wenores = sqlStatement($wenoInfo,$val);
         $wenoArray = array();
         while($row = sqlFetchArray($wenores)){
             $wenoArray[] = $row;
         }
         
         return $wenoArray;
	}

    public function validatePatient($pid){
         $patientInfo = "SELECT DOB, street, postal_code, city, state, sex FROM patient_data WHERE pid = ?";
         $val = array($pid);
         $patientRes = sqlQuery($patientInfo,$val);
         return $patientRes;    	
    }

}

?>