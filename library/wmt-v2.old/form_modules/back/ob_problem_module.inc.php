<?php 
if(!isset($field_prefix)) $field_prefix = '';
if(!isset($portal_mode)) $portal_mode = false;
?>
		<table width="100%" border="0" cellspacing="0" cellpadding="2">
			<tr>
				<td class="wmtLabel wmtBorder1R" style="width:50%;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Problems/Plans</td>
				<td class="wmtLabel">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Medication List&nbsp;<span class="wmtBody3">(Include Dosage)</span></td>
				<td class="wmtLabel" style="width: 110px;">&nbsp;Start Date</td>
				<td class="wmtLabel" style="width: 110px;">&nbsp;Stop Date</td>
			</tr>
<?php
$cnt = 1;
while($cnt < 13) {
	if(!isset($dt{'problem_plan'.$cnt})) $dt{'problem_plan'.$cnt} = '';
	if(!isset($dt{'medications_'.$cnt})) $dt{'medications_'.$cnt} = '';
	if(!isset($dt{'medications_'.$cnt.'ications_start'})) 
															$dt{'medications_'.$cnt.'_start'} = '';
	if(!isset($dt{'medications_'.$cnt.'_end'})) 
															$dt{'medications_'.$cnt.'_end'} = '';
?>
			<tr>
				<td class="wmtBorder1R"><span class="wmtBody"><?php echo $cnt; ?>.&nbsp;&nbsp;</span><div style="width: 95%; float: right;"><input name="<?php echo 'problem_plan'.$cnt; ?>" id="<?php echo 'problem_plan'.$cnt; ?>" type="text" class="wmtFullInput" value="<?php echo htmlspecialchars($dt{'problem_plan'.$cnt}, ENT_QUOTES, '', FALSE); ?>" /></div></td>
				<td><span class="wmtBody"><?php echo $cnt; ?>.&nbsp;&nbsp;</span><div style="width: 90%; float: right;"><input name="<?php echo 'medications_'.$cnt; ?>" id="<?php echo 'medications_'.$cnt; ?>" type="text" class="wmtFullInput" value="<?php echo htmlspecialchars($dt{'medications_'.$cnt}, ENT_QUOTES, '', FALSE); ?>" /></div></td>
				<td><input name="<?php echo 'medications_'.$cnt.'_start'; ?>" id="<?php echo 'medications_'.$cnt.'_start'; ?>" type="text" class="wmtFullInput" value="<?php echo htmlspecialchars($dt{'medications_'.$cnt.'_start'}, ENT_QUOTES, '', FALSE); ?>" /></td>
				<td><input name="<?php echo 'medications_'.$cnt.'_end'; ?>" id="<?php echo 'medications_'.$cnt.'_end'; ?>" type="text" class="wmtFullInput" value="<?php echo htmlspecialchars($dt{'medications_'.$cnt.'_end'}, ENT_QUOTES, '', FALSE); ?>" /></td>
			</tr>
<?php 
	$cnt++;
}
?>
		</table>
