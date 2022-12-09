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

require_once('../../../interface/globals.php');
require_once($GLOBALS['srcdir'].'/auth.inc');
require_once($GLOBALS['srcdir'].'/pnotes.inc');
require_once($GLOBALS['srcdir'].'/wmt-v2/rvw_log.inc');

$order = 'first';
$pid = $_SESSION['pid'];
if(isset($_REQUEST['pid']))     $pid = strip_tags($_REQUEST['pid']);
if(!isset($_REQUEST['mode']))   $_REQUEST['mode'] = '';
if(!isset($_REQUEST['module'])) $_REQUEST['module'] = '';
if(!isset($_REQUEST['user']))   $_REQUEST['user'] = $_SESSION['authUserID'];
if(!isset($_REQUEST['frmdir'])) $_REQUEST['frmdir'] = '';
if(!isset($_REQUEST['enc']))    $_REQUEST['enc'] = '';
if(!isset($_REQUEST['dt']))     $_REQUEST['dt'] = date('Y-m-d');

if(!$pid || !$_REQUEST['module'] || !$_REQUEST['mode']) {
	echo 'Nothing to Do';
	exit;
}

addReview($_REQUEST['mode'], $pid, $_REQUEST['enc'], $_REQUEST['frmdir'], 
	$_REQUEST['module'], $_REQUEST['user'], $_REQUEST['dt']);

$ret = '';
$row = sqlQuery('SELECT * FROM users WHERE id=?',array($_REQUEST['user']));
if(!isset($row{'suffix'})) $row{'suffix'} = '';
if($order == 'last') {
 	$ret = $row{'lname'};
	if($row{'suffix'}) $ret .= ' ' . $row{'suffix'};
	$ret .= ', ' . $row{'fname'} . ' ' . $row{'mname'};
} else if($order == 'first') {
	$ret = $row{'fname'};
	if($row{'mname'}) $ret .=  ' ' . $row{'mname'};
	$ret .= ' ' . $row{'lname'};
	if($row{'suffix'}) $ret .= ', ' . $row{'suffix'};
}
if($ret) $ret = ' [ ' . $ret . ' ]';
if($mode == 'delete') $ret = '';

echo $ret;
exit;

?>
