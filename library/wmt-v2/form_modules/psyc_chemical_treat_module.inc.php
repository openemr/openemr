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
			<fieldset>
				<legend>Chemical Dependency Treatments</legend>

				<table width="100%" border="0" cellspacing="0" cellpadding="2">
					<tr>
						<td class="wmtHeader" style="width:200px">Facility</td>
						<td class="wmtHeader" style="width:100px">Date</td>
						<td class="wmtHeader" style="width:120px">Type</td>
						<td class="wmtHeader" style="width:80px">Completed</td>
						<td class="wmtHeader">Comments</td>
					</tr>
<?php 
	$cdt_list = explode('|',$hx_data->treatments_array);
	for ($i=0; $i < 4; $i++) {
		$cdt_data = ($cdt_list[$i]) ? explode('^',$cdt_list[$i]) : array(); 
		$x = $i + 1;
?>
					<tr>
						<td class="wmtLabel">
							<input name="hx_cdt_facility_<?php echo $x ?>" class="wmtFullInput" value="<?php echo $cdt_data[0] ?>" />
						</td>
						<td class="wmtLabel">
							<input name="hx_cdt_date_<?php echo $x ?>" class="wmtFullInput" value="<?php echo $cdt_data[1] ?>" />
						</td>
						<td class="wmtLabel">
							<select name="hx_cdt_type_<?php echo $x ?>">
								<?php ListSel($cdt_data[2], 'PSYC_Prior_TX') ?>
							</select>
						</td>
						<td class="wmtLabel">
							<select name="hx_cdt_completed_<?php echo $x ?>" class="wmtFullInput">
								<?php ListSel($cdt_data[3], 'yesno') ?>
							</select>
						</td>
						<td class="wmtLabel">
							<input name="hx_cdt_notes_<?php echo $x ?>" class="wmtFullInput" value="<?php echo $cdt_data[4] ?>" />
						</td>
					</tr>
<?php } ?>
				</table>
			</fieldset>
			
			<fieldset>
				<legend>Mental Health / Psychiatric Treatments</legend>

				<table width="100%" border="0" cellspacing="0" cellpadding="2">
					<tr>
						<td class="wmtHeader" style="width:200px">Facility</td>
						<td class="wmtHeader" style="width:100px">Date</td>
						<td class="wmtHeader">Reason</td>
					</tr>
									
<?php 
	$mht_list = explode('|',$hx_data->mental_array);
	for ($i=0; $i < 4; $i++) {
		$mht_data = ($mht_list[$i]) ? explode('^',$mht_list[$i]) : array(); 
		$x = $i + 1;
?>
					<tr>
						<td class="wmtLabel">
							<input name="hx_mht_facility_<?php echo $x ?>" class="wmtFullInput" value="<?php echo $mht_data[0] ?>" />
						</td>
						<td class="wmtLabel">
							<input name="hx_mht_date_<?php echo $x ?>" class="wmtFullInput" value="<?php echo $mht_data[1] ?>"/>
						</td>
						<td class="wmtLabel">
							<input name="hx_mht_reason_<?php echo $x ?>" class="wmtFullInput" value="<?php echo $mht_data[2] ?>" />
						</td>
					</tr>
<?php } ?>
				</table>
			</fieldset>
			
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
				<tr>
					<td class="wmtLabel" style="padding-top:5px;width:170px">
						Longest Length of Sobriety:
					</td>
					<td class="wmtLabel" style="width:120px">
						<input name="hx_sober_length" class="wmtInput" type="text" style="width:100px" value="<?php echo $hx_data->sober_length ?>" />
					</td>
					<td class="wmtLabel" style="width:50px">
						When:
					</td>
					<td class="wmtLabel" style="width:120px">
						<input name="hx_sober_when" type="text" class="wmtInput" style="width:100px" value="<?php echo $hx_data->sober_when; ?>" />
					</td>
					<td class="wmtLabel" style="width:60px">
						Comments:
					</td>
					<td class="wmtLabel">
						<input name="hx_sober_notes" type="text" class="wmtFullInput" value="<?php echo $hx_data->sober_notes; ?>" />
					</td>
				</tr>
					
				<tr>
					<td class="wmtLabel" valign="top" style="padding-top:15px" colspan="6">
						Additional Comments:
						<textarea name="hx_treatment_comments" id="hx_treatment_comments" class="wmtFullInput" rows="4" style="height:97px"><?php echo $hx_data->treatment_comments; ?></textarea>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
