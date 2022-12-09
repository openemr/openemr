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
				<legend>Last Six Months</legend>

				<table width="100%" border="0" cellspacing="0" cellpadding="2">
					<tr>
						<td class="wmtHeader">Substance</td>
						<td class="wmtHeader" style="width:80px">Type</td>
						<td class="wmtHeader" style="width:80px">Amount</td>
						<td class="wmtHeader" style="width:120px">Frequency</td>
						<td class="wmtHeader" style="width:80px">Method</td>
						<td class="wmtHeader" style="width:80px">Last Use</td>
						<td class="wmtHeader" style="width:80px">Age Started</td>
						<td class="wmtHeader">Comments</td>
					</tr>
<?php 
	
	// convert into associative array (key => substance, value => data array)
	$lsm_array = array();
	$lsm_list = explode('|',$hx_data->abuse_array);
	for ($i = 0;$i < count($lsm_list);$i++) {
		$lsm_data = ($lsm_list[$i]) ? explode('^',$lsm_list[$i]) : array();
		$lsm_array[$lsm_data[0]] = $lsm_data;
	}
	
	// process each substance
	$x = 0;
	$sub_list = wmtOption::fetchOptions('PSYC_Substances');
	foreach ($sub_list as $substance) {
		$x++;
		$lsm_data = ($lsm_array[$substance->option_id]) ? $lsm_array[$substance->option_id] : array(); 
?>
					<tr>
						<td class="wmtLabel" style="width:150px">
							<input class="wmtCheck" type="checkbox" name="hx_lsm_drug_<?php echo $x ?>" id="hx_lsm_drug_<?php echo $x ?>" value="<?php echo $substance->option_id ?>" <?php if ($lsm_data[0]) echo 'checked' ?> />
							<input type="hidden" name="hx_lsm_name_<?php echo $x ?>" value="<?php echo $substance->title?>" />
							<label class="wmtCheck"><?php echo $substance->title ?></label>
						</td>
						<td class="wmtLabel">
							<input name="hx_lsm_type_<?php echo $x ?>" id="hx_lsm_type_<?php echo $x ?>" class="wmtFullInput" value="<?php echo $lsm_data[2] ?>" />
						</td>
						<td class="wmtLabel">
							<input name="hx_lsm_amount_<?php echo $x ?>" id="hx_lsm_amount_<?php echo $x ?>" class="wmtFullInput" value="<?php echo $lsm_data[3] ?>" />
						</td>
						<td class="wmtLabel">
							<input name="hx_lsm_freq_<?php echo $x ?>" id="hx_lsm_freq_<?php echo $x ?>" class="wmtFullInput" value="<?php echo $lsm_data[4] ?>" />
						</td>
						<td class="wmtLabel">
							<input name="hx_lsm_method_<?php echo $x ?>" id="hx_lsm_method_<?php echo $x ?>" class="wmtFullInput" value="<?php echo $lsm_data[5] ?>" />
						</td>
						<td class="wmtLabel">
							<input name="hx_lsm_last_<?php echo $x ?>" id="hx_lsm_last_<?php echo $x ?>" class="wmtFullInput" value="<?php echo $lsm_data[6] ?>" />
						</td>
						<td class="wmtLabel">
							<input name="hx_lsm_age_<?php echo $x ?>" id="hx_lsm_age_<?php echo $x ?>" class="wmtFullInput" value="<?php echo $lsm_data[7] ?>" />
						</td>
						<td class="wmtLabel">
							<input name="hx_lsm_notes_<?php echo $x ?>" id="hx_lsm_notes_<?php echo $x ?>" class="wmtFullInput" value="<?php echo $lsm_data[8] ?>" />
						</td>
					</tr>
<?php 
	} // end foreach substance
?>
	 
				</table>
			</fieldset>
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
				<tr>
					<td class="wmtLabel" valign="top">
						Additional Comments:
						<textarea name="hx_abuse_comments" id="hx_abuse_comments" class="wmtFullInput" rows="4" style="height:97px"><?php echo $hx_data->abuse_comments; ?></textarea>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
