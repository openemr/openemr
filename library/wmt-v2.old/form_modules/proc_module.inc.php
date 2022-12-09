<?php 
if(!isset($field_prefix)) $field_prefix = '';
if(!isset($dt{$field_prefix.'proc_nt'})) $dt{$field_prefix.'proc_nt'} = '';
$procs = LoadActiveProcedures('wmt::procedures',$frmdir);
$canned_text = array();
foreach($procs as $procedure) {
	$txt = getKeyedText($procedure['option_id'], $visit->provider_id);
	if($txt == "") $txt = $procedure['notes'];
	$canned_text[$procedure['option_id']] = $txt;
}
$chosen_procs = explode('|', $dt[$field_prefix.'proc_choices']);
$proc_rows = 0;
?>
  <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
			<td style="width: 20%">
    		<table width="100%" border="0" cellspacing="0" cellpadding="0">
					<tr>
						<td class="wmtLabel">Choices:</td>
					</tr>
					<?php
					foreach($procs as $procedure) { 
					?>
						<tr>
      				<td class="wmtLabel"><input name="tmp_proc_<?php echo $procedure['option_id']; ?>" id="tmp_proc_<?php echo $procedure['option_id']; ?>" type="checkbox" value="<?php echo $procedure['option_id']; ?>" <?php echo (in_array($procedure['option_id'], $chosen_procs)?' checked':''); ?> 
							<?php if($canned_text[$procedure['option_id']] != '') { ?>
							onChange="addCannedText(this,'<?php echo $field_prefix; ?>proc_nt','<?php echo $canned_text[$procedure['option_id']]; ?>','<?php echo strtoupper($procedure['title']); ?>');"
							<?php } ?>
							/><label for="tmp_proc_<?php echo $procedure['option_id']; ?>">&nbsp;&nbsp;<?php echo $procedure['title']; ?></label></td>
						</tr>
						<?php
						$proc_rows++;	
					}
					while($proc_rows < 10) {
						echo "<tr><td>&nbsp;</td></tr>\n";
						$proc_rows++;
					}
					?>
				</table>
			</td>
			<td>
    		<table width="100%" border="0" cellspacing="0" cellpadding="0">
					<tr>
						<td class="wmtLabel">Notes:</td>
						<td><div style="float: right; margin-right: 8px;"><a href="javascript:;" class="css_button" tabindex="-1" onclick="ClearThisField('<?php echo $field_prefix; ?>proc_nt');"><span>Clear Notes</span></a></div></td>
					</tr>
    			<tr>
      			<td colspan="2" rowspan="<?php echo $proc_rows; ?>"><textarea name="<?php echo $field_prefix; ?>proc_nt" id="<?php echo $field_prefix; ?>proc_nt" class="FullInput" rows="<?php echo ($proc_rows + 1); ?>"><?php echo htmlspecialchars($dt{$field_prefix.'proc_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
    			</tr>
				</table>
			</td>
		</tr>
	</table>
