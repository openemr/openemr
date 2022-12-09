<?php 
unset($local_fields);
$local_fields = sqlListFields('form_sub_induct2');
$slice = 10;
if($frmdir != 'sub_induct2') $slice = 14;
$local_fields = array_slice($local_fields, $slice);

include(FORM_BRICKS . 'module_setup.inc.php');
include(FORM_BRICKS . 'module_loader.inc.php');

if(!isset($dt['si_form_dt'])) $dt['si_form_dt'] = '';
if($frmdir != $this_module) {
	if(!$dt['si_form_dt']) $dt['si_form_dt'] = date('Y-m-d');
}
if($dt['si_form_dt'] == '0000-00-00') $dt['si_form_dt'] = NULL;
if($dt['si_next_dt'] == '0000-00-00') $dt['si_next_dt'] = NULL;
if($dt['si_counsel_dt'] == '0000-00-00') $dt['si_counsel_dt'] = NULL;

$chp_printed = TRUE;
printChapter($module['title']);
// THIS IS SO WE CAN INCLUDE A SEPARATE DATE FOR INDUCTION WITHIN THE FORM
if($frmdir != $this_module) {
?>
  <tr>
		<td style="text-align: right;"><?php echo htmlspecialchars(oeFormatShortDate($dt['si_form_dt']), ENT_QUOTES); ?></td>
		<td>Induction Visit Date</td>
	</tr>

<?php
}
?>

	<tr>
		<td style="text-align: right; width: 220px;"><?php echo ListLook($dt{'si_q1'},'yesno'); ?></td>
		<td class="text">OUD confirmed diagnosis into MAT?</td>
	</tr>
	<tr>
		<td style="text-align: right;"><?php echo ListLook($dt{'si_q2'},'yesno'); ?></td>
		<td class="text">Has the patient signed the Consent For Treatment?</td>
	</tr>
	<tr>
		<td style="text-align: right;"><?php echo ListLook($dt{'si_q3'},'yesno'); ?></td>
		<td class="text">Is the patient and family members educated on the use of Naloxone?</td>
	</tr>
	<tr>
		<td style="text-align: right;"><?php echo ListLook($dt{'si_q9'},'yesno'); ?></td>
		<td class="text">Does the patient have Naloxone prescribed?</td>
	</tr>
	<tr>
		<td style="text-align: right;"><?php echo ListLook($dt{'si_q4'},'Sub_Med'); ?></td>
		<td class="text">Which medication will the patient begin treatment with</td>
	</tr>
	<tr>
		<td style="text-align: right;"><?php echo ListLook($dt{'si_q5'},'Sub_Dose'); ?></td>
		<td><span class="text">Dosage&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
		<?php echo htmlspecialchars($dt{'si_q6'}, ENT_QUOTES); ?>&nbsp;&nbsp;
		<span class="text">Pill Count</span></td>
	</tr>
	<tr>
		<td style="text-align: right;"><?php echo ListLook($dt{'si_q7'},'Pos_Neg'); ?></td>
		<td class="text">Drug screen results</td>
	</tr>
	<tr>
		<td style="text-align: right;"><?php echo htmlspecialchars($dt{'si_q8'}, ENT_QUOTES); ?></td>
		<td class="text">Readiness for change</td>
	</tr>
  <tr>
		<td style="text-align: right;"><?php echo htmlspecialchars(oeFormatShortDate($dt['si_next_dt']), ENT_QUOTES); ?></td>
		<td class="text">Next MAT visit date and time&nbsp;&nbsp;&nbsp;
		<?php echo htmlspecialchars($dt['si_next_hr'], ENT_QUOTES); ?>
		&nbsp;:&nbsp;
		<?php echo htmlspecialchars($dt['si_next_mn'], ENT_QUOTES); ?>
		</td>
	</tr>
  <tr>
		<td style="text-align: right;"><?php echo htmlspecialchars(oeFormatShortDate($dt['si_counsel_dt']), ENT_QUOTES); ?></td>
		<td class="text">Next BH visit date and time&nbsp;&nbsp;&nbsp;
		<?php echo htmlspecialchars($dt['si_counsel_hr'], ENT_QUOTES); ?>
		&nbsp;:&nbsp;
		<?php echo htmlspecialchars($dt['si_counsel_mn'], ENT_QUOTES); ?>
		</td>
	</tr>
