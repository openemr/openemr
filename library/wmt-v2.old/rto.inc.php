<?php
include_once($GLOBALS['srcdir'].'/wmt-v2/wmt.msg.inc');
if(!isset($frmdir)) $frmdir = '';
if(!isset($rto_data)) $rto_data = array();
if(!isset($dt{'rto_action'})) $dt{'rto_action'} = '';
if(!isset($dt{'rto_ordered_by'})) $dt{'rto_ordered_by'} = '';
if(!isset($dt{'rto_status'})) $dt{'rto_status'} = '';
if(!isset($dt{'rto_resp_user'})) $dt{'rto_resp_user'} = '';
if(!isset($dt{'rto_notes'})) $dt{'rto_notes'} = '';
if(!isset($dt{'rto_target_date'})) $dt{'rto_target_date'} = '';
if(!isset($dt{'rto_num'})) $dt{'rto_num'} = '';
if(!isset($dt{'rto_frame'})) $dt{'rto_frame'} = '';
if(!isset($dt{'rto_date'})) $dt{'rto_date'} = '';
if(!isset($dt{'rto_stop_date'})) $dt{'rto_stop_date'} = '';
if(!isset($dt{'rto_repeat'})) $dt{'rto_repeat'} = '';
if($GLOBALS['date_display_format'] == 1) {
	$date_title_fmt = 'MM/DD/YYYY';
} else if($GLOBALS['date_display_format'] == 2) {
	$date_title_fmt = 'DD/MM/YYYY';
} else $date_title_fmt = 'YYYY-MM-DD';
$date_title_fmt = 'Please Format Date As '.$date_title_fmt;
?>
<table width='100%'	border='0' cellspacing='0' cellpadding='2'>
	<tr><td colspan='6'><div style='height: 3px;'</td></tr>
<?php
$is_admin = \OpenEMR\Common\Acl\AclMain::aclCheckCore('admin','super');
$cnt=1;
foreach($rto_data as $rto) {
	$complete = isComplete($rto['rto_status']);
?>
	<tr>
		<td class='wmtLabel2'>&nbsp;<?php xl('Order','e') ?>:</td>
		<td>
			<?php if(!$is_admin && $complete) { ?>
				<input name='rto_action_<?php echo $cnt; ?>' id='rto_action_<?php echo $cnt; ?>' class='wmtFullInput' readonly='readonly' type='text' value="<?php echo ListLook($rto['rto_action'],'RTO_Action'); ?>" />
			<?php } else { ?>
				<select name='rto_action_<?php echo $cnt; ?>' id='rto_action_<?php echo $cnt; ?>' class='wmtFullInput' onchange="updateBorder(this);" >
				<?php ListSel($rto['rto_action'], 'RTO_Action'); ?></select>
			<?php } ?>
		</td>

		<td class='wmtLabel2'>&nbsp;<?php xl('Ordered By','e'); ?>:</td>
		<td>
			<?php if(!$is_admin && $complete) { ?>
				<input name='rto_ordered_by_<?php echo $cnt; ?>' id='rto_ordered_by_<?php echo $cnt; ?>' class='wmtFullInput' readonly='readonly' type='text' value="<?php echo UserNameFromName($rto['rto_ordered_by']); ?>" />
			<?php } else { ?>
				<select name='rto_ordered_by_<?php echo $cnt; ?>' id='rto_ordered_by_<?php echo $cnt; ?>' class='wmtFullInput' style='float: right;'>
	 			<?php UserSelect($rto['rto_ordered_by']); ?></select>
			<?php } ?>
		</td>

		<td class='wmtLabel2'>&nbsp;<?php xl('Notes','e'); ?>:</td>
		<td><input name='rto_id_<?php echo $cnt; ?>' id='rto_id_<?php echo $cnt; ?>' type='hidden' value="<?php echo $rto['id']; ?>" />
			<?php if(isset($rto['test_target_dt'])) { ?>
				<input name='rto_test_target_dt_<?php echo $cnt; ?>' id='rto_test_target_dt_<?php echo $cnt; ?>' type='hidden' value="<?php echo $rto['test_target_dt']; ?>" />
			<?php } ?>
			<?php if($is_admin) { ?>
			<a href="javascript:;" class='css_button_small' tabindex='-1' onClick="return DeleteRTO('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $cnt; ?>','<?php echo $id; ?>');"><span><?php xl('Delete','e'); ?></span></a>
			<?php } ?>
		&nbsp;</td>
	</tr>

	<tr>
		<td class='wmtLabel2'>&nbsp;<?php xl('Status','e'); ?>:</td>
		<td>
			<?php if(!$is_admin && $complete) { ?>
				<input name='rto_status_<?php echo $cnt; ?>' id='rto_status_<?php echo $cnt; ?>' class='wmtFullInput' readonly='readonly' type='text' value="<?php echo ListLook($rto['rto_status'],'RTO_Status'); ?>" />
			<?php } else { ?>
				<select name='rto_status_<?php echo $cnt; ?>' id='rto_status_<?php echo $cnt; ?>' class='wmtFullInput'>
				<?php ListSel($rto['rto_status'], 'RTO_Status'); ?></select>
			<?php } ?>
		</td>

		<td class='wmtLabel2'>&nbsp;<?php xl('Assigned To','e'); ?>:</td>
		<td>
			<?php if(!$is_admin && $complete) { ?>
				<input name='rto_resp_<?php echo $cnt; ?>' id='rto_resp_<?php echo $cnt; ?>' class='wmtFullInput' readonly='readonly' type='text' value="<?php echo MsgUserGroupDisplay($rto['rto_resp']); ?>" />
			<?php } else { ?>
				<select name='rto_resp_<?php echo $cnt; ?>' id='rto_resp_<?php echo $cnt; ?>' class='wmtFullInput' onchange="updateBorder(this);" >
				<?php MsgUserGroupSelect($rto['rto_resp_user'], true, false, false, array(), true); ?></select>
			<?php } ?>
		</td>

		<td rowspan='3'><div style='margin-left: 5px; margin-right: 5px;'>
			<textarea name='rto_notes_<?php echo $cnt; ?>' id='rto_notes_<?php echo $cnt; ?>' class='wmtFullInput' <?php echo (!$is_admin && $complete) ? 'readonly' : ''; ?> rows='4'><?php echo htmlspecialchars($rto['rto_notes'], ENT_QUOTES, '', FALSE); ?></textarea></div>
		</td>
		<td>
			<?php if(!$is_admin && $complete) { ?>
				&nbsp;
			<?php } else { ?>
				<a class='css_button_small' tabindex='-1' onClick="return SubmitLinkBuilder('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $cnt; ?>','<?php echo $id; ?>','updaterto','rto_id_','Order/Task');" href='javascript:;'><span><?php xl('Revise','e'); ?></span></a>
			<?php } ?>
		</td>
	</tr>
	<tr>
		<td class='wmtLabel2'>&nbsp;<?php xl('Due Date','e'); ?>:</td>
		<td><input name='rto_target_date_<?php echo $cnt; ?>' id='rto_target_date_<?php echo $cnt; ?>' class='wmtInput' type='text' <?php echo (!$is_admin && $complete)? 'readonly' : ''; ?> style='width: 85px;' value="<?php echo oeFormatShortDate($rto['rto_target_date']); ?>" title="<?php echo $date_title_fmt; ?>" 
			<?php if(isset($rto['test_target_dt'])) { ?>
				onchange="TestByAction('rto_test_target_dt_<?php echo $cnt; ?>','rto_target_date_<?php echo $cnt; ?>','rto_action_<?php echo $cnt; ?>');"	
			<?php } ?>
				/><div style="float: right;"><span class='wmtLabel2'>&nbsp;&nbsp;-<?php xl('or','e'); ?>-&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
			<?php if(!$is_admin && $complete) { ?>
				<input name='rto_num_<?php echo $cnt; ?>' id='rto_num_<?php echo $cnt; ?>' class='wmtInput' readonly='readonly' type='text' style="width: 30px;" value="<?php echo ListLook($rto['rto_num'], 'RTO_Number'); ?>" />
				<?php } else { ?>
					<select name='rto_num_<?php echo $cnt; ?>' id='rto_num_<?php echo $cnt; ?>' class='wmtInput' style='float: right;' onchange="FutureDate('rto_date_<?php echo $cnt; ?>','rto_num_<?php echo $cnt; ?>','rto_frame_<?php echo $cnt; ?>','rto_target_date_<?php echo $cnt; ?>','<?php echo $GLOBALS['date_display_format']; ?>');" >
					<?php ListSel($rto['rto_num'], 'RTO_Number'); ?></select>
				<?php } ?>
		</div></td>

		<td colspan='2'>&nbsp;&nbsp;
		<?php if(!$is_admin && $complete) { ?>
			<input name='rto_frame_<?php echo $cnt; ?>' id='rto_frame_<?php echo $cnt; ?>' class='wmtInput' readonly='readonly' type='text' style="width: 80px;" value="<?php echo ListLook($rto['rto_frame'], 'RTO_Frame'); ?>" />
		<?php } else { ?>
			<select name='rto_frame_<?php echo $cnt; ?>' id='rto_frame_<?php echo $cnt; ?>' class='wmtInput' onchange="FutureDate('rto_date_<?php echo $cnt; ?>','rto_num_<?php echo $cnt; ?>','rto_frame_<?php echo $cnt; ?>','rto_target_date_<?php echo $cnt; ?>','<?php echo $GLOBALS['date_display_format']; ?>');" >
			<?php ListSel($rto['rto_frame'], 'RTO_Frame'); ?></select>
		<?php } ?>
			<span class='wmtLabel2'>&nbsp;&nbsp;<?php xl('from','e'); ?>&nbsp;&nbsp;</span>
			<input name='rto_date_<?php echo $cnt; ?>' id='rto_date_<?php echo $cnt; ?>' class='wmtInput' type='text' <?php echo (!$is_admin && $complete) ? 'readonly ' : ''; ?> style='width: 85px' value="<?php echo oeFormatShortDate($rto['rto_date']); ?>" onchange="FutureDate('rto_date_<?php echo $cnt; ?>','rto_num_<?php echo $cnt; ?>','rto_frame_<?php echo $cnt; ?>','rto_target_date_<?php echo $cnt; ?>','<?php echo $GLOBALS['date_display_format']; ?>');" title="<?php echo $date_title_fmt; ?>" /></td>
		<td><a class='css_button_small' tabindex='-1' onClick="return SubmitLinkBuilder('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $cnt; ?>','<?php echo $id; ?>','remindrto','rto_id_','Order/Task');" href='javascript:;'><span><?php xl('Send Reminder','e'); ?></span></a></td>
	</tr>
	<tr>
		<td class="wmtLabel2">&nbsp;Recurring:</td>
		<td class="wmtBody2"><input name='rto_repeat_<?php echo $cnt; ?>' id='rto_repeat_<?php echo $cnt; ?>' type='checkbox' value='1' <?php echo $rto['rto_repeat'] == 1 ? 'checked="checked"' : ''; ?> /><label for='rto_repeat_<?php echo $cnt; ?>'>&nbsp;Yes (as above)</label></td>
		<td class="wmtLabel2">&nbsp;Stop Date:</td>
		<td><input name='rto_stop_date_<?php echo $cnt; ?>' id='rto_stop_date_<?php echo $cnt; ?>' class='wmtInput' type='text' style='width: 85px' value="<?php echo oeFormatShortDate($rto['rto_stop_date']); ?>" title='<?php echo $date_title_fmt; ?>' /></td>
		<?php if(!$complete) { ?>
		<td><a class='css_button_small' tabindex='-1' onClick="return handleComplete('<?php echo $cnt; ?>');" href='javascript:;'><span><?php xl('Set Complete','e'); ?></span></a></td>
		<?php } ?>
	</tr>
	<tr><td colspan='8'><div class='wmtDottedB'></div></td></tr>
<?php
	$cnt++;
}
?>
	<tr>
		<td class='wmtLabel2' style='width: 70px;'>&nbsp;<?php xl('Order','e'); ?>:</td>
		<td style='width: 20%;'><select name='rto_action' id='rto_action' class='wmtFullInput' <?php echo ($frmdir == 'rto') ? "tabindex='10'" : ""; ?> onchange="updateBorder(this);" ><?php ListSel($dt['rto_action'], 'RTO_Action'); ?>
		</select></td>
		<td class='wmtLabel2' style='width: 95px;'><?php xl('Ordered By','e'); ?>:</td>
		<td style='width: 20%;'><select name='rto_ordered_by' id='rto_ordered_by' class='wmtFullInput' <?php echo ($frmdir == 'rto') ? "tabindex='20'" : ""; ?>><?php UserSelect($dt['rto_ordered_by']); ?>
		</select></td>
		<td class='wmtLabel2'>&nbsp;<?php xl('Notes','e'); ?>:</td>
		<td style='width: 95px;'>&nbsp;<input name='tmp_rto_cnt' id='tmp_rto_cnt' type='hidden' tabindex='-1' value="<?php echo ($cnt - 1); ?>" /></td>
	</tr>
	<tr>
		<td class='wmtLabel2'><?php xl('Status','e'); ?>:</td>
		<td><select name='rto_status' id='rto_status' class='wmtFullInput' <?php echo ($frmdir == 'rto') ? "tabindex='40'" : ""; ?>><?php ListSel($dt['rto_status'], 'RTO_Status'); ?>
		</select></td>
		<td class='wmtLabel2'>&nbsp;<?php xl('Assigned To','e'); ?>:</td>
		<td><select name='rto_resp_user' id='rto_resp_user' class='wmtFullInput' <?php echo ($frmdir == 'rto') ? "tabindex='50'" : ""; ?> onchange="updateBorder(this);" ><?php MsgUserGroupSelect($dt['rto_resp_user'], true, false, false, array(), true); ?>
		</select></td>
		<td rowspan='3'><div style='margin-right: 5px; margin-left: 5px;'><textarea name='rto_notes' id='rto_notes' class='wmtFullInput' <?php echo ($frmdir == 'rto') ? "tabindex='200'" : ""; ?> rows='4'><?php echo $dt['rto_notes']; ?></textarea></div>
		</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class='wmtLabel2'>&nbsp;<?php xl('Due Date','e'); ?>:</td>
		<td><input name='rto_target_date' id='rto_target_date' class='wmtInput' <?php echo ($frmdir == 'rto') ? "tabindex='80'" : ""; ?> style='text-align: right; width: 85px;' type='text' value="<?php echo oeFormatShortDate($dt['rto_target_date']); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title='<?php echo $date_title_fmt; ?>' />
			<img src='../../pic/show_calendar.gif' width='22' height='20' id='img_rto_target_dt' border='0' alt='[?]' style='cursor:pointer; vertical-align: middle;' tabindex='-1' title="<?php xl('Click here to choose a date','e'); ?>">
			<div style='float: right;'><span class='wmtLabel2'>&nbsp;&nbsp;-<?php xl('or','e'); ?>-&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><select name='rto_num' id='rto_num' class='wmtInput' <?php echo ($frmdir == 'rto') ? "tabindex='90'" : ""; ?> onchange="SetRTOStatus('rto_status'); FutureDate('rto_date','rto_num','rto_frame','rto_target_date','<?php echo $GLOBALS['date_display_format']; ?>');"><?php ListSel($dt['rto_num'], 'RTO_Number'); ?>
		</select></div></td>
		<td colspan='2'>&nbsp;&nbsp;<select name='rto_frame' id='rto_frame' class='wmtInput' <?php echo ($frmdir == 'rto') ? "tabindex='100'" : ""; ?> onchange="SetRTOStatus('rto_status'); FutureDate('rto_date','rto_num','rto_frame','rto_target_date','<?php echo $GLOBALS['date_display_format']; ?>');"><?php ListSel($dt['rto_frame'], 'RTO_Frame'); ?>
			</select><span class='wmtLabel2'>&nbsp;<?php xl('From','e'); ?>&nbsp;</span>
			<input name='rto_date' id='rto_date' class='wmtInput wmtR' type='text' <?php echo ($frmdir == 'rto') ? "tabindex='110'" : ""; ?> style='width: 85px;' value="<?php echo oeFormatShortDate($dt['rto_date']); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" onchange="FutureDate('rto_date','rto_num','rto_frame','rto_target_date','<?php echo $GLOBALS['date_display_format']; ?>');" title='<?php echo $date_title_fmt; ?>' />
			<img src='../../pic/show_calendar.gif' width='22' height='20' id='img_rto_dt' border='0' alt='[?]' style='cursor:pointer; vertical-align: middle;' title="<?php xl('Click here to choose a date','e'); ?>"></td>
	</tr>
	<tr>
		<td class="wmtLabel2">&nbsp;Recurring:</td>
		<td class="wmtBody2"><input name='rto_repeat' id='rto_repeat' type='checkbox' value='1' <?php echo ($frmdir == 'rto') ? "tabindex='140'" : ""; ?> <?php echo $dt['rto_repeat'] == 1 ? 'checked="checked"' : ''; ?> /><label for='rto_repeat'>&nbsp;Yes (as above)</label></td>
		<td class="wmtLabel2">&nbsp;Stop Date:</td>
		<td><input name='rto_stop_date' id='rto_stop_date' class='wmtInput wmtR' type='text' style='width: 85px' <?php echo ($frmdir == 'rto') ? "tabindex='150'" : ""; ?> value="<?php echo oeFormatShortDate($dt['rto_stop_date']); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title='Specify a date to stop this order if applicable' />
			<img src='../../pic/show_calendar.gif' width='22' height='20' id='img_stop_dt' border='0' alt='[?]' style='cursor:pointer; vertical-align: middle;' title="<?php xl('Click here to choose a date','e'); ?>"></td>
	</tr>
	<tr><td class='wmtBorder1B' colspan='6'><div style='height: 3px;'</td></tr>
	<tr>
		<td class='wmtCollapseBar' colspan='6' style='margin: 4px;'><a class='css_button' onClick="SubmitRTO('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $id; ?>');" href='javascript:;'><span style='text-transform: none;'><?php xl('Add Another','e'); ?></span></a>
<?php if(isset($_GET['allrto'])) { ?>
			<a class='css_button' style='float: right; padding-right: 10px;' onClick="return ShowPendingRTO('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $id; ?>');" href='javascript:;'><span style='text-transform: none;'><?php xl('Show Pending','e'); ?></span></a></td>
<?php } else { ?>
			<a class='css_button' style='float: right; padding-right: 15px;' onClick="return ShowAllRTO('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $id; ?>');" href='javascript:;'><span style='text-transform: none;'><?php xl('Show ALL','e'); ?></span></a></td>
<?php } ?>
	</tr>
</table>
