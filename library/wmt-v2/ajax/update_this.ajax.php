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

$_update = TRUE;
if(!isset($_POST{'table'})) $_update = FALSE;
if(!isset($_POST{'columns'})) $_update = FALSE;
if(!isset($_POST{'keys'})) $_update = FALSE;

if(!$_update) {
	echo 'Some required data was missing, no action taken';
	exit;
}

$table = strip_tags($_POST['table']);
$data_pairs = explode('^|', $_POST['columns']);
$key_pairs = explode('^|', $_POST['keys']);

// BUILD AN SQL STATEMENT BASED ON THE COLUMNS AND KEYS
$sql = "UPDATE `$table` SET ";
$binds = array();
$_first = TRUE;
foreach($data_pairs as $pair) {
	list($column, $val) = explode('^~', $pair);
	if(!$_first) $sql .= ', ';
	$sql .= "`$column` = ?";
	$binds[] = $val;
}
$sql .= ' WHERE ';
$_first = TRUE;
foreach($key_pairs as $pair) {
	list($column, $val) = explode('^~', $pair);
	if(!$_first) $sql .= ' AND ';
	$sql .= "`$column`  = ?";
	$binds[] = $val;
}

$out = "Statement: [$sql] With Bind Array: ";
foreach($binds as $key => $val) {
	$out .= "($key, $val), ";
}

sqlStatement($sql, $binds);
echo $out;

exit;

?>
