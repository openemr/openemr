<?php
if(!isset($dt['fyi_surg_nt'])) { $dt['fyi_surg_nt'] = ''; }
if(!isset($dt['ps_begdate'])) $dt['ps_begdate'] = '';
if(!isset($dt['ps_title'])) $dt['ps_title'] = '';
if(!isset($dt['ps_comments'])) $dt['ps_comments'] = '';
if(!isset($dt['ps_referredby'])) $dt['ps_referredby'] = '';
if(!isset($dt['ps_hospitalized'])) $dt['ps_hospitalized'] = '';
if(!isset($dt['fyi_portal_surg_nt'])) $dt['fyi_portal_surg_nt'] = '';
if(!isset($portal_mode)) $portal_mode = false;
if(!isset($pat_entries_exist)) $pat_entries_exist = false;
if(!isset($field_prefix)) $field_prefix = false;
if(!isset($delete_allow)) $delete_allow = \OpenEMR\Common\Acl\AclMain::aclCheckCore('admin','super');
if(!isset($unlink_allow)) $unlink_allow = 
				(\OpenEMR\Common\Acl\AclMain::aclCheckCore('patients','med') || $delete_allow);
if(!isset($pat_entries['fyi_surg_nt'])) 
			$pat_entries['fyi_surg_nt'] = $portal_data_layout;
if(!isset($frmdir)) $frmdir = '';

if($frmdir == 'dashboard') {
	$delete_allow = \OpenEMR\Common\Acl\AclMain::aclCheckCore('admin','super');
	$unlink_allow = (\OpenEMR\Common\Acl\AclMain::aclCheckCore('patients','med') || $delete_allow);
}
?>
<table width='100%' border='0' cellspacing='0' cellpadding='0' style="white-space: normal;">
	<tr>
		<td class="<?php echo (($portal_mode)?'wmtLabel wmtC wmtBorder1B':'wmtLabel wmtC wmtBorder1B'); ?>" style="width: 95px">Date</td>
		<td class="<?php echo (($portal_mode)?'wmtLabel wmtC wmtBorder1B':'wmtBorder1B wmtBorder1L wmtLabel wmtC'); ?>">Type of Surgery</td>
		<td class="<?php echo (($portal_mode)?'wmtLabel wmtC wmtBorder1B':'wmtBorder1B wmtBorder1L wmtLabel wmtC'); ?>">Hospitalized?</td>
		<td class="<?php echo (($portal_mode)?'wmtLabel wmtC wmtBorder1B':'wmtBorder1B wmtBorder1L wmtLabel wmtC'); ?>">Notes</td>
		<td class="<?php echo (($portal_mode)?'wmtLabel wmtC wmtBorder1B':'wmtBorder1B wmtBorder1L wmtLabel wmtC'); ?>">Performed By</td>
<?php 
if($portal_mode) {
echo "		<td class='wmtBorder1B' style='width: 115px'>&nbsp;</td>\n";
} else if($delete_allow && $unlink_allow) {
	echo "		<td class='wmtBorder1L wmtBorder1B' style='width: 175px'>&nbsp;</td>\n";
} else if($delete_allow || $unlink_allow) {
echo "		<td class='wmtBorder1L wmtBorder1B' style='width: 115px'>&nbsp;</td>\n";
} else {
echo "		<td class='wmtBorder1L wmtBorder1B' style='width: 65px'>&nbsp;</td>\n";
}
?>
	</tr>
<?php
$bg = 'wmtLight';
$cnt=1;
$portal_data_exists = false;
if(isset($surg)) {
	foreach($surg as $prev) {
?>
	<tr class="<?php echo (($portal_mode)? $bg : ''); ?><?php echo ((!$portal_mode && ($prev['classification'] == 9))?'wmtHighlight':''); ?>" >
		<td class="<?php echo (($portal_mode)?'wmtDateCell wmtBody':'wmtDateCell wmtBorder1B'); ?>" ><input name="ps_id_<?php echo $cnt; ?>" id="ps_id_<?php echo $cnt; ?>" type='hidden' readonly='readonly' value="<?php echo $prev['id']; ?>" /><input name="ps_num_links_<?php echo $cnt; ?>" id="ps_num_links_<?php echo $cnt; ?>" type="hidden" tabindex="-1" value="<?php echo $prev['num_links']; ?>" />
		<?php if($portal_mode && ($prev['classification'] != 9)) { ?>
			<?php echo $prev['begdate']; ?>&nbsp;
		<?php } else { ?>
			<input name="ps_begdate_<?php echo $cnt; ?>" id="ps_begdate_<?php echo $cnt; ?>" class="<?php echo (($portal_mode) ? 'wmtInput':'wmtInput'); ?>" type="text" style="width: 85px;" tabindex='-1' value="<?php echo $prev['begdate']; ?>" />
		<?php } ?>
		</td>

		<td class="<?php echo (($portal_mode)?'wmtBody':'wmtBorder1L wmtBorder1B'); ?>" >
		<?php if($portal_mode && ($prev['classification'] != 9)) { ?>
			&nbsp;<?php echo $prev['title']; ?>
		<?php } else { ?>
			<input name="ps_title_<?php echo $cnt; ?>" id="ps_title_<?php echo $cnt; ?>" class="<?php echo (($portal_mode)?'wmtFullInput':'wmtFullInput'); ?><?php echo ((!$portal_mode && $prev['classification'] == '9')?' wmtHighlight':''); ?>" type='text' tabindex='-1' value="<?php echo $prev['title']; ?>" />
		<?php } ?>
		</td>

		<td class="<?php echo (($portal_mode)?'wmtBody':'wmtBorder1L wmtBorder1B'); ?>" >
		<?php if($portal_mode && ($prev['classification'] != 9)) { ?>
			&nbsp;<?php echo ListLook($prev['extrainfo'],'YesNo'); ?>
		<?php } else { ?>
			<select name="ps_hospitalized_<?php echo $cnt; ?>" id="ps_hospitalized_<?php echo $cnt; ?>" class="<?php echo (($portal_mode)?'wmtFullInput':'wmtFullInput'); ?><?php echo ((!$portal_mode && $prev['classification'] == '9')?' wmtHighlight':''); ?>" tabindex='-1'>
			<?php echo ListSel($prev['extrainfo'],'YesNo'); ?></select>
		<?php } ?>
		</td>

		<td class="<?php echo (($portal_mode)?'wmtBody':'wmtBorder1L wmtBorder1B'); ?>" >
		<?php if($portal_mode && ($prev['classification'] != 9)) { ?>
			&nbsp;<?php echo $prev['comments']; ?>
		<?php } else { ?>
			<input name="ps_comments_<?php echo $cnt; ?>" id="ps_comments_<?php echo $cnt; ?>" class="<?php echo (($portal_mode)?'wmtFullInput':'wmtFullInput'); ?><?php echo ((!$portal_mode && $prev['classification'] == '9')?' wmtHighlight':''); ?>" type='text' tabindex='-1' value="<?php echo $prev['comments']; ?>" />
		<?php } ?>
		</td>

		<td class="<?php echo (($portal_mode)?'wmtBody':'wmtBorder1L wmtBorder1B'); ?> ">
		<?php if($portal_mode && ($prev['classification'] != 9)) { ?>
			&nbsp;<?php echo $prev['referredby']; ?>
		<?php } else { ?>
			<input name="ps_referredby_<?php echo $cnt; ?>" id="ps_referredby_<?php echo $cnt; ?>" class="<?php echo (($portal_mode)?'wmtFullInput':'wmtFullInput'); ?><?php echo ((!$portal_mode && $prev['classification'] == '9')?' wmtHighlight':''); ?>" type='text' tabindex='-1' value="<?php echo $prev['referredby']; ?>" />
		<?php } ?>
		</td>

		<td class="<?php echo (($portal_mode)?'wmtBody':'wmtBorder1L wmtBorder1B'); ?>">
		<?php if(!$portal_mode || ($prev['classification'] == 9)) { ?>
			<div class="wmtListButton"><a class='css_button_small' tabindex='-1' onClick="return SubmitLinkBuilder('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $cnt; ?>','<?php echo $id; ?>','updatesurg','ps_id_','Previous Surgery','<?php echo $prev['num_links']; ?>');" href='javascript:;'><span>Update</span></a></div>
		<?php } ?>

<?php
if($unlink_allow) {
?>
		<div class="wmtListButton"><a class='css_button_small' tabindex='-1' onClick="return SubmitLinkBuilder('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $cnt; ?>','<?php echo $id; ?>','unlinksurg','ps_id_','Previous Surgery','<?php echo $prev['num_links']; ?>');" href='javascript:;'><span>Un-Link</span></a></div>
<?php 
}
if($delete_allow || ($portal_mode && ($prev['classification'] == 9))) {
?>
		<div class="wmtListButton"><a class='css_button_small' tabindex='-1' onClick="return SubmitLinkBuilder('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $cnt; ?>','<?php echo $id; ?>','delsurg','ps_id_','Previous Surgery','<?php echo $prev['num_links']; ?>');" href='javascript:;'><span>Delete</span></a></div>
<?php
}
?>
		</td>
	</tr>
<?php
		if($prev['classification'] == 9) { $portal_data_exists = true; }
		$bg = ($bg == 'wmtAltLight' ? 'wmtLight' : 'wmtAltLight');	
		$cnt++;
	}
}
?>
	<tr class="<?php echo ($portal_mode) ? $bg : ''; ?>">
		<td class="<?php echo (($portal_mode)?'wmtDateCell':'wmtDateCell'); ?> wmtBorder1B" ><input name='ps_begdate' id='ps_begdate' class="<?php echo (($portal_mode)?'wmtInput':'wmtInput'); ?>" type="text" style="width: 90px;" title="YYYY-MM-DD" value="<?php echo $dt{'ps_begdate'}; ?>" /></td>
		<td class="<?php echo (($portal_mode)?'':'wmtBorder1L'); ?> wmtBorder1B" ><input name='ps_title' id='ps_title' class="<?php echo (($portal_mode)?'wmtFullInput':'wmtFullInput'); ?>" type='text' value="<?php echo $dt{'ps_title'}; ?>" /></td>
		<td class="<?php echo (($portal_mode)?'':'wmtBorder1L'); ?> wmtBorder1B" ><select name="ps_hospitalized" id="ps_hospitalized" class="<?php echo (($portal_mode)?'wmtFullInput':'wmtFullInput'); ?>">
			<?php echo ListSel($dt['ps_hospitalized'],'YesNo'); ?></select></td>
		<td class="<?php echo (($portal_mode)?'':'wmtBorder1L'); ?> wmtBorder1B" ><input name='ps_comments' id='ps_comments' class="<?php echo (($portal_mode)?'wmtFullInput':'wmtFullInput'); ?>" type='text' value="<?php echo $dt{'ps_comments'}; ?>" /></td>
		<td class="<?php echo (($portal_mode)?'':'wmtBorder1L'); ?> wmtBorder1B" ><input name='ps_referredby' id='ps_referredby' class="<?php echo (($portal_mode)?'wmtFullInput':'wmtFullInput'); ?>" type='text' value="<?php echo $dt{'ps_referredby'}; ?>" /></td>
		<td class="<?php echo (($portal_mode)?'':'wmtBorder1L'); ?> wmtBorder1B" >&nbsp;</td>
	</tr>

	<tr class="wmtColorBar">
		<td class="<?php echo (((checkSettingMode('wmt::fyi_surg_nt','',$frmdir) || (isset($dt['fyi_surg_nt']))) || $portal_mode)?'wmtBorder1B' : ''); ?>" colspan="2"><div style="padding-left: 8px;"><a class="css_button" onClick="return SubmitLinkBuilder('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','','<?php echo $id; ?>','surg','ps_','Previous Surgery');" href='javascript:;'><span>Add Another</span></a></div></td>
		<td class="<?php echo (((checkSettingMode('wmt::fyi_surg_nt','',$frmdir) || (isset($dt['fyi_surg_nt']))) || $portal_mode)?'wmtBorder1B' : ''); ?>" colspan="5"><div style="float: right; padding-right: 12px;"><i><b><?php echo ((!$portal_mode && $portal_data_exists)?'** Highlighted items have been entered through the portal, \'Update\' to Verify/Accept':'&nbsp;'); ?></b></i></div></td>
	</tr>

<?php // if(checkSettingMode('wmt::fyi_surg_nt','',$frmdir) || $portal_mode) { ?>
	<tr>
		<td class="<?php echo $portal_mode ? 'wmtLabel' : 'wmtLabel'; ?>">Notes:</td>
	</tr>
	<tr>
		<td colspan="6"><textarea name="fyi_surg_nt" id="fyi_surg_nt" rows="4" class="<?php echo $portal_mode ? 'wmtFullInput' : 'wmtFullInput'; ?>"><?php echo htmlspecialchars($dt['fyi_surg_nt'], ENT_QUOTES, '', FALSE); ?></textarea></td>
	</tr>
</table>
<?php
if($pat_entries_exist && !$portal_mode) {
	if($pat_entries['fyi_surg_nt']['content'] && (strpos($dt{$field_prefix.'fyi_surg_nt'},$pat_entries['fyi_surg_nt']['content']) === false)) {
?>
		<div class="wmtLabel wmtPortalData wmtL" style="margin: 6px;">Notes input by the patient via the portal:</div>
	<div class="wmtPortalData wmtBorderHighlight wmtBody wmtL" style="margin: 6px;" id="tmp_<?php echo $field_prefix; ?>fyi_surg_nt" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['fyi_surg_nt']['content'], ENT_QUOTES, '', FALSE); ?></div>
<?php
	}
}
?>

