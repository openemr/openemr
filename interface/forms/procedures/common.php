<?php
/** **************************************************************************
 *	procedures/common.php
 *
 *	Copyright (c)2020 - Williams Medical Technology, Inc.
 *
 *	This file contains the standard screen processing used for both the "new" 
 *	and "view" processes for generic treatments.
 *
 *	This program is free software: you can redistribute it and/or modify it 
 *	under the terms of the GNU General Public License as published by the Free 
 *	Software Foundation, either version 3 of the License, or (at your option) 
 *	any later version.
 *
 *	This program is distributed in the hope that it will be useful, but WITHOUT 
 *	ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or 
 *	FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for 
 *	more details.
 *
 *	You should have received a copy of the GNU General Public License along with 
 *	this program.  If not, see <http://www.gnu.org/licenses/>.	This program is 
 *	free software; you can redistribute it and/or modify it under the terms of 
 *	the GNU Library General Public License as published by the Free Software 
 *	Foundation; either version 2 of the License, or (at your option) any 
 *	later version.
 *
 *  @package Standard OEMR
 *  @subpackage procedures
 *  @version 1.0
 *  @copyright Williams Medical Technologies, Inc.
 *  @author Rich Genandt <rgenandt@gmail.com>
 * 
 *************************************************************************** */
require_once("../../globals.php");
require_once("{$GLOBALS['srcdir']}/options.inc.php");
require_once("{$GLOBALS['srcdir']}/api.inc");
require_once("{$GLOBALS['srcdir']}/forms.inc");
require_once("{$GLOBALS['srcdir']}/translation.inc.php");
require_once("{$GLOBALS['srcdir']}/formatting.inc.php");
//require_once("{$GLOBALS['srcdir']}/billing.inc");
require_once("{$GLOBALS['fileroot']}/custom/code_types.inc.php");
include_once("{$GLOBALS['srcdir']}/wmt-v2/wmtstandard.inc");
include_once("{$GLOBALS['srcdir']}/wmt-v2/wmtpatient.class.php");
include_once("{$GLOBALS['srcdir']}/wmt-v2/printvisit.class.php");
include_once("{$GLOBALS['srcdir']}/wmt-v2/procedures.inc");
include_once("{$GLOBALS['srcdir']}/wmt-v2/favorites.inc");
include_once("{$GLOBALS['srcdir']}/wmt-v2/billing_tools.inc");
$frmdir = 'procedures';
$form_title = 'Procedures';

use OpenEMR\Core\Header;
use OpenEMR\Billing\BillingUtilities;

// INITIALIZE FORM DEFAULTS
$continue = FALSE;
$id = '';
if(isset($_GET['id'])) $id = strip_tags($_GET['id']);
if(isset($_SESSION['pid'])) $pid = $_SESSION['pid'];
if(isset($_GET['pid'])) $pid = strip_tags($_GET['pid']);
if(!$pid) ReportMissingPID();
$encounter = $_SESSION['encounter'];
if(isset($_GET['enc'])) $encounter = strip_tags($_GET['enc']);
if(isset($_GET['continue'])) $continue = strip_tags($_GET['continue']);
$cancel_url = $GLOBALS['rootdir'].'/patient_file/encounter/encounter_top.php';
$popup = true;
$popmode = 'on';
if(isset($_GET['pop'])) $popmode = strtolower(strip_tags($_GET['pop']));
global $user_lookup_order;
$user_lookup_order = strtolower(checkSettingMode('wmt::msg_user_order'));
// THESE CAN PROBABLY BE DELETED
$use_justify = checkSettingMode('wmt::use_justify','',$frmdir);
if(!isset($GLOBALS['wmt::include_billing_diags'])) 
				$GLOBALS['wmt::include_billing_diags'] = '';
// STOP HERE

if($popmode == 'on') {
	$popup = TRUE;
	$cancel_url = 'javascript:window.close();';
} else if($popmode == 'db') {
	$db = TRUE;
	$cancel_url = $GLOBALS['rootdir'].'/patient_file/summary/demographics.php';
} else {
	$popmode = 'off';
}

$form_table = '';
$save_url = $GLOBALS['rootdir'].'/forms/'.$frmdir.'/new.php?mode=save&pid=' .
	$pid . '&pop=' . $popmode;
$abort_url = $GLOBALS['rootdir'].'/patient_file/summary/demographics.php';
$base_action = $GLOBALS['rootdir'].'/forms/'.$frmdir.'/new.php';
$base_action .= '?pop='.$popmode.'&pid='.$pid;
if(!isset($_POST['tmp_proc_window_mode'])) $_POST['tmp_proc_window_mode'] = '';
if($_POST['tmp_proc_window_mode'] == 'allproc') $_GET['allproc'] = 'allproc';
if(!isset($_GET['allproc'])) {
	if($encounter) $base_action .= '&enc='.$encounter;
}
$wrap_mode = 'new';
$mode = 'new';
if(isset($_GET['mode'])) $mode = strip_tags($_GET['mode']);
$dt = array();

// HERE WE COULD CHECK TO AUTO-CREATE AN ENCOUNTER IF THIS CAN 
// BE CALLED FROM SOMEWHERE OTHER THAN A FORM
$visit = wmtPrintVisit::getEncounter($encounter);
$patient = wmtPatData::getPidPatient($pid);

// DELETE - MOVED TO MODULE
/*
$diag = GetProblemsWithDiags($pid, 'enc', $encounter, 'ICD', true);
if($GLOBALS['wmt::include_billing_diags']) {
	$extra = getBillingCodes($pid, $encounter, 'ct_diag = 1');
	foreach($extra as $x) {
		$used = false;
		foreach($diag as $l) {
			if($l['encounter'] != $encounter) continue;
			if($l['diagnosis'] == $x['code_type'].':'.$x['code']) $used = true;
		}
		if(!$used) {
			if(!isset($x['fee_sheet_slot'])) $x['fee_sheet_slot'] = '';
			$new = array('id' => -1,
				'seq' => $x['fee_sheet_slot'],
				'begdate' => substr($x['date'],0,10),
				'encounter' => $x['encounter'],
				'diagnosis' => $x['code_type'] . ':' . $x['code']);
			$diag[] = $new;
		}
	}
}
*/
// STOP HERE

foreach($patient as $key => $val) {
	$dt['pat_'.$key] = $val;
}

// DELETE - MOVED TO MODULE
/*
$autoj = '';
if($max_to_justify = checkSettingMode('wmt::auto_justify','',$frmdir)) {
	$cnt = 0;
	foreach($diag as $prev) {
		$codes = explode(';',$prev['diagnosis']);
		list($type, $code) = explode(':', $codes[0]);
		if($type && $code) {
			if($autoj) $autoj .= ',';
			$autoj .= $type .'|' . $code;
			$cnt++;
		}
		if($cnt == $max_to_justify) break;
	}
}
*/
// STOP HERE

foreach($_POST as $key => $val) {
	if(is_string($val)) $val = trim($val);
	$dt[$key] = $val;
	if(strpos($key, '_date') !== false) $dt[$key] = DateToYYYYMMDD($val);
}
$load = '';
$justinit = "var f = document.forms[0];\n";

// QUICK CALL-BACK FOR RE-OPEN
if($mode == 'reopen') {
	$tmp = updateClaim(true, $pid, $encounter, -1, -1, 1, 0);
	$mode = 'new';
} else if($mode == 'new') {
	$dt['proc_date'] = date('Y-m-d');
	$dt['proc_ordered_by'] = $visit->provider_id;
	if(IsDoctor()) $dt['proc_ordered_by'] = $_SESSION['authUser'];

} else {
	if($mode == 'fav') {
		if(!isset($_GET['itemID'])) $_GET['itemID'] = 0;
		$cnt = trim($_GET['itemID']);
		$type_field = 'proc_type';
		$code_field = 'proc_code';
		$plan_field = 'proc_plan';
		if($cnt) {
			$type_field .= '_' . $cnt;
			$code_field .= '_' . $cnt;
			$plan_field .= '_' . $cnt;
		}
		AddFavorite($dt[$type_field], $dt[$code_field], $dt[$plan_field]);
	}
	// UPDATE ANY EXISTING PROCEDURES ON THE FORM
	$cnt=1;
	while($cnt <= $dt['tmp_proc_cnt']) {
		AddOrUpdatePlan($pid, $encounter, $dt['proc_type_'.$cnt],
			$dt['proc_code_'.$cnt], $dt['proc_modifier_'.$cnt], 
			$dt['proc_plan_'.$cnt], $dt['proc_title_'.$cnt]);
		if($dt['proc_units_'.$cnt] == '') $dt['proc_units_'.$cnt] = 1;
		if(!isset($dt['proc_on_fee_'.$cnt])) $dt['proc_on_fee_'.$cnt] = '';
		if($dt['proc_on_fee_'.$cnt] && $dt['proc_type_'.$cnt] && 
					$dt['proc_code_'.$cnt]) {
			if($bill_id = billingExists($dt['proc_type_'.$cnt], 
				$dt['proc_code_'.$cnt], $pid, $encounter, $dt['proc_modifier_'.$cnt])) {

				$line = array('units' => $dt['proc_units_'.$cnt],
					'mod' => $dt['proc_modifier_'.$cnt], 
					'type' => $dt['proc_type_'.$cnt]);
				if(isset($dt['proc_justify_'.$cnt])) {
					if(!$dt['proc_justify_'.$cnt]) $dt['proc_justify_'.$cnt] = $autoj;
					$line['justify'] = convertJustifyToDB($dt['proc_justify_'.$cnt]);
				}
				updateBillingItem($bill_id, -1, $line);
			} else {
				if(!isset($dt['proc_justify_'.$cnt])) $dt['proc_justify_'.$cnt] = '';
				if(!$dt['proc_justify_'.$cnt]) $dt['proc_justify_'.$cnt] = $autoj;
				$jst = convertJustifyToDB($dt['proc_justify_'. $cnt]);
				$desc = $dt['proc_title_'.$cnt];
				if($desc == '') $desc = 
					lookup_code_descriptions($dt['proc_type_'.$cnt].':'.$dt['proc_code_'.$cnt]);
				$fee = getFee($dt['proc_type_'.$cnt], $dt['proc_code_'.$cnt], 
						$patient->pricelevel, $dt['proc_modifier_'.$cnt]);
				$bill_id = addBilling($encounter, $dt['proc_type_'.$cnt], 
					$dt['proc_code_'.$cnt], $desc, $pid, 1, $visit->provider_id, 
					$dt['proc_modifier_'.$cnt], $dt['proc_units_'.$cnt], $fee, '',
					$jst);
			}	
		}
		$cnt++;
	}

	// THIS START THE PROCESS FOR THE POSSIBLY ADDED CODE
	$test = AddOrUpdatePlan($pid, $encounter, $dt['proc_type'], $dt['proc_code'],
			$dt['proc_modifier'], $dt['proc_plan'], $dt['proc_title']);
	if(!isset($dt['proc_on_fee_'.$cnt])) $dt['proc_on_fee_'.$cnt] = '';

	if($dt['proc_on_fee'] && $dt['proc_type'] && $dt['proc_code']) {
		if($dt['proc_units'] == '') $dt['proc_units'] = 1;
		if($bill_id = billingExists($dt['proc_type'], $dt['proc_code'],
			$pid, $encounter, $dt['proc_modifier'])) {
			$line = array('units' => $dt['proc_units'],'mod' => $dt['proc_modifier'],
					'type' => $dt['proc_type']);
			if(isset($dt['proc_justify'])) {
				if(!$dt['proc_justify']) $dt['proc_justify'] = $autoj;
				$line['justify'] = convertJustifyToDB($dt['proc_justify']);
			}
			updateBillingItem($bill_id, -1, $line);
		} else {
			if(!isset($dt['proc_justify'])) $dt['proc_justify'] = '';
			if(!$dt['proc_justify']) $dt['proc_justify'] = $autoj;
			$jst = convertJustifyToDB($dt['proc_justify']);
			$desc = $dt['proc_title'];
			if($desc == '') $desc = 
				lookup_code_descriptions($dt['proc_type'].':'.$dt['proc_code']);
			$fee = getFee($dt['proc_type'], $dt['proc_code'], 
					$patient->pricelevel, $dt['proc_modifier']);
			// echo "Adding<br>\n";
			$bill_id = addBilling($encounter, $dt['proc_type'], 
				$dt['proc_code'], $desc, $pid, 1, $visit->provider_id, 
				$dt['proc_modifier'], $dt['proc_units'], $fee, '', $jst);
		}	
	}

  if(!$continue) {
		if($popup) {
			echo "<html>\n";
			echo "<head>\n";
			echo "<title>Redirecting....</title>\n";
			echo "<script type='text/javascript'>window.close();</script>\n";
			echo "</head>\n";
			echo "</html>\n";
			exit;
		} else {
			formJump($cancel_url);
		}
	}

	$dt['proc_type'] = $dt['proc_code'] = $dt['proc_modifier'] = '';
	$dt['proc_units'] = $dt['proc_title'] = $dt['proc_plan'] = '';
	$dt['proc_justify'] = '';

	if($mode == 'delproc') {
		$cnt = trim($_GET['itemID']);
		DeleteProcedure($pid, $encounter, $dt['proc_type_'.$cnt], 
			$dt['proc_code_'.$cnt], $dt['proc_modifier_'.$cnt]);
	}

}

$isBilled = BillingUtilities::isEncounterBilled($pid, $encounter);
$bill_flds = sqlListFields('billing');

$proc_data = GetEncounterProcedures($pid, $encounter, 'billing');
// NOW GET ANY PROCEDURES THAT ARE NOT IN THE 'billing' TABLE FOR SOME REASON
$extra = GetEncounterProcedures($pid, $encounter, 'lists');
foreach($extra as $x) {
	foreach($bill_flds as $fld) {
		$x[$fld] = '';
	}
	$x['code_type'] = $x['stype'];
	$x['code'] = $x['scode'];
	$x['modifier'] = $x['injury_part'];
	$proc_data[] = $x;
}

$diagJS = array();
if(count($diag) > 0) {
  foreach($diag as $line) {
		if($line['encounter'] != $encounter) continue;
		$diagJS[] = $line['diagnosis'];
	}
}

$cancel_warning = xl("Are you sure you wish to discard your changes? Click the 'OK' button to discard all of the changes you have made to this form or click the 'Cancel' button to continue working on this form.", 'r');

$dt['tmp_pat_disp_mode'] = 'block';
?>
<!DOCTYPE HTML>
<html>
	<head>
		<title><?php echo $form_title ?></title>
		<link rel="stylesheet" href="<?php echo $GLOBALS['css_header'];?>" type="text/css">
    <link rel="stylesheet" href="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/wmt.default.css" type="text/css">

    <?php Header::setupHeader(['datetime-picker', 'jquery', 'jquery-ui']); ?>

		<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt/wmtcalendar.js.php"></script>

		<script type="text/javascript">
		var mypcc = '<?php echo $GLOBALS['phone_country_code']; ?>';
				
			// confirm cancel
		function cancelClicked() {
			response = confirm("<?php echo $cancel_warning; ?>");
			if (response == true) {
				<?php
				if(!$popup) echo "	top.restoreSession();\n";
				?>
				return true;
			}
			else {
				return false;
			}
		}

		function validateProc() {
			<?php
			if(!$popup) echo "	top.restoreSession();\n";
			?>
			var skip = true;
			var item = '';
			if(arguments.length) skip = arguments[0];
			if(arguments.length > 1) item = arguments[1];
			var i;
			var sel;
			var val;
			var f = document.forms[0];
			var l = f.elements.length;
			var msg;
			for (i=0; i<l; i++) {
			}
			if(msg) {
				alert(msg);
				return false;
			}	
			return true;
		}

		function saveProcClicked() {
			if(validateProc(false)) document.forms[0].submit();
		}

		function reopenClicked() {
			document.forms[0].action += '&mode=reopen';
			document.forms[0].submit();
		}

		var diags = new Array();
		<?php
		if(count($diagJS)) {
			foreach($diagJS as $jst) {
				$all = explode(';', $jst);
				list($type, $code) = explode(':', $all[0]);
				genDiagJS($type, $code);
			}
		}
		?>

		// WHEN A JUSTIFY SELECTION IS MADE, APPLY IT TO THE CURRENT LIST FOR
		// THIS PROCEDURE AND THEN REBUILD ITS SELECTION LIST.
		function setJustify(seljust) {
			var theopts = seljust.options;
			var jdisplay = theopts[0].text;
			// COMPUTE REVISED JUSTIFICATOIN STRING. *NOTE: THIS DOES NOTHING IF
			// THE FIRST ENTRY IS STILL SELECTED, WHICH IS HANDY AT STARTUP.
			if (seljust.selectedIndex > 0) {
						var newdiag = seljust.value;
				if (newdiag.length == 0) {
					jdisplay = '';
				} else {
					if (jdisplay.length) jdisplay += ',';
							jdisplay += newdiag;
				}
			}
			// REBUILD THE SELECTION LIST.
			var jhaystack = ',' + jdisplay + ',';
			var j = 0;
			theopts.length = 0;
			theopts[j++] = new Option(jdisplay,jdisplay,true,true);
			for (var i = 0; i < diags.length; ++i) {
				if (jhaystack.indexOf(',' + diags[i] + ',') < 0) {
					theopts[j++] = new Option(diags[i],diags[i],false,false);
				}
			}
			theopts[j++] = new Option('Clear','',false,false);
		}
		</script>
	</head>

	<div id="save-notification" class="notification" style="left: 45%; top: 40%; z-index: 850; display: none;">Processing....</div>
	<body class="wmtFormBodyLight" style="margin: 30px 6px 6px 6px;" onLoad="<?php echo $load; ?>">

		<form method='post' action="<?php echo $save_url ?>" name='form_proc'> 
		<?php include($GLOBALS['srcdir'].'/wmt-v2/floating_menu.inc.php'); ?>
		<div style='padding-left: 5px; padding-right: 5px;'>
				<?php if($popup) { ?>
				<table width="100%" border="0" cellspacing="0" cellpadding="0">
					<td class="wmtLabel"><?php xl('Patient','e'); ?>:&nbsp;&nbsp;<span class="wmtBody"><?php echo $patient->full_name; ?></span></td>
					<td class="wmtLabel"><?php xl('ID','e'); ?>:&nbsp;&nbsp;<span class="wmtBody"><?php echo $patient->pubpid; ?></span></td>
				</table>
				<?php } ?>

			<?php
			if(checkSettingMode('wmt::include_pat_info','',$frmdir)) {
				echo "<div class='wmtMainContainer'>\n";
				$field_prefix = 'pat_';
				generateChapter('Patient Information','pat',$dt['tmp_pat_disp_mode'],
					'wmtCollapseBar','wmtChapter');
  			echo '<div id="PatBox" class="wmtCollapseBox" style="display: ',$dt['tmp_pat_disp_mode'],';" >';
				include($GLOBALS['srcdir'].'/wmt-v2/form_modules/pat_info_ins_module.inc.php');
				echo "	</div></div>\n";
			}
			$target_container = 'ProcBox';
			?>

			<div class="wmtMainContainer">
				<div id="ProcOCollapseBar" class="wmtCollapseBar" style="border-bottom: solid 1px black">
					<table width="100%" border="0" cellspacing="0" cellpadding="0">
						<tr class="wmtColorBar">
							<td class="wmtChapter" style="text-align: center">
								<?php xl('Procedures', 'e'); ?>
							</td>
						</tr>
					</table>
				</div>
				<div id="ProcBox" class="wmtCollapseBoxWhite" style="text-align:left">
					<?php include($GLOBALS['srcdir']."/wmt-v2/procedures.inc.php"); ?>
				</div>
			</div>
		</div>
			
			<br>
	<?php if($isBilled) { ?>
	<div style="width: 100%; padding: 8px;">
	<span class="bkkC" style="color: green; margin-left: 10px;">This encounter has already been billed. If you need to change it, it must be re-opened.</span>
	<div style="float: right; margin-right: 45px;"><a class="css_button" name="btn_reopen" id="btn_reopen" tabindex="-1" href="javascript:reopenClicked(); "><span><?php xl('Re-Open This Encounter','e'); ?></span></a></div>
	</div>
	<br>
	<?php } ?>

			<!-- Start of Buttons -->
			<table width="100%" border="0">
				<tr>
					<td class="wmtLabel" style="vertical-align:top;float:left;margin-left: 10px;">
						<a class="css_button" tabindex="-1" href="javascript:saveProcClicked(); "><span><?php xl('Save Data','e'); ?></span></a>
					</td>
					<td class="wmtLabel" style="vertical-align:top;float:right;margin-right: 45px">
						<a class="css_button" tabindex="-1" href="<?php echo $cancel_url ?>"><span><?php xl('Exit','e'); ?></span></a>
					</td>
				</tr>
			</table>
			<!-- End of Buttons -->
			
		</div>
		<input type="hidden" name="tmp_proc_window_mode" id="tmp_proc_window_mode" value="<?php echo isset($_GET['allproc']) ? 'allproc' : 'encounter'; ?>" tabindex="-1" />
<input name='tmp_price_level' id='tmp_price_level' type='hidden' value='<?php echo $patient->pricelevel; ?>' />
<input name='tmp_visit_dr' id='tmp_visit_dr' type='hidden' value='<?php echo $visit->provider_id; ?>' />
		</form>
	</body>

<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/wmtstandard.popup.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/wmtpopup.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/cpt.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/lists.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/diagnosis.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/js/common.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/textformat.js"></script>
<script type="text/javascript">

$(document).ready(function() {
	var i;
	var f = document.forms[0];
	var l = f.elements.length;
	for (i=0; i<l; i++) {
		<?php if($isBilled) { ?>
		if(f.elements[i].name.indexOf('btn_') == -1) {
			f.elements[i].readonly = true;
			f.elements[i].disabled = true;
		}
		<?php } ?>
		if(f.elements[i].name.indexOf('pat_') == 0) {
			f.elements[i].readonly = true;
			f.elements[i].disabled = true;
		}
	}

});
<?php echo $justinit; ?>
</script>
</html>
