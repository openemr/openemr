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

use OpenEMR\Common\Session\SessionUtil;
use OpenEMR\Core\OEGlobalsBag;

// Will start the (patient) portal OpenEMR session/cookie.
// Need access to classes, so run autoloader now instead of in globals.php.
require_once(__DIR__ . "/../../vendor/autoload.php");
$globalsBag = OEGlobalsBag::getInstance();
SessionUtil::portalSessionStart();

if (isset($_SESSION['pid']) && isset($_SESSION['patient_portal_onsite_two'])) {
    // ensure patient is bootstrapped (if sent)
    if (!empty($_POST['pid'])) {
        if ($_POST['pid'] != $_SESSION['pid']) {
            echo "illegal Action";
            SessionUtil::portalSessionCookieDestroy();
            exit;
        }
    }
    $ignoreAuth_onsite_portal = true;
    require_once(__DIR__ . "/../../interface/globals.php");
    if (empty($_SESSION['portal_username'])) {
        echo xlt("illegal Action");
        SessionUtil::portalSessionCookieDestroy();
        exit;
    }
    // owner is the patient portal_username
    $owner = $_SESSION['portal_username'];

    // ensure the owner is bootstrapped to the $_POST['sender_id'] and
    //   $_POST['sender_name'], if applicable
    if (empty($_POST['sender_id']) && !empty($_POST['sender_name'])) {
        echo xlt("illegal Action");
        SessionUtil::portalSessionCookieDestroy();
        exit;
    }
    if (!empty($_POST['sender_id'])) {
        if ($_POST['sender_id'] != $owner) {
            echo xlt("illegal Action");
            SessionUtil::portalSessionCookieDestroy();
            exit;
        }
    }
    if (!empty($_POST['sender_name'])) {
        $nameCheck = sqlQuery("SELECT `fname`, `lname` FROM `patient_data` WHERE `pid` = ?", [$_SESSION['pid']]);
        if (empty($nameCheck) || ($_POST['sender_name'] != ($nameCheck['fname'] . " " . $nameCheck['lname']))) {
            echo xlt("illegal Action");
            SessionUtil::portalSessionCookieDestroy();
            exit;
        }
    }
} else {
    SessionUtil::portalSessionCookieDestroy();
    $ignoreAuth = false;
    require_once(__DIR__ . "/../../interface/globals.php");
    if (!isset($_SESSION['authUserID']) || empty($_SESSION['authUser'])) {
        $landingpage = "index.php";
        header('Location: ' . $landingpage);
        exit();
    }
    //owner is the user authUser
    $owner = $_SESSION['authUser'];
}

require_once(__DIR__ . "/../lib/portal_mail.inc.php");
require_once("{$globalsBag->getString('srcdir')}/pnotes.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;

if (!$globalsBag->getBoolean('portal_onsite_two_enable')) {
    echo xlt('Patient Portal is turned off');
    exit;
}
// confirm csrf (from both portal and core)
if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"], 'messages-portal')) {
    CsrfUtils::csrfNotVerified();
}

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
$sid = $_POST['sender_id'] ?? null;
$sn = $_POST['sender_name'] ?? null;
$rid = $_POST['recipient_id'] ?? null;
$rn = $_POST['recipient_name'] ?? null;
$header = '';

switch ($task) {
    case "forward":
        $pid = $_POST['pid'] ?? 0;
        addPnote($pid, $note, 1, 1, $title, $sid, '', 'New');
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

if (!empty($_POST["submit"])) {
    $url = $_POST["submit"];
    header("Location: " . $url);
    exit();
}
