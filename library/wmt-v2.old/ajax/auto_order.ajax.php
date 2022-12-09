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
require_once($GLOBALS['srcdir'].'/wmt-v2/wmtstandard.inc');
require_once($GLOBALS['srcdir'].'/wmt-v2/rto.class.php');

$out = '';
$rto_options = array();
$rto_options['rto_date'] = date('Y-m-d');
if(!isset($_POST['list'])) $_POST['list'] = '';
if(!isset($_POST['id'])) $_POST['id'] = '';
if(!isset($_POST['title'])) $_POST['title'] = '';
if($_POST['list'] == '' || $_POST['id'] == '') {
	$out = 'No LIST or No OPTION ID - Aborted';
	echo $out;
	exit;
}
$action = sqlQuery('SELECT * FROM list_options WHERE list_id = ? AND '.
	'option_id = ?',array(strip_tags($_POST['list']),strip_tags($_POST['id'])) );
if($rto = strstr($action['option_id'],'::')) $action['option_id'] = substr($rto,2);
$rto_options['rto_action'] = $action['option_id'];
// FIRST LOAD AUTO-CREATE SETTINGS FROM THE ACTION
$default = sqlQuery('SELECT * FROM list_options WHERE list_id=? AND '.
		'option_id=?', array('RTO_Action',$rto_options['rto_action']));
if(!isset($default['mapping'])) $default['mapping'] = '';
$rto_options['window'] = $default['mapping'];
if(!isset($default['notes'])) $default['notes'] = '';
$settings = explode(';', $default['notes']);
foreach($settings as $setting) {
	if(strpos($setting, ':') !== false) {
		list($label, $val) = explode(':', $setting);
		$rto_options[$label] = $val;
	}
}

// HERE WE CAN SET OVERRIDES BASED ON FIELD ROW
if(!isset($action['mapping'])) $action['mapping'] = '';
if($action['mapping'] != '') $rto_options['window'] = $action['mapping'];
if(!isset($action['notes'])) $action['notes'] = '';
if($action['notes'] != '') {
	$settings = explode(';', $action['notes']);
	foreach($settings as $setting) {
		if(strpos($setting, ':') !== false) {
			list($label, $val) = explode(':',$setting);
			$rto_options[$label] = $val;
		}
	}
}

if($_POST['title'] != '') {
	$rto_options['rto_notes'] = 'This task auto-created by the pick link from '.
		strip_tags($_POST['title']).' on '.date('Y-m-d');
}
$rto_options['rto_date'] = date('Y-m-d');
$order = wmtRTOData::findOrCreateRTO($pid, $rto_options, true);
echo 'Created Order : [';
print_r($order);
echo ']';
echo "<br>\n";

exit;

?>
