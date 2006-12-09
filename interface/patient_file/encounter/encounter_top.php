<?php
// Cloned from patient_encounter.php.

include_once("../../globals.php");
include_once("$srcdir/encounter.inc");

if (isset($_GET["set_encounter"])) {
 setencounter($_GET["set_encounter"]);
}
?>
<html>
<head>
</head>
<frameset cols="*,200">
 <frame src="forms.php" name="Forms" scrolling="auto">
 <frame src="new_form.php" name="New Form" scrolling="auto">
</frameset>
</html>
