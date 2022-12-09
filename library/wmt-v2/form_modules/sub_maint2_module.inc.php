<?php 
unset($local_fields);
$local_fields = sqlListFields('form_sub_maint2');
$slice = 10;
if($frmdir != 'sub_maint22') $slice = 14;
$local_fields = array_slice($local_fields, $slice);

include(FORM_BRICKS . 'module_setup.inc.php');
include(FORM_BRICKS . 'module_loader.inc.php');

if($frmdir != $this_module) {
	if(!isset($dt['sm_form_dt'])) $dt['sm_form_dt']= '';
	if($form_mode == 'new') {
		if(!$dt['sm_form_dt']) $dt['sm_form_dt'] = date('Y-m-d');
	} else {
		$dt['sm_form_dt'] = $md['form_dt'];
	}
	if($dt['sm_form_dt'] == '0000-00-00') $dt['sm_form_dt'] = NULL;
}
if($dt['sm_next_dt'] == '0000-00-00') $dt['sm_next_dt'] = NULL;
if($dt['sm_counsel_dt'] == '0000-00-00') $dt['sm_counsel_dt'] = NULL;

if($draw_display) {
?>
<table width="100%" border="0" cellpadding="2" cellspacing="0">
<?php
// THIS IS SO WE CAN INCLUDE A SEPARATE DATE FOR MAINTENANCE WITHIN THE FORM
if($frmdir != $this_module) {
?>
  <tr>
		<td style="text-align: right;"><input name="sm_form_dt" id="sm_form_dt" style="width: 80px;" type="text" value="<?php echo htmlspecialchars(oeFormatShortDate($dt['sm_form_dt']), ENT_QUOTES); ?>" title="Enter In <?php echo $date_title_fmt; ?> Format" />
			<img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="24" height="22" id="img_sm_form_dt" border="0" alt="[?]" style="cursor:pointer; vertical-align: middle;" tabindex="-1" title="<?php xl('Click here to choose a date','e'); ?>"></td>
		<td>Induction Visit Date</td>
	</tr>

<script type="text/javascript">
Calendar.setup({inputField:"sm_form_dt", ifFormat:"<?php echo $date_img_fmt; ?>", daFormat:"<?php echo $date_img_fmt; ?>", button:"img_sm_form_dt"});
</script>
<?php
}
?>

	<tr>
		<td style="text-align: right; width: 220px;"><select name="sm_q1" id="sm_q1"><?php ListSel($dt{'sm_q1'},'yesno'); ?></select></td>
		<td><span class="clickable text" onclick="toggleThroughSelect('sm_q1');">Was the Prescription Drug Monitoring Program reviewed? (PDMP)</span><input id="sub_maint2_id" name="sub_maint2_id" type="hidden" value="<?php echo $dt['sub_maint2_id']; ?>" /></td>
	</tr>
	<tr>
		<td style="text-align: right;"><select name="sm_q5" id="sm_q5"><?php ListSel($dt{'sm_q5'},'Sub_Dose'); ?></select></td>
		<td><span class="clickable text" onclick="toggleThroughSelect('sm_q5');">Dosage&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
		<select name="sm_q6" id="sm_q6"><?php NumSel($dt{'sm_q6'}, 1, 100, 1, '', TRUE); ?></select>&nbsp;&nbsp;
		<span class="clickable text" onclick="toggleThroughSelect('sm_q6');">Pill Count</span></td>
	</tr>
	<tr>
		<td style="text-align: right;"><select name="sm_q7" id="sm_q7"><?php ListSel($dt{'sm_q7'},'Pos_Neg'); ?></select></td>
		<td><span class="clickable text" onclick="toggleThroughSelect('sm_q7');">Drug screen results</span></td>
	</tr>
	<tr>
		<td style="text-align: right;"><select name="sm_q3" id="sm_q3"><?php ListSel($dt{'sm_q3'},'yesno'); ?></select></td>
		<td><span class="clickable text" onclick="toggleThroughSelect('sm_q3');">Is the patient and family members educated on the use of Naloxone?</span></td>
	</tr>
	<tr>
		<td style="text-align: right;"><select name="sm_q2" id="sm_q2"><?php ListSel($dt{'sm_q2'},'yesno'); ?></select></td>
		<td><span class="clickable text" onclick="toggleThroughSelect('sm_q2');">Does the patient have Naloxone prescribed?</span></td>
	</tr>
	<tr>
		<td style="text-align: right;"><select name="sm_q8" id="sm_q8"><?php ListSel($dt{'sm_q8'},'Sub_Pharmacies'); ?></select></td>
		<td><span class="clickable text" onclick="toggleThroughSelect('sm_q8');">Pharmacy</span>&nbsp;&nbsp;<input name="sm_q8_nt" id="sm_q8_nt" type="text" value="<?php echo htmlspecialchars($dt['sm_q8_nt'], ENT_QUOTES); ?>" /></td>
	</tr>
	<tr>
		<td style="text-align: right;"><select name="sm_q10" id="sm_q10"><?php ListSel($dt{'sm_q10'},'yesno'); ?></select></td>
		<td><span class="clickable text" onclick="toggleThroughSelect('sm_q10');">Have you used drugs since your last visit?</span></td>
	</tr>
	<tr>
		<td style="text-align: right;"><select name="sm_q11" id="sm_q11"><?php ListSel($dt{'sm_q11'},'yesno'); ?></select></td>
		<td><span class="clickable text" onclick="toggleThroughSelect('sm_q11');">Have you taken your medications as prescribed?</span></td>
	</tr>
	<tr>
		<td style="text-align: right;"><select name="sm_q12" id="sm_q12"><?php ListSel($dt{'sm_q12'},'yesno'); ?></select></td>
		<td><span class="clickable text" onclick="toggleThroughSelect('sm_q12');">Is your housing situation stable?</span></td>
	</tr>
	<tr>
		<td style="text-align: right;"><select name="sm_q13" id="sm_q13"><?php ListSel($dt{'sm_q13'},'yesno'); ?></select></td>
		<td><span class="clickable text" onclick="toggleThroughSelect('sm_q13');">Are you in a support group outside of this program?</span></td>
	</tr>
	<tr>
		<td style="text-align: right;"><select name="sm_q14" id="sm_q14"><?php ListSel($dt{'sm_q14'},'yesno'); ?></select></td>
		<td><span class="clickable text" onclick="toggleThroughSelect('sm_q14');">Have you been arrested or had any other illegal involvement since your last visit?</span></td>
	</tr>
	<tr>
		<td style="text-align: right;"><select name="sm_q15" id="sm_q15"><?php ListSel($dt{'sm_q15'},'Employment_Status'); ?></select></td>
		<td><span class="clickable text" onclick="toggleThroughSelect('sm_q15');">What is your employment status?</span></td>
	</tr>
	<tr>
		<td style="text-align: right;"><select name="sm_q9" id="sm_q9"><?php NumSel($dt{'sm_q9'}, 1, 10, 1, 11, TRUE); ?></select></td>
		<td><span class="clickable text" onclick="toggleThroughSelect('sm_q9');">Readiness for Change</span></td>
	</tr>
	<tr>
		<td class="text" style="text-align: right;">Goals:</td>
		<td><textarea rows="4" name="sm_goal_nt" id="sm_goal_nt" class="wmtFullInput"><?php echo htmlspecialchars($dt{'sm_goal_nt'}, ENT_NOQUOTES); ?></textarea></td>
	</tr>
	<tr>
		<td class="text" style="text-align: right;">Possible barriers:</td>
		<td><textarea rows="4" name="sm_barrier_nt" id="sm_barrier_nt" class="wmtFullInput"><?php echo htmlspecialchars($dt{'sm_barrier_nt'}, ENT_NOQUOTES); ?></textarea></td>
	</tr>
  <tr>
		<td style="text-align: right;"><input name="sm_next_dt" id="sm_next_dt" style="width: 80px;" type="text" value="<?php echo htmlspecialchars(oeFormatShortDate($dt['sm_next_dt']), ENT_QUOTES); ?>" title="Enter In <?php echo $date_title_fmt; ?> Format" />&nbsp;
			<img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="24" height="22" id="img_next_dt" border="0" alt="[?]" style="cursor:pointer; vertical-align: middle;" tabindex="-1" title="<?php xl('Click here to choose a date','e'); ?>"></td>
		<td class="text">Next MAT visit date and time&nbsp;&nbsp;&nbsp;
		<select name="sm_next_hr" id="sm_next_hr" style="width: 60px;">
		<?php NumSel($dt['sm_next_hr'], 7, 19, 1, '', TRUE); ?></select>
		&nbsp;:&nbsp;
		<select name="sm_next_mn" id="sm_next_mn" style="width: 60px;">
		<?php NumSel($dt['sm_next_mn'], 0, 55, 5, '', TRUE, '', 0, 2); ?></select>
		</td>
	</tr>
  <tr>
		<td style="text-align: right;"><input name="sm_counsel_dt" id="sm_counsel_dt" style="width: 80px;" type="text" value="<?php echo htmlspecialchars(oeFormatShortDate($dt['sm_counsel_dt']), ENT_QUOTES); ?>" title="Enter In <?php echo $date_title_fmt; ?> Format" />&nbsp;
			<img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="24" height="22" id="img_counsel_dt" border="0" alt="[?]" style="cursor:pointer; vertical-align: middle;" tabindex="-1" title="<?php xl('Click here to choose a date','e'); ?>"></td>
		<td class="text">Next BH visit date and time&nbsp;&nbsp;&nbsp;
		<select name="sm_counsel_hr" id="sm_counsel_hr" style="width: 60px;">
		<?php NumSel($dt['sm_counsel_hr'], 7, 19, 1, '', TRUE); ?></select>
		&nbsp;:&nbsp;
		<select name="sm_counsel_mn" id="sm_counsel_mn" style="width: 60px;">
		<?php NumSel($dt['sm_counsel_mn'], 0, 55, 5, '', TRUE, '', 0, 2); ?></select>
		</td>
	</tr>
</table>
<script type="text/javascript">
Calendar.setup({inputField:"sm_next_dt", ifFormat:"<?php echo $date_img_fmt; ?>", daFormat:"<?php echo $date_img_fmt; ?>", button:"img_next_dt"});
Calendar.setup({inputField:"sm_counsel_dt", ifFormat:"<?php echo $date_img_fmt; ?>", daFormat:"<?php echo $date_img_fmt; ?>", button:"img_counsel_dt"});
</script>

<?php 
} // END OF DRAW DISPLAY
?>
