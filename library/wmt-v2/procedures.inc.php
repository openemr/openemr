<?php
include_once($GLOBALS['srcdir'].'/billing.inc');
include_once($GLOBALS['srcdir'].'/wmt-v2/billing_tools.inc');
include_once($GLOBALS['fileroot'].'/custom/code_types.inc.php');
if(!isset($diag)) $diag = array();
if(!isset($dt['proc_seq'])) $dt['proc_seq'] = '';
if(!isset($dt['proc_code'])) $dt['proc_code'] = '';
if(!isset($dt['proc_type'])) $dt['proc_type'] = '';
if(!isset($dt['proc_title'])) $dt['proc_title'] = '';
if(!isset($dt['proc_plan'])) $dt['proc_plan'] = '';
if(!isset($dt['proc_modifier'])) $dt['proc_modifier'] = '';
if(!isset($dt['proc_units'])) $dt['proc_units'] = '';
if(!isset($dt['proc_justify'])) $dt['proc_justify'] = '';
if(!isset($target_container)) $target_container = '';
if(!(isset($first_pass))) $first_pass = FALSE;
if(!(isset($isBilled))) $isBilled = FALSE;
if(!(isset($draw_display))) $draw_display = TRUE;
if(!isset($frmdir)) {
	echo "Warning - The Procedures Window Include has no form directory!<br>\n";
	$frmdir = '';
}
if(!isset($GLOBALS['wmt::use_proc_favorites'])) 
				$GLOBALS['wmt::use_proc_favorites'] = TRUE;
if(!isset($GLOBALS['wmt::include_billing_diags']))
				$GLOBALS['wmt::include_billing_diags'] = TRUE;
$use_favorites = $GLOBALS['wmt::use_proc_favorites'];
$use_justify = checkSettingMode('wmt::use_justify','',$frmdir);

// LOAD THE DIAGNOSIS CODES FOR THE JUSTIFICATION
if($use_justify) {
	$diag = GetProblemsWithDiags($pid, 'enc', $encounter, 'ICD', true);
	if($GLOBALS['wmt::include_billing_diags']) {
		$extra = getBillingCodes($pid, $encounter, 'ct_diag = 1');
		foreach($extra as $x) {
			$used = false;
			foreach($diag as $l) {
				if($l['encounter'] != $encounter) continue;
				if($l['diagnosis'] == $x['code_type'].':'.$x['code']) $used = TRUE;
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
}

if($form_mode == 'new' || $form_mode == 'update') {

} else {
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
	// GATHER OUR SPECIFIC VARIABLE SET
	unset($procedures);
	$procedures = array();
	foreach($_POST as $key => $val) {
		if(substr($key,0,5) != 'proc_') continue;
		if(is_string($val)) $val = trim($val);
		$tmp = explode('_', $key);
		$cnt = $tmp[count($tmp)-1];
		$tmp = strrpos($key,0,$tmp);
		$key_base = substr($key,0,$tmp);
		if(strpos($key, '_date') !== FALSE) $val = DateToYYYYMMDD($val);
		$procedures[$cnt][$keybase] = $val;
	}

	foreach($procedures as $procedure) {
		AddOrUpdatePlan($pid, $encounter, $procedure['proc_type'],
			$procedure['proc_code'], $procedure['proc_modifier'], 
			$procedure['proc_plan'], $procedure['proc_title']);
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

}

// BUILD THE BUTTONS HERE TO MAKE IT MORE LEGIBLE BELOW
if($draw_display) {
	// SET THESE FOR THE BUTTON BUILDERS - THIS WILL HAPPEN IN EACH MODULE NOW
	$module_tag = 'proc';
	$item_id_tag = 'proc_id_';
	$module_desc = 'Procedure';
	$suppress_class = ($suppress_plan) ? 'wmtBorder1B ' : '';
	$show_delete = ( (\OpenEMR\Common\Acl\AclMain::aclCheckCore('patients', 'med') || 
				\OpenEMR\Common\Acl\AclMain::aclCheckCore('super', 'admin') || isDoctor() ) && !$isBilled);
	$suppress_plan = checkSettingMode('wmt::suppress_proc_plan','',$frmdir);
	$proc_use_ajax = checkSettingMode('wmt::proc_use_ajax','',$frmdir);
	if($proc_use_ajax) {
		$base_action = $GLOBALS['rootdir']."/forms/$frmdir/save.php?mode=save&enc=$encounter&pid=$pid&wrap=$wrap_mode";
		if($id) $base_action .= "&id=$id";
		$add_btn = "<a class='css_button' tabindex='-1' onclick='return ajaxIssueRefresh(\"$encounter\",\"$pid\",\"$frmdir\",\"$id\",\"procedures\",\"add\",\"Procedures\",\"$target_container\",\"proc_\",\"$wrap_mode\",\"".$dt['tmp_proc_window_mode']."\");' href='javascript:;'><span>Add Another</span></a>";
		$add_new_plan_btn = "<a class='css_button_small' tabindex='-1' onclick='return ajaxSubmitPlan(\"proc_type\",\"proc_code\",\"proc_plan\");' href='javascript:;'><span>Save Detail</span></a>";
	} else {
		$add_btn = "<a class='css_button' tabindex='-1' onclick='return AddProcedure(\"$base_action\",\"$wrap_mode\",\"$id\");' href='javascript:;'><span>Add Another</span></a>";
		$add_new_plan_btn = "<a class='css_button_small' tabindex='-1' onclick='return SubmitFavorite(\"$base_action\",\"$wrap_mode\",\"0\",\"$id\",\"proc_code\",\"proc_plan\",\"proc_type\");' href='javascript:;'><span>Save Detail</span></a>";
	}

?>
	
	<table width='100%' border='0' cellspacing='0' cellpadding='0' style='border-collapse: collapse;'>
		<tr>
			<td class='wmtLabel wmtBorder1L wmtBorder1B' style='width: 85px;'>Code</td>
			<td class='wmtLabel wmtBorder1L wmtBorder1B' style='width: 55px;'>Modifier</td>
			<td class='wmtLabel wmtBorder1L wmtBorder1B' style='width: 55px;'>Units</td>
			<?php if($use_justify) { ?>
			<td class='wmtLabel wmtBorder1L wmtBorder1B'>Justification</td>
			<?php } ?>
			<td class='wmtLabel wmtBorder1L wmtBorder1B'>Description</td>
			<td class='wmtLabel wmtBorder1B' style='width: 90px; padding: 0px; margin: 0px;'>&nbsp;</td>
		</tr>
	<?php
	$cnt=1;
	
	if(count($proc_data) > 0) {
		foreach($proc_data as $prev) {
			if(!$prev['ct_proc']) continue;
			if($prev['code_text'] == '') {
				$prev['code_text'] = $prev['title'];
				if($prev['code_text'] = '') $prev['code_text'] = 
						lookup_code_descriptions($prev['code_type'] . ':' . $prev['code']);
			}
	
   		echo "	<tr";
			echo $suppress_class ? ' class="'.$suppress_class.'"' : '';
			echo ">\n";
			echo "		<td>";
			echo "<input name='proc_bill_id_$cnt' id='proc_bill_id_$cnt' type='hidden' readonly='readonly' value='".$prev['id']."' />";
			echo "<input name='proc_code_$cnt' id='proc_code_$cnt' class='wmtFullInput' type='text' value='".htmlspecialchars($prev['code'],ENT_QUOTES,'',FALSE)."' " ;
			echo "readonly title='Click to select or clear a procedure' /></td>\n";
			echo "		<td>"; 
			echo "<input name='proc_modifier_$cnt' id='proc_modifier_$cnt' class='wmtFullInput' type='text' value='".htmlspecialchars($prev['modifier'],ENT_QUOTES,'',FALSE)."' readonly /></td>\n";
			echo "		<td>";
			echo "<input name='proc_units_$cnt' id='proc_units_$cnt' class='wmtFullInput' type='text' value='".htmlspecialchars($prev['units'],ENT_QUOTES,'',FALSE)."' /></td>\n";
			if($use_justify) {
				echo '  <td class="tightFit" style="width: 104px;" ';
				echo 'title="' , xla("Select one or more diagnosis codes to justify the service") , '" >';
				echo '<select name="proc_justify_',$cnt,'" id="proc_justify_',$cnt,'" class="wmtInput" style="width: 90px;" tabindex="-1"  onchange="setJustify(this)">';
				echo '<option value="';
				echo convertJustifyToFee($prev['justify']);
				echo '">';
				echo convertJustifyToFee($prev['justify']);
				echo '</option></select>';
				echo '</td>';
				$justinit .= "setJustify(f['proc_justify_".$cnt."']);\n";
				echo "\n";
			}
			echo "		<td>";
			// echo $suppress_class ? ' class="'.$suppress_class.'"' : '';
			echo "<input name='proc_title_$cnt' id='proc_title_$cnt' class='wmtFullInput' type='text' value='";
			echo ($prev['title'] != '') ? htmlspecialchars($prev['title'],ENT_QUOTES,'',FALSE) : htmlspecialchars($prev['code_text'],ENT_QUOTES,'',FALSE);
			echo "' />";
			echo "<input name='proc_type_$cnt' id='proc_type_$cnt' type='hidden' value='".$prev['code_type']."' />";
			echo "<input name='proc_on_fee_$cnt' id='proc_on_fee_$cnt' type='hidden' value='".$prev['proc_on_fee']."' />";
			echo "</td>\n";
			echo "		<td class='wmtBorder1L'>&nbsp;";
			if($show_delete) {
				if($proc_use_ajax) {
					echo "<a class='css_button' tabindex='-1' onclick='return ajaxIssueAction(\"$encounter\",\"$pid\",\"$frmdir\",\"$id\",\"procedures\",\"$cnt\",\"del\",\"Procedures\",\"$target_container\",\"proc_\",\"$wrap_mode\",\"".$dt['tmp_proc_window_mode']."\");' href='javascript:;'><span>Delete</span></a>";
				} else {
					echo "<a class='css_button' tabindex='-1' onclick='return DeleteProcedure(\"$base_action\",\"$wrap_mode\",\"$cnt\");' href='javascript:;'><span>Delete</span></a>";
				}
			}
			echo "</td>\n";
			// SAVE THIS IN CASE WE NEED THE ABILITY
			/**
			echo "		<td class='wmtBody wmtBorder1L'><input name='proc_on_fee_$cnt' id='proc_on_fee_$cnt' type='checkbox' value='1'";
			echo ($prev['id']) ? ' checked="checked"' : '';
			echo " /></td>\n";
			**/
			echo "	</tr>\n";
	
			if(!$suppress_plan) {
				echo "	<tr>\n";
				echo "		<td class='wmtT wmtR wmtBorder1B'>Detail:";
				echo "<br><br><div style='float: left; padding-left: 5px; margin-bottom: 3px;'><a href='javascript:;' onClick='GetPlan(\"proc_plan_$cnt\",\"proc_code_$cnt\",\"proc_type_$cnt\");' class='css_button_small' tabindex='-1' title='Select a plan for this procedure from favorites'><span>Details</span></a>";
				echo "</td>\n";
				echo "		<td colspan='";
				echo $use_justify ? 4 : 3;
				echo "' class='wmtBorder1B'><textarea name='proc_plan_$cnt' id='proc_plan_$cnt' class='wmtFullInput'>",htmlspecialchars($prev['comments'],ENT_QUOTES,'',FALSE),"</textarea></td>\n";
			
				echo "		<td class='wmtLabel wmtBorder1L wmtBorder1B' style='vertical-align: top'>";
				echo "<div style='float: left; padding-top: 5px;'><a class='css_button_small' tabindex='-1' ";
				if($proc_use_ajax) {
					echo "onclick='return ajaxSubmitPlan(\"proc_type_$cnt\",\"proc_code_$cnt\",\"proc_plan_$cnt\");' href='javascript:;'><span>Save Detail</span></a>";
				} else {
					echo "<a class='css_button_small' tabindex='-1' onclick='return SubmitFavorite(\"$base_action\",\"$wrap_mode\",\"$cnt\",\"0\",\"proc_code_$cnt\",\"proc_plan_$cnt\",\"proc_type_$cnt\",\"1\");' href='javascript:;'><span>Save Detail</span></a>";
				}
				echo "</div></td>\n";
			// IMPORTANT - PLAN IS SUPPRESSED BUT CARRY THROUGH FOR DATA INDEGRITY
			} else {
				echo "		<input name='proc_plan_$cnt' id='proc_plan_$cnt' tabindex='-1' type='hidden' value='".htmlspecialchars($prev['comments'],ENT_QUOTES,'',FALSE)."' />\n";
			}
			echo "	</tr>\n";
			$cnt++;
		}
	}
	
	// HERE ARE THE INPUTS FOR THE NEW PROCEDURE
	if(!$isBilled) {
		echo "	<tr>\n";
		if($suppress_class) $suppress_class = "class='wmtBorder1B'";
		echo "		<td $suppress_class><input name='proc_code' id='proc_code' class='wmtFullInput' type='text' value='",htmlspecialchars($dt{'proc_code'},ENT_QUOTES,'',FALSE),"' onClick='get_cpt(\"proc_code\",\"proc_title\", \"\", \"\", \"\", \"proc_type\");' title='Click to select a procedure' /></td>\n";
		echo "		<td $suppress_class><input name='proc_modifier' id='proc_modifier' class='wmtFullInput' type='text' value='",htmlspecialchars($dt{'proc_modifier'},ENT_QUOTES,'',FALSE),"' /></td>\n";
		echo "		<td $suppress_class><input name='proc_units' id='proc_units' class='wmtFullInput' type='text' value='",htmlspecialchars($dt{'proc_units'},ENT_QUOTES,'',FALSE),"' title='YYYY-MM-DD' /></td>\n";
		if($use_justify) {
			echo '  <td class="tightFit" style="width: 104px;" ';
			echo 'title="' , xla("Select one or more diagnosis codes to justify the service") , '" >';
			echo '<select name="proc_justify" id="proc_justify" class="wmtInput" tabindex="-1" style="width: 90px;" onchange="setJustify(this)">';
			echo '<option value="',$dt['proc_justify'],'">',$dt['proc_justify'],'</option></select>';
			echo '</td>';
			$justinit .= "setJustify(f['proc_justify']);\n";
			echo "\n";
		}
		echo "		<td $suppress_class><input name='proc_title' id='proc_title' class='wmtFullInput' type='text' value='",htmlspecialchars($dt{'proc_title'},ENT_QUOTES,'',FALSE),"' title='Enter a brief description of the procedure here'/>";
		echo "<input name='proc_type' id='proc_type' type='hidden' value='",$dt['proc_type']."' />";
		echo "<input name='proc_on_fee' id='proc_on_fee' type='hidden' value='1' /></td>\n";
		echo "		<td class='wmtBorder1L $suppress_class'>&nbsp;";
		// SAVE IN CASE
		/**
		echo "		<input name='proc_on_fee' id='proc_on_fee' type='checkbox' value='1' checked='checked' />";
		echo "<label class='wmtBody' for='proc_on_fee'>Fee Sheet</label>";
		**/
		echo "</td>\n";
		echo "	</tr>\n";
		if(!$suppress_plan) {
			echo "	<tr>\n";
			echo "		<td class='wmtT wmtR wmtBorder1B'>Detail:";
			echo "<br><br><div style='float: left; margin-left: 5px; margin-bottom: 3px;'><a href='javascript:;' onClick='GetPlan(\"proc_plan\",\"proc_code\",\"proc_type\");' class='css_button_small' tabindex='-1' title='Select a plan for this procedure from Favorites'><span>Details</span></a></div>";
			echo "</td>\n";
			echo "		<td class='wmtBorder1B' colspan='";
			echo $use_justify ? 4 : 3;
			echo "'><textarea name='proc_plan' id='proc_plan' class='wmtFullInput'>",htmlspecialchars($dt{'proc_plan'},ENT_QUOTES,'',FALSE),"</textarea></td>\n";
			echo "		<td class='wmtBorder1L wmtBorder1B'>&nbsp;";
			echo "<br>$add_new_plan_btn\n";
			echo "</td>\n";
			echo "	</tr>\n";
		} else {
			echo "<input name='proc_plan' id='proc_plan' type='hidden' tabindex='-1' value='' />\n";
		}
	
	} // END - isBilled BOOLEAN
	echo "	<tr class='wmtBorder1B'>\n";
	echo "		<td colspan='2' class='wmtCollapseBar'>$add_btn</td>\n";
	echo "		<td colspan='";
	echo $use_justify ? 4 : 3;
	echo "' class='wmtCollapseBar'>";
	echo "<input name='tmp_proc_cnt' id='tmp_proc_cnt' type='hidden' value='".($cnt-1)."' />&nbsp;\n";
	echo "</td></tr>\n";
	echo "</table>\n";
}
?>

<script type="text/javascript">

function AddProcedure(base,wrap,formID)
{
	SetScrollTop();
	if(base.indexOf('?') == -1) {
		base += '?continue=true&mode=proc';
	} else {
		base += '&continue=true&mode=proc';
	}
	document.forms[0].action = base+'&wrap='+wrap;
 	if(formID != '' && formID != 0 && formID != null) {
		document.forms[0].action += '&id='+formID;
	}
	document.forms[0].submit();
}

function DeleteProcedure(base,wrap,itemID,formID)
{
	SetScrollTop();
	if(base.indexOf('?') == -1) {
		base += '?continue=true&mode=delproc';
	} else {
		base += '&continue=true&mode=delproc';
	}
	if(!ValidateItem(itemID, 'proc_bill_id_', 'Procedure')) return false;
	if(confirm("      Delete This Procedure?\n\nThis Action Can Not Be Reversed!")) {

  	document.forms[0].action = base+'&wrap='+wrap+'&itemID='+itemID;
 		if(formID != '' && formID != 0 && formID != null) {
  		document.forms[0].action += '&id='+formID;
		}
		document.forms[0].submit();
	}
}

</script>
