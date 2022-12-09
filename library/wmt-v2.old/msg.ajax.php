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

require_once("../../interface/globals.php");
require_once("$srcdir/auth.inc");

// Get request type
$type = isset($_REQUEST['type']) ? strip_tags($_REQUEST['type']) : '';
$id = isset($_REQUEST['id']) ? strip_tags($_REQUEST['id']) : '';
$order = isset($_REQUEST['order']) ? strip_tags($_REQUEST['order']) : 'u.lname';
$prnt = '';

if ($type == 'status') {
	if(!$id) {
		echo '';
	} else {
		$sql = 'SELECT * FROM msg_status WHERE user_id=?';
		$frow = sqlQuery($sql, array($id));
		$prnt = $frow{'status'}.'~~'.$frow{'until'}.'~~'.$frow{'user_msg'};
	}
}

if ($type == 'members') {
	$sql = 'SELECT link.user_id, u.lname, u.fname, u.mname FROM msg_group_link '.
	'AS link LEFT JOIN users AS u ON (link.user_id = u.id) WHERE '.
	'link.group_id=? ORDER BY '.$order;
	$fres = sqlStatement($sql, array($id));
	$users = '';
	while($frow = sqlFetchArray($fres)) {
		if($users != '') $users .= ', ';
		$users .= $frow{'fname'}.' '.$frow{'lname'};
		if($prnt != '') $prnt .= '~|';
		$prnt .= $frow{'user_id'};
	}
	$prnt = $users . '~~' . $prnt;
}

if ($type == 'group_disp' || $type == 'status') {
	$sql = 'SELECT link.group_id, list.title FROM msg_group_link AS link '.
	'LEFT JOIN list_options AS list ON (link.group_id = list.option_id AND '.
	'list.list_id = "Messaging_Groups") WHERE link.user_id=?';
	$fres = sqlStatement($sql, array($id));
	if($prnt != '') $prnt .= '~~';
	$res = '';
	while($frow = sqlFetchArray($fres)) {
		if($res != '') $res .= ', ';
		$res .= $frow{'title'};
	}
	$prnt .= $res;
}

if ($type == 'group_chosen' || $type == 'status') {
	$sql = 'SELECT msg_group_link.group_id FROM msg_group_link WHERE user_id=?';
	$fres = sqlStatement($sql, array($id));
	if($prnt != '') $prnt .= '~~';
	$res = '';
	while($frow = sqlFetchArray($fres)) {
		if($res != '') $res .= '~|';
		$res .= $frow{'group_id'};
	}
	$prnt .= $res;
}
echo $prnt;

exit;

?>
