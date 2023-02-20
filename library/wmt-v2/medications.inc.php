<?php
if(!isset($med_add_allowed)) { $med_add_allowed = false; }
if(!isset($max_med)) { $max_med = false; }
if(!isset($dt['tmp_med_window_mode'])) { $dt['tmp_med_window_mode'] = 'all'; }
if(!isset($unlink_allow)) { $unlink_allow = false; }
if(!isset($unlink_all_meds)) { $unlink_all_meds = false; }
if(!isset($use_meds_not_rx)) { $use_meds_not_rx = false; }
if(!isset($portal_mode)) { $portal_mode = false; }
if(!isset($dt['fyi_portal_med_nt'])) { $dt['fyi_portal_med_nt'] = ''; }
?>
<table width='100%' border='0' cellspacing='0' cellpadding='2' style='table-layout: fixed; margin: 0px;'>
<?php if($use_meds_not_rx) { ?>
		<tr>
			<td class='wmtLabel wmtC wmtBorder1B' style='width: 85px'><?php xl('Start Date','e'); ?></td>
			<td class='wmtLabel wmtC wmtBorder1L wmtBorder1B'><?php xl('Medication','e'); ?></td>
			<td class='wmtLabel wmtC wmtBorder1L wmtBorder1B'><?php xl('End Date','e'); ?></td>
			<td class='wmtLabel wmtC wmtBorder1L wmtBorder1B'><?php xl('Active','e'); ?></td>
			<td class='wmtLabel wmtC wmtBorder1L wmtBorder1B'><?php xl('Comments','e'); ?></td>
	<?php if($unlink_allow) { ?>
			<td class='wmtLabel wmtBorder1L wmtBorder1B' style='width: 115px'>&nbsp;</td>
	<?php } else { ?>
			<td class='wmtLabel wmtBorder1L wmtBorder1B' style='width: 65px'>&nbsp;</td>
	<?php } ?>
	</tr>
	<?php 
	$cnt=1;
	if(isset($meds) && (count($meds) > 0)) {
		foreach($meds as $prev) {
			if($dt['tmp_med_window_mode'] != 'all' && $max_med && ($cnt > $max_med)) { break; }
			$med_status='Y';
			if($prev['active'] != '1') {
				$med_status='N';
			}
			if(!isset($prev['list_id'])) { $prev['list_id'] = ''; }
	?>
	<tr>
		<td class='wmtBody wmtBorder1B'><input name='med_id_<?php echo $cnt; ?>' id='med_id_<?php echo $cnt; ?>' type="hidden" tabindex='-1' readonly='readonly' value="<?php echo $prev['id']; ?>" /><?php echo $prev['begdate']; ?>&nbsp;
		<input name='med_list_id_<?php echo $cnt; ?>' id='med_list_id_<?php echo $cnt; ?>' type='hidden' readonly='readonly' tabindex='-1' value="<?php echo $prev['list_id']; ?>" /></td>
		<td class='wmtBody wmtBorder1L wmtBorder1B'><?php echo $prev['title']; ?>&nbsp;</td>
		<td class='wmtBody wmtBorder1L wmtBorder1B'><?php echo $prev['enddate']; ?>&nbsp;</td>
		<td class='wmtBody wmtBorder1L wmtBorder1B'><?php echo $med_status; ?>&nbsp;</td>
		<td class='wmtBody wmtBorder1L wmtBorder1B'><?php echo $prev['comments']; ?>&nbsp;</td>
		<td class='wmtBorder1L wmtBorder1B btnActContainer'><a href="javascript:;" class='css_button_small' tabindex='-1' onClick="return UpdateMedication('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $cnt; ?>','<?php echo $id; ?>');" ><span><?php xl('Update','e'); ?></span></a>
			<?php if($unlink_allow) { ?>
			<div><a href="javascript:;" class='css_button_small' tabindex='-1' onClick="return UnlinkMedication('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $cnt; ?>','<?php echo $id; ?>');" ><span><?php xl('Un-Link','e'); ?></span></a></div>
			<?php } ?>
		</td>
	</tr>
	<?php
			$cnt++;
		}
		$cnt--;
		// This is the mid window bar for unlink all or toggling window mode
		// For the window that pulls from the medications ('lists' table)
		// FIX! - Needs the mode button and the limit notice if used
		if($unlink_all_meds && $unlink_allow) { ?>
	<tr>
		<td class="wmtCollapseBar wmtBorder1B" colspan="4">&nbsp;<input type="hidden" name="tmp_med_cnt" id="tmp_med_cnt" tabindex="-1" value="<?php echo ($cnt); ?>" /></td>
		<td class="wmtCollapseBar wmtBorder1B" colspan="2">
		<div style="float: right; padding-right: 15px;"><a href="javascript:;" class='css_button_small' tabindex='-1' onClick="return UnlinkAllMedications('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo ($cnt); ?>','<?php echo $id; ?>');" ><span><?php xl('Un-Link ALL Medications','e'); ?></span></a></div>
		</td>
	</tr>
<?php 
		}
	} else {
	// This is the window for medications from 'lists' - none found
	?>
	<tr>
		<td class='wmtLabel wmtBorder1B'>&nbsp;</td>
		<td class='wmtLabel wmtBorder1L wmtBorder1B'>&nbsp;<?php xl('None on File','e'); ?></td>
		<td class='wmtLabel wmtBorder1L wmtBorder1B'>&nbsp;</td>
		<td class='wmtLabel wmtBorder1L wmtBorder1B'>&nbsp;</td>
		<td class='wmtLabel wmtBorder1L wmtBorder1B'>&nbsp;</td>
		<td class='wmtLabel wmtBorder1L wmtBorder1B'>&nbsp;</td>
		</tr>
	<?php } ?>
	<tr>
		<td class='wmtBorder1B'><input name='med_begdate' id='med_begdate' class='wmtDateInput' type='text' title='YYYY-MM-DD' value="<?php echo $dt{'med_begdate'}; ?>" /></td>
		<td class='wmtBorder1L wmtBorder1B'><input name='med_title' id='med_title' class='wmtFullInput' type='text' value="<?php echo $dt{'med_title'}; ?>" /></td>
		<td class='wmtBorder1L wmtBorder1B'><input name='med_enddate' id='med_enddate' class='wmtFullInput' type='text' title='YYYY-MM-DD' value="<?php echo $dt{'med_enddate'}; ?>" /></td>
		<td class='wmtBorder1L wmtBorder1B'><input name='med_stat' id='med_stat' class='wmtFullInput' type='text' value="<?php echo $dt{'med_stat'}; ?>" /></td>
		<td class='wmtBorder1L wmtBorder1B'><input name='med_comm' id='med_comm' class='wmtFullInput' type='text' value="<?php echo $dt{'med_comm'}; ?>" /></td>
		<td class='wmtBorder1L wmtBorder1B'>&nbsp;</td>
	</tr>
	<tr>
	  <td class='wmtCollapseBar' colspan='6'><a href="javascript:;" class='css_button_small' tabindex='-1' onClick="return AddMedication('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $id; ?>');" href='javascript:;'><span><?php xl('Update','e'); ?></span></a></td>
	</tr>
	<?php if(($dt['tmp_med_window_mode'] == 'limit') && 
															$max_med && (count($meds) > $max_med)) { ?>
	<tr>
		<td colspan="7" class="wmtBody2 wmtBorder1B"><i>&nbsp;&nbsp;*&nbsp;This view is currently limited to the most recent&nbsp;<?php echo $max_med;?>&nbsp;prescriptions</i></td>
	</tr>
	<?php } ?>
<?php 
	// End of the medication adding window version
} else {
	// This is the section for e-Rx clients, no medication adding
?>
	<tr>
		<td class='wmtLabel wmtC wmtBorder1B' style='width: 75px'><?php xl('Start Date','e'); ?></td>
		<td class='wmtLabel wmtC wmtBorder1L wmtBorder1B'><?php xl('Medication','e'); ?></td>
		<!-- Quantity is not sent back by the eRx system
		<td class='wmtLabel wmtC wmtBorder1L wmtBorder1B' style='width: 65px;'><?php xl('Quantity','e'); ?></td>
		-->
		<td class='wmtLabel wmtC wmtBorder1L wmtBorder1B' style='width: 65px;'><?php xl('Dosage','e'); ?></td>
		<td class='wmtLabel wmtC wmtBorder1L wmtBorder1B'><?php xl('Sig','e'); ?></td>
		<td class='wmtLabel wmtC wmtBorder1L wmtBorder1B'><?php xl('Comments','e'); ?></td>
		<?php if($unlink_allow) { ?>
		<td class='wmtLabel wmtC wmtBorder1L wmtBorder1B' style='width: 115px'>&nbsp;</td>
		<?php } else { ?>
		<td class='wmtLabel wmtC wmtBorder1L wmtBorder1B' style='width: 65px'>&nbsp;</td>
		<?php } ?>
	</tr>
<?php
	// echo "Count of Meds before window: ",count($meds),"<br>\n";
	// echo "Window mode: ",$dt['tmp_med_window_mode'],"<br>\n";
	$cnt=1;
	if(isset($meds) && (count($meds) > 0)) {
		foreach($meds as $prev) {
			if(($dt['tmp_med_window_mode'] != 'all') && 
															$max_med && ($cnt > $max_med)) { break; }
			$sig1=trim(ListLook($prev['route'],'drug_route'));
			if($sig1) { $sig1 = ' '.$sig1; }
			$form=trim(ListLook($prev['form'],'drug_form'));
			if($form) { $form = ' '.$form; }
			$sig2=trim(ListLook($prev['interval'],'drug_interval'));
			if($sig2) { $sig2 = ' '.$sig2; }
			$sig1=$prev['dosage'].$form.$sig1.$sig2;
			$size=trim($prev['size']);
			$unit=trim(ListLook($prev['unit'],'drug_units'));
			$size.=$unit;

			if(!isset($prev['list_id'])) { $prev['list_id'] = ''; }
?>
	<tr>
		<td class='wmtBody wmtBorder1B'><input name='med_id_<?php echo $cnt; ?>' id='med_id_<?php echo $cnt; ?>' type='hidden' readonly='readonly' tabindex='-1' value="<?php echo $prev['id']; ?>" /><?php echo $prev['date_added']; ?>&nbsp;
		<input name='med_list_id_<?php echo $cnt; ?>' id='med_list_id_<?php echo $cnt; ?>' type='hidden' readonly='readonly' tabindex='-1' value="<?php echo $prev['list_id']; ?>" /></td>
		<td class='wmtBody wmtBorder1L wmtBorder1B'><?php echo $prev['drug']; ?>&nbsp;</td>
		<!-- Quantity is currently not available
		<td class='wmtBody wmtBorder1L wmtBorder1B'><?php echo $prev['quantity']; ?>&nbsp;</td>
		-->
		<td class='wmtBody wmtBorder1L wmtBorder1B'><?php echo $size; ?>&nbsp;</td>
		<td class='wmtBody wmtBorder1L wmtBorder1B'><?php echo $sig1; ?>&nbsp;</td>
		<td class='wmtBody wmtBorder1L wmtBorder1B'><input name='med_comments_<?php echo $cnt; ?>' id='med_comments_<?php echo $cnt; ?>' type='text' class='wmtFullInput' tabindex='-1' value="<?php echo $prev['note']; ?>" /></td>
		<td class='wmtBody wmtBorder1L wmtBorder1B btnActContainer'><a href="javascript:;" class='css_button_small' tabindex='-1' onClick="return UpdatePrescription('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $cnt; ?>','<?php echo $id; ?>');" ><span><?php xl('Update','e'); ?></span></a>
	<?php if($unlink_allow) { 
		if($prev['list_id']) { ?>
			<a href="javascript:;" class='css_button_small' tabindex='-1' onClick="return UnlinkPrescription('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $cnt; ?>','<?php echo $id; ?>');" ><span><?php xl('Un-Link','e'); ?></span></a>
		<?php } else { ?>
			<a href="javascript:;" class='css_button_small' tabindex='-1' onClick="return LinkPrescription('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $cnt; ?>','<?php echo $id; ?>');" ><span><?php xl('Link','e'); ?></span></a>
	<?php } 
			} ?>
		</td>
	</tr>
<?php 
			$cnt++;
		}
		$cnt--;
		if(($dt['tmp_med_window_mode'] == 'limit') && 
																	$max_med && (count($meds) > $max_med)) {
?>
	<tr>
		<td colspan="6" class="wmtBody2 wmtBorder1B"><i>&nbsp;&nbsp;*&nbsp;This view is currently limited to the most recent&nbsp;<?php echo $max_med;?>&nbsp;prescriptions</i></td>
	</tr>
		<?php
		}
		if($unlink_all_meds || $max_med) {
		?>
	<tr>
		<td class="wmtCollapseBar wmtBorder1B">&nbsp;<input type="hidden" name="tmp_med_cnt" id="tmp_med_cnt" tabindex="-1" value="<?php echo ($cnt); ?>" /></td>
			<?php	if(($dt['tmp_med_window_mode'] == 'limit') && $max_med && (count($meds) > $max_med)) { ?>
			<td class="wmtCollapseBar wmtBorder1B">
			<div style="float: left; padding-left: 15px;"><a href="javascript:;" class='css_button_small' tabindex='-1' onClick="return ToggleWindowDisplayMode('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $id; ?>','medwindow','all');" ><span><?php xl('Show ALL Prescriptions','e'); ?></span></a></div>
			</td>
			<?php } else if(($dt['tmp_med_window_mode'] == 'all') && 
																		(count($meds) > $max_med) && $max_med) { ?>
			<td class="wmtCollapseBar wmtBorder1B">
			<div style="float: left; padding-left: 15px;"><a href="javascript:;" class='css_button_small' tabindex='-1' onClick="return ToggleWindowDisplayMode('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $id; ?>','medwindow','limit');" ><span><?php xl('Show '.$max_med.' Most Recent','e'); ?></span></a></div>
			<?php } else { ?>
			<td class="wmtCollapseBar wmtBorder1B">&nbsp;</td>
			<?php } ?>
			<td class="wmtCollapseBar wmtBorder1B" colspan="2">
			&nbsp;</td>
		
			<?php	if($unlink_all_meds) { ?>
			<td class="wmtCollapseBar wmtBorder1B" colspan="2">
			<div style="float: right; padding-right: 15px;"><a href="javascript:;" class='css_button_small' tabindex='-1' onClick="return UnlinkAllPrescriptions('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo ($cnt); ?>','<?php echo $id; ?>');" ><span><?php xl('Un-Link ALL Prescriptions','e'); ?></span></a></div>
			</td>
			<?php } else { ?>
			<td class="wmtCollapseBar wmtBorder1B" colspan="2">&nbsp;</td>
			<?php } ?>
		<?php } ?>
	</tr>
<?php 
	} else {
?>
	<tr>
		<td class='wmtLabel wmtBorder1B'>&nbsp;</td>
		<td class='wmtLabel wmtBorder1L wmtBorder1B'>&nbsp;<?php xl('None on File','e'); ?></td>
		<td class='wmtLabel wmtBorder1L wmtBorder1B'>&nbsp;</td>
		<!-- td class='wmtLabel wmtBorder1L wmtBorder1B'>&nbsp;</td -->
		<td class='wmtLabel wmtBorder1L wmtBorder1B'>&nbsp;</td>
		<td class='wmtLabel wmtBorder1L wmtBorder1B'>&nbsp;</td>
		<td class='wmtLabel wmtBorder1L wmtBorder1B'>&nbsp;</td>
	</tr>
<?php 
	}
}
$tmp_col=6;
if(!$portal_mode) {
?>
	<tr>
		<td class='wmtLabel' colspan='2'><?php xl('Other Notes','e'); ?>:</td>
	</tr>
	<tr>
		<td colspan='<?php echo $tmp_col; ?>'><textarea name='fyi_med_nt' id='fyi_med_nt' rows='4' class='wmtFullInput'><?php echo $dt['fyi_med_nt']; ?></textarea></td>
	</tr>
<?php
}
if($portal_mode || $dt['fyi_portal_med_nt'] != '') {
?>
	<tr>
		<td class="<?php echo (($portal_mode)?'bkkLabel bkkBorder1T':'wmtLabel'); ?>" colspan="6"><?php echo (($portal_mode) ? 'Notes':'Notes input by the patient via the portal'); ?>:</td>
	</tr>
	<tr>
		<td colspan="<?php echo $tmp_col; ?>" style="<?php echo (($portal_mode)?'padding: 6px;':''); ?>"><textarea name="fyi_portal_med_nt" id="fyi_portal_med_nt" rows="4" class="<?php echo (($portal_mode)?'bkkFullInput':'wmtFullInput'); ?>"><?php echo $dt['fyi_portal_med_nt']; ?></textarea></td>
	</tr>
<?php } ?>
</table>
