<?php 
if(!isset($field_prefx)) $field_prefix = '';
$desc = '';
if($dt{$field_prefix.'cows_total'} && $dt{$field_prefix.'cows_total'} < 5) $desc =  'NO ACTIVE WITHDRAWAL';
if($dt{$field_prefix.'cows_total'} >= 5 && $dt{$field_prefix.'cows_total'} < 13) $desc = 'MILD WITHDRAWAL';
if($dt{$field_prefix.'cows_total'} >= 13 && $dt{$field_prefix.'cows_total'} < 25) $desc = 'MODERATE WITHDRAWAL';
if($dt{$field_prefix.'cows_total'} >= 25 && $dt{$field_prefix.'cows_total'} < 37) $desc  = 'MODERATELY SEVERE WITHDRAWAL';
if($dt{$field_prefix.'cows_total'} >= 37) $desc = 'SEVERE WITHDRAWAL';
?>
<table width="100%" border="0" cellspacing="0" cellpadding="5">
	<tr>
		<td class="wmtPrnBody"><b>Resting Pulse Rate (BPM)</b> Measure pulse rate after patient is sitting or lying down for 1 minute</td>
		<td class="wmtPrnBody wmtPrnR"><?php echo ListLook($dt{$field_prefix.'cows_q1'},'cows_q1_choices'); ?></td>
	</tr>
	<tr>
		<td class="wmtPrnBody"><b>Sweating</b> Sweating not accounted for by room temperature or patient activity over the last 0.5 hours</td>
		<td class="wmtPrnBody wmtPrnR"><?php echo ListLook($dt{$field_prefix.'cows_q2'},'cows_q2_choices'); ?></td>
	</tr>
	<tr>
		<td class="wmtPrnBody"><b>Restlessness observation during assessment</b></td>
		<td class="wmtPrnBody wmtPrnR"><?php echo ListLook($dt{$field_prefix.'cows_q3'},'cows_q3_choices'); ?></td>
	</tr>
	<tr>
		<td class="wmtPrnBody"><b>Pupil Size</b></td>
		<td class="wmtPrnBody wmtPrnR"><?php echo ListLook($dt{$field_prefix.'cows_q4'},'cows_q4_choices'); ?></td>
	</tr>
	<tr>
		<td class="wmtPrnBody"><b>Bone or joint aches</b> If patient was having pain previously, only the additional component attributed to opiate withdrawal is scored</td>
		<td class="wmtPrnBody wmtPrnR"><?php echo ListLook($dt{$field_prefix.'cows_q5'},'cows_q5_choices'); ?></td>
	</tr>
	<tr>
		<td class="wmtPrnBody"><b>Runny nose or tearing</b> Not accounted for by cold symptoms or allergies</td>
		<td class="wmtPrnBody wmtPrnR"><?php echo ListLook($dt{$field_prefix.'cows_q6'},'cows_q6_choices'); ?></td>
	</tr>
	<tr>
		<td class="wmtPrnBody"><b>GI Upset</b> Over the last 0.5 hours</td>
		<td class="wmtPrnBody wmtPrnR"><?php echo ListLook($dt{$field_prefix.'cows_q7'},'cows_q7_choices'); ?></td>
	</tr>
	<tr>
		<td class="wmtPrnBody"><b>Tremor observation of outstretched hands</b></td>
		<td class="wmtPrnBody wmtPrnR"><?php echo ListLook($dt{$field_prefix.'cows_q8'},'cows_q8_choices'); ?></td>
	</tr>
	<tr>
		<td class="wmtPrnBody"><b>Yawning observation during assessment</b></td>
		<td class="wmtPrnBody wmtPrnR"><?php echo ListLook($dt{$field_prefix.'cows_q9'},'cows_q9_choices'); ?></td>
	</tr>
	<tr>
		<td class="wmtPrnBody"><b>Anxiety or irritability</b></td>
		<td class="wmtPrnBody wmtPrnR"><?php echo ListLook($dt{$field_prefix.'cows_q10'},'cows_q10_choices'); ?></td>
	</tr>
	<tr>
		<td class="wmtPrnBody"><b>Gooseflesh skin</b></td>
		<td class="wmtPrnBody wmtPrnR"><?php echo ListLook($dt{$field_prefix.'cows_q11'},'cows_q11_choices'); ?></td>
	</tr>
	<tr>
		<td class="wmtPrnBody"><b>Questionnaire Score:&nbsp;&nbsp;</b><span id="cows_description" style="float: right; margin-right: 12px;"><?php echo $desc; ?></span></td>
		<td class="wmtPrnBody wmtPrnB wmtPrnR"><?php echo htmlspecialchars($dt{$field_prefix.'cows_total'}, ENT_QUOTES, '', FALSE); ?></td>
	</tr>
