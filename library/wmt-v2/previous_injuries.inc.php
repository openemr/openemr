<?php
if(!isset($inj)) $inj = array();
if(!isset($dt['inj_begdate'])) $dt['inj_begdate'] = '';
if(!isset($dt['inj_title'])) $dt['inj_title'] = '';
if(!isset($dt['inj_hospitalized'])) $dt['inj_hospitalized'] = '';
if(!isset($dt['inj_comments'])) $dt['inj_comments'] = '';
if(!isset($unlink_allow)) $unlink_allow = false;
if($frmdir == 'dashboard') $unlink_allow = false;
if(!isset($portal_mode)) $portal_mode = false;
if(!isset($pat_entries_exist)) $pat_entries_exist = false;
if(!isset($field_prefix)) $field_prefix = false;
if(!isset($delete_allow)) $delete_allow = false;
if(!isset($pat_entries['fyi_inj_nt'])) 
			$pat_entries['fyi_inj_nt'] = $portal_data_layout;
?>
<table width='100%' border='0' cellspacing='0' cellpadding='4'>
	<tr>
		<td class="<?php echo (($portal_mode)?'bkkLabel bkkC bkkBorder1B':'wmtLabel wmtC wmtBorder1B'); ?>" style="width: 95px">Date</td>
		<td class="<?php echo (($portal_mode)?'bkkLabel bkkC bkkBorder1B':'wmtBorder1B wmtBorder1L wmtLabel wmtC'); ?>">Type of Injury</td>
		<td class="<?php echo (($portal_mode)?'bkkLabel bkkC bkkBorder1B':'wmtBorder1B wmtBorder1L wmtLabel wmtC'); ?>" style="width: 100px;" >Hospitalized?</td>
		<td class="<?php echo (($portal_mode)?'bkkLabel bkkC bkkBorder1B':'wmtBorder1B wmtBorder1L wmtLabel wmtC'); ?>">Notes</td>
<?php if($portal_mode) { ?>
		<td class='bkkBorder1B' style='width: 115px'>&nbsp;</td>
<?php } else if($delete_allow && $unlink_allow) { ?>
		<td class='wmtBorder1L wmtBorder1B' style='width: 175px'>&nbsp;</td>
<?php } else if($delete_allow || $unlink_allow) { ?>
		<td class='wmtBorder1L wmtBorder1B' style='width: 115px'>&nbsp;</td>
<?php } else { ?>
		<td class='wmtBorder1L wmtBorder1B' style='width: 65px'>&nbsp;</td>
<?php } ?>
	</tr>
<?php
$bg = 'bkkLight';
$cnt=1;
$portal_data_exists = false;
foreach($inj as $prev) {
?>
	<tr class="<?php echo (($portal_mode)? $bg : ''); ?><?php echo ((!$portal_mode && ($prev['classification'] == 9))?'wmtHighlight':''); ?>" >
		<td class="<?php echo (($portal_mode)?'bkkDateCell bkkBody':'wmtDateCell wmtBorder1B'); ?>" ><input name="inj_id_<?php echo $cnt; ?>" id="inj_id_<?php echo $cnt; ?>" type='hidden' readonly='readonly' value="<?php echo $prev['id']; ?>" /><input name="inj_num_links_<?php echo $cnt; ?>" id="inj_num_links_<?php echo $cnt; ?>" type="hidden" tabindex="-1" value="<?php echo $prev['num_links']; ?>" />&nbsp;
		<?php if($portal_mode && ($prev['classification'] != 9)) { ?>
			<?php echo $prev['begdate']; ?>
		<?php } else { ?>
			<input name="inj_begdate_<?php echo $cnt; ?>" id="inj_begdate_<?php echo $cnt; ?>" class="<?php echo (($portal_mode) ? 'bkkInput':'wmtInput'); ?>" type="text" style="width: 85px;" tabindex='-1' value="<?php echo $prev['begdate']; ?>" />
		<?php } ?>
		</td>

		<td class="<?php echo (($portal_mode)?'bkkBody':'wmtBorder1L wmtBorder1B'); ?>" >
		<?php if($portal_mode && ($prev['classification'] != 9)) { ?>
			&nbsp;<?php echo $prev['title']; ?>
		<?php } else { ?>
			<input name="inj_title_<?php echo $cnt; ?>" id="inj_title_<?php echo $cnt; ?>" class="<?php echo (($portal_mode)?'bkkFullInput':'wmtFullInput'); ?><?php echo ((!$portal_mode && $prev['classification'] == '9')?' wmtHighlight':''); ?>" type='text' tabindex='-1' value="<?php echo $prev['title']; ?>" />
		<?php } ?>
		</td>

		<td class="<?php echo (($portal_mode)?'bkkBody':'wmtBorder1L wmtBorder1B'); ?>" >
		<?php if($portal_mode && ($prev['classification'] != 9)) { ?>
			&nbsp;<?php echo ListLook($prev['extrainfo'],'YesNo'); ?>
		<?php } else { ?>
			<select name="inj_hospitalized_<?php echo $cnt; ?>" id="inj_hospitalized_<?php echo $cnt; ?>" class="<?php echo (($portal_mode)?'bkkFullInput':'wmtFullInput'); ?><?php echo ((!$portal_mode && $prev['classification'] == '9')?' wmtHighlight':''); ?>" tabindex='-1'>
			<?php echo ListSel($prev['extrainfo'],'YesNo'); ?></select>
		<?php } ?>
		</td>

		<td class="<?php echo (($portal_mode)?'bkkBody':'wmtBorder1L wmtBorder1B'); ?>" >
		<?php if($portal_mode && ($prev['classification'] != 9)) { ?>
			&nbsp;<?php echo $prev['comments']; ?>
		<?php } else { ?>
			<input name="inj_comments_<?php echo $cnt; ?>" id="inj_comments_<?php echo $cnt; ?>" class="<?php echo (($portal_mode)?'bkkFullInput':'wmtFullInput'); ?><?php echo ((!$portal_mode && $prev['classification'] == '9')?' wmtHighlight':''); ?>" type='text' tabindex='-1' value="<?php echo $prev['comments']; ?>" />
		<?php } ?>
		</td>

		<td class="<?php echo (($portal_mode)?'bkkBody':'wmtBorder1L wmtBorder1B'); ?>" style="padding-top: 3px; padding-bottom: 3px;">
		<?php if(!$portal_mode || ($prev['classification'] == 9)) { ?>
		<div class="wmtListButton"><a class="css_button_small" tabindex="-1" onClick="return SubmitLinkBuilder('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $cnt; ?>','<?php echo $id; ?>','updateinj','inj_id_','Injury');" href="javascript:;"><span>Update</span></a></div>
		<?php } ?>

<?php
if($unlink_allow) {
?>
	<div class="wmtListButton"><a class="css_button_small" tabindex="-1" onClick="return SubmitLinkBuilder('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $cnt; ?>','<?php echo $id; ?>','unlinkinj','inj_id_','Injury');" href="javascript:;"><span>Un-Link</span></a></div>
<?php 
}
if($delete_allow || ($portal_mode && ($prev['classification'] == 9))) {
?>
	<div class="wmtListButton"><a class="css_button_small" tabindex="-1" onClick="return SubmitLinkBuilder('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $cnt; ?>','<?php echo $id; ?>','delinj','inj_id_','Injury','<?php echo $prev['num_links']; ?>');" href="javascript:;"><span>Delete</span></a></div>
<?php
}
?>
		</td>
	</tr>
<?php
	if($prev['classification'] == 9) { $portal_data_exists = true; }
	$bg = ($bg == 'bkkAltLight' ? 'bkkLight' : 'bkkAltLight');	
	$cnt++;
}
?>
	<tr class="<?php echo ($portal_mode) ? $bg : ''; ?>">
		<td class="<?php echo (($portal_mode)?'bkkDateCell':'wmtDateCell'); ?> wmtBorder1B" ><input name='inj_begdate' id='inj_begdate' class="<?php echo (($portal_mode)?'bkkInput':'wmtInput'); ?>" type="text" style="width: 90px;" title="YYYY-MM-DD" value="<?php echo $dt{'inj_begdate'}; ?>" /></td>
		<td class="<?php echo (($portal_mode)?'':'wmtBorder1L'); ?> wmtBorder1B" ><input name='inj_title' id='inj_title' class="<?php echo (($portal_mode)?'bkkFullInput':'wmtFullInput'); ?>" type='text' value="<?php echo $dt{'inj_title'}; ?>" /></td>
		<td class="<?php echo (($portal_mode)?'':'wmtBorder1L'); ?> wmtBorder1B" ><select name="inj_hospitalized" id="inj_hospitalized" class="<?php echo (($portal_mode)?'bkkFullInput':'wmtFullInput'); ?>">
			<?php echo ListSel($dt['inj_hospitalized'],'YesNo'); ?></select></td>
		<td class="<?php echo (($portal_mode)?'':'wmtBorder1L'); ?> wmtBorder1B" ><input name='inj_comments' id='inj_comments' class="<?php echo (($portal_mode)?'bkkFullInput':'wmtFullInput'); ?>" type='text' value="<?php echo $dt{'inj_comments'}; ?>" /></td>
		<td class="<?php echo (($portal_mode)?'':'wmtBorder1L'); ?> wmtBorder1B" >&nbsp;</td>
	</tr>

	<tr>
		<td class="wmtCollapseBar <?php echo ((checkSettingMode('wmt::fyi_inj_nt','',$frmdir) || $portal_mode) ? 'wmtBorder1B' : ''); ?>" colspan="2"><div style="padding-left: 8px;"><a class="css_button" onClick="return SubmitLinkBuilder('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','','<?php echo $id; ?>','inj');" href='javascript:;'><span>Add Another</span></a></div></td>
		<td class="wmtCollapseBar <?php echo ((checkSettingMode('wmt::fyi_inj_nt','',$frmdir) || $portal_mode) ? 'wmtBorder1B' : ''); ?>" colspan="5"><div style="float: right; padding-right: 12px;"><i><b><?php echo ((!$portal_mode && $portal_data_exists) ? "** Highlighted items have been entered through the portal, 'Update' to Verify/Accept" : '&nbsp;'); ?></b></i></div></td>
	</tr>

<?php if(checkSettingMode('wmt::fyi_inj_nt','',$frmdir) || $portal_mode) { ?>
	<tr>
		<td class="<?php echo $portal_mode ? 'bkkLabel' : 'wmtLabel'; ?>">Notes:</td>
	</tr>
	<tr>
		<td colspan="6"><textarea name="fyi_inj_nt" id="fyi_inj_nt" rows="4" class="<?php echo $portal_mode ? 'bkkFullInput' : 'wmtFullInput'; ?>"><?php echo htmlspecialchars($fyi->fyi_inj_nt, ENT_QUOTES, '', FALSE); ?></textarea></td>
	</tr>
<?php
	if($pat_entries_exist && !$portal_mode) {
		if($pat_entries['fyi_inj_nt']['content'] && (strpos($dt{$field_prefix.'fyi_inj_nt'},$pat_entries['fyi_inj_nt']['content']) === false)) {
?>
		<div class="wmtLabel wmtPortalData wmtL" style="margin: 6px;">Notes input by the patient via the portal:</div>
		<div class="wmtPortalData wmtBorderHighlight wmtBody wmtL" style="margin: 6px;" id="tmp_<?php echo $field_prefix; ?>fyi_inj_nt" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['fyi_inj_nt']['content'], ENT_QUOTES, '', FALSE); ?></div>
<?php
		}
	}
}
?>
</table>

