<!-- Form generated from formsWiz -->
<?php
include_once("../../globals.php");
include_once("$srcdir/api.inc");
formHeader("Form: dictation");
?>
<html><head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
<form method=post action="<?echo $rootdir;?>/forms/dictation/save.php?mode=new" name="my_form">
<span class="title">Speech Dictation</span><br><br>
<span class=text>Dictation: </span><br><textarea cols=80 rows=24 wrap=virtual name="dictation" ></textarea><br>
<span class=text>Additional Notes: </span><br><textarea cols=80 rows=8 wrap=virtual name="additional_notes" ></textarea><br>
<br>
<a href="javascript:document.my_form.submit();" class="link_submit">[Save]</a>
<br>
<a href="<?echo "$rootdir/patient_file/encounter/patient_encounter.php";?>" class="link">[Don't Save]</a>
</form>
<?php
formFooter();
?>
