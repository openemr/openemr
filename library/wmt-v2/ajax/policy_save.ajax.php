<?php
include_once("../../../interface/globals.php");
include_once("$srcdir/patient.inc");
include_once("$srcdir/acl.inc");
include_once("$srcdir/options.inc.php");
include_once("$srcdir/wmt-v2/wmtpatient.class.php");

foreach ($_POST as $key => $val) {
    if ($val == "MM/DD/YYYY") {
        $_POST[$key] = "";
    }
}

if(isset($_POST['pid'])) $pid = $_POST['pid'];
$patient = wmtPatData::getPidPatient($pid);

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

$i3dob  = DateToYYYYMMDD(filter_input(INPUT_POST, "i3subscriber_DOB"));
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

if(!isset($GLOBALS['wmt::limit_case_ins'])) 
	$GLOBALS['wmt::limit_case_ins'] = '';
if($GLOBALS['wmt::limit_case_ins']) {
	$policies = wmtPatData::getPidPoliciesByDate($pid, $_POST['case_dt']);
} else {
	$policies = wmtPatData::getPidPolicies($pid);
}

$ret = array();
if(count($policies) < 1) {
	$policy = array('id' => 0, 'name' => 'No Policies On File', 'last' => '',
		'first' => '', 'middle' => '');
	$ret[0] = $policy;
} else {
	foreach ($policies as $policy) {
		$ret[] = $policy;
	}
}

echo json_encode($ret);
exit;
