<?php 
unset($local_fields);
$local_fields = sqlListFields('form_sub_induct2');
$slice = 10;
if($frmdir != 'sub_induct2') $slice = 14;
$local_fields = array_slice($local_fields, $slice);

include(FORM_BRICKS . 'module_setup.inc.php');
include(FORM_BRICKS . 'module_loader.inc.php');

if($frmdir != $this_module) {
	if(!isset($dt['si_form_dt'])) $dt['si_form_dt'] = '';
	if($form_mode == 'new') {
		if(!$dt['si_form_dt']) $dt['si_form_dt'] = date('Y-m-d');
	} else {
		$dt['si_form_dt'] = $md['form_dt'];
	}
	if($dt['si_form_dt'] == '0000-00-00') $dt['si_form_dt'] = NULL;
}
if($dt['si_next_dt'] == '0000-00-00') $dt['si_next_dt'] = NULL;
if($dt['si_counsel_dt'] == '0000-00-00') $dt['si_counsel_dt'] = NULL;

if($draw_display) {
?>
<table width="100%" border="0" cellpadding="2" cellspacing="0">
<?php
// THIS IS SO WE CAN INCLUDE A SEPARATE DATE FOR INDUCTION WITHIN THE FORM
if($frmdir != $this_module) {
?>
  <tr>
		<td style="text-align: right;"><input name="si_form_dt" id="si_form_dt" style="width: 80px;" type="text" value="<?php echo htmlspecialchars(oeFormatShortDate($dt['si_form_dt']), ENT_QUOTES); ?>" title="Enter In <?php echo $date_title_fmt; ?> Format" />
			<img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="24" height="22" id="img_si_form_dt" border="0" alt="[?]" style="cursor:pointer; vertical-align: middle;" tabindex="-1" title="<?php xl('Click here to choose a date','e'); ?>"></td>
		<td>Induction Visit Date</td>
	</tr>

<script type="text/javascript">
Calendar.setup({inputField:"si_form_dt", ifFormat:"<?php echo $date_img_fmt; ?>", daFormat:"<?php echo $date_img_fmt; ?>", button:"img_si_form_dt"});
</script>
<?php
}
?>

	<tr>
		<td style="text-align: right; width: 220px;"><select name="si_q1" id="si_q1"><?php ListSel($dt{'si_q1'},'yesno'); ?></select></td>
		<td><span class="clickable text" onclick="toggleThroughSelect('si_q1');">OUD confirmed diagnosis into MAT?</span></td>
	</tr>
	<tr>
		<td style="text-align: right;"><select name="si_q2" id="si_q2"><?php ListSel($dt{'si_q2'},'yesno'); ?></select></td>
		<td><span class="clickable text" onclick="toggleThroughSelect('si_q2');">Has the patient signed the Consent For Treatment?</span></td>
	</tr>
	<tr>
		<td style="text-align: right;"><select name="si_q3" id="si_q3"><?php ListSel($dt{'si_q3'},'yesno'); ?></select></td>
		<td><span class="clickable text" onclick="toggleThroughSelect('si_q3');">Is the patient and family members educated on the use of Naloxone?</span></td>
	</tr>
	<tr>
		<td style="text-align: right;"><select name="si_q9" id="si_q9"><?php ListSel($dt{'si_q9'},'yesno'); ?></select></td>
		<td><span class="clickable text" onclick="toggleThroughSelect('si_q9');">Does the patient have Naloxone prescribed?</span></td>
	</tr>
	<tr>
		<td style="text-align: right;"><select name="si_q4" id="si_q4"><?php ListSel($dt{'si_q4'},'Sub_Med'); ?></select></td>
		<td><span class="clickable text" onclick="toggleThroughSelect('si_q4');">Which medication will the patient begin treatment with</span></td>
	</tr>
	<tr>
		<td style="text-align: right;"><select name="si_q5" id="si_q5"><?php ListSel($dt{'si_q5'},'Sub_Dose'); ?></select></td>
		<td><span class="clickable text" onclick="toggleThroughSelect('si_q5');">Dosage&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
		<select name="si_q6" id="si_q6"><?php NumSel($dt{'si_q6'}, 1, 100, 1, '', TRUE); ?></select>&nbsp;&nbsp;
		<span class="clickable text" onclick="toggleThroughSelect('si_q6');">Pill Count</span></td>
	</tr>
	<tr>
		<td style="text-align: right;"><select name="si_q7" id="si_q7"><?php ListSel($dt{'si_q7'},'Pos_Neg'); ?></select></td>
		<td><span class="clickable text" onclick="toggleThroughSelect('si_q7');">Drug screen results</span></td>
	</tr>
	<tr>
		<td style="text-align: right;"><select name="si_q8" id="si_q8"><?php NumSel($dt{'si_q8'}, 1, 10, 1, '', TRUE); ?></select></td>
		<td><span class="clickable text" onclick="toggleThroughSelect('si_q8');">Readiness for change</span></td>
	</tr>
  <tr>
		<td style="text-align: right;"><input name="si_next_dt" id="si_next_dt" style="width: 80px;" type="text" value="<?php echo htmlspecialchars(oeFormatShortDate($dt['si_next_dt']), ENT_QUOTES); ?>" title="Enter In <?php echo $date_title_fmt; ?> Format" />&nbsp;
			<img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="24" height="22" id="img_next_dt" border="0" alt="[?]" style="cursor:pointer; vertical-align: middle;" tabindex="-1" title="<?php xl('Click here to choose a date','e'); ?>"></td>
		<td class="text">Next MAT visit date and time&nbsp;&nbsp;&nbsp;
		<select name="si_next_hr" id="si_next_hr" style="width: 60px;">
		<?php NumSel($dt['si_next_hr'], 7, 19, 1, '', TRUE); ?></select>
		&nbsp;:&nbsp;
		<select name="si_next_mn" id="si_next_mn" style="width: 60px;">
		<?php NumSel($dt['si_next_mn'], 0, 55, 5, '', TRUE, '', 0, 2); ?></select>
		</td>
	</tr>
  <tr>
		<td style="text-align: right;"><input name="si_counsel_dt" id="si_counsel_dt" style="width: 80px;" type="text" value="<?php echo htmlspecialchars(oeFormatShortDate($dt['si_counsel_dt']), ENT_QUOTES); ?>" title="Enter In <?php echo $date_title_fmt; ?> Format" />&nbsp;
			<img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="24" height="22" id="img_counsel_dt" border="0" alt="[?]" style="cursor:pointer; vertical-align: middle;" tabindex="-1" title="<?php xl('Click here to choose a date','e'); ?>"></td>
		<td class="text">Next BH visit date and time&nbsp;&nbsp;&nbsp;
		<select name="si_counsel_hr" id="si_counsel_hr" style="width: 60px;">
		<?php NumSel($dt['si_counsel_hr'], 7, 19, 1, '', TRUE); ?></select>
		&nbsp;:&nbsp;
		<select name="si_counsel_mn" id="si_counsel_mn" style="width: 60px;">
		<?php NumSel($dt['si_counsel_mn'], 0, 55, 5, '', TRUE, '', 0, 2); ?></select>
		</td>
	</tr>
</table>
<script type="text/javascript">
Calendar.setup({inputField:"si_next_dt", ifFormat:"<?php echo $date_img_fmt; ?>", daFormat:"<?php echo $date_img_fmt; ?>", button:"img_next_dt"});
Calendar.setup({inputField:"si_counsel_dt", ifFormat:"<?php echo $date_img_fmt; ?>", daFormat:"<?php echo $date_img_fmt; ?>", button:"img_counsel_dt"});
</script>

<?php 
} // END OF DRAW DISPLAY
?>
