<?php
include_once($GLOBALS['srcdir'].'/wmt/wmt.class.php');
/* RETRIEVE MOST RECENT HISTORY DATA IF NOT SET BY A PRIOR MODULE */
if(!isset($hx_data) || !is_object($hx_data)) {
	$old = sqlQuery('SELECT `id`, `date` FROM `form_psyc_history` WHERE ' .
		'`pid` = ? ORDER BY `date` DESC', array($pid));
	if(!isset($old{'id'})) $old{'id'} = '';
	$hx_data = new wmtForm('psyc_history', $old{'id'});
	$pat_data = wmtPatient::getPidPatient($pid);
	if (! $hx_data->education) $hx_data->education = $pat_data->education;
	if (! $hx_data->employment) $hx_data->employment = $pat_data->employment;
}
?>
<table width="100%"	border="0" cellspacing="2" cellpadding="0">
	<tr>
		<td>
			<table width="100%" border="0" cellspacing="0" cellpadding="2">
				<tr>
					<td class="wmtLabel" style="width:180px">
						Level of Education:
					</td>
					<td class="wmtLabel" style="width:200px">
						<select name="hx_education">
							<?php ListSel($hx_data->education, 'PSYC_Education')?>
						</select>
					</td>
					<td class="wmtLabel" style="width:100px">
						Area of Study:
					</td>
					<td class="wmtLabel">
						<input name="hx_study" type="text" class="wmtFullInput" value="<?php echo $hx_data->study; ?>" />
					</td>
				</tr>
				
				<tr>
					<td class="wmtLabel">
						Barriers to Learning:
					</td>
					<td class="wmtRadio">
						<input name="hx_barrier_flag" class="wmtRadio" type="radio" <?php echo ((!$hx_data->barrier_flag)? ' checked':''); ?> value="0" />No
						<input name="hx_barrier_flag" class="wmtRadio" type="radio" style="margin-left:10px" <?php echo (($hx_data->barrier_flag)? ' checked':''); ?> value="1" />Yes
					</td>
					<td class="wmtLabel">
						Explain:
					</td>
					<td class="wmtLabel">
						<input name="hx_barrier_notes" type="text" class="wmtFullInput" value="<?php echo $hx_data->barrier_notes; ?>" />
					</td>
				</tr>
				<tr>
					<td class="wmtLabel">
						Desire to Continue Education:
					</td>
					<td class="wmtRadio">
						<input name="hx_continue_flag" class="wmtRadio" type="radio" <?php echo ((!$hx_data->continue_flag)? ' checked':''); ?> value="0" />No
						<input name="hx_continue_flag" class="wmtRadio" type="radio" style="margin-left:10px" <?php echo (($hx_data->continue_flag)? ' checked':''); ?> value="1" />Yes
						<input name="hx_continue_flag" class="wmtRadio" type="radio" style="margin-left:10px" <?php echo (($hx_data->continue_flag)? ' checked':''); ?> value="2" />Not Sure
					</td>
				</tr>	
			</table>
			
			<hr style="border-color:#eee" />
			
			<table width="100%" border="0" cellspacing="0" cellpadding="2">
				<tr>
					<td class="wmtLabel" style="width:180px">
						Current Employment:
					</td>
					<td class="wmtLabel" style="width:200px">
						<select name="hx_employment">
							<?php ListSel($hx_data->employment, 'PSYC_Employment')?>
						</select>
					</td>
					<td class="wmtLabel" style="width:120px">
						Number of Jobs:
					</td>
					<td class="wmtLabel" style="width:100px">
						<input name="hx_jobs" type="text" class="wmtFullInput" style="width:30px" value="<?php echo $hx_data->jobs; ?>" />
					</td>
					<td class="wmtLabel" style="width:100px">
						Trade or Skill:
					</td>
					<td class="wmtLabel">
						<input name="hx_trade" type="text" class="wmtFullInput" value="<?php echo $hx_data->trade; ?>" />
					</td>
				</tr>
				
				<tr>
					<td class="wmtLabel">
						Current / Last Employer:
					</td>
					<td class="wmtLabel">
						<input name="hx_employer" type="text" class="wmtFullInput" style="width:180px" value="<?php echo $hx_data->employer; ?>" />
					</td>
					<td class="wmtLabel">
						Job Duration:
					</td>
					<td class="wmtLabel">
						<input name="hx_duration" type="text" class="wmtFullInput" style="width:80px" value="<?php echo $hx_data->duration; ?>" />
					</td>
					<td class="wmtLabel">
						Position:
					</td>
					<td class="wmtLabel">
						<input name="hx_position" type="text" class="wmtFullInput" value="<?php echo $hx_data->position; ?>" />
					</td>
				</tr>
			</table>
			
			<hr style="border-color:#eee" />
											
			<table width="100%" border="0" cellspacing="0" cellpadding="2">
				<tr>
					<td class="wmtLabel" style="width:180px">
						Military Service:
					</td>
					<td class="wmtRadio" style="width:120px">
						<input name="hx_military_flag" class="wmtRadio" type="radio" <?php echo ((!$hx_data->military_flag)? ' checked':''); ?> value="0" />No
						<input name="hx_military_flag" class="wmtRadio" type="radio" style="margin-left:10px" <?php echo (($hx_data->military_flag)? ' checked':''); ?> value="1" />Yes
					</td>
					<td class="wmtLabel" style="width:90px">
						Years Served:
					</td>
					<td class="wmtLabel" style="width:60px">
						<input name="hx_served" type="text" class="wmtInput" style="width:30px" value="<?php echo $hx_data->served; ?>" />
					</td>
					<td class="wmtLabel" style="width:50px">
						Branch:
					</td>
					<td class="wmtLabel" style="width:150px">
						<input name="hx_branch" type="text" class="wmtInput" style="width:120px" value="<?php echo $hx_data->branch; ?>" />
					</td>
					<td class="wmtLabel" style="width:100px">
						Discharge Type:
					</td>
					<td class="wmtLabel">
						<input name="hx_discharge" type="text" class="wmtFullInput" value="<?php echo $hx_data->discharge; ?>" />
					</td>
				</tr>
				
				<tr>
					<td class="wmtRadio" style="padding-left:25px;padding-top:5px" colspan="8">
						If client served in the military, qualifies for VA Benefits:
						<input name="hx_benefits_flag" class="wmtRadio" type="radio" <?php echo ((!$hx_data->benefits_flag)? ' checked':''); ?> value="0" />No
						<input name="hx_benefits_flag" class="wmtRadio" type="radio" style="margin-left:10px" <?php echo (($hx_data->benefits_flag)? ' checked':''); ?> value="1" />Yes
					</td>
				</tr>
				
				<tr>
					<td class="wmtLabel" valign="top" style="padding-top:15px" colspan="8">
						Education & Employment Comments:
						<textarea name="hx_employment_comments" id="hx_employment_comments" class="wmtFullInput" rows="4" style="height:97px"><?php echo $hx_data->employment_comments; ?></textarea>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
