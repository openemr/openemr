<?php

/**
 * Patient Portal
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2016-2021 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

//Need to unwrap data to ensure user/patient is authorized
$data = (array)(json_decode(file_get_contents("php://input"), true, 512, JSON_THROW_ON_ERROR));
$req_pid = $data['pid'];
$user = $data['user'];
$type = $data['type'];
$isPortal = $data['is_portal'];
$signer = '';
$ignoreAuth = false;

// this script is used by both the patient portal and main openemr; below does authorization.
if ($isPortal) {
    require_once(__DIR__ . "/../../../src/Common/Session/SessionUtil.php");
    OpenEMR\Common\Session\SessionUtil::portalSessionStart();

    if (isset($_SESSION['pid']) && isset($_SESSION['patient_portal_onsite_two'])) {
        // authorized by patient portal
        $req_pid = $_SESSION['pid'];
        $ignoreAuth_onsite_portal = true;
    } else {
        OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
        echo js_escape("error");
        exit();
    }
}
require_once("../../../interface/globals.php");

if (!$isPortal) {
    $userManipulatedFlag = false;
    if ($user != $_SESSION['authUserID']) {
        $userManipulatedFlag = true;
    }

    if ($userManipulatedFlag) {
        echo js_escape("error");
        exit();
    }
}

$created = time();
$lastmod = date('Y-m-d H:i:s');
$status = 'filed';
$info_query = array();
$isAdmin = ($type === 'admin-signature');
if ($isAdmin) {
    $req_pid = 0;
}
if ($type === 'witness-signature') {
    echo(js_escape('Witness Signature'));
    exit();
}
if ($req_pid === 0 || empty($user)) {
    if (!$isAdmin) {
        echo(js_escape('error not an admin'));
        exit();
    }
}

if (($data['mode'] ?? null) === 'fetch_info') {
    $stmt = "Select CONCAT(IFNULL(fname,''), ' ',IFNULL(lname,'')) as userName From users Where id = ?";
    $user_result = sqlQuery($stmt, array($user)) ?: [];
    $stmt = "Select CONCAT(IFNULL(fname,''), ' ',IFNULL(lname,'')) as ptName From patient_data Where pid = ?";
    $pt_result = sqlQuery($stmt, array($req_pid)) ?: [];
    $signature = [];
    if ($pt_result) {
        $info_query = array_merge($pt_result, $user_result, $signature);
    } else {
        $info_query = array_merge($user_result, $signature);
    }

    if ($isAdmin) {
        $signer = $user_result['userName'];
    } else {
        $signer = $pt_result['ptName'];
    }
    if (!$signer) {
        echo js_escape("error");
        exit();
    }
}

if ($isAdmin) {
    $req_pid = 0;
    $row = sqlQuery("SELECT pid,status,sig_image,type,user FROM onsite_signatures WHERE user=? && type=?", array($user, $type));
} else {
    $row = sqlQuery("SELECT pid,status,sig_image,type,user FROM onsite_signatures WHERE pid=? And user=?", array($req_pid, $user));
}

if (!($row['pid'] ?? null) && !($row['user'] ?? null)) {
    $status = 'waiting';
    $qstr = "INSERT INTO onsite_signatures (pid,lastmod,status,type,user,signator,created) VALUES (?,?,?,?,?,?,?)";
    sqlStatement($qstr, array($req_pid, $lastmod, $status, $type, $user, $signer, $created));
}

if (($row['status'] ?? null) == 'filed') {
    if (($data['mode'] ?? null) === 'fetch_info') {
        $info_query['signature'] = $row['sig_image'];
        echo js_escape($info_query);
        exit();
    }
    echo js_escape($row['sig_image']);
} elseif (($row['status'] ?? null) == 'waiting' || $status == 'waiting') {
    $info_query['message'] = 'waiting';
    echo js_escape($info_query);
}

exit();
