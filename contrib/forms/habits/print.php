<?
# print the habits form.


include("../../../library/api.inc");

formHeader("Habits form");

	// this part is the copy of what we have inside the function on the report.php file
	include_once ('form_report.php');

?>
<hr>
<a href="../../patient_file/encounter/patient_encounter.php">Done</a>

<?php
formFooter();
?>
