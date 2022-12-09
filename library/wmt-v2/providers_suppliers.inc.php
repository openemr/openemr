<table width='100%' border='0' cellspacing='0' cellpadding='0'>
	<tr>
		<td class='wmtLabel wmtC wmtBorder1B'>Name</td>
		<td class='wmtLabel wmtC wmtBorder1L wmtBorder1B'>Specialty / Type</td>
		<td class='wmtLabel wmtC wmtBorder1L wmtBorder1B'>Reason</td>
<?php 
if(\OpenEMR\Common\Acl\AclMain::aclCheckCore('admin','super') && $unlink_allow) {
	echo "		<td class='wmtBorder1L wmtBorder1B' style='width: 175px'>&nbsp;</td>\n";
} else if($unlink_allow) {
echo "		<td class='wmtBorder1L wmtBorder1B' style='width: 115px'>&nbsp;</td>\n";
} else if(\OpenEMR\Common\Acl\AclMain::aclCheckCore('admin','super')) {
echo "		<td class='wmtBorder1L wmtBorder1B' style='width: 115px'>&nbsp;</td>\n";
} else {
echo "		<td class='wmtBorder1L wmtBorder1B' style='width: 65px'>&nbsp;</td>\n";
}
?>
	</tr>
<?php
$cnt=1;
if(isset($supp)) {
	foreach($supp as $prev) {
?>
	<tr>
		<td class="wmtBorder1B"><input name="sp_id_<?php echo $cnt; ?>" id="sp_id_<?php echo $cnt; ?>" type='hidden' readonly='readonly' value="<?php echo $prev['id']; ?>" /><input name="sp_num_links_<?php echo $cnt; ?>" id="sp_num_links_<?php echo $cnt; ?>" type="hidden" tabindex="-1" value="<?php echo $prev['num_links']; ?>" /><input name="sp_referredby_<?php echo $cnt; ?>" id="sp_referredby_<?php echo $cnt; ?>" class='wmtFullInput' type='text' tabindex='-1' value="<?php echo $prev['referredby']; ?>" /></td>
		<td class='wmtBorder1L wmtBorder1B'><input name="sp_title_<?php echo $cnt; ?>" id="sp_title_<?php echo $cnt; ?>" class='wmtFullInput' type='text' tabindex='-1' value="<?php echo $prev['title']; ?>" /></td>
		<td class='wmtBorder1L wmtBorder1B'><input name="sp_comments_<?php echo $cnt; ?>" id="sp_comments_<?php echo $cnt; ?>" class='wmtFullInput' type='text' tabindex='-1' value="<?php echo $prev['comments']; ?>" /></td>
		<td class='wmtBorder1L wmtBorder1B' style="padding-top: 3px; padding-bottom: 3px;"><div class="wmtListButton"><a class='css_button_small' tabindex='-1' onClick="return SubmitLinkBuilder('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $cnt; ?>','<?php echo $id; ?>','updatesp','sp_id_','Supplier');" href='javascript:;'><span>Update</span></a></div>
<?php
if($unlink_allow) {
?>
		<div class="wmtListButton"><a class='css_button_small' tabindex='-1' onClick="return SubmitLinkBuilder('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $cnt; ?>','<?php echo $id; ?>','unlinksp','sp_id_','Supplier');" href='javascript:;'><span>Un-Link</span></a></div>
<?php 
}
if(\OpenEMR\Common\Acl\AclMain::aclCheckCore('admin','super')) {
?>
		<div class="wmtListButton"><a class='css_button_small' tabindex='-1' onClick="return SubmitLinkBuilder('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $cnt; ?>','<?php echo $id; ?>','delsp','sp_id_','Supplier','<?php echo $prev['num_links']; ?>');" href='javascript:;'><span>Delete</span></a></div>
<?php
}
?>
		</td>
	</tr>
<?php
		$cnt++;
	}
}
?>
	<tr>
		<td class='wmtBorder1B'><input name='sp_name' id='sp_name' class='wmtFullInput' type='text' value="<?php echo $dt{'sp_name'}; ?>" /></td>
		<td class='wmtBorder1L wmtBorder1B'><input name='sp_type' id='sp_type' class='wmtFullInput' type='text' value="<?php echo $dt{'sp_type'}; ?>" /></td>
		<td class='wmtBorder1L wmtBorder1B'><input name='sp_why' id='sp_why' class='wmtFullInput' type='text' value="<?php echo $dt{'sp_why'}; ?>" /></td>
		<td class='wmtBorder1L wmtBorder1B'>&nbsp;</td>
	</tr>
	<tr>
		<td class='wmtCollapseBar' colspan='7'><a class='css_button' onClick="return SubmitLinkBuilder('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','','<?php echo $id; ?>','sp');" href='javascript:;'><span>Add Another</span></a></td>
	</tr>
</table>
