<?php
/**
 * Returns a count of due messages for current user.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Craig Bezuidenhout <https://www.tajemo.co.za/>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2012 tajemo.co.za <https://www.tajemo.co.za/>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../../interface/globals.php");
require_once("$srcdir/dated_reminder_functions.php");
require_once("$srcdir/pnotes.inc");

use OpenEMR\Common\Csrf\CsrfUtils;

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

// if portal is enable get various alerts
if (isset($_POST['isPortal'])) {
    echo GetPortalAlertCounts();
    exit();
}

//Collect number of due reminders
$dueReminders = GetDueReminderCount(5, strtotime(date('Y/m/d')));

//Collect number of active messages
$activeMessages = getPnotesByUser("1", "no", $_SESSION['authUser'], true);

$totalNumber = $dueReminders + $activeMessages;
echo ($totalNumber > 0 ? '('.text(intval($totalNumber)).')' : '');
