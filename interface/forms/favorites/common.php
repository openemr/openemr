<?php
/** **************************************************************************
 *	favorites/common.php
 *
 *	Copyright (c)2012 - Williams Medical Technology, Inc.
 *
 *	This file contains the standard screen processing used for picking and
 *	editing the doctor's plan favorites list.
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
 *  @subpackage favorites
 *  @version 1.0
 *  @copyright Williams Medical Technologies, Inc.
 *  @author Rich Genandt <rgenandt@gmail.com>
 * 
 *************************************************************************** */
require_once("../../globals.php");
require_once("{$GLOBALS['srcdir']}/options.inc.php");
require_once("{$GLOBALS['srcdir']}/api.inc");
include_once("{$GLOBALS['srcdir']}/wmt-v2/wmtstandard.inc");
include_once("{$GLOBALS['srcdir']}/wmt-v2/favorites.inc");

use OpenEMR\Core\Header;

/* INITIALIZE FORM DEFAULTS */
$cancel_url = $rootdir.'/patient_file/encounter/encounter_top.php';
$choose = false;
$form_title = 'Diagnosis Plan/Favorites Maintenance';
$form_table = 'wmt_plan_fav';
$save_url = $rootdir.'/forms/favorites/new.php';
$abort_url = $rootdir.'/patient_file/summary/demographics.php';
$base_action = $rootdir.'/forms/favorites/new.php';
$dt = array();
$flds = sqlListFields('wmt_plan_fav');
$flds = array_slice($flds,3);
$wrap_mode = 'new';
$mode = 'new';
$title_cols = '8';
$type = 'plan';
if(isset($_GET['mode'])) $mode = strip_tags($_GET['mode']);
if(isset($_GET['wrap'])) $wrap_mode = strip_tags($_GET['wrap']);
if(isset($_GET['type'])) $type = strip_tags($_GET['type']);
if($type == 'goal') $form_title = 'Diagnosis Goals Maintenance';
if(isset($_GET['choose'])) { 
	$choose= strtolower(strip_tags($_GET['choose']));
	$mode= 'choose';
	$cancel_url= 'javascript:window.close();';
	$title_cols='2';
	$form_title= 'Diagnosis Plan/Favorites Selection';
	if($type == 'goal') $form_title = 'Diagnosis Goals Selection';
}
$ctype='ICD10';
if(isset($GLOBALS['wmt::default_diag_type'])) 
									$ctype = $GLOBALS['wmt::default_diag_type'];
if(isset($_GET['ctype'])) $ctype=strip_tags($_GET['ctype']);
$save_url .= "?ctype=$ctype&type=$type";
$base_action .= "?ctype=$ctype&type=$type";
$dt['tmp_rpt_disp_mode'] = 'block';
if(isset($_GET['rdisp'])) $dt['tmp_rpt_disp_mode'] = strip_tags($_GET['rdisp']);
$save_url .= "&rdisp=".$dt['tmp_rpt_disp_mode'];
$base_action .= "&rdisp=".$dt['tmp_rpt_disp_mode'];
$code = '';
if(isset($_GET['code'])) $code = strip_tags($_GET['code']);
$target = '';
if(isset($_GET['target'])) $target = strip_tags($_GET['target']);
foreach($flds as $key => $fld) {
	$dt[$fld]='';
}
foreach($_POST as $key => $val) {
	$dt[$key] = htmlspecialchars($val,ENT_QUOTES,'UTF-8',false);
	// echo "This Key: $key  --> Value: $val<br>\n";
}
// echo "Code Type is Set: $ctype<br>\n";
// echo "Code is Set: $code<br>\n";
// echo "Save URL is Set: $save_url<br>\n";
// echo "Base Action is Set: $base_action<br>\n";

$client_id = checkSettingMode('wmt::client_id');

if($mode == 'save') {
	// Update any other existing favorites in case they changed
	$cnt=1;
	while($cnt <= $dt['tmp_favorites_cnt']) {
		if(!isset($dt['fav_global_'.$cnt])) { $dt['fav_global_'.$cnt] = '0'; }
		UpdateFavorite($dt['fav_id_'.$cnt],$ctype,$dt['fav_code_'.$cnt],
			$dt['fav_plan_'.$cnt],$dt['fav_title_'.$cnt],$dt['fav_seq_'.$cnt],
			$dt['fav_notes_'.$cnt], $_SESSION['authUser'],$dt['fav_global_'.$cnt]);
		$cnt++;
	}
	$test = AddFavorite($ctype,$dt['code'],$dt['plan'],$dt['title'],$dt['seq'],
			$dt['notes'],$_SESSION['authUser'],$dt['global_list'],$type);
	$dt['code']=$dt['plan']=$dt['title']=$dt['seq']=$dt['notes']='';
	$dt['global_list']='';

	// if($choose) {
	// 	echo "<html>\n";
	// 	echo "<head>\n";
	// 	echo "<title>Redirecting....</title>\n";
	// 	echo "<script language='javascript'>window.close();</script>\n";
	// 	echo "</head>\n";
	// 	echo "</html>\n";
	// 	exit;
	// } else {
		// formJump($cancel_url);
	// }
} else if($mode == 'addfav') {
	// Update any other existing favorites in case they changed
	$cnt=1;
	while($cnt <= $dt['tmp_favorites_cnt']) {
		if(!isset($dt['fav_global_'.$cnt])) { $dt['fav_global_'.$cnt] = '0'; }
		UpdateFavorite($dt['fav_id_'.$cnt],$ctype,$dt['fav_code_'.$cnt],
			$dt['fav_plan_'.$cnt],$dt['fav_title_'.$cnt],$dt['fav_seq_'.$cnt],
			$dt['fav_notes_'.$cnt], $_SESSION['authUser'], $dt['fav_global_'.$cnt]);
		$cnt++;
	}

	$test = AddFavorite($ctype, $dt['code'], $dt['plan'], $dt['title'],
			$dt['seq'],$dt['notes'],$_SESSION['authUser'],$dt['global_list'], $type);
	$dt['code']=$dt['plan']=$dt['title']=$dt['seq']=$dt['notes']='';
	$dt['global_list']='';

} else if($mode == 'updatefav') {
	$cnt=trim($_GET['itemID']);
	if(!isset($dt['fav_global_'.$cnt])) { $dt['fav_global_'.$cnt] = '0'; }
	UpdateFavorite($dt['fav_id_'.$cnt], $ctype, $dt['fav_code_'.$cnt],
		$dt['fav_plan_'.$cnt], $dt['fav_title_'.$cnt], $dt['fav_seq_'.$cnt],
		$dt['fav_notes_'.$cnt], $_SESSION['authUser'],$dt['fav_global_'.$cnt]);

} else if($mode == 'delfav') {
	$cnt=trim($_GET['itemID']);
	DeleteFavorite($dt['fav_id_'.$cnt]);

} else if($mode == 'choose') {
	// Not necessary yet
} else if($mode == 'new') {
	// Not necessary yet
} else {
	echo "** Fatal Error - called with unknown mode [$mode]<br/>\n";
	exit;

}

/* RETRIEVE FAVORITES */
$favorites = array();
$tmp_header_desc = "Plan Maintenance for $ctype Codes";
$form_focus = 'code';
if(isset($_GET['all'])) {
	// echo "All Should NOT Be Set<br>\n";
	$favorites = getAllFavorites($ctype, $type);
} else if($code) {
	// echo "Going to Load Favorites for Code: $code<br>\n";
	$favorites = getFavoritesByCode($ctype, $code, $type);
	// foreach($favorites as $list) {
			// print_r($list);
			// echo "<br>\n";
	// }
	$_desc = GetDiagDescription($ctype.':'.$code);
	if(strlen($_desc) > 30) $_desc = substr($_desc,0,30). '...';
	$tmp_header_desc = ucfirst($type).'s for '.$ctype.': '.$code.' - '. $_desc;
	$form_focus= 'title';
}
// GET THE SUMMARY REPORT OF DIAGS WITH FAVORITES 
$sql = "SELECT code, seq FROM wmt_plan_fav WHERE code_type=? AND ".
	"(list_user=? OR global_list=?) AND `type` = ? GROUP BY code ORDER BY code";
 
$res = sqlStatement($sql, array($ctype, $_SESSION['authUser'], 1, $type));
$rpt= array();
for($iter=0; $row=sqlFetchArray($res); $iter++) $rpt[] = $row;
$cnt=0;

?>
<!DOCTYPE HTML>
<html>
	<head>
		<title><?php echo $form_title ?></title>

		<link rel="stylesheet" href="<?php echo $GLOBALS['css_header'];?>" type="text/css">

		<?php Header::setupHeader(['datetime-picker', 'jquery', 'jquery-ui', 'dialog']); ?>

		<link rel="stylesheet" href="<?php echo $GLOBALS['webroot'];?>/library/wmt/wmt.default.css" type="text/css">
<?php if($v_major > 4) { ?>
		<!-- <script type="text/javascript" src="<?php //echo $GLOBALS['assets_static_relative']; ?>/jquery-min-1-11-3/index.js"></script>
		<script type="text/javascript" src="<?php //echo $GLOBALS['assets_static_relative']; ?>/jquery-ui-1-11-4/jquery-ui.min.js"></script> -->
<?php } else { ?>
		<!-- <script type="text/javascript" src="<?php //echo $GLOBALS['webroot']; ?>/library/js/jquery.1.3.2.js"></script>
		<script type="text/javascript" src="<?php //echo $GLOBALS['webroot']; ?>/library/js/jquery-ui.js"></script>
		<script type="text/javascript" src="<?php //echo $GLOBALS['webroot']; ?>/library/js/jquery-ui-1.7.1.custom.min.js"></script> -->
<?php } ?>
		<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/js/common.js"></script>
		<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/textformat.js"></script>
		<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/wmtstandard.js"></script>
		<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/favorites.js"></script>
		<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/wmtpopup.js"></script>
		<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/dialog.js"></script>

		<script>
			var mypcc = '<?php echo $GLOBALS['phone_country_code']; ?>';

			// This invokes the find-code popup.
			function get_diagnosis(diagField)
			{
 				var numargs = arguments.length;
 				var srch = document.forms[0].elements[diagField].value;
 				var target = '../../../custom/diag_code_popup.php?codetype=<?php echo $ctype; ?>&thisdiag='+diagField;
 				if(srch != '') {
					target= target+'&bn_search=1&search_term='+srch;
 				}
 				if(numargs >= 2) {
					target = target+'&thisdesc='+arguments[1];
 				}
 				if(numargs >= 3) {
					target = target+'&thisdate='+arguments[2];
 				}
 				if(numargs >= 4) {
					target = target+'&nextfocus='+arguments[3];
 				}
 				dlgopen(target, '_blank', 500, 600);
			}

			// This is for callback by the find-code popup.
			// Appends to or erases the current list of diagnoses.
			function set_diag(codetype, code, selector, codedesc, codefield)
			{
 				if (code) {
  				document.forms[0].elements[codefield].value = code;
					new_action= document.forms[0].action+'&code='+code+'&mode=addfav';
					document.forms[0].action= new_action;
					document.forms[0].submit();
					return true;
 				} else {
  				document.forms[0].elements[codefield].value = '';
					return false;
 				}
			}

			function clear_form()
			{
  			document.forms[0].elements['code'].value = '';
				new_action= document.forms[0].action+'&mode=new';
				document.forms[0].action= new_action;
				document.forms[0].submit();
				return true;
			}

			function display_this_diag(this_code)
			{
  			var tst_code = document.forms[0].elements['code'].value;
				if(tst_code) {
					new_action= document.forms[0].action+'&code='+tst_code+'&mode=addfav';
					document.forms[0].action= new_action;
					document.forms[0].submit();
					return true;
				} else {
					return false;
				}
			}

			function DisplayThis(base_action, disp_code)
			{
				if(disp_code) {
					var new_action= base_action+'&code='+disp_code;
					document.forms[0].action= new_action;
					document.forms[0].submit();
					return true;
				} else {
					return false;
				}
			}

			function update_favorite(itemID)
			{
  			var tst_code = document.forms[0].elements['code'].value;
				if(isNaN(itemID) || itemID == 0) {
					alert("No Update - A Valid Favorite Item ID Was Not Included");
					return false;
				} else {
					new_action= document.forms[0].action+'&code='+tst_code+'&mode=updatefav&itemID='+itemID;
					document.forms[0].action= new_action;
					document.forms[0].submit();
					return true;
				}
			}

			function delete_favorite(itemID)
			{
  			var tst_code = document.forms[0].elements['code'].value;
				if(isNaN(itemID) || itemID == 0) {
					alert("No Delete - A Valid Favorite Item ID Was Not Included");
					return false;
				} else {
					new_action= document.forms[0].action+'&code='+tst_code+'&mode=delfav&itemID='+itemID;
					document.forms[0].action= new_action;
					document.forms[0].submit();
					return true;
				}
			}

			// return a plan
			function setFavorite(field,plan) {
				if(opener.closed || !opener.set_plan) {
					alert('The destination form was closed, unable to attach this plan!');
				} else {
					opener.set_plan(field,plan);
					window.close();
				}
			}
				
			// confirm cancel
			function cancelClicked() {
				response = confirm("Are you sure you wish to discard your changes? Click the 'OK' button to discard all of the changes you have made to this form or click the 'Cancel' button to continue working on this form.");
				if (response == true) {
					<?php
					if(!$choose) { echo "			top.restoreSession();\n"; }
					?>
					return true;
				}
				else {
					return false;
				}
			}

			// process save
			function saveClicked() {
				<?php
				if(!$choose) { echo "			top.restoreSession();\n"; }
				?>
				new_action= document.forms[0].action+'&code='+document.forms[0].elements['code'].value+'&mode=addfav';
				document.forms[0].action= new_action;
				document.forms[0].submit();
 			}

		</script>
	</head>

	<body <?php if(!$choose) echo "onLoad='AdjustFocus(\"{$form_focus}\");'"; ?> >

		<form method='post' action="<?php echo $save_url ?>" name='favorites'> 
		<br/>
		<div style='padding-left: 5px; padding-right: 5px;'>
			<?php if(!$code) { ?>
			<input type="hidden" name="title" id="title" value="" tabindex="-1" />
			<input type="hidden" name="seq" id="seq" value="" tabindex="-1" />
			<input type="hidden" name="global_list" id="global_list" value="" tabindex="-1" />
			<input type="hidden" name="plan" id="plan" value="" tabindex="-1" />
			<input type="hidden" name="tmp_favorites_cnt" id="tmp_favorites_cnt" value="" tabindex="-1" />
			<?php } ?>
			<?php if(!$choose) { ?>	
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
				<tr>
					<td class="wmtLabel" style="width: 100%; text-align: center;">Select A Code:&nbsp;&nbsp;<input name="code" id="code" type="text" value="<?php echo $code; ?>" onClick="get_diagnosis('code');" onChange="display_this_diag('code');" title="Click to Open The Diagnosis Search Box" /></td>
				</tr>
			</table>
			<br>
			<?php } ?>
			<!-- Start of Favorites List -->
			<!-- May want to adjust div background based on mode -->
			<div class="wmtMainContainer" style="background-color: white;">
				<table width="100%" border="0" cellspacing="0" cellpadding="0">
					<tr class="wmtColorBar">
						<td style="border-bottom: solid 1px black; width: 100%;"><b>
							<?php echo $tmp_header_desc; ?></b>
						</td>
					</tr>
				</table>
				<table width="100%" border="0" cellspacing="0" cellpadding="0">
					<?php
					if(isset($favorites) && (count($favorites) > 0)) {
						$cnt=0;
						foreach($favorites as $list) {
							$cnt++;
							// Simple view hyperlinked for when choosing a plan
							if($choose) {
								$getPlan = base64_encode($list['plan']);
								$anchor = "<a href='javascript:;' onclick='return setFavorite(\"$target\",\"$getPlan\")'>";
								echo "			<tr>\n";
								echo "				<td class='wmtLabel wmtT' style='width: 10%; border-top: solid 1px black;'>$cnt.&nbsp;)</a></td>\n";
								echo "				<td class='wmtBody' style='border-top: solid 1px black;'>$anchor",$list['plan'],"</a></td>\n";
								echo "			</tr>\n";
							// Full edit mode of all fields
							} else {
								echo "			<tr>\n";
								echo "				<td class='wmtBody' style='width: 80px;'><input type='hidden' name='fav_id_$cnt' id='fav_id_$cnt' tabindex='-1' value='".$list['id']."' /><input type='hidden' name='fav_code_$cnt' id='fav_code_$cnt' tabindex='-1' value='$code' /><input type='hidden' name='fav_notes_$cnt' id='fav_notes_$cnt' tabindex='-1' value='".$list['note']."' />Title:</td>\n";
								echo "				<td><input name='fav_title_$cnt' id='fav_title_$cnt' type='text' class='wmtFullInput' value='".$list['title']."' /></td>\n";
								echo "				<td class='wmtBody'>&nbsp;&nbsp;Sequence:</td>\n";
								echo "				<td style='width: 80px;'><input name='fav_seq_$cnt' id='fav_seq_$cnt' type='text' style='width: 80px;' class='wmtInput' value='".$list['seq']."' /></td>\n";
								echo "				<td class='wmtBody' style='border-left: solid 1px black; padding-left: 5px; width: 120px;'><input name='fav_global_$cnt' id='fav_global_$cnt' type='checkbox' value='1' ".(($list['global_list'] == 1)?' checked ':'')." />Global Plan</td>\n";
								echo "			</tr>\n";
								echo "			<tr>\n";
								echo "				<td class='wmtBody wmtT' style='border-bottom: solid 1px black;' rowspan='4'>Plan:</td>\n";
								echo "				<td colspan='3' style='border-bottom: solid 1px black;' rowspan='4'><textarea name='fav_plan_$cnt' id='fav_plan_$cnt' class='wmtFullInput' rows='4'>".$list['plan']."</textarea></td>\n";
								echo "				<td style='border-left: solid 1px black;'>&nbsp;</td>\n";
								echo "			</tr>\n";
								echo "				<td style='border-left: solid 1px black;'><div style='float: left; padding-left: 5px;'><a class='css_button' href='javascript:update_favorite(\"$cnt\")'><span>Update</span></a></div></td>\n";
								echo "			</tr>\n";
								echo "			<tr>\n";
								echo "				<td style='border-left: solid 1px black;'>&nbsp;</td>\n";
								echo "			</tr>\n";
								echo "			<tr>\n";
								echo "				<td style='border-left: solid 1px black; border-bottom: solid 1px black;'><div style='float: left; padding-left: 5px; padding-bottom: 5px;'><a class='css_button' href='javascript:delete_favorite(\"$cnt\")'><span>Delete</span></a></div></td>\n";
								echo "			</tr>\n";
							}
						}
					// This else is for when there are no items (favorites) for this code
					} else {
						// This is for the mode where we are just selecting
						if($choose) {
							echo "<tr><td colspan='2' style='border-top: solid 1px black;'>&nbsp;</td></tr>\n";
							echo "<tr><td colspan='2'>&nbsp;</td></tr>\n";
							echo "<tr><td colspan='2' style='text-align: center;'>No Entries for $ctype Code: <b>$code</b> on File</td></tr>\n";
							echo "<tr><td colspan='2'>&nbsp;</td></tr>\n";
							echo "<tr><td colspan='2'>&nbsp;</td></tr>\n";
						// This is not used currently, can add a plan below
						// This would just show that there are no plans for this diag
						// currently if it contained anything
						} else {
						}
					}
					// Inputs to add a new plan 
					if(!$choose) {
						if($code) {
							echo "			<tr>\n";
							echo "				<td class='wmtBody' style='width: 80px; '><input type='hidden' name='note' id='note' value='' tabindex='-1' />Title:</td>\n";
							echo "				<td><input name='title' id='title' type='text' class='wmtFullInput' value='".$dt['title']."' /></td>\n";
							echo "				<td class='wmtBody' style='width: 80px;'>Sequence:</td>\n";
							echo "				<td style='width: 80px;'><input name='seq' id='seq' type='text' class='wmtInput' style='width: 80px;' value='".$dt['seq']."' /></td>\n";
							echo "				<td class='wmtBody' style='width: 120px; border-left: solid 1px black;'><input name='global_list' id='global_list' type='checkbox' value='1' ".(($dt['global_list'] == 1)?' checked ':'')." />&nbsp;&nbsp;Global Plan</td>\n";
							echo "			</tr>\n";
							echo "			<tr>\n";
							echo "				<td class='wmtBody wmtT' rowspan='4'>Plan:</td>\n";
							echo "				<td colspan='3' rowspan='4'><textarea name='plan' id='plan' class='wmtFullInput' rows='4'>".$dt['plan']."</textarea></td>\n";
							echo "				<td rowspan='4' style='border-left: solid 1px black;'>&nbsp;</td>\n";
							echo "			</tr>\n";
						// If we don't have a code there is nothing to do until next pass
						} else {
							echo "			<tr><td>&nbsp;</td></tr>\n";
							echo "			<tr>\n";
							echo "			<td style='width: 100%; text-align: center;' class='wmtLabel'>Select a Diagnosis Code to See Favorites</td>\n";
							echo "			</tr>\n";
							echo "			<tr><td>&nbsp;</td></tr>\n";
						}
					}
					?>
				</table>
				<input type="hidden" name="tmp_favorites_cnt" id="tmp_favorites_cnt" value="<?php echo $cnt; ?>" tabindex="-1" />
			</div>
			
			<table width="100%" border="0">
				<tr>
					<td class="Label" style="vertical-align:top;float:left;margin-left: 10px;">
				<?php
				if($choose) {
				?>
					&nbsp;</td>
					<td>&nbsp;</td>
					<td class="Label" style="vertical-align:top;float:right;margin-right: 45px">
						<a class="css_button" tabindex="-1" href="<?php echo $cancel_url ?>"><span>Exit</span></a>
					</td>
				<?php
				} else {
				?>
					<a class="css_button" href="javascript:saveClicked()"><span>Save / Add Another</span></a></td>
					<td><div style="float: right; padding-right: 10px;"><a class="css_button" href="javascript:clear_form()"><span>Choose Another Code</span></a></div></td>
				<?php } ?>
				</tr>
			</table>
		<!-- End of Buttons -->
			<br/>

		<!-- Summary window showing diags with plans -->
		<?php if(!$choose) { ?>
			<div class="wmtMainContainer">
  			<div class="wmtCollapseBar" id="FavRptCollapseBar" style="border-bottom: <?php echo (($dt['tmp_rpt_disp_mode']=='block')?'solid 1px black':'none'); ?>" onclick="togglePanel('FavRptBox','FavRptImageL','FavRptImageR','FavRptCollapseBar','','tmp_rpt_disp_mode')">
    			<table width="100%" border="0" cellspacing="0" cellpadding="0">
    			<tr>
					<?php 
					if($dt['tmp_rpt_disp_mode']=='block') {
      			echo "<td><img id='FavRptImageL' src='../../../library/wmt/fill-090.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
    				echo "<td class='wmtChapter'>Diagnoses with Defined Plans</td>\n";
    				echo "<td style='text-align: right'><img id='FavRptImageR' src='../../../library/wmt/fill-090.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
					} else {
      			echo "<td><img id='FavRptImageL' src='../../../library/wmt/fill-270.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
    				echo "<td class='wmtChapter'>Diagnoses with Defined Plans</td>\n";
    				echo "<td style='text-align: right'><img id='FavRptImageR' src='../../../library/wmt/fill-270.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
					}
					?>
					</tr>
    			</table> </div><!-- End of the HPI Collapse Bar -->
  			<div id="FavRptBox" class="CollapseBoxWhite" style="display: <?php echo (($dt['tmp_rpt_disp_mode']=='block')?'block':'none'); ?>">
    			<table width="100%" border="0" cellspacing="0" cellpadding="0">
						<?php
						if(count($rpt) > 0) {
							echo "<tr>\n";
							echo "<td class='wmtLabel'>&nbsp;&nbsp;Code</td>\n";
							echo "<td class='wmtLabel'>Description</td>\n";
							echo "<td class='wmtLabel wmtC'>Plans</td>\n";
							echo "</tr>\n";
							foreach($rpt as $line) {
								$sql = "SELECT COUNT(*) FROM wmt_plan_fav WHERE code_type=? ".
									"AND (list_user=? OR global_list=?) AND code=? AND type=?";
								$parms = array($ctype, $_SESSION['authUser'], 1, $line['code'], $type);
								$res = sqlStatement($sql, $parms);
								$fres = sqlFetchArray($res);
								$_code = $line['code'];
								$_num = $fres['COUNT(*)'];
								$_desc = GetDiagDescription($ctype.':'.$line['code']);
								if(strlen($_desc) > 80) { $_desc=substr($_desc, 0, 80).'...'; }
								$anchor = "<a href='javascript:;' onclick='DisplayThis(\"$base_action\",\"$_code\")'>";
								echo "<tr>\n";
								echo "<td class='wmtBody'>&nbsp;&nbsp;$anchor$_code</a></td>\n";
								echo "<td class='wmtBody'>$_desc</td>\n";
								echo "<td class='wmtBody wmtC'>$_num</td>\n";
								echo "</tr>\n";
							}
						} else {
							echo "<tr><td>&nbsp;</td></tr>\n";
							echo "<tr><td style='text-align: center;'><b>No Plans Are Currently Defined For Any Diagnoses</b></td></tr>\n";
							echo "<tr><td>&nbsp;</td></tr>\n";
						}
						?>
					</table>
				</div>
			</div>
			<?php } ?>
			<input name="tmp_rpt_disp_mode" id="tmp_rpt_disp_mode" type="hidden" tabindex="-1" value="<?php echo $dt['tmp_rpt_disp_mode']; ?>" />

			
		</div>
	</form>
	</body>

</html>
