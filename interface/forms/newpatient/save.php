<?
include_once("../../globals.php");
include_once("$srcdir/forms.inc");
include_once("$srcdir/sql.inc");
include_once("$srcdir/encounter.inc");

foreach ($_POST as $k => $var) {
	$_POST[$k] = mysql_escape_string($var);
	echo "$var\n";
}

//if ($encounter == "") {
	//$encounter = date("Ymd");
	$conn = $GLOBALS['adodb']['db'];
	$encounter = $conn->GenID("sequences");
	setEncounter( $encounter );
//}


$date = $_POST["year"]."-".$_POST["month"]."-".$_POST["day"];
if ($mode == 'new') {

addForm($encounter, "New Patient Encounter", idSqlStatement("insert into form_encounter set date='$date',reason='$reason',facility='$facility',pid='$pid',encounter='$encounter'"), "newpatient", $pid, $userauthorized, $date);

} elseif ($mode == 'update') {
	$id = $_POST["id"];
	sqlStatement("update form_encounter set date='$date',reason='$reason',facility='$facility' where id='$id'");
}

$_SESSION["encounter"] = $encounter;



?>


<html>
<body>
<script language="Javascript">

window.location="<?echo "$rootdir/patient_file/encounter/patient_encounter.php";?>";


</script>


</body>
</html>
