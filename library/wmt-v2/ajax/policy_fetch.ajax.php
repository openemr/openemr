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

// THIS IS DESIGNED TO FETCH ONLY VALUES FROM ONE UNIQUE ROW

// SANITIZE ALL ESCAPES
$sanitize_all_escapes = true;

// STOP FAKE REGISTER GLOBALS
$fake_register_globals = false;

require_once("../../../interface/globals.php");
require_once("$srcdir/auth.inc");

if(!isset($_POST{'policy_id'})) $_POST['policy_id'] = '';
if(!isset($_POST{'pid'})) $_POST['pid'] = '';

$policy = strip_tags($_POST['policy_id']);
$pid = strip_tags($_POST['pid']);

if(!$policy || !$pid) {
	echo 'Some required data was missing, no action taken';
	exit;
}

$query = 'SELECT ins.*, ic.`name`, ic.`attn`, ic.`ins_type_code`, ' .
	'ad.`line1`, ad.`line2`, '.
	'ad.`city`, ad.`state`, ad.`zip`, ad.`plus_four`, ph.`area_code`, '.
	'ph.`prefix`, ph.`number` FROM insurance_data AS ins '.
	'LEFT JOIN insurance_companies AS ic ON ins.`provider` = ic.`id` '.
	'LEFT JOIN phone_numbers AS ph ON '.
	'(ic.`id` = ph.`foreign_id` AND ph.`type` = 2) '.
	'LEFT JOIN addresses AS ad ON (ic.`id` = ad.`foreign_id`) '.
	'WHERE ins.`pid` = ? AND ins.`id` = ?';
		
$res = sqlQuery($query, array($pid, $policy));

if(isset($res{'subscriber_lname'})) {
	$res['subscriber_full_name'] = $res{'subscriber_lname'} . ', ' .
		$res{'subscriber_fname'} . ' ' . $res{'subscriber_mname'};
}

if(isset($res{'provider'})) {
	$address_str = array();
	$icobj = new InsuranceCompany($res['provider']);
	$adobj = $icobj->get_address();

	if(!empty($adobj->get_line1())) {
    	$address_str[] = htmlspecialchars($adobj->get_line1(), ENT_NOQUOTES);
  	}
    $address_str[] = htmlspecialchars($adobj->get_city() . ', ' . $adobj->get_state() . ' ' . $adobj->get_zip(), ENT_NOQUOTES);

   $address_str[] = htmlspecialchars(xl('PH'), ENT_NOQUOTES) . ': ' . htmlspecialchars($icobj->get_phone(), ENT_NOQUOTES);
   $res['address_str'] = !empty($address_str) ? implode("\n", $address_str) : "";
}

//unset uuid
if(isset($res['uuid'])) unset($res['uuid']);

$result = json_encode($res);
echo $result;

exit;

?>
