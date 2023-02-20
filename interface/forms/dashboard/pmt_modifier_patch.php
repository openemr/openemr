<?php
require_once('../../globals.php');
require_once($GLOBALS['srcdir'].'/api.inc');
require_once($GLOBALS['srcdir'].'/wmt-v2/wmtstandard.inc');

set_time_limit(0);
$fix_log = fopen($webserver_root.'/sites/default/patches/pmt_mod_upd.log','w');
$chk_log = fopen($webserver_root.'/sites/default/patches/pmt_mod_chk.log','w');

$upd = "UPDATE ar_activity SET modifier = ? WHERE pid = ? AND encounter = ? ".
	"AND sequence_no = ?";
$sel = "SELECT * FROM billing WHERE code_type = ? AND code = ? AND " .
	"encounter = ? AND pid = ?";
$sql = "SELECT * FROM ar_activity ".
	"WHERE payer_type > 0 AND (modifier != '' AND modifier IS NOT NULL)";
$mres = sqlStatement($sql);
echo "Reading all payments with no modifier<br>\n";

while($mrow = sqlFetchArray($mres)) {
	$pid = $mrow{'pid'};
	$seq = $mrow{'sequence_no'};
	$enc = $mrow{'encounter'};
	$log = "Processing: PID [$pid] Enc ($enc) Sequence [$seq] ".$mrow{'code_type'}.':'.$mrow{'code'}."\n";
	echo $log;
	fwrite($chk_log, $log);

	$bres = sqlStatement($sel, array($mrow{'code_type'},$mrow{'code'},$enc,$pid);
	while($brow = sqlFetchArray($bres)) {

		$log = "Found: ".$brow{'code_type'}.':'.$brow{'code'}.':'$brow{'modifier'}."\n";
		echo $log;
		fwrite($chk_log, $log);

		if($brow{'modifier'}) {
			$log = "** Updating with Modifier ".$brow{'modifier'}."\n";	
			fwrite($fix_log, $log);
			sqlStatement($upd, array($brow{'modifier'}, $pid, $enc, $seq));
		} else {
			$log = "No Update\n";	
			fwrite($chk_log, $log);
		}
	}
}
fclose($fix_log);
fclose($chk_log);

?>
