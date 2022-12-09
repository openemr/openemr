<?php
if(!isset($client_id)) $client_id = '';
if(!isset($unlink_allow)) $unlink_allow = false;
if(!isset($portal_mode)) $portal_mode = false;
if(!isset($field_prefix)) $field_prefix = '';
if(!isset($pat_entries_exist)) $pat_entries_exist = false;
if(!isset($pat_entries['fyi_fh_nt'])) 
				$pat_entries['fyi_fh_nt'] = $portal_data_layout;
if(!isset($dt['fyi_fh_nt'])) $dt['fyi_fh_nt'] = '';
if(!isset($dt['fh_who'])) $dt['fh_who'] = '';
if(!isset($dt['fh_dead'])) $dt['fh_dead'] = '';
if(!isset($dt['fh_age'])) $dt['fh_age'] = '';
if(!isset($dt['fh_age_dead'])) $dt['fh_age_dead'] = '';
if(!isset($dt['fh_type'])) $dt['fh_type'] = '';
if(!isset($dt['fh_nt'])) $dt['fh_nt'] = '';
if(!isset($dt['db_fh_non_contrib'])) $dt['db_fh_non_contrib'] = '';
if(!isset($dt['db_fh_adopted'])) $dt['db_fh_adopted'] = '';
$delete_allow = \OpenEMR\Common\Acl\AclMain::aclCheckCore('admin','super');
$fh_ros_position = strtolower(checkSettingMode('wmt::fh_ros_display','',$frmdir));
if($fh_ros_position == 'top') {
	include($GLOBALS['srcdir'].'/wmt-v2/family_history_ros.inc.php');
}
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr <?php echo ($fh_ros_position == 'top') ? 'class="bkkBorder1T"' : ''; ?>>
		<td class="<?php echo (($portal_mode)?'bkkLabel bkkC bkkBorder1B':'wmtLabel wmtC wmtBorder1B'); ?>">Who</td>
		<td class="<?php echo (($portal_mode)?'bkkLabel bkkC bkkBorder1B':'wmtLabel wmtC wmtBorder1L wmtBorder1B'); ?>">Deceased</td>
<?php if($client_id != 'wcs') { ?>
		<td class="<?php echo (($portal_mode)?'bkkLabel bkkC bkkBorder1B':'wmtLabel wmtC wmtBorder1L wmtBorder1B'); ?>" style='width: 105px;'>Current Age</td>
<?php } ?>
		<td class="<?php echo (($portal_mode)?'bkkLabel bkkC bkkBorder1B':'wmtLabel wmtC wmtBorder1L wmtBorder1B'); ?>" style='width: 105px;'>Age at Death</td>
		<td class="<?php echo (($portal_mode)?'bkkLabel bkkC bkkBorder1B':'wmtLabel wmtC wmtBorder1L wmtBorder1B'); ?>">Condition</td>
		<td class="<?php echo (($portal_mode)?'bkkLabel bkkC bkkBorder1B':'wmtLabel wmtC wmtBorder1L wmtBorder1B'); ?>">Notes</td>
<?php if($delete_allow && $unlink_allow) { ?>
		<td class="<?php echo (($portal_mode)?'bkkLabel bkkC bkkBorder1B':'wmtLabel wmtC wmtBorder1L wmtBorder1B'); ?>" style='width: 175px'>&nbsp;</td>
<?php } else if($delete_allow || $portal_mode) { ?>
		<td class="<?php echo (($portal_mode)?'bkkLabel bkkC bkkBorder1B':'wmtLabel wmtC wmtBorder1L wmtBorder1B'); ?>" style='width: 115px'>&nbsp;</td>
<?php } else if($unlink_allow) { ?>
		<td class="<?php echo (($portal_mode)?'bkkLabel bkkC bkkBorder1B':'wmtLabel wmtC wmtBorder1L wmtBorder1B'); ?>" style='width: 115px'>&nbsp;</td>
<?php } else { ?>
		<td class="<?php echo (($portal_mode)?'bkkLabel bkkC bkkBorder1B':'wmtLabel wmtC wmtBorder1L wmtBorder1B'); ?>" style='width: 65px'>&nbsp;</td>
<?php } ?>
	</tr>
<?php
$bg = 'bkkLight';
$cnt=1;
$portal_data_exists = false;
if(isset($fh)) {
	foreach($fh as $prev) {
?>
	<tr class="<?php echo (($portal_mode)? $bg : ''); ?><?php echo ((!$portal_mode && $prev['classification'] == 9)?' wmtHighlight':''); ?>">
		<td class="<?php echo (($portal_mode)?'bkkBody':'wmtBorder1B'); ?>"><input name="fh_id_<?php echo $cnt; ?>" id="fh_id_<?php echo $cnt; ?>" type="hidden" readonly="readonly" value="<?php echo $prev['id']; ?>" /><input name="fh_num_links_<?php echo $cnt; ?>" id="fh_num_links_<?php echo $cnt; ?>" type="hidden" tabindex="-1" value="<?php echo $prev['fh_num_links']; ?>" />
		<?php if((!$portal_mode || $prev['classification'] == 9)) { ?>
			<select name="fh_who_<?php echo $cnt; ?>" id="fh_who_<?php echo $cnt; ?>" class="<?php echo (($portal_mode)?'bkkFullInput':'wmtFullInput'); ?>" tabindex="-1">
			<?php ListSel($prev['fh_who'],'Family_Relationships'); ?>
		</select>
		<?php } else { ?>
			<?php echo ListLook($prev['fh_who'],'Family_Relationships'); ?>
		<?php } ?>
		</td>

		<td class="<?php echo (($portal_mode)?'bkkBody':'wmtBorder1L wmtBorder1B'); ?>">
		<?php if((!$portal_mode || $prev['classification'] == 9)) { ?>
			<select name="fh_dead_<?php echo $cnt; ?>" id="fh_dead_<?php echo $cnt; ?>" class="<?php echo (($portal_mode)?'bkkFullInput':'wmtFullInput'); ?>" tabindex="-1">
			<?php ListSel($prev['fh_deceased'],'YesNo'); ?>
		</select>
		<?php } else { ?>
			<?php echo ListLook($prev['fh_deceased'],'YesNo'); ?>
		<?php } ?>

		<?php if($client_id != 'wcs') { ?>
		</td>
		<td class="<?php echo (($portal_mode)?'bkkBody':'wmtBorder1L wmtBorder1B'); ?>">
			<?php if((!$portal_mode || $prev['classification'] == 9)) { ?>
				<input name="fh_age_<?php echo $cnt; ?>" id="fh_age_<?php echo $cnt; ?>" class="<?php echo (($portal_mode)?'bkkFullInput':'wmtFullInput'); ?>" type="text" tabindex="-1" value="<?php echo htmlspecialchars($prev['fh_age'], ENT_QUOTES, '', FALSE); ?>" /></td>
			<?php } else { ?>
				<?php echo htmlspecialchars($prev['fh_age'], ENT_QUOTES, '', FALSE); ?>
			<?php } ?>
		<?php } else { ?>
			<input name="fh_age_<?php echo $cnt; ?>" id="fh_age_<?php echo $cnt; ?>" type="hidden" tabindex="-1" value="<?php echo $prev['fh_age']; ?>" /></td>
		<?php } ?>

		<td class="<?php echo (($portal_mode)?'bkkBody':'wmtBorder1L wmtBorder1B'); ?>">
			<?php if((!$portal_mode || $prev['classification'] == 9)) { ?>
			<input name="fh_age_dead_<?php echo $cnt; ?>" id="fh_age_dead_<?php echo $cnt; ?>" class="<?php echo (($portal_mode)?'bkkFullInput':'wmtFullInput'); ?>" type="text" tabindex="-1" value="<?php echo htmlspecialchars($prev['fh_age_dead'], ENT_QUOTES, '', FALSE); ?>" />
			<?php } else { ?>
				<?php echo htmlspecialchars($prev['fh_age_dead'], ENT_QUOTES, '', FALSE); ?>
			<?php } ?>
		</td>

		<td class="<?php echo (($portal_mode)?'bkkBody':'wmtBorder1L wmtBorder1B'); ?>">
			<?php if((!$portal_mode || $prev['classification'] == 9)) { ?>
			<select name="fh_type_<?php echo $cnt; ?>" id="fh_type_<?php echo $cnt; ?>" class="<?php echo (($portal_mode)?'bkkFullInput':'wmtFullInput'); ?>" tabindex="-1">
	 			<?php ListSelALpha($prev['fh_type'],'Family_History_Problems'); ?>
			</select>
			<?php } else { ?>
				<?php echo ListLook($prev['fh_type'],'Family_History_Problems'); ?>
			<?php } ?>
		</td>

		<td class="<?php echo (($portal_mode)?'bkkBody':'wmtBorder1L wmtBorder1B'); ?>">
			<?php if((!$portal_mode || $prev['classification'] == 9)) { ?>
			<input name="fh_nt_<?php echo $cnt; ?>" id="fh_nt_<?php echo $cnt; ?>" class="<?php echo (($portal_mode)?'bkkFullInput':'wmtFullInput'); ?>" type="text" value="<?php echo htmlspecialchars($prev['fh_nt'], ENT_QUOTES, '', FALSE); ?>" />
			<?php } else { ?>
				<?php echo htmlspecialchars($prev['fh_nt'], ENT_QUOTES, '', FALSE); ?>
			<?php } ?>
		</td>

		<td class="<?php echo (($portal_mode)?'bkkBody':'wmtBorder1L wmtBorder1B'); ?>">
			<?php if((!$portal_mode || $prev['classification'] == 9)) { ?>
			<div style="float: left; padding-left: 2px;"><a class="css_button_small" href="javascript:;" tabindex="-1" onClick="return SubmitLinkBuilder('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $cnt; ?>','<?php echo $id; ?>','updatefh','fh_id_','Family History','<?php echo $prev['fh_num_links']; ?>');" ><span>Update</span></a></div>
			<?php } ?>
			<?php if($unlink_allow) { ?>
			<div style="float: left; padding-left: 2px;"><a class="css_button_small" href="javascript:;" tabindex="-1" onClick="return SubmitLinkBuilder('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $cnt; ?>','<?php echo $id; ?>','unlinkfh','fh_id_','Family History','<?php echo $prev['fh_num_links']; ?>');" ><span>Un-Link</span></a></div>
			<?php } ?>
			<?php if($delete_allow || ($portal_mode && ($prev['classification'] == 9))) { ?>
			<div style="float: left; padding-left: 2px;"><a class="css_button_small" href="javascript:;" tabindex="-1" onClick="return SubmitLinkBuilder('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $cnt; ?>','<?php echo $id; ?>','delfh','fh_id_','Family History','<?php echo $prev['fh_num_links']; ?>');" ><span>Delete</span></a></div>
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
		<td class="<?php echo (($portal_mode)?'bkkBorder1B':'wmtBorder1B'); ?>"><select name="fh_who" id="fh_who" class="<?php echo (($portal_mode)?'bkkFullInput':'wmtFullInput'); ?>" onchange="get_family_defaults();">
	<?php ListSel($dt{'fh_who'},'Family_Relationships'); ?>
			</select></td>
		<td class="<?php echo (($portal_mode)?'bkkBorder1B':'wmtBorder1L wmtBorder1B'); ?>"><select name="fh_dead" id="fh_dead" class="<?php echo (($portal_mode)?'bkkFullInput':'wmtFullInput'); ?>">
	<?php ListSel($dt{'fh_dead'},'YesNo'); ?>
			</select>
	<?php if($client_id != 'wcs') { ?>
		</td>
		<td class="<?php echo (($portal_mode)?'bkkBorder1B':'wmtBorder1L wmtBorder1B'); ?>"><input name="fh_age" id="fh_age" class="<?php echo (($portal_mode)?'bkkFullInput':'wmtFullInput'); ?>" type="text" value="<?php echo htmlspecialchars($dt{'fh_age'}, ENT_QUOTES, '', FALSE); ?>" /></td>
	<?php } else { ?>
			<input name="fh_age" id="fh_age" type="hidden" value="<?php echo $dt{'fh_age'}; ?>" /></td>
	<?php } ?>
		<td class="<?php echo (($portal_mode)?'bkkBorder1B':'wmtBorder1L wmtBorder1B'); ?>"><input name="fh_age_dead" id="fh_age_dead" class="<?php echo (($portal_mode)?'bkkFullInput':'wmtFullInput'); ?>" type="text" value="<?php echo htmlspecialchars($dt{'fh_age_dead'}, ENT_QUOTES, '', FALSE); ?>" /></td>
		<td class="<?php echo (($portal_mode)?'bkkBorder1B':'wmtBorder1L wmtBorder1B'); ?>"><select name="fh_type" id="fh_type" class="<?php echo (($portal_mode)?'bkkFullInput':'wmtFullInput'); ?>">
	<?php ListSelAlpha($dt{'fh_type'},'Family_History_Problems'); ?>
			</select></td>
		<td class="<?php echo (($portal_mode)?'bkkBorder1B':'wmtBorder1L wmtBorder1B'); ?>"><input name="fh_nt" id="fh_nt" class="<?php echo (($portal_mode)?'bkkFullInput':'wmtFullInput'); ?>" type="text" value="<?php echo htmlspecialchars($dt{'fh_nt'}, ENT_QUOTES, '', FALSE); ?>" /></td>
		<td class="<?php echo (($portal_mode)?'bkkBorder1B':'wmtBorder1L wmtBorder1B'); ?>">&nbsp;</td>
	</tr>
	<tr>
	<?php 
	$cols=3;
	if($client_id != 'wcs') $cols=4;
	?>
		<td class="<?php echo (($portal_mode)?'bkkCollapseBar':'wmtCollapseBar'); ?>"><div><a class="css_button" href="javascript:;" onClick="return SubmitLinkBuilder('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','','<?php echo $id; ?>','fh','fh_','Family History');" ><span>Add Another</span></a></div></td>
		<td class="<?php echo (($portal_mode)?'bkkCollapseBar':'wmtCollapseBar'); ?>" colspan="2">
		<?php if(!$portal_mode) { ?>
			<a class="css_button" href="javascript:;" onClick="return add_item('fh_type','Family_History_Problems');" ><span>Add A Condition Type</span></a></div>
		<?php } ?>
		&nbsp;</td>
		<td class="<?php echo (($portal_mode)?'bkkCollapseBar':'wmtCollapseBar'); ?>" colspan="4">&nbsp;
		<?php if(!$portal_mode && $portal_data_exists) { ?>
			<div style="float: right; padding-right: 12px;"><b><i>** Highlighted items have been entered through the portal, 'Update' to Verify/Accept</i></b></div>
		<?php } ?>
		</td>
	</tr>
	<tr>
		<td class="<?php echo $portal_mode ? 'bkkLabel bkkBorder1T' : 'wmtLabel wmtBorder1T'; ?>" colspan="<?php echo $cols; ?>"><input name="db_fh_non_contrib" id="db_fh_non_contrib" type="checkbox" value="1" <?php echo (($dt{'db_fh_non_contrib'} == 1)?'checked':''); ?> /><label for="db_fh_non_contrib">&nbsp;&nbsp;Family History is Non-Contributory</label></td>
		<td class="<?php echo $portal_mode ? 'bkkLabel bkkBorder1T' : 'wmtLabel wmtBorder1T'; ?>" colspan="3"><input name="db_fh_adopted" id="db_fh_adopted" type="checkbox" value="1" <?php echo (($dt{'db_fh_adopted'} == 1)?'checked':''); ?> /><label for="db_fh_adopted">&nbsp;&nbsp;Patient is Adopted</label></td>
	</tr>
</table>
<?php
if($fh_ros_position == 'middle') {
	include($GLOBALS['srcdir'].'/wmt-v2/family_history_ros.inc.php');
}

$field_name = 'fyi_fh_nt';
include($GLOBALS['srcdir'].'/wmt-v2/specified_text_box.inc.php');

if($pat_entries_exist && !$portal_mode) {
	if($pat_entries['fyi_fh_nt']['content'] && (strpos($dt{$field_prefix.'fyi_fh_nt'},$pat_entries['fyi_fh_nt']['content']) === false)) {
?>
		<div class="wmtLabel wmtPortalData wmtL" style="margin: 6px;">Notes input by the patient via the portal:</div>
		<div class="wmtPortalData wmtBorderHighlight wmtBody wmtL"  style="margin: 6px;" id="tmp_<?php echo $field_prefix; ?>fyi_fh_nt" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['fyi_fh_nt']['content'], ENT_QUOTES, '', FALSE); ?></div>
<?php
	}
}
if($fh_ros_position == 'bottom') {
	include($GLOBALS['srcdir'].'/wmt-v2/family_history_ros.inc.php');
}
?>
