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
// require_once("$srcdir/formatting.inc.php");

// Get request type
$type = isset($_REQUEST['type']) ? strip_tags($_REQUEST['type']) : '';
$pid = isset($_REQUEST['pid']) ? strip_tags($_REQUEST['pid']) : '';


if ($type == 'pid') {
	$fres = sqlStatement("SELECT date, encounter FROM form_encounter WHERE ".
                        "pid = ? ORDER BY encounter DESC", array($pid));

	$encList = $dosList = '<option value="">-- ALL --</option>';
	while($frow = sqlFetchArray($fres)) {
		$encList .= '<option value="'.$frow{'encounter'}.'">'.
			htmlspecialchars($frow{'encounter'}, ENT_QUOTES, '', FALSE).'</option>';
		$dosList .= '<option value="'.substr($frow{'encounter'},0,10).'">'.
			substr($frow{'date'},0,10).'</option>';
	}
	echo $encList . '|~|~' . $dosList;
	// echo $pid;
}
exit;
?>
