<?php

/**
 * demographics_save.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/wmt-v2/wmtstandard.inc");
require_once("$srcdir/OemrAD/oemrad.globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Services\ContactService;
use OpenEMR\OemrAd\Demographicslib;
use OpenEMR\OemrAd\EmailVerificationLib;

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}
global $pid;
// Check authorization.
if ($pid) {
    if (!AclMain::aclCheckCore('patients', 'demo', '', 'write')) {
        die(xlt('Updating demographics is not authorized.'));
    }

    $tmp = getPatientData($pid, "squad");
    if ($tmp['squad'] && ! AclMain::aclCheckCore('squads', $tmp['squad'])) {
        die(xlt('You are not authorized to access this squad.'));
    }
} else {
    if (!AclMain::aclCheckCore('patients', 'demo', '', array('write','addonly'))) {
        die(xlt('Adding demographics is not authorized.'));
    }
}

foreach ($_POST as $key => $val) {
    if ($val == "MM/DD/YYYY") {
        $_POST[$key] = "";
    }
}

// Update patient_data and employer_data:
//
$newdata = array();
$newdata['patient_data']['id'] = $_POST['db_id'];
$fres = sqlStatement("SELECT * FROM layout_options " .
  "WHERE form_id = 'DEM' AND uor > 0 AND field_id != '' " .
  "ORDER BY group_id, seq");

$addressFieldsToSave = array();
while ($frow = sqlFetchArray($fres)) {
    $data_type = $frow['data_type'];
    if ((int)$data_type === 52) {
        // patient name history is saved in add.
        continue;
    }
    $field_id = $frow['field_id'];
    $colname = $field_id;
    $table = 'patient_data';
    //ensure compliant wth php 7.4 (no str_starts_with() function in 7.4)
    if ((!function_exists('str_starts_with') && strpos($field_id, 'em_') === 0) || (function_exists('str_starts_with') && str_starts_with($field_id, 'em_'))) {
        $colname = substr($field_id, 3);
        $table = 'employer_data';
    }

    // Get value only if field exist in $_POST (prevent deleting of field with disabled attribute)
    // *unless* the data_type is a checkbox ("21"), because if the checkbox is unchecked, then it will not
    // have a value set on the form, it will be empty.
    if ($data_type == 54) { // address list
        $addressFieldsToSave[$field_id] = get_layout_form_value($frow);
    } else if (isset($_POST["form_$field_id"]) || $data_type == 21) {
        $newdata[$table][$colname] = get_layout_form_value($frow);
    }
}

// TODO: All of this should be bundled up inside a transaction...

updatePatientData($pid, $newdata['patient_data']);
if (!$GLOBALS['omit_employers']) {
    updateEmployerData($pid, $newdata['employer_data']);
}

/* OEMR - Update email verification data and alert log data. */
EmailVerificationLib::updateEmailVerification($pid, $_POST);
Demographicslib::dem_after_save();
/* End */

if (!empty($addressFieldsToSave)) {
    // TODO: we would handle other types of address fields here, for now we will just go through and populate the patient
    // address information
    // TODO: how are error messages supposed to display if the save fails?
    foreach ($addressFieldsToSave as $field => $addressFieldData) {
        // if we need to save other kinds of addresses we could do that here with our field column...
        $contactService = new ContactService();
        $contactService->saveContactsForPatient($pid, $addressFieldData);
    }
}

// OEMR - Change
if(isset($_POST['updateallpayer'])) $updateallpayer = $_POST['updateallpayer'];

if(isset($updateallpayer) && $updateallpayer == true) {
    $payercount = isset($_POST['ipayercount']) ? $_POST['ipayercount'] : 1;
    for ($i=1; $i <= $payercount ; $i++) {
        $type = "primary";
        $provider = filter_input(INPUT_POST, "i".$i."provider");
        $policy_number = filter_input(INPUT_POST, "i".$i."policy_number");
        $group_number = filter_input(INPUT_POST, "i".$i."group_number");
        $claim_number = filter_input(INPUT_POST, "i".$i."claim_number");
        $plan_name = filter_input(INPUT_POST, "i".$i."plan_name");
        $subscriber_lname = filter_input(INPUT_POST, "i".$i."subscriber_lname");
        $subscriber_mname = filter_input(INPUT_POST, "i".$i."subscriber_mname");
        $subscriber_fname = filter_input(INPUT_POST, "i".$i."subscriber_fname");
        $subscriber_relationship = filter_input(INPUT_POST, "form_i".$i."subscriber_relationship");
        $subscriber_ss = filter_input(INPUT_POST, "i".$i."subscriber_ss");
        $subscriber_DOB = DateToYYYYMMDD(filter_input(INPUT_POST, "i".$i."subscriber_DOB"));
        $subscriber_street = filter_input(INPUT_POST, "i".$i."subscriber_street");
        $subscriber_postal_code = filter_input(INPUT_POST, "i".$i."subscriber_postal_code");
        $subscriber_city = filter_input(INPUT_POST, "i".$i."subscriber_city");
        $subscriber_state = filter_input(INPUT_POST, "form_i".$i."subscriber_state");
        $subscriber_country = filter_input(INPUT_POST, "form_i".$i."subscriber_country");
        $subscriber_phone = filter_input(INPUT_POST, "i".$i."subscriber_phone");
        $subscriber_employer = filter_input(INPUT_POST, "i".$i."subscriber_employer");
        $subscriber_employer_street = filter_input(INPUT_POST, "i".$i."subscriber_employer_street");
        $subscriber_employer_city = filter_input(INPUT_POST, "i".$i."subscriber_employer_city");
        $subscriber_employer_postal_code = filter_input(INPUT_POST, "i".$i."subscriber_employer_postal_code");
        $subscriber_employer_state = filter_input(INPUT_POST, "form_i".$i."subscriber_employer_state");
        $subscriber_employer_country = filter_input(INPUT_POST, "form_i".$i."subscriber_employer_country");
        $copay = filter_input(INPUT_POST, "i".$i."copay");
        $subscriber_sex = filter_input(INPUT_POST, "form_i".$i."subscriber_sex");
        $effective_date = DateToYYYYMMDD(filter_input(INPUT_POST, "i".$i."effective_date"));
        $accept_assignment = filter_input(INPUT_POST, "i".$i."accept_assignment");
        $policy_type = filter_input(INPUT_POST, "i".$i."policy_type");
        $payer_inactive = filter_input(INPUT_POST, "i".$i."payer_inactive");
        $payerid = filter_input(INPUT_POST, "i".$i."payerid");

        if($provider == "" && $payerid == "") continue;
        
        saveInsuranceData(
            array(
                "payerid" => $payerid,
                "pid" => $pid,
                "type" => $type,
                "provider" => $provider,
                "policy_number" => $policy_number,
                "group_number" => $group_number,
                "claim_number" => $claim_number,
                "plan_name" => $plan_name,
                "subscriber_lname" => $subscriber_lname,
                "subscriber_mname" => $subscriber_mname,
                "subscriber_fname" => $subscriber_fname,
                "subscriber_relationship" => $subscriber_relationship,
                "subscriber_ss" => $subscriber_ss,
                "subscriber_DOB" => $subscriber_DOB,
                "subscriber_street" => $subscriber_street,
                "subscriber_postal_code" => $subscriber_postal_code,
                "subscriber_city" => $subscriber_city,
                "subscriber_state" => $subscriber_state,
                "subscriber_country" => $subscriber_country,
                "subscriber_phone" => $subscriber_phone,
                "subscriber_employer" => $subscriber_employer,
                "subscriber_employer_street" => $subscriber_employer_street,
                "subscriber_employer_city" => $subscriber_employer_city,
                "subscriber_employer_postal_code" => $subscriber_employer_postal_code,
                "subscriber_employer_state" => $subscriber_employer_state,
                "subscriber_employer_country" => $subscriber_employer_country,
                "copay" => $copay,
                "subscriber_sex" => $subscriber_sex,
                "effective_date" => $effective_date,
                "accept_assignment" => $accept_assignment,
                "policy_type" => $policy_type,
                "payer_inactive" => $payer_inactive === "1" ? 1 : 0
            )
        );
    }
} else {

$i1dob = DateToYYYYMMDD(filter_input(INPUT_POST, "i1subscriber_DOB"));
$i1date = DateToYYYYMMDD(filter_input(INPUT_POST, "i1effective_date"));

newInsuranceData(
    $pid,
    "primary",
    filter_input(INPUT_POST, "i1provider"),
    filter_input(INPUT_POST, "i1policy_number"),
    filter_input(INPUT_POST, "i1group_number"),
    filter_input(INPUT_POST, "i1plan_name"),
    filter_input(INPUT_POST, "i1subscriber_lname"),
    filter_input(INPUT_POST, "i1subscriber_mname"),
    filter_input(INPUT_POST, "i1subscriber_fname"),
    filter_input(INPUT_POST, "form_i1subscriber_relationship"),
    filter_input(INPUT_POST, "i1subscriber_ss"),
    $i1dob,
    filter_input(INPUT_POST, "i1subscriber_street"),
    filter_input(INPUT_POST, "i1subscriber_postal_code"),
    filter_input(INPUT_POST, "i1subscriber_city"),
    filter_input(INPUT_POST, "form_i1subscriber_state"),
    filter_input(INPUT_POST, "form_i1subscriber_country"),
    filter_input(INPUT_POST, "i1subscriber_phone"),
    filter_input(INPUT_POST, "i1subscriber_employer"),
    filter_input(INPUT_POST, "i1subscriber_employer_street"),
    filter_input(INPUT_POST, "i1subscriber_employer_city"),
    filter_input(INPUT_POST, "i1subscriber_employer_postal_code"),
    filter_input(INPUT_POST, "form_i1subscriber_employer_state"),
    filter_input(INPUT_POST, "form_i1subscriber_employer_country"),
    filter_input(INPUT_POST, 'i1copay'),
    filter_input(INPUT_POST, 'form_i1subscriber_sex'),
    $i1date,
    filter_input(INPUT_POST, 'i1accept_assignment'),
    filter_input(INPUT_POST, 'i1policy_type')
);

//Dont save more than one insurance since only one is allowed / save space in DB
if (!$GLOBALS['insurance_only_one']) {
    $i2dob = DateToYYYYMMDD(filter_input(INPUT_POST, "i2subscriber_DOB"));
    $i2date = DateToYYYYMMDD(filter_input(INPUT_POST, "i2effective_date"));


    newInsuranceData(
        $pid,
        "secondary",
        filter_input(INPUT_POST, "i2provider"),
        filter_input(INPUT_POST, "i2policy_number"),
        filter_input(INPUT_POST, "i2group_number"),
        filter_input(INPUT_POST, "i2plan_name"),
        filter_input(INPUT_POST, "i2subscriber_lname"),
        filter_input(INPUT_POST, "i2subscriber_mname"),
        filter_input(INPUT_POST, "i2subscriber_fname"),
        filter_input(INPUT_POST, "form_i2subscriber_relationship"),
        filter_input(INPUT_POST, "i2subscriber_ss"),
        $i2dob,
        filter_input(INPUT_POST, "i2subscriber_street"),
        filter_input(INPUT_POST, "i2subscriber_postal_code"),
        filter_input(INPUT_POST, "i2subscriber_city"),
        filter_input(INPUT_POST, "form_i2subscriber_state"),
        filter_input(INPUT_POST, "form_i2subscriber_country"),
        filter_input(INPUT_POST, "i2subscriber_phone"),
        filter_input(INPUT_POST, "i2subscriber_employer"),
        filter_input(INPUT_POST, "i2subscriber_employer_street"),
        filter_input(INPUT_POST, "i2subscriber_employer_city"),
        filter_input(INPUT_POST, "i2subscriber_employer_postal_code"),
        filter_input(INPUT_POST, "form_i2subscriber_employer_state"),
        filter_input(INPUT_POST, "form_i2subscriber_employer_country"),
        filter_input(INPUT_POST, 'i2copay'),
        filter_input(INPUT_POST, 'form_i2subscriber_sex'),
        $i2date,
        filter_input(INPUT_POST, 'i2accept_assignment'),
        filter_input(INPUT_POST, 'i2policy_type')
    );

    $i3dob = DateToYYYYMMDD(filter_input(INPUT_POST, "i3subscriber_DOB"));
    $i3date = DateToYYYYMMDD(filter_input(INPUT_POST, "i3effective_date"));

    newInsuranceData(
        $pid,
        "tertiary",
        filter_input(INPUT_POST, "i3provider"),
        filter_input(INPUT_POST, "i3policy_number"),
        filter_input(INPUT_POST, "i3group_number"),
        filter_input(INPUT_POST, "i3plan_name"),
        filter_input(INPUT_POST, "i3subscriber_lname"),
        filter_input(INPUT_POST, "i3subscriber_mname"),
        filter_input(INPUT_POST, "i3subscriber_fname"),
        filter_input(INPUT_POST, "form_i3subscriber_relationship"),
        filter_input(INPUT_POST, "i3subscriber_ss"),
        $i3dob,
        filter_input(INPUT_POST, "i3subscriber_street"),
        filter_input(INPUT_POST, "i3subscriber_postal_code"),
        filter_input(INPUT_POST, "i3subscriber_city"),
        filter_input(INPUT_POST, "form_i3subscriber_state"),
        filter_input(INPUT_POST, "form_i3subscriber_country"),
        filter_input(INPUT_POST, "i3subscriber_phone"),
        filter_input(INPUT_POST, "i3subscriber_employer"),
        filter_input(INPUT_POST, "i3subscriber_employer_street"),
        filter_input(INPUT_POST, "i3subscriber_employer_city"),
        filter_input(INPUT_POST, "i3subscriber_employer_postal_code"),
        filter_input(INPUT_POST, "form_i3subscriber_employer_state"),
        filter_input(INPUT_POST, "form_i3subscriber_employer_country"),
        filter_input(INPUT_POST, 'i3copay'),
        filter_input(INPUT_POST, 'form_i3subscriber_sex'),
        $i3date,
        filter_input(INPUT_POST, 'i3accept_assignment'),
        filter_input(INPUT_POST, 'i3policy_type')
    );
}

}

// if refresh tab after saving then results in csrf error
include_once("demographics.php");
