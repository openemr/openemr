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
require_once("../../custom/code_types.inc.php");

// Get request type
$type = isset($_REQUEST['type']) ? strip_tags($_REQUEST['type']) : '';
$code = isset($_REQUEST['code']) ? strip_tags($_REQUEST['code']) : '';
$title = isset($_REQUEST['title']) ? strip_tags($_REQUEST['title']) : '';
$group = isset($_REQUEST['group']) ? strip_tags($_REQUEST['group']) : '';
$prnt = '';

// GET THE LAST ONE AND ADD 10          
// THIS JUST SEEMS LIKE A WASTED STATEMENT - NO ONE USES SEQUENCE ANYWAY
/****
$sql = "SELECT seq FROM wmt_diag_fav WHERE code_type=? ".
	"AND list_user=? AND grp=? ORDER BY seq DESC LIMIT 1";
$binds = array($type, $_SESSION['authUser'], $group); 
$frow = sqlQuery($sql,$binds);
if(!isset($frow{'seq'})) $frow{'seq'} = 0;
$seq = $frow{'seq'} + 10;
****/

$seq = 0;

if(!$title) {
	$title = lookup_code_descriptions($type.':'.$code);
}

$sql = 'INSERT INTO wmt_diag_fav (`date`, `user`, `code_type`, `code`, ' .
	'`seq`, `title`, `list_user`, `global_list`, `grp`) VALUES (NOW(), ' .
	'?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE `user`=VALUES(`user`)';
$binds = array($_SESSION['authUser'], $type, $code, $seq, $title,
		$_SESSION['authUser'], '0', $group);
$prnt = sqlInsert($sql, $binds);
$prnt = "Added Favorite ID ($prnt)";	

echo $prnt;
exit;

?>
