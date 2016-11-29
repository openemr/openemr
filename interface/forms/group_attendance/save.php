<?php
include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");

foreach ($_POST as $k => $var) {
    $_POST[$k] = add_escape_custom($var);
    echo "$var\n";
}

$res = sqlStatement("SELECT MAX(id) as largestId FROM `form_therapy_group_attendance`");
$getMaxid = sqlFetchArray($res);
if ($getMaxid['largestId']) {
    $newid = $getMaxid['largestId'] + 1;
} else {
    $newid = 1;
}

addForm($encounter, "Group Attendance Form", $newid, "group_attendance", null, $userauthorized);

$_SESSION["encounter"] = $encounter;
formHeader("Redirecting....");
formJump();
formFooter();
?>