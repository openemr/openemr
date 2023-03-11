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
//require_once("{$GLOBALS['srcdir']}/acl.inc");
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
include_once("$srcdir/OemrAD/oemrad.globals.php");

use OpenEMR\Core\Header;

use OpenEMR\OemrAd\Caselib;
use OpenEMR\OemrAd\OrderLbfForm;

$neworderid = '';
$page_limit = 10;
$pageno = isset($_REQUEST['pageno']) ? $_REQUEST['pageno'] : 1;

$rtoformname = isset($_REQUEST['rto_formname']) ? $_REQUEST['rto_formname'] : '';
$rtoformtitle = isset($_REQUEST['rto_form_title']) ? $_REQUEST['rto_form_title'] : '';

//$f_rto_action = isset($_REQUEST['f_rto_action']) ? $_REQUEST['f_rto_action'] : '';
$f_rto_action = isset($_REQUEST['f_rto_action']) ? json_decode($_REQUEST['f_rto_action']) : array();
$f_rto_date = isset($_REQUEST['f_rto_date']) ? $_REQUEST['f_rto_date'] : '';
$f_rto_id = isset($_REQUEST['f_rto_id']) ? $_REQUEST['f_rto_id'] : '';
$f_rto_status = isset($_REQUEST['f_rto_status']) ? json_decode($_REQUEST['f_rto_status']) : array('p', 'misinfo', 'pendins', 'pendpi', 'ssss', 'ssss142', 'deny', 'x', 'Patdec', 'sc85', 'UCRijw$', 'PPR5561');
$f_case_id = isset($_REQUEST['f_case_id']) ? $_REQUEST['f_case_id'] : '';

$currentFormateDate = DateFormatRead("jquery-datetimepicker");

function getFilterQueryStr() {
	global $f_rto_action, $f_rto_date, $f_rto_id, $f_rto_status, $f_case_id;

	$sql = '';
	if(!empty($f_rto_action)) {
		//$sql .= " AND rto_action = '".$f_rto_action."'";
		if(is_array($f_rto_action)) {
			if(!in_array("", $f_rto_action)) {
				$sql .= " AND rto_action IN ('".implode("','",$f_rto_action)."') ";
			}
		} else {
			$sql .= " AND rto_action = '".$f_rto_action."' ";
		}
	}

	if(!empty($f_rto_date)) {
		$sql .= " AND rto_date = '".date('Y-m-d',strtotime($f_rto_date))."'";
	}

	if(!empty($f_rto_id)) {
		$sql .= " AND id = '".$f_rto_id."'";
	}

	if(!empty($f_rto_status)) {
		if(is_array($f_rto_status)) {
			if(!in_array("", $f_rto_status)) {
				$sql .= " AND rto_status IN ('".implode("','",$f_rto_status)."') ";
			}
		} else {
			$sql .= " AND rto_status = '".$f_rto_status."' ";
		}
	}

	if(!empty($f_case_id)) {
		$sql .= " AND rto_case = '".$f_case_id."' ";
	}

	return $sql;
}

function getAllRTO1($thisPid, $limit = 0, $pageno = 1, $pageDetails = false)
{
	$sql =  "SELECT * FROM form_rto WHERE pid=? ";
	$bindArray = array($thisPid);

	if(!isset($_GET['allrto'])) {
		//$sql .= " AND (rto_status != 'c' AND rto_status != 's') "; 
	}

	$filterStr = getFilterQueryStr();
	if(!empty($filterStr)) {
		$sql .= $filterStr;
	}


	//$sql .= "ORDER BY date, rto_status DESC";
	$sql .= "ORDER BY date DESC";

  if($pageDetails === true) {
  	$row = sqlStatementNoLog($sql, $bindArray);
  	$total_records = sqlNumRows($row);
	$total_pages = ceil($total_records / $limit);

	return array(
		'total_records' => $total_records,
		'limit' => $limit,
		'total_pages' => $total_pages,
	);
  }

  $page_offset = ($pageno >= 1) ? ($pageno-1) * $limit : 1;
  if(!empty($limit) && $limit != 0) {
  	$sql .= " LIMIT $page_offset, $limit";
  }

  $all=array();
  $res = sqlStatement($sql, $bindArray);
  for($iter =0;$row = sqlFetchArray($res);$iter++) { 
		$links = LoadLinkedTriggers($row{'id'}, $thisPid);
		if($links) {
			$settings = explode('|', $links);
			foreach($settings as $test) {
				$tmp = explode('^',$test);
				$key = $tmp[0];
				$val = $tmp[1];
				$row[$key] = $val;
			}
		}
		$all[] = $row;
	}
  return $all;
}

function UpdateRTOExt($thisPid,$item,$num='',$frame='',$stat='',$note='',$resp='',$action='', $dt='', $target='', $by='', $force_msg=false, $repeat=NULL, $stop=NULL, $case=NULL, $stat1 = NULL)
{
		
  $responce = array();	
  if(!VerifyPatientID($thisPid)) return false;
	if(!empty($num) || !empty($frame) || !empty($stat) || !empty($note) || 
		!empty($resp) || !empty($action) || ($repeat != NULL) || ($stop != NULL)) {
		// If the responsible user or action has changed we need to store
		$rrow = sqlQuery('SELECT form_rto.*, '.
			'o.codes, o.title FROM form_rto LEFT JOIN '.
			'(SELECT * FROM list_options WHERE list_id="RTO_Action") AS o '.
			'on option_id = rto_action '.
			'WHERE pid=? AND id=?', array($thisPid, $item));

		// echo "This is our existing Row ($thisPid, $item): ";
		// print_r($rrow);
		// echo "<br>\n";
		$status_change = $rto_completed = false;
		if($stat != $rrow{'rto_status'}) {
			$status_change = true;
			if(isComplete($stat)) $rto_completed = true;
		}

		$resp_change = false;
		if($resp != $rrow{'rto_resp_user'}) {
			sqlStatement("UPDATE form_rto SET rto_last_resp_user=? ".
				"WHERE pid=? AND id=?", array($resp, $thisPid, $item));
			$resp_change= true;
		}
		if($resp_change || $force_msg) {
			$text= CreateNoteText($num, $frame, $action, $dt, $target, $by, $note);
			$responce['note_id'] = addPnote($thisPid,$text,$_SESSION['userauthorized'],'1','New Orders',$resp);
		}

		if($action != $rrow{'rto_action'}) {
			sqlStatement("UPDATE form_rto SET rto_last_action=? ".
				"WHERE pid=? AND id=?", array($action, $thisPid, $item));
		}

		// Is This is Billable Action and is it complete?
		// if($rrow{'codes'}) {
			// echo "Billable<br>\n";
			// echo "Complete Status Is: $completed_status<br>\n";
			// if($stat == $completed_status) {
				// echo "Adding Forms<br>\n";
				// $thisEnc = GetEncounterForToday($thisPid);
				// addForm($thisEnc, 'Completed Orders', 0, $thisPid, 
								// $_SESSION['userauthorized']);
				// LinkListEntry($thisPid, $item, $thisEnc, 'rto', false, true); 
			// }
		// }

		// ONLY IF CHANGED DO WE REALLY WANT UPDATE AND LOG THE TOUCHING
		if($repeat == NULL) $repeat = 0;
		if($num == '') $num = 0;
		if($rrow['rto_num'] == '') $rrow['rto_num'] = 0;
		/*
		if($dt != $rrow['rto_date']) echo "Date is a culrpit!<br>\n";
		if($num != $rrow['rto_num']) echo "Number is a culrpit!<br>\n";
		if($frame != $rrow['rto_frame']) echo "Frame is a culrpit!<br>\n";
		if($stat != $rrow['rto_status']) echo "Status is a culrpit!<br>\n";
		if($note != $rrow['rto_notes']) echo "Notes is a culrpit!<br>\n";
		if($resp != $rrow['rto_resp_user']) echo "Resp User is a culrpit!<br>\n";
		if($action != $rrow['rto_action']) echo "Action is a culrpit!<br>\n";
		if($target != $rrow['rto_target_date']) echo "Target is a culrpit!<br>\n";
		if($by != $rrow['rto_ordered_by']) echo "By is a culrpit!<br>\n";
		if($repeat != $rrow['rto_repeat']) echo "Repeat is a culrpit!<br>\n";
		if($stop != $rrow['rto_stop_date']) echo "Stop is a culrpit!<br>\n";
		exit;
		*/
		if( ($dt != $rrow['rto_date']) || ($num != $rrow['rto_num']) ||
			($frame != $rrow['rto_frame']) || ($stat != $rrow['rto_status']) ||
			($note != $rrow['rto_notes']) || ($resp != $rrow['rto_resp_user']) ||
			($action != $rrow['rto_action']) || ($target != $rrow['rto_target_date'])
			|| ($by != $rrow['rto_ordered_by']) || ($repeat != $rrow['rto_repeat'])
			|| ($stop != $rrow['rto_stop_date']) || ($case != $rrow['rto_case']) || ($stat1 !== NULL && $stat1 != $rrow['rto_stat'])) {

			$parms= array($dt, $num, $frame, $stat, $note, $resp, $action, $target, 
				$by, $repeat, $stop, $case, $_SESSION['authUserID'], $thisPid, $item);

			$dQuery = "";
			if($stat1 !== NULL) {
				$dQuery =", rto_stat = ? ";
				$parms= array($dt, $num, $frame, $stat, $note, $resp, $action, $target, 
				$by, $repeat, $stop, $case, $_SESSION['authUserID'], $stat1, $thisPid, $item);
			}

  			sqlStatement("UPDATE form_rto SET rto_last_touch=NOW(), rto_date=?, ".
				"rto_num=?, rto_frame=?, rto_status=?, rto_notes=?, rto_resp_user=?, ".
				"rto_action=?, rto_target_date=?, rto_ordered_by=?, rto_repeat=?, ".
				"rto_stop_date=?, rto_case=?, rto_touch_by=? $dQuery WHERE pid=? AND id=?",
			 	$parms);
		}

		// NOW DEAL WITH CREATING A NEW REPEATING EVENT IF APPLICABLE
		if($repeat && $rto_completed) resolveRepeatingRTO($item);	
	}

	UpdateRTOLinks($item, $thisPid, $stat, $action, $target);

	return $responce;
}

function generatePagination($page_details, $pageno) {
	$pageList = array();
	$max = 5;
    if($pageno < $max)
        $sp = 1;
    elseif($pageno >= ($page_details['total_pages'] - floor($max / 2)) )
        $sp = $page_details['total_pages'] - $max + 1;
    elseif($pageno >= $max)
        $sp = $pageno  - floor($max/2);

    for($i = $sp; $i <= ($sp + $max -1);$i++) {
    	if($i > $page_details['total_pages']) {
            continue;
        } else {
        	$pageList[] = $i;
        }
    }

    if($page_details['total_pages'] > 1) {
	?>
	<div class="paginationContainer">
	<ul class="pagination">
        <li class="actionBtn <?php if($pageno <= 1){ echo 'disabled'; } ?>"><input type="button" class="page_submit" style="padding:3px 8px;margin:0;" onclick="changePage('1')" value=" First " /></li>
        <li class="actionBtn <?php if($pageno <= 1){ echo 'disabled'; } ?>">
            <input type="button" class="page_submit" style="padding:3px 8px;margin:0;" onclick="changePage('<?php echo ($pageno <= 1) ? '' : ($pageno - 1); ?>')" value=" Prev " />
        </li>
        <?php 
        	foreach ($pageList as $page) {
        		?>
        		<li class="pageBtn <?php if($page == $pageno){ echo 'active'; } ?>">
		            <input type="button" class="page_submit " style="padding:3px 8px;margin:0;" onclick="changePage('<?php echo $page; ?>')" value=" <?php echo $page; ?> " />
		        </li>
        		<?php
        	}
        ?>
        <li class="actionBtn <?php if($pageno >= $page_details['total_pages']){ echo 'disabled'; } ?>">
            <input type="button" class="page_submit" style="padding:3px 8px;margin:0;" onclick="changePage('<?php echo ($pageno >= $page_details['total_pages']) ? '' : ($pageno + 1); ?>')" value=" Next " />
        </li>
        <li class="actionBtn <?php if($pageno >= $page_details['total_pages']){ echo 'disabled'; } ?>"><input type="button" class="page_submit" style="padding:3px 8px;margin:0;" onclick="changePage('<?php echo $page_details['total_pages'] ?>')" value=" Last " /></li>
    </ul>
	</div>
	<?php
	}
}

/* OEMR - Changes */
if($newordermode === true) {
	$mode = strip_tags($_GET['mode']);
}
/* End */

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
$save_url = $rootdir.'/forms/rto1/new.php?mode=save&pid='.$pid.'&pop='.$popmode;
$filter_url = $rootdir.'/forms/rto1/new.php?pid='.$pid.'&pop='.$popmode;
$abort_url = $rootdir.'/patient_file/summary/demographics.php';
$base_action = $rootdir.'/forms/rto1/new.php';
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

// OEMR - Change
if($mode == "delrto") {
	$cnt = trim($_GET['itemID']);
	$rto_id = isset($dt['rto_id_'.$cnt]) ? $dt['rto_id_'.$cnt] : '';
	
	$rtoData = getRtoLayoutFormData($pid, $rto_id);
	$form_id = isset($rtoData['form_id']) ? $rtoData['form_id'] : 0;

	manageAction($pid, $rto_id, $form_id);
}

if($mode == 'new') {
	$dt['rto_date'] = date('Y-m-d');
	$dt['rto_ordered_by'] = $_SESSION['authUser'];
} else if($mode == 'save' || $mode == 'rto' || $mode == 'new_rto_save') {

	//Ignore all data save
	if($mode != 'save') {
		include_once("rto_save.php");

		$dt['rto_date'] = date('Y-m-d');
		$dt['rto_ordered_by'] = $_SESSION['authUser'];
		$dt['rto_num'] = $dt['rto_frame'] = $dt['rto_target_date'] = '';
		$dt['rto_action'] = $dt['rto_repeat'] = $dt['rto_stop_date'] = '';
		$dt['rto_status'] = $dt['rto_notes'] = $dt['rto_resp_user'] = '';
	}
	
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
	if(!isset($dt['rto_stat_'.$cnt])) $dt['rto_stat_'.$cnt] = 0;
	
	//RTO Data
	$rto_data_bup = getRTObyId($pid, $dt['rto_id_'.$cnt]);

	UpdateRTO($pid,$dt['rto_id_'.$cnt],$dt['rto_num_'.$cnt],
		$dt['rto_frame_'.$cnt],$dt['rto_status_'.$cnt],$dt['rto_notes_'.$cnt],
		$dt['rto_resp_'.$cnt],$dt['rto_action_'.$cnt],
		$dt['rto_date_'.$cnt],$dt['rto_target_date_'.$cnt],
		$dt['rto_ordered_by_'.$cnt],false,$dt['rto_repeat_'.$cnt],
		$dt['rto_stop_date_'.$cnt], $dt['rto_case_'.$cnt], $dt['rto_stat_'.$cnt]);

	// OEMR - Change
	rtoBeforeSave($pid);

	unset($rto_data_bup);

	// if($id) {
	// 	echo "<html>\n";
	// 	echo "<head>\n";
	// 	echo "<title>Redirecting....</title>\n";
	// 	echo "\n<script type='text/javascript'>window.close();</script>\n";
	// 	echo "</head>\n";
	// 	echo "</html>\n";
	// 	exit;
	// }

} else if($mode == 'delrto') {
	$cnt = trim($_GET['itemID']);
	DeleteRTO($pid, $dt['rto_id_'.$cnt]);

} else if($mode == 'remindrto') {
	$cnt = trim($_GET['itemID']);
	if(!isset($dt['rto_repeat_'.$cnt])) $dt['rto_repeat_'.$cnt] = '';
	if(!isset($dt['rto_stat_'.$cnt])) $dt['rto_stat_'.$cnt] = 0;
	$uResponce = UpdateRTOExt($pid,$dt['rto_id_'.$cnt],$dt['rto_num_'.$cnt],
		$dt['rto_frame_'.$cnt],$dt['rto_status_'.$cnt],$dt['rto_notes_'.$cnt],
		$dt['rto_resp_'.$cnt],$dt['rto_action_'.$cnt],
		$dt['rto_date_'.$cnt],$dt['rto_target_date_'.$cnt],
		$dt['rto_ordered_by_'.$cnt],true,$dt['rto_repeat_'.$cnt],
		$dt['rto_stop_date_'.$cnt], $dt['rto_case_'.$cnt], $dt['rto_stat_'.$cnt]);

	$noteId = isset($uResponce['note_id']) ? $uResponce['note_id'] : '';
	
	/* OEMR - Changes */
	$rto_id = isset($dt['rto_id_'.$cnt]) ? $dt['rto_id_'.$cnt] : '';
	$relation_id = isset($noteId) && !empty($noteId) ? $noteId : NULL;
	saveOrderLog("INTERNAL_NOTE", $rto_id, $relation_id, NULL, $pid, 'Reminder', $_SESSION['authUserID']);
	/* End */
}

/* RETRIEVE RTO DATA */
if($newordermode === true) {
	if(isset($_GET['allrto'])) {
		$rto_data = getAllRTO1($pid, $page_limit, $pageno);
		$rto_page_details = getAllRTO1($pid, $page_limit, 0, true);
	} else if($id) {
		$rto_data = getRTObyId($pid, $id);
	} else {
		$rto_data = getAllRTO1($pid, $page_limit, $pageno);
		$rto_page_details = getAllRTO1($pid, $page_limit, 0, true);
	}
} else {
	if(isset($_GET['allrto'])) {
		$rto_data = getAllRTO($pid);
	} else if($id) {
		$rto_data = getRTObyId($pid, $id);
	} else {
		$rto_data = getAllRTO1($pid);
	}
}

//Rto Base64 Data
$rto_base64 = base64_encode(json_encode($rto_data));

$cancel_warning = xl("Are you sure you wish to discard your changes? Click the 'OK' button to discard all of the changes you have made to this form or click the 'Cancel' button to continue working on this form.", 'r');

$patient = wmtPatData::getPidPatient($pid);
foreach($patient as $key => $val) {
	$dt['pat_'.$key] = $val;
}
$dt['tmp_pat_disp_mode'] = 'block';

//Remove if to enable
if($newordermode == false) {
	$load .= "AdjustFocus('rto_action'); ";
}
?>
<!DOCTYPE HTML>
<html>
	<head>
		<title><?php echo $form_title ?></title>

		<link rel="stylesheet" href="<?php echo $GLOBALS['css_header'];?>" type="text/css">
		<link rel="stylesheet" href="<?php echo $GLOBALS['webroot'].'/library/wmt-v2/wmt.default.css'; ?>" type="text/css">

		<?php Header::setupHeader(['opener', 'common', 'jquery-ui', 'jquery-ui-base', 'datetime-picker', 'oemr_ad']); ?>

		<?php include('rto1.js.php'); ?>
		<?php include('rto.js.php'); ?>

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

		async function updateRTOClicked(pid, base, wrap, cnt, id, mode, fieldId, title) {

			var rto_resp_user = $('#rto_resp_'+cnt).val();
			var rto_action = $('#rto_action_'+cnt).val();
			var case_id = $('#rto_case_'+cnt).val(); 

			if(rto_resp_user == "") {
				alert("Please select assigned To");
				return false;
			}

			if(validateRTO(false)) {
				var caseData = await checkCaseValidation(pid, rto_action, case_id);
				if(caseData === false) {
					return false;
				}
				

				SetScrollTop();
			  	document.forms[0].action=base+'&mode='+mode+'&wrap='+wrap+'&itemID='+cnt;

			  	//if(formID != '' && formID != 0) {
			 	//	document.forms[0].action=base+'&mode=rto&wrap='+wrap+'&id='+formID;
				//}

				document.forms[0].submit();
			}
		}

		async function saveRTOClicked() {
			if(validateRTO(false)) {
				var caseData = await checkCaseValidation('<?php echo $pid; ?>');
				if(caseData === false) {
					return false;
				}
				document.forms[0].submit();
			}
		}

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
	</head>

	<body onLoad="<?php echo $load; ?>">
		<?php if(empty($id)) { ?>
		<div class="filterContainer">
			<div class="wmtMainContainer">
			<div class="innerContainer">
				<table class="filterTable">
					<tr>
						<td>
							<div class="headerContainer">
								<div>
									<button type="button" class="css_button_small addOrderBtn" onclick="addOrderBtn()">Add Order</button>
								</div>
								<div class="filterFieldContainer">
									<div class="ordeActionFilterContainer">
										<label><b>Order Type: </b>&nbsp;</label>
										<div>
											<select name='tmp_rto_action' id='tmp_rto_action' class='wmtFullInput fRtoAction' multiple>
											<?php MultiListSel($f_rto_action, 'RTO_Action', '-- ALL --'); ?>	
											</select>
										</div>
									</div>

									<div>
										<label><b>Order Date: </b>&nbsp;</label>
										<div>
											<input type="text" name="tmp_rto_date" id="tmp_rto_date" class="wmtFullInput fRtoDate datepicker" value="<?php echo getFormatedDate($currentFormateDate, $f_rto_date) ?>" />
										</div>
									</div>

									<div>
										<label><b>Order Id: </b>&nbsp;</label>
										<div>
											<input type="text" name="tmp_rto_id" id="tmp_rto_id" class="wmtFullInput fRtoId" value="<?php echo $f_rto_id; ?>" />
										</div>
									</div>

									<div class="ordeStatusFilterContainer">
										<label><b>Order Status: </b>&nbsp;</label>
										<div>
											<select name='tmp_rto_status' id='tmp_rto_status' class='wmtFullInput fRtoStatus' multiple>
											<?php MultiListSel($f_rto_status, 'RTO_Status', '-- ALL --'); ?></select>
										</div>
									</div>

									<div>
										<label><b>Order Case: </b>&nbsp;</label>
										<div>
											<input type="text" name="tmp_case_id" id="tmp_case_id" class="wmtFullInput fRtoCase" value="<?php echo $f_case_id; ?>" onclick="sel_case('<?php echo $pid; ?>', '', 'filter');" />
										</div>
									</div>
								</div>
							</div>
						</td>
						<td valign="top">
							<button type="button" class="css_button_small filterBtn" onclick="filterSubmit()">Submit</button>
						</td>
					</tr>
				</table>
			</div>
			</div>
		</div>
		<?php } ?>

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
			<?php
				if($newordermode === true) {
					echo generatePagination($rto_page_details, $pageno);
				}
			?>
		</div>
			
			</br>

			<!-- Start of Buttons -->
			<table width="100%" border="0">
				<tr>
					<td class="wmtLabel" style="vertical-align:top;float:left;margin-left: 10px; display:none;">
						<a class="css_button" tabindex="-1" href="javascript:saveRTOClicked(); "><span><?php xl('Save Data','e'); ?></span></a>
					</td>
					<td class="wmtLabel" style="vertical-align:top;float:right;margin-right: 45px">
						<a class="css_button" onClick="onCancelPage(event)" tabindex="-1" href="<?php echo $cancel_url ?>" <?php if(!$GLOBALS['concurrent_layout']) echo 'target="Main"'; ?> ><span><?php xl('Exit','e'); ?></span></a>
					</td>
				</tr>
			</table>
			<!-- End of Buttons -->
			
		</div>
		<input type="hidden" name="tmp_disp_mode" id="tmp_disp_mode" value="<?php echo isset($_GET['allrto']) ? 'allrto' : ''; ?>" tabindex="-1" />
		</form>
		<form method='post' action="<?php echo $filter_url ?>" name='form_rto_filter'>
			<input type="hidden" name="f_rto_action" id="f_rto_action" value="<?php echo $f_rto_action; ?>" />
			<input type="hidden" name="f_rto_date" id="f_rto_date" value="<?php echo getFormatedDate($currentFormateDate, $f_rto_date) ?>" />
			<input type="hidden" name="f_rto_id" id="f_rto_id" value="<?php echo $f_rto_date ?>" />
			<input type="hidden" name="f_rto_status" id="f_rto_status" value="<?php echo $f_rto_status ?>" />
			<input type="hidden" name="f_case_id" id="f_case_id" value="<?php echo $f_case_id ?>" />
			<input type="hidden" name="pageno" id="f_pageno" value="<?php echo $pageno ?>" />
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

<?php if($newordermode == false) { ?>
Calendar.setup({inputField:"rto_date", ifFormat:"<?php echo $date_fmt; ?>", button:"img_rto_dt"});
Calendar.setup({inputField:"rto_target_date", ifFormat:"<?php echo $date_fmt; ?>", button:"img_rto_target_dt"});
Calendar.setup({inputField:"rto_stop_date", ifFormat:"<?php echo $date_fmt; ?>", button:"img_rto_stop_dt"});
<?php } ?>

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


	//Init Detect Form Change
	validateFormChange('<?php echo $rto_base64 ?>');

	$('[name="form_rto"] :input').change(function() {
	   validateFormChange('<?php echo $rto_base64 ?>');
	});

});

function filterSubmit(page = '', section = '') {
	var rto_filter = document.forms.form_rto_filter;
	//rto_filter.f_rto_action.value = document.getElementById('tmp_rto_action').value;
	rto_filter.f_rto_date.value = document.getElementById('tmp_rto_date').value;
	rto_filter.f_rto_id.value = document.getElementById('tmp_rto_id').value;
	//rto_filter.f_rto_status.value = document.getElementById('tmp_rto_status').value;
	rto_filter.f_case_id.value = document.getElementById('tmp_case_id').value;

	rto_filter.f_rto_action.value = JSON.stringify($('#tmp_rto_action').val());
	rto_filter.f_rto_status.value= JSON.stringify($('#tmp_rto_status').val());

	if(page != '') {
		rto_filter.pageno.value = page;
	}

	if(section != '') {
		rto_filter.action = rto_filter.action + section;
	}

	rto_filter.submit();
}

function addOrderBtn() {
	//changePage('<?php //echo $rto_page_details['total_pages'] ?>', 'addOrderSection');
	var url = '<?php echo $GLOBALS['webroot']."/interface/forms/rto1/new_order.php" ?>?&pid='+'<?php echo $pid; ?>'+'&frmdir='+'<?php echo $frmdir; ?>'+'&newordermode=true&pop='+'<?php echo $popmode; ?>';
	dlgopen(url,'addOrderPop', 700, 300, '', 'New Order');
}

function jQFormSerializeArrToJson(formSerializeArr){
	var jsonObj = {};
	jQuery.map( formSerializeArr, function( n, i ) {
		jsonObj[n.name] = n.value;
	});
	return jsonObj;
}

function validateRtoItemChange(item = [], formData = [], cnt = 0) {
	var itemChangeStatus = true;
	/*var fieldMappingList = {
		'rto_action' : 'rto_action',
		'rto_ordered_by' : 'rto_ordered_by',
		'rto_notes' : 'rto_notes',
		'rto_case' : 'rto_case',
		'rto_status' : 'rto_status',
		'rto_resp_user' : 'rto_resp',
		'rto_frame' : 'rto_frame',
		'rto_target_date' : 'rto_target_date',
		'rto_date' : 'rto_date',
		'rto_stop_date' : 'rto_stop_date'
	};*/

	var fieldMappingList = {
		'rto_action' : 'rto_action',
		'rto_ordered_by' : 'rto_ordered_by',
		'rto_notes' : 'rto_notes',
		'rto_case' : 'rto_case',
		'rto_status' : 'rto_status',
		'rto_resp_user' : 'rto_resp',
		'rto_frame' : 'rto_frame',
		'rto_stat' : 'rto_stat'
	};
	var dateFields = ['rto_date', 'rto_target_date', 'rto_stop_date'];
	var checkboxFields = ['rto_stat'];

	$.each(fieldMappingList, function(field, mappingField) {

		if(!item.hasOwnProperty(field) || !formData.hasOwnProperty(mappingField + '_' +cnt)) {
			if(checkboxFields.includes(field)) {
	    		formData[mappingField + '_' +cnt] = 0;
	    	}
	    }

		if(item.hasOwnProperty(field) && formData.hasOwnProperty(mappingField + '_' +cnt)) {
			var rtoField = item[field] ? item[field] : "";
	    	var formField = formData[mappingField + '_' +cnt] !== "" ? formData[mappingField + '_' +cnt] : "";

	    	if(dateFields.includes(field)) {
	    		if(rtoField) {
		    		rField = new Date(rtoField);
		    		rField.setHours(0,0,0,0);
		    		rtoField = rField.getTime();
	    		}

	    		if(formField) {
		    		fField = new Date(formField);
		    		fField.setHours(0,0,0,0);
		    		formField = fField.getTime();
	    		}
	    	}

	    	if(rtoField != formField) {
	    		itemChangeStatus = false;
	    	}
		}
	});

	return itemChangeStatus;
}

function validateFormChange(data) {
	var decodedString = atob(data);
	var rtoJSON = decodedString != "" ? JSON.parse(decodedString) : {};

	var formData = $('[name="form_rto"]').serializeArray();
	var formJSONData = jQFormSerializeArrToJson(formData);
	var formChangeStatus = true;

	if($.isArray(rtoJSON)) {
		jQuery.each(rtoJSON, function(index, item) {
			var cnt = (index + 1);
			var formStatus = validateRtoItemChange(item, formJSONData, cnt);
			var uBtn = $('#update_btn_'+cnt);

			if(formStatus === false) {
				formChangeStatus = formStatus;
				if(uBtn) {
					uBtn.addClass("updateBtn");
				}
			} else {
				if(uBtn) {
					uBtn.removeClass("updateBtn");
				}
			}
		});
	}

	return formChangeStatus;
}

function onCancelPage(event) {
	var formChangeStatus = validateFormChange('<?php echo $rto_base64 ?>');
	if(formChangeStatus === false) {
		if(!showPageLeavingConfirmBox()) {
			event.preventDefault();	
		}
	}
}

function showPageLeavingConfirmBox() {
	return confirm("Do you want to leave this page without saving?");
}

// Event listener for on tab close
window.addEventListener("tabCloseEvent", function(event) {
	var tabTitle = event.detail.data && event.detail.data.title() ? event.detail.data.title() : "";
	if(tabTitle == "Order Entry") {
		var formChangeStatus = validateFormChange('<?php echo $rto_base64 ?>');
    	if(formChangeStatus === false) {
    		if(!showPageLeavingConfirmBox()) {
    			event.preventDefault();	
    		}
    	}
	}
});

</script>
</html>
