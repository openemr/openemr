<?php

/**
 * weno rx mark tx.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2016-2017 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once($srcdir . "/patient.inc");

use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Rx\Weno\TransmitData;

if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

$date = date("Y-m-d");
$pid = $GLOBALS['pid'];
$uid = $_SESSION['authUserID'];

//Randomly generate number for each order unique ID
$i = rand() . rand() . rand();
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

    // Set up crypto object
    $cryptoGen = new CryptoGen();

    // Build the array
    $completeArray = array(
        array(
            "patient" => array(
                "lname"  => $pInfo['lname'],
                "fname"  => $pInfo['fname'],
                "street" => $pInfo['street'],
                "city"   => $pInfo['city'],
                "state"  => $pInfo['state'],
                "postal" => $pInfo['postal_code'],
                "DOB"    => $pInfo['DOB'],
                "Sex"    => $sex
            )
        ),
        array(
            "provider" => array(
                "provlname"       => $proData[0]['fname'],
                "provfname"       => $proData[0]['lname'],
                "provnpi"         => $proData[0]['npi'],
                "facilityfax"     => preg_replace("/[^0-9]/", "", $proData[0]['fax']),
                "facilityphone"   => preg_replace("/[^0-9]/", "", $proData[0]['phone']),
                "facilityname"    => $proData[0]['name'],
                "facilitystreet"  => $proData[0]['street'],
                "facilitycity"    => $proData[0]['city'],
                "facilitystate"   => $proData[0]['state'],
                "facilityzip"     => $proData[0]['postal_code'],
                "qualifier"       => $GLOBALS['weno_provider_id'] . ':' . $proData[0]['weno_prov_id'],
                "wenoAccountId"   => $GLOBALS['weno_account_id'],
                "wenoAccountPass" => (($cryptoGen->cryptCheckStandard($GLOBALS['weno_account_pass'])) ? $cryptoGen->decryptStandard($GLOBALS['weno_account_pass']) : $GLOBALS['weno_account_pass']),
                "wenoClinicId"    => $GLOBALS['weno_provider_id'] . ':' . $proData[0]['weno_prov_id']
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
                "take"         => $drugData['dosage'],
                "strength"     => $drugData['strength'],
                "route"        => $drugData['route'],
                "potency"      => $drugData['potency_unit_code'],
                "qualifier"    => $drugData['drug_db_code_qualifier'],
                "dea_sched"    => $drugData['dea_schedule']
            )
        )
    );

    // Convert the array to json
    $completeJson = json_encode($completeArray);

    // echo json
    echo $completeJson;
}
