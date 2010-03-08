<?
include_once("../../globals.php");

include_once("$srcdir/sql.inc");
include_once("$srcdir/patient.inc");

//function called to set the global session variable for patient id (pid) number
function setpid($new_pid) {
    global $pid;

    $_SESSION['pid']=$new_pid;
    $pid=$new_pid;

    newEvent("view",$_SESSION["authUser"],$_SESSION["authProvider"],1, $pid);
}

//check if the name already exists:
if ($result = sqlQuery("select * from patient_data where lower(fname)=lower('".$_POST["fname"]."') and lower(lname)=lower('".$_POST["lname"]."')")) {
    //setpid($result{"pid"});
} else {
    //here, we lock the patient data table while we find the most recent max PID
    //other interfaces can still read the data during this lock, however
    sqlStatement("lock tables patient_data read");

    $result = sqlQuery("select max(pid)+1 as pid from patient_data");

    sqlStatement("unlock tables");
    //end table lock

    //do not set pid
    //setpid($result{"pid"});
    $pid = $result{"pid"};

    if($pid == NULL) { $pid = 0; }

    //what do we set for the public pid?
    if (isset($_POST["pubpid"]) && ($_POST["pubpid"] != "")) {
        $mypubpid = $_POST["pubpid"];
    } else {
        $mypubpid = $pid;
    }

    newPatientData($_POST["db_id"],
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
}

?>

<html>
<body>
<script language="Javascript">

window.location="<?php echo "find_patient.php?mode=setpatient&pid=$pid";?>";

</script>

</body>
</html>
