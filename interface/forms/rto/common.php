<?php
/** **************************************************************************
 *	rto/common.php
 *
 *	Copyright (c)2012 - Williams Medical Technology, Inc.
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
 *  @subpackage rto           
 *  @version 1.0
 *  @copyright Williams Medical Technologies, Inc.
 *  @author Rich Genandt <rgenandt@gmail.com>
 * 
 *************************************************************************** */
require_once("../../globals.php");
require_once("{$GLOBALS['srcdir']}/options.inc.php");
require_once("{$GLOBALS['srcdir']}/api.inc");
require_once("{$GLOBALS['srcdir']}/calendar.inc");
require_once("{$GLOBALS['srcdir']}/pnotes.inc");
require_once("{$GLOBALS['srcdir']}/forms.inc");
require_once("{$GLOBALS['srcdir']}/translation.inc.php");
require_once("{$GLOBALS['srcdir']}/formatting.inc.php");
include_once("{$GLOBALS['srcdir']}/wmt-v2/wmtstandard.inc");
include_once("{$GLOBALS['srcdir']}/wmt-v2/rto.inc");
include_once("{$GLOBALS['srcdir']}/wmt-v2/wmtpatient.class.php");
include_once("{$GLOBALS['srcdir']}/wmt-v2/rto.class.php");
include_once("{$GLOBALS['srcdir']}/wmt-v2/wmt.msg.inc");

use OpenEMR\Core\Header;

$frmdir = 'rto';

/* INITIALIZE FORM DEFAULTS */
$id= '';

if(isset($_GET['id'])) $id = strip_tags($_GET['id']);
if(isset($_SESSION['pid'])) $pid = $_SESSION['pid'];
if(isset($_GET['pid'])) $pid = strip_tags($_GET['pid']);
if($pid == '' || $pid == 0) ReportMissingPID();
$encounter = $_SESSION['encounter'];
$cancel_url = $rootdir.'/patient_file/encounter/encounter_top.php';
$cancel_url = $GLOBALS['form_exit_url'];
$popup = false;
$db = false;
$popmode = 'no';
if(isset($_GET['pop'])) $popmode = strtolower(strip_tags($_GET['pop']));
global $user_lookup_order;
$user_lookup_order = strtolower(checkSettingMode('wmt::msg_user_order'));
if(strpos($popmode,'yes') !== false) {
	$popup = true;
	$cancel_url = 'javascript:window.close();';
} else if($popmode == 'db') {
	$db= true;
	$cancel_url = $rootdir.'/patient_file/summary/demographics.php';
} else {
	$popmode = 'no';
}
/****************
Defaults can be specified in a string in user settings by mode, passed 
through a button or link
action::status::assigned_to::number::frame::note
****************/
$default_settings = checkSettingMode('wmt::'.$popmode,'',$frmdir);
$defaults = array();
if($default_settings) $defaults = explode('::',$default_settings);

$form_title = 'Order Entry';
$form_table = 'form_rto';
$save_url = $rootdir.'/forms/rto/new.php?mode=save&pid='.$pid.'&pop='.$popmode;
$abort_url = $rootdir.'/patient_file/summary/demographics.php';
$base_action = $rootdir.'/forms/rto/new.php';
$base_action .= '?pop='.$popmode.'&pid='.$pid;
if(!isset($_POST['tmp_disp_mode'])) $_POST['tmp_disp_mode'] = '';
if($_POST['tmp_disp_mode'] == 'allrto') $_GET['allrto'] = 'allrto';
if(!isset($_GET['allrto'])) {
	if($id != 0 && $id != '') $base_action .= $base_action.'&id='.$id;
}
$wrap_mode = 'new';
$mode = 'new';
if(isset($_GET['mode'])) $mode = strip_tags($_GET['mode']);
$dt = array();
$flds = sqlListFields('form_rto');
foreach($flds as $key => $fld) { $dt[$fld]=''; }
foreach($_POST as $key => $val) {
	$val = trim($val);
	$dt[$key] = $val;
	if(strpos($key, '_date') !== false) $dt[$key] = DateToYYYYMMDD($val);
}
if(count($defaults) > 0) $dt['rto_action'] = $defaults[0];
if(count($defaults) > 1) $dt['rto_status'] = $defaults[1];
if(count($defaults) > 2) $dt['rto_resp_user'] = $defaults[2];
if(count($defaults) > 3) $dt['rto_num'] = $defaults[3];
if(count($defaults) > 4) $dt['rto_frame'] = $defaults[4];
if(count($defaults) > 5) $dt['rto_notes'] = $defaults[5];
$load = '';
if($dt['rto_num'] && $dt['rto_frame']) 
	$load = "FutureDate('rto_date','rto_num','rto_frame','rto_target_date','".
		$GLOBALS['date_display_format']."');";

$client_id = $GLOBALS['wmt::client_id'];

if($mode == 'new') {
	$dt['rto_date'] = date('Y-m-d');
	$dt['rto_ordered_by'] = $_SESSION['authUser'];
} else if($mode == 'save' || $mode == 'rto') {
	// Update any other existing RTO's in case they changed
	$cnt=1;
	// echo "All the data: ";
	// print_r($dt);
	// echo "<br>\m";
	while($cnt <= $dt['tmp_rto_cnt']) {
		if($dt['rto_status_'.$cnt] == '') $dt['rto_status_'.$cnt] = 'p';
		if(!isset($dt['rto_repeat_'.$cnt])) $dt['rto_repeat_'.$cnt] = '';
		// echo "Going to Update ($cnt)<br>\n";
		UpdateRTO($pid,$dt['rto_id_'.$cnt],$dt['rto_num_'.$cnt],
			$dt['rto_frame_'.$cnt],$dt['rto_status_'.$cnt],$dt['rto_notes_'.$cnt],
			$dt['rto_resp_'.$cnt],$dt['rto_action_'.$cnt],$dt['rto_date_'.$cnt],
			$dt['rto_target_date_'.$cnt],$dt['rto_ordered_by_'.$cnt],false,
			$dt['rto_repeat_'.$cnt],$dt['rto_stop_date_'.$cnt]);
		$cnt++;
	}
	if($dt['rto_status'] == '') $dt['rto_status'] = 'p';
	if(!isset($dt['rto_repeat'])) $dt['rto_repeat'] = '';
	$test = AddRTO($pid,$dt['rto_num'],$dt['rto_frame'],$dt['rto_status'],
		$dt['rto_notes'],$dt['rto_resp_user'],$dt['rto_action'],$dt['rto_date'],
		$dt['rto_target_date'],$dt['rto_ordered_by'],$dt['rto_repeat'],
		$dt['rto_stop_date']);
	if($test) {
		$text = CreateNoteText($dt['rto_num'],$dt['rto_frame'],$dt['rto_action'],
			$dt['rto_date'],$dt['rto_target_date'],$dt['rto_ordered_by'],
			$dt['rto_notes']);
		$title = 'New Orders';
		addPnote($pid,$text,$_SESSION['userauthorized'],'1',$title,$dt['rto_resp_user']);
	}
	$dt['rto_date'] = date('Y-m-d');
	$dt['rto_ordered_by'] = $_SESSION['authUser'];
	$dt['rto_num'] = $dt['rto_frame'] = $dt['rto_target_date'] = '';
	$dt['rto_action'] = $dt['rto_repeat'] = $dt['rto_stop_date'] = '';
	$dt['rto_status'] = $dt['rto_notes'] = $dt['rto_resp_user'] = '';
	if($mode == 'save') {
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

} else if($mode == 'updaterto') {
	$cnt = trim($_GET['itemID']);
	if(!isset($dt['rto_repeat_'.$cnt])) $dt['rto_repeat_'.$cnt] = '';
	UpdateRTO($pid,$dt['rto_id_'.$cnt],$dt['rto_num_'.$cnt],
		$dt['rto_frame_'.$cnt],$dt['rto_status_'.$cnt],$dt['rto_notes_'.$cnt],
		$dt['rto_resp_'.$cnt],$dt['rto_action_'.$cnt],
		$dt['rto_date_'.$cnt],$dt['rto_target_date_'.$cnt],
		$dt['rto_ordered_by_'.$cnt],false,$dt['rto_repeat_'.$cnt],
		$dt['rto_stop_date_'.$cnt]);
	if($id) {
		echo "<html>\n";
		echo "<head>\n";
		echo "<title>Redirecting....</title>\n";
		echo "\n<script type='text/javascript'>window.close();</script>\n";
		echo "</head>\n";
		echo "</html>\n";
		exit;
	}

} else if($mode == 'delrto') {
	$cnt = trim($_GET['itemID']);
	DeleteRTO($pid, $dt['rto_id_'.$cnt]);

} else if($mode == 'remindrto') {
	$cnt = trim($_GET['itemID']);
	if(!isset($dt['rto_repeat_'.$cnt])) $dt['rto_repeat_'.$cnt] = '';
	UpdateRTO($pid,$dt['rto_id_'.$cnt],$dt['rto_num_'.$cnt],
		$dt['rto_frame_'.$cnt],$dt['rto_status_'.$cnt],$dt['rto_notes_'.$cnt],
		$dt['rto_resp_'.$cnt],$dt['rto_action_'.$cnt],
		$dt['rto_date_'.$cnt],$dt['rto_target_date_'.$cnt],
		$dt['rto_ordered_by_'.$cnt],true,$dt['rto_repeat_'.$cnt],
		$dt['rto_stop_date_'.$cnt]);
}

/* RETRIEVE RTO DATA */
if(isset($_GET['allrto'])) {
	$rto_data = getAllRTO($pid);
} else if($id) {
	$rto_data = getRTObyId($pid, $id);
} else {
	$rto_data = getAllRTO($pid);
}
$cancel_warning = xl("Are you sure you wish to discard your changes? Click the 'OK' button to discard all of the changes you have made to this form or click the 'Cancel' button to continue working on this form.", 'r');

$patient = wmtPatData::getPidPatient($pid);
foreach($patient as $key => $val) {
	$dt['pat_'.$key] = $val;
}
$dt['tmp_pat_disp_mode'] = 'block';
$load .= "AdjustFocus('rto_action'); ";
?>
<!DOCTYPE HTML>
<html>
	<head>
		<title><?php echo $form_title ?></title>

		<!-- <style type="text/css">@import url(../../../library/dynarch_calendar.css);</style> -->
		<link rel="stylesheet" href="<?php echo $GLOBALS['css_header'];?>" type="text/css">
		<link rel="stylesheet" href="<?php echo $GLOBALS['webroot'].'/library/wmt-v2/wmt.default.css'; ?>" type="text/css">

		<?php Header::setupHeader(['datetime-picker', 'jquery', 'jquery-ui']); ?>

		<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt/wmtcalendar.js.php"></script>

		<script>
		var mypcc = '<?php echo $GLOBALS['phone_country_code']; ?>';

		<?php include($GLOBALS['srcdir'].'/wmt-v2/ajax/init_ajax.inc.js'); ?>
				
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

		function validateRTO() {
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
				if(f.elements[i].name.indexOf('rto_resp_') == 0) {
					// alert("We did find the field");
					<?php if(checkSettingMode('wmt::rto_assign_require','',$frmdir)) { ?>
					sel = f.elements[i];
					val = sel.options[sel.selectedIndex].value;
					// alert("This is my user: "+val);
					if(!skip || f.elements[i].name != 'rto_resp_user') {
						if(!item || f.elements[i].name == 'rto_resp_user_'+item) {
							if(!val) {
								sel.style.border = 'solid 1px red';
								msg = 'Fields bordered in red are required';
							}
						}
					}
					<?php } ?>
				}

				if(f.elements[i].name.indexOf('rto_action') == 0) {
					<?php if(checkSettingMode('wmt::rto_action_require','',$frmdir)) { ?>
					sel = f.elements[i];
					val = sel.options[sel.selectedIndex].value;
					if(!skip || f.elements[i].name != 'rto_action') {
						if(!item || f.elements[i].name == 'rto_action_'+item) {
							if(!val) {
								sel.style.border = 'solid 1px red';
								msg = 'Fields bordered in red are required';
							}
						}
					}
					<?php } ?>
				}

				if(f.elements[i].name.indexOf('rto_target_date') == 0) {
					<?php if(checkSettingMode('wmt::rto_target_require','',$frmdir)) { ?>
					val = f.elements[i].value;
					if(!skip || f.elements[i].name != 'rto_target_date') {
						if(!item || f.elements[i].name == 'rto_target_date_'+item) {
							if(!val || val == '0000-00-00' || val == '00/00/0000') {
								f.elements[i]..style.border = 'solid 1px red';
								msg = 'Fields bordered in red are required';
							}
						}
					}
					<?php } ?>
				}
			}
			if(msg) {
				alert(msg);
				return false;
			}	
			return true;
		}

		function saveRTOClicked() {
			if(validateRTO(false)) document.forms[0].submit();
		}
;
		function updateBorder(sel) {
			if(sel.options[sel.selectedIndex].value != '') {
				sel.style.border = 'solid 1px grey';
			}
		}

		function TestByAction(testFld, schedFld, actionFld) {
			if(testFld.indexOf('rto_test_target_dt') != -1) {
				var action = document.getElementById(actionFld).value;
				// alert("The action code is: "+action);	
				if(action == 'sa' || action == 'ref_pend') {
					// alert("Checking....");
					ExtraDateCheck(schedFld, testFld);
				}
			}
			return true;
		}

		function setOrderComplete(item) {
			var output = 'error';
			if(!item) {
				alert('No Order ID Was Specified...Something Must Be Wrong!');
				return false;
			}
			var tst = document.getElementById('rto_id_'+item);
			if(!tst) {
				alert('No Order ID Could Be Found...Something Must Be Wrong!');
				return false;
			}
			var item_id = tst.value;
			$.ajax({
				type: "POST",
				url: "<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/ajax/update_this.ajax.php",
				datatype: "html",
				data: {
					table: 'form_rto',
					columns: 'rto_status^~c',
					keys: 'id^~'+item_id
				},
				success: function(result) {
					if(result['error']) {
						output = '';
						alert('There was a problem updating that order\n'+result['error']);
					} else {
						output = result;
					}
				},
				async: false
			});
			return output;
		}

		function handleComplete(item) {
			setOrderComplete(item);
			findAndSelect('rto_status_'+item,'c');
		}

		</script>
		<style type="text/css">
			.disabledInput {
				color: #000!important;
			}
		</style>
	</head>

	<body onLoad="<?php echo $load; ?>">

		<form method='post' action="<?php echo $save_url ?>" name='form_rto'> 
		<div style='padding-left: 5px; padding-right: 5px;'>
			<!-- Start of RTO -->
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
			?>

			<div class="wmtMainContainer">
				<div id="RTOCollapseBar" class="wmtCollapseBar" style="border-bottom: solid 1px black">
					<table width="100%" border="0" cellspacing="0" cellpadding="0">
						<tr class="wmtColorBar">
							<td class="wmtChapter" style="text-align: center">
								<?php echo xlt('Order Entry'); ?>
							</td>
						</tr>
					</table>
				</div>
				<div id="RTOBox" class="wmtCollapseBoxWhite" style="text-align:left">
					<?php include("../../../library/wmt-v2/rto.inc.php"); ?>
				</div>
			</div><!-- End of RTO -->
		</div>
			
			</br>

			<!-- Start of Buttons -->
			<table width="100%" border="0">
				<tr>
					<td class="wmtLabel" style="vertical-align:top;float:left;margin-left: 10px;">
						<a class="css_button" tabindex="-1" href="javascript:saveRTOClicked(); "><span><?php xl('Save Data','e'); ?></span></a>
					</td>
					<td class="wmtLabel" style="vertical-align:top;float:right;margin-right: 45px">
						<a class="css_button" tabindex="-1" href="<?php echo $cancel_url ?>" <?php if(!$GLOBALS['concurrent_layout']) echo 'target="Main"'; ?> ><span><?php xl('Exit','e'); ?></span></a>
					</td>
				</tr>
			</table>
			<!-- End of Buttons -->
			
		</div>
		<input type="hidden" name="tmp_disp_mode" id="tmp_disp_mode" value="<?php echo isset($_GET['allrto']) ? 'allrto' : ''; ?>" tabindex="-1" />
		</form>
	</body>

<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/wmtstandard.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/rto.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/wmt.forms.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/wmtpopup.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/js/common.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/textformat.js"></script>
<script type="text/javascript">
<?php
if($GLOBALS['date_display_format'] == 1) {
	$date_fmt = '%m/%d/%Y';
} else if($GLOBALS['date_display_format'] == 2) {
	$date_fmt = '%d/%m/%Y';
} else $date_fmt = '%Y-%m-%d';
?>
Calendar.setup({inputField:"rto_date", ifFormat:"<?php echo $date_fmt; ?>", button:"img_rto_dt"});
Calendar.setup({inputField:"rto_target_date", ifFormat:"<?php echo $date_fmt; ?>", button:"img_rto_target_dt"});
Calendar.setup({inputField:"rto_stop_date", ifFormat:"<?php echo $date_fmt; ?>", button:"img_rto_stop_dt"});

$(document).ready(function() {
	var i;
	var f = document.forms[0];
	var l = f.elements.length;
	for (i=0; i<l; i++) {
		if(f.elements[i].name.indexOf('pat_') == 0) {
			f.elements[i].readonly = true;
			f.elements[i].disabled = true;
		}
	}

});
</script>
</html>
