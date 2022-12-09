<?php
include($GLOBALS['srcdir'].'/wmt-v2/amc_auto_add_med.inc.php');
if(!isset($med_add_allowed)) 
					$med_add_allowed = checkSettingMode('wmt::db_meds_add');
if(!isset($max_med)) $max_med = false;
if(!isset($dt['tmp_med_window_mode'])) $dt['tmp_med_window_mode'] = 'all';
if(!isset($use_meds_not_rx)) $use_meds_not_rx = false;
if(!isset($portal_mode)) $portal_mode = false;
if(!isset($dt['fyi_med_nt'])) $dt['fyi_med_nt'] = '';
if(!isset($dt['med_begdate'])) $dt['med_begdate'] = '';
if(!isset($dt['med_title'])) $dt['med_title'] = '';
if(!isset($dt['med_enddate'])) $dt['med_enddate'] = '';
if(!isset($dt['med_dest'])) $dt['med_dest'] = '';
if(!isset($dt['med_comm'])) $dt['med_comm'] = '';
if(!isset($meds)) $meds = array();
$delete_allow = \OpenEMR\Common\Acl\AclMain::aclCheckCore('admin','super');
$unlink_allow = (\OpenEMR\Common\Acl\AclMain::aclCheckCore('patients','med') || $delete_allow);
if($frmdir == 'dashboard') $unlink_allow = $delete_allow = false;
?>
<table width='100%' border='0' cellspacing='0' cellpadding='3'>
	<tr>
		<td class="wmtLabel wmtC wmtBorder1B wmtDateCell"><?php xl('Start Date','e'); ?></td>
		<td class="wmtLabel wmtC wmtBorder1L wmtBorder1B"><?php xl('Medication','e'); ?></td>
		<td class="wmtLabel wmtC wmtBorder1L wmtBorder1B wmtDateCell"><?php xl('End Date','e'); ?></td>
		<td class="wmtLabel wmtC wmtBorder1L wmtBorder1B"><?php xl('Destination','e'); ?></td>
		<td class="wmtLabel wmtC wmtBorder1L wmtBorder1B"><?php xl('Comments','e'); ?></td>
	<?php if($portal_mode) { ?>
		<td class="wmtLabel wmtBorder1L wmtBorder1B" style='width: 115px'>&nbsp;</td>
	<?php } else if($delete_allow && $unlink_allow) { ?>
		<td class="wmtLabel wmtBorder1L wmtBorder1B" style='width: 175px'>&nbsp;</td>
	<?php } else if($delete_allow || $unlink_allow) { ?>
		<td class="wmtLabel wmtBorder1L wmtBorder1B" style='width: 115px'>&nbsp;</td>
	<?php } else { ?>
		<td class="wmtLabel wmtBorder1L wmtBorder1B" style='width: 65px'>&nbsp;</td>
	<?php } ?>
	</tr>
	<?php 
	$bg = 'bkkLight';
	$cnt=0;
	$portal_data_exists = false;
	if(count($meds) > 0) {
		foreach($meds as $prev) {
			$cnt++;
			if($dt['tmp_med_window_mode'] != 'all' && 
											$max_med && ($cnt > $max_med)) break;
	?>
	<tr>
		<td class='wmtBody wmtBorder1B'><input name='med_id_<?php echo $cnt; ?>' id='med_id_<?php echo $cnt; ?>' type="hidden" tabindex='-1' readonly='readonly' value="<?php echo $prev['id']; ?>" /><input name="med_begdate_<?php echo $cnt; ?>" id="med_begdate_<?php echo $cnt; ?>" type="text" class="wmtDateInput" value="<?php echo htmlspecialchars($prev['begdate'], ENT_QUOTES, '', FALSE); ?>" title="YYYY-MM-DD Format" />
		<!-- input name='med_list_id_<?php // echo $cnt; ?>' id='med_list_id_<?php // echo $cnt; ?>' type='hidden' readonly='readonly' tabindex='-1' value="<?php // echo $prev['list_id']; ?>" / --></td>
		<input name='med_num_links_<?php echo $cnt; ?>' id='med_num_links_<?php echo $cnt; ?>' type='hidden' readonly='readonly' tabindex='-1' value="<?php echo $prev['num_links']; ?>" /></td>
		<td class='wmtBody wmtBorder1L wmtBorder1B'><input name="med_title_<?php echo $cnt; ?>" id="med_title_<?php echo $cnt; ?>" type="text" class="wmtFullInput" value="<?php echo htmlspecialchars($prev['title'], ENT_QUOTES, '', FALSE); ?>" /></td>
		<td class='wmtBody wmtBorder1L wmtBorder1B'><input name="med_enddate_<?php echo $cnt; ?>" id="med_enddate_<?php echo $cnt; ?>" type="text" class="wmtDateInput" value="<?php echo htmlspecialchars($prev['enddate'], ENT_QUOTES, '', FALSE); ?>" title="YYYY-MM-DD Format, A date here will move the entry to History" /></td>
		<td class='wmtBody wmtBorder1L wmtBorder1B'><input name="med_dest_<?php echo $cnt; ?>" id="med_dest_<?php echo $cnt; ?>" type="text" class="wmtFullInput" value="<?php echo htmlspecialchars($prev['destination'], ENT_QUOTES, '', FALSE); ?>" /></td>
		<td class='wmtBody wmtBorder1L wmtBorder1B'><input name="med_comments_<?php echo $cnt; ?>" id ="med_comments_<?php echo $cnt; ?>" type="text" class="wmtFullInput" value="<?php echo htmlspecialchars($prev['comments'], ENT_QUOTES, '', FALSE); ?>" /></td>
		<td class='wmtBorder1L wmtBorder1B'>
<?php if(!$portal_mode || ($prev['classification'] == 9)) { ?>
			<div style="float: left; padding-left: 2px;"><a href="javascript:;" class='css_button_small' tabindex='-1' onClick="return SubmitLinkBuilder('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $cnt; ?>','<?php echo $id; ?>','updatemed','med_id_','Medication');" ><span><?php xl('Update','e'); ?></span></a></div>
<?php } ?>
<?php if($unlink_allow) { ?>
			<div style="float: left; padding-left: 2px;"><a href="javascript:;" class='css_button_small' tabindex='-1' onClick="return SubmitLinkBuilder('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $cnt; ?>','<?php echo $id; ?>','unlinkmed','med_id_','Medication');" ><span><?php xl('Un-Link','e'); ?></span></a></div>
<?php } ?>
<?php if($delete_allow || ($portal_mode && $prev['classification'] == 9)) { ?>
			<div style="float: left; padding-left: 2px;"><a href="javascript:;" class='css_button_small' tabindex='-1' onClick="return SubmitLinkBuilder('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $cnt; ?>','<?php echo $id; ?>','delmed','med_id_','Medication','<?php echo $prev['num_links']; ?>');" ><span><?php xl('Delete','e'); ?></span></a></div>
<?php } ?>
		</td>
	</tr>
	<?php
		}
	// End of array 'meds' has information
	} else if(!$med_add_allowed) {
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
		<td class='wmtBorder1B'><input name='med_begdate' id='med_begdate' class='wmtDateInput' type='text' title='YYYY-MM-DD' value="<?php echo htmlspecialchars($dt{'med_begdate'}, ENT_QUOTES, '', FALSE); ?>" /></td>
		<td class='wmtBorder1L wmtBorder1B'><input name='med_title' id='med_title' class='wmtFullInput' type='text' value="<?php echo htmlspecialchars($dt{'med_title'}, ENT_QUOTES, '', FALSE); ?>" /></td>
		<td class='wmtBorder1L wmtBorder1B'><input name='med_enddate' id='med_enddate' class='wmtDateInput' type='text' title='YYYY-MM-DD Format, Entering a date here will move this item to history' value="<?php echo htmlspecialchars($dt{'med_enddate'}, ENT_QUOTES, '', FALSE); ?>" /></td>
		<td class='wmtBorder1L wmtBorder1B'><input name='med_dest' id='med_dest' class='wmtFullInput' type='text' value="<?php echo htmlspecialchars($dt{'med_dest'}, ENT_QUOTES, '', FALSE); ?>" /></td>
		<td class='wmtBorder1L wmtBorder1B'><input name='med_comm' id='med_comm' class='wmtFullInput' type='text' value="<?php echo htmlspecialchars($dt{'med_comm'}, ENT_QUOTES, '', FALSE); ?>" /></td>
		<td class='wmtBorder1L wmtBorder1B'>&nbsp;</td>
	</tr>
	<?php 
	} 
	if(($dt['tmp_med_window_mode'] == 'limit') && 
															$max_med && (count($meds) > $max_med)) {
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
			<input type="hidden" name="tmp_med_cnt" id="tmp_med_cnt" tabindex="-1" value="<?php echo ($cnt); ?>" />
		<?php if($med_add_allowed) { ?>
			<div style="float: left; padding-left: 10px;">
			<a href="javascript:;" class='css_button_small' tabindex='-1' onClick="return SubmitLinkBuilder('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','','<?php echo $id; ?>','addmed');" href='javascript:;'><span><?php xl('Add Another','e'); ?></span></a></div>
		<?php } ?>	
		<!-- FIX - VIEW LIMIT/SHOW BUTTONS WOULD GO HERE -->
			<?php if($unlink_allow && count($meds)) { ?>
			<div style="float: right; padding-right: 10px;"><a href="javascript:;" class='css_button_small' tabindex='-1' onClick="return SubmitLinkBuilder('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','','<?php echo $id; ?>','unlinkallmeds');" ><span><?php xl('Un-Link ALL Meds','e'); ?></span></a></div>
			<?php } ?>
		&nbsp;</td>
	</tr>


	<tr>
		<td class="wmtLabel" colspan="2"><?php xl('Other Notes','e'); ?>:</td>
	</tr>
	<tr>
		<td colspan="6"><textarea name='fyi_med_nt' id='fyi_med_nt' rows='4' class='wmtFullInput'><?php echo htmlspecialchars($dt['fyi_med_nt'], ENT_QUOTES, '', FALSE); ?></textarea></td>
	</tr>
</table>
<?php
if($review = checkSettingMode('wmt::meds_add_review','',$frmdir)) {
	$caller = 'meds';
	$chk_title = 'Medications';
	include($GLOBALS['srcdir'].'/wmt-v2/form_bricks/module_reviewed.inc.php');
}
?>
