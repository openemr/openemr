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

$out = '';

$fetch = "SELECT * FROM openemr_postcalendar_events where pc_eid=?";
$update = "INSERT INTO openemr_postcalendar_fee_sheets (pf_date, pf_user, ".
	"pf_pid, pf_aid, pf_eventDate, pf_startTime, pf_printed) VALUES ".
	"(NOW(), ?, ?, ?, ?, ?, '1') ON DUPLICATE KEY UPDATE pf_date = NOW(), ".
	"pf_user = VALUES(pf_user), pf_printed = (pf_printed + 1)";
foreach($_REQUEST['id'] as $key => $val) {
	$out .= '~'.$val;
	$ev = array();
	if($val) $ev = sqlQuery($fetch, array($val));
	if(!isset($ev{'pc_pid'})) $ev{'pc_pid'} = '';
	if($ev{'pc_pid'}) {
		sqlStatement($update, array($_SESSION['authUser'], $ev{'pc_pid'}, 
			$ev{'pc_aid'}, $ev{'pc_eventDate'}, $ev{'pc_startTime'}));
	}
}
echo $out;

exit;

?>
