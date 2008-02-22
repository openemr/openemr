<?php
// Cloned from patient_encounter.php.

include_once("../../globals.php");
include_once("$srcdir/pid.inc");
include_once("$srcdir/encounter.inc");

if (isset($_GET["set_encounter"])) {
 // The billing page might also be setting a new pid.
 $set_pid = $_GET["set_pid"] ? $_GET["set_pid"] : $_GET["pid"];
 if ($set_pid && $set_pid != $_SESSION["pid"]) {
  setpid($set_pid);
 }
 setencounter($_GET["set_encounter"]);
}
?>
<html>
<head>
<? html_header_show();?>
</head>
<frameset cols="*,200">
 <frame src="forms.php" name="Forms" scrolling="auto">
 <frame src="new_form.php" name="New Form" scrolling="auto">
</frameset>
</html>
