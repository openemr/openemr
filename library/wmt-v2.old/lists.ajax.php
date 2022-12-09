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

require_once('../../interface/globals.php');
require_once($GLOBALS['srcdir'].'/auth.inc');
require_once($GLOBALS['srcdir'].'/billing.inc');
require_once($GLOBALS['srcdir'].'/wmt-v2/wmtstandard.inc');
include_once($GLOBALS['fileroot'].'/custom/code_types.inc.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/procedures.inc');
include_once($GLOBALS['srcdir'].'/wmt-v2/billing_tools.inc');

global $frmdir, $id, $encounter, $pid, $target_container, $wrap_mode, $wmode;

$encounter = isset($_REQUEST['enc']) ? strip_tags($_REQUEST['enc']) : '';
$pid= isset($_REQUEST['pid']) ? strip_tags($_REQUEST['pid']) : '';
$frmdir = isset($_REQUEST['frmdir']) ? strip_tags($_REQUEST['frmdir']) : '';
$id = isset($_REQUEST['id']) ? strip_tags($_REQUEST['id']) : '';
$type = isset($_REQUEST['type']) ? strip_tags($_REQUEST['type']) : '';
$action = isset($_REQUEST['action']) ? strip_tags($_REQUEST['action']) : '';
$title = isset($_REQUEST['title']) ? strip_tags($_REQUEST['title']) : '';
$target_container = isset($_REQUEST['div']) ? strip_tags($_REQUEST['div']) : '';
$prefix = isset($_REQUEST['prefix']) ? strip_tags($_REQUEST['prefix']) : '';
$wrap_mode = isset($_REQUEST['wrap']) ? strip_tags($_REQUEST['wrap']) : 'new';
$wmode=isset($_REQUEST['wmode']) ? strip_tags($_REQUEST['wmode']) : 'encounter';
$code = isset($_REQUEST['code']) ? strip_tags($_REQUEST['code']) : '';
$ctype = isset($_REQUEST['ctype']) ? strip_tags($_REQUEST['ctype']) : '';
$mod = isset($_REQUEST['cmod']) ? strip_tags($_REQUEST['cmod']) : '';
$cdesc = isset($_REQUEST['cdesc']) ? strip_tags($_REQUEST['cdesc']) : '';
$cplan = isset($_REQUEST['cplan']) ? strip_tags($_REQUEST['cplan']) : '';
$cseq = isset($_REQUEST['cseq']) ? strip_tags($_REQUEST['cseq']) : '';
$item = isset($_REQUEST['item']) ? strip_tags($_REQUEST['item']) : '';
$suff = isset($_REQUEST['remain']) ? strip_tags($_REQUEST['remain']) : '';
$on_fee = isset($_REQUEST['on_fee']) ? strip_tags($_REQUEST['on_fee']) : '';
$justify = isset($_REQUEST['justify']) ? strip_tags($_REQUEST['justify']) : -1;
$level =isset($_REQUEST['level']) ? strip_tags($_REQUEST['level']) : 'standard';

if($action == 'all' || $action == 'current' || $action == 'encounter') {
	$wmode = $action;
}
// echo "Action ($action)  Mode [$wmode]<br>\n";

$s_dt = isset($_REQUEST['start_dt']) ? strip_tags($_REQUEST['start_dt']) : '';
$e_dt = isset($_REQUEST['end_dt']) ? strip_tags($_REQUEST['end_dt']) : '';

function ApplyTemplate($type, $data) {
	global $frmdir, $id, $encounter, $pid, $target_container, $wrap_mode, $wmode;
	// echo "Template  Mode [$wmode]<br>\n";
	if(!$type) return 'error';
	if($type == 'medical_problem') {
		$diag = $data;
		$dt['tmp_diag_window_mode'] = $wmode;
		$module = $GLOBALS['srcdir'].'/wmt-v2/diagnosis.inc.php';		
	}
	if($type == 'procedures') {
		$proc_data = $data;
		$dt['tmp_proc_window_mode'] = $wmode;
		$module = $GLOBALS['srcdir'].'/wmt-v2/procedures.inc.php';		
	}
	ob_start();
	include($module);		
	$table = ob_get_contents();
	ob_end_clean();
	return $table;
}

if ($action == 'unlink') {
	if(!$pid || !$id || !$encounter || !$type) {
		echo 'error';
		exit;
	}
	UnlinkListEntry($pid, $id, $encounter, $type);
	echo 'success';
	exit;
}

if ($action == 'link') {
	if(!$pid || !$id || !$encounter || !$type) {
		echo 'error';
		exit;
	}
	LinkListEntry($pid, $id, $encounter, $type);
	echo 'success';
	exit;
}

if($action == 'del') {
	if(!$pid || !$encounter || !$type) {
		echo 'error';
		exit;
	}
	if($type == 'procedures') {
		if(!$ctype || !$code) {
			echo 'error';
			exit;
		}
		DeleteProcedure($pid, $encounter, $ctype, $code, $mod);
		// sleep(2);
		// error_log("Delete Done $pid, $encounter, $ctype, $code, $mod)");
	}
}

if($action == 'update') {
	if(!$pid || !$encounter || !$type) {
		echo 'error';
		exit;
	}
	if($type == 'medical_problem') {
		UpdateDiagnosis($pid,$item,$code,$cdesc,$cplan,$s_dt,$e_dt,$ctype,$suff,$cseq,$encounter);
	} else if($type == 'procedures') {
		$dr = isset($_REQUEST['dr']) ? strip_tags($_REQUEST['dr']) : '-1';
		AddOrUpdatePlan($pid,$encounter,$ctype,$code,$mod,$cplan,$cdesc);
		if($on_fee) {
			if($bill_id = billingExists($ctype, $code, $pid, $encounter, $mod)) {
				$line = array('units' => $units, 'mod' => $mod, 'type' => $ctype);
				if($justify != -1) $line['justify'] = $_REQUEST['justify'];
				updateBillingItem($bill_id, -1, $line);
			} else {
				if($cdesc == '') $cdesc = lookup_code_descriptions($ctype.':'.$code);
				$fee = getFee($ctype, $code, $level, $mod);
				if($justify != -1) { 
					$bill_id = addBilling($encounter, $ctype, $code, $cdesc, $pid, 1, 
						$dr, $mod, $units, $fee, '', $justify);
				} else {
					$bill_id = addBilling($encounter, $ctype, $code, $cdesc, $pid, 1, 
						$dr, $mod, $units, $fee);
				}
			}	
		}
	}
	if(strtolower($_REQUEST['suppress']) == 'yes') {
		echo 'success';
		exit;
	}
}

if($action == 'add') {
	if(!$pid || !$encounter || !$type) {
		echo 'error';
		exit;
	}
	if($type == 'medical_problem') {
		AddDiagnosis($pid,$encounter,$ctype,$code,$cdesc,$cplan,$s_dt,$e_dt,$cseq);
		if(!isset($_REQUEST['suppress'])) $_REQUEST['suppress'] = '';
		if(strtolower($_REQUEST['suppress']) == 'yes') {
			echo 'success';
			exit;
		}
	} else if($type == 'procedures') {
		// ADD HAS ALREADY BEEN CALLED SO WE SIMPLY REFRESH
	}
	// echo "Add Action Finished and reload is not suppressed<br>\n";
}

$list = array();
if($type == 'medical_problem') {
	$list = GetProblemsWithDiags($pid, $action, $encounter);
} else if($type == 'procedures') {
	$bill_flds = sqlListFields('billing');
	$list = GetEncounterProcedures($pid, $encounter, 'billing');
	$extra = GetEncounterProcedures($pid, $encounter, 'lists');
	foreach($extra as $x) {
		foreach($bill_flds as $fld) {
			$x[$fld] = '';
		}
		$x['code_type'] = $x['stype'];
		$x['code'] = $x['scode'];
		$x['modifier'] = $x['injury_part'];
		$list[] = $x;
	}
} else {
	$list = GetList($pid, $type, $encounter, $s_dt, $action);
}
$output = ApplyTemplate($type, $list);
echo $output;

exit;

?>
