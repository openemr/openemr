<?php

include_once("../../../interface/globals.php");
include_once("$srcdir/patient.inc");
include_once("$srcdir/options.inc.php");
include_once("$srcdir/wmt-v2/wmtpatient.class.php");

foreach ($_POST as $key => $val) {
    if ($val == "MM/DD/YYYY") {
        $_POST[$key] = "";
    }
}

if(isset($_POST['pid'])) $pid = $_POST['pid'];
// OEMR - Change
if(isset($_POST['updateallpayer'])) $updateallpayer = $_POST['updateallpayer'];

$patient = wmtPatData::getPidPatient($pid);

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
}

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
