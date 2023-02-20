<?php
// if(!isset($dt['fyi_pmh_nt'])) { $dt['fyi_pmh_nt'] = ''; }
if(!isset($pmh)) $pmh = array();
if(!isset($unlink_allow)) $unlink_allow = false;
if(!isset($portal_mode)) $portal_mode = false;
if(!isset($field_prefix)) $field_prefix = '';
if(!isset($frmdir)) $frmdir = '';
if(!isset($pat_entries_exist)) $pat_entries_exist = false;
if(!isset($dt['pmh_type'])) $dt['pmh_type'] = '';
if(!isset($dt['pmh_nt'])) $dt['pmh_nt'] = '';
if(!isset($dt['pmh_hospitalized'])) $dt['pmh_hospitalized'] = '';
if(!isset($dt['fyi_pmh_nt'])) $dt['fyi_pmh_nt'] = '';
if(!isset($pat_entries['fyi_pmh_nt'])) 
		$pat_entries['fyi_pmh_nt'] = $portal_data_layout;
$delete_allow = \OpenEMR\Common\Acl\AclMain::aclCheckCore('admin','super');
?>
	<table width='100%' border='0' cellspacing='0' cellpadding='3'>
		<tr>
			<td class="<?php echo (($portal_mode)?'bkkLabel bkkC bkkBorder1B':'wmtLabel wmtC wmtBorder1B'); ?>">Issue</td>
			<td class="<?php echo (($portal_mode)?'bkkLabel bkkC bkkBorder1B':'wmtLabel wmtC wmtBorder1L wmtBorder1B'); ?>">Notes</td>
<?php if($portal_mode) { ?>
			<td class="bkkBorder1B" style="width: 115px">&nbsp;</td>

<?php } else if($delete_allow && $unlink_allow) { ?>
			<td class="wmtLabel wmtBorder1L wmtBorder1B" style="width: 175px">&nbsp;</td>
<?php } else if($unlink_allow) { ?>
			<td class="wmtLabel wmtBorder1L wmtBorder1B" style="width: 115px">&nbsp;</td>
<?php } else if($delete_allow) { ?>
			<td class="wmtLabel wmtBorder1L wmtBorder1B" style="width: 115px">&nbsp;</td>
<?php } else { ?>
			<td class="wmtLabel wmtBorder1L wmtBorder1B" style="width: 65px">&nbsp;</td>
<?php } ?>
		</tr>
<?php
$bg = 'bkkLight';
$cnt=1;
$portal_data_exists = false;
if(count($pmh) > 0) {
	foreach($pmh as $prev) {
?>
		<tr class="<?php echo (($portal_mode)? $bg : ''); ?><?php echo ((!$portal_mode && ($prev['classification'] == 9))?'wmtHighlight':''); ?>">
		<td class="<?php echo (($portal_mode)?'bkkBody':'wmtBorder1B'); ?>"><input name="pmh_id_<?php echo $cnt; ?>" id="pmh_id_<?php echo $cnt; ?>" type="hidden" readonly="readonly" value="<?php echo $prev['id']; ?>" /><input name='pmh_num_links_<?php echo $cnt; ?>' id='pmh_num_links_<?php echo $cnt; ?>' type='hidden' tabstop='-1' value="<?php echo $prev['pmh_num_links']; ?>" />
		<?php if(($portal_mode && ($prev['classification'] != 9))) { ?>
			&nbsp;<?php echo htmlspecialchars(ListLook($prev['pmh_type'],'Medical_History_Problems'), ENT_NOQUOTES); ?>
		<?php } else { ?>
			<select name='pmh_type_<?php echo $cnt; ?>' id='pmh_type_<?php echo $cnt; ?>' class="<?php echo (($portal_mode)?'bkkFullInput':'wmtFullInput'); ?>" tabindex="-1">
			<?php ListSelAlpha($prev['pmh_type'],'Medical_History_Problems'); ?>
			</select>
		<?php } ?>
		</td>

		<td class="<?php echo (($portal_mode)?'bkkBody':'wmtBorder1L wmtBorder1B'); ?>">
		<?php if($portal_mode && ($prev['classification'] != 9)) { ?>
			&nbsp;<?php echo htmlspecialchars($prev['pmh_nt'], ENT_NOQUOTES); ?>
		<?php } else { ?>
			<input name="pmh_nt_<?php echo $cnt; ?>" id="pmh_nt_<?php echo $cnt; ?>" class="<?php echo(($portal_mode)?'bkkFullInput':'wmtFullInput'); ?>" type="text" tabindex="-1" value="<?php echo htmlspecialchars($prev['pmh_nt'], ENT_NOQUOTES); ?>" />
		<?php } ?>
		<input name="pmh_hospitalized_<?php echo $cnt; ?>" id="pmh_hospitalized_<?php echo $cnt; ?>" type="hidden" value="<?php echo $prev['extrainfo']; ?>" /></td>

		<td class="<?php echo (($portal_mode)?'bkkBody':'wmtBorder1L wmtBorder1B'); ?> btnActContainer">
		<?php if(!$portal_mode || ($portal_mode && $prev['classification'] == '9')) { ?>
			<div class="wmtListButton"><a class="css_button_small" href="javascript:;" tabindex="-1" onClick="return SubmitLinkBuilder('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $cnt; ?>','<?php echo $id; ?>','updatepmh','pmh_id_','Medical History','<?php echo $prev['pmh_num_links']; ?>');" ><span>Update</span></a></div>
		<?php } ?>
		<?php if($unlink_allow) { ?>
			<div class="wmtListButton"><a class="css_button_small" href="javascript:;" tabindex="-1" onClick="return SubmitLinkBuilder('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $cnt; ?>','<?php echo $id; ?>','unlinkpmh','pmh_id_','Medical History','<?php echo $prev['pmh_num_links']; ?>');" ><span>Un-Link</span></a></div>
		<?php } ?>
		<?php if($delete_allow || ($portal_mode && $prev['classification'] == 9)) { ?>
			<div class="wmtListButton"><a class="css_button_small" tabindex="-1" href="javascript:;" onClick="return SubmitLinkBuilder('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $cnt; ?>','<?php echo $id; ?>','delpmh','pmh_id_','Medical History','<?php echo $prev['pmh_num_links']; ?>');" ><span>Delete</span></a></div>
		<?php } ?>
			</td>
		</tr>
<?php
		if($prev['classification'] == 9) $portal_data_exists = true;
		$bg = ($bg == 'bkkAltLight' ? 'bkkLight' : 'bkkAltLight');
		$cnt++;
	}
}
?>
		<tr class="<?php echo ($portal_mode) ? $bg : ''; ?>">
			<td class="<?php echo (($portal_mode)?'bkkBorder1B':'wmtBorder1B'); ?>"><select name="pmh_type" id="pmh_type" class="<?php echo (($portal_mode)?'bkkFullInput':'wmtFullInput'); ?>">
			<?php ListSelAlpha($dt{'pmh_type'},'Medical_History_Problems'); ?>
			</select></td>

			<td class="<?php echo (($portal_mode)?'bkkBorder1B':'wmtBorder1L wmtBorder1B'); ?>"><input name="pmh_nt" id="pmh_nt" class="<?php echo (($portal_mode)?'bkkFullInput':'wmtFullInput'); ?>" type="text" value="<?php echo $dt{'pmh_nt'}; ?>" /><input name="pmh_hospitalized" id="pmh_hospitalized" type="hidden" value="<?php echo $dt['pmh_hospitalized']; ?>" /></td>
			<td class="<?php echo (($portal_mode)?'bkkBorder1B':'wmtBorder1L wmtBorder1B'); ?>">&nbsp;</td>
		</tr>
		<tr class="wmtColorBar">
			<td  colspan="3" class="<?php echo ((checkSettingMode('wmt::fyi_pmh_nt') && (isset($dt['fyi_pmh_nt'])) && !$portal_mode)?'wmtBorder1B' : ''); ?><?php echo (($portal_mode)?'bkkBorder1B':''); ?>" >
				<div style="float: left;"><a class='css_button' href="javascript:;" style="padding-left: 8px;" onClick="return SubmitLinkBuilder('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','','<?php echo $id; ?>','pmh','pmh_id_','Medical History');" ><span>Add Another</span></a></div>
			<?php if(!$portal_mode) { ?>
				<div style="float: left; padding-left: 8px;"><a href="javascript:;" class='css_button' onClick="return add_item('pmh_type','Medical_History_Problems');" ><span>Add An Issue Type</span></a></div>
			<?php } ?>
			<?php if(!$portal_mode) { 
			include($GLOBALS['srcdir'].'/wmt-v2/btn_view_documents.inc.php');
			} ?>
			<?php if(!$portal_mode && $portal_data_exists) { ?>
				<div style="float: right; padding-right: 12px;"><i><b>** Highlighted items have been entered through the portal, 'Update' to Verify/Accept</b></i></div>
			<?php } ?>
			</td>
		</tr>

<?php // if(checkSettingMode('wmt::fyi_pmh_nt','',$frmdir) || $portal_mode) { ?>
	<tr>
		<td class="<?php echo $portal_mode ? 'bkkLabel' : 'wmtLabel'; ?>">Notes:</td>
	</tr>
	<tr>
		<td colspan="3"><textarea name="fyi_pmh_nt" id="fyi_pmh_nt" rows="4" class="<?php echo $portal_mode ? 'bkkFullInput' : 'wmtFullInput'; ?>"><?php echo htmlspecialchars($dt['fyi_pmh_nt'], ENT_QUOTES, '', FALSE); ?></textarea></td>
	</tr>
</table>
<?php
if($pat_entries_exist && !$portal_mode) {
	if($pat_entries['fyi_pmh_nt']['content'] && (strpos($dt{'fyi_pmh_nt'},$pat_entries['fyi_pmh_nt']['content']) === false)) {
?>
		<div class="wmtLabel wmtPortalData wmtL" style="margin: 6px;">Notes input by the patient via the portal:</div>
		<div class="wmtPortalData wmtBorderHighlight wmtBody wmtL" style="margin: 6px;" id="tmp_<?php echo $field_prefix; ?>fyi_pmh_nt" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['fyi_pmh_nt']['content'], ENT_QUOTES, '', FALSE); ?></div>
<?php
	}
}
?>
