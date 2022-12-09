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
require_once($GLOBALS['srcdir'].'/wmt-v2/clinical_rules.inc');

$pid = $_SESSION['pid'];
if(isset($_REQUEST['pid'])) $pid = strip_tags($_REQUEST['pid']);
if(!isset($_REQUEST['category'])) $_REQUEST['category'] = '';
if(!isset($_REQUEST['item'])) $_REQUEST['item'] = '';
if(!isset($_REQUEST['complete'])) $_REQUEST['complete'] = '';
if(!isset($_REQUEST['date'])) $_REQUEST['date'] = '';
if(!isset($_REQUEST['result'])) $_REQUEST['result'] = '';

if(!$_REQUEST['item'] || !$pid) {
	echo 'Nothing to do';
	exit;
}

$tst = meetClinicalTarget($pid, $_REQUEST); 

if($tst === false) {
	echo 'No item, Nothing do do';
} else if($tst > 0) {
	echo 'Rule Added';
} else {
	echo 'Skipped - newer rule date exists';
}

exit;

?>
