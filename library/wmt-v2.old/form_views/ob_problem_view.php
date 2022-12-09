<?php 
if(!isset($field_prefix)) $field_prefix = '';
if(!isset($portal_mode)) $portal_mode = false;
$chp_printed = PrintChapter($chp_title, $chp_printed);
?>
			<tr>
				<td class="wmtPrnLabel wmtPrnBorder1R" style="width:50%;">&nbsp;&nbsp;Problems/Plans</td>
				<td class="wmtPrnLabel">&nbsp;&nbsp;Medication List&nbsp;<span class="wmtPrnBody3">(Include Dosage)</span></td>
				<td class="wmtPrnLabel">&nbsp;Start Date</td>
				<td class="wmtPrnLabel">&nbsp;Stop Date</td>
			</tr>
<?php
$cnt = $seq = 1;
while($cnt < 13) {
	if(!isset($dt{'problem_plan'.$cnt})) $dt{'problem_plan'.$cnt} = '';
	if(!isset($dt{'meds_'.$cnt})) $dt{'meds_'.$cnt} = '';
	if(!isset($dt{'meds_'.$cnt.'_start'})) 
														$dt{'meds_'.$cnt.'_start'} = '';
	if(!isset($dt{'meds_'.$cnt.'_end'})) 
														$dt{'meds_'.$cnt.'_end'} = '';
	if($dt['problem_plan'.$cnt] || $dt['meds_'.$cnt] || 
			$dt['meds_'.$cnt.'_start'] || $dt['meds_'.$cnt.'_end']) {
?>
			<tr>
				<td class="wmtPrnBorder1R wmtPrnBody"><span class="wmtPrnLabel"><?php echo $seq; ?>.&nbsp;&nbsp;</span><?php echo $dt{'problem_plan'.$cnt}; ?></td>
				<td class="wmtPrnBody"><span class="wmtPrnLabel"><?php echo $seq; ?>.&nbsp;&nbsp;</span><?php echo $dt{'meds_'.$cnt}; ?></td>
				<td class="wmtPrnBody"><?php echo $dt{'meds_'.$cnt.'_start'}; ?></td>
				<td class="wmtPrnBody"><?php echo $dt{'meds_'.$cnt.'_end'}; ?></td>
		</tr>
<?php 
		$seq++;
	}
	$cnt++;
}
?>
