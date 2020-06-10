<?php

//------------Forms generated from formsWiz
require_once("../../globals.php");
require_once("$srcdir/api.inc");
require_once("$srcdir/forms.inc");

if ($encounter == "") {
    $encounter = date("Ymd");
}

if ($_GET["mode"] == "new") {
    $newid = formSubmit("form_pain", $_POST, $_GET["id"], $userauthorized);
    addForm($encounter, "Pain Evaluation", $newid, "pain", $pid, $userauthorized);
} elseif ($_GET["mode"] == "update") {
    sqlStatement("update form_pain set pid = ?,groupname = ?, user = ?, authorized = ?, activity = 1, date = NOW(), history_of_pain = ?,
    dull = ?, colicky = ?, sharp = ?, duration_of_pain = ?, pain_referred_to_other_sites= ?, what_relieves_pain= ?,
    what_makes_pain_worse= ?, accompanying_symptoms_vomitting= ?, accompanying_symptoms_nausea= ?,
    accompanying_symptoms_headache = ?, accompanying_symptoms_other = ?, additional_notes= ? WHERE id = ?", array($_SESSION["pid"], $_SESSION["authProvider"], $_SESSION["authUser"], $userauthorized, $_POST["history_of_pain"], $_POST["dull"], $_POST["colicky"], $_POST["sharp"], $_POST["duration_of_pain"], $_POST["pain_referred_to_other_sites"],
    $_POST["what_relieves_pain"], $_POST["what_makes_pain_worse"], $_POST["accompanying_symptoms_vomitting"], $_POST["accompanying_symptoms_nausea"], $_POST["accompanying_symptoms_headache"],
    $_POST["accompanying_symptoms_other"], $_POST["additional_notes"], $id));
}

formHeader("Redirecting....");
formJump();
formFooter();
