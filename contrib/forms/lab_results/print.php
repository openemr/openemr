<?php
include_once("../../globals.php");
include_once("../../../library/api.inc");
formHeader("Lab Results");
?>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
<html>
<head>
<title>New Patient Encounter</title>
</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
<br><br>
<?php
$row = formFetch('form_lab_results', $_GET['id']);
echo $row['notes'];
?>
<br><br>
<a href="<?php echo $GLOBALS['form_exit_url']; ?>" onclick="top.restoreSession()">Done</a>
</body>
<?php
formFooter();
?>
