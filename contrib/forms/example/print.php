<?
include("../../../library/api.inc");
//formHeader("View my form");
//todaye we decide not to use the recommended header
?>
<html>
<body>
<span class=title>New Patient Encounter Form</span>
<br>

<span class=text>Why did you just visit?</span><br>

<?php
$data = formFetch('example', $_GET['id']);
echo $data["reason"];
?>

<br>
<br>

<span class=text>Do they like cats?</span><br>
<?php
echo $data['cats'];
?>
<br>

<br><Br>
<hr>
<a href="../../patient_file/encounter/patient_encounter.php">i'm all done</a>

<?php
formFooter();
?>
