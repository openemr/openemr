<?
include_once("../../globals.php");
include_once("$srcdir/forms.inc");
include_once("$srcdir/encounter.inc");
include_once("$srcdir/patient.inc");

//if (isset($_GET["set_encounter"])) {
//	$_SESSION["encounter"] = $_GET["set_encounter"];
//} else {
//	$_SESSION["encounter"] = "";
//}
//$encounter = $_SESSION["encounter"];
//setencounter($_GET["set_encounter"]);
?>

<html>
<head>

<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">

</head>
<body <?echo $title_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>

<?
$result = getPatientData($pid, "fname,lname");
?>

<table border="0" cellspacing="0" cellpadding="0" width="100%" height="100%">
<tr>
<td>
<span class="title_bar_top"><?echo $result{"fname"} . " " . $result{"lname"};?></span>
</td>
<td align=right>

</td>
</tr>
</table>

</body>
</html>
