<!-- view.php -->
<?php
include_once("../../globals.php");
include_once("../../../library/api.inc");
formHeader("Form: CAMOS");
?>
<html><head>
<? html_header_show();?>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
<form method=post action="<?echo $rootdir?>/forms/CAMOS/save.php?mode=delete&id=<?echo $_GET["id"];?>" name="my_form">
<h1> CAMOS </h1>
<input type="submit" name="submit form" value="Delete" /><?
echo "<a href='".$GLOBALS['webroot'] . "/interface/patient_file/encounter/patient_encounter.php'>[do nothing]</a>";
?>
</form>
<?php
formFooter();
?>
