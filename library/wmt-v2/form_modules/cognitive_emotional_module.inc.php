<?php
include_once($GLOBALS['srcdir'].'/wmt/wmt.class.php');
/* RETRIEVE MOST RECENT HISTORY DATA IF NOT SET BY A PRIOR MODULE */
if(!isset($hx_data) || !is_object($hx_data)) {
	$old = sqlQuery('SELECT `id`, `date` FROM `form_psyc_history` WHERE ' .
		'`pid` = ? ORDER BY `date` DESC', array($pid));
	if(!isset($old{'id'})) $old{'id'} = '';
	$hx_data = new wmtForm('psyc_history', $old{'id'});
	$pat_data = wmtPatient::getPidPatient($pid);
}
?>

<table width="100%"	border="0" cellspacing="2" cellpadding="0">
	<tr>
		<td>
			<table width="100%" border="0" cellspacing="0" cellpadding="2">
				<tr>
					<td class="wmtLabel" style="width:220px">
						Treated for Clinical Depression:
					</td>
					<td class="wmtRadio" style="width:140px">
						<input name="hx_depression_flag" class="wmtRadio" type="radio" <?php echo ((!$hx_data->depression_flag)? ' checked':''); ?> value="0" />No
						<input name="hx_depression_flag" class="wmtRadio" type="radio" style="margin-left:10px" <?php echo (($hx_data->depression_flag)? ' checked':''); ?> value="1" />Yes
					</td>
					<td class="wmtLabel" style="width:50px">
						Explain:
					</td>
					<td class="wmtLabel">
						<input name="hx_depression_notes" type="text" class="wmtFullInput" value="<?php echo $hx_data->depression_notes; ?>" />
					</td>
				</tr>
				
				<tr>
					<td class="wmtLabel">
						Treated for Anxiety Disorder:
					</td>
					<td class="wmtRadio">
						<input name="hx_anxiety_flag" class="wmtRadio" type="radio" <?php echo ((!$hx_data->anxiety_flag)? ' checked':''); ?> value="0" />No
						<input name="hx_anxiety_flag" class="wmtRadio" type="radio" style="margin-left:10px" <?php echo (($hx_data->anxiety_flag)? ' checked':''); ?> value="1" />Yes
					</td>
					<td class="wmtLabel">
						Explain:
					</td>
					<td class="wmtLabel">
						<input name="hx_anxiety_notes" type="text" class="wmtFullInput" value="<?php echo $hx_data->anxiety_notes; ?>" />
					</td>
				</tr>
				
				<tr>
					<td class="wmtLabel">
						Diagnosed with Other Mental Illness:
					</td>
					<td class="wmtRadio">
						<input name="hx_mental_flag" class="wmtRadio" type="radio" <?php echo ((!$hx_data->mental_flag)? ' checked':''); ?> value="0" />No
						<input name="hx_mental_flag" class="wmtRadio" type="radio" style="margin-left:10px" <?php echo (($hx_data->mental_flag)? ' checked':''); ?> value="1" />Yes
					</td>
					<td class="wmtLabel">
						Explain:
					</td>
					<td class="wmtLabel">
						<input name="hx_mental_notes" type="text" class="wmtFullInput" value="<?php echo $hx_data->mental_notes; ?>" />
					</td>
				</tr>
			</table>
			
			<hr style="border-color:#eee" />
			
			<table width="100%" border="0" cellspacing="0" cellpadding="2">
				<tr>
					<td class="wmtLabel" style="width:180px">
						Mood (client description):
					</td>
					<td class="wmtLabel" colspan="3">
						<input name="hx_mood" type="text" class="wmtFullInput" value="<?php echo $hx_data->mood; ?>" />
					</td>
				</tr>
					
				<tr>
					<td class="wmtLabel">
						Affect:
					</td>
					<td class="wmtLabel">
						<?php ListCheck('hx_affect',$hx_data->affect_array,'PSYC_Affect') ?>
					</td>
				</tr>
				<tr>
					<td></td>
					<td class="wmtLabel">
						<input name="hx_affect_notes" type="text" class="wmtFullInput" value="<?php echo $hx_data->affect_notes; ?>" />
					</td>
				</tr>
					
				<tr>
					<td class="wmtLabel">
						Dress:
					</td>
					<td class="wmtLabel">
						<?php ListCheck('hx_dress',$hx_data->dress_array,'PSYC_Dress') ?>
					</td>
				</tr>
				<tr>
					<td></td>
					<td class="wmtLabel">
						<input name="hx_dress_notes" type="text" class="wmtFullInput" value="<?php echo $hx_data->dress_notes; ?>" />
					</td>
				</tr>
					
				<tr>
					<td class="wmtLabel">
						Grooming:
					</td>
					<td class="wmtLabel">
						<?php ListCheck('hx_grooming',$hx_data->grooming_array,'PSYC_Grooming') ?>
					</td>
				</tr>
				<tr>
					<td></td>
					<td class="wmtLabel">
						<input name="hx_grooming_notes" type="text" class="wmtFullInput" value="<?php echo $hx_data->grooming_notes; ?>" />
					</td>
				</tr>
			
				<tr>
					<td class="wmtLabel">
						Eye Contact:
					</td>
					<td class="wmtLabel">
						<?php ListCheck('hx_eye_contact',$hx_data->eye_contact_array,'PSYC_Eye_Contact') ?>
					</td>
				</tr>
				<tr>
					<td></td>
					<td class="wmtLabel">
						<input name="hx_eye_notes" type="text" class="wmtFullInput" value="<?php echo $hx_data->eye_notes; ?>" />
					</td>
				</tr>
											
				<tr>
					<td class="wmtLabel">
						Activity Level:
					</td>
					<td class="wmtLabel">
						<?php ListCheck('hx_response',$hx_data->response_array,'PSYC_Activity_Level') ?>
					</td>
				</tr>
				<tr>
					<td></td>
					<td class="wmtLabel">
						<input name="hx_response_notes" type="text" class="wmtFullInput" value="<?php echo $hx_data->response_notes; ?>" />
					</td>
				</tr>
											
				<tr>
					<td class="wmtLabel">
						Behavior:
					</td>
					<td class="wmtLabel">
						<?php ListCheck('hx_behavior',$hx_data->behavior_array,'PSYC_Behavior') ?>
					</td>
				</tr>
				<tr>
					<td></td>
					<td class="wmtLabel">
						<input name="hx_behavior_notes" type="text" class="wmtFullInput" value="<?php echo $hx_data->behavior_notes; ?>" />
					</td>
				</tr>
											
				<tr>
					<td class="wmtLabel">
						Thought Content:
					</td>
					<td class="wmtLabel">
						<?php ListCheck('hx_content',$hx_data->content_array,'PSYC_Thought_Content') ?>
					</td>
				</tr>
				<tr>
					<td></td>
					<td class="wmtLabel">
						<input name="hx_content_notes" type="text" class="wmtFullInput" value="<?php echo $hx_data->content_notes; ?>" />
					</td>
				</tr>
											
				<tr>
					<td class="wmtLabel">
						Thought Processes:
					</td>
					<td class="wmtLabel">
						<?php ListCheck('hx_process',$hx_data->process_array,'PSYC_Thought_Process') ?>
					</td>
				</tr>
				<tr>
					<td></td>
					<td class="wmtLabel">
						<input name="hx_process_notes" type="text" class="wmtFullInput" value="<?php echo $hx_data->process_notes; ?>" />
					</td>
				</tr>
											
				<tr>
					<td class="wmtLabel">
						Other Addictive Behaviors:
					</td>
					<td class="wmtLabel">
						<?php ListCheck('hx_addictive',$hx_data->addictive_array,'PSYC_Addictive') ?>
					</td>
				</tr>
				<tr>
					<td></td>
					<td class="wmtLabel">
						<input name="hx_addictive_notes" type="text" class="wmtFullInput" value="<?php echo $hx_data->addictive_notes; ?>" />
					</td>
				</tr>
											
				<tr>
					<td class="wmtLabel">
						Sleep Patterns:
					</td>
					<td class="wmtLabel">
						<?php ListCheck('hx_sleep',$hx_data->sleep_array,'PSYC_Sleep') ?>
					</td>
				</tr>
				<tr>
					<td></td>
					<td class="wmtLabel">
						<input name="hx_sleep_notes" type="text" class="wmtFullInput" value="<?php echo $hx_data->sleep_notes; ?>" />
					</td>
				</tr>
			
				<tr>
					<td class="wmtLabel" valign="top" style="padding-top:15px" colspan="4">
						Strengths (as described by client):
						<textarea name="hx_strengths" id="hx_comments" class="wmtFullInput" rows="2"><?php echo $hx_data->strengths; ?></textarea>
					</td>
				</tr>
			
				<tr>
					<td class="wmtLabel" valign="top" style="padding-top:15px" colspan="4">
						Weaknesses (as described by client):
						<textarea name="hx_weaknesses" id="hx_weaknesses" class="wmtFullInput" rows="2"><?php echo $hx_data->weaknesses; ?></textarea>
					</td>
				</tr>
			</table>
											
			<fieldset>
				<legend>Evaluation of Suicidal / Homicidal Ideation & Self-Harming Behavior</legend>
				<span style="color:#c00;font-style:italic;font-size:80%;font-family:times,serif">Please note: a '<b>yes</b>' response to any of the questions in this section must be reported to the Program Director.</span>
				
				
				<table width="100%" border="0" cellspacing="0" cellpadding="2" style="margin-top:10px">
					<tr>
						<td class="wmtLabel" style="padding-top:5px;width:400px">
							Do you currently have thoughts of suicide:
						</td>
						<td class="wmtRadio">
							<input name="hx_suicide_flag" class="wmtRadio" type="radio" <?php echo ((!$hx_data->suicide_flag)? ' checked':''); ?> value="0" />No
							<input name="hx_suicide_flag" class="wmtRadio" type="radio" style="margin-left:10px" <?php echo (($hx_data->suicide_flag)? ' checked':''); ?> value="1" />Yes
						</td>
						<td class="wmtLabel" style="width:50px;vertical-align:top;padding-top:6px" rowspan="3">
							Explain:
						</td>
						<td class="wmtLabel" rowspan="3">
							<textarea name="hx_suicide_notes" class="wmtFullInput" style="resize:none" rows="3" ><?php echo $hx_data->suicide_notes; ?></textarea>
						</td>
					</tr>
					
					<tr>
						<td class="wmtLabel" style="padding-left:25px;padding-top:5px">
							If yes, do you have a plan:
						</td>
						<td class="wmtRadio" colspan="3">
							<input name="hx_plans_flag" class="wmtRadio" type="radio" <?php echo ((!$hx_data->plans_flag)? ' checked':''); ?> value="0" />No
							<input name="hx_plans_flag" class="wmtRadio" type="radio" style="margin-left:10px" <?php echo (($hx_data->plans_flag)? ' checked':''); ?> value="1" />Yes
						</td>
					</tr>
				
					<tr>
						<td class="wmtLabel" style="padding-left:25px;padding-top:5px">
							If yes, do you have access to the components of your plan:
						</td>
						<td class="wmtRadio" colspan="3">
							<input name="hx_access_flag" class="wmtRadio" type="radio" <?php echo ((!$hx_data->access_flag)? ' checked':''); ?> value="0" />No
							<input name="hx_access_flag" class="wmtRadio" type="radio" style="margin-left:10px" <?php echo (($hx_data->access_flag)? ' checked':''); ?> value="1" />Yes
						</td>
					</tr>
				
					<tr>
						<td class="wmtLabel" style="padding-top:5px;width:400px">
							Any past suicide attempts or ideation:
						</td>
						<td class="wmtRadio" style="width:120px">
							<input name="hx_attempts_flag" class="wmtRadio" type="radio" <?php echo ((!$hx_data->attempts_flag)? ' checked':''); ?> value="0" />No
							<input name="hx_attempts_flag" class="wmtRadio" type="radio" style="margin-left:10px" <?php echo (($hx_data->attempts_flag)? ' checked':''); ?> value="1" />Yes
						</td>
						<td class="wmtLabel" style="width:50px">
							Explain:
						</td>
						<td class="wmtLabel">
							<input name="hx_attempts_notes" type="text" class="wmtFullInput" value="<?php echo $hx_data->attempts_notes; ?>" />
						</td>
					</tr>
					
					<tr>
						<td class="wmtLabel" style="padding-top:5px;width:400px">
							Family history of suicide:
						</td>
						<td class="wmtRadio" style="width:120px">
							<input name="hx_history_flag" class="wmtRadio" type="radio" <?php echo ((!$hx_data->history_flag)? ' checked':''); ?> value="0" />No
							<input name="hx_history_flag" class="wmtRadio" type="radio" style="margin-left:10px" <?php echo (($hx_data->history_flag)? ' checked':''); ?> value="1" />Yes
						</td>
						<td class="wmtLabel">
							Explain:
						</td>
						<td class="wmtLabel">
							<input name="hx_history_notes" type="text" class="wmtFullInput" value="<?php echo $hx_data->history_notes; ?>" />
						</td>
					</tr>
					
					<tr>
						<td class="wmtLabel" style="padding-top:5px;width:400px">
							Do you have thoughts of hurting yourself (e.g. cutting?):
						</td>
						<td class="wmtRadio" style="width:120px">
							<input name="hx_hurt_flag" class="wmtRadio" type="radio" <?php echo ((!$hx_data->hurt_flag)? ' checked':''); ?> value="0" />No
							<input name="hx_hurt_flag" class="wmtRadio" type="radio" style="margin-left:10px" <?php echo (($hx_data->hurt_flag)? ' checked':''); ?> value="1" />Yes
						</td>
						<td class="wmtLabel">
							Explain:
						</td>
						<td class="wmtLabel">
							<input name="hx_hurt_notes" type="text" class="wmtFullInput" value="<?php echo $hx_data->hurt_notes; ?>" />
						</td>
					</tr>
					
					<tr>
						<td class="wmtLabel" style="padding-top:5px;width:400px">
							Do you have thoughts of hurting someone else:
						</td>
						<td class="wmtRadio" style="width:120px">
							<input name="hx_harm_flag" class="wmtRadio" type="radio" <?php echo ((!$hx_data->harm_flag)? ' checked':''); ?> value="0" />No
							<input name="hx_harm_flag" class="wmtRadio" type="radio" style="margin-left:10px" <?php echo (($hx_data->harm_flag)? ' checked':''); ?> value="1" />Yes
						</td>
						<td class="wmtLabel">
							Explain:
						</td>
						<td class="wmtLabel">
							<input name="hx_harm_notes" type="text" class="wmtFullInput" value="<?php echo $hx_data->harm_notes; ?>" />
						</td>
					</tr>
				</table>
			</fieldset>
			
			<table width="100%" border="0" cellspacing="0" cellpadding="2">
				<tr>
					<td class="wmtLabel" valign="top">
						Functioning Comments:
						<textarea name="hx_function_comments" id="hx_function_comments" class="wmtFullInput" rows="4" style="height:97px"><?php echo $hx_data->function_comments; ?></textarea>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
