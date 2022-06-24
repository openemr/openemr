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
$data = (array)(json_decode(file_get_contents("php://input")));
$req_pid = $data['pid'];
$user = $data['user'];
$signer = !empty($data['signer']) ? $data['signer'] : '';
$type = $data['type'];
$isPortal = $data['is_portal'];
$output = urldecode($data['output']);
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
        echo js_escape("error invalid session,");
        exit();
    }
}
require_once("../../../interface/globals.php");

if ($type === 'witness-signature') {
    echo(js_escape('Done'));
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($type === 'admin-signature') {
        $req_pid = 0;
    }
    $sig_hash = hash('sha3-512', $output);
    $created = time();
    $ip = $_SERVER['REMOTE_ADDR'];
    $status = 'filed';
    $lastmod = date('Y-m-d H:i:s');
    $r = sqlStatement("SELECT COUNT( DISTINCT TYPE ) x FROM onsite_signatures where pid = ? and user = ? ", array($req_pid, $user));
    $c = sqlFetchArray($r);
    $isit = $c['x'] * 1;
    if ($isit) {
        $qstr = "UPDATE onsite_signatures SET pid=?,lastmod=?,status=?, user=?, signature=?, sig_hash=?, ip=?,sig_image=? WHERE pid=? && user=?";
        $rcnt = sqlStatement($qstr, array($req_pid, $lastmod, $status, $user, null, $sig_hash, $ip, $output, $req_pid, $user));
    } else {
        $qstr = "INSERT INTO onsite_signatures (pid,lastmod,status,type,user,signator, signature, sig_hash, ip, created, sig_image) VALUES (?,?,?,?,?,?,?,?,?,?,?) ";
        sqlStatement($qstr, array($req_pid, $lastmod, $status, $type, $user, $signer, null, $sig_hash, $ip, $created, $output));
    }

    echo json_encode('Done', JSON_THROW_ON_ERROR);
    exit();
}
