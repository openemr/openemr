<?php
/**
 * Patient Portal
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2016-2019 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

//Need to unwrap data to ensure user/patient is authorized
$data = (array)(json_decode(file_get_contents("php://input")));
$pid = $data['pid'];
$user = $data['user'];
$signer = $data['signer'];
$type = $data['type'];
$output = urldecode($data['output']);

// this script is used by both the patient portal and main openemr; below does authorization.
if ($type == 'patient-signature') {
    // authorize via patient portal

    // Will start the (patient) portal OpenEMR session/cookie.
    require_once(dirname(__FILE__) . "/../../../src/Common/Session/SessionUtil.php");
    OpenEMR\Common\Session\SessionUtil::portalSessionStart();

    if (isset($_SESSION['pid']) && isset($_SESSION['patient_portal_onsite_two'])) {
        // authorized by patient portal
        $pid = $_SESSION['pid'];
        $ignoreAuth = true;
    } else {
        exit();
    }
} else if ($type == 'admin-signature') {
    // authorize via main openemr
    $ignoreAuth = false;
} else {
    exit();
}
require_once("../../../interface/globals.php");


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($type == 'admin-signature') {
        $signer = $user;
    }

    $image_data = $output;

    $sig_hash = sha1($output);
    $created = time();
    $ip = $_SERVER['REMOTE_ADDR'];
    $status = 'filed';
    $lastmod = date('Y-m-d H:i:s');
    $r = sqlStatement("SELECT COUNT( DISTINCT TYPE ) x FROM onsite_signatures where pid = ? and user = ? ", array($pid, $user));
    $c = sqlFetchArray($r);
    $isit = $c['x'] * 1;
    if ($isit) {
        $qstr = "UPDATE onsite_signatures SET pid=?,lastmod=?,status=?, user=?, signature=?, sig_hash=?, ip=?,sig_image=? WHERE pid=? && user=?";
        $rcnt = sqlStatement($qstr, array($pid, $lastmod, $status, $user, $svgsig, $sig_hash, $ip, $image_data, $pid, $user));
    } else {
        $qstr = "INSERT INTO onsite_signatures (pid,lastmod,status,type,user,signator, signature, sig_hash, ip, created, sig_image) VALUES (?,?,?,?,?,?,?,?,?,?,?) ";
        sqlStatement($qstr, array($pid, $lastmod, $status, $type, $user, $signer, $svgsig, $sig_hash, $ip, $created, $image_data));
    }

    echo json_encode('Done');
}
