<table width='100%'	border='0' cellspacing='0' cellpadding='2'>
	<!-- tr><td colspan='6'><div style='height: 3px;'</td></tr -->
	<tr>
		<td class="wmtLabel wmtBorder1B">&nbsp;<?php echo xl('Order') ?>:</td>
		<td class='wmtLabel wmtBorder1B wmtBorder1L'>&nbsp;<?php echo xl('Status'); ?>:</td>
		<td class='wmtLabel wmtBorder1B wmtBorder1L'>&nbsp;<?php echo xl('Assigned To'); ?>:</td>
		<td class="wmtBorder1B wmtBorder1L">&nbsp;</td>
	</tr>
<?php
if(!isset($tasks)) { $tasks= array(); }
$cnt=0;
foreach($tasks as $task) {
	//echo "<tr><td colspan='3'>Test Date: (",$rto['test_target_dt'],")</td></tr>\n";
?>
	<tr>
		<td><select name='rto_action_<?php echo $cnt; ?>' id='rto_action_<?php echo $cnt; ?>' class='wmtFullInput'>
		<?php ListSel($task['rto_action'], 'RTO_Action'); ?></select></td>
		<td><select name='rto_status_<?php echo $cnt; ?>' id='rto_status_<?php echo $cnt; ?>' class='wmtFullInput'>
		<?php ListSel($task['rto_status'], 'RTO_Status'); ?></select></td>
		<td><select name='rto_resp_<?php echo $cnt; ?>' id='rto_resp_<?php echo $cnt; ?>' class='wmtFullInput'>
		<?php UserSelect($task['rto_resp_user']); ?></select></td>
		<td class="wmtBorder1L">&nbsp;<input name='rto_id_<?php echo $cnt; ?>' id='rto_id_<?php echo $cnt; ?>' type='hidden' value="<?php echo $task['id']; ?>" />&nbsp;
			<div><a href="javascript:;" class='css_button_small' tabindex='-1' onClick="return UpdateRTO('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $cnt; ?>','<?php echo $id; ?>');"><span><?php echo xl('Revise'); ?></span></a></div>
		</td>
	</tr>
	<tr>
		<td colspan="3" rowspan="2" class="wmtBorder1B"><div style='margin-left: 5px; margin-right: 5px;'>
			<textarea name='rto_notes_<?php echo $cnt; ?>' id='rto_notes_<?php echo $cnt; ?>' class='wmtFullInput' rows='2'><?php echo $task['rto_notes']; ?></textarea></div>
		</td>
		<td class="wmtBorder1L wmtBorder1B">&nbsp;
			<div><a href="javascript:;" class='css_button_small' tabindex='-1' onClick="return CheckRTO('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $cnt; ?>','<?php echo $id; ?>');"><span><?php echo xl('Result?'); ?></span></a></div>
		</td>
	</tr>
	<tr>
		<td class="wmtBorder1L">&nbsp;
		<?php if(\OpenEMR\Common\Acl\AclMain::aclCheckCore('admin','super')) { ?>
			<a href="javascript:;" class='css_button_small' tabindex='-1' onClick="return DeleteRTO('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $cnt; ?>','<?php echo $id; ?>');"><span><?php echo xl('Delete'); ?></span></a>
		<?php } ?>
		</td>
	</tr>
<?php
	$cnt++;
}
?>
	<tr>
		<td><select name='rto_action' id='rto_action' class='wmtFullInput'>
			<?php ListSel($dt['rto_action'], 'RTO_Action'); ?></select></td>
		<td><select name='rto_status' id='rto_status' class='wmtFullInput'>
			<?php ListSel($dt['rto_status'], 'RTO_Status'); ?></select></td>
		<td class='wmtBody'><select name='rto_resp_user' id='rto_resp_user' class='wmtFullInput'>
			<?php UserSelect($dt['rto_resp_user']); ?></select></td>
		<td style='width: 95px;' class="wmtBorder1L">&nbsp;<input name='tmp_rto_cnt' id='tmp_rto_cnt' type='hidden' tabindex='-1' value="<?php echo $cnt; ?>" /></td>
	</tr>
	<tr>
		<td class='wmtBody' colspan='3'><div style='margin-right: 5px; margin-left: 5px;'>
		<textarea name='rto_notes' id='rto_notes' class='wmtFullInput' rows='2'><?php echo $dt['rto_notes']; ?></textarea></div></td>
		<td class="wmtBorder1L">&nbsp;</td>
	</tr>
	<tr>
		<td class='wmtCollapseBar wmtBorder1T' style='margin: 4px;'><a class='css_button' onClick="return SubmitRTO('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $id; ?>');" href='javascript:;'><span style='text-transform: none;'><?php echo xl('Add Another'); ?></span></a></td>
		<td class="wmtCollapseBar wmtBorder1T">&nbsp;</td>
		<td class='wmtCollapseBar wmtBorder1T' colspan='2' style='margin: 4px;'><div style="float: right;"><a class='css_button' onClick="return ResultRTO('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $id; ?>');" href='javascript:;'><span style='text-transform: none;'><?php echo xl('Check for Results'); ?></span></a></div></td>
	</tr>
</table>
