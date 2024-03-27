<?php

/**
 * demographics_save.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2024 Care Management Solutions, Inc. <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("$srcdir/patient.inc.php");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Services\ContactService;
use OpenEMR\Events\Patient\PatientUpdatedEventAux;


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
    if (str_starts_with($field_id, 'em_')) {
        $colname = substr($field_id, 3);
        $table = 'employer_data';
    }

    // Get value only if field exist in $_POST (prevent deleting of field with disabled attribute)
    // *unless* the data_type is a checkbox ("21"), because if the checkbox is unchecked, then it will not
    // have a value set on the form, it will be empty.
    if ($data_type == 54) { // address list
        $addressFieldsToSave[$field_id] = get_layout_form_value($frow);
    } elseif (isset($_POST["form_$field_id"]) || $data_type == 21) {
        $newdata[$table][$colname] = get_layout_form_value($frow);
    }
}

// TODO: All of this should be bundled up inside a transaction...

updatePatientData($pid, $newdata['patient_data']);
if (!$GLOBALS['omit_employers']) {
    updateEmployerData($pid, $newdata['employer_data']);
}

if (!empty($addressFieldsToSave)) {
    // TODO: we would handle other types of address fields here,
    // for now we will just go through and populate the patient
    // address information
    // TODO: how are error messages supposed to display if the save fails?
    foreach ($addressFieldsToSave as $field => $addressFieldData) {
        // if we need to save other kinds of addresses we could do that here with our field column...
        $contactService = new ContactService();
        $contactService->saveContactsForPatient($pid, $addressFieldData);
    }
}

/**
 * trigger events to listeners who want data that is not directly available in
 * the patient_data table on update
 */
$GLOBALS["kernel"]->getEventDispatcher()->dispatch(new PatientUpdatedEventAux($pid, $_POST), PatientUpdatedEventAux::EVENT_HANDLE, 10);
// if refresh tab after saving then results in csrf error
include_once("demographics.php");
