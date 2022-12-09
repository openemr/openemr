<?php
if(!isset($med_hist)) $med_hist = array();
if(!isset($max_med_hist)) $max_med_hist = false;
if(!isset($use_meds_not_rx)) $use_meds_not_rx = false;
if(!isset($dt['tmp_mhist_window_mode'])) $dt['tmp_mhist_window_mode'] = 'all';
if(!isset($portal_mode)) $portal_mode = false;
if(!isset($dt['fyi_medhist_nt'])) $dt['fyi_medhist_nt'] = '';
if(!isset($dt['med_hist_begdate'])) $dt['med_hist_begdate'] = '';
if(!isset($dt['med_hist_title'])) $dt['med_hist_title'] = '';
if(!isset($dt['med_hist_enddate'])) $dt['med_hist_enddate'] = '';
if(!isset($dt['med_hist_dest'])) $dt['med_hist_dest'] = '';
if(!isset($dt['med_hist_comm'])) $dt['med_hist_comm'] = '';
$delete_allow = \OpenEMR\Common\Acl\AclMain::aclCheckCore('admin','super');
$unlink_allow = \OpenEMR\Common\Acl\AclMain::aclCheckCore('patients','med');
if($frmdir == 'dashboard') $unlink_allow = false;
?>
<table width='100%' border='0' cellspacing='0' cellpadding='3'>
	<!--tr><td colspan="6">This is Meds Not RX</td></tr-->
	<tr>
		<td class='wmtLabel wmtDateCell wmtC wmtBorder1B'><?php xl('Start Date','e'); ?></td>
		<td class='wmtLabel wmtC wmtBorder1L wmtBorder1B'><?php xl('Medication','e'); ?></td>
		<td class='wmtLabel wmtC wmtDateCell wmtBorder1L wmtBorder1B'><?php xl('End Date','e'); ?></td>
		<td class='wmtLabel wmtC wmtBorder1L wmtBorder1B'><?php xl('Destination','e'); ?></td>
		<td class='wmtLabel wmtC wmtBorder1L wmtBorder1B'><?php xl('Comments','e'); ?></td>
<?php if($portal_mode) { ?>
		<td class='wmtLabel wmtBorder1L wmtBorder1B' style='width: 115px'>&nbsp;</td>
<?php } else if($delete_allow && $unlink_allow) { ?>
		<td class='wmtLabel wmtBorder1L wmtBorder1B' style='width: 175px'>&nbsp;</td>
<?php } else if($delete_allow || $unlink_allow) { ?>
		<td class='wmtLabel wmtBorder1L wmtBorder1B' style='width: 115px'>&nbsp;</td>
<?php } else { ?>
		<td class='wmtLabel wmtBorder1L wmtBorder1B' style='width: 65px'>&nbsp;</td>
<?php } ?>
	</tr>
	<?php 
	$cnt=0;
	if(count($med_hist) > 0) {
		foreach($med_hist as $prev) {
			$cnt++;
			if($dt['tmp_mhist_window_mode'] != 'all' && 
											$max_med_hist && ($cnt > $max_med_hist)) break;
	?>
	<tr>
		<td class='wmtBody wmtBorder1B'><input name='med_hist_id_<?php echo $cnt; ?>' id='med_hist_id_<?php echo $cnt; ?>' type="hidden" tabindex='-1' readonly='readonly' value="<?php echo $prev['id']; ?>" /><input name="med_hist_begdate_<?php echo $cnt; ?>" id="med_hist_begdate_<?php echo $cnt; ?>" type="text" class="wmtDateInput" value="<?php echo htmlspecialchars($prev['begdate'], ENT_QUOTES, '', FALSE); ?>" title="YYYY-MM-DD" />
		<!-- input name='med_hist_list_id_<?php // echo $cnt; ?>' id='med_hist_list_id_<?php // echo $cnt; ?>' type='hidden' readonly='readonly' tabindex='-1' value="<?php // echo $prev['list_id']; ?>" / --></td>
		<input name='med_hist_num_links_<?php echo $cnt; ?>' id='med_hist_num_links_<?php echo $cnt; ?>' type='hidden' readonly='readonly' tabindex='-1' value="<?php echo $prev['num_links']; ?>" /></td>
		<td class='wmtBody wmtBorder1L wmtBorder1B'><input name="med_hist_title_<?php echo $cnt; ?>" id="med_hist_title_<?php echo $cnt; ?>" type="text" class="wmtFullInput" value="<?php echo htmlspecialchars($prev['title'], ENT_QUOTES, '', FALSE); ?>" /></td>
		<td class='wmtBody wmtBorder1L wmtBorder1B'><input name="med_hist_enddate_<?php echo $cnt; ?>" id="med_hist_enddate_<?php echo $cnt; ?>" type="text" class="wmtDateInput" value="<?php echo htmlspecialchars($prev['enddate'], ENT_QUOTES, '', FALSE); ?>" title="YYYY-MM-DD Clearing this field will move the medication to current" /></td>
		<td class='wmtBody wmtBorder1L wmtBorder1B'><input name="med_hist_dest_<?php echo $cnt; ?>" id="med_hist_dest_<?php echo $cnt; ?>" type="text" class="wmtFullInput" value="<?php echo htmlspecialchars($prev['destination'], ENT_QUOTES, '', FALSE); ?>" /></td>
		<td class='wmtBody wmtBorder1L wmtBorder1B'><input name="med_hist_comm_<?php echo $cnt; ?>" id="med_hist_comm_<?php echo $cnt; ?>" type=text class="wmtFullInput" style="width: 99%;" value="<?php echo htmlspecialchars($prev['comments'], ENT_QUOTES, '', FALSE); ?>" /></td>
		<td class='wmtBorder1L wmtBorder1B'>
<?php if(!$portal_mode || ($prev['classification'] == 9)) { ?>
			<div class="wmtListButton"><a href="javascript:;" class='css_button_small' tabindex='-1' onClick="return SubmitLinkBuilder('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $cnt; ?>','<?php echo $id; ?>','updatemedhist','med_hist_id_','Medication History');" ><span><?php xl('Update','e'); ?></span></a></div>
<?php } ?>
<?php if($unlink_allow) { ?>
			<div class="wmtListButton"><a href="javascript:;" class='css_button_small' tabindex='-1' onClick="return SubmitLinkBuilder('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $cnt; ?>','<?php echo $id; ?>','unlinkmedhist','med_hist_id_','Medication History');" ><span><?php xl('Un-Link','e'); ?></span></a></div>
<?php } ?>
<?php if($delete_allow || ($portal_mode && ($prev['classification'] == 9))) { ?>
			<div class="wmtListButton"><a href="javascript:;" class='css_button_small' tabindex='-1' onClick="return SubmitLinkBuilder('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $cnt; ?>','<?php echo $id; ?>','delmedhist','med_hist_id_','Medication History','<?php echo $prev['num_links']; ?>');" ><span><?php xl('Delete','e'); ?></span></a></div>
<?php } ?>
		</td>
	</tr>
	<?php
		}
	// End of array 'meds' has information
	} else {
	?>
	<tr>
		<td class='wmtLabel wmtBorder1B'>&nbsp;</td>
		<td class='wmtLabel wmtBorder1L wmtBorder1B'>&nbsp;<?php xl('None on File','e'); ?></td>
		<td class='wmtLabel wmtBorder1L wmtBorder1B'>&nbsp;</td>
		<td class='wmtLabel wmtBorder1L wmtBorder1B'>&nbsp;</td>
		<td class='wmtLabel wmtBorder1L wmtBorder1B'>&nbsp;</td>
		<td class='wmtLabel wmtBorder1L wmtBorder1B'>&nbsp;</td>
		</tr>
	<?php 
	}
	?>
	<?php 
	if($med_add_allowed) {
	?>
	<tr>
		<td class='wmtBorder1B'><input name='med_hist_begdate' id='med_hist_begdate' class='wmtDateInput' type='text' title='YYYY-MM-DD' value="<?php echo htmlspecialchars($dt{'med_hist_begdate'}, ENT_QUOTES, '', FALSE); ?>" /></td>
		<td class='wmtBorder1L wmtBorder1B'><input name='med_hist_title' id='med_hist_title' class='wmtFullInput' type='text' value="<?php echo htmlspecialchars($dt{'med_hist_title'}, ENT_QUOTES, '', FALSE); ?>" /></td>
		<td class='wmtBorder1L wmtBorder1B'><input name='med_hist_enddate' id='med_hist_enddate' class='wmtFullInput' type='text' title='YYYY-MM-DD' value="<?php echo htmlspecialchars($dt{'med_hist_enddate'}, ENT_QUOTES, '', FALSE); ?>" /></td>
		<td class='wmtBorder1L wmtBorder1B'><input name='med_hist_dest' id='med_hist_dest' class='wmtFullInput' type='text' value="<?php echo htmlspecialchars($dt{'med_hist_dest'}, ENT_QUOTES, '', FALSE); ?>" /></td>
		<td class='wmtBorder1L wmtBorder1B'><input name='med_hist_comm' id='med_hist_comm' class='wmtFullInput' type='text' value="<?php echo htmlspecialchars($dt{'med_hist_comm'}, ENT_QUOTES, '', FALSE); ?>" /></td>
		<td class='wmtBorder1L wmtBorder1B'>&nbsp;</td>
	</tr>
	<?php 
	} 
	if(($dt['tmp_mhist_window_mode'] == 'limit') && 
													$max_med_hist && (count($med_hist) > $max_med_hist)) {
	?>
	<tr>
		<td colspan="6" class="wmtBody2 wmtBorder1B">
			<i>&nbsp;&nbsp;*&nbsp;This view is currently limited to the most recent&nbsp;<?php echo $max_med;?>&nbsp;prescriptions</i></td>
	</tr>
	<?php 
	}
	// FIX! - The limited view options need to be built here
	?>
	<tr>
	  <td class="wmtCollapseBar wmtBorder1B" colspan="6">
			<input type="hidden" name="tmp_med_hist_cnt" id="tmp_med_hist_cnt" tabindex="-1" value="<?php echo ($cnt); ?>" />
		<?php if($med_add_allowed) { ?>
			<div style="float: left; padding-left: 10px;">
			<a href="javascript:;" class='css_button_small' tabindex='-1' onClick="return SubmitLinkBuilder('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','','<?php echo $id; ?>','addmedhist');" href='javascript:;'><span><?php xl('Add Another','e'); ?></span></a></div>
		<?php } ?>	
		<!-- FIX - VIEW LIMIT/SHOW BUTTONS WOULD GO HERE -->
			<?php if($unlink_allow && count($med_hist)) { ?>
			<div style="float: right; padding-right: 15px;"><a href="javascript:;" class='css_button_small' tabindex='-1' onClick="return SubmitLinkBuilder('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','','<?php echo $id; ?>','unlinkallmedhist');" ><span><?php xl('Un-Link ALL Med History','e'); ?></span></a></div>
			<?php } ?>
		&nbsp;</td>
	</tr>

	<tr>
		<td class='wmtLabel' colspan='2'><?php xl('Other Notes','e'); ?>:</td>
	</tr>
	<tr>
		<td colspan='6'><textarea name='fyi_medhist_nt' id='fyi_medhist_nt' rows='4' class='wmtFullInput'><?php echo htmlspecialchars($dt['fyi_medhist_nt'], ENT_QUOTES, '', FALSE); ?></textarea></td>
	</tr>

</table>
