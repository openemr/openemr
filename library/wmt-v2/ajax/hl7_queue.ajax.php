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

$sanitize_all_escapes = true;

$fake_register_globals = false;

require_once("../../../interface/globals.php");
require_once("$srcdir/auth.inc");

if(!isset($_REQUEST['hl7_msg_group'])) $_REQUEST['hl7_msg_group'] = '';
if(!isset($_REQUEST['hl7_msg_type'])) $_REQUEST['hl7_msg_type'] = '';
if(!isset($_REQUEST['oemr_table'])) $_REQUEST['oemr_table'] = '';
if(!isset($_REQUEST['oemr_ref_id'])) $_REQUEST['oemr_ref_id'] = '';
if(!isset($_REQUEST['flag'])) $_REQUEST['flag'] = NULL;
if(!isset($_REQUEST['target'])) $_REQUEST['target'] = '';
if(!isset($_REQUEST['log_table'])) $_REQUEST['log_table'] = NULL;
if(!isset($_REQUEST['log_field'])) $_REQUEST['log_field'] = NULL;
if(!isset($_REQUEST['processed'])) $_REQUEST['processed'] = NULL;

if(!$_REQUEST['hl7_msg_group'] && !$_REQUEST['hl7_msg_type'] &&
		!$_REQUEST['oemr_table'] && !$_REQUEST['oemr_ref_id']) {
	echo 'Nothing to Do.';
	exit;
}

$binds = array( $_REQUEST['hl7_msg_group'], 
								$_REQUEST['hl7_msg_type'],
								$_REQUEST['oemr_table'], 
								$_REQUEST['oemr_ref_id'], 
								$_REQUEST['target']);

$sql  = 'INSERT INTO hl7_queue (hl7_msg_group, hl7_msg_type, oemr_table, ' .
		'oemr_ref_id, target';
$addl = '';

if($_REQUEST['flag'] !== NULL) {
	$sql .= ', flag';
	$addl .= ', ?';
	$binds[] = $_REQUEST['flag'];
}

if($_REQUEST['log_table'] !== NULL) {
	$sql .= ', log_table';
	$addl .= ', ?';
	$binds[] = $_REQUEST['log_table'];
}

if($_REQUEST['log_field'] !== NULL) {
	$sql .= ', log_field';
	$addl .= ', ?';
	$binds[] = $_REQUEST['log_field'];
}

if($_REQUEST['processed'] !== NULL) {
	$sql .= ', processed';
	$addl .= ', ?';
	$binds[] = $_REQUEST['processed'];
}

$sql .= ') VALUES (?, ?, ?, ?, ?' . $addl . ') ON DUPLICATE KEY '.
	'UPDATE `target` = VALUES (`target`)';

sqlStatement($sql, $binds);

echo 'Queue Updated';
exit;

?>
