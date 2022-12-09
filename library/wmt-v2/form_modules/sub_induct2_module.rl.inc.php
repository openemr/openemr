<?php 
unset($local_fields);
$local_fields = sqlListFields('form_sub_induct2');
$slice = 10;
if($frmdir != 'sub_induct2') $slice = 14;
$local_fields = array_slice($local_fields, $slice);

include(FORM_BRICKS . 'module_setup.inc.php');
include(FORM_BRICKS . 'module_loader.inc.php');

if($draw_display) {
?>
<table width="100%" border="0" cellpadding="2" cellspacing="0">
<?php
// THIS IS SO WE CAN INCLUDE A SEPARATE DATE FOR INDUCTION WITHIN THE FORM
if($frmdir != $this_module) {
?>
  <tr>
		<td><input name="si_form_dt" id="si_form_dt" style="width: 80px;" type="text" value="<?php echo htmlspecialchars(oeFormatShortDate($dt['si_form_dt']), ENT_QUOTES); ?>" title="Enter In <?php echo $date_title_fmt; ?> Format" />&nbsp;
			<img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="24" height="22" id="img_form_dt" border="0" alt="[?]" style="cursor:pointer; vertical-align: middle;" tabindex="-1" title="<?php xl('Click here to choose a date','e'); ?>">&mbsp;&nbsp;Induction Visit Date</td>
	</tr>

<script type="text/javascript">
Calendar.setup({inputField:"si_form_dt", ifFormat:"<?php echo $date_img_fmt; ?>", daFormat:"<?php echo $date_img_fmt; ?>", button:"img_next_dt"});
</script>
<?php
}
?>

	<tr>
		<td colspan="2">Diagnosis associated with induction into MAT:</td>
		<td colspan="2"><input name="sub_induct2_id" id="sub_induct2_id" type="hidden" value="<?php echo $dt['sub_induct2_id']; ?>" />
		<label><input name="tmp_si_diag_1" id="tmp_si_diag_1" type="checkbox" value="OUD" <?php echo (strpos($dt['si_q1'], 'OUD') !== FALSE) ? 'checked="checked"' : ''; ?> />&nbsp;OUD</label>
		<label><input name="tmp_si_diag_2" id="tmp_si_diag_2" type="checkbox" value="AUD" <?php echo (strpos($dt['si_q1'], 'AUD') !== FALSE) ? 'checked="checked"' : ''; ?> />&nbsp;AUD</label>
		<label><input name="tmp_si_diag_3" id="tmp_si_diag_3" type="checkbox" value="SUD" <?php echo (strpos($dt['si_q1'], 'SUD') !== FALSE) ? 'checked="checked"' : ''; ?> />&nbsp;SUD</label></td>
	</tr>
	<tr>
		<td colspan="2" class="clickable text" onclick="toggleThroughSelect('si_q2');">Has the patient reviewed and signed the Treatment Agreement:</td>
		<td colspan="2"><select name="si_q2" id="si_q2"><?php ListSel($dt{'si_q2'},'yesno'); ?></select></td>
	</tr>
	<tr>
		<td colspan="2" class="clickable text" onclick="toggleThroughSelect('si_q3');">Was the patient educated on the importance of properly storing the medications assigned (safety assessment):</td>
		<td colspan="2"><select name="si_q3" id="si_q3"><?php ListSel($dt{'si_q3'},'yesno'); ?></select></td>
	</tr>
	<tr>
		<td colspan="2" class="clickable text" onclick="toggleThroughSelect('si_q4');">Which medication will the patient begin treatment with:</td>
		<td colspan="2"><select name="si_q4" id="si_q4"><?php ListSel($dt{'si_q4'},'Sub_Med'); ?></select></td>
	</tr>
	<tr>
		<td class="clickable text" onclick="toggleThroughSelect('si_q5');">Dosage:</td>
		<td><select name="si_q5" id="si_q5"><?php ListSel($dt{'si_q5'},'Sub_Dose'); ?></select></td>
		<td class="clickable text" onclick="toggleThroughSelect('si_q6');">Pill Count:</td>
		<td><select name="si_q6" id="si_q6"><?php NumSel($dt{'si_q6'}, 1, 100, 1, '', TRUE); ?></select></td>
	</tr>
	<tr>
		<td colspan="2" class="clickable text" onclick="toggleThroughSelect('si_q7');">Drug screen results:</td>
		<td colspan="2"><select name="si_q7" id="si_q7"><?php ListSel($dt{'si_q7'},'Pos_Neg'); ?></select></td>
	</tr>
	<tr>
		<td colspan="2" class="clickable text" onclick="toggleThroughSelect('si_q8');">Safety:</td>
		<td colspan="2"><select name="si_q8" id="si_q8"><?php ListSel($dt{'si_q8'},'yesno'); ?></select></td>
	</tr>
  <tr>
		<td>Next Visit Date:&nbsp;&nbsp;
		<input name="si_next_dt" id="si_next_dt" style="width: 80px;" type="text" value="<?php echo htmlspecialchars(oeFormatShortDate($dt['si_next_dt']), ENT_QUOTES); ?>" title="Enter In <?php echo $date_title_fmt; ?> Format" />&nbsp;
			<img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="24" height="22" id="img_next_dt" border="0" alt="[?]" style="cursor:pointer; vertical-align: middle;" tabindex="-1" title="<?php xl('Click here to choose a date','e'); ?>"></td>
		<td>Time:&nbsp;&nbsp;
		<select name="si_next_hr" id="si_next_hr" style="width: 60px;">
		<?php NumSel($dt['si_next_hr'], 7, 19, 1, '', TRUE); ?></select></td>
		&nbsp;:&nbsp;
		<select name="si_next_mn" id="si_next_mn" style="width: 60px;">
		<?php NumSel($dt['si_next_mn'], 0, 55, 5, '', TRUE, '', 0, 2); ?></select></td>
	</tr>
</table>
<script type="text/javascript">
Calendar.setup({inputField:"si_next_dt", ifFormat:"<?php echo $date_img_fmt; ?>", daFormat:"<?php echo $date_img_fmt; ?>", button:"img_next_dt"});
</script>

<?php 
} // END OF DRAW DISPLAY
?>
