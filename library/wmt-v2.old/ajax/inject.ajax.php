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

require_once("../../../interface/globals.php");
require_once("$srcdir/auth.inc");

$id = isset($_REQUEST['id']) ? strip_tags($_REQUEST['id']) : '';
if($id == '') {
	echo '~%';
	exit;
}
$ndc = $uom = '';

$frow = sqlQuery("SELECT ij1_cpt, ij1_ndc, ij1_dose_unit FROM form_inject1 ".
	"WHERE ij1_cpt=? ORDER BY date DESC LIMIT 1", array($id));
// if(!isset($frow{'ij1_cpt'})) $frow{'ij1_cpt'} = '';
$ndc = $frow{'ij1_ndc'};
$uom = $frow{'ij1_dose_unit'};

$frow = sqlQuery("SELECT * FROM list_options WHERE " .
         "list_id = ? AND option_id = ?", array('Injection_CPT', $id));
$flds = sqlListFields('list_options');
if(in_array('codes',$flds)) {
	if($frow['codes']) {
 		$code_res = trim($frow['codes']);
	} else {
		$code_res = trim($frow['notes']);
	}
} else {
	$code_res = trim($frow['notes']);
}
$codes = explode(';', $code_res);
$bill_code = '';
foreach($codes as $item) {
	$type = $code = '';
	if(strpos($item, ':') !== false) list($type, $code) = explode(':', $item);
	if(strtoupper($type) == 'HCPCS') $bill_code = $code;
}

if($bill_code) {
	$frow = sqlQuery("SELECT code_type, code, ndc_info FROM billing WHERE ".
		"code_type = 'HCPCS' AND code = ? AND ndc_info != '' AND " .
		"ndc_info IS NOT NULL ORDER BY date DESC", array($bill_code));
	if($frow{'ndc_info'} != '') $ndc = substr($frow{'ndc_info'},2,13);
}

echo $ndc . '~%' . $uom;
exit;

?>
