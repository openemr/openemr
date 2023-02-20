<?php
require_once('../../globals.php');
require_once($GLOBALS['srcdir'].'/api.inc');
require_once($GLOBALS['srcdir'].'/wmt/wmtstandard.inc');
require_once('../../../custom/code_types.inc.php');

set_time_limit(0);
$fix_log = fopen($webserver_root.'/sites/default/patches/fixed_appts.log','w');
$chk_log = fopen($webserver_root.'/sites/default/patches/check_appts.log','w');
// STEP ONE, GET ALL THE APPOINTMENTS
$sql = "SELECT * FROM openemr_postcalendar_events LEFT JOIN ".
	"appointment_encounter ON pc_eid = eid WHERE pc_pid != 0 AND ".
	"pc_pid != '' AND pc_pid IS NOT NULL AND pc_apptstatus != 'x' AND ".
	"pc_apptstatus != '[NS]' AND pc_apptstatus != '[NSR]' AND ".
	"(encounter = 0 OR encounter IS NULL)";
$mres = sqlStatement($sql);
echo "Reading our results<br>\n";

$sql = "SELECT * FROM form_encounter WHERE pid=? AND pc_catid=? AND ".
	"date=?";
while($mrow = sqlFetchArray($mres)) {
	$pid = $mrow{'pc_pid'};
	$id = $mrow{'pc_eid'};
	$date = $mrow{'pc_eventDate'} . " 00:00:00";
	$dr = $mrow{'pc_aid'};
	$cat = $mrow{'pc_catid'};
	echo "Processing: PID [$pid] Item ($id) {$date} '$cat'<br>\n";

	$eres = sqlQuery($sql, array($pid, $cat, $date));
	if($eres{'encounter'}) {
		$enc = $eres{'encounter'};
		$log = "Updating Enc [".$eres{'encounter'}."] for Event ($id)\n";	
		fwrite($fix_log, $log);
		// INSERT THE NEW RECORD IN APPOINTMENT ENCOUNTER
		sqlStatement("INSERT INTO appointment_encounter (eid, encounter) ".
			"VALUES ($id, $enc)");
	} else {
		$log = "Could NOT Find Encounter for Event ($id) [$pid] {$cat}\n";	
		fwrite($chk_log, $log);
	}
}
fclose($fix_log);
fclose($chk_log);

?>
