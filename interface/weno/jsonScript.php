<?php
/**
 *
 * The purpose of this is to on the fly output the json code needed to transmit the prescriptions individually
 *
 *
 * Copyright (C) 2016 Sherwin Gaddis <sherwingaddis@gmail.com>
 * Copyright (C) 2017 Brady Miller <brady.g.miller@gmail.com>
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
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @link    http://www.open-emr.org
 */

$fake_register_globals=false;
$sanitize_all_escapes=true;

include_once("../globals.php");
include_once($srcdir."/patient.inc");
include_once("transmitDataClass.php"); 

$date = date("Y-m-d");
$pid = $GLOBALS['pid'];
$uid = $_SESSION['authUserID'];

$i = rand();
$fillData = filter_input(INPUT_GET, "getJson");


$fill = explode(",", $fillData);

$id = $fill[0]; //setting the pharmacy ID for later

array_shift($fill);  //removing the pharmacy from the array

//created a loop in case
foreach ($fill as $data) {

    // Collect patient data
    $pInfo = getPatientData($pid);
    if ($pInfo['sex'] == "Male") {
        $sex = "M";
    }
    if ($pInfo['sex'] == "Female") {
        $sex = "F";
    }

    // Collect provider data
    $prInfo = new transmitData();
    $proData = $prInfo->getProviderFacility($uid);

    // Collect pharmacy data
    $pharmData = $prInfo->findPharmacy($id);

    // Collect drug data
    $drugData = $prInfo->oneDrug($data);

    // Build the array
    $completeArray = array(
        array(
            "patient" => array(
                "lname"  => $pInfo['lname'],
                "fname"  => $pInfo['fname'],
                "street" => $pInfo['street'],
                "city"   => $pInfo['city'],
                "postal" => $pInfo['postal_code'],
                "DOB"    => $pInfo['DOB'],
                "Sex"    => $sex
            )
        ),
        array(
            "provider" => array(
                "provlname"       => $proData[1]['fname'],
                "provfname"       => $proData[1]['lname'],
                "provnpi"         => $proData[1]['npi'],
                "facilityfax"     => str_replace("-","",$proData[1]['fax']),
                "facilityphone"   => str_replace("-","",$proData[1]['phone']),
                "facilityname"    => $proData[1]['name'],
                "facilitystreet"  => $proData[1]['street'],
                "facilitycity"    => $proData[1]['city'],
                "facilitystate"   => $proData[1]['state'],
                "facilityzip"     => $proData[1]['postal_code'],
                "qualifier"       => $proData[1]['weno_prov_id'],
                "wenoAccountId"   => $proData[0][0]['gl_value'],
                "wenoAccountPass" => $proData[0][1]['gl_value'],
                "wenoClinicId"    => $proData[1]['weno_prov_id']
            )
        ),
        array(
            "pharmacy" => array(
                "storename"     => $pharmData[0]['name'],
                "storenpi"      => $pharmData[0]['npi'],
                "pharmacy"      => $pharmData[0]['ncpdp'],
                "pharmacyPhone" => $pharmData[1][0]['area_code'] . $pharmData[1][0]['prefix'] . $pharmData[1][0]['number'],
                "pharmacyFax"   => $pharmData[1][1]['area_code'] . $pharmData[1][1]['prefix'] . $pharmData[1][1]['number']
            )
        ),
        array(
            "script" => array(
                "drugName"     => trim($drugData['drug']),
                "drug_NDC"     => $drugData['drug_id'],
                "dateAdded"    => $drugData['date_Added'],
                "quantity"     => $drugData['quantity'],
                "refills"      => $drugData['refills'],
                "dateModified" => $drugData['date_Modified'],
                "note"         => $drugData['note'],
                "take"         => $drugData['dosage']
            )
        )
    );

    // Convert the array to json
    $completeJson = json_encode($completeArray);

    

    // echo json
    echo $completeJson;

}
