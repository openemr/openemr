<?php 
unset($local_fields);
$local_fields = sqlListFields('form_sub_maint2');
$slice = 10;
if($frmdir != 'sub_indect2') $slice = 14;
$local_fields = array_slice($local_fields, $slice);

include(FORM_BRICKS . 'module_setup.inc.php');
include(FORM_BRICKS . 'module_loader.inc.php');

if(!isset($dt['sm_form_dt'])) $dt['sm_form_dt']= '';
if($frmdir != $this_module) {
	if(!$dt['sm_form_dt']) $dt['sm_form_dt'] = date('Y-m-d');
}
if($dt['sm_form_dt'] == '0000-00-00') $dt['sm_form_dt'] = NULL;
if($dt['sm_next_dt'] == '0000-00-00') $dt['sm_next_dt'] = NULL;
if($dt['sm_counsel_dt'] == '0000-00-00') $dt['sm_counsel_dt'] = NULL;

$chp_printed = TRUE;
printChapter($module['title']);
// THIS IS SO WE CAN INCLUDE A SEPARATE DATE FOR MAINTENANCE WITHIN THE FORM
if($frmdir != $this_module) {
?>
  <tr>
		<td style="text-align: right;"><?php echo htmlspecialchars(oeFormatShortDate($dt['sm_form_dt']), ENT_QUOTES); ?></td>
		<td>Induction Visit Date</td>
	</tr>

<?php
}
?>

	<tr>
		<td style="text-align: right; width: 220px;"><?php echo ListLook($dt{'sm_q1'},'yesno'); ?></td>
		<td class="text">Was the Prescription Drug Monitoring Program reviewed? (PDMP)</td>
	</tr>
	<tr>
		<td style="text-align: right;"><?php echo ListLook($dt{'sm_q5'},'Sub_Dose'); ?></td>
		<td class="text">Dosage&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<?php echo htmlspecialchars($dt{'sm_q6'}, ENT_QUOTES); ?>&nbsp;&nbsp;Pill Count</td>
	</tr>
	<tr>
		<td style="text-align: right;"><?php echo ListLook($dt{'sm_q7'},'Pos_Neg'); ?></td>
		<td class="text">Drug screen results</td>
	</tr>
	<tr>
		<td style="text-align: right;"><?php echo ListLook($dt{'sm_q3'},'yesno'); ?></td>
		<td class="text">Is the patient and family members educated on the use of Naloxone?</td>
	</tr>
	<tr>
		<td style="text-align: right;"><?php echo ListLook($dt{'sm_q2'},'yesno'); ?></td>
		<td class="text">Does the patient have Naloxone prescribed?</td>
	</tr>
	<tr>
		<td style="text-align: right;"><?php echo ListLook($dt{'sm_q8'},'Sub_Pharmacies'); ?></td>
		<td class="text">Pharmacy&nbsp;&nbsp;<?php echo htmlspecialchars($dt['sm_q8_nt'], ENT_QUOTES); ?></td>
	</tr>
	<tr>
		<td style="text-align: right;"><?php echo ListLook($dt{'sm_q10'},'yesno'); ?></td>
		<td class="text">Have you used drugs since your last visit?</td>
	</tr>
	<tr>
		<td style="text-align: right;"><?php echo ListLook($dt{'sm_q11'},'yesno'); ?></td>
		<td class="text">Have you taken your medications as prescribed?</td>
	</tr>
	<tr>
		<td style="text-align: right;"><?php echo ListLook($dt{'sm_q12'},'yesno'); ?></td>
		<td class="text">Is your housing situation stable?</td>
	</tr>
	<tr>
		<td style="text-align: right;"><?php echo ListLook($dt{'sm_q13'},'yesno'); ?></td>
		<td class="text">Are you in a support group outside of this program?</td>
	</tr>
	<tr>
		<td style="text-align: right;"><?php echo ListLook($dt{'sm_q14'},'yesno'); ?></td>
		<td class="text">Have you been arrested or had any other illegal involvement since your last visit?</td>
	</tr>
	<tr>
		<td style="text-align: right;"><?php echo ListLook($dt{'sm_q15'},'Employment_Status'); ?></td>
		<td class="text">What is your employment status?</td>
	</tr>
	<tr>
		<td style="text-align: right;"><?php echo htmlspecialchars($dt{'sm_q9'}, ENT_QUOTES); ?></td>
		<td class="text">Readiness for Change</td>
	</tr>
	<tr>
		<td class="text" style="text-align: right;">Goals:</td>
		<td><?php echo htmlspecialchars($dt{'sm_goal_nt'}, ENT_QUOTES); ?></td>
	</tr>
	<tr>
		<td class="text" style="text-align: right;">Possible barriers:</td>
		<td><?php echo htmlspecialchars($dt{'sm_barrier_nt'}, ENT_QUOTES); ?></td>
	</tr>
  <tr>
		<td style="text-align: right;"><?php echo htmlspecialchars(oeFormatShortDate($dt['sm_next_dt']), ENT_QUOTES); ?></td>
		<td class="text">Next MAT visit date and time&nbsp;&nbsp;&nbsp;
		<?php echo htmlspecialchars($dt['sm_next_hr'], ENT_QUOTES); ?>
		&nbsp;:&nbsp;
		<?php echo htmlspecialchars($dt['sm_next_mn'], ENT_QUOTES); ?>
		</td>
	</tr>
  <tr>
		<td style="text-align: right;"><?php echo htmlspecialchars(oeFormatShortDate($dt['sm_counsel_dt']), ENT_QUOTES); ?></td>
		<td class="text">Next BH visit date and time&nbsp;&nbsp;&nbsp;
		<?php echo htmlspecialchars($dt['sm_counsel_hr'], ENT_QUOTES); ?>
		&nbsp;:&nbsp;
		<?php echo htmlspecialchars($dt['sm_counsel_mn'], ENT_QUOTES); ?>
		</td>
	</tr>
