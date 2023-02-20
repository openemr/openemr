<?php
require_once('../../globals.php');
require_once($GLOBALS['srcdir'].'/api.inc');
require_once($GLOBALS['srcdir'].'/wmt/wmtstandard.inc');

set_time_limit(0);
$fix_log = fopen($webserver_root.'/sites/default/patches/fixed_eRx.log','w');
// STEP ONE, GET ALL ENCOUNTERS AFTER 2017-09-25 
$sql = "SELECT * FROM form_encounter WHERE date >= '2017-09-25 00:00:00'";
$mres = sqlStatement($sql);
echo "Reading our results<br>\n";

$sql = "UPDATE patient_data SET usertext1 = '' WHERE pid = ?";
	"date=?";
while($mrow = sqlFetchArray($mres)) {
	$pid = $mrow{'pid'};
	$date = substr($mrow{'date'},0,10);
	echo "Processing: PID [$pid] {$date}<br>\n";
	sqlStatement($sql, array($pid));

	$log = "Fixed [$pid] {$date}\n";	
	fwrite($fix_log, $log);
}
fclose($fix_log);

?>
