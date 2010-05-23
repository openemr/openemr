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
 // =========================
 // DBC DUTCH SYSTEM
 if ( $GLOBALS['dutchpc']) {
   echo '<frame src="dbc_content.php" name="Content" scrolling="auto">';
   echo '<frame src="dbc_history.php" name="DBC history" scrolling="auto"> ';
 } else {
   echo '<frame src="coding.php" name="Codesets" scrolling="auto">';
   echo '<frame src="blank.php" name="Codes" scrolling="auto">';
   echo '<frame src="diagnosis.php" name="Diagnosis" scrolling="auto"> '; 
 }
 // EOS
 // =========================
 ?>
</frameset>
</html>
