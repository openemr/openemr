<?php 
$queued_by = '';
if($dt{'queued_by'} && $dt{'queued_by'} != 0) 
			$queued_by = UserNameFromID($dt{'queued_by'}, 'first');
?>
<table width="100%" border="0" cellspacing="0" cellpadding="4">
	<tr>
		<td class="wmtBody wmtClick" onclick="toggleThroughSelect('q1');">1. Do you intend to quit smoking in the near future or are you trying to quit now?</td>
		<td style="width: 50px;"><select name="q1" id="q1" class="wmtInput">
		<?php echo ListSel($dt{'q1'},'YesNo'); ?>
		</select></td>
	</tr>
	<tr>
		<td class="wmtBody wmtClick" onclick="toggleThroughSelect('q2');">2. Have you tried to quit smoking in the past?</td>
		<td><select name="q2" id="q2" class="wmtInput">
		<?php echo ListSel($dt{'q2'},'YesNo'); ?>
		</select></td>
	</tr>
	<tr>
		<td class="wmtBody wmtClick" onclick="toggleThroughSelect('q3');">3. Do you think smoking is harming your health</td>
		<td><select name="q3" id="q3" class="wmtInput">
		<?php echo ListSel($dt{'q3'},'YesNo'); ?>
		</select></td>
	</tr>
	<tr>
		<td class="wmtBody wmtClick" onclick="toggleThroughSelect('q4');">4. Do you have family or friends who will support you in your effort to quit?</td>
		<td><select name="q4" id="q4" class="wmtInput">
		<?php echo ListSel($dt{'q4'},'YesNo'); ?>
		</select></td>
	</tr>
	<tr>
		<td class="wmtBody wmtClick" onclick="toggleThroughSelect('q5');">5. Do you find it hard to stay on track when you quit smoking?</td>
		<td><select name="q5" id="q5" class="wmtInput">
		<?php echo ListSel($dt{'q5'},'YesNo'); ?>
		</select></td>
	</tr>
	<tr>
		<td class="wmtBody wmtClick" onclick="toggleThroughSelect('q6');">6. Are you worried about weight gain if you quit smoking?</td>
		<td><select name="q6" id="q6" class="wmtInput">
		<?php echo ListSel($dt{'q6'},'YesNo'); ?>
		</select></td>
	</tr>
	<tr>
		<td class="wmtBody wmtClick" onclick="toggleThroughSelect('q7');">7. Are you worried about how you will deal with stress if you quit?</td>
		<td><select name="q7" id="q7" class="wmtInput">
		<?php echo ListSel($dt{'q7'},'YesNo'); ?>
		</select></td>
	</tr>
	<tr>
		<td class="wmtBody wmtClick" onclick="toggleThroughSelect('q8');">8. Are you confident that you can quit smoking for good?</td>
		<td><select name="q8" id="q8" class="wmtInput">
		<?php echo ListSel($dt{'q8'},'YesNo'); ?>
		</select></td>
	</tr>
	<tr>
		<td class="wmtBody wmtClick" onclick="toggleThroughSelect('q9');">9. Do you feel you are motivated to quit smoking?</td>
		<td><select name="q9" id="q9" class="wmtInput">
		<?php echo ListSel($dt{'q9'},'YesNo'); ?>
		</select></td>
	</tr>
	<tr>
		<td class="wmtBody wmtClick" onclick="toggleThroughSelect('q10'); ">10. Do you have other smokers around you at home or at work?</td>
		<td><select name="q10" id="q10" class="wmtInput">
		<?php echo ListSel($dt{'q10'},'YesNo'); ?>
		</select></td>
	</tr>
	<tr>
		<td class="wmtBody wmtClick" onclick="toggleThroughSelect('q11'); ">11. Do you believe that secondhand smoke can harm your family and friends?</td>
		<td><select name="q11" id="q11" class="wmtInput">
		<?php echo ListSel($dt{'q11'},'YesNo'); ?>
		</select></td>
	</tr>
	<tr>
		<td colspan="2"><div class="wmtDottedB"></div></td>
	</tr>
	<tr>
		<td class="wmtLabel" colspan="2"><input name="referral" id="referral" type="checkbox" value="1" <?php echo $dt['referral'] == 1 ? 'checked' : ''; ?> onchange="toggleChange(this);" /><label for="referral">&nbsp;&nbsp;Referral made to Optum</label>
			<div id="queue_label" style="float: right; margin-right: 18px;"><?php echo $dt['queued'] ? 'Referral Sent On: ' . $dt['sent'] . ' by ' . $queued_by : ''; ?></div>
			<input type="hidden" name="tmp_send_now" id="tmp_send_now" value="" />
			<input type="hidden" name="queued" id="queued" value="<?php echo $dt['queued']; ?>" />
			<input type="hidden" name="sent" id="sent" value="<?php echo $dt['sent']; ?>" />
			<input type="hidden" name="queued_by" id="queued_by" value="<?php echo $dt['queued_by']; ?>" />
		</td> 
	</tr>
	<tr>
		<td colspan="2">Best Time to Contact?&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="best_time" id="best_time" class="wmtInput" style="width: 255px;" value="<?php echo $dt{'best_time'}; ?>" />
		<div style="float: right;">Best Number For Contact?&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="best_phone" id="best_phone" class="wmtInput" style="width: 255px;" value="<?php echo $dt{'best_phone'}; ?>" /></div></td>
	</tr>
</table>

		<!-- td class="wmtLabel" colspan="2"><input name="referral" id="referral" type="checkbox" value="1" <?php echo $dt['referral'] == 1 ? 'checked' : ''; ?> onchange="confirmHL7Send(this, '<?php echo $GLOBALS['webroot']; ?>','<?php echo $dt['queued']; ?>','<?php echo $pid; ?>','<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $id; ?>','<?php echo $_SESSION['authUserID']; ?>');" /><label for="referral">&nbsp;&nbsp;Referral made to Optum</label -->

<script src="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/form_js/optum.js" type="text/javascript"></script>
