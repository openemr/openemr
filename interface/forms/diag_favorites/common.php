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
require_once("{$GLOBALS['srcdir']}/api.inc");
include_once("{$GLOBALS['srcdir']}/wmt-v2/wmtstandard.inc");
include_once("{$GLOBALS['srcdir']}/wmt-v2/diag_favorites.inc");

use OpenEMR\Core\Header;
use OpenEMR\Common\Acl\AclMain;

/* INITIALIZE FORM DEFAULTS */
$cancel_url = $rootdir.'/patient_file/encounter/encounter_top.php';
$form_title = 'Diagnosis Favorites Maintenance';
$form_table = 'wmt_diag_fav';
$save_url = $rootdir.'/forms/diag_favorites/new.php';
$abort_url = $rootdir.'/patient_file/summary/demographics.php';
$base_action = $rootdir.'/forms/diag_favorites/new.php';
$default_global = checkSettingMode('wmt::diag_fav_globals');
$dt = array();
$flds = sqlListFields('wmt_diag_fav');
$flds = array_slice($flds,3);
$ctype = 'ICD10';
$mode = '';
if(isset($_GET['ctype'])) $ctype = strip_tags($_GET['ctype']);
if(isset($_POST['form_ctype'])) $ctype = strip_tags($_POST['form_ctype']);
if(isset($_GET['mode'])) $mode = strip_tags($_GET['mode']);
$save_url .= "?ctype=$ctype";
$base_action .= "?ctype=$ctype";
$continue = false;
$allow_user_edit = AclMain::aclCheckCore('admin','super');
if(isset($_GET['continue'])) $continue = strip_tags($_GET['continue']);
foreach($flds as $key => $fld) { 
	$dt[$fld]='';
}
foreach($_POST as $key => $val) {
	$dt[$key] = $val;
}

if($mode == 'save') {
	// Update any other existing favorites in case they changed
	$cnt=1;
	while($cnt <= $dt['tmp_favorites_cnt']) {
		if(!isset($dt['global_'.$cnt])) $dt['global_'.$cnt] = '0';
		if(!isset($dt['modifier_'.$cnt])) $dt['modifier_'.$cnt] = '';
		UpdateDiagFavorite($dt['id_'.$cnt],$ctype,$dt['code_'.$cnt], 
			$dt['title_'.$cnt],$dt['seq_'.$cnt],'',
			$dt['global_'.$cnt],$dt['grp_'.$cnt],$dt['modifier_'.$cnt]);
		$cnt++;
	}
	if(!isset($dt['global_list'])) $dt['global_list'] = '0';
	if(!isset($dt['modifier'])) $dt['modifier'] = '';
	$test = AddDiagFavorite($ctype,$dt['code'],$dt['title'],$dt['seq'],
			$_SESSION['authUser'],$dt['global_list'],$dt['grp'],$dt['modifier']);
	$dt['code'] = $dt['title'] = $dt['seq'] = '';
	$dt['global_list'] = $dt['modifier'] = '';

} else if($mode == 'addfav') {
	// Update any other existing favorites in case they changed
	$cnt=1;
	while($cnt <= $dt['tmp_favorites_cnt']) {
		if(!isset($dt['global_'.$cnt])) $dt['global_'.$cnt] = '0';
		if(!isset($dt['modifier_'.$cnt])) $dt['modifier_'.$cnt] = '';
		UpdateDiagFavorite($dt['id_'.$cnt],$ctype,$dt['code_'.$cnt],
			$dt['title_'.$cnt],$dt['seq_'.$cnt],'',
			$dt['global_'.$cnt],$dt['grp_'.$cnt],$dt['modifier_'.$cnt]);
		$cnt++;
	}

	if(!isset($dt['global_list'])) $dt['global_list'] = '';
	if(!isset($dt['modifier'])) $dt['modifier'] = '';
	$test = AddDiagFavorite($ctype, $dt['code'], $dt['title'], $dt['seq'], 
		$_SESSION['authUser'], $dt['global_list'], $dt['grp'], $dt['modifier']);
	$dt['code'] = $dt['title'] = $dt['seq'] = '';
	$dt['global_list'] = $dt['grp'] = $dt['modifier'] = '';

} else if($mode == 'updatefav') {
	$cnt = trim($_GET['itemID']);
	if(!isset($dt['global_'.$cnt])) $dt['global_'.$cnt] = '0';
	if(!isset($dt['modifier_'.$cnt])) $dt['modifier_'.$cnt] = '';
	UpdateDiagFavorite($dt['id_'.$cnt], $ctype, $dt['code_'.$cnt],
		$dt['title_'.$cnt], $dt['seq_'.$cnt],'',
		$dt['global_'.$cnt],$dt['grp_'.$cnt],$dt['modifier_'.$cnt]);

} else if($mode == 'delfav') {
	$cnt = trim($_GET['itemID']);
	DeleteDiagFavorite($dt['id_'.$cnt]);
}

/* RETRIEVE FAVORITES */
$favorites = array();
$header_desc = "Current Favorites for $ctype Codes";
$form_focus = 'code';
$favorites = getAllDiagFavorites($ctype);
$form_focus = 'title';
$list_type = 'Procedure_Categories';
if($ctype == 'ICD10') $list_type = 'Diagnosis_Categories';

?>
<!DOCTYPE HTML>
<html>
	<head>
		<title><?php echo $form_title ?></title>

		<link rel="stylesheet" href="<?php echo $GLOBALS['css_header'];?>" type="text/css">
		<link rel="stylesheet" href="<?php echo $GLOBALS['webroot'];?>/library/wmt/wmt.default.css" type="text/css">

		<?php Header::setupHeader(['datetime-picker', 'jquery', 'jquery-ui', 'datatables', 'datatables-colreorder', 'datatables-dt', 'datatables-bs', 'oemr_ad']); ?>
		<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt/wmtcalendar.js.php"></script>

		<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/wmtstandard.js"></script>
		<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/wmtpopup.js"></script>

		<script>
			var mypcc = '<?php echo $GLOBALS['phone_country_code']; ?>';

			function set_cpt(codetype, code, codedesc, fee, codefield, descfield) {
 				var f = document.forms[0];
 				var s = f.elements[codefield].value;
 				var numargs = arguments.length;
 				if (code != '') {
  				f.elements[codefield].value = code;
  				f.elements[descfield].value = codedesc;
					return true;
 				} else {
  				f.elements[codefield].value = '';
  				f.elements[descfield].value = '';
					return false;
 				}
			}

			function get_cpt(cptField) {
 			var numargs = arguments.length;
 			var srch = document.forms[0].elements[cptField].value;
 			var target = '../../../custom/cpt_code_popup.php?fav=off&thiscpt='+cptField;
 			if(srch != '') target += '&bn_search=1&search_term='+srch;
 			if(numargs > 1) {
				if(arguments[1] != '') target += '&thisdesc='+arguments[1];
 			}
 			if(numargs > 2) {
				if(arguments[2] != '') target += '&thisfee='+arguments[2];
 			}
 			if(numargs > 3) {
				if(arguments[3] != '') target += '&thistype='+arguments[3];
				if(arguments[3] != '') target += '&codetype='+arguments[3];
 			}
 			wmtOpen(target, '_blank', 700, 800);
			}

			// This invokes the find-code popup.
			function get_diagnosis(diagField)
			{
 				var numargs = arguments.length;
 				var srch = document.forms[0].elements[diagField].value;
 				var target = '../../../custom/diag_code_popup.php?codetype=<?php echo $ctype; ?>&thisdiag='+diagField+'&fav=off';
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
 				wmtOpen(target, '_blank', 500, 600);
			}

			// This is for callback by the find-code popup.
			// Appends to or erases the current list of diagnoses.
			function set_diag(codetype, code, selector, codedesc, codefield)
			{
 				if (code) {
  				document.forms[0].elements[codefield].value = code;
  				document.forms[0].elements['title'].value = codedesc;
					new_action= document.forms[0].action+'&code='+code+'&mode=addfav';
					document.forms[0].action= new_action;
					// document.forms[0].submit();
					return true;
 				} else {
  				document.forms[0].elements[codefield].value = '';
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

			// confirm cancel
			function cancelClicked() {
				return true;
			}

			// process save
			function saveClicked(mode) {
				new_action= document.forms[0].action+'&code='+document.forms[0].elements['code'].value+'&mode=addfav';
				if(mode == 'continue') new_action = new_action + '&continue=continue';
				document.forms[0].action= new_action;
				document.forms[0].submit();
 			}

			function refreshCodes() {
				document.forms[0].submit();
			}

		</script>
	</head>

<body class="wmtFormBodyLight" style="margin: 8px;">
<form method='post' action="<?php echo $save_url; ?>" name='favorites'> 

<!-- May want to adjust div background based on mode -->
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td class="wmtLabel" style="text-align: right;">Code Type:&nbsp;</td>
		<td style="text-align: left;"><select name="form_ctype" id="form_ctype" class="wmtHeaderInput" onchange="refreshCodes();">
			<option value="CPT4" <?php echo $ctype == 'CPT4' ? 'selected="selected"' : ''; ?>>CPT4</option>
			<option value="HCPCS" <?php echo $ctype == 'HCPCS' ? 'selected="selected"' : ''; ?>>HCPCS</option>
			<option value="ICD10" <?php echo $ctype == 'ICD10' ? 'selected="selected"' : ''; ?>>ICD10</option>
		</select></td>
	</tr>
</table>
<br>
<div class="wmtMainContainer bgWhite" style="/*background-color: white;*/ margin: 0px; padding 0px; white-space: normal;">

	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td colspan="6" class="wmtCollapseBar wmtBorder1B wmtC wmtChapter"><?php echo $header_desc; ?></td>
		</tr> 
		<tr>
			<td class="wmtLabel wmtBorder1B wmtBorder1R wmtDateCell">Code</td>
			<td class="wmtLabel wmtBorder1B wmtBorder1R" style="width: 15%;">Category</td>
			<td class="wmtLabel wmtBorder1B wmtBorder1R">Title / Description</td>
			<td class="wmtLabel wmtBorder1B wmtBorder1R wmtDateCell">Sequence</td>
			<td class="wmtLabel wmtBorder1B wmtBorder1R wmtC" style="width: 60px;" >Global</td>
			<td class="wmtLabel wmtBorder1B" style="width: 125px;">&nbsp;</td>
		</tr>
		<?php
		if(isset($favorites) && (count($favorites) > 0)) {
			$cnt=0;
			foreach($favorites as $list) {
				$cnt++;
		?>
		<tr>
			<td class="wmtBody wmtBorder1B wmtBorder1R"><input type="hidden" name="id_<?php echo $cnt; ?>" id="id_<?php echo $cnt; ?>" tabindex="-1" value="<?php echo $list['id']; ?>" /><input name="code_<?php echo $cnt; ?>" id="code_<?php echo $cnt; ?>" type="text" class="wmtAltInput wmtFullInput" readonly="readonly" tabindex="-1" value="<?php echo htmlspecialchars($list['code'],ENT_QUOTES,'',FALSE); ?>" /></td>
			<td class="wmtBody wmtBorder1B wmtBorder1R"><select name="grp_<?php echo $cnt; ?>" id="grp_<?php echo $cnt; ?>" class="wmtAltInput wmtFullInput">
				<?php ListSel($list['grp'],$list_type); ?>
			</select></td>
			<td class="wmtBody wmtBorder1B wmtBorder1R"><input name="title_<?php echo $cnt; ?>" id="title_<?php echo $cnt; ?>" type="text" class="wmtAltInput wmtFullInput" style="width: 100%;" value="<?php echo htmlspecialchars($list['title'],ENT_QUOTES,'',FALSE); ?>" /></td>
			<td class="wmtBody wmtBorder1B wmtBorder1R"><input name="seq_<?php echo $cnt; ?>" id="seq_<?php echo $cnt; ?>" type="text" class="wmtAltInput wmtFullInput" value="<?php echo htmlspecialchars($list['seq'],ENT_QUOTES,'',FALSE); ?>" /></td>
			<td class="wmtBody wmtBorder1B wmtBorder1R wmtC"><input name="global_<?php echo $cnt; ?>" id="global_<?php echo $cnt; ?>" type="checkbox" class="wmtAltInput" value="1" <?php echo $list['global_list'] ? 'checked="checked"' : ''; ?> /></td>
			<td class="wmtBody wmtBorder1B btnActContainer">
				<div style="float: left; padding-left: 5px;"><a class="css_button_small" href="javascript:update_favorite('<?php echo $cnt; ?>');"><span>Update</span></a></div>
				<div style="float: left; padding-left: 5px; "><a class="css_button_small" href="javascript:delete_favorite('<?php echo $cnt; ?>');"><span>Delete</span></a></div>
			</td>
		</tr>
		<?php
			}
		}
		?>
		<tr>
			<td class="wmtBody wmtBorder1R"><input name="code" id="code" type="text" class="wmtAltInput wmtFullInput" value="<?php echo htmlspecialchars($dt['code'],ENT_QUOTES,'',FALSE); ?>" onClick="<?php echo $ctype == 'ICD10' ? "get_diagnosis('code', 'title');" : "get_cpt('code', 'title', '', '$ctype');"; ?>" title="Click to Open The Code Search Box" /></td>
			<td class="wmtBody wmtBorder1R wmtM"><select name="grp" id="grp" class="wmtAltInput wmtFullInput">
				<?php ListSel($dt['grp'],$list_type); ?>
			</select></td>
			<td class="wmtBody wmtBorder1R" style="vertical-align: middle;"><input name="title" id="title" type="text" class="wmtAltInput wmtFullInput" value="<?php echo htmlspecialchars($dt['title'],ENT_QUOTES,'',FALSE); ?>" /></td>
			<td class="wmtBody wmtBorder1R"><input name="seq" id="seq" type="text" class="wmtAltInput wmtFullInput" value="<?php echo htmlspecialchars($dt['seq'],ENT_QUOTES,'',FALSE); ?>" /></td>
			<td class="wmtBody wmtBorder1R wmtC"><input name="global_list" id="global_list" type="checkbox" class="wmtAltInput" value="1" <?php echo $default_global ? 'checked="checked"' : ''; ?> /></td>
			<td class="wmtBody">&nbsp;</td>
		</tr>
	</table>
<input type="hidden" name="tmp_favorites_cnt" id="tmp_favorites_cnt" value="<?php echo $cnt; ?>" tabindex="-1" />
</div>
<div style="float: left; margin-left: 0px;">
	<a class="css_button" href="javascript:saveClicked('continue')"><span>Save / Add Another</span></a></div>
<!--
<div style="float: right; margin-right: 10px;">
	<a class="css_button" href="javascript:saveClicked('exit')"><span>Save & Exit</span></a></div>
<div style="float: right; padding-right: 10px;"><a class="css_button" href="<?php // echo $cancel_url; ?>"><span>Cancel</span></a></div>
-->

</form>
</body>

</html>
