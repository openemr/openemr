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

$enc = isset($_REQUEST['enc']) ? strip_tags($_REQUEST['enc']) : '';
$ret = 'No Data';

if($enc != '') {
	$f = sqlQuery("SELECT * FROM form_encounter WHERE encounter=?",array($enc));
	$sql = 'SELECT i.name AS ins FROM openemr_postcalendar_events AS oe '.
		'LEFT JOIN insurance_companies AS i ON oe.pc_insurance = i.id WHERE '.
		'oe.pc_pid = ? AND oe.pc_eventDate = ? AND oe.pc_facility = ? AND '.
		'oe.pc_aid = ?';
	$binds = array($f{'pid'}, substr($f{'date'},0,10), $f{'facility_id'}, 
		$f{'provider_id'});
	$r = sqlQuery($sql, $binds);
	if(!isset($r{'ins'})) $r{'ins'} = 'No Insurance Specified';
	$ret = $r{'ins'};
}

echo $ret;
exit;

?>
