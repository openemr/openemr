<?
include_once("../../globals.php");
include_once("../../../library/api.inc");

formHeader("Phone Exam");
?>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
                                                                                
<html>
<head>
<title>New Patient Encounter</title>
                                                                                
</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
<br><br>
<?
$row = formFetch('form_phone_exam', $_GET['id']);
echo $row['notes'];
?>
<br><br>
<a href="../../patient_file/encounter/patient_encounter.php">Done</a>
</body>
<?php
formFooter();
?>

