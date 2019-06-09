<?php
/**
 * Patient Portal
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2016-2019 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../../interface/globals.php");

$data = (array)(json_decode(file_get_contents("php://input")));
$pid = $data['pid'];
$user = $data['user'];
$type = $data['type'];
$signer = $data['signer'];

$created = time();
$lastmod = date('Y-m-d H:i:s');
$status = 'filed';

if ($pid == 0 || empty($user)) {
    if ($type != 'admin-signature' || empty($user)) {
        echo(text('error'));
        return;
    }
}

if ($type == 'admin-signature') {
    $pid = 0;
    $row = sqlQuery("SELECT pid,status,sig_image,type,user FROM onsite_signatures WHERE user=? && type=?", array($user, $type));
} else {
    $row = sqlQuery("SELECT pid,status,sig_image,type,user FROM onsite_signatures WHERE pid=?", array($pid));
}

if (!$row['pid'] && !$row['user']) {
    $status = 'waiting';
    $qstr = "INSERT INTO onsite_signatures (pid,lastmod,status,type,user,signator,created) VALUES (?,?,?,?,?,?,?)";
    sqlStatement($qstr, array($pid, $lastmod, $status, $type, $user, $signer, $created));
}

if ($row['status'] == 'filed') {
    echo js_escape($row['sig_image']);
} elseif ($row['status'] == 'waiting' || $status == 'waiting') {
    echo js_escape('waiting');
}

exit();
