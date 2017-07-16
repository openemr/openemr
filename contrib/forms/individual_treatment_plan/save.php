<?php

include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");
foreach ($_POST as $k => $var) {
    $_POST[$k] = add_escape_custom($var);
    echo "$var\n";
}

if ($encounter == "") {
    $encounter = date("Ymd");
}

if ($_GET["mode"] == "new") {
    $newid = formSubmit("form_individual_treatment_plan", $_POST, $_GET["id"], $userauthorized);

    addForm($encounter, "Individual Treatment Plan", $newid, "individual_treatment_plan", $pid, $userauthorized);
} elseif ($_GET["mode"] == "update") {
    sqlInsert("update form_individual_treatment_plan set pid = {$_SESSION["pid"]},groupname='".$_SESSION["authProvider"]."',user='".$_SESSION["authUser"]."',authorized=$userauthorized,activity=1, date = NOW(), 
date_of_referal ='".$_POST["date_of_referal"]."',
dcn ='".$_POST["dcn"]."',
icd9 ='".$_POST["icd9"]."',
prognosis ='".$_POST["prognosis"]."',
diagnosis_description ='".$_POST["diagnosis_description"]."',
presenting_problem ='".$_POST["presenting_problem"]."',
frequency ='".$_POST["frequency"]."',
duration ='".$_POST["duration"]."',
scope ='".$_POST["scope"]."',
short_term_goals_1 ='".$_POST["short_term_goals_1"]."',
time_frame_1 ='".$_POST["time_frame_1"]."',
short_term_goals_2 ='".$_POST["short_term_goals_2"]."',
time_frame_2 ='".$_POST["time_frame_2"]."',
short_term_goals_3 ='".$_POST["short_term_goals_3"]."',
time_frame_3 ='".$_POST["time_frame_3"]."',
long_term_goals ='".$_POST["long_term_goals"]."',
discharge_criteria ='".$_POST["discharge_criteria"]."',
individual_family_therapy ='".$_POST["individual_family_therapy"]."',
substance_abuse ='".$_POST["substance_abuse"]."',
group_therapy ='".$_POST["group_therapy"]."',
parenting ='".$_POST["parenting"]."',
action_steps_by_supports ='".$_POST["action_steps_by_supports"]."',
other_supports_name_1 ='".$_POST["other_supports_name_1"]."',
other_supports_name_2 ='".$_POST["other_supports_name_2"]."',
other_supports_contact_1 ='".$_POST["other_supports_contact_1"]."',
other_supports_contact_2 ='".$_POST["other_supports_contact_2"]."',
medications_1 ='".$_POST["medications_1"]."',
medications_2 ='".$_POST["medications_2"]."',
referrals_1 ='".$_POST["referrals_1"]."',
referrals_2 ='".$_POST["referrals_2"]."' where id=$id");
}

$_SESSION["encounter"] = $encounter;
formHeader("Redirecting....");
formJump();
formFooter();
