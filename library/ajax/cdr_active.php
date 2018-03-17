<?php
/**
 * active reminders (ajax version of popup gui)
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2011-2017 Brady Miller <brady.g.miller@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once(dirname(__FILE__) . "/../../interface/globals.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/clinical_rules.php");

// Set the session flag to show that notification was last done with this patient
$_SESSION['alert_notify_pid'] = $pid;

function forceArray ($objIn) {
    if (is_array($objIn)) {
        return $objIn;
    } else {
        return array($objIn);
    }
}
$cdrActiveAlerts = array();


if ($GLOBALS['enable_allergy_check']) {
  // Will show allergy and medication/prescription conflicts here
    $chkCdr = allergy_conflict($pid, 'all', $_SESSION['authUser']);
    if ($chkCdr) {
        $cdrActiveAlerts[xlt("ALLERGY WARNING")] = forceArray($chkCdr);
    }
}

$chkCdr = active_alert_summary($pid, "reminders-due", '', 'default', $_SESSION['authUser']);
if ($chkCdr) {
    $pfxCdr = xlt("Reminder");
    if (strpos(trim($chkCdr), ($pfxCdr.': ')) == 0) {
        $chkCdr = trim(substr(trim($chkCdr), strlen($pfxCdr)+2));
    }
    $cdrActiveAlerts[xlt("Reminder")] = forceArray($chkCdr);
}

echo json_encode($cdrActiveAlerts);
?>