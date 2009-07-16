<?php
include_once("../../globals.php");
include_once("$srcdir/patient.inc");
include_once("$srcdir/acl.inc");
include_once("$srcdir/options.inc.php");

// Check authorization.
$thisauth = acl_check('patients', 'demo');
if ($pid) {
  if ($thisauth != 'write')
    die(xl('Updating demographics is not authorized.'));
  $tmp = getPatientData($pid, "squad");
  if ($tmp['squad'] && ! acl_check('squads', $tmp['squad']))
    die(xl('You are not authorized to access this squad.'));
} else {
  if ($thisauth != 'write' && $thisauth != 'addonly')
    die(xl('Adding demographics is not authorized.'));
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
  "ORDER BY group_name, seq");
while ($frow = sqlFetchArray($fres)) {
  $data_type = $frow['data_type'];
  $field_id  = $frow['field_id'];
  // $value  = '';
  $colname = $field_id;
  $table = 'patient_data';
  if (strpos($field_id, 'em_') === 0) {
    $colname = substr($field_id, 3);
    $table = 'employer_data';
  }

  // if (isset($_POST["form_$field_id"])) $value = $_POST["form_$field_id"];
  $value = get_layout_form_value($frow);

  $newdata[$table][$colname] = $value;
}
updatePatientData($pid, $newdata['patient_data']);
updateEmployerData($pid, $newdata['employer_data']);

$i1dob = fixDate($_POST["i1subscriber_DOB"]);
$i1date = fixDate($_POST["i1effective_date"], date('Y-m-d'));

newInsuranceData(
  $pid,
  "primary",
  $_POST["i1provider"],
  $_POST["i1policy_number"],
  $_POST["i1group_number"],
  $_POST["i1plan_name"],
  $_POST["i1subscriber_lname"],
  $_POST["i1subscriber_mname"],
  $_POST["i1subscriber_fname"],
  $_POST["form_i1subscriber_relationship"],
  $_POST["i1subscriber_ss"],
  $i1dob,
  $_POST["i1subscriber_street"],
  $_POST["i1subscriber_postal_code"],
  $_POST["i1subscriber_city"],
  $_POST["form_i1subscriber_state"],
  $_POST["form_i1subscriber_country"],
  $_POST["i1subscriber_phone"],
  $_POST["i1subscriber_employer"],
  $_POST["i1subscriber_employer_street"],
  $_POST["i1subscriber_employer_city"],
  $_POST["i1subscriber_employer_postal_code"],
  $_POST["form_i1subscriber_employer_state"],
  $_POST["form_i1subscriber_employer_country"],
  $_POST['i1copay'],
  $_POST['form_i1subscriber_sex'],
  $i1date,
  $_POST['i1accept_assignment']
);

$i2dob = fixDate($_POST["i2subscriber_DOB"]);
$i2date = fixDate($_POST["i2effective_date"], date('Y-m-d'));

newInsuranceData(
  $pid,
  "secondary",
  $_POST["i2provider"],
  $_POST["i2policy_number"],
  $_POST["i2group_number"],
  $_POST["i2plan_name"],
  $_POST["i2subscriber_lname"],
  $_POST["i2subscriber_mname"],
  $_POST["i2subscriber_fname"],
  $_POST["form_i2subscriber_relationship"],
  $_POST["i2subscriber_ss"],
  $i2dob,
  $_POST["i2subscriber_street"],
  $_POST["i2subscriber_postal_code"],
  $_POST["i2subscriber_city"],
  $_POST["form_i2subscriber_state"],
  $_POST["form_i2subscriber_country"],
  $_POST["i2subscriber_phone"],
  $_POST["i2subscriber_employer"],
  $_POST["i2subscriber_employer_street"],
  $_POST["i2subscriber_employer_city"],
  $_POST["i2subscriber_employer_postal_code"],
  $_POST["form_i2subscriber_employer_state"],
  $_POST["form_i2subscriber_employer_country"],
  $_POST['i2copay'],
  $_POST['form_i2subscriber_sex'],
  $i2date,
  $_POST['i2accept_assignment']
);

$i3dob  = fixDate($_POST["i3subscriber_DOB"]);
$i3date = fixDate($_POST["i3effective_date"], date('Y-m-d'));

newInsuranceData(
  $pid,
  "tertiary",
  $_POST["i3provider"],
  $_POST["i3policy_number"],
  $_POST["i3group_number"],
  $_POST["i3plan_name"],
  $_POST["i3subscriber_lname"],
  $_POST["i3subscriber_mname"],
  $_POST["i3subscriber_fname"],
  $_POST["form_i3subscriber_relationship"],
  $_POST["i3subscriber_ss"],
  $i3dob,
  $_POST["i3subscriber_street"],
  $_POST["i3subscriber_postal_code"],
  $_POST["i3subscriber_city"],
  $_POST["form_i3subscriber_state"],
  $_POST["form_i3subscriber_country"],
  $_POST["i3subscriber_phone"],
  $_POST["i3subscriber_employer"],
  $_POST["i3subscriber_employer_street"],
  $_POST["i3subscriber_employer_city"],
  $_POST["i3subscriber_employer_postal_code"],
  $_POST["form_i3subscriber_employer_state"],
  $_POST["form_i3subscriber_employer_country"],
  $_POST['i3copay'],
  $_POST['form_i3subscriber_sex'],
  $i3date,
  $_POST['i3accept_assignment']
);

if ($GLOBALS['concurrent_layout']) {
 include_once("demographics.php");
} else {
 include_once("patient_summary.php");
}
?>
