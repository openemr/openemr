<?php

require_once("../../globals.php");
require_once("$srcdir/api.inc");
require_once("$srcdir/forms.inc");

if ($encounter == "") {
    $encounter = date("Ymd");
}

if ($_GET["mode"] == "new") {
    $newid = formSubmit("form_individual_treatment_plan", $_POST, $_GET["id"], $userauthorized);

    addForm($encounter, "Individual Treatment Plan", $newid, "individual_treatment_plan", $pid, $userauthorized);
} elseif ($_GET["mode"] == "update") {
    sqlStatement("update form_individual_treatment_plan set pid = ?, groupname= ?, user = ?, authorized = ?, activity=1, date = NOW(),
date_of_referal = ?,
dcn = ?,
icd9 = ?,
prognosis = ?,
diagnosis_description = ?,
presenting_problem = ?,
frequency = ?,
duration = ?,
scope = ?,
short_term_goals_1 = ?,
time_frame_1 = ?,
short_term_goals_2 = ?,
time_frame_2 = ?,
short_term_goals_3 = ?,
time_frame_3 = ?,
long_term_goals = ?,
discharge_criteria = ?,
individual_family_therapy = ?,
substance_abuse = ?,
group_therapy = ?,
parenting = ?,
action_steps_by_supports = ?,
other_supports_name_1 = ?,
other_supports_name_2 = ?,
other_supports_contact_1 = ?,
other_supports_contact_2 = ?,
medications_1 = ?,
medications_2 = ?,
referrals_1 = ?,
referrals_2 = ? where id=?", array($_SESSION["pid"], $_SESSION["authProvider"], $_SESSION["authUser"], $userauthorized, $_POST["date_of_referal"], $_POST["dcn"], $_POST["icd9"], $_POST["prognosis"],$_POST["diagnosis_description"],
    $_POST["presenting_problem"], $_POST["frequency"], $_POST["duration"], $_POST["scope"], $_POST["short_term_goals_1"], $_POST["time_frame_1"], $_POST["short_term_goals_2"], $_POST["time_frame_2"],
    $_POST["short_term_goals_3"], $_POST["time_frame_3"], $_POST["long_term_goals"], $_POST["discharge_criteria"], $_POST["individual_family_therapy"], $_POST["substance_abuse"], $_POST["group_therapy"],
    $_POST["parenting"], $_POST["action_steps_by_supports"], $_POST["other_supports_name_1"], $_POST["other_supports_name_2"], $_POST["other_supports_contact_1"], $_POST["other_supports_contact_2"],$_POST["medications_1"],
    $_POST["medications_2"], $_POST["referrals_1"], $_POST["referrals_2"], $id));
}

formHeader("Redirecting....");
formJump();
formFooter();
