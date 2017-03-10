<?php
/**
 * weno rx mark tx.
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2016-2017 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../globals.php");
require_once($srcdir."/patient.inc");
use OpenEMR\Rx\Weno\TransmitData;

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
    $prInfo = new TransmitData();
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
                "facilityfax"     => str_replace("-", "", $proData[1]['fax']),
                "facilityphone"   => str_replace("-", "", $proData[1]['phone']),
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
