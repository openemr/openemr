<?
include_once("../globals.php");
include_once("$srcdir/upload.inc");

include_once("$srcdir/patient.inc");
include_once("$srcdir/billrep.inc");
include_once("$srcdir/log.inc");


//global variables:
if (!isset($_POST["mode"])) {
	if (!isset($_POST["from_date"])) {
		$from_date=date("Y-m-d");
	} else {
		$from_date = $_POST["from_date"];
	}
	if (!isset($_POST["to_date"])) {
		$to_date = date("Y-m-d");
	} else {
		$to_date = $_POST["to_date"];
	}
	if (!isset($_POST["code_type"])) {
		$code_type="all";
	} else {
		$code_type = $_POST["code_type"];
	}
	if (!isset($_POST["unbilled"])) {
		$unbilled = "on";
	} else {
		$unbilled = $_POST["unbilled"];
	}
	if (!isset($_POST["authorized"])) {
		$my_authorized = "on";
	} else {
		$my_authorized = $_POST["authorized"];
	}
} else {
	$from_date = $_POST["from_date"];
	$to_date = $_POST["to_date"];
	$code_type = $_POST["code_type"];
	$unbilled = $_POST["unbilled"];
	$my_authorized = $_POST["authorized"];
}


?>

<html>
<head>


<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">

</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>


<?
if ($userauthorized) {
?>
<a href="../main/main.php" target=Main><font class=title>Billing Report</font><font class=more><?echo $tback;?></font></a>
<?} else {?>
<a href="../main/onotes/office_comments.php" target=Main><font class=title>Billing Report</font><font class=more><?echo $tback;?></font></a>
<?
}
?>
<br>
No billing system is currently active<br />

<?

print_r($_POST);

?>
</body>
</html>

