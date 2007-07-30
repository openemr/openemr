<?php
// file new.php
// presents a vital signs form 
// this file made by andres@paglayan.com on 2004-09-23

include_once("../../globals.php");
include_once("../../../library/api.inc");
formHeader("Vital Signs");
?>
<html><head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>

<!--REM note that every input method has the same name as a valid column, this will make things easier in save.php -->

<br>
<form method='post' action="<?echo $rootdir;?>/forms/vitals/save.php?mode=new" name='vitals' >

<!-- the form goes here -->
<?php
	$obj=array(); // just to avoid undeclared var warning
	include ('form.php'); // to use a single file for both, empty and editing
?>
<!-- the form ends here -->

<!--REM note our nifty jscript submit -->
<a href="javascript:top.restoreSession();document.vitals.submit();" class="link_submit">[Save]</a>
<br>

<a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="link" onclick="top.restoreSession()">[Don't Save]</a>
</form>

<?php
formFooter();
?>
