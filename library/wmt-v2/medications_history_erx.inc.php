<?php
if(!isset($unlink_allow)) $unlink_allow = \OpenEMR\Common\Acl\AclMain::aclCheckCore('patients', 'med');
if(!isset($unlink_all_rx_history)) $unlink_all_rx_history = \OpenEMR\Common\Acl\AclMain::aclCheckCore('patients', 'med');
if(!isset($max_med_hist)) $max_med_hist = false;
if(!isset($dt['tmp_mhist_window_mode'])) $dt['tmp_mhist_window_mode'] = 'all';
if(!isset($dt['tmp_mhist_link_mode'])) $dt['tmp_mhist_link_mode'] = 'link';
?>
<table width='100%' border='0' cellspacing='0' cellpadding='3'>
	<tr>
		<td class='wmtLabel wmtC wmtBorder1B' style='width: 95px'><?php xl('Start Date','e'); ?></td>
		<td class='wmtLabel wmtC wmtBorder1L wmtBorder1B'><?php xl('Medication','e'); ?></td>
		<!-- Quantity is not available currently with eRx
		<td class='wmtLabel wmtC wmtBorder1L wmtBorder1B'><?php xl('Quantity','e'); ?></td>
		-->
		<td class='wmtLabel wmtC wmtBorder1L wmtBorder1B'><?php xl('Dosage','e'); ?></td>
		<td class='wmtLabel wmtC wmtBorder1L wmtBorder1B'><?php xl('Sig','e'); ?></td>
		<td class='wmtLabel wmtC wmtBorder1L wmtBorder1B'><?php xl('Comments','e'); ?></td>
<?php if($unlink_allow) {	?>
			<td class='wmtLabel wmtC wmtBorder1L wmtBorder1B' style='width: 115px'>&nbsp;</td>
<?php } else { ?>
			<td class='wmtLabel wmtC wmtBorder1L wmtBorder1B' style='width: 65px'>&nbsp;</td>
<?php } ?>
	</tr>
<?php
$cnt=1;
if($med_hist && (count($med_hist) > 0)) {
	foreach($med_hist as $prev) {
		if(($dt['tmp_mhist_window_mode'] == 'limit') && 
												$max_med_hist && ($cnt > $max_med_hist)) { break; }
		if(($dt['tmp_mhist_window_mode'] != 'all') && 
														!$prev['list_id']) { continue; }
		$sig1=trim(ListLook($prev['route'],'drug_route'));
		if($sig1) { $sig1 = ' '.$sig1; }
		$form=trim(ListLook($prev['form'],'drug_form'));
		if($form) { $form = ' '.$form; }
		$sig2=trim(ListLook($prev['interval'],'drug_interval'));
		if($sig2) { $sig2 = ' '.$sig2; }
		$sig1=$prev['dosage'].$form.$sig1.$sig2;
		$size=trim($prev['size']);
		$unit=trim(ListLook($prev['unit'],'drug_units'));
		$size.=$unit;
?>
	<tr>
		<td class='wmtBody wmtBorder1B'><input name='med_hist_id_<?php echo $cnt; ?>' id='med_hist_id_<?php echo $cnt; ?>' type='hidden' readonly='readonly' tabindex='-1' value="<?php echo $prev['id']; ?>" /><?php echo $prev['date_added']; ?>&nbsp;</td>
		<td class='wmtBody wmtBorder1L wmtBorder1B'><?php echo $prev['drug']; ?>&nbsp;</td>
		<!--
		<td class='wmtBody wmtBorder1L wmtBorder1B'><?php // echo $prev['quantity']; ?>&nbsp;</td>
		-->
		<td class='wmtBody wmtBorder1L wmtBorder1B'><?php echo $size; ?>&nbsp;</td>
		<td class='wmtBody wmtBorder1L wmtBorder1B'><?php echo $sig1; ?>&nbsp;</td>
		<td class='wmtBody wmtBorder1L wmtBorder1B'><input name='med_hist_comments_<?php echo $cnt; ?>' id='med_hist_comments_<?php echo $cnt; ?>' type='text' class='wmtFullInput' tabindex='-1' value="<?php echo $prev['note']; ?>" /></td>
		<td class='wmtBody wmtBorder1L wmtBorder1B'><div class="wmtListButton"><a href="javascript:;" class='css_button_small' tabindex='-1' onClick="return SubmitLinkBuilder('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $cnt; ?>','<?php echo $id; ?>','updatemedhist','med_hist_id_','Prescription');" ><span><?php xl('Update','e'); ?></span></a></div>
<?php
		if($unlink_allow) {
			if($prev['list_id']) { 
?>
			<div class="wmtListButton"><a href="javascript:;" class='css_button_small' tabindex='-1' onClick="return SubmitLinkBuilder('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $cnt; ?>','<?php echo $id; ?>','unlinkmedhist','med_hist_id_','Prescription');" ><span><?php xl('Un-Link','e'); ?></span></a></div>
<?php
			} else {
?>
			<div class="wmtListButton"><a href="javascript:;" class='css_button_small' tabindex='-1' onClick="return SubmitLinkBuilder('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $cnt; ?>','<?php echo $id; ?>','linkmedhist','ned_hist_id_','Prescription');" ><span><?php xl('Link','e'); ?></span></a></div>
<?php
			}
		}
?>
		</td>
	</tr>
<?php
		$cnt++;
	}
	$cnt--;
	if(($dt['tmp_mhist_window_mode'] == 'limit') && ($cnt == $max_med_hist) &&
												$max_med_hist && (count($med_hist) > $max_med_hist)) {
?>
	<tr>
		<td colspan="6" class="wmtBody2 wmtBorder1B"><i>&nbsp;&nbsp;*&nbsp;This view is currently limited to the most recent&nbsp;<?php echo $max_med_hist;?>&nbsp;historical prescriptions</i></td>
	</tr>
<?php
	}
	if($unlink_allow || $max_med_hist) {
		// If this is enabled we need buttons to toggle limit and views
		if($max_med_hist) {
?>
	<tr>
		<td class="wmtCollapseBar wmtBorder1B">&nbsp;<input type="hidden" name="tmp_med_hist_cnt" id="tmp_med_hist_cnt" tabindex="-1" value="<?php echo ($cnt); ?>" /></td>
<?php
			if(($dt['tmp_mhist_window_mode'] == 'limit') && 
												$max_med_hist && (count($med_hist) > $max_med_hist)) {
?>
		<td class="wmtCollapseBar wmtBorder1B">
		<div style="float: left; padding-left: 5px;"><a href="javascript:;" class='css_button_small' tabindex='-1' onClick="return ToggleWindowDisplayMode('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $id; ?>','medhistwindow','all');" ><span><?php xl('Show ALL Prescription History','e'); ?></span></a></div>
		</td>
<?php
			} else if(($dt['tmp_mhist_window_mode'] == 'all') && 
												(count($med_hist) > $max_med_hist) && $max_med_hist) {
?>
		<td class="wmtCollapseBar wmtBorder1B">
		<div style="float: left; padding-left: 5px;"><a href="javascript:;" class='css_button_small' tabindex='-1' onClick="return ToggleWindowDisplayMode('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $id; ?>','medhistwindow','limit');" ><span><?php xl('Show '.$max_med_hist.' Most Recent Linked','e'); ?></span></a></div>
<?php
			} else {
			// Nothing needed - don't confuse the end users!
?>
		<td class="wmtCollapseBar wmtBorder1B">&nbsp;</td>
<?php
			}
		} else {
?>
		<td class="wmtCollapseBar wmtBorder1B">&nbsp;</td>
<?php
		}
?>
		<td class="wmtCollapseBar wmtBorder1B" colspan="2">&nbsp;</td>
<?php
		if($unlink_all_rx_history) {
?>
		<td class="wmtCollapseBar wmtBorder1B" colspan="2">
		<div style="float: right; padding-right: 15px;"><a href="javascript:;" class='css_button_small' tabindex='-1' onClick="return SubmitLinkBuilder('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','','<?php echo $id; ?>','unlinkallmedhist');" ><span><?php xl('Un-Link ALL History','e'); ?></span></a></div>
		</td>
<?php
		} else {
?>
		<td class="wmtCollapseBar wmtBorder1B" colspan="2">
<?php
		}
?>
	</tr>
<?php 
	}
} else {
?>
	<tr>
		<td class='wmtLabel wmtBorder1B'>&nbsp;</td>
		<td class='wmtLabel wmtBorder1L wmtBorder1B'>&nbsp;<?php xl('None on File','e'); ?></td>
		<td class='wmtLabel wmtBorder1L wmtBorder1B'>&nbsp;</td>
		<td class='wmtLabel wmtBorder1L wmtBorder1B'>&nbsp;</td>
		<!-- td class='wmt wmtLabel wmtBorder1L wmtBorder1B'>&nbsp;</td -->
		<td class='wmtLabel wmtBorder1L wmtBorder1B'>&nbsp;</td>
		<td class='wmtLabel wmtBorder1L wmtBorder1B'>&nbsp;</td>
	</tr>
<?php
}

// if(checkSettingMode('wmt::fyi_medhist_nt')) {
?>
	<tr>
		<td class="wmtLabel">Notes:</td>
	</tr>
	<tr>
		<td colspan="6" class="wmtBorder1B"><textarea name="fyi_medhist_nt" id="fyi_medhist_nt" rows="4" class="wmtFullInput"><?php echo $dt['fyi_medhist_nt']; ?></textarea>
	</tr>
<?php
// }
?>

</table>
