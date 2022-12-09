<?php
include($GLOBALS['srcdir'].'/wmt-v2/amc_auto_add_med.inc.php');
if(!isset($meds)) $meds = array();
if(!isset($max_med)) $max_med = false;
if(!isset($dt['tmp_med_window_mode'])) $dt['tmp_med_window_mode'] = 'all';
if(!isset($dt['tmp_med_link_mode'])) $dt['tmp_med_link_mode'] = 'link';
$unlink_allow = (\OpenEMR\Common\Acl\AclMain::aclCheckCore('patients','med') || \OpenEMR\Common\Acl\AclMain::aclCheckCore('admin','super'));
if(!isset($portal_mode)) $portal_mode = false;
if(!isset($pat_entries_exist)) $pat_entries_exist = false;
if(!isset($dt{'med_begdate'})) $dt{'med_begdate'} = '';
if(!isset($dt{'med_enddate'})) $dt{'med_enddate'} = '';
if(!isset($dt{'med_title'})) $dt{'med_title'} = '';
if(!isset($dt{'med_comments'})) $dt{'med_comments'} = '';
if(!isset($dt{'med_stat'})) $dt{'med_stat'} = '';
if(!isset($frmdir)) $frmdir = '';
if(!isset($field_prefix)) $field_prefix = '';
$note_field = 'fyi_med_nt';
if(!isset($dt[$note_field])) $dt[$note_field] = '';
if(!isset($fyi->$note_field)) $fyi->$note_field = '';
if(!isset($pat_entries[$note_field])) 
		$pat_entries[$note_field] = $portal_data_layout;

$use_border = false;
?>
<table width="100%" border="0" cellspacing="0" cellpadding="4" style="table-layout: fixed; margin: 0px;">
	<tr>
		<td class="wmtLabel wmtC wmtBorder1B" style='width: 75px'><?php echo xl('Start Date'); ?></td>
		<td class="wmtLabel wmtC <?php echo(($portal_mode)?'':'wmtBorder1L'); ?> wmtBorder1B"><?php echo xl('Medication'); ?></td>
		<!-- Quantity is not sent back by the eRx system
		<td class="wmtLabel wmtC <?php // echo (($portal_mode)?'':'wmtBorder1L'); ?> wmtBorder1B" style='width: 65px;'><?php // xl('Quantity','e'); ?></td>
		-->
		<td class="wmtLabel wmtC <?php echo (($portal_mode)?'':'wmtBorder1L'); ?> wmtBorder1B" style='width: 65px;'><?php echo xl('Dosage'); ?></td>
		<td class="wmtLabel wmtC <?php echo (($portal_mode)?'':'wmtBorder1L'); ?> wmtBorder1B"><?php echo xl('Sig'); ?></td>
		<td class="wmtLabel wmtC <?php echo (($portal_mode)?'':'wmtBorder1L'); ?> wmtBorder1B"><?php echo xl('Comments'); ?></td>
		<?php if(!$portal_mode) { ?>
			<?php if($unlink_allow) { ?>
			<td class="wmtLabel wmtC <?php echo (($portal_mode)?'':'wmtBorder1L'); ?> wmtBorder1B" style='width: 115px'>&nbsp;</td>
			<?php } else { ?>
			<td class="wmtLabel wmtC <?php echo (($portal_mode)?'':'wmtBorder1L'); ?> wmtBorder1B" style='width: 65px'>&nbsp;</td>
			<?php } ?>
		<?php } ?>
	</tr>

<?php
// echo "Count of Meds before window: ",count($meds),"<br>\n";
// echo "Window mode: ",$dt['tmp_med_window_mode'],"<br>\n";
$bg = '';
$cnt=1;
if(count($meds) > 0) {
	foreach($meds as $prev) {
		if(!isset($prev['list_id'])) $prev['list_id'] = '';
		if(($dt['tmp_med_window_mode'] != 'all') && 
														$max_med && ($cnt > $max_med)) break;
		if(($dt['tmp_med_window_mode'] != 'all') && !$prev['list_id']) continue;
		$sig1 = trim(ListLook($prev['route'],'drug_route'));
		if(substr($sig1,0,3) == "Add") $sig1 = '';
		if($sig1) $sig1 = ' ' . $sig1;
		$form = trim(ListLook($prev['form'],'drug_form'));
		if(substr($form,0,3) == "Add") $form = '';
		if($form) $form = ' ' . $form;
		$sig2 = trim(ListLook($prev['interval'],'drug_interval'));
		if(substr($sig2,0,3) == "Add") $sig2 = '';
		if($sig2) $sig2 = ' ' . $sig2;
		if(substr($prev['dosage'],0,3) == "Add") $prev['dosage'] = '';
		$sig1 = $prev['dosage']. $form . $sig1 . $sig2;
		$size = trim($prev['size']);
		$unit = trim(ListLook($prev['unit'],'drug_units'));
		$size .= $unit;
?>
	<tr class="<?php echo (($portal_mode)? $bg : ''); ?>" >
		<td class="wmtBody <?php echo (($portal_mode)?'':'wmtBorder1B'); ?>"><input name='med_id_<?php echo $cnt; ?>' id='med_id_<?php echo $cnt; ?>' type='hidden' readonly='readonly' tabindex='-1' value="<?php echo $prev['id']; ?>" /><?php echo htmlspecialchars($prev['date_added'], ENT_QUOTES); ?>&nbsp;
		<input name='med_list_id_<?php echo $cnt; ?>' id='med_list_id_<?php echo $cnt; ?>' type='hidden' readonly='readonly' tabindex='-1' value="<?php echo $prev['list_id']; ?>" /></td>
		<td class="wmtBody <?php echo (($portal_mode)?'':'wmtBorder1L wmtBorder1B'); ?>"><?php echo htmlspecialchars($prev['drug'], ENT_QUOTES); ?>&nbsp;</td>
		<!-- Quantity is currently not available
		<td class="wmtBody <?php // echo (($portal_mode)?'':'wmtBorder1L wmtBorder1B'); ?>"><?php // echo $prev['quantity']; ?>&nbsp;</td>
		-->
		<td class="wmtBody <?php echo (($portal_mode)?'':'wmtBorder1L wmtBorder1B'); ?>"><?php echo htmlspecialchars($size, ENT_QUOTES); ?>&nbsp;</td>
		<td class="wmtBody <?php echo (($portal_mode)?'':'wmtBorder1L wmtBorder1B'); ?>"><?php echo htmlspecialchars($sig1, ENT_QUOTES); ?>&nbsp;</td>
		<td class="wmtBody <?php echo (($portal_mode)?'':'wmtBorder1L wmtBorder1B'); ?>">
		<?php if($portal_mode) { 
		echo '&nbsp;'.htmlspecialchars($prev['note'], ENT_QUOTES);
		} else { ?>
		<input name='med_comments_<?php echo $cnt; ?>' id='med_comments_<?php echo $cnt; ?>' type='text' class='wmtFullInput' tabindex='-1' value="<?php echo htmlspecialchars($prev['note'], ENT_QUOTES); ?>" />
		<?php } ?>
		</td>
		<?php if(!$portal_mode) { ?>
		<td class="wmtBody <?php echo (($portal_mode)?'':'wmtBorder1L wmtBorder1B'); ?>"><div class="wmtListButton"><a href="javascript:;" class='css_button_small' tabindex='-1' onClick="return SubmitLinkBuilder('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $cnt; ?>','<?php echo $id; ?>','updatemed','med_id_','Prescription');" ><span><?php xl('Update','e'); ?></span></a>
			<?php
			if($unlink_allow) { 
				if($prev['list_id']) {
			?>
			<div class="wmtListButton"><a href="javascript:;" class='css_button_small' tabindex='-1' onClick="return SubmitLinkBuilder('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $cnt; ?>','<?php echo $id; ?>','unlinkmed','med_id_','Prescription');" ><span><?php xl('Un-Link','e'); ?></span></a></div>
			<?php
			 	} else {
			?>
			<div class="wmtListButton"><a href="javascript:;" class='css_button_small' tabindex='-1' onClick="return SubmitLinkBuilder('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $cnt; ?>','<?php echo $id; ?>','linkmed','med_id_','Prescription');" ><span><?php xl('Link','e'); ?></span></a></div>
			<?php
				} 
			}
			?>
		</td>
		<?php
		}
		$bg = ($bg == 'bkkAltLight' ? 'bkkLight' : 'bkkAltLight');
		$bg = '';
		?>
	</tr>
<?php 
		$cnt++;
	}
	$cnt--;
	$use_border = true;
	if(($dt['tmp_med_window_mode'] == 'limit') && 
						($cnt == $max_med) &&	$max_med && (count($meds) > $max_med)) {
		$use_border = false;
		$cols = ($portal_mode ? 5 : 6);
?>
	<tr>
		<td colspan="<?php echo $cols; ?>" class="wmtBody2 wmtBorder1T"><i>&nbsp;&nbsp;*&nbsp;This view is currently limited to the most recent&nbsp;<?php echo $max_med;?>&nbsp;prescriptions</i></td>
	</tr>
<?php
	}
	if($unlink_allow || $max_med) {
	echo '<tr class="wmtColorHeader">';
		// If this is enabled we need buttons to toggle limit and views
		if($max_med) {
?>
		<td>&nbsp;<input type="hidden" name="tmp_med_cnt" id="tmp_med_cnt" tabindex="-1" value="<?php echo ($cnt); ?>" /></td>
<?php
			// Button to display all ONLY IF we exceeded the limit
			if(($dt['tmp_med_window_mode'] == 'limit') && $max_med && (count($meds) > $max_med)) {
?>
			<td>
			<div style="float: left; padding-left: 5px;"><a href="javascript:;" class='css_button_small' tabindex='-1' onClick="return ToggleWindowDisplayMode('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $id; ?>','medwindow','all');" ><span><?php xl('Show ALL Prescriptions','e'); ?></span></a></div>
			</td>
<?php
			// This button will limit IF All are displayed and we exceeded the limit
			} else if(($dt['tmp_med_window_mode'] == 'all') && 
																		(count($meds) > $max_med) && $max_med) {
?>
			<td>
			<div style="float: left; padding-left: 5px;"><a href="javascript:;" class='css_button_small' tabindex='-1' onClick="return ToggleWindowDisplayMode('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $id; ?>','medwindow','limit');" ><span><?php xl('Show '.$max_med.' Most Recent Linked','e'); ?></span></a></div>
			</td>
<?php
			} else {
			// Nothing is needed - don't confuse them!
?>
			<td>&nbsp;</td>
<?php
			}
		} else {
?>
			<td>&nbsp;</td>
<?php
		}
		$cols = ($portal_mode ? 1 : 2);
?>
			<td colspan="<?php echo $cols; ?>"> &nbsp;</td>
		
<?php
		if($unlink_allow) {
?>
			<td colspan="3">
			<div style="float: right; padding-right: 15px;"><a href="javascript:;" class='css_button_small' tabindex='-1' onClick="return SubmitLinkBuilder('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','','<?php echo $id; ?>','unlinkallmeds');" ><span><?php xl('Un-Link ALL Prescriptions','e'); ?></span></a></div>
			</td>
<?php
		} else {
			$cols = ($portal_mode ? 1 : 2);
?>
			<td colspan="<?php echo $cols; ?>">&nbsp;</td>
<?php
		}
	}
?>
	</tr>
<?php 
} else {
?>
	<tr class="<?php echo (($portal_mode)? $bg : ''); ?>">
		<td class="<?php echo (($portal_mode)?'bkkBorder1B':'wmtBorder1B'); ?>">&nbsp;</td>
		<td class="<?php echo (($portal_mode)?'bkkBorder1B bkkLabel':'wmtLabel wmtBorder1L wmtBorder1B'); ?>">&nbsp;<?php xl('None on File','e'); ?></td>
		<td class="<?php echo (($portal_mode)?'bkkBorder1B':'wmtBorder1L wmtBorder1B'); ?>">&nbsp;</td>
		<!-- td class="<?php // echo (($portal_mode)?'bkkBorder1B':'wmtBorder1L wmtBorder1B'); ?>">&nbsp;</td -->
		<td class="<?php echo (($portal_mode)?'bkkBorder1B':'wmtBorder1L wmtBorder1B'); ?>">&nbsp;</td>
		<td class="<?php echo (($portal_mode)?'bkkBorder1B':'wmtBorder1L wmtBorder1B'); ?>">&nbsp;</td>
	<?php if(!$portal_mode) { ?>
		<td class="<?php echo (($portal_mode)?'bkkBorder1B':'wmtBorder1L wmtBorder1B'); ?>">&nbsp;</td>
	<?php } ?>
	</tr>
<?php 
}
?>
	<tr>
</table>
<?php
$field_name = 'fyi_med_nt';
include($GLOBALS['srcdir'].'/wmt-v2/specified_text_box.inc.php');
// echo "Pat Entries Exist: $pat_entries_exist<br>\n";
// echo "Portal Mode: $portal_mode<br>\n";
// echo "Content of FYI Note: ",$pat_entries['fyi_med_nt']['content'],"<br>\n";
if($pat_entries_exist && !$portal_mode) {
	if($pat_entries['fyi_med_nt']['content'] && (strpos($dt{$field_prefix.'fyi_med_nt'},$pat_entries['fyi_med_nt']['content']) === false)) {
?>
		<div class="wmtLabel wmtPortalData wmtL" style="margin: 6px;">Notes input by the patient via the portal:</div>
		<div class="wmtPortalData wmtBorderHighlight wmtBody wmtL" id="tmp_<?php echo $field_prefix; ?>fyi_med_nt" style="padding: 6px;" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['fyi_med_nt']['content'], ENT_QUOTES, '', FALSE); ?></div>
<?php 
	}	
}
if($review = checkSettingMode('wmt::meds_erx_review','',$frmdir)) {
	$caller = 'meds_erx';
	$chk_title = 'Medications (eRx)';
	include($GLOBALS['srcdir'].'/wmt-v2/form_bricks/module_reviewed.inc.php');
}
?>
