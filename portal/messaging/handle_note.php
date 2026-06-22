<?php

/**
 * handle_note.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2016-2017 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\PortalMessagingSender;

// Will start the (patient) portal OpenEMR session/cookie.
// Need access to classes, so run autoloader now instead of in globals.php.
require_once(__DIR__ . "/../../vendor/autoload.php");
$globalsBag = OEGlobalsBag::getInstance();
$session = SessionWrapperFactory::getInstance()->getActiveSession();

if ($session->has('pid') && $session->has('patient_portal_onsite_two')) {
    // ensure patient is bootstrapped (if sent)
    if (!empty($_POST['pid'])) {
        if ($_POST['pid'] != $session->get('pid')) {
            echo "illegal Action";
            SessionWrapperFactory::getInstance()->destroyPortalSession();
            exit;
        }
    }
    $ignoreAuth_onsite_portal = true;
    require_once(__DIR__ . "/../../interface/globals.php");
    if (empty($session->get('portal_username'))) {
        echo xlt("illegal Action");
        SessionWrapperFactory::getInstance()->destroyPortalSession();
        exit;
    }
    // owner is the patient portal_username
    $owner = $session->get('portal_username');

    // ensure the owner is bootstrapped to the $_POST['sender_id'] and
    //   $_POST['sender_name'], if applicable
    if (empty($_POST['sender_id']) && !empty($_POST['sender_name'])) {
        echo xlt("illegal Action");
        SessionWrapperFactory::getInstance()->destroyPortalSession();
        exit;
    }
    if (!empty($_POST['sender_id'])) {
        if ($_POST['sender_id'] != $owner) {
            echo xlt("illegal Action");
            SessionWrapperFactory::getInstance()->destroyPortalSession();
            exit;
        }
    }
    if (!empty($_POST['sender_name'])) {
        $nameCheck = sqlQuery("SELECT `fname`, `lname` FROM `patient_data` WHERE `pid` = ?", [$session->get('pid')]);
        if (empty($nameCheck) || ($_POST['sender_name'] != ($nameCheck['fname'] . " " . $nameCheck['lname']))) {
            echo xlt("illegal Action");
            SessionWrapperFactory::getInstance()->destroyPortalSession();
            exit;
        }
    }
} else {
    SessionWrapperFactory::getInstance()->destroyPortalSession();
    $ignoreAuth = false;
    $session = SessionWrapperFactory::getInstance()->getCoreSession();
    require_once(__DIR__ . "/../../interface/globals.php");
    if (!$session->has('authUserID') || empty($session->get('authUser'))) {
        $landingpage = "index.php";
        header('Location: ' . $landingpage);
        exit();
    }
    //owner is the user authUser
    $owner = $session->get('authUser');

    // For staff/dashboard sessions, derive sender identity from authenticated
    // session to prevent impersonation via client-supplied values.
    $authUser = $session->get('authUser');
    $staffSenderId = is_string($authUser) ? $authUser : '';
    $staffDisplayName = QueryUtils::fetchSingleValue(
        <<<'SQL'
        SELECT CONCAT(fname, ' ', lname) AS display_name FROM users WHERE username = ?
        SQL,
        'display_name',
        [$staffSenderId]
    );
    $staffSenderName = is_string($staffDisplayName) && trim($staffDisplayName) !== ''
        ? $staffDisplayName
        : $staffSenderId;
}

require_once(__DIR__ . "/../lib/portal_mail.inc.php");
require_once("{$globalsBag->getString('srcdir')}/pnotes.inc.php");


if (!$globalsBag->getBoolean('portal_onsite_two_enable')) {
    echo xlt('Patient Portal is turned off');
    exit;
}
CsrfUtils::checkCsrfInput(INPUT_POST, subject: 'messages-portal', dieOnFail: true);

if (empty($owner)) {
    echo xlt('Critical error, so exiting');
    exit;
}

$task = $_POST['task'];
if (! $task) {
    return 'no task';
}

$noteid = ($_POST['noteid'] ?? null) ?: 0;
$notejson = ($_POST['notejson'] ?? null) ? json_decode((string) $_POST['notejson'], true) : 0;
$reply_noteid = $_POST['replyid'] ?? null ?: 0;
$note = $_POST['inputBody'] ?? null;
$title = $_POST['title'] ?? null;
$rid = $_POST['recipient_id'] ?? null;
// Resolve recipient_name server-side so the stored display name is always the
// patient's real name (fname + lname) rather than whatever the client submitted.
// This prevents portal_username from appearing in the message list when a
// clinician sends a chart note to a patient (issue #11202).
if (!empty($rid)) {
    // First try to look up as a portal patient (rid = portal_username).
    $recipPatient = sqlQuery(
        "SELECT CONCAT(pd.fname, ' ', pd.lname) AS full_name
         FROM patient_data AS pd
         INNER JOIN patient_access_onsite AS pao ON pao.pid = pd.pid
         WHERE pao.portal_username = ?",
        [$rid]
    );
    if (!empty($recipPatient['full_name'])) {
        $rn = $recipPatient['full_name'];
    } else {
        // Fall back to EMR user lookup (rid = username) for staff-to-staff messages.
        $recipUser = sqlQuery(
            "SELECT CONCAT(fname, ' ', lname) AS full_name FROM users WHERE username = ?",
            [$rid]
        );
        $rn = !empty($recipUser['full_name']) ? $recipUser['full_name'] : ($_POST['recipient_name'] ?? null);
    }
} else {
    $rn = $_POST['recipient_name'] ?? null;
}
$header = '';

$postedSenderId = $_POST['sender_id'] ?? null;
$postedSenderName = $_POST['sender_name'] ?? null;
$resolvedStaffSenderId = $staffSenderId ?? null;
$resolvedStaffSenderName = $staffSenderName ?? null;
[$sid, $sn] = (new PortalMessagingSender())->resolve(
    is_string($resolvedStaffSenderId) ? $resolvedStaffSenderId : null,
    is_string($resolvedStaffSenderName) ? $resolvedStaffSenderName : null,
    is_string($postedSenderId) ? $postedSenderId : null,
    is_string($postedSenderName) ? $postedSenderName : null,
);

switch ($task) {
    case "forward":
        $pid = $_POST['pid'] ?? 0;
        addPnote($pid, $note, 1, 1, $title, $sn, '', 'New');
        updatePortalMailMessageStatus($noteid, 'Sent', $owner);
        if (empty($_POST["submit"])) {
            echo 'ok';
        }

        break;
    case "add":
        // each user has their own copy of message
        sendMail($owner, $note, $title, $header, $noteid, $sid, $sn, $rid, $rn, 'New');
        sendMail($rid, $note, $title, $header, $noteid, $sid, $sn, $rid, $rn, 'New', $reply_noteid);
        if (empty($_POST["submit"])) {
            echo 'ok';
        }
        break;
    case "reply":
        sendMail($owner, $note, $title, $header, $noteid, $sid, $sn, $rid, $rn, 'Reply', '');
        sendMail($rid, $note, $title, $header, $noteid, $sid, $sn, $rid, $rn, 'New', $reply_noteid);
        if (empty($_POST["submit"])) {
            echo 'ok';
        }
        break;
    case "delete":
        updatePortalMailMessageStatus($noteid, 'Delete', $owner);
        if (empty($_POST["submit"])) {
            echo 'ok';
        }
        break;
    case "massdelete":
        foreach ($notejson as $deleteid) {
            updatePortalMailMessageStatus($deleteid, 'Delete', $owner);
            if (empty($_POST["submit"])) {
                echo 'ok';
            }
        }
        break;
    case "setread":
        if ($noteid > 0) {
            updatePortalMailMessageStatus($noteid, 'Read', $owner);
            if (empty($_POST["submit"])) {
                echo 'ok';
            }
        } else {
            echo 'missing note id';
        }
        break;
    case "getinbox":
        if ($owner) {
            $result = getMails($owner, 'inbox', '', '');
            echo json_encode($result);
        } else {
            echo 'error';
        }
        break;
    case "getsent":
        if ($owner) {
            $result = getMails($owner, 'sent', '', '');
            echo json_encode($result);
        } else {
            echo 'error';
        }
        break;
    case "getall":
        if ($owner) {
            $result = getMails($owner, 'all', '', '');
            echo json_encode($result);
        } else {
            echo 'error';
        }
        break;
    case "getdeleted":
        if ($owner) {
            $result = getMails($owner, 'deleted', '', '');
            echo json_encode($result);
        } else {
            echo 'error';
        }
        break;
    default:
        echo 'failed';
        break;
}

if (isset($_POST['submit'])) {
    header('Location: messages.php');
}
