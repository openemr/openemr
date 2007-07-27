<?php
include_once("../../globals.php");
include_once("../../../library/api.inc");
formHeader("Form: CAMOS");
$returnurl = $GLOBALS['concurrent_layout'] ? 'encounter_top.php' : 'patient_encounter.php';
?>
<html><head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
<form method=post action="<?echo $rootdir;?>/forms/CAMOS/save.php?mode=new" name="my_form"
 onsubmit="return top.restoreSession()">
<h1><php xl ('CAMOS','e'); ?> </h1>
<hr>
<input type="submit" name="submit form" value="<?php xl('submit form','e'); ?>" />
<?php
echo "<a href='".$GLOBALS['webroot'] .
  "/interface/patient_file/encounter/$returnurl' onclick='top.restoreSession()'>[" .
  xl('do not save') . "]</a>";
?>
<table></table><h3><php xl ('Computer Aided Medical Ordering System','e'); ?></h3>
<table><tr><td><php xl ('category','e'); ?></td> <td><input type="text" name="category"  /></td></tr>
<tr><td><php xl ('subcategory','e'); ?></td> <td><input type="text" name="subcategory"  /></td></tr>
<tr><td><php xl ('item','e'); ?></td> <td><input type="text" name="item"  /></td></tr>
<tr><td><php xl ('content','e'); ?></td> <td><input type="text" name="content"  /></td></tr>
</table><input type="submit" name="submit form" value="submit form" />
<?php
echo "<a href='" . $GLOBALS['webroot'] .
  "/interface/patient_file/encounter/$returnurl' onclick='top.restoreSession()'>[" .
  xl('do not save') ."]</a>";
?>

</form>
<?php
formFooter();
?>
