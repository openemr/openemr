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
require_once($GLOBALS['srcdir'].'/amc.php');

$pid = $_SESSION['pid'];
if(isset($_REQUEST['pid'])) $pid = strip_tags($_REQUEST['pid']);
if(!isset($_REQUEST['amc_id'])) $_REQUEST['amc_id'] = '';
if(!isset($_REQUEST['map_category'])) $_REQUEST['map_category'] = '';
if(!isset($_REQUEST['map_id'])) $_REQUEST['map_id'] = '';

if(!$_REQUEST['amc_id'] && !$pid) return 'Nothing to do';

amcAdd($_REQUEST['amc_id'], TRUE, $pid, $_REQUEST['map_category'], 
	$_REQUEST['map_id']);

echo 'AMC Complete';

exit;

?>
