<?php 
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
<?php html_header_show();?>


<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

</head>
<body class="body_top">


<?php 
if ($userauthorized) {
?>
<a href="../main/main.php" target=Main><font class=title><?php xl('Billing Report','e')?></font><font class=more><?php echo $tback;?></font></a>
<?php } else {?>
<a href="../main/onotes/office_comments.php" target=Main><font class=title><?php xl('Billing Report','e')?></font><font class=more><?php echo $tback;?></font></a>
<?php 
}
?>
<br>
<?php xl('No billing system is currently active','e')?><br />

<?php 

print_r($_POST);

?>
</body>
</html>

