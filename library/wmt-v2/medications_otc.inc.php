<?php
if(!isset($frmdir)) $frmdir = '';
if(!isset($max_otc)) $max_otc = checkSettingMode('wmt::max_otc_meds','',$frmdir);
if(!isset($dt['fyi_otc_nt'])) $dt['fyi_otc_nt'] = '';
if(isset($fyi->fyi_otc_nt)) $dt['fyi_otc_nt'] = $fyi->fyi_otc_nt;
if(!isset($pat_entries['fyi_otc_nt'])) 
		$pat_entries['fyi_otc_nt'] = $portal_data_layout;
if(!isset($otc_add_allowed)) 
		$otc_add_allowed = checkSettingMode('wmt::otc_add_allowed','',$frmdir);
if(!isset($dt['tmp_otc_window_mode'])) $dt['tmp_otc_window_mode'] = 'all';
if(!isset($portal_mode)) $portal_mode = false;
if(!isset($dt['fyi_otc_nt'])) $dt['fyi_otc_nt'] = '';
if(!isset($dt['otc_begdate'])) $dt['otc_begdate'] = '';
if(!isset($dt['otc_title'])) $dt['otc_title'] = '';
if(!isset($dt['otc_enddate'])) $dt['otc_enddate'] = '';
if(!isset($dt['otc_extrainfo'])) $dt['otc_extrainfo'] = '';
if(!isset($dt['otc_injury_type'])) $dt['otc_injury_type'] = '';
if(!isset($dt['injury_type'])) $dt['injury_type'] = '';
if(!isset($dt['otc_referredby'])) $dt['otc_referredby'] = '';
if(!isset($dt['otc_comments'])) $dt['otc_comments'] = '';
if(!isset($otc)) $otc = array();
$delete_allow = \OpenEMR\Common\Acl\AclMain::aclCheckCore('admin','super');
$unlink_allow = (\OpenEMR\Common\Acl\AclMain::aclCheckCore('patients','med') || $delete_allow);
if($frmdir == 'dashboard') $unlink_allow = false;
?>
<table width='100%' border='0' cellspacing='0' cellpadding='3'>
	<tr>
		<td class="wmtLabel wmtC wmtBorder1B wmtDateCell"><?php xl('Start Date','e'); ?></td>
		<td class="wmtLabel wmtC wmtBorder1L wmtBorder1B wmtDateCell"><?php xl('End Date','e'); ?></td>
		<td class="wmtLabel wmtC wmtBorder1L wmtBorder1B"><?php xl('Type','e'); ?></td>
		<td class="wmtLabel wmtC wmtBorder1L wmtBorder1B"><?php xl('Prescribing Dr','e'); ?></td>
		<td class="wmtLabel wmtC wmtBorder1L wmtBorder1B"><?php xl('Medication','e'); ?></td>
		<td class="wmtLabel wmtC wmtBorder1L wmtBorder1B"><?php xl('When','e'); ?></td>
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
$when_href = $GLOBALS['webroot'].
	  '/custom/otc_when_popup.php?list=Portal_OTC_Times';
$portal_data_exists = false;
foreach($otc as $prev) {
	$cnt++;
	if($dt['tmp_otc_window_mode'] != 'all' && 
									$max_otc && ($cnt > $max_otc)) break;
	$when = array();
	$when_display = '';
	if($prev['extrainfo']) $when = explode('^|', $prev['extrainfo']);
	foreach($when as $w) {
		if($when_display) $when_display .= ', ';
		$when_display .= ucfirst(str_replace('_', ' ', $w));
	}
?>
	<tr>
		<td class='wmtBorder1B'><input name='otc_id_<?php echo $cnt; ?>' id='otc_id_<?php echo $cnt; ?>' type="hidden" tabindex='-1' readonly='readonly' value="<?php echo $prev['id']; ?>" /><input name="otc_begdate_<?php echo $cnt; ?>" id="otc_begdate_<?php echo $cnt; ?>" type="text" class="wmtDateInput" value="<?php echo htmlspecialchars($prev['begdate'], ENT_QUOTES, '', FALSE); ?>" title="YYYY-MM-DD Format" />
		<input name='otc_num_links_<?php echo $cnt; ?>' id='otc_num_links_<?php echo $cnt; ?>' type='hidden' readonly='readonly' tabindex='-1' value="<?php echo $prev['num_links']; ?>" /></td>
		<td class='wmtBorder1L wmtBorder1B'><input name="otc_enddate_<?php echo $cnt; ?>" id="otc_enddate_<?php echo $cnt; ?>" type="text" class="wmtDateInput" value="<?php echo htmlspecialchars($prev['enddate'], ENT_QUOTES, '', FALSE); ?>" title="YYYY-MM-DD Format, A date here will move the entry to History" /></td>
		<td class='wmtBorder1L wmtBorder1B'><input name="otc_injury_type_<?php echo $cnt; ?>" id="otc_injury_type_otc_<?php echo $cnt; ?>" type="radio" class="wmtInput" value="OTC" <?php echo $prev['injury_type'] == 'OTC' ? 'checked="checked"' : ''; ?> /><label for="otc_injury_type_otc_<?php echo $cnt; ?>">OTC</label>&nbsp;&nbsp;
			<input name="otc_injury_type_<?php echo $cnt; ?>" id="otc_injury_type_rx_<?php echo $cnt; ?>" type="radio" class="wmtInput" value="Rx" <?php echo $prev['injury_type'] == 'Rx' ? 'checked="checked"' : ''; ?> /><label for="otc_injury_type_rx_<?php echo $cnt; ?>" >Rx</label></td>
		<td class='wmtBorder1L wmtBorder1B'><input name="otc_referredby_<?php echo $cnt; ?>" id="otc_referredby_<?php echo $cnt; ?>" type="text" class="wmtFullInput" value="<?php echo htmlspecialchars($prev['referredby'], ENT_QUOTES, '', FALSE); ?>" /></td>
		<td class='wmtBorder1L wmtBorder1B'><input name="otc_title_<?php echo $cnt; ?>" id="otc_title_<?php echo $cnt; ?>" type="text" class="wmtFullInput" value="<?php echo htmlspecialchars($prev['title'], ENT_QUOTES, '', FALSE); ?>" /></td>
		<td class='wmtBorder1L wmtBorder1B' onclick="build_when_link('<?php echo $when_href; ?>','<?php echo $cnt; ?>');" ><input name="otc_when_<?php echo $cnt; ?>" id="otc_when_<?php echo $cnt; ?>" type="text" readonly="readonly" class="wmtFullInput wmtClick" value="<?php echo htmlspecialchars($when_display, ENT_QUOTES, '', FALSE); ?>" title="<?php echo htmlspecialchars($when_display, ENT_QUOTES, '', FALSE); ?>" />
			<input name="otc_extrainfo_<?php echo $cnt; ?>" id="otc_extrainfo_<?php echo $cnt; ?>" type="hidden" value="<?php echo $prev['extrainfo']; ?>" tabindex="-1" /></td>
		<td class='wmtBorder1L wmtBorder1B'><input name="otc_comments_<?php echo $cnt; ?>" id ="otc_comments_<?php echo $cnt; ?>" type="text" class="wmtFullInput" value="<?php echo htmlspecialchars($prev['comments'], ENT_QUOTES, '', FALSE); ?>" title="<?php echo htmlspecialchars($prev['comments'], ENT_QUOTES, '', FALSE) ?>" /></td>
		<td class='wmtBorder1L wmtBorder1B'>
<?php if(!$portal_mode || ($prev['classification'] == 9)) { ?>
			<div style="float: left; padding-left: 2px;"><a href="javascript:;" class='css_button_small' tabindex='-1' onClick="return SubmitLinkBuilder('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $cnt; ?>','<?php echo $id; ?>','updateotc','otc_id_','Patient Medication');" ><span><?php xl('Update','e'); ?></span></a></div>
<?php } ?>
<?php if($unlink_allow) { ?>
			<div style="float: left; padding-left: 2px;"><a href="javascript:;" class='css_button_small' tabindex='-1' onClick="return SubmitLinkBuilder('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $cnt; ?>','<?php echo $id; ?>','unlinkotc','otc_id_','Patient Medication');" ><span><?php xl('Un-Link','e'); ?></span></a></div>
<?php } ?>
<?php if($delete_allow || ($portal_mode && $prev['classification'] == 9)) { ?>
			<div style="float: left; padding-left: 2px;"><a href="javascript:;" class='css_button_small' tabindex='-1' onClick="return SubmitLinkBuilder('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $cnt; ?>','<?php echo $id; ?>','delotc','otc_id_','Patient Medication','<?php echo $prev['num_links']; ?>');" ><span><?php xl('Delete','e'); ?></span></a></div>
<?php } ?>
		</td>
	</tr>
	<?php
}
// End of array 'meds' has information
if(!$cnt) {
?>
	<tr>
		<td class='wmtBorder1B'>&nbsp;</td>
		<td class='wmtBorder1L wmtBorder1B'>&nbsp;</td>
		<td class='wmtBorder1L wmtBorder1B'>&nbsp;</td>
		<td class='wmtLabel wmtBorder1L wmtBorder1B'>&nbsp;<?php xl('None on File','e'); ?></td>
		<td class='wmtBorder1L wmtBorder1B'>&nbsp;</td>
		<td class='wmtBorder1L wmtBorder1B'>&nbsp;</td>
		<td class='wmtBorder1L wmtBorder1B'>&nbsp;</td>
		<td class='wmtBorder1L wmtBorder1B'>&nbsp;</td>
		</tr>
<?php 
}
if($otc_add_allowed) {
?>
	<tr>
		<td class='wmtBorder1B'><input name='otc_begdate' id='otc_begdate' class='wmtDateInput' type='text' title='YYYY-MM-DD' value="<?php echo htmlspecialchars($dt{'otc_begdate'}, ENT_QUOTES, '', FALSE); ?>" /></td>
		<td class='wmtBorder1L wmtBorder1B'><input name='otc_enddate' id='otc_enddate' class='wmtDateInput' type='text' title='YYYY-MM-DD Format, Entering a date here will move this item to history' value="<?php echo htmlspecialchars($dt{'otc_enddate'}, ENT_QUOTES, '', FALSE); ?>" /></td>
		<td class="wmtBorder1L wmtBorder1B"><input name="otc_type" id="type_otc" type="radio" value="OTC" <?php echo ($dt['injury_type'] == 'OTC') ? 'checked="checked"' : ''; ?> title="An 'over the counter' medication purchased with no prescription" /><label for="type_otc">OTC</label>&nbsp;&nbsp;
			<input name="otc_type" id="type_rx" type="radio" value="Rx" <?php echo ($dt['injury_type'] == 'Rx') ? 'checked="checked"' : ''; ?> title="Pescription written by another doctor" /><label for="type_rx">Rx</label></td>
		<td class='wmtBorder1L wmtBorder1B'><input name='otc_referredby' id='otc_referredby' class='wmtFullInput' type='text' value="<?php echo htmlspecialchars($dt{'otc_referredby'}, ENT_QUOTES, '', FALSE); ?>" /></td>
		<td class='wmtBorder1L wmtBorder1B'><input name='otc_title' id='otc_title' class='wmtFullInput' type='text' value="<?php echo htmlspecialchars($dt{'otc_title'}, ENT_QUOTES, '', FALSE); ?>" /></td>
		<td class='wmtBorder1L wmtBorder1B' onclick="build_when_link('<?php echo $when_href; ?>','');" ><input name="otc_when" id="otc_when" class="wmtFullInput wmtClick" readonly="readonly" onfocus="build_when_link('<?php echo $when_href; ?>','');" title="Click to Select Times Taken" value="" />
			<input name="otc_extrainfo" id="otc_extrainfo" type="hidden" value="" tabindex="-1" /></td>
		<td class='wmtBorder1L wmtBorder1B'><input name='otc_comments' id='otc_comments' class='wmtFullInput' type='text' value="<?php echo htmlspecialchars($dt{'otc_comments'}, ENT_QUOTES, '', FALSE); ?>" /></td>
		<td class='wmtBorder1L wmtBorder1B'>&nbsp;</td>
	</tr>
	<?php 
	} 
	if(($dt['tmp_otc_window_mode'] == 'limit') && 
															$max_otc && (count($meds) > $max_otc)) {
	?>
	<tr>
		<td colspan="6" class="wmtBody2 wmtBorder1B">
			<i>&nbsp;&nbsp;*&nbsp;This view is currently limited to the most recent&nbsp;<?php echo $max_otc;?>&nbsp;prescriptions</i></td>
	</tr>
	<?php 
	}
	// FIX! - The limited view options need to be built here
	?>
	<tr>
	  <td class="wmtCollapseBar wmtBorder1B" colspan="8">
			<input type="hidden" name="tmp_otc_cnt" id="tmp_otc_cnt" tabindex="-1" value="<?php echo ($cnt); ?>" />
		<?php if($otc_add_allowed) { ?>
			<div style="float: left; padding-left: 10px;">
			<a href="javascript:;" class='css_button_small' tabindex='-1' onClick="return SubmitLinkBuilder('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','','<?php echo $id; ?>','addotc');" href='javascript:;'><span><?php xl('Add Another','e'); ?></span></a></div>
		<?php } ?>	
		<!-- FIX - VIEW LIMIT/SHOW BUTTONS WOULD GO HERE -->
			<?php if($unlink_allow && count($meds)) { ?>
			<div style="float: right; padding-right: 10px;"><a href="javascript:;" class='css_button_small' tabindex='-1' onClick="return SubmitLinkBuilder('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','','<?php echo $id; ?>','unlinkallotc');" ><span><?php xl('Un-Link ALL','e'); ?></span></a></div>
			<?php } ?>
		&nbsp;</td>
	</tr>

	<tr>
		<td class="wmtLabel" colspan="2"><?php xl('Other Notes','e'); ?>:</td>
	</tr>
	<tr>
		<td colspan="8"><textarea name='fyi_otc_nt' id='fyi_otc_nt' rows='4' class='wmtFullInput'><?php echo htmlspecialchars($dt['fyi_otc_nt'], ENT_QUOTES, '', FALSE); ?></textarea></td>
	</tr>
</table>

<script type="text/javascript">

function build_when_link(base, itemID) {
	var lbl = 'otc_extrainfo';
	if(itemID) lbl += '_' + itemID;
	base += '&extra=' + document.getElementById(lbl).value;
	if(itemID) base += '&item=' + itemID;
	wmtOpen(base,'_blank',400,400);
}

function set_when(itemID, extra, disp) {
	var extra_lbl = 'otc_extrainfo';
	var disp_lbl = 'otc_when';
	var next_fld = 'otc_comments';
	if(itemID) {
		extra_lbl += '_' + itemID;
		disp_lbl += '_' + itemID;
		next_fld += '_' + itemID;
	}
	document.getElementById(extra_lbl).value = extra;
	document.getElementById(disp_lbl).value = disp;
	document.getElementById(disp_lbl).title = disp;
	document.getElementById(next_fld).focus();
}

</script>
