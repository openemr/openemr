<?php
require_once('../../globals.php');
require_once($GLOBALS['srcdir'].'/api.inc');
$ar_tables = array('ar_session', 'billing', 'claims');

$log = fopen($webserver_root.'/sites/default/patches/ins_smash.log','w');
$rpt = fopen($webserver_root.'/sites/default/patches/ins_smash.rpt','w');

$txt = '"Target ID","Target Name","Target Street","Target City",' .
	'"Source ID","Source Name","Source Street","Source City"';
		
fwrite($log, $txt . "\n");	

$source_sql = 'SELECT i.id AS iid, i.name, i.attn, a.line1, a.city, a.state, ' .
	'a.zip FROM insurance_companies AS i LEFT JOIN addresses AS a ON '.
	'(insurance_companies.id = addresses.foreign_id) WHERE ' .
	'i.id = ?';
$target_sql = 'SELECT i.id AS iid, i.name, i.attn, a.line1, a.city, a.state, ' .
	'a.zip FROM insurance_companies AS i LEFT JOIN addresses AS a ON '.
	'(insurance_companies.id = addresses.foreign_id) WHERE name LIKE ? '.
	'AND i.id != ?';

function MergePlans($source, $target) {
	global $ar_tables;
	foreach($ar_tables as $tbl) {
		$sql = 'UPDATE ' . $tbl . ' SET payer_id = ? WHERE payer_id = ?';
		sqlStatement($sql, array($target, $source));
	}
	sqlStatement('UPDATE hl7_ins_xlat SET oemr_id = ? WHERE oemr_id = ?',
		array($target, $source));
	sqlStatement('UPDATE insurance_data SET provider = ? WHERE provider = ?',
		array($target, $source));

	sqlStatement('DELETE FROM addresses WHERE foreign_id = ?',array($source));
	sqlStatement('DELETE FROM phone_numbers WHERE foreign_id = ?',array($source));
	sqlStatement('DELETE FROM insurance_companies WHERE id = ?',array($source));
}

$irow = sqlQuery('SELECT MAX(`id`) AS max FROM insurance_companies');
$max = $irow{'max'};
$ins = 1;
while($ins <= $max) {
	$irow = sqlQuery($source_sql, array($ins));
	if(!isset($irow{'id'})) {
		$ins++;
		continue;
	}

	$tres = sqlStatement($target_sql, array($irow{'name'}, $irow{'iid'}));
	$txt = 'Checking Plan [' .
		$irow{'iid'} . '] - (' . $irow{'name'} . ') <' . $irow{'attn'} . '>';
	fwrite($rpt, $txt . "\n");	
	$txt = '   [' . $irow{'line1'} . '] - (' . $irow{'city'} . ')';
	fwrite($rpt, $txt . "\n");	

	while($trow = sqlFetchArray($tres)) {
		if($trow{'iid'} == $irow{'iid'}) continue;
		$txt = '  -> Evaluating Plan [' . $trow{'iid'} . '] - (' . $trow{'name'} . 
			') <' . $trow{'attn'} . '>';
		fwrite($rpt, $txt . "\n");	
		$txt = '     [' . $trow{'line1'} . '] - (' . $trow{'city'} . ')';
		fwrite($rpt, $txt . "\n");	

		if(($irow{'attn'} == $trow{'attn'}) && 
				($irow{'line1'} == $trow{'line1'}) &&
							($irow{'city'} == $trow{'city'})) {
			$txt = '     ** MATCH ** Plan [' . $trow{'iid'} . 
				'] Will be Merged Into (' . $irow{'iid'} . ')';
			fwrite($rpt, $txt . "\n");	
			$txt = '"' . $irow{'iid'} . '","' . $irow{'name'} . '","' . 
				$irow{'line1'} . '","' . $irow{'city'} . '","' . $trow{'iid'} . 
				'","' . $trow{'name'} . '","' .  $trow{'line1'} . '","' . 
				$trow{'city'} . '"';
			fwrite($log, $txt . "\n");
			MergePlans($trow{'iid'}, $irow{'iid'});
		}
	}
	
	fwrite($rpt, "\n");
	$ins++;
	
}

?>
