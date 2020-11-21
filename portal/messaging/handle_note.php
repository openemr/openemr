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

// Will start the (patient) portal OpenEMR session/cookie.
require_once(dirname(__FILE__) . "/../../src/Common/Session/SessionUtil.php");
OpenEMR\Common\Session\SessionUtil::portalSessionStart();

if (isset($_SESSION['pid']) && isset($_SESSION['patient_portal_onsite_two'])) {
    $ignoreAuth_onsite_portal = true;
    require_once(dirname(__FILE__) . "/../../interface/globals.php");
} else {
    OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
    $ignoreAuth = false;
    require_once(dirname(__FILE__) . "/../../interface/globals.php");
    if (! isset($_SESSION['authUserID'])) {
        $landingpage = "index.php";
        header('Location: ' . $landingpage);
        exit();
    }
}

require_once(dirname(__FILE__) . "/../lib/portal_mail.inc");
require_once("$srcdir/pnotes.inc");

$task = $_POST['task'];
if (! $task) {
    return 'no task';
}

$noteid = $_POST['noteid'] ? $_POST['noteid'] : 0;
$notejson = $_POST['notejson'] ? json_decode($_POST['notejson'], true) : 0;
$reply_noteid = $_POST['replyid'] ? $_POST['replyid'] : 0;
$owner = isset($_POST['owner']) ? $_POST['owner'] : $_SESSION['pid'];
$note = $_POST['inputBody'];
$title = $_POST['title'];
$sid = $_POST['sender_id'];
$sn = $_POST['sender_name'];
$rid = $_POST['recipient_id'];
$rn = $_POST['recipient_name'];
$header = '';

switch ($task) {
    case "forward":
        $pid = isset($_POST['pid']) ? $_POST['pid'] : 0;
        addPnote($pid, $note, 1, 1, $title, $sid, '', 'New');
        updatePortalMailMessageStatus($noteid, 'Sent');
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
        updatePortalMailMessageStatus($noteid, 'Delete');
        if (empty($_POST["submit"])) {
            echo 'ok';
        }
        break;
    case "massdelete":
        foreach ($notejson as $deleteid) {
            updatePortalMailMessageStatus($deleteid, 'Delete');
            if (empty($_POST["submit"])) {
                echo 'ok';
            }
        }
        break;
    case "setread":
        if ($noteid > 0) {
            updatePortalMailMessageStatus($noteid, 'Read');
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
