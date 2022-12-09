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
require_once($GLOBALS['fileroot'].'/custom/code_types.inc.php');
require_once("$srcdir/auth.inc");
require_once("$srcdir/billing.inc");
require_once("$srcdir/wmt-v2/billing_tools.inc");
require_once("$srcdir/wmt-v2/printvisit.class.php");
require_once("$srcdir/wmt-v2/wmtSettings.inc");

$diag_code = array();
$diag_desc = array();
$bill_code = checkSettingMode('wmt::auto_bill_cpt','','food_handler');
$diag_code[1] = checkSettingMode('wmt::auto_bill_diag_1','','food_handler');
$cpt_desc = lookup_code_descriptions('CPT4:' . $bill_code);
$diag_desc[1] = lookup_code_descriptions('ICD10:' . $diag_code[1]);

$out = '';
foreach($_REQUEST['id'] as $key => $val) {
	if($val) 
		$ref = sqlQuery('SELECT * FROM form_food_handler WHERE id=?', array($val));
	if(!isset($ref{'id'})) $ref{'id'} = '';
	if($ref{'id'}) {
		sqlStatement('UPDATE form_food_handler SET referral = (referral + 1), '.
			'result_printed_dt = NOW(), result_printed_by = ? WHERE id=?',
			array($_SESSION['authUserID'], $ref{'id'}));
		$out .= $ref{'id'} . '~';

		$visit = wmtPrintVisit::getEncounterByForm($ref{'id'}, 'food_handler');
		if(!billingExists('CPT4', $bill_code, $ref{'pid'}, $visit->encounter_id)) {
			$pat = sqlQuery('SELECT pid, pricelevel FROM patient_data WHERE pid = ?',
				array($ref{'pid'}));
			$fee = getFee('CPT4', $bill_code, $pat{'privelevel'});
			addBilling($visit->encounter_id, 'ICD10', $diag_code[1], $diag_desc[1], 
				$ref{'pid'}, $_SESSION['userauthorized'], $visit->provider_id, '', 1);
			addBilling($visit->encounter_id, 'CPT4', $bill_code, $cpt_desc, 
				$ref{'pid'}, $_SESSION['userauthorized'], $visit->provider_id, '', 1, 
				$fee, $diag_code[1].':');
		}
	}
}
echo $out;

exit;

?>
