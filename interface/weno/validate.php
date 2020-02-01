<?php
/**
 * weno rx validation.
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2016-2017 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../globals.php");
require_once("$srcdir/patient.inc");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Rx\Weno\ValidateRxData;

if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

$facility = getFacilities($first = '');
$pid = $GLOBALS['pid'];
$uid = $_SESSION['authUserID'];

$validation = new ValidateRxData();

?>
<html>
<head>
    <title><?php print xlt("Data Validation"); ?></title>
</head>
<body>
<h3><?php echo xlt("Data Validation"); ?></h3>

<?php
$patient = $validation->validatePatient($pid);
$pharmacy = $validation->patientPharmacyInfo($pid);
$diagnosis = $validation->medicalProblem();

$i = 0;
if (empty($facility[0]['name']) || $facility[0]['name'] == "Your clinic name here") {
    print xlt("Please fill out facility name properly")."<br>";
    ++$i;
}

if (empty($facility[0]['phone'])) {
    print xlt("Please fill out facility phone properly")."<br>";
    ++$i;
}

if (empty($facility[0]['fax'])) {
    print xlt("Please fill out facility fax properly"."<br>");
    ++$i;
}

if (empty($facility[0]['street'])) {
    print xlt("Please fill out facility street properly")."<br>";
    ++$i;
}

if (empty($facility[0]['city'])) {
    print xlt("Please fill out facility city properly")."<br>";
    ++$i;
}

if (empty($facility[0]['state'])) {
    print xlt("Please fill out facility state properly")."<br>";
    ++$i;
}

if (empty($facility[0]['postal_code'])) {
    print xlt("Please fill out facility postal code properly")."<br>";
    ++$i;
}

if (empty($GLOBALS['weno_account_id'])) {
    print xlt("Weno Account ID information missing")."<br>";
    ++$i;
}
if (empty($GLOBALS['weno_provider_id'])) {
    print xlt("Weno Account Clinic ID information missing")."<br>";
    ++$i;
}
if (empty($patient['DOB'])) {
    print xlt("Patient DOB missing"). "<br>";
    ++$i;
}
if (empty($patient['street'])) {
    print xlt("Patient street missing"). "<br>";
    ++$i;
}
if (empty($patient['postal_code'])) {
    print xlt("Patient Zip Code missing"). "<br>";
    ++$i;
}
if (empty($patient['city'])) {
    print xlt("Patient city missing"). "<br>";
    ++$i;
}
if (empty($patient['state'])) {
    print xlt("Patient state missing"). "<br>";
    ++$i;
}
if (empty($patient['sex'])) {
    print xlt("Patient sex missing"). "<br>";
    ++$i;
}
if (empty($diagnosis)) {
    print xlt("Please enter a Medical Problem for this patient"). "<br>";
}


$ncpdpLength = strlen($pharmacy['ncpdp']);
if (empty($pharmacy['ncpdp']) || $ncpdpLength < 7) {
    print xlt("Pharmacy missing NCPDP ID or less than 7 digits"). "<br>";
    ++$i;
}
$npiLength = strlen($pharmacy['npi']);
if (empty($pharmacy['npi'] || $npiLength < 10)) {
    print xlt("Pharmacy missing NPI  or less than 10 digits"). "<br>";
    ++$i;
}
//validate NPI exist
//Test if the NPI is a valid number on file
$seekvalidation = $validation->validateNPI($pharmacy['npi']);
if ($seekvalidation == 0) {
    print xlt("Please use valid NPI");
    ++$i;
}
if ($i < 1) {
header('Location: prescriptionOrder.php');
} else {
    die(xlt("Review the above"));
}

?>
</body>
</html>
