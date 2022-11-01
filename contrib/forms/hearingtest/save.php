<?php

//------------Forms generated from formsWiz
require_once("../../globals.php");
require_once("$srcdir/api.inc.php");
require_once("$srcdir/forms.inc.php");

if ($encounter == "") {
    $encounter = date("Ymd");
}

if ($_GET["mode"] == "new") {
    $newid = formSubmit("form_hearingtest", $_POST, $_GET["id"], $userauthorized);
    addForm($encounter, "Hearing Test", $newid, "hearingtest", $pid, $userauthorized);
} elseif ($_GET["mode"] == "update") {
    sqlStatement(
        "update form_hearingtest set pid=?, groupname=? , user=?, authorized=?, activity=1, date = NOW(), right_ear_250=?, right_ear_500=?, right_ear_1000=?, right_ear_2000=?, right_ear_3000=?, right_ear_4000=?, right_ear_5000=?, right_ear_6000=?, left_ear_250=?, left_ear_500=?, left_ear_1000=?, left_ear_2000=?, left_ear_3000=?, left_ear_4000=?, left_ear_5000=?, left_ear_6000=?, with_hearing_aid=?, additional_notes=? where id=?",
        [
            $_SESSION["pid"],
            $_SESSION["authProvider"],
            $_SESSION["authUser"],
            $userauthorized,
            $_POST["right_ear_250"],
            $_POST["right_ear_500"],
            $_POST["right_ear_1000"],
            $_POST["right_ear_2000"],
            $_POST["right_ear_3000"],
            $_POST["right_ear_4000"],
            $_POST["right_ear_5000"],
            $_POST["right_ear_6000"],
            $_POST["left_ear_250"],
            $_POST["left_ear_500"],
            $_POST["left_ear_1000"],
            $_POST["left_ear_2000"],
            $_POST["left_ear_3000"],
            $_POST["left_ear_4000"],
            $_POST["left_ear_5000"],
            $_POST["left_ear_6000"],
            $_POST["with_hearing_aid"],
            $_POST["additional_notes"],
            $id
        ]
    );
}

formHeader("Redirecting....");
formJump();
formFooter();
