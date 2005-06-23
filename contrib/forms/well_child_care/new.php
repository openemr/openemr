<?
// file new.php for well child evaluation
// input designed by Lowell Gordon, MD lgordon@whssf.org


include_once("../../globals.php");
include_once("../../../library/api.inc");
include_once("C_WellChildCare.class.php");


?>
<html><head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>

<!--REM note that every input method has the same name as a valid column, this will make things easier in save.php -->

<br>
<form method='post' action="<?echo $rootdir;?>/forms/well_child_care/save.php?mode=new" name='well_child_care' target='Main' >

<!-- the form goes here -->
<?php
		$form=new C_WellChildCare($pid);
		$a=$form->put_form();
?>
<!-- the form ends here -->

<!--REM note our nifty jscript submit -->
<a href="javascript:document.well_child_care.submit();" class="link_submit">[Save]</a>
<br>

<a href="<?echo "$rootdir/patient_file/encounter/patient_encounter.php";?>" class="link">[Don't Save]</a>
</form>



<?php
formFooter();
?>
