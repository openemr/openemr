<?php
if(!isset($hosp)) $hosp = array();
if(isset($local_fields)) unset($local_fields);
$local_fields = array('hosp_dt', 'hosp_type', 'hosp_why', 'hosp_nt');
include(FORM_BRICKS . 'module_setup.inc.php');
$note_field = 'fyi_admissions_nt';
?>

	<table width='100%' border='0' cellspacing='0' cellpadding='2'>
		<tr>
			<td class="<?php echo ($portal_mode) ? 'wmtLabel wmtC wmtBorder1B wmtDateCell' : 'wmtLabel wmtC wmtBorder1B wmtDateCell'; ?>">Date</td>
			<td class="<?php echo ($portal_mode) ? 'wmtLabel wmtC wmtBorder1B': 'wmtLabel wmtC wmtBorder1L wmtBorder1B'; ?>">Facility</td>
			<td class="<?php echo ($portal_mode) ? 'wmtLabel wmtC wmtBorder1B': 'wmtLabel wmtC wmtBorder1L wmtBorder1B'; ?>">Reason</td>
			<td class="<?php echo ($portal_mode) ? 'wmtLabel wmtC wmtBorder1B': 'wmtLabel wmtC wmtBorder1L wmtBorder1B'; ?>">Comments</td>
<?php if($portal_mode) { ?>
			<td class="wmtBorder1B" style='width: 115px'>&nbsp;</td>
<?php } else if($delete_allow && $unlink_allow) { ?>
			<td class="wmtBorder1L wmtBorder1B" style='width: 185px'>&nbsp;</td>
<?php } else if($unlink_allow) { ?>
			<td class="wmtBorder1L wmtBorder1B" style='width: 125px'>&nbsp;</td>
<?php } else if($delete_allow) { ?>
			<td class="wmtBorder1L wmtBorder1B" style='width: 125px'>&nbsp;</td>
<?php } else { ?>
			<td class="wmtBorder1L wmtBorder1B" style='width: 65px'>&nbsp;</td>
<?php } ?>
		</tr>
<?php
$bg = 'wmtLight';
$cnt=1;
$portal_data_exists = false;
foreach($hosp as $prev) {
?>
		<tr class="<?php echo ($portal_mode) ? $bg : ''; ?><?php echo (!$portal_mode && ($prev['classification'] == 9)) ? 'wmtHighlight' : ''; ?>">
			<td class="<?php echo ($portal_mode) ? 'wmtBody' : 'wmtBorder1B'; ?>"><input name='hosp_id_<?php echo $cnt; ?>' id='hosp_id_<?php echo $cnt; ?>' type='hidden' readonly='readonly' value="<?php echo $prev['id']; ?>" /><input name='hosp_num_links_<?php echo $cnt; ?>' id='hosp_num_links_<?php echo $cnt; ?>' type='hidden' tabindex='-1' value="<?php echo $prev['num_links']; ?>" />
			<?php if($portal_mode && ($prev['classification'] != 9)) { ?>
				&nbsp;<?php echo htmlspecialchars($prev['begdate'],ENT_QUOTES, '', FALSE); ?>
			<?php } else { ?>
				<input name='hosp_dt_<?php echo $cnt; ?>' id='hosp_dt_<?php echo $cnt; ?>' class="wmtFullInput dInput" type='text' tabindex='-1' value="<?php echo htmlspecialchars($prev['begdate'], ENT_QUOTES, '', FALSE); ?>" />
			<?php } ?>
			</td>

			<td class="<?php echo ($portal_mode) ? 'wmtBody' : 'wmtBorder1L wmtBorder1B'; ?>">
			<?php if($portal_mode && ($prev['classification'] != 9)) { ?>
				&nbsp;<?php echo htmlspecialchars($prev['extrainfo'],ENT_QUOTES, '', FALSE); ?>
			<?php } else { ?>
				<input name='hosp_type_<?php echo $cnt; ?>' id='hosp_type_<?php echo $cnt; ?>' class="wmtFullInput" type='text' tabindex='-1' value="<?php echo htmlspecialchars($prev['extrainfo'], ENT_QUOTES, '', FALSE); ?>" />
			<?php } ?>
			</td>

			<td class="<?php echo ($portal_mode) ? 'wmtBody' : 'wmtBorder1L wmtBorder1B'; ?>">
			<?php if($portal_mode && ($prev['classification'] != 9)) { ?>
				&nbsp;<?php echo htmlspecialchars($prev['title'],ENT_QUOTES); ?>
			<?php } else { ?>
				<input name='hosp_why_<?php echo $cnt; ?>' id='hosp_why_<?php echo $cnt; ?>' class="wmtFullInput" type='text' tabindex='-1' value="<?php echo htmlspecialchars($prev['title'], ENT_QUOTES, '', FALSE); ?>" />
			<?php } ?>
			</td>
			<td class="<?php echo ($portal_mode) ? 'wmtBody' : 'wmtBorder1L wmtBorder1B'; ?>">
			<?php if($portal_mode && ($prev['classification'] != 9)) { ?>
				&nbsp;<?php echo htmlspecialchars($prev['comments'],ENT_QUOTES, '', FALSE); ?>
			<?php } else { ?>
				<input name='hosp_nt_<?php echo $cnt; ?>' id='hosp_nt_<?php echo $cnt; ?>' class="wmtFullInput" type='text' tabindex='-1' value="<?php echo htmlspecialchars($prev['comments'], ENT_QUOTES, '', FALSE); ?>" />
			<?php } ?>
			</td>
			<td class="<?php echo ($portal_mode) ? 'wmtBody' : 'wmtBorder1L wmtBorder1B'; ?> btnActContainer">
			<?php if(!$portal_mode || ($portal_mode && ($prev['classification'] == 9))) { ?>
				<div style="float: left; padding-left: 2px;"><a class='css_button_small' tabindex='-1' onClick="return SubmitLinkBuilder('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $cnt; ?>','<?php echo $id; ?>','updatehosp','hosp_id_','Admission','<?php echo $prev['num_links']; ?>');" href='javascript:;'><span>Update</span></a></div>
			<?php } ?>
<?php
		if($unlink_allow) {
			echo "<div style='float: left; padding-left: 2px;'><a class='css_button_small' tabindex='-1' onClick='return SubmitLinkBuilder(\"$base_action\",\"$wrap_mode\",\"$cnt\",\"$id\",\"unlinkhosp\",\"hosp_id_\",\"Admission\",\"{$prev['num_links']}\");' href='javascript:;'><span>Un-Link</span></a></div>\n";
		}
		if(\OpenEMR\Common\Acl\AclMain::aclCheckCore('admin','super')) {
			echo "<div style='float: left; padding-left: 2px;'><a class='css_button_small' tabindex='-1' onClick='return SubmitLinkBuilder(\"$base_action\",\"$wrap_mode\",\"$cnt\",\"$id\",\"delhosp\",\"hosp_id_\",\"Admission\",\"{$prev['num_links']}\");' href='javascript:;'><span>Delete</span></a></div>\n";
		}
	echo "</td>\n";
	echo "</tr>\n";
	if($prev['classification'] == 9) $portal_data_exists = true;
	$bg = ($bg == 'wmtAltLight' ? 'wmtLight' : 'wmtAltLight');
	$cnt++;
}
?>
		<tr class="<?php echo ($portal_mode) ? $bg : ''; ?>">
			<td class="<?php echo ($portal_mode) ? '' : 'wmtBorder1B'; ?>"><input name='hosp_dt' id='hosp_dt' class="<?php echo ($portal_mode) ? 'wmtFullInput' : 'wmtFullInput'; ?>" type='text' value="<?php echo htmlspecialchars($dt{'hosp_dt'}, ENT_QUOTES, '', FALSE); ?>" /></td>
			<td class="<?php echo ($portal_mode) ? '' : 'wmtBorder1L wmtBorder1B'; ?>"><input name='hosp_type' id='hosp_type' class="<?php echo ($portal_mode) ? 'wmtFullInput' : 'wmtFullInput'; ?>" type='text' value="<?php echo htmlspecialchars($dt{'hosp_type'}, ENT_QUOTES, '', FALSE); ?>" /></td>
			<td class="<?php echo ($portal_mode) ? '' : 'wmtBorder1L wmtBorder1B'; ?>"><input name='hosp_why' id='hosp_why' class="<?php echo ($portal_mode) ? 'wmtFullInput' : 'wmtFullInput'; ?>" type='text' value="<?php echo htmlspecialchars($dt{'hosp_why'}, ENT_QUOTES, '', FALSE); ?>" /></td>
			<td class="<?php echo ($portal_mode) ? '' : 'wmtBorder1L wmtBorder1B'; ?>"><input name='hosp_nt' id='hosp_nt' class="<?php echo ($portal_mode) ? 'wmtFullInput' : 'wmtFullInput'; ?>" type='text' value="<?php echo htmlspecialchars($dt{'hosp_nt'}, ENT_QUOTES, '', FALSE); ?>" /></td>
			<td class="<?php echo ($portal_mode) ? '' : 'wmtBorder1L wmtBorder1B'; ?>">&nbsp;</td>
		</tr>
		<tr class="wmtColorBar">
			<td class="<?php echo ($portal_mode) ? 'wmtBorder1T' : ''; ?>" colspan="5"><div style="float: left; padding-left: 12px;"><a class='css_button' onClick="return SubmitLinkBuilder('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','','<?php echo $id; ?>','hosp','hosp_','Admission');" href='javascript:;'><span>Add Another</span></a></div>
			<?php if(!$portal_mode && $portal_data_exists) { ?>
				<div style="float: right; padding-right: 12px;"><i><b>** Highlighted items have been entered through the portal, 'Update' to Verify/Accept</b></i></div>
			<?php } ?>
			</td>
		</tr>
<?php // if(checkSettingMode('wmt::fyi_admissions_nt','',$frmdir) || $portal_mode) { ?>
		<!-- tr>
			<td colspan="5" class="<?php echo $portal_mode ? 'wmtLabel wmtBorder1T' : 'wmtLabel wmtBorder1T'; ?>">Notes:</td>
		</tr>
		<tr>
			<td colspan="5"><textarea name="fyi_admissions_nt" id="fyi_admissions_nt" rows="4" class="<?php // echo $portal_mode ? 'wmtFullInput' : 'wmtFullInput'; ?>"><?php // echo htmlspecialchars($dt['fyi_admissions_nt'], ENT_QUOTES, '', FALSE); ?></textarea></td>
		</tr -->
</table>
<?php include(INC_DIR . 'list_note_section.inc.php'); ?>

