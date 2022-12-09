<?php
include_once("../../../custom/code_types.inc.php");
?>
<table width="100%" border="0" cellspacing="0" cellpadding="3">
	<tr>
		<td class="bkkLabel bkkBorder1B bkkC" style="width: 100px;">Diagnosis</td>
		<td class="bkkLabel bkkBorder1B bkkC">Description</td>
	</tr>
<?php
$bg = 'bkkLight';
$cnt=1;
if(isset($diag) & (count($diag) > 0)) {
	foreach($diag as $prev) {
		if($prev['enddate']) continue;
		// If multiple diags OEMR puts them in a semi-colon delimited list
		// we'll use the first but keep the rest to put back if we update
		$remainder='';
		$code_type='';
		if($pos = strpos($prev['diagnosis'],';')) {
			$remainder=trim(substr($prev['diagnosis'],($pos+1)));
			$prev['diagnosis']=trim(substr($prev['diagnosis'],0,$pos));
		}
		$desc = lookup_code_descriptions($prev['diagnosis']);
		if($pos = strpos($prev['diagnosis'],':')) {
			$prev['diagnosis']=trim(substr($prev['diagnosis'],($pos+1)));
		}
?>
	<tr class="<?php echo $bg; ?>">
		<td class="bkkBody">&nbsp;&nbsp;<?php echo htmlspecialchars($prev['diagnosis'],ENT_QUOTES,'',FALSE); ?></td>
		<td class="bkkBody"><?php echo htmlspecialchars($desc,ENT_QUOTES,'',FALSE); ?></td>
	</tr>
		
<?php
		$bg = ($bg == 'bkkAltLight' ? 'bkkLight' : 'bkkAltLight');
		$cnt++;
	}
} else {
?>
	<tr class="<?php echo $bg; ?>">
		<td class="bkkBody">&nbsp;</td>
		<td class="bkkLabel">No Current Diagnoses To Display</td>
	</tr>
<?php 
}
?>
</table>
