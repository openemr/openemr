<?php

/**
 * dupecheck mergerecords.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../../interface/globals.php");
require_once("../../../library/pnotes.inc");
require_once("./Utils.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Common\Twig\TwigContainer;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
    foreach ($_POST as $key => $value) {
        $parameters[$key] = $value;
    }
}

if (!empty($_GET)) {
    if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
    foreach ($_GET as $key => $value) {
        $parameters[$key] = $value;
    }
}

if (!AclMain::aclCheckCore('admin', 'super')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Merge Records")]);
    exit;
}

?>

<html>
<body>

<?php
// check for required data
if (! isset($parameters['masterid'])) {
    echo "Missing a Master Merge ID";
    exit;
}

if (! isset($parameters['otherid'])) {
    echo "Missing a Other matching IDs";
    exit;
}

// get the PID matching the masterid
$sqlstmt = "select pid from patient_data where id=?";
$qResults = sqlStatement($sqlstmt, array($parameters['masterid']));
if (! $qResults) {
    echo "Error fetching master PID.";
    exit;
}

$row = sqlFetchArray($qResults);
$masterPID = $row['pid'];

$commitchanges = false;
if ($parameters['confirm'] == 'yes') {
    $commitchanges = true;
}

// loop over the other IDs and alter their database records
foreach ($parameters['otherid'] as $otherID) {
    // get info about the "otherID"
    $sqlstmt = "select lname, pid from patient_data where id=?";
    $qResults = sqlStatement($sqlstmt, array($otherID));
    if (! $qResults) {
        echo "Error fetching master PID.";
        exit;
    }

    $orow = sqlFetchArray($qResults);
    $otherPID = $orow['pid'];

    echo "Merging PID " . text($otherPID) . " into the master PID " . text($masterPID) . "<br />";

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
    $qResults = sqlStatement($sqlstmt);
    while ($row = sqlFetchArray($qResults)) {
        UpdateTable($row['Tables_in_' . $sqlconf["dbase"] . ' (form%)'], "pid", $otherPID, $masterPID);
    }

    // How to handle the data that should be unique to each patient:
    //  Demographics, Employment, Insurance, and History ??
    //
    //UpdateTable("patient_data", "pid", $otherID, $$parameters['masterid']);
    //UpdateTable("employer_data", "pid", $otherPID, $masterPID);
    //UpdateTable("history_data", "pid", $otherPID, $masterPID);
    //UpdateTable("insurance_data", "pid", $otherPID, $masterPID);

    // alter the patient's last name to indicate they have been merged into another record
    $newlname = "~~~MERGED~~~" . $orow['lname'];
    $sqlstmt = "update patient_data set lname=? where pid=?";
    if ($commitchanges == true) {
        $qResults = sqlStatement($sqlstmt, array($newlname, $otherPID));
    }

    echo "<li>Altered last name of PID " . text($otherPID) . " to '" . text($newlname) . "'</li>";

    // add patient notes regarding the merged data
    $notetext = "All related patient data has been merged into patient record PID# " . $masterPID;
    echo "<li>Added note about the merge to the PID " . text($otherPID) . "</li>";
    if ($commitchanges == true) {
        addPnote($otherPID, $notetext);
    }

    $notetext = "All related patient data has been merged from patient record PID# " . $otherPID;
    echo "<li>Added note about the merge to the Master PID " . text($masterPID) . "</li>";
    if ($commitchanges == true) {
        addPnote($masterPID, $notetext);
    }

    // add a log entry regarding the merged data
    if ($commitchanges == true) {
        EventAuditLogger::instance()->newEvent("data_merge", $_SESSION['authUser'], "Default", 1, "Merged PID " . $otherPID . " data into master PID " . $masterPID);
    }

    echo "<li>Added entry to log</li>";

    echo "<br /><br />";
} // end of otherID loop

function UpdateTable($tablename, $pid_col, $oldvalue, $newvalue)
{
    global $commitchanges;

    $sqlstmt = "select count(*) as numrows from " . $tablename . " where " . $pid_col . "='" . $oldvalue . "'";
    $qResults = sqlStatement($sqlstmt);

    if ($qResults) {
        $row = sqlFetchArray($qResults);
        if ($row['numrows'] > 0) {
            $sqlstmt = "update " . escape_table_name($tablename) . " set " . escape_sql_column_name($pid_col, array($tablename)) . "=? where " . escape_sql_column_name($pid_col, array($tablename)) . "=?";
            if ($commitchanges == true) {
                $qResults = sqlStatement($sqlstmt, array($newvalue, $oldvalue));
            }

            $rowsupdated = generic_sql_affected_rows();
            echo "<li>";
            echo "" . text($tablename) . ": " . text($rowsupdated) . " row(s) updated<br />";
            echo "</li>";
        }
    }
}

?>

<?php if ($commitchanges == false) : ?>
Nothing has been changed yet. What you see above are the changes that will be made if you choose to commit them.<br />
Do you wish to commit these changes to the database?
<form method="post" action="mergerecords.php">
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
<input type="hidden" name="masterid" value="<?php echo attr($parameters['masterid']); ?>">
<input type="hidden" name="dupecount" value="<?php echo attr($parameters['dupecount']); ?>">
    <?php
    foreach ($parameters['otherid'] as $otherID) {
        echo "<input type='hidden' name='otherid[]' value='<?php echo attr($otherID); ?>'>";
    }
    ?>
<input type="submit" name="confirm" value="yes">
<input type="button" value="no" onclick="javascript:window.close();"?>
</form>
<?php else : ?>
<a href="" onclick="javascript:window.close();">Close this window</a>
<?php endif; ?>

</body>
<?php if ($commitchanges == true) : ?>
<script>
window.opener.removedupe(<?php echo js_escape($parameters['dupecount']); ?>);
window.close();
</script>
<?php endif; ?>
</html>
