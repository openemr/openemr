<?php 
if(!isset($allergy_add_allowed)) 
					$allergy_add_allowed = checkSettingMode('wmt::db_allergy_add');
if(!isset($portal_mode)) $portal_mode = false;
if(!isset($pat_entries_exist)) $pat_entries_exist = false;
if(!isset($field_prefix)) $field_prefix = '';
if(!isset($dt['all_begdate'])) $dt['all_begdate'] = '';
if(!isset($dt['all_title'])) $dt['all_title'] = '';
if(!isset($dt['all_react'])) $dt['all_react'] = '';
if(!isset($dt['all_comm'])) $dt['all_comm'] = '';
if(!isset($dt['all_occur'])) $dt['all_occur'] = '';
if(!isset($pat_entries['fyi_allergy_nt'])) 
					$pat_entries['fyi_allergy_nt'] = $portal_data_layout;
$use_border = true;
if(!isset($allergies)) $allergies = array();
$delete_allow = \OpenEMR\Common\Acl\AclMain::aclCheckCore('admin','super');
if(!$allergy_add_allowed) $delete_allow = false;
$unlink_allow = (\OpenEMR\Common\Acl\AclMain::aclCheckCore('patients','med') || $delete_allow);
if($frmdir == 'dashboard') $unlink_allow = $delete_allow = false;

?>
<table width="100%" border="0" cellspacing="0" cellpadding="2" style="white-space: normal;">
	<tr>
		<td class="wmtLabel wmtC wmtBorder1B wmtDateCell"><?php xl('Start Date','e'); ?></td>
		<td class="wmtLabel wmtC <?php echo (($portal_mode)?'':'wmtBorder1L'); ?> wmtBorder1B"><?php xl('Title','e'); ?></td>
		<td class="wmtLabel wmtC <?php echo (($portal_mode)?'':'wmtBorder1L'); ?> wmtBorder1B"><?php xl('Reaction','e'); ?></td>
		<td class="wmtLabel wmtC <?php echo (($portal_mode)?'':'wmtBorder1L'); ?> wmtBorder1B"><?php xl('Comments','e'); ?></td>
<?php if($portal_mode) { ?>
		<td class="wmtLabel wmtC <?php echo (($portal_mode)?'':'wmtBorder1L'); ?> wmtBorder1B" style='width: 115px'>&nbsp;</td>
<?php } else if($delete_allow && $unlink_allow) { ?>
		<td class="wmtLabel wmtC <?php echo (($portal_mode)?'':'wmtBorder1L'); ?> wmtBorder1B" style='width: 175px'>&nbsp;</td>
<?php } else if($delete_allow || $unlink_allow) { ?>
		<td class="wmtLabel wmtC <?php echo (($portal_mode)?'':'wmtBorder1L'); ?> wmtBorder1B" style='width: 120px'>&nbsp;</td>
<?php } else { ?>
		<td class="wmtLabel wmtC <?php echo (($portal_mode)?'':'wmtBorder1L'); ?> wmtBorder1B" style='width: 65px'>&nbsp;</td>
<?php } ?>
	</tr>

<?php
$bg = 'bkkLight';
$cnt=0;
if(count($allergies) > 0) {
	foreach($allergies as $prev) {
		$cnt++;
?>
	<tr <?php echo (($portal_mode)? 'class="'.$bg.'"' : ''); ?> >
		<td class="<?php echo (($portal_mode)?'':'wmtBorder1B'); ?>"><input name="all_id_<?php echo $cnt; ?>" id="all_id_<?php echo $cnt; ?>" type="hidden" tabindex="-1" readonly="readonly" value="<?php echo $prev['id']; ?>" /><input name="all_num_links_<?php echo $cnt; ?>" id="all_num_links_<?php echo $cnt; ?>" type="hidden" tabindex="-1" readonly="readonly" value="<?php echo $prev['num_links']; ?>" />
		<?php
		 if(($portal_mode && $prev['classification'] != 9) || 
							(!$portal_mode && !$allergy_add_allowed)) {
			echo htmlspecialchars($prev['begdate'], ENT_QUOTES, '', FALSE).'&nbsp;';
		} else {
		?>
		<input name="all_begdate_<?php echo $cnt; ?>" id="all_begdate_<?php echo $cnt; ?>" class="wmtInput" type="text" tabindex="-1" value="<?php echo htmlspecialchars($prev['begdate'], ENT_QUOTES, '', FALSE); ?>" />
		<?php } ?>
		</td>

		<td class="<?php echo (($portal_mode)?'':'wmtBorder1L wmtBorder1B'); ?>">
		<?php if(($portal_mode && $prev['classification'] != 9) || 
														(!$portal_mode && !$allergy_add_allowed)) { ?>
		<span class="all_title" data-id="<?php echo $cnt; ?>"><?php echo htmlspecialchars($prev['title'], ENT_QUOTES, '', FALSE); ?></span>&nbsp;
		<?php } else { ?>
			<input name="all_title_<?php echo $cnt; ?>" id="all_title_<?php echo $cnt; ?>" class="<?php echo (($portal_mode) ? 'bkkFullInput':'wmtInput'); ?>" type="text" tabindex="-1" value="<?php echo htmlspecialchars($prev['title'], ENT_QUOTES, '', FALSE); ?>" />
		<?php } ?>
		</td>

		<td class="<?php echo (($portal_mode)?'':'wmtBorder1L wmtBorder1B'); ?>">
		<?php if($allergy_add_allowed || $portal_mode) { ?>
			<?php if(($portal_mode && $prev['classification'] != 9) || 
														(!$portal_mode && !$allergy_add_allowed)) { ?>
			<?php echo htmlspecialchars($prev['reaction'], ENT_QUOTES, '', FALSE); ?>&nbsp;
			<?php } else { ?>
				<input name="all_react_<?php echo $cnt; ?>" id="all_react_<?php echo $cnt; ?>" class="<?php echo (($portal_mode) ? 'bkkFullInput':'wmtInput'); ?>" type="text" tabindex="-1" value="<?php echo htmlspecialchars($prev['reaction'], ENT_QUOTES, '', FALSE); ?>" />
			<?php } ?>
		<?php } else { ?>
			<?php echo ListLook($prev['outcome'],'outcome',''); ?>
		<?php } ?>
		</td>

		<td class="<?php echo (($portal_mode)?'':'wmtBorder1L wmtBorder1B'); ?>">
		<?php if(($portal_mode && $prev['classification'] != 9)) { ?>
			<?php echo htmlspecialchars($prev['comments'], ENT_QUOTES, '', FALSE); ?>&nbsp;
		<?php } else { ?>
			<input name="all_comments_<?php echo $cnt; ?>" id="all_comments_<?php echo $cnt; ?>" class="<?php echo (($portal_mode) ? 'bkkInput' : 'wmtInput'); ?>" type="text" style="width: 98%;" tabindex="-1" value="<?php echo htmlspecialchars($prev['comments'], ENT_QUOTES, '', FALSE); ?>" />
		<?php } ?>
		</td>

		<td class="<?php echo (($portal_mode)?'':'wmtBorder1L wmtBorder1B'); ?>">
		<?php if(!$portal_mode || $prev['classification'] == 9) { ?>
			<div style="float: left; padding-left: 2px;"><a href="javascript:;" class="css_button_small" tabindex="-1" onClick="return SubmitLinkBuilder('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $cnt; ?>','<?php echo $id; ?>','updateall','all_id_','Allergy');" title='Update this allergy'><span><?php xl('Update','e'); ?></span></a></div>
		<?php } ?>
		<?php if($unlink_allow) { ?>
			<div style="float: left; padding-left: 2px;"><a href="javascript:;" class="css_button_small" tabindex="-1" onClick="return SubmitLinkBuilder('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $cnt; ?>','<?php echo $id; ?>','unlinkall','all_id_','Allergy');" title='Unlink this allergy from this visit'><span><?php xl('Un-Link','e'); ?></span></a></div>
		<?php } ?>
		<?php if($delete_allow || ($portal_mode && $prev['classification'] == 9)) { ?>
			<div style="float: left; padding-left: 2px;"><a href="javascript:;" class="css_button_small" tabindex="-1" onClick="return SubmitLinkBuilder('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $cnt; ?>','<?php echo $id; ?>','delall','all_id_','Allergy','<?php echo $prev['num_links']; ?>');" title='Delete thie allergy'><span><?php xl('Delete','e'); ?></span></a></div>
		<?php } ?>
		</td>
	</tr>
<?php 
		$bg = ($bg == 'bkkAltLight' ? 'bkkLight' : 'bkkAltLight');
	}
} else {
?>
	<tr class="<?php echo (($portal_mode)? $bg : ''); ?>" >
		<td class="wmtLabel wmtBorder1B">&nbsp;</td>
		<td class="wmtLabel <?php echo (($portal_mode)?'':'wmtBorder1L'); ?> wmtBorder1B">&nbsp;<?php xl('None on File','e'); ?></td>
		<td class="wmtLabel <?php echo (($portal_mode)?'':'wmtBorder1L'); ?> wmtBorder1B">&nbsp;</td>
		<td class="wmtLabel <?php echo (($portal_mode)?'':'wmtBorder1L'); ?> wmtBorder1B">&nbsp;</td>
		<td class="wmtLabel <?php echo (($portal_mode)?'':'wmtBorder1L'); ?> wmtBorder1B">&nbsp;</td>
	</tr>
<?php
}
if($allergy_add_allowed || $portal_mode) {
?>
	<tr>
		<td class='wmtBorder1B'><input name='all_begdate' id='all_begdate' class='wmtDateInput' type='text' title='YYYY-MM-DD' placeholder="YYYY-MM-DD" value="<?php echo $dt{'all_begdate'}; ?>" /></td>
		<td class='wmtBorder1L wmtBorder1B'><input name='all_title' id='all_title' class='wmtFullInput' type='text' value="<?php echo $dt{'all_title'}; ?>" /></td>
		<td class='wmtBorder1L wmtBorder1B'><input name='all_react' id='all_react' class='wmtFullInput' type='text' value="<?php echo $dt{'all_react'}; ?>" /></td>
		<td class='wmtBorder1L wmtBorder1B'><input name='all_comm' id='all_comm' class='wmtFullInput' type='text' value="<?php echo $dt{'all_comm'}; ?>" /></td>
	  <td class='wmtBorder1L wmtBorder1B'>&nbsp;</td>
	</tr>
<?php } ?>
	<tr class="wmtColorBar">
		<td class='wmtBorder1B' colspan='2'>
		<?php if($portal_mode || $allergy_add_allowed) { ?>
			<div style="float: left; padding-left: 10px;"><a href="javascript:;" class='css_button' onClick="return SubmitLinkBuilder('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','','<?php echo $id; ?>','all');" ><span><?php xl('Add Another','e'); ?></span></a></div>
		<?php } ?>
		</td>
		<td class="wmtBorder1B" colspan="1">&nbsp;<input type="hidden" name="tmp_allergy_cnt" id="tmp_allergy_cnt" tabindex="-1" value="<?php echo ($cnt-1); ?>" /></td>
		<td class="wmtBorder1B" colspan="2">
	<?php if($unlink_allow && count($allergies) > 0) { ?>
			<div style="float: right; padding-right: 10px;"><a href="javascript:;" class='css_button_small' tabindex='-1' onClick="return SubmitLinkBuilder('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo ($cnt); ?>','<?php echo $id; ?>','unlinkallall');" ><span><?php xl('Un-Link ALL Allergies','e'); ?></span></a></div>
	<?php } else { ?>
		&nbsp;
	<?php } ?>
		</td>
	</tr>
	<tr>
		<td class="<?php echo $portal_mode ? 'bkkLabel' : 'wmtLabel'; ?>" colspan="5"><?php echo xl('Other Notes'); ?>:</td>
	</tr>
	<tr>
		<td colspan="5"><textarea name="fyi_allergy_nt" id="fyi_allergy_nt" rows="4" class="<?php echo (($portal_mode)?'bkkFullInput':'wmtFullInput'); ?>" ><?php echo htmlspecialchars($dt['fyi_allergy_nt'], ENT_QUOTES, '', FALSE); ?></textarea></td>
	</tr>
</table>
<?php
if($pat_entries_exist && !$portal_mode) {
	if($pat_entries['fyi_allergy_nt']['content'] && (strpos($dt{$field_prefix.'fyi_allergy_nt'},$pat_entries['fyi_allergy_nt']['content']) === false)) {
?>
		<div class="wmtLabel wmtPortalData wmtL" style="margin: 6px;">Notes input by the patient via the portal:</div>
		<div class="wmtPortalData wmtBorderHighlight wmtBody wmtL" style="margin: 6px;" id="tmp_<?php echo $field_prefix; ?>fyi_allergy_nt" onclick="AcceptPortalData(this.id);" ><?php echo htmlspecialchars($pat_entries['fyi_allergy_nt']['content'], ENT_QUOTES, '', FALSE); ?></div>
<?php 
	}
}
if($review = checkSettingMode('wmt::allergy_review','',$frmdir)) {
	$caller = 'allergy';
	$chk_title = 'Allergies';
	include($GLOBALS['srcdir'].'/wmt-v2/form_bricks/module_reviewed.inc.php');
}
?>
