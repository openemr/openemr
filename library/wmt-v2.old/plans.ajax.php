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
require_once("$srcdir/api.inc");

// Get request type
$type = isset($_REQUEST['type']) ? strip_tags($_REQUEST['type']) : '';
$code = isset($_REQUEST['code']) ? strip_tags($_REQUEST['code']) : '';
$plan = isset($_REQUEST['plan']) ? strip_tags($_REQUEST['plan']) : '';
$prnt = '';

if(!empty($code) && !empty($plan) && !empty($plan)) {
	// MAKE SURE SOME GENIUS END USER IS NOT JUST CLICKING AND CLICKING
	$sql = "SELECT id, date FROM wmt_plan_fav WHERE code_type=? AND ".
			"code=? AND plan=? AND list_user=?";
	$binds = array($type, $code, $plan, $_SESSION['authUser']);
	$frow = sqlQuery($sql, $binds);
	if($frow{'id'}) {
		return 'Plan Already Exists....';
		exit;
	}

	// GET THE LAST ONE AND ADD 10          
	$sql = "SELECT seq FROM wmt_plan_fav WHERE code_type=? AND code=? ".
		"AND (list_user=? OR global_list=1) ORDER BY seq DESC LIMIT 1";
	$binds = array($type, $code, $_SESSION['authUser']); 
	$frow = sqlQuery($sql,$binds);
	if(!isset($frow{'seq'})) $frow{'seq'} = 0;
	$seq = $frow{'seq'} + 10;

  $sql = "INSERT INTO wmt_plan_fav (date, user, code_type, code, plan, ".
		"title, seq, notes, list_user, global_list) VALUES ".
		"(NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?)";
	$binds = array($_SESSION['authUser'], $type, $code, $plan, '', $seq, '',
			$_SESSION['authUser'], '0');
  $prnt = sqlInsert($sql, $binds);
	$prnt = "Added Plan ID ($prnt)";	
} else {
	$prnt = 'Nothing To Do...';
}

echo $prnt;
exit;

?>
