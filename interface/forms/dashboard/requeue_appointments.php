<?php
require_once('../../globals.php');
require_once($GLOBALS['srcdir'].'/api.inc');
require_once($GLOBALS['srcdir'].'/wmt-v2/wmtstandard.inc');

set_time_limit(0);
// STEP ONE, GET ALL THE APPOINTMENTS
$sql = "SELECT * FROM openemr_postcalendar_events ".
	"WHERE pc_pid != 0 AND pc_pid != '' AND pc_pid IS NOT NULL ".
	"AND pc_apptstatus != 'x' AND pc_time >= '2018-08-01 00:00:00' ".
	"AND pc_eventDate < '2018-09-01 00:00:00'";
$mres = sqlStatement($sql);
echo "Reading our results<br>\n";

$sql = "INSERT INTO hl7_queue(hl7_msg_group, hl7_msg_type, oemr_table, ".
	"oemr_ref_id, processed) VALUES ('SIU', 'S12', ".
	"'openemr_postcalendar_events', ?, 0) ON DUPLICATE KEY UPDATE ".
	"processed = 0";
while($mrow = sqlFetchArray($mres)) {
	$pid = $mrow{'pc_pid'};
	$id = $mrow{'pc_eid'};
	$date = $mrow{'pc_eventDate'};
	$dr = $mrow{'pc_aid'};
	$cat = $mrow{'pc_catid'};
	echo "Processing: PID [$pid] Item ($id) {$date} '$cat'<br>\n";
	$eres = sqlStatement($sql, array($id));
}

?>
