<?php
if(!isset($img)) $img = array();
if(isset($local_fields)) unset($local_fields);
$local_fields = array('img_dt', 'img_type', 'img_nt');
include(FORM_BRICKS . 'module_setup.inc.php');
$note_field = 'fyi_img_nt';
?>
<table width="100%" border="0" cellspacing="0" cellpadding="3" style="border-collapse: collapse;">
	<tr class="<?php echo $portal_mode ? 'wmtBorder1B' : 'wmtBorder1B'; ?>">
		<td class="wmtDateCell <?php echo $portal_mode ? 'wmtLabel wmtC' : 'wmtLabel wmtC'; ?>" style="width: 90px">Date</td>
		<td class="<?php echo $portal_mode ? 'wmtLabel wmtC' : 'wmtLabel wmtC'; ?>">Type</td>
		<td class="<?php echo $portal_mode ? 'wmtLabel wmtC' : 'wmtLabel wmtC wmtBorder1L'; ?>">Notes</td>
<?php if($delete_allow && $unlink_allow) { ?>
		<td class="<?php echo $portal_mode ? '' : 'wmtLabel wmtBorder1L'; ?>" style="width: 185px">&nbsp;</td>
<?php } else if($unlink_allow) { ?>
		<td class="<?php echo $portal_mode ? '' : 'wmtLabel wmtBorder1L'; ?>" style="width: 125px">&nbsp;</td>
<?php } else if($delete_allow || $portal_mode) { ?>
		<td class="<?php echo $portal_mode ? '' : 'wmtLabel wmtBorder1L'; ?>" style="width: 125px">&nbsp;</td>
<?php } else { ?>
		<td class="<?php echo $portal_mode ? '' : 'wmtLabel wmtBorder1LB'; ?>" style="width: 65px">&nbsp;</td>
<?php } ?>
	</tr>
<?php
$cnt=1;
$bg = 'wmtLight';
$portal_data_exists = false;
foreach($img as $prev) {
?>
	<tr class="<?php echo $portal_mode ? $bg.' ' : ''; ?><?php echo (!$portal_mode && ($prev['classification'] == 9)) ? 'wmtHighlight' : ''; ?><?php echo !$portal_mode ? ' wmtBorder1B' : ''; ?>">
		<td><input name="img_id_<?php echo $cnt; ?>" id="img_id_<?php echo $cnt; ?>" type="hidden" readonly="readonly" value="<?php echo $prev['id']; ?>" /><input name="img_num_links_<?php echo $cnt; ?>" id="img_num_links_<?php echo $cnt; ?>" type="hidden" tabindex="-1" value="<?php echo $prev['img_num_links']; ?>" />
		<?php if($portal_mode && ($prev['classification'] != 9)) { ?>
			<span class="wmtBody"><?php echo htmlspecialchars($prev['img_dt'], ENT_QUOTES, '', FALSE); ?>&nbsp;</span>
		<?php } else { ?>
			<input name="img_dt_<?php echo $cnt; ?>" id="img_dt_<?php echo $cnt; ?>" class="<?php echo $portal_mode ? 'wmtFullInput' : 'wmtFullInput dInput'; ?>" type="text" value="<?php echo htmlspecialchars($prev['img_dt'], ENT_QUOTES, '', FALSE); ?>" title="YYYY-MM-DD" />
		<?php } ?>
		</td>
		<td class="<?php echo $portal_mode ? '' : 'wmtBorder1B wmtBorder1L'; ?>">
		<?php if($portal_mode && ($prev['classification'] != 9)) { ?>
			<span class="wmtBody"><?php echo ListLook($prev['img_type'],'Image_Types'); ?>&nbsp;</span>
		<?php } else { ?>
			<select name="img_type_<?php echo $cnt; ?>" id="img_type_<?php echo $cnt; ?>" class="<?php echo $portal_mode ? 'wmtFullInput' : 'wmtFullInput'; ?>" tabindex="-1">
				<?php ListSelAlpha($prev['img_type'],'Image_Types'); ?>
			</select>
		<?php } ?>
		</td>
		<td class="<?php echo $portal_mode ? '' : 'wmtBorder1B wmtBorder1L'; ?>">
		<?php if ($portal_mode && ($prev['classification'] != 9)) { ?>
			<span class="wmtBody"><?php echo $prev['img_nt']; ?>&nbsp;</span>
		<?php } else { ?>
			<input name="img_nt_<?php echo $cnt; ?>" id="img_nt_<?php echo $cnt; ?>" class="<?php echo $portal_mode ? 'wmtFullInput' : 'wmtFullInput'; ?>" type="text" tabindex="-1" value="<?php echo htmlspecialchars($prev['img_nt'], ENT_QUOTES, '', FALSE); ?>" />
		<?php } ?>
		</td>
		<td class="<?php echo $portal_mode ? '' : 'wmtBorder1B wmtBorder1L'; ?> btnActContainer">
			
		<?php if(!$portal_mode || ($portal_mode && ($prev['classification'] == 9))) { ?>
			<div style="float: left; padding-left: 2px;"><a class="css_button_small" tabindex="-1" onClick="SubmitLinkBuilder('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $cnt; ?>','<?php echo $id; ?>','updateimg','img_id_','Image');" href="javascript:;"><span>Update</span></a></div>
		<?php } ?>
		<?php if($unlink_allow) { ?>
			<div style="float: left; padding-left: 2px;"><a class="css_button_small" tabindex="-1" onClick="SubmitLinkBuilder('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $cnt; ?>','<?php echo $id; ?>','unlinkimg','img_id_','Image');" href="javascript:;"><span>Un-Link</span></a></div>
		<?php } ?>
		<?php if($delete_allow || ($portal_mode && $prev['classification'] == 9)) { ?>
			<div style="float: left; padding-left: 2px;"><a class="css_button_small" tabindex="-1" onClick="SubmitLinkBuilder('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $cnt; ?>','<?php echo $id; ?>','delimg','img_id_','Image','<?php echo $prev['img_num_links']; ?>');" href="javascript:;"><span>Delete</span></a></div>
		<?php } ?>
		<!-- br>
		<div style="float: left; padding-left: 6px;"><a class="css_button" href="javascript:;" onclick="wmtOpen('../../../custom/document_popup.php?pid=<?php // echo $pid; ?>&task=link&item_id=<?php // echo $prev['id']; ?>&prefix=img_&cnt=<?php // echo $cnt; ?>', '_blank', 800, 600);"><span>Link A Document</span></a></div -->
		&nbsp;</td>
	</tr>
<?php
	if($prev['classification'] == 9) $portal_data_exists = true;
	$bg = ($bg == 'wmtAltLight' ? 'wmtLight' : 'wmtAltLight');
	$cnt++;
}
$cnt--;
?>

	<tr class="<?php echo $portal_mode ? $bg . ' wmtBorder1B' : 'wmtBorder1B'; ?>">
		<td class="<?php echo $portal_mode ? 'wmtDateCell' : ''; ?>"><input name="img_dt" id="img_dt" class="<?php echo $portal_mode ? 'wmtFullInput' : 'wmtFullInput'; ?>" type="text" value="<?php echo htmlspecialchars($dt['img_dt'], ENT_QUOTES, '', FALSE); ?>" /></td>
		<td class="<?php echo (($portal_mode)?'wmtBorder1B':'BodyBorderLB'); ?>"><select name="img_type" id="img_type" class="<?php echo $portal_mode ? 'wmtFullInput' : 'wmtFullInput'; ?>">
			<?php ListSelAlpha($dt['img_type'],'Image_Types'); ?>
		</select></td>
		<td class="<?php echo $portal_mode ? 'wmtBorder1B' : 'wmtBorder1L'; ?>"><input name="img_nt" id="img_nt" class="<?php echo $portal_mode ? 'wmtFullInput' : 'wmtFullInput'; ?>" type="text" value="<?php echo htmlspecialchars($dt['img_nt'], ENT_QUOTES, '', FALSE); ?>" /></td>
		<td class="<?php echo $portal_mode ? 'wmtBorder1B' : 'wmtBorder1L'; ?>">&nbsp;</td>
	</tr>
	<tr class="wmtColorBar <?php echo $portal_mode ? 'wmtBorder1B': 'wmtBorder1B'; ?>">
		<td colspan="2"><div style="float: left;"><a class="css_button" onClick="SubmitLinkBuilder('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','','<?php echo $id; ?>','img');" href="javascript:;"><span>Add Another</span></a></div>
		<?php if(!$portal_mode) { ?>
		<div style="float: left; padding-left: 8px;"><a class="css_button" onClick="add_item('img_type','Image_Types');" href="javascript:;"><span>Add A Type</span></a><input name="tmp_img_cnt" id="tmp_img_cnt" type="hidden" tabindex="-1" value="<?php echo $cnt; ?>" /></div>
		<div style="float: left; padding-left: 16px;"><a class="css_button" href="javascript:;" onclick="wmtOpen('../../../custom/document_popup.php?pid=<?php echo $pid; ?>', '_blank', 800, 600);"><span>View Documents</span></a></div>
		<?php } ?>
		&nbsp;</td>
		<td>
		<?php if(!$portal_mode && $portal_data_exists) { ?>
		<div style="float: right; padding-right: 12px;"><b><i>** Highlighted items have been entered through the portal, 'Update' to Verify/Accept</i></b></div>
		<?php } ?>
		&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
</table>

<?php include($GLOBALS['srcdir'].'/wmt-v2/list_note_section.inc.php'); ?>
