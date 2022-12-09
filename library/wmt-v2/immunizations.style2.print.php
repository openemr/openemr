<?php
if(!isset($imm)) $imm = array();
if(!isset($pane_printed)) $pane_printed = false;
if(!isset($pane_title)) $pane_title= xl('Immunizations','r');
if(!isset($dt['fyi_imm_nt'])) $dt['fyi_imm_nt'] = '';
if(!isset($fyi->fyi_imm_nt)) $fyi->fyi_imm_nt = $dt{'fyi_imm_nt'};
?>
<fieldset style="border: solid 1px black; padding: 0px; border-collpase: collapse;"><legend class="bkkPrnHeader">&nbsp;<?php echo $pane_title; ?>&nbsp;</legend>
	<table width='100%' border='0' cellspacing='0' cellpadding='3' style="border-collapse: collapse; margin-top: 4px;">
	 	<tr>
 			<td class='bkkPrnLabel bkkPrnC' style='width: 95px'>Date</td>
 			<td class='bkkPrnLabel bkkPrnC'>Immunization</td>
 			<td class='bkkPrnLabel bkkPrnC'>Notes</td>
 		</tr>
<?php
if(count($imm) > 0) {
	foreach($imm as $prev) {
?>
		<tr>
			<td class='bkkPrnBody bkkPrnBorder1T'><?php echo htmlspecialchars(substr($prev['administered_date'],0,10), ENT_QUOTES, '', FALSE); ?>&nbsp;</td>
			<td class='bkkPrnBody bkkPrnBorder1T bkkPrnBorder1L'><?php echo htmlspecialchars(ImmLook($prev['cvx_code'],'immunizations'), ENT_QUOTES, '', FALSE); ?>&nbsp;</td>
			<td class='bkkPrnBody bkkPrnBorder1T bkkPrnBorder1L'><?php echo htmlspecialchars($prev['note'], ENT_QUOTES, '', FALSE); ?>&nbsp;</td>
		</tr>
<?php 
	}
} else {
?>
			<td class='bkkPrnBody'>&nbsp;</td>
			<td class='bkkPrnBody' colspan='2'>No Detail on File</td>

<?php
}
if(!empty($fyi->fyi_imm_nt)) {
?>
		<tr>
			<td class='bkkPrnLabel bkkPrnBorder1T' colspan='3'>&nbsp;<?php xl('Other Notes','e'); ?>:</td>
		</tr>
		<tr>
			<td class='bkkPrnBody' colspan='3'><?php echo htmlspecialchars($fyi->fyi_imm_nt, ENT_QUOTES, '', FALSE); ?></td>
		</tr>
<?php 
}
$pane_printed = true;
?>
	</table>
</fieldset>
