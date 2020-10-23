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
use OpenEMR\Rx\Weno\TransmitData;
use OpenEMR\Services\FacilityService;

if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

$facilityService = new FacilityService();
$facility = $facilityService->getPrimaryBillingLocation();

$pid = $GLOBALS['pid'];
$uid = $_SESSION['authUserID'];

$validation = new TransmitData();

$patient = $validation->validatePatient($pid);
$pharmacy = $validation->patientPharmacyInfo($pid);

if (empty($facility['name']) || $facility['name'] == "Your clinic name here") {
    print xlt("Please fill out facility name properly");
    exit;
}

if (empty($facility['phone'])) {
    print xlt("Please fill out facility phone properly");
    exit;
}

if (empty($facility['fax'])) {
    print xlt("Please fill out facility fax properly");
    exit;
}

if (empty($facility['street'])) {
    print xlt("Please fill out facility street properly");
    exit;
}

if (empty($facility['city'])) {
    print xlt("Please fill out facility city properly");
    exit;
}

if (empty($facility['state'])) {
    print xlt("Please fill out facility state properly");
    exit;
}

if (empty($facility['postal_code'])) {
    print xlt("Please fill out facility postal code properly");
    exit;
}

if (empty($GLOBALS['weno_account_id'])) {
    print xlt("Weno Account ID information missing") . "<br />";
    exit;
}
if (empty($GLOBALS['weno_provider_id'])) {
    print xlt("Weno Account Clinic ID information missing") . "<br />";
    exit;
}
if (empty($patient['DOB'])) {
    print xlt("Patient DOB missing") . "<br />";
    exit;
}
if (empty($patient['street'])) {
    print xlt("Patient street missing") . "<br />";
    exit;
}
if (empty($patient['postal_code'])) {
    print xlt("Patient Zip Code missing") . "<br />";
    exit;
}
if (empty($patient['city'])) {
    print xlt("Patient city missing") . "<br />";
    exit;
}
if (empty($patient['state'])) {
    print xlt("Patient state missing") . "<br />";
    exit;
}
if (empty($patient['sex'])) {
    print xlt("Patient sex missing") . "<br />";
    exit;
}
if (empty($pharmacy['name'])) {
    print xlt("Pharmacy not assigned to the patient") . "<br />";
    exit;
}
$ncpdpLength = strlen($pharmacy['ncpdp']);
if (empty($pharmacy['ncpdp']) || $ncpdpLength < 7) {
    print xlt("Pharmacy missing NCPDP ID or less than 7 digits") . "<br />";
    exit;
}
$npiLength = strlen($pharmacy['npi']);
if (empty($pharmacy['npi'] || $npiLength < 10)) {
    print xlt("Pharmacy missing NPI  or less than 10 digits") . "<br />";
    exit;
}
//validate NPI exist
//Test if the NPI is a valid number on file
$seekvalidation = $validation->validateNPI($pharmacy['npi']);
if ($seekvalidation == 0) {
    print xlt("Please use valid NPI");
    exit;
}
header('Location: confirm.php');
