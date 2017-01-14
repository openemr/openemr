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
$dsn = "mysql:dbname=" . $GLOBALS ['dbase'] . ";host=" . $GLOBALS ['host'] . ";port=" . $GLOBALS ['port'];
$userdsn = $GLOBALS ['login'];
$pass = $GLOBALS ['pass'];
$db = new PDO ( $dsn, $userdsn, $pass );
$db->setAttribute ( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
$db->setAttribute ( PDO::MYSQL_ATTR_FOUND_ROWS, TRUE );
$db->exec ( 'SET NAMES utf8' );

$sig_hash = sha1 ( $output );
$created = time();
$ip = $_SERVER ['REMOTE_ADDR'];
$status = 'filed';

$lastmod = date ( 'Y-m-d H:i:s' );
if ($type == 'admin-signature') {
    $pid = 0;
    $statement = $db->prepare ( "SELECT pid,status, sig_image,type,user FROM onsite_signatures WHERE user = :user && type=:type" );
    $statement->execute ( array (
            ':user' => $user,
            ':type' => $type
    ) );
} else {
    $statement = $db->prepare ( "SELECT pid,status, sig_image,type,user FROM onsite_signatures WHERE pid = :pid" );
    $statement->execute ( array (
            ':pid' => $pid
    ) );
}
$row = $statement->fetch ();
if ( !$row ['pid'] && !$row ['user']) {
    $status = 'waiting';
    $qstr = "INSERT INTO onsite_signatures (pid,lastmod,status,type,user,signator,created) VALUES (:pid ,:lastmod,:status,:type,:user,:signator,:created) ";
    $pstm = $db->prepare ( $qstr );
    $pstm->bindValue ( ':pid', $pid, PDO::PARAM_INT );
    $pstm->bindValue ( ':lastmod', $lastmod, PDO::PARAM_STR );
    $pstm->bindValue ( ':status', $status, PDO::PARAM_STR );
    $pstm->bindValue ( ':type', $type, PDO::PARAM_STR );
    $pstm->bindValue ( ':user', $user, PDO::PARAM_STR );
    $pstm->bindValue ( ':signator', $signer, PDO::PARAM_STR );
    // $pstm->bindValue ( ':signature', $output, PDO::PARAM_STR );
    // $pstm->bindValue ( ':sig_hash', $sig_hash, PDO::PARAM_STR );
    // $pstm->bindValue ( ':ip', $ip, PDO::PARAM_STR );
    $pstm->bindValue ( ':created', $created, PDO::PARAM_INT );
    try {
        $pstm->execute ();
        echo 'waiting';
        return;
    } catch ( PDOException $e ) {
        echo 'insert error';
        return;
    }
}
if ($row ['status'] == 'filed') {
    header ( "Content-Type: image/png" );
    echo $row ['sig_image'];
    return;
} else if ($row ['status'] == 'waiting') {
    echo 'waiting';
    return;
}

?>