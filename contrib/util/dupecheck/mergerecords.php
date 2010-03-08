<?php 
require_once("../../../interface/globals.php");
require_once("../../../library/pnotes.inc");
require_once("../../../library/log.inc");
require_once("./Utils.php");

$parameters = GetParameters();
$oemrdb = $GLOBALS['dbh'];
?>

<html>
<body>

<?php
// check for required data
if (! isset($parameters['masterid'])) { echo "Missing a Master Merge ID"; exit; }
if (! isset($parameters['otherid'])) { echo "Missing a Other matching IDs"; exit; }

// get the PID matching the masterid
$sqlstmt = "select pid from patient_data where id='".$parameters['masterid']."'";
$qResults = mysql_query($sqlstmt, $oemrdb);
if (! $qResults) { echo "Error fetching master PID."; exit; }
$row = mysql_fetch_assoc($qResults);
$masterPID = $row['pid'];

$commitchanges = false;
if ($parameters['confirm'] == 'yes') { $commitchanges = true; }

// loop over the other IDs and alter their database records
foreach ($parameters['otherid'] as $otherID) {

    // get info about the "otherID"
    $sqlstmt = "select lname, pid from patient_data where id='".$otherID."'";
    $qResults = mysql_query($sqlstmt, $oemrdb);
    if (! $qResults) { echo "Error fetching master PID."; exit; }
    $orow = mysql_fetch_assoc($qResults);
    $otherPID = $orow['pid'];
    
    echo "Merging PID ".$otherPID." into the master PID ".$masterPID."<br>";

    UpdateTable("batchcom", "patient_id", $otherPID, $masterPID);
    UpdateTable("immunizations", "patient_id", $otherPID, $masterPID);
    UpdateTable("prescriptions", "patient_id", $otherPID, $masterPID);
    UpdateTable("claims", "patient_id", $otherPID, $masterPID);

    UpdateTable("ar_activity", "pid", $otherPID, $masterPID);
    UpdateTable("billing", "pid", $otherPID, $masterPID);
    UpdateTable("drug_sales", "pid", $otherPID, $masterPID);
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
    $qResults = mysql_query($sqlstmt, $oemrdb);
    while ($row = mysql_fetch_assoc($qResults)) {
        UpdateTable($row['Tables_in_'.$sqlconf["dbase"].' (form%)'], "pid", $otherPID, $masterPID);
    }
    
    // How to handle the data that should be unique to each patient:
    //  Demographics, Employment, Insurance, and History ??
    //
    //UpdateTable("patient_data", "pid", $otherID, $$parameters['masterid']);
    //UpdateTable("employer_data", "pid", $otherPID, $masterPID);
    //UpdateTable("history_data", "pid", $otherPID, $masterPID);
    //UpdateTable("insurance_data", "pid", $otherPID, $masterPID);

    // alter the patient's last name to indicate they have been merged into another record
    $newlname = "~~~MERGED~~~".$orow['lname'];
    $sqlstmt = "update patient_data set lname='".$newlname."' where pid='".$otherPID."'";
    if ($commitchanges == true) $qResults = mysql_query($sqlstmt, $oemrdb);
    echo "<li>Altered last name of PID ".$otherPID." to '".$newlname."'</li>";

    // add patient notes regarding the merged data
    $notetext = "All related patient data has been merged into patient record PID# ".$masterPID;
    echo "<li>Added note about the merge to the PID ".$otherPID."</li>";
    if ($commitchanges == true) addPnote($otherPID, $notetext);

    $notetext = "All related patient data has been merged from patient record PID# ".$otherPID;
    echo "<li>Added note about the merge to the Master PID ".$masterPID."</li>";
    if ($commitchanges == true) addPnote($masterPID, $notetext);

    // add a log entry regarding the merged data
    if ($commitchanges == true) newEvent("data_merge", $_SESSION['authUser'], "Default", 1, "Merged PID ".$otherPID." data into master PID ".$masterPID);
    echo "<li>Added entry to log</li>";

    echo "<br><br>";
} // end of otherID loop

function UpdateTable($tablename, $pid_col, $oldvalue, $newvalue) {
    global $commitchanges, $oemrdb;

    $sqlstmt = "select count(*) as numrows from ".$tablename." where ".$pid_col."='".$oldvalue."'";
    $qResults = mysql_query($sqlstmt, $oemrdb);

    if ($qResults) { 
        $row = mysql_fetch_assoc($qResults);
        if ($row['numrows'] > 0) {
            $sqlstmt = "update ".$tablename." set ".$pid_col."='".$newvalue."' where ".$pid_col."='".$oldvalue."'";
            if ($commitchanges == true) {
                $qResults = mysql_query($sqlstmt, $oemrdb);
            }
            $rowsupdated = mysql_affected_rows($oemrdb);
            echo "<li>";
            echo "".$tablename.": ".$rowsupdated." row(s) updated<br>";
            echo "</li>";
        }
    }


}

?>

<?php if ($commitchanges == false) : ?>
Nothing has been changed yet. What you see above are the changes that will be made if you choose to commit them.<br>
Do you wish to commit these changes to the database?
<form method="post" action="mergerecords.php">
<input type="hidden" name="masterid" value="<?php echo $parameters['masterid']; ?>">
<input type="hidden" name="dupecount" value="<?php echo $parameters['dupecount']; ?>">
<?php
foreach ($parameters['otherid'] as $otherID) {
    echo "<input type='hidden' name='otherid[]' value='$otherID'>";
}
?>
<input type="submit" name="confirm" value="yes">
<input type="button" value="no" onclick="javascript:window.close();"?>
</form>
<?php else: ?>
<a href="" onclick="javascript:window.close();">Close this window</a>
<?php endif; ?>

</body>
<?php if ($commitchanges == true) : ?>
<script language="javascript">
window.opener.removedupe(<?php echo $parameters['dupecount']; ?>);
window.close();
</script>
<?php endif; ?>
</html>
