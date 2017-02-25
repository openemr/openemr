<?php
/** 
 *
 * Copyright (C) 2016-2017 Jerry Padgett <sjpadgett@gmail.com>
 *
 * LICENSE: This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEMR
 * @author Jerry Padgett <sjpadgett@gmail.com>
 * @link http://www.open-emr.org
 */

$sanitize_all_escapes=true;
$fake_register_globals=false;

$ignoreAuth = true;
require_once ("../../../interface/globals.php");

$errors = array ();
// @TODO sanatize these
$pid = $_GET ['pid'];
$user = $_GET ['user'];
$type = $_GET ['type'];
$signer = $_GET ['signer'];

if ( $pid == 0 || empty($user) ){
    if( $type != 'admin-signature' || empty($user) ){
        echo ('error');
        return;
    }
}
$sig_hash = sha1 ( $output );
$created = time();
$ip = $_SERVER ['REMOTE_ADDR'];
$status = 'filed';
$lastmod = date ( 'Y-m-d H:i:s' );
if ($type == 'admin-signature') {
    $pid = 0;
    $row = sqlQuery( "SELECT pid,status,sig_image,type,user FROM onsite_signatures WHERE user=? && type=?", array($user,$type) );
} else {
	$row = sqlQuery( "SELECT pid,status,sig_image,type,user FROM onsite_signatures WHERE pid=?", array($pid) );
}
if ( !$row ['pid'] && !$row ['user']) {
    $status = 'waiting';
    $qstr = "INSERT INTO onsite_signatures (pid,lastmod,status,type,user,signator,created) VALUES (?,?,?,?,?,?,?) ";
    sqlStatement( $qstr, array($pid,$lastmod, $status,$type,$user,$signer,$created) );
}
if ($row ['status'] == 'filed') {
    header ( "Content-Type: image/png" );
    echo $row ['sig_image'];
    return;
} else if ($row ['status'] == 'waiting' || $status  == 'waiting') {
    echo 'waiting';
    return;
}

?>