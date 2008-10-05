<?php
include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");

if ($_POST["off_work_from"] == "0000-00-00" || $_POST["off_work_from"] == "") 
	{ $_POST["is_unable_to_work"] = "0"; $_POST["off_work_to"] = "";} 
	else {$_POST["is_unable_to_work"] = "1";}

if ($_POST["hospitalization_date_from"] == "0000-00-00" || $_POST["hospitalization_date_from"] == "") 
	{ $_POST["is_hospitalized"] = "0"; $_POST["hospitalization_date_to"] = "";} 
	else {$_POST["is_hospitalized"] = "1";}

foreach ($_POST as $k => $var) {
$_POST[$k] = mysql_escape_string($var);
echo "$var\n";
}
if ($encounter == "")
$encounter = date("Ymd");
if ($_GET["mode"] == "new"){
$newid = formSubmit("form_misc_billing_options", $_POST, $_GET["id"], $userauthorized);
addForm($encounter, "Misc Billing Options", $newid, "misc_billing_options", $pid, $userauthorized);
}elseif ($_GET["mode"] == "update") {
sqlInsert("update form_misc_billing_options set pid = {$_SESSION["pid"]},
	groupname='".$_SESSION["authProvider"]."',
	user='".$_SESSION["authUser"]."',
	authorized=$userauthorized,activity=1, date = NOW(),
	employment_related='".$_POST["employment_related"]."',
	auto_accident='".$_POST["auto_accident"]."',
	accident_state='".$_POST["accident_state"]."',
	other_accident='".$_POST["other_accident"]."',
	outside_lab='".$_POST["outside_lab"]."',
	lab_amount='".$_POST["lab_amount"]."',
	is_unable_to_work='".$_POST["is_unable_to_work"]."',
	off_work_from='".$_POST["off_work_from"]."',
	off_work_to='".$_POST["off_work_to"]."',
	is_hospitalized='".$_POST["is_hospitalized"]."',
	hospitalization_date_from='".$_POST["hospitalization_date_from"]."',
	hospitalization_date_to='".$_POST["hospitalization_date_to"]."',
	medicaid_resubmission_code='".$_POST["medicaid_resubmission_code"]."',
	medicaid_original_reference='".$_POST["medicaid_original_reference"]."',
	prior_auth_number='".$_POST["prior_auth_number"]."',
  replacement_claim='".$_POST["replacement_claim"]."',
	comments='".$_POST["comments"]."'
	where id=$id");
}
$_SESSION["encounter"] = $encounter;
formHeader("Redirecting....");
formJump();
formFooter();
?>
