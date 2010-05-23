<?php
// Cloned from patient_encounter.php.

include_once("../../globals.php");
include_once("$srcdir/encounter.inc");
?>
<html>
<head>
<? html_header_show();?>
</head>
<frameset rows="*" cols="200,400,*">
 <?php 
   echo '<frame src="coding.php" name="Codesets" scrolling="auto">';
   echo '<frame src="blank.php" name="Codes" scrolling="auto">';
   echo '<frame src="diagnosis.php" name="Diagnosis" scrolling="auto"> '; 
 ?>
</frameset>
</html>
