<?php
/**
 * weno rx validation.
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2016-2017 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


include_once('../globals.php');
use OpenEMR\Rx\Weno\TransmitData;

$pid = $GLOBALS['pid'];
$uid = $_SESSION['authUserID'];

$validation = new TransmitData();

$patient = $validation->validatePatient($pid);
$pharmacy = $validation->patientPharmacyInfo($pid);

if (empty($GLOBALS['weno_account_id'])) {
    print xlt("Weno Account ID information missing")."<br>";
    exit;
}
if (empty($GLOBALS['weno_provider_id'])) {
    print xlt("Weno Account Clinic ID information missing")."<br>";
    exit;
}
if (empty($patient['DOB'])) {
    print xlt("Patient DOB missing"). "<br>";
    exit;
}
if (empty($patient['street'])) {
    print xlt("Patient street missing"). "<br>";
    exit;
}
if (empty($patient['postal_code'])) {
    print xlt("Patient Zip Code missing"). "<br>";
    exit;
}
if (empty($patient['city'])) {
    print xlt("Patient city missing"). "<br>";
    exit;
}
if (empty($patient['state'])) {
    print xlt("Patient state missing"). "<br>";
    exit;
}
if (empty($patient['sex'])) {
    print xlt("Patient sex missing"). "<br>";
    exit;
}
if (empty($pharmacy['name'])) {
    print xlt("Pharmacy not assigned to the patient"). "<br>";
    exit;
}
header('Location: confirm.php');
