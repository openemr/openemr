<?
include_once("../../globals.php");
include_once("$srcdir/patient.inc");
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
<td valign="middle" nowrap>

<span class="title_bar_top"><?echo $result{"fname"} . " " . $result{"lname"};?></span>

</td>
</tr>
</table>

</body>
</html>
