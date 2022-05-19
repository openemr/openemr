<?php

/**
 * Returns a count of due messages for current user.
 *  In 2021, added the timeout mechanism to this script.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Craig Bezuidenhout <https://www.tajemo.co.za/>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2012 tajemo.co.za <https://www.tajemo.co.za/>
 * @copyright Copyright (c) 2018-2021 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../interface/globals.php");
require_once("$srcdir/dated_reminder_functions.php");
require_once("$srcdir/pnotes.inc");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Session\SessionTracker;

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

// ensure timeout has not happened
if (SessionTracker::isSessionExpired()) {
    echo json_encode(['timeoutMessage' => 'timeout']);
    exit;
}
// keep this below above time out check.
OpenEMR\Common\Session\SessionUtil::setSession('keepAliveTime', time());

$portal_count = array();
// if portal is enabled get various alerts
if (!empty($_POST['isPortal'])) {
    $portal_count = GetPortalAlertCounts();
}

//Collect number of due reminders
$dueReminders = GetDueReminderCount(5, strtotime(date('Y/m/d')));

//Collect number of active messages
$activeMessages = getPnotesByUser("1", "no", $_SESSION['authUser'], true);

$totalNumber = $dueReminders + $activeMessages;
$portal_count['reminderText'] = ($totalNumber > 0 ? text((int)$totalNumber) : '');

echo json_encode($portal_count);
