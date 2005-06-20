<?
include_once("../../globals.php");
include_once("$srcdir/patient.inc");

foreach ($_POST as $key => $val) {
  if ($val == "MM/DD/YYYY") {
    $_POST[$key] = "";
  }
}

if ($_POST["sex"] == "Unselected") {
	$var_sex = "";
} else {
	$var_sex = $_POST["sex"];
}

if ($_POST{dob} != "") {
	$dob = $_POST["dob"];
} else {
	$dob = "";
}

$finrev = fixDate($_POST["financial_review"]);

newPatientData(
  $_POST["db_id"],
  $_POST["title"],
  $_POST["fname"],
  $_POST["lname"],
  $_POST["mname"],
  $var_sex,
  $dob,
  $_POST["street"],
  $_POST["postal_code"],
  $_POST["city"],
  $_POST["state"],
  $_POST["country_code"],
  $_POST["ss"],
  $_POST["occupation"],
  $_POST["phone_home"],
  $_POST["phone_biz"],
  $_POST["phone_contact"],
  $_POST["status"],
  $_POST["contact_relationship"],
  $_POST["referrer"],
  $_POST["referrerID"],
  $_POST["email"],
  strtolower($_POST["language"]),
  $_POST["ethnoracial"],
  $_POST["interpretter"],
  $_POST["migrantseasonal"],
  $_POST["family_size"],
  $_POST["monthly_income"],
  $_POST["homeless"],
  $finrev,
  $_POST["pubpid"],
  $pid,
  $_POST["providerID"],
  $_POST["genericname1"],
  $_POST["genericval1"],
  $_POST["genericname2"],
  $_POST["genericval2"],
  $_POST["phone_cell"],
  $_POST["hipaa_mail"],
  $_POST["hipaa_voice"],
  $_POST["squad"]
);

newEmployerData(
  $pid,
  $_POST["ename"],
  $_POST["estreet"],
  $_POST["epostal_code"],
  $_POST["ecity"],
  $_POST["estate"],
  $_POST["ecountry"]
);

$i1dob = fixDate($_POST["i1subscriber_DOB"]);

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
  $_POST["i1subscriber_relationship"],
  $_POST["i1subscriber_ss"],
  $i1dob,
  $_POST["i1subscriber_street"],
  $_POST["i1subscriber_postal_code"],
  $_POST["i1subscriber_city"],
  $_POST["i1subscriber_state"],
  $_POST["i1subscriber_country"],
  $_POST["i1subscriber_phone"],
  $_POST["i1subscriber_employer"],
  $_POST["i1subscriber_employer_street"],
  $_POST["i1subscriber_employer_city"],
  $_POST["i1subscriber_employer_postal_code"],
  $_POST["i1subscriber_employer_state"],
  $_POST["i1subscriber_employer_country"],
  $_POST['i1copay'],
  $_POST['i1subscriber_sex']
);

$i2dob = fixDate($_POST["i2subscriber_DOB"]);

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
  $_POST["i2subscriber_relationship"],
  $_POST["i2subscriber_ss"],
  $i2dob,
  $_POST["i2subscriber_street"],
  $_POST["i2subscriber_postal_code"],
  $_POST["i2subscriber_city"],
  $_POST["i2subscriber_state"],
  $_POST["i2subscriber_country"],
  $_POST["i2subscriber_phone"],
  $_POST["i2subscriber_employer"],
  $_POST["i2subscriber_employer_street"],
  $_POST["i2subscriber_employer_city"],
  $_POST["i2subscriber_employer_postal_code"],
  $_POST["i2subscriber_employer_state"],
  $_POST["i2subscriber_employer_country"],
  $_POST['i2copay'],
  $_POST['i2subscriber_sex']
);

$i3dob = fixDate($_POST["i3subscriber_DOB"]);

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
  $_POST["i3subscriber_relationship"],
  $_POST["i3subscriber_ss"],
  $i3dob,
  $_POST["i3subscriber_street"],
  $_POST["i3subscriber_postal_code"],
  $_POST["i3subscriber_city"],
  $_POST["i3subscriber_state"],
  $_POST["i3subscriber_country"],
  $_POST["i3subscriber_phone"],
  $_POST["i3subscriber_employer"],
  $_POST["i3subscriber_employer_street"],
  $_POST["i3subscriber_employer_city"],
  $_POST["i3subscriber_employer_postal_code"],
  $_POST["i3subscriber_employer_state"],
  $_POST["i3subscriber_employer_country"],
  $_POST['i3copay'],
  $_POST['i3subscriber_sex']
);

include_once("patient_summary.php");
?>
