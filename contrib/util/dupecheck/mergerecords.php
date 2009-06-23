<?php 
require_once("../../../library/sql.inc");
require_once("./Utils.php");

$parameters = GetParameters();
$oemrdb = $GLOBALS['dbh'];

// check for required data
if (! isset($parameters['masterid'])) { echo "Missing a Master Merge ID"; exit; }
if (! isset($parameters['otherid'])) { echo "Missing a Other matching IDs"; exit; }

// get the PID matching the masterid
$sqlstmt = "select pid from patient_data where id='".$parameters['masterid']."'";
$qResults = mysql_query($sqlstmt);
if (! $qResults) { echo "Error fetching master PID."; exit; }
$row = mysql_fetch_assoc($qResults);
$masterPID = $row['pid'];

// loop over the other IDs and alter their database records
foreach ($parameters['otherid'] as $otherID) {

    // get the PID matching the other ID 
    $sqlstmt = "select pid from patient_data where id='".$otherID."'";
    $qResults = mysql_query($sqlstmt);
    if (! $qResults) { echo "Error fetching master PID."; exit; }
    $row = mysql_fetch_assoc($qResults);
    $otherPID = $row['pid'];

    UpdateTable("batchcom", "patient_id", $otherPID, $masterPID);
    UpdateTable("immunizations", "patient_id", $otherPID, $masterPID);
    UpdateTable("prescriptions", "patient_id", $otherPID, $masterPID);
    UpdateTable("claims", "patient_id", $otherPID, $masterPID);

    UpdateTable("ar_activity", "pid", $otherPID, $masterPID);
    UpdateTable("billing", "pid", $otherPID, $masterPID);
    UpdateTable("drug_sales", "pid", $otherPID, $masterPID);
    UpdateTable("employer_data", "pid", $otherPID, $masterPID);
    UpdateTable("history_data", "pid", $otherPID, $masterPID);
    UpdateTable("insurance_data", "pid", $otherPID, $masterPID);
    UpdateTable("issue_encounter", "pid", $otherPID, $masterPID);
    UpdateTable("lists", "pid", $otherPID, $masterPID);
    UpdateTable("payments", "pid", $otherPID, $masterPID);
    UpdateTable("pnotes", "pid", $otherPID, $masterPID);
    UpdateTable("transactions", "pid", $otherPID, $masterPID);

    UpdateTable("chart_tracker", "ct_pid", $otherPID, $masterPID);
    UpdateTable("openemr_postcalendar_events", "pc_pid", $otherPID, $masterPID);
    UpdateTable("documents", "foreign_id", $otherPID, $masterPID);
   
    // update all the forms* tables
    $sqlstmt = "show tables like 'form%'";
    $qResults = mysql_query($sqlstmt);
    while ($row = mysql_fetch_assoc($qResults)) {
        UpdateTable($row['Tables_in_openemr (form%)'], "pid", $otherPID, $masterPID);
    }
    
    // How to handle the actual patient_data record, delete it? alter fields?
    //UpdateTable("patient_data", "pid", $otherID, $$parameters['masterid']);
    //$sqlstmt = "update patient_data set pid='".$parameters['masterid']."' where pid='".$otherID."'";
    //echo $sqlstmt."<br>";

}

function UpdateTable($tablename, $pid_col, $oldvalue, $newvalue) {
    //$sqlstmt = "update ".$tablename." set ".$pid_col."='".$newvalue."' where ".$pid_col."='".$oldvalue."'";
    
    $sqlstmt = "select count(*) as numrows from ".$tablename." where ".$pid_col."='".$oldvalue."'";
echo $sqlstmt;
    $qResults = mysql_query($sqlstmt);
    if ($qResults) { 
        $row = mysql_fetch_assoc($qResults);
        if ($row['numrows'] > 0) {
echo "<br> ==> ".$row['numrows']." rows";
        }
    }
echo "<br>";

}

?>
