<?php
require_once('../../globals.php');
require_once($GLOBALS['srcdir'].'/api.inc');
require_once($GLOBALS['srcdir'].'/wmt/wmtstandard.inc');

set_time_limit(0);
$fix_log = fopen($webserver_root.'/sites/default/patches/pap_nt_update.log','w');
$chk_log = fopen($webserver_root.'/sites/default/patches/pap_nt_check.log','w');

$sql = "SELECT db.id, db.db_pap_hist_nt, db.pid, wc.wc_pap_hist_nt ".
	"FROM form_dashboard AS db LEFT JOIN form_whc_comp AS wc ON (wc.id = ".
	"(SELECT id FROM form_whc_comp WHERE pid = db.pid ORDER BY wc_form_dt DESC ".
	"LIMIT 1) ) ".
	"WHERE wc_pap_hist_nt IS NOT NULL AND wc_pap_hist_nt != ''";
$mres = sqlStatement($sql);
echo "Reading our results<br>\n";

$sql = "UPDATE form_dashboard SET db_pap_hist_nt = ? WHERE id = ?";
while($mrow = sqlFetchArray($mres)) {
	$pid = $mrow{'pid'};
	$id = $mrow{'id'};
	$log = "Processing: PID [$pid] Dashboard ($id)\n";
	echo $log;
	fwrite($chk_log, $log);
	$log = "Dashboard Nt: ".$mrow{'db_pap_hist_nt'}."\nRecent Comp Note: ".$mrow{'wc_pap_hist_nt'}."\n";
	echo $log;
	fwrite($chk_log, $log);

	if(strpos($mrow{'db_pap_hist_nt'}, $mrow{'wc_pap_hist_nt'}) === FALSE) {
		$log = "** NO MATCH Updating Dashboard [$pid] ($id)\n";	
		fwrite($fix_log, $log);
		// INSERT THE NEW RECORD IN APPOINTMENT ENCOUNTER
		$new = $mrow{'db_pap_hist_nt'} ."\r". $mrow{'wc_pap_hist_nt'};
		sqlStatement($sql, array($new, $id));
	} else {
		$log = "No Update\n";	
		fwrite($chk_log, $log);
	}
}
fclose($fix_log);
fclose($chk_log);

?>
