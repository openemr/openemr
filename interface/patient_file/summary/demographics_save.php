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

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

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
while ($frow = sqlFetchArray($fres)) {
    $data_type = $frow['data_type'];
    $field_id = $frow['field_id'];
    // $value  = '';
    $colname = $field_id;
    $table = 'patient_data';
    if (strpos($field_id, 'em_') === 0) {
        $colname = substr($field_id, 3);
        $table = 'employer_data';
    }

    // Get value only if field exist in $_POST (prevent deleting of field with disabled attribute)
    // *unless* the data_type is a checkbox ("21"), because if the checkbox is unchecked, then it will not
    // have a value set on the form, it will be empty.
    if (isset($_POST["form_$field_id"]) || $data_type == 21) {
        $newdata[$table][$colname] = get_layout_form_value($frow);
    }
}

updatePatientData($pid, $newdata['patient_data']);
updateEmployerData($pid, $newdata['employer_data']);

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
 include_once("demographics.php");
