<?
include_once("../globals.php");

include_once("$srcdir/sql.inc");
include_once("$srcdir/pid.inc");
include_once("$srcdir/patient.inc");

//here, we lock the patient data table while we find the most recent max PID
//other interfaces can still read the data during this lock, however
sqlStatement("lock tables patient_data read");

$result = sqlQuery("select max(pid)+1 as pid from patient_data");

sqlStatement("unlock tables");
//end table lock
$newpid = 1;

if ($result['pid'] > 1)
	$newpid = $result['pid'];

setpid($newpid);

if($pid == NULL) {
	$pid = 0;
}

//what do we set for the public pid?
if (isset($_POST["pubpid"]) && ($_POST["pubpid"] != "")) {
	$mypubpid = $_POST["pubpid"];
} else {
	$mypubpid = $pid;
}



newPatientData(         $_POST["db_id"],
			$_POST["title"],
                        ucwords($_POST["fname"]),
                        ucwords($_POST["lname"]),
                        ucwords($_POST["mname"]),
                        "",
                        "",
                        "",
                        "",
                        "",
                        "",
                        "",
                        "",
                        "",
                        "",
                        "",
                        "",
                        "",
			"",
			"",
			"",
			"",
                        "",
                        "",
                        "",
                        "",
                        "",
                        "",
                        "",
                        "",
                        "$mypubpid",
                        $pid
                );


newEmployerData( $pid);

newHistoryData( $pid);


newInsuranceData( $pid, "primary");




newInsuranceData( $pid, "secondary");


newInsuranceData( $pid, "tertiary");






?>
<html>
<body>
<script language="Javascript">
<!--
window.location="<?echo "$rootdir/patient_file/patient_file.php?set_pid=$pid";?>";
-->
</script>


</body>
</html>
