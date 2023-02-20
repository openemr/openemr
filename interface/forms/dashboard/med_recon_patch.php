<?php
require_once('../../globals.php');
require_once($GLOBALS['srcdir'].'/api.inc');
require_once($GLOBALS['srcdir'].'/lists.inc');
require_once($GLOBALS['srcdir'].'/wmt-v2/wmtstandard.inc');

set_time_limit(0);
$fix_log = fopen($webserver_root.'/sites/default/patches/med_recon.log','w');
$sql = "SELECT fe.date AS dos, fe.pid, fe.encounter, form_ext_exam1.date AS ".
	"exam_dt FROM forms LEFT JOIN form_encounter AS fe USING(encounter) ".
	"LEFT JOIN form_ext_exam1 ON (forms.form_id = form_ext_exam1.id) ".
	"WHERE fe.date >= '2017-01-01 00:00:00' AND forms.formdir = 'ext_exam1' ".
	"AND forms.deleted = 0";
$mres = sqlStatement($sql);
echo "Reading our results<br>\n";
$insert = 'INSERT INTO amc_misc_data (amc_id, pid, map_category, '.
	'map_id, date_created, date_completed) VALUES ("med_reconc_amc", ?, '.
	'?, ?, ?, ?) ON DUPLICATE KEY UPDATE pid = ?';

while($mrow = sqlFetchArray($mres)) {
	$pid = $mrow{'pid'};
	$date = $mrow{'dos'};
	$enc = $mrow{'encounter'};
	echo "Encounter Processing: PID [$pid] {$date}<br>\n";
	sqlStatement($insert, array($pid, 'form_encounter', $enc, $date, $date, $pid));	

	$log = "Encounter Reconciled [$pid] {$date}\n";	
	fwrite($fix_log, $log);
}

$sql = "SELECT db.date AS db_date, db.pid FROM form_dashboard AS db ".
	"WHERE db.date >= '2017-01-01 00:00:00'";
$mres = sqlStatement($sql);
while($mrow = sqlFetchArray($mres)) {
	$pid = $mrow{'pid'};
	$date = $mrow{'db_date'};
	echo "Dashboard Processing: PID [$pid] {$date}<br>\n";
	sqlStatement($insert, array($pid, 'pat_dashboard', '0', $date, $date, $pid));	

	$log = "Dashboard Reconciled [$pid] {$date}\n";	
	fwrite($fix_log, $log);
}

fclose($fix_log);

?>
