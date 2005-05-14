<?
// file new.php
include_once("../../globals.php");
include_once("../../../library/api.inc");
formHeader("Phone Exam");

?>
<html><head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>

<br>
<form method='post' action="<?echo $rootdir;?>/forms/phone_exam/save.php?mode=new" name='phone_exam_form' target='Main' enctype="multipart/form-data">
<span class=title>Phone Exam</span>
<br>

<span class=text>Notes:</span><br>
<textarea name="notes" wrap="virtual" cols="45" rows="10"></textarea><br>


<!--REM note our nifty jscript submit -->
<input type="hidden" name="action" value="submit">
<a href="javascript:document.phone_exam_form.submit();" class="link_submit">[Save]</a>
<br>

<a href="<?echo "$rootdir/patient_file/encounter/patient_encounter.php";?>" class="link">[Don't Save]</a>
</form>




<?php
formFooter();
?>
