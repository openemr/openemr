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
 require_once("../globals.php");


$state = filter_input(INPUT_POST, state);
echo $state;

switch($state){
	case (preg_match("/^[A-Ka-k]/i", $state) ? true : false) :
	       echo "State is between A - K";
	       $file = "./pharmacyList1.csv";
	       break;

	case (preg_match("/^[L-Nl-n]/i", $state) ? true : false) :
	       echo "State is between L - N";
	       $file = "./pharmacyList2.csv";
	       break;

	case (preg_match("/^[O-Zo-z]/i", $state) ? true : false) :
	       echo "State is between O - Z";
	       $file = "./pharmacyList3.csv";
	       break;

	   defalut:
	       echo "Something went wrong!";      
} 

 $lines = count(file($file));
 echo $lines. "<br>";
 
 $i = 0;

while($i <= $lines){

 $fileName = new SplFileObject($file);
 $line = $fileName->seek($i);
 $lineToInsert = $fileName->current();
 $lineParts = explode(",", $lineToInsert);	
 $i++;
 
if($state !== $lineParts[7]){ continue; }
	
sqlStatement("INSERT INTO `pharmacies_weno` (`id`, `Last_Updated`, `Store_Name`, `NCPDP`, `Active`, `NPI`, `Pharmacy_Phone`, `Pharmacy_Fax`, `Address_Line_1`, `Address_Line_2`, `City`, `State`, `ZipCode`, `Retail`, `Specialty`, `Long_Term_Care`, `Mail_Order`, `Mail_State_Codes`, `Mail_Address_Line_1`, `Mail_Address_Line_2`, `Mail_City`, `Mail_State`, `Mail_Zip_Code`, `Mail_Phone`, `Mail_Fax`, `EPCS_Permitted`, `Accept_NewRx`, `Accept_RefillResponse`, `Accept_RxChangeResponse`, `Accept_Verify`, `Accept_CancelRx`, `Accept_RxHistoryRequest`, `Accept_RxHistoryResponse`, `Accept_Census`, `Accept_Resupply`) VALUES (NULL, NULL, ?, ?, NULL, ?, ?, ?, ?,  NULL, ?, ?, ?, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL); ", array($lineParts[3],$lineParts[1],$lineParts[2],$lineParts[10],$lineParts[11],$lineParts[4],$lineParts[9],$lineParts[7],$lineParts[8]));

	
}
//insert test pharmacy
sqlStatement("INSERT INTO `pharmacies_weno` (`id`, `Last_Updated`, `Store_Name`, `NCPDP`, `Active`, `NPI`, `Pharmacy_Phone`, `Pharmacy_Fax`, `Address_Line_1`, `Address_Line_2`, `City`, `State`, `ZipCode`, `Retail`, `Specialty`, `Long_Term_Care`, `Mail_Order`, `Mail_State_Codes`, `Mail_Address_Line_1`, `Mail_Address_Line_2`, `Mail_City`, `Mail_State`, `Mail_Zip_Code`, `Mail_Phone`, `Mail_Fax`, `EPCS_Permitted`, `Accept_NewRx`, `Accept_RefillResponse`, `Accept_RxChangeResponse`, `Accept_Verify`, `Accept_CancelRx`, `Accept_RxHistoryRequest`, `Accept_RxHistoryResponse`, `Accept_Census`, `Accept_Resupply`) VALUES (NULL, NULL, ?, ?, NULL, ?, ?, ?, ?,  NULL, ?, ?, ?, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL); ", array('Test Direct Pharmacy', 1234567, '321', '2109128143', '5128525926', '8127 Mesa Drive, B206-210', 'Austin', 'TX', 78759));

flush();
header('Location: ' . $_SERVER['HTTP_REFERER']);


 

