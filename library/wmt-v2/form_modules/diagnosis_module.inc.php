<?php
include_once($GLOBALS['srcdir'].'/wmt-v2/amc_ed.php');

// THIS IS ALL THE BASIC SETUP STUFF
$local_fields = array('dg_seq', 'dg_code', 'dg_begdt', 'dg_enddt', 'dg_title',
	'dg_type', 'tmp_dg_desc', 'dg_plan', 'dg_goal');
foreach($local_fields as $fld) {
	if(!isset($dt[$fld . '_0'])) $dt[$fld. '_0'] = '';
}

if(!isset($target_container)) $target_container = '';
if(!isset($portal_mode)) $portal_mode = FALSE;
if(!isset($first_pass)) $first_pass = FALSE;
if(!isset($draw_display)) $draw_display = TRUE;
if(!$portal_mode) include_once($GLOBALS['fileroot'].'/custom/code_types.inc.php');
if(!isset($show_unlink)) 
	$show_unlink = (acl_check('patients', 'med') || acl_check('super', 'admin'));
if(!isset($frmdir)) {
	echo "Warning - The Diagnosis Window Include has no form directory!<br>\n";
	$frmdir = '';
}
if($frmdir == 'dashboard') $show_unlink = FALSE;

if(!isset($GLOBALS['wmt::use_plan_favorites'])) 
						$GLOBALS['wmt::use_plan_favorites'] = TRUE;
if(!isset($GLOBALS['wmt::link_diag_education'])) 
						$GLOBALS['wmt::link_diag_education'] = FALSE;
$use_favorites = $GLOBALS['wmt::use_plan_favorites'];
$prefix = 'dg_';
$list_type = 'medical_problem';
$list_desc = 'diagnosis';
if($frmdir == 'dashboard') $use_sequence = FALSE;
if($diag_use_ajax) $diag_use_checkbox = FALSE;
// SET THESE FOR THE BUTTON BUILDERS - THIS WILL HAPPEN IN EACH MODULE NOW
$module_tag = 'diag';
$item_id_tag = 'dg_id_';
$module_desc = 'Diagnosis';
$suppress_class = '';
$suppress_class = ($suppress_plan) ? 'wmtBorder1B ' : '';
// BUILD THE BUTTONS HERE TO MAKE IT MORE LEGIBLE BELOW
// echo "Encounter: $encounter  Mode:: ",$dt['tmp_diag_window_mode'],"<br>\n";
if($diag_use_ajax) {
	$base_action = $GLOBALS['rootdir']."/forms/$frmdir/save.php?mode=save&enc=$encounter&pid=$pid&wrap=$wrap_mode";
	if($id) $base_action .= "&id=$id";
	$link_btn = $GLOBALS['srcdir'].'/wmt-v2/btn_link_ajax.inc.php';
	$unlink_btn = $GLOBALS['srcdir'].'/wmt-v2/btn_unlink_ajax.inc.php';
	$show_all_btn = "<a class='css_button' tabindex='-1' onclick='return ajaxIssueRefresh(\"$encounter\",\"$pid\",\"$frmdir\",\"$id\",\"medical_problem\",\"all\",\"Diagnoses / Problems\",\"$target_container\",\"dg_\",\"$wrap_mode\",\"".$dt['tmp_diag_window_mode']."\");' href='javascript:;'><span>Show All Diagnoses</span></a>";
	$show_curr_btn = "<a class='css_button' tabindex='-1' onclick='return ajaxIssueRefresh(\"$encounter\",\"$pid\",\"$frmdir\",\"$id\",\"medical_problem\",\"current\",\"Diagnoses / Problems\",\"$target_container\",\"dg_\",\"$wrap_mode\",\"".$dt['tmp_diag_window_mode']."\");' href='javascript:;'><span>Show Current Diagnoses</span></a>";
	$show_enc_btn = "<a class='css_button' tabindex='-1' onclick='return ajaxIssueRefresh(\"$encounter\",\"$pid\",\"$frmdir\",\"$id\",\"medical_problem\",\"encounter\",\"Diagnoses / Problems\",\"$target_container\",\"dg_\",\"$wrap_mode\",\"".$dt['tmp_diag_window_mode']."\");' href='javascript:;'><span>Only This Encounter</span></a>";
	$add_btn = "<a class='css_button' tabindex='-1' onclick='return ajaxIssueRefresh(\"$encounter\",\"$pid\",\"$frmdir\",\"$id\",\"medical_problem\",\"add\",\"Diagnosis / Problems\",\"$target_container\",\"dg_\",\"$wrap_mode\",\"".$dt['tmp_diag_window_mode']."\");' href='javascript:;'><span>Add Another</span></a>";
	$add_new_plan_btn = "<a class='css_button_small' tabindex='-1' onclick='return ajaxSubmitPlan(\"dg_type\",\"dg_code\",\"dg_plan\",\"plan\");' href='javascript:;'><span>Save Plan</span></a>";
	$add_new_goal_btn = "<a class='css_button_small' tabindex='-1' onclick='return ajaxSubmitPlan(\"dg_type\",\"dg_code\",\"dg_goal\",\"goal\");' href='javascript:;'><span>Save Goal</span></a>";
	$unlink_all_btn = "<div style='float: right;'><a class='css_button' tabindex='-1' id='dg_link_all_btn' onclick='return ajaxIssueLinkAll(\"dg_\",\"$encounter\",\"$pid\",\"unlink\",\"medical_problem\",\"tmp_diag_cnt\");' href='javascript:;'><span>Unlink ALL Diagnoses</span></a></div>";
} else {
	$max = count($diag);
	$link_btn = $GLOBALS['srcdir'].'/wmt-v2/btn_link.inc.php';
	$unlink_btn = $GLOBALS['srcdir'].'/wmt-v2/btn_unlink.inc.php';
	$show_all_btn = "<a class='css_button' tabindex='-1' onclick='return ToggleDiagWindowMode(\"$base_action\",\"$wrap_mode\",\"$id\",\"all\");' href='javascript:;'><span>Show All Diagnoses</span></a>";
	$show_curr_btn = "<a class='css_button' tabindex='-1' onclick='return ToggleDiagWindowMode(\"$base_action\",\"$wrap_mode\",\"$id\",\"current\");' href='javascript:;'><span>Show Current Diagnoses</span></a>";
	$show_enc_btn = "<a class='css_button' tabindex='-1' onclick='return ToggleDiagWindowMode(\"$base_action\",\"$wrap_mode\",\"$id\",\"encounter\");' href='javascript:;'><span>Only This Encounter</span></a>";
	$add_btn = "<a class='css_button' tabindex='-1' onclick='return AddDiagnosis(\"$base_action\",\"$wrap_mode\",\"$id\");' href='javascript:;'><span>Add Another</span></a>";
	$add_new_plan_btn = "<a class='css_button_small' tabindex='-1' onclick='return SubmitFavorite(\"$base_action\",\"$wrap_mode\",\"0\",\"$id\",\"dg_code\",\"dg_plan\",\"dg_type\",\"plan\");' href='javascript:;'><span>Save Plan</span></a>";
	$add_new_goal_btn = "<a class='css_button_small' tabindex='-1' onclick='return SubmitFavorite(\"$base_action\",\"$wrap_mode\",\"0\",\"$id\",\"dg_code\",\"dg_goal\",\"dg_type\",\"goal\");' href='javascript:;'><span>Save Goal</span></a>";
	$unlink_all_btn = "<div style='float: right;'><a class='css_button' tabindex='-1' onclick='return UnlinkAllDiagnoses(\"$base_action\",\"$wrap_mode\",\"$max\",\"$id\");' href='javascript:;'><span>Unlink ALL Diagnoses</span></a></div>\n";
}
// END OF THE SETUP 

if($form_mode == 'new') {
} else if($form_mode == 'update') {
}
// WE CAN GET PROBLEMS LIKE THIS ALWAYS
if(!isset($dt['tmp_diag_window_mode'])) $dt['tmp_diag_window_mode'] = 'encounter';
$diag = GetProblemsWithDiags($pid, $dt['tmp_diag_window_mode'], $encounter);


if($draw_display) {
	$diag_use_checkbox = checkSettingMode('wmt::diag_use_checkbox','',$frmdir);
	$suppress_plan = checkSettingMode('wmt::suppress_diag_plan','',$frmdir);
	$suppress_goal = checkSettingMode('wmt::suppress_diag_goal','',$frmdir);
	$diag_use_ajax = checkSettingMode('wmt::diag_use_ajax','',$frmdir);
	$use_sequence = checkSettingMode('wmt::diag_use_sequence','',$frmdir);
?>
<table width='100%' border='0' cellspacing='0' cellpadding='0' style='border-collapse: collapse;'>
	<tr>
		<td class='wmtLabel wmtBorder1B' style='width: 60px;'><?php echo ($use_sequence) ? 'Seq #' : '&nbsp;'; ?></td>
		<td class='wmtLabel wmtBorder1L wmtBorder1B' style='width: 85px;'>Diagnosis</td>
		<td class='wmtLabel wmtBorder1L wmtBorder1B' style='width: 85px;'>Start Date</td>
		<td class='wmtLabel wmtBorder1L wmtBorder1B' style='width: 85px;'>End Date</td>
		<td class='wmtLabel wmtBorder1L wmtBorder1B'>Title</td>
<?php if($show_unlink || !$suppress_plan || !$suppress_goal) { ?>
		<td class='wmtLabel wmtBorder1L wmtBorder1B'>Description</td>
		<td class='wmtLabel wmtBorder1B' style='width: 90px; padding: 0px; margin: 0px;'>&nbsp;</td>
<?php } else { ?>
		<td class='wmtLabel wmtBorder1B' style='padding-right: 0px;'>Description</td>
<?php } ?>
	</tr>
<?php
$cnt=1;
//  echo "Diag Array Here: ";
//  print_r($diag);
//  echo "<br>\n";
//  echo "Count: ",count($diag),"<br>\n";
//  echo "Window Mode: (".$dt['tmp_diag_window_mode'].")<br>\n";

if(count($diag) > 0) {
	foreach($diag as $prev) {
		// IF MULTIPLE DIAGS ARE ATTACHED IN OEMR THEY AER IN A SEMI-COLON 
		// DELIMITED LIST. THIS IS REALLY ONLY RELEVANT FOR NON-WMT SYSTEMS.
		// WE'LL USE THE FIRST BUT KEEP THE REMAINDER TO PUT BACK WHEN WE UPDATE.
		$remainder='';
		$code_type='';
		if($dt['tmp_diag_window_mode'] == 'current') {
			if($prev['enddate']) continue;
		}
		if($dt['tmp_diag_window_mode'] == 'encounter') {
			if($prev['encounter'] != $encounter) continue;
		}
		if($pos = strpos($prev['diagnosis'],';')) {
			$remainder = trim(substr($prev['diagnosis'],($pos+1)));
			$prev['diagnosis'] = trim(substr($prev['diagnosis'],0,$pos));
		}
		if($pos = strpos($prev['diagnosis'],':')) {
			// IMPORTANT - KEEP THE TYPE IN A HIDDEN FIELD TO PUT IT BACK
			$code_type = trim(substr($prev['diagnosis'],0,$pos));
			$prev['diagnosis'] = trim(substr($prev['diagnosis'],($pos+1)));
		}
		$desc = lookup_code_descriptions($code_type.':'.$prev['diagnosis']);

		if($first_pass && $form_mode == 'new' && !$prev['seq'] && $use_sequence) {
			$seq = GetDiagnosisSequence($pid, $encounter);
			SequenceDiagnosis($pid, $prev['id'], $encounter, $seq);
			$prev['seq'] = $seq;
		}
   	echo "	<tr>\n";
		echo "		<td class='$suppress_class wmtLabel'><input name='dg_id_$cnt' id='dg_id_$cnt' type='hidden' readonly='readonly' value='".$prev['id']."' />&nbsp;";
		if($use_sequence) {
			echo "<input name='dg_seq_$cnt' id='dg_seq_$cnt' class='wmtInput' type='text' style='width: 40px;' value='".htmlspecialchars($prev['seq'],ENT_QUOTES)."' /></td>\n";
		} else {
			echo $cnt. "&nbsp;)&nbsp;</td>\n";
		}
		echo "		<td";
		echo $suppress_class ? ' class="'.$suppress_class.'"' : '';
		echo "><input name='dg_code_$cnt' id='dg_code_$cnt' class='wmtFullInput' type='text' value='".htmlspecialchars($prev['diagnosis'],ENT_QUOTES)."' onClick='get_diagnosis(\"dg_code_$cnt\",\"tmp_dg_desc_$cnt\",\"dg_begdt_$cnt\",\"dg_title_$cnt\",\"dg_type_$cnt\");' ";
		echo "title='Click to select or clear a diagnosis' /></td>\n";
		echo "		<td"; 
		echo "><input name='dg_begdt_$cnt' id='dg_begdt_$cnt' class='wmtFullInput' type='text' value='".htmlspecialchars($prev['begdate'],ENT_QUOTES)."' /></td>\n";
		echo "		<td";
		echo $suppress_class ? ' class="'.$suppress_class.'"' : '';
		echo "><input name='dg_enddt_$cnt' id='dg_enddt_$cnt' class='wmtFullInput' type='text' value='".htmlspecialchars($prev['enddate'],ENT_QUOTES)."' /></td>\n";
		echo "		<td";
		echo $suppress_class ? ' class="'.$suppress_class.'"' : '';
		echo "><input name='dg_title_$cnt' id='dg_title_$cnt' class='wmtFullInput' type='text' readonly='readonly' value='".htmlspecialchars($prev['title'],ENT_QUOTES)."' /></td>\n";
		echo "		<td";
		echo $suppress_class ? ' class="'.$suppress_class.'"' : '';
		echo "><input name='tmp_dg_desc_$cnt' id='tmp_dg_desc_$cnt' class='wmtFullInput' type='text' tabindex='-1' value='".htmlspecialchars($desc,ENT_QUOTES)."'";
		if($GLOBALS['wmt::link_diag_education']) {
			$href = $GLOBALS['webroot'].'/interface/patient_file/education_frames.php?';
			$href .= 'type='.$code_type.'&code='.$prev['diagnosis'].'&source=MLP';
			if(strtolower($patient->language) == 'spanish') $href .= '&language=es';
			$href .= '&auto=TRUE';
			echo " style='cursor: pointer; color: blue;'";
			echo " title='Click to Open Patient Education'";
			echo " onclick=\"wmtOpen('$href', '_blank', 800, 600);\"";
		}
		echo " />";

		echo "			<input name='dg_remain_$cnt' id='dg_remain_$cnt' type='hidden' value='$remainder' /><input name='dg_type_$cnt' id='dg_type_$cnt' type='hidden' value='$code_type' />";
		echo "</td>\n";
		if(($show_unlink && !$suppress_plan) || !$show_unlink) {
			echo "		<td class='wmtBody wmtBorder1L'>&nbsp;</td>\n";
		}
		// UGLY - BUT IF THE PLAN IS SUPPRESSED WE HAVE TO SHOW THE UN-LINK CHOICE
		// HERE ON THE SAME LINE.  THE CODE FOR THE BUTTONS IS DUPLICATED BELOW.
		if($show_unlink && $suppress_plan) {
			echo "		<td class='wmtBorder1L $suppress_class'>\n";
			if($diag_use_checkbox) {
				if(!isset($dt['dg_link_'.$cnt])) {
					$dt['dg_link_'.$cnt] = '';
				}
				if($first_pass || $form_mode == 'window' || $form_mode == 'diag') {
					if($prev['encounter'] == $encounter) $dt['dg_link_'.$cnt] = 1;
				}
				echo "<input name='dg_link_$cnt' id='dg_link_$cnt' type='checkbox' value='1' ";
				echo ($dt['dg_link_'.$cnt]) ? 'checked' : '';
				echo " /><label for='dg_link_$cnt' class='wmtBody'>&nbsp;Linked&nbsp;</label>";
			} else {
				if($prev['encounter'] == $encounter) {
					$btn_mode = 'unlinkdiag';
					include($unlink_btn);
				} else {
					$btn_mode = 'linkdiag';
					include($link_btn);
				}
			}
			echo "</td>\n";
		}
		echo "	</tr>\n";

		if($GLOBALS['wmt::link_diag_education']) {
			$link_type = ($frmdir == 'dashboard') ? $frmdir : 'form_encounter';
			$link_id = ($frmdir == 'dashboard') ? 0 : $encounter;
			if($frmdir == 'dashboard') {
				$ed_print_title = $ed_portal_title = 'Not Provided';
				$ed_printed = amcEdMostRecent($pid, 
					'patient_edu_amc', $code_type . ':' . $prev['diagnosis'], 'print');
				if(!isset($ed_printed['id'])) $ed_printed['id'] = '';
				if($ed_printed['id']) {
					if($ed_printed['date'] == '0000-00-00') $ed_printed['date'] = '';
					$ed_print_title = 'Last Printed By ' . 
						$ed_printed['full_name'] . ' On ' . $ed_printed['date'];
				}
				$ed_portal = amcEdMostRecent($pid, 
					'patient_edu_amc', $code_type . ':' . $prev['diagnosis'], 'portal');
				if(!isset($ed_portal['id'])) $ed_portal['id'] = '';
				if($ed_portal['id']) {
					if($ed_portal['date'] == '0000-00-00') $ed_portal['date'] = '';
					$ed_portal_title = 'Last Sent to the Portal By ' . 
						$ed_portal['full_name'] . ' On ' . $ed_portal['date'];
				}
			}
			echo "<tr>\n";
			echo "<td class='wmtBody' colspan='4'>\n";
			echo "&nbsp;&nbsp;&nbsp;<a style='cursor: pointer; color: blue;' ";
			echo "title='Click to Open Patient Education' ";
			echo "onclick=\"wmtOpen('$href', '_blank', 800, 600);\" >";
			echo "Click to View Patient Education</a></td>";
			echo "<td><input name='tmp_dg_ed_prn_$cnt' id='tmp_dg_ed_prn_$cnt' type='checkbox' value='1' ";
			echo ($prev['print_dt'] != '' && $prev['print_dt'] != '0000-00-00') ? 'checked ' : '';
			echo "onChange=\"SetPatEd('" . $GLOBALS['webroot'] . "', '$pid', this, '$link_type', '$link_id', 'dg_code_$cnt', 'dg_type_$cnt', 'print', '$patient->language');\"";
			echo " /><label for='tmp_dg_ed_prn_$cnt' class='wmtBody'";
			if($frmdir == 'dashboard') echo "title='$ed_print_title' ";
			echo ">&nbsp;Printed &amp; Given to Patient&nbsp;</label>";
			if($frmdir == 'dashboard' && $ed_printed['id']) 
				echo "&nbsp;&nbsp;<span class='wmtBody' style='color: blue;' title='" .
					$ed_print_title . "'>[&nbsp;Hover for Recent&nbsp;]</span>";
			echo "</td>";
			echo "<td class='wmtBorder1R'><input name='tmp_dg_ed_sent_$cnt' id='tmp_dg_ed_sent_$cnt' type='checkbox' value='1' ";
			echo ($prev['portal_dt'] != '' && $prev['portal_dt'] != '0000-00-00') ? 'checked ' : '';
			echo "onChange=\"SetPatEd('" . $GLOBALS['webroot'] . "', '$pid', this, '$link_type', '$link_id', 'dg_code_$cnt', 'dg_type_$cnt', 'portal', '$patient->language');\"";
			echo " /><label for='tmp_dg_ed_sent_$cnt' class='wmtBody'";
			if($frmdir == 'dashboard') echo "title='$ed_portal_title' ";
			echo ">&nbsp;Sent to Portal&nbsp;</label>";
			if($frmdir == 'dashboard' && $ed_portal['id']) 
				echo "&nbsp;&nbsp;<span class='wmtBody' style='color: blue;' title='" .
					$ed_portal_title . "'>[&nbsp;Hover for Recent&nbsp;]</span>";
			echo "</td>\n";
			echo "</tr>\n";
		}

		if(!$suppress_plan) {
			echo "	<tr>\n";
			echo "		<td class='wmtBody wmtT wmtR wmtBorder1B'>Plan:";
			echo "</br></br><div style='float: right; padding-right: 5px;'><a href='javascript:;' onClick='GetPlan(\"dg_plan_$cnt\",\"dg_code_$cnt\",\"dg_type_$cnt\");' class='css_button_small' tabindex='-1' title='Select a plan for this diagnosis from Favorites'><span>Plans</span></a>";
			echo "</td>\n";
			echo "		<td colspan='5' class='wmtBorder1B'><textarea name='dg_plan_$cnt' id='dg_plan_$cnt' class='wmtFullInput'>",htmlspecialchars($prev['comments'],ENT_QUOTES),"</textarea></td>\n";
		
			echo "		<td class='wmtLabel wmtBorder1L wmtBorder1B' style='vertical-align: top'>";
			if($show_unlink) {
				if($diag_use_checkbox) {
					if(!isset($dt['dg_link_'.$cnt])) {
						$dt['dg_link_'.$cnt] = '';
						// echo "Was NOT Set\n";
					}
					if($first_pass || $form_mode == 'window' || $form_mode == 'diag') {
						if($prev['encounter'] == $encounter) $dt['dg_link_'.$cnt] = 1;
						// echo "Setting To Set $encounter [".$prev['encounter']."\n";
					}
					echo "<input name='dg_link_".$cnt."' id='dg_link_".$cnt."' type='checkbox' value='1' ";
					echo ($dt['dg_link_'.$cnt]) ? 'checked' : '';
					echo " /><label for='dg_link_".$cnt."'>&nbsp;Linked&nbsp;</label>";
				} else {
					if($prev['encounter'] == $encounter) {
						$btn_mode = 'unlinkdiag';
						include($unlink_btn);
					} else {
						$btn_mode = 'linkdiag';
						include($link_btn);
					}
					echo "<br>";
				}
			}
			echo "<div style='float: left; padding-top: 5px;'><a class='css_button_small' tabindex='-1' ";
			if($diag_use_ajax) {
				echo "onclick='return ajaxSubmitPlan(\"dg_type_$cnt\",\"dg_code_$cnt\",\"dg_plan_$cnt\");' href='javascript:;'><span>Save Plan</span></a>";
			} else {
				echo "<a class='css_button_small' tabindex='-1' onclick='return SubmitFavorite(\"$base_action\",\"$wrap_mode\",\"$cnt\",\"$id\",\"dg_code_$cnt\",\"dg_plan_$cnt\",\"dg_type_$cnt\");' href='javascript:;'><span>Save Plan</span></a>";
			}
			echo "</div></td>\n";
			echo "	</tr>\n";
		// IMPORTANT - PLAN IS SUPPRESSED BUT CARRY THROUGH FOR DATA INDEGRITY
		} else {
			echo "		<input name='dg_plan_$cnt' id='dg_plan_$cnt' tabindex='-1' type='hidden' value='".htmlspecialchars($prev['comments'],ENT_QUOTES)."' />\n";
		}

		if(!$suppress_goal) {
			echo "	<tr>\n";
			echo "		<td class='wmtBody wmtT wmtR wmtBorder1B'>Plan:";
			echo "</br></br><div style='float: right; padding-right: 5px;'><a href='javascript:;' onClick='GetPlan(\"dg_plan_$cnt\",\"dg_code_$cnt\",\"dg_type_$cnt\",\"".$GLOBALS['webroot']."\",\"goal\");' class='css_button_small' tabindex='-1' title='Select a plan for this diagnosis from Favorites'><span>Plans</span></a>";
			echo "</td>\n";
			echo "		<td colspan='5' class='wmtBorder1B'><textarea name='dg_plan_$cnt' id='dg_plan_$cnt' class='wmtFullInput'>",htmlspecialchars($prev['comments'],ENT_QUOTES),"</textarea></td>\n";
		
			echo "		<td class='wmtLabel wmtBorder1L wmtBorder1B' style='vertical-align: top'>";
			if($show_unlink) {
				if($diag_use_checkbox) {
					if(!isset($dt['dg_link_'.$cnt])) {
						$dt['dg_link_'.$cnt] = '';
						// echo "Was NOT Set\n";
					}
					if($first_pass || $form_mode == 'window' || $form_mode == 'diag') {
						if($prev['encounter'] == $encounter) $dt['dg_link_'.$cnt] = 1;
						// echo "Setting To Set $encounter [".$prev['encounter']."\n";
					}
					echo "<input name='dg_link_".$cnt."' id='dg_link_".$cnt."' type='checkbox' value='1' ";
					echo ($dt['dg_link_'.$cnt]) ? 'checked' : '';
					echo " /><label for='dg_link_".$cnt."'>&nbsp;Linked&nbsp;</label>";
				} else {
					if($prev['encounter'] == $encounter) {
						$btn_mode = 'unlinkdiag';
						include($unlink_btn);
					} else {
						$btn_mode = 'linkdiag';
						include($link_btn);
					}
					echo "<br>";
				}
			}
			echo "<div style='float: left; padding-top: 5px;'><a class='css_button_small' tabindex='-1' ";
			if($diag_use_ajax) {
				echo "onclick='return ajaxSubmitPlan(\"dg_type_$cnt\",\"dg_code_$cnt\",\"dg_plan_$cnt\");' href='javascript:;'><span>Save Plan</span></a>";
			} else {
				echo "<a class='css_button_small' tabindex='-1' onclick='return SubmitFavorite(\"$base_action\",\"$wrap_mode\",\"$cnt\",\"$id\",\"dg_code_$cnt\",\"dg_plan_$cnt\",\"dg_type_$cnt\");' href='javascript:;'><span>Save Plan</span></a>";
			}
			echo "</div></td>\n";
			echo "	</tr>\n";
		// IMPORTANT - PLAN IS SUPPRESSED BUT CARRY THROUGH FOR DATA INTEGRITY
		} else {
			echo "		<input name='dg_plan_$cnt' id='dg_plan_$cnt' tabindex='-1' type='hidden' value='".htmlspecialchars($prev['comments'],ENT_QUOTES)."' />\n";
		}
		$cnt++;
	}
}

// HERE ARE THE INPUTS FOR THE NEW DIAGNOSIS
echo "	<tr>\n";
if($use_sequence) {
	echo "		<td class='wmtLabel wmtR $suppress_class'>#)<input name='dg_seq_0' id='dg_seq_0' class='wmtInput' type='text' style='width: 35px;' value='",htmlspecialchars($dt{'dg_seq_0'},ENT_QUOTES),"' /></td>\n";
} else {
	echo "		<td class='wmtLabel $suppress_class'>&nbsp;$cnt&nbsp;).&nbsp;</td>\n";
}
if($suppress_class) $suppress_class = "class='wmtBorder1B'";
$suppress_class = '';
echo "		<td $suppress_class><input name='dg_code_0' id='dg_code_0' class='wmtFullInput' type='text' value='",htmlspecialchars($dt{'dg_code_0'},ENT_QUOTES),"' onClick='get_diagnosis(\"dg_code_0\",\"tmp_dg_desc_0\",\"dg_begdt_0\",\"dg_title_0\",\"dg_type_0\");' title='Click to select a diagnosis' /></td>\n";
echo "		<td $suppress_class><input name='dg_begdt_0' id='dg_begdt_0' class='wmtFullInput' type='text' value='",htmlspecialchars($dt{'dg_begdt_0'},ENT_QUOTES),"' title='YYYY-MM-DD' /></td>\n";
echo "		<td $suppress_class><input name='dg_enddt_0' id='dg_enddt_0' class='wmtFullInput' type='text' value='",htmlspecialchars($dt{'dg_enddt_0'},ENT_QUOTES),"' title='YYYY-MM-DD' /></td>\n";
echo "		<td $suppress_class><input name='dg_title_0' id='dg_title_0' class='wmtFullInput' type='text' value='",htmlspecialchars($dt{'dg_title_0'},ENT_QUOTES),"' title='Enter a brief description of the problem here'/></td>\n";
echo "		<td $suppress_class><input name='tmp_dg_desc_0' id='tmp_dg_desc_0' class='wmtFullInput' type='text' readonly='readonly' value='",htmlspecialchars($dt{'tmp_dg_desc_0'},ENT_QUOTES),"' /><input name='dg_type_0' id='dg_type_0' type='hidden' value='",$dt['dg_type_0']."' /></td>\n";
if($show_unlink || $use_favorites) {
	if($suppress_class) $suppress_class = 'wmtBorder1B';
	echo "		<td class='wmtBody wmtBorder1L $suppress_class'>&nbsp;</td>\n";
}
echo "	</tr>\n";
if(!$suppress_plan) {
	echo "	<tr>\n";
	echo "		<td class='wmtBody wmtT wmtR wmtBorder1B'>Plan:";
	echo "</br></br><div style='float: right; padding-right: 5px;'><a href='javascript:;' onClick='GetPlan(\"dg_plan_0\",\"dg_code_0\",\"dg_type_0\");' class='css_button_small' tabindex='-1' title='Select a plan for this diagnosis from Favorites'><span>Plans</span></a>";
	echo "</td>\n";
	echo "		<td class='wmtBorder1B' colspan='5'><textarea name='dg_plan_0' id='dg_plan_0' class='wmtFullInput'>",htmlspecialchars($dt{'dg_plan_0'},ENT_QUOTES),"</textarea></td>\n";
	echo "		<td class='wmtBody wmtBorder1L wmtBorder1B'>&nbsp;";
	echo "<br/>$add_new_plan_btn\n";
	echo "</td>\n";
	echo "	</tr>\n";
} else {
	echo "<input name='dg_plan' id='dg_plan' type='hidden' tabindex='-1' value='' />\n";
}
if(!$suppress_goal) {
	echo "	<tr>\n";
	echo "		<td class='wmtBody wmtT wmtR wmtBorder1B'>Plan:";
	echo "</br></br><div style='float: right; padding-right: 5px;'><a href='javascript:;' onClick='GetGoal(\"dg_goal_0\",\"dg_code_0\",\"dg_type_0\");' class='css_button_small' tabindex='-1' title='Select a goal template for this diagnosis from Favorites'><span>Plans</span></a>";
	echo "</td>\n";
	echo "		<td class='wmtBorder1B' colspan='5'><textarea name='dg_goal_0' id='dg_goal_0' class='wmtFullInput'>",htmlspecialchars($dt{'dg_goal_0'},ENT_QUOTES),"</textarea></td>\n";
	echo "		<td class='wmtBody wmtBorder1L wmtBorder1B'>&nbsp;";
	echo "<br/>$add_new_goal_btn\n";
	echo "</td>\n";
	echo "	</tr>\n";
} else {
	echo "<input name='dg_goal_0' id='dg_goal_0' type='hidden' tabindex='-1' value='' />\n";
}
echo "	<tr class='wmtBorder1B'>\n";
echo "		<td colspan='2' class='wmtCollapseBar'>$add_btn</td>\n";
// I DON'T THINK WE NEED THIS ANYMORE
echo "		<td class='wmtCollapseBar'><input name='tmp_diag_cnt' id='tmp_diag_cnt' type='hidden' value='".($cnt-1)."' />&nbsp;</td>\n";

if($show_unlink || $use_favorites) {
	if($dt['tmp_diag_window_mode']=='encounter') {
		echo "		<td class='wmtCollapseBar' colspan='2'>";
		echo "<div style='float: left;'>";
		echo $show_all_btn;
		echo "</div>";
		echo "<div style='float: right; padding-right: 6px;'>";
		echo $show_curr_btn;
		echo "</div></td>\n";
		echo "		<td class='wmtCollapseBar'>$unlink_all_btn</td>\n";
	} else if($dt['tmp_diag_window_mode']=='all') {
		echo "		<td class='wmtCollapseBar' colspan='2'>";
		echo "<div style='float: left;'>";
		echo $show_curr_btn;
		echo "</div>";
		if($frmdir != 'dashboard') {
			echo "<div style='float: right; padding-right: 6px;'>";
			echo $show_enc_btn;
			echo "</div></td>\n";
			echo "		<td class='wmtCollapseBar'>$unlink_all_btn</td>\n";
		} else {
			echo "		</td>\n";
			echo "		<td class='wmtCollapseBar'>&nbsp;</td>\n";
		}
	} else {
		echo "		<td class='wmtCollapseBar' colspan='2'>";
		echo "<div style='float: left;'>";
		echo $show_all_btn;
		echo "</div>";
		if($frmdir != 'dashboard') {
			echo "<div style='float: right; padding-right: 6px;'>";
			echo $show_enc_btn;
			echo "</div></td>\n";
			echo "		<td class='wmtCollapseBar'>$unlink_all_btn</td>\n";
		} else {
			echo "		</td>\n";
			echo "		<td class='wmtCollapseBar'>&nbsp;</td>\n";
		}
	}
} else {
	echo "		<td class='wmtCollapseBar' colspan='3'>&nbsp;</td>\n";
}
echo "		<td class='wmtCollapseBar' style='margin: 0px; padding: 0px;'>&nbsp;</td>\n";
echo "	</tr>\n";
echo "</table>\n";

} // END OF DRAW DISPLAY
?>
