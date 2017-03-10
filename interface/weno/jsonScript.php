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
 
 /*
 * The purpose of this is to on the fly output the json code needed to transmit the prescriptions individually
 */

$fake_register_globals=false;

$sanitize_all_escapes=true;	

include_once("../globals.php");
include_once($srcdir."/patient.inc");
include_once("transmitDataClass.php"); //class include file, breaking away from procedure coding

$date = date("Y-m-d");
$pid = $GLOBALS['pid'];
$uid = $_SESSION['authUserID'];

$i = rand();
$fillData = filter_input(INPUT_GET, "getJson");


$fill = explode(",", $fillData);

$id = $fill[0]; //setting the pharmacy ID for later

array_shift($fill);  //removing the pharmacy from the array

//created a loop in case
foreach($fill as $data){
$pInfo = getPatientData($pid);


$pfname     = $pInfo['fname'];
$plname     = $pInfo['lname'];
$pstreet     = $pInfo['street'];
$ppostalCode = $pInfo['postal_code'];
$pcity       = $pInfo['city'];
$DOB        = $pInfo['DOB'];
$sex        = $pInfo['sex'];

function fillJsonPatient($pfname,$plname,$pstreet,$ppostalCode,$pcity,$DOB,$sex){

    if($sex == "Male"){ $sex = "M";}
	if($sex == "Female"){$sex = "F";} //has to be a single letter
	
$patient ='[{"patient": {"lname" : "'.$plname.'","fname" : "'.$pfname.'","street" : "'.$pstreet.'","city" : "'.$pcity.'","postal" : "'.$ppostalCode.'","DOB" : "'.$DOB.'","Sex" : "'.$sex.'"}},';
	return $patient;
}

$prInfo = new transmitData();
$proData = $prInfo->getProviderFacility($uid);

    $fname      = $proData[1]['fname'];
    $lname      = $proData[1]['lname'];
    $npi        = $proData[1]['npi'];
    $wenoProvId = $proData[1]['weno_prov_id'];
    $name       = $proData[1]['name'];
    $phone      = str_replace("-","",$proData[1]['phone']);
    $fax        = str_replace("-","",$proData[1]['fax']);
    $street     = $proData[1]['street'];
    $city       = $proData[1]['city'];
    $state      = $proData[1]['state'];
    $postalCode = $proData[1]['postal_code'];
    $wenoAccId  = $proData[0][0]['gl_value'];
    $wenoPass   = $proData[0][1]['gl_value'];

function fillJsonProvider($fname,$lname,$npi,$wenoProvId,$name,$phone,$fax,$street,$city,$state,$postalCode,$wenoAccId,$wenoPass){
	
 $provider ='{"provider": {"provlname" : "'.$fname.'","provfname" : "'.$lname.'","provnpi" : '.$npi.',"facilityfax" : '.$fax.',"facilityphone" : '.$phone.',"facilityname" : "'.$name.'","facilitystreet" : "'.$street.'","facilitycity" : "'.$city.'","facilitystate" : "'.$state.'","facilityzip" : '.$postalCode.',"qualifier" : "'.$wenoProvId.'","wenoAccountId" : "'.$wenoAccId.'","wenoAccountPass" : "'.$wenoPass.'","wenoClinicId" : "'.$wenoProvId.'"}},';
	return $provider; 
}

$pharmData = $prInfo->findPharmacy($id);

$storename     = $pharmData[0]['name'];
$storenpi      = $pharmData[0]['npi'];
$pharmacy      = $pharmData[0]['ncpdp'];
$pharmacyPhone = $pharmData[1][0]['area_code'].$pharmData[1][0]['prefix'].$pharmData[1][0]['number'];
$pharmacyFax   = $pharmData[1][1]['area_code'].$pharmData[1][1]['prefix'].$pharmData[1][1]['number'];

function fillJsonPharmacy($storename,$storenpi,$pharmacy,$pharmacyPhone,$pharmacyFax){
	
$pharmacy ='{"pharmacy": {"storename" : "'.$storename.'","storenpi" : '.$storenpi.',"pharmacy" : '.$pharmacy.',"pharmacyPhone" : '.$pharmacyPhone.',"pharmacyFax" : '.$pharmacyFax.'}},';
	return $pharmacy;
}	

$drugData = $prInfo->oneDrug($data);

    $drugName     = trim($drugData['drug']);
    $drugNDC      = $drugData['drug_id'];
    $dateAdded    = $drugData['date_Added'];
    $quantity     = $drugData['quantity'];
    $refills      = $drugData['refills'];
    $dateModified = $drugData['date_Modified'];
    $note         = $drugData['note'];
    $take         = $drugData['dosage'];

function fillJsonScript($drugName,$drugNDC,$dateAdded,$quantity,$refills,$dateModified,$note,$take){
$script ='{"script": {"drugName" : "'.$drugName.'","drug_NDC" : "'.$drugNDC.'","dateAdded" : "'.$dateAdded.'","quantity" : "'.$quantity.'","refills" : "'.$refills.'","dateModified" : "'.$dateModified.'","note" : "'.$note.'","take" : "'.$take.'"}}]';
		
		return $script;
}

$completeJson =  fillJsonPatient($pfname,$plname,$pstreet,$ppostalCode,$pcity,$DOB,$sex) . 
                 fillJsonProvider($fname,$lname,$npi,$wenoProvId,$name,$phone,$fax,$street,$city,$state,$postalCode,$wenoAccId,$wenoPass) . 
				 fillJsonPharmacy($storename,$storenpi,$pharmacy,$pharmacyPhone,$pharmacyFax) . 
				 fillJsonScript($drugName,$drugNDC,$dateAdded,$quantity,$refills,$dateModified,$note,$take);

$log = $i."jsonScript.txt";
file_put_contents($log, $completeJson); //leave this for troubleshooting
echo $completeJson;
copy ($log, "log/".$log);
unlink($log);
} //end of foreach loop!!!