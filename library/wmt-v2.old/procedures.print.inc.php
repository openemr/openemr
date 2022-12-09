<?php
include_once($GLOBALS['srcdir'].'/wmt-v2/procedures.inc');
include_once($GLOBALS['srcdir'].'/wmt-v2/billing_tools.inc');
if(!isset($suppress_class)) $suppress_class = '';
$suppress_plan = checkSettingMode('wmt::suppress_proc_plan','',$frmdir);
$use_justify = FALSE;
$proc_data = GetEncounterProcedures($pid, $encounter, 'billing');
// NOW GET ANY PROCEDURES THAT ARE NOT IN THE 'billing' TABLE FOR SOME REASON
$extra = GetEncounterProcedures($pid, $encounter, 'lists');
foreach($extra as $x) {
	foreach($bill_flds as $fld) {
		$x[$fld] = '';
	}
	$x['code_type'] = $x['stype'];
	$x['code'] = $x['scode'];
	$x['modifier'] = $x['injury_part'];
	$proc_data[] = $x;
}

$_print = FALSE;
foreach($proc_data as $prev) {
	if($prev['ct_proc']) $_print = TRUE; 
}

if($_print) {
	PrintChapter('Procedures');	
	$chp_printed = TRUE;
?>
	
<tr>
	<td class='wmtPrnLabel wmtPrnBorder1All' style='width: 85px;'>Code</td>
	<td class='wmtPrnLabel wmtPrnBorder1All' style='width: 55px;'>Modifier</td>
	<td class='wmtPrnLabel wmtPrnBorder1All' style='width: 55px;'>Units</td>
	<?php if($use_justify) { ?>
	<td class='wmtPrnLabel wmtPrnBorder1All'>Justification</td>
	<?php } ?>
	<td class='wmtPrnLabel wmtPrnBorder1All'>Description</td>
</tr>
<?php
$cnt=1;
foreach($proc_data as $prev) {
	if(!$prev['ct_proc']) continue;
	if($prev['code_text'] == '') {
		$prev['code_text'] = $prev['title'];
		if($prev['code_text'] = '') $prev['code_text'] = 
				lookup_code_descriptions($prev['code_type'] . ':' . $prev['code']);
	}

 	echo "<tr";
	echo $suppress_class ? ' class="'.$suppress_class.'"' : '';
	echo ">\n";
	echo "<td>";
	echo htmlspecialchars($prev['code'],ENT_QUOTES);
	echo "</td>\n";
	echo "<td>"; 
	echo htmlspecialchars($prev['modifier'],ENT_QUOTES);
	echo "</td>\n";
	echo "<td>";
	echo htmlspecialchars($prev['units'],ENT_QUOTES);
	echo "</td>\n";
	if($use_justify) {
		echo "<td>";
		echo convertJustifyToFee($prev['justify']);
		echo "</td>\n";
	}
	echo "<td>";
	echo ($prev['title'] != '') ? htmlspecialchars($prev['title'],ENT_QUOTES) : htmlspecialchars($prev['code_text'],ENT_QUOTES);
	echo "</td>\n";

	if(!$suppress_plan && $prev['comments']) {
		echo "<tr>\n";
		echo "<td class='wmtPrnLabel wmtBorder1B' style='vertical-align: top;'>Detail:";
		echo "</td>\n";
		echo "<td colspan='";
		echo $use_justify ? 4 : 3;
		echo "' class='wmtBorder1B'>";
		echo htmlspecialchars($prev['comments'],ENT_QUOTES);
		echo "</td>\n";
		echo "</tr>\n";
	}
}
	CloseChapter();
} //END - IF PRINT
?>
