<?php 
if(!isset($field_prefx)) $field_prefix = '';
$this_module = 'opiate_withdraw';
$this_table = 'form_opiate_withdraw';
include(FORM_BRICKS . 'module_loader.inc.php');
$desc = '';
if($dt{$field_prefix.'cows_total'} && $dt{$field_prefix.'cows_total'} < 5) $desc =  'NO ACTIVE WITHDRAWAL';
if($dt{$field_prefix.'cows_total'} >= 5 && $dt{$field_prefix.'cows_total'} < 13) $desc = 'MILD WITHDRAWAL';
if($dt{$field_prefix.'cows_total'} >= 13 && $dt{$field_prefix.'cows_total'} < 25) $desc = 'MODERATE WITHDRAWAL';
if($dt{$field_prefix.'cows_total'} >= 25 && $dt{$field_prefix.'cows_total'} < 37) $desc  = 'MODERATELY SEVERE WITHDRAWAL';
if($dt{$field_prefix.'cows_total'} >= 37) $desc = 'SEVERE WITHDRAWAL';
$chp_printed = printChapter($chp_title, FALSE);
?>
	<tr>
		<td class="text"><b>Resting Pulse Rate (BPM)</b> Measure pulse rate after patient is sitting or lying down for 1 minute</td>
		<td class="wmtRight"><?php echo ListLook($dt{$field_prefix.'cows_q1'},'cows_q1_choices'); ?></td>
	</tr>
	<tr>
		<td class="text"><b>Sweating</b> Sweating not accounted for by room temperature or patient activity over the last 0.5 hours</td>
		<td class="wmtRight"><?php echo ListLook($dt{$field_prefix.'cows_q2'},'cows_q2_choices'); ?></td>
	</tr>
	<tr>
		<td class="text"><b>Restlessness observation during assessment</b></td>
		<td class="wmtRight"><?php echo ListLook($dt{$field_prefix.'cows_q3'},'cows_q3_choices'); ?></td>
	</tr>
	<tr>
		<td class="text"><b>Pupil Size</b></td>
		<td class="wmtRight"><?php echo ListLook($dt{$field_prefix.'cows_q4'},'cows_q4_choices'); ?></td>
	</tr>
	<tr>
		<td class="text"><b>Bone or joint aches</b> If patient was having pain previously, only the additional component attributed to opiate withdrawal is scored</td>
		<td class="wmtRight"><?php echo ListLook($dt{$field_prefix.'cows_q5'},'cows_q5_choices'); ?></td>
	</tr>
	<tr>
		<td class="text"><b>Runny nose or tearing</b> Not accounted for by cold symptoms or allergies</td>
		<td class="wmtRight"><?php echo ListLook($dt{$field_prefix.'cows_q6'},'cows_q6_choices'); ?></td>
	</tr>
	<tr>
		<td class="text"><b>GI Upset</b> Over the last 0.5 hours</td>
		<td class="wmtRight"><?php echo ListLook($dt{$field_prefix.'cows_q7'},'cows_q7_choices'); ?></td>
	</tr>
	<tr>
		<td class="text"><b>Tremor observation of outstretched hands</b></td>
		<td class="wmtRight"><?php echo ListLook($dt{$field_prefix.'cows_q8'},'cows_q8_choices'); ?></td>
	</tr>
	<tr>
		<td class="text"><b>Yawning observation during assessment</b></td>
		<td class="wmtRight"><?php echo ListLook($dt{$field_prefix.'cows_q9'},'cows_q9_choices'); ?></td>
	</tr>
	<tr>
		<td class="text"><b>Anxiety or irritability</b></td>
		<td class="wmtRight"><?php echo ListLook($dt{$field_prefix.'cows_q10'},'cows_q10_choices'); ?></td>
	</tr>
	<tr>
		<td class="text"><b>Gooseflesh skin</b></td>
		<td class="wmtRight"><?php echo ListLook($dt{$field_prefix.'cows_q11'},'cows_q11_choices'); ?></td>
	</tr>
	<tr>
		<td class="text"><b>Questionnaire Score:&nbsp;&nbsp;</b><span id="cows_description" style="float: right; margin-right: 12px;"><?php echo $desc; ?></span></td>
		<td class="text  wmtRight"><?php echo htmlspecialchars($dt{$field_prefix.'cows_total'}, ENT_QUOTES); ?></td>
	</tr>
