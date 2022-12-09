<?php
// +-----------------------------------------------------------------------+
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// A copy of the GNU General Public License is included along with this 
// program:  openemr/interface/login/GnuGPL.html
// For more information write to the Free Software Foundation, Inc.
// 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
//
// +-----------------------------------------------------------------------+

// SANITIZE ALL ESCAPES
$sanitize_all_escapes = true;

// STOP FAKE REGISTER GLOBALS
$fake_register_globals = false;

require_once("../../../interface/globals.php");
require_once("$srcdir/auth.inc");

$out = '';
if(!isset($_REQUEST['pid'])) $_REQUEST['pid'] = '';
if(!isset($_REQUEST['doc_id'])) $_REQUEST['doc_id'] = '';
if(!isset($_REQUEST['link_type'])) $_REQUEST['link_type'] = '';
if(!isset($_REQUEST['action'])) $_REQUEST['action'] = 'add';
if(!$_REQUEST['pid']) $out .= 'No PID Was Provided - ';
if(!$_REQUEST['doc_id']) $out .= 'No Document ID Was Provided - ';
if(!$_REQUEST['link_type']) $out .= 'No Document Type Was Provided - ';
if($out) {
	echo $out;
	exit;
}

if($_REQUEST['action'] == 'add') {
	$sql = "INSERT INTO wmt_linked_documents (`user`, `date`, `pid`, ".
		"`document_id`, `type`) VALUES (?, NOW(), ?, ?, ?) ON DUPLICATE KEY ".
		"UPDATE `document_id` = ?, `user` = ?, `date` = NOW()";
	$binds = array($_SESSION['authUser'], $_REQUEST['pid'], $_REQUEST['doc_id'], 
		$_REQUEST['link_type'], $_REQUEST['doc_id'], $_SESSION['authUser']);
	sqlStatement($sql, $binds);
} else if($_REQUEST['action'] == 'delete') {
	$sql = "DELETE FROM wmt_linked_documents WHERE `pid` = ? AND  ".
		"`document_id` = ? AND `type` = ?";
	$binds = array($_REQUEST['pid'], $_REQUEST['doc_id'], 
		$_REQUEST['link_type']);
	sqlStatement($sql, $binds);
}

echo "Success";
exit;
?>
