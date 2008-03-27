<!-- view.php -->
<?php
include_once("../../globals.php");
include_once("../../../library/api.inc");
formHeader("Form: CAMOS");
?>
<html><head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
</head>
<body class="body_top">
<form method=post action="<?php echo $rootdir?>/forms/CAMOS/save.php?mode=delete&id=<?php echo $_GET["id"];?>" name="my_form">
<h1> CAMOS </h1>
<input type="submit" name="submit form" value="Delete" /><?php
echo "<a href='".$GLOBALS['webroot'] . "/interface/patient_file/encounter/patient_encounter.php'>[do nothing]</a>";
?>
</form>
<?php
formFooter();
?>
