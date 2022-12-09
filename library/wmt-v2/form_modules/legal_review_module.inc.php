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
					<td class="wmtLabel" style="padding-top:5px;width:200px">
						Felony Arrests:
					</td>
					<td class="wmtRadio" style="width:120px">
						<input name="hx_arrests_flag" class="wmtRadio" type="radio" <?php echo ((!$hx_data->arrests_flag)? ' checked':''); ?> value="0" />No
						<input name="hx_arrests_flag" class="wmtRadio" type="radio" style="margin-left:10px" <?php echo (($hx_data->arrests_flag)? ' checked':''); ?> value="1" />Yes
					</td>
					<td class="wmtLabel" style="width:50px">
						Explain:
					</td>
					<td class="wmtLabel">
						<input name="hx_arrests_notes" type="text" class="wmtFullInput" value="<?php echo $hx_data->arrests_notes; ?>" />
					</td>
				</tr>
				
				<tr>
					<td class="wmtLabel">
						Felony Convictions:
					</td>
					<td class="wmtRadio">
						<input name="hx_convict_flag" class="wmtRadio" type="radio" <?php echo ((!$hx_data->convict_flag)? ' checked':''); ?> value="0" />No
						<input name="hx_convict_flag" class="wmtRadio" type="radio" style="margin-left:10px" <?php echo (($hx_data->convict_flag)? ' checked':''); ?> value="1" />Yes
					</td>
					<td class="wmtLabel">
						Explain:
					</td>
					<td class="wmtLabel">
						<input name="hx_convict_notes" type="text" class="wmtFullInput" value="<?php echo $hx_data->convict_notes; ?>" />
					</td>
				</tr>
				
				<tr>
					<td class="wmtLabel" style="padding-top:5px">
						Sentenced:
					</td>
					<td class="wmtRadio">
						<input name="hx_jailed_flag" class="wmtRadio" type="radio" <?php echo ((!$hx_data->jailed_flag)? ' checked':''); ?> value="0" />No
						<input name="hx_jailed_flag" class="wmtRadio" type="radio" style="margin-left:10px" <?php echo (($hx_data->jailed_flag)? ' checked':''); ?> value="1" />Yes
					</td>
					<td class="wmtLabel">
						Explain:
					</td>
					<td class="wmtLabel">
						<input name="hx_jailed_notes" type="text" class="wmtFullInput" value="<?php echo $hx_data->jailed_notes; ?>" />
					</td>
				</tr>
			</table>

			<hr style="border-color:#eee" />
								
			<table width="100%" border="0" cellspacing="0" cellpadding="2">
				<tr>
					<td class="wmtLabel" style="padding-top:5px;width:200px">
						Currently on Probation / Parole:
					</td>
					<td class="wmtRadio" style="width:120px">
						<input name="hx_parole_flag" class="wmtRadio" type="radio" <?php echo ((!$hx_data->parole_flag)? ' checked':''); ?> value="0" />No
						<input name="hx_parole_flag" class="wmtRadio" type="radio" style="margin-left:10px" <?php echo (($hx_data->parole_flag)? ' checked':''); ?> value="1" />Yes
					</td>
					<td class="wmtLabel" style="width:50px">
						Explain:
					</td>
					<td class="wmtLabel">
						<input name="hx_parole_notes" type="text" class="wmtFullInput" value="<?php echo $hx_data->parole_notes; ?>" />
					</td>
				</tr>	
			</table>

			<hr style="border-color:#eee" />
			
			<table width="100%" border="0" cellspacing="0" cellpadding="2">
				<tr>
					<td class="wmtLabel" style="padding-top:5px;width:200px">
						DUI Arrests:
					</td>
					<td class="wmtRadio" style="width:120px">
						<input name="hx_dui_flag" class="wmtRadio" type="radio" <?php echo ((!$hx_data->dui_flag)? ' checked':''); ?> value="0" />No
						<input name="hx_dui_flag" class="wmtRadio" type="radio" style="margin-left:10px" <?php echo (($hx_data->dui_flag)? ' checked':''); ?> value="1" />Yes
					</td>
					<td class="wmtLabel" style="width:50px">
						Explain:
					</td>
					<td class="wmtLabel">
						<input name="hx_dui_notes" type="text" class="wmtFullInput" value="<?php echo $hx_data->dui_notes; ?>" />
					</td>
				</tr>
									
				<tr>
					<td class="wmtLabel" style="padding-top:5px;">
						Misdemeanors:
					</td>
					<td class="wmtRadio">
						<input name="hx_minor_flag" class="wmtRadio" type="radio" <?php echo ((!$hx_data->minor_flag)? ' checked':''); ?> value="0" />No
						<input name="hx_minor_flag" class="wmtRadio" type="radio" style="margin-left:10px" <?php echo (($hx_data->minor_flag)? ' checked':''); ?> value="1" />Yes
					</td>
					<td class="wmtLabel">
						Explain:
					</td>
					<td class="wmtLabel">
						<input name="hx_minor_notes" type="text" class="wmtFullInput" value="<?php echo $hx_data->minor_notes; ?>" />
					</td>
				</tr>
				
				<tr>
					<td class="wmtLabel" style="padding-top:5px">
						Pending Legal Problems:
					</td>
					<td class="wmtRadio">
						<input name="hx_pending_flag" class="wmtRadio" type="radio" <?php echo ((!$hx_data->pending_flag)? ' checked':''); ?> value="0" />No
						<input name="hx_pending_flag" class="wmtRadio" type="radio" style="margin-left:10px" <?php echo (($hx_data->pending_flag)? ' checked':''); ?> value="1" />Yes
					</td>
					<td class="wmtLabel">
						Explain:
					</td>
					<td class="wmtLabel">
						<input name="hx_pending_notes" class="wmtFullInput" type="text" value="<?php echo $hx_data->pending_notes ?>" />
					</td>
				</tr>
			</table>

			<table width="100%" border="0" cellspacing="0" cellpadding="0">
				<tr>
					<td class="wmtLabel" style="padding-top:15px" valign="top">
						Additional Comments:
						<textarea name="hx_legal_comments" id="hx_legal_comments" class="wmtFullInput" rows="4" style="height:97px"><?php echo $hx_data->legal_comments; ?></textarea>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
