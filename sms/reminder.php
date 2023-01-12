<?php
/** **************************************************************************
 *	sms/reminder.php
 *
 *	Copyright (c)2019 - Medical Technology Services (MDTechSvcs.com)
 *
 *	This program is licensed software: licensee is granted a limited nonexclusive
 *  license to install this Software on more than one computer system, as long as all
 *  systems are used to support a single licensee. Licensor is and remains the owner
 *  of all titles, rights, and interests in program.
 *  
 *  Licensee will not make copies of this Software or allow copies of this Software 
 *  to be made by others, unless authorized by the licensor. Licensee may make copies 
 *  of the Software for backup purposes only.
 *
 *	This program is distributed in the hope that it will be useful, but WITHOUT 
 *	ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS 
 *  FOR A PARTICULAR PURPOSE. LICENSOR IS NOT LIABLE TO LICENSEE FOR ANY DAMAGES, 
 *  INCLUDING COMPENSATORY, SPECIAL, INCIDENTAL, EXEMPLARY, PUNITIVE, OR CONSEQUENTIAL 
 *  DAMAGES, CONNECTED WITH OR RESULTING FROM THIS LICENSE AGREEMENT OR LICENSEE'S 
 *  USE OF THIS SOFTWARE.
 *
 *  @package sms
 *  @version 1.0
 *  @copyright Medical Technology Services
 *  @author Ron Criswell <ron.criswell@MDTechSvcs.com>
 * 
 *************************************************************************** */
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_STRICT & ~E_DEPRECATED);
ini_set('error_reporting', E_ALL & ~E_NOTICE & ~E_WARNING & ~E_STRICT & ~E_DEPRECATED);
ini_set('display_errors',1);

$ignoreAuth = true; // signon not required!!

// Command line setup
if (defined('STDIN')) {
	parse_str(implode('&', array_slice($argv, 1)), $_GET);
}
 
if (empty($_GET['site'])) $_GET['site'] = 'default';

$docroot = dirname(dirname(__FILE__));
require_once("$docroot/interface/globals.php");
require_once("$docroot/library/wmt-v3/wmt.globals.php");
require_once("$docroot/library/OemrAD/oemrad.globals.php");

use OpenEMR\OemrAd\Smslib;
use OpenEMR\OemrAd\Twiliolib;

$start = trim(strip_tags($_GET['start']));
$facility = intval(trim(strip_tags($_GET['facility'])));
$type = strtoupper(trim(strip_tags($_GET['type'])));

// Validate input parameters
if (empty($type) || ($type != 'N' && $type != 'C')) { 
	$type = 'N';  // default to notification
}

if (!empty($facility) && ($facility < 1 || $facility > 32)) { 
	throw new \Exception("FATAL ERROR: invalid facility ($facility)");
}

if (empty($start)) {
	if ($type == 'N') $start = (empty($GLOBALS['SMS_NOTIFICATION_HOUR']))? 2 : $GLOBALS['SMS_NOTIFICATION_HOUR'];
	if ($type == 'C') $start = (empty($GLOBALS['SMS_CONFIRMATION_HOUR']))? '48:24' : $GLOBALS['SMS_CONFIRMATION_HOUR'];
}

// Check for multiple start times
if (strpos($start, ':') === false) {
	$start = array($start);
} else {
	$start = explode(':', $start);
}

// Validate start times
$ranges = array();
foreach ($start AS $hours) {
	if (!empty($hours) && (intval($hours) < 1 || intval($hours) > 72)) {
		throw new \Exception("FATAL ERROR: invalid prior to appointment start hours ($hours)");
	}

	// Calculate qualification range
	$start_hours = strtotime("+$hours hours");
	if ($start_hours === false) {
		throw new \Exception("FATAL ERROR: invalid start calculation");
	}
	$finish_hours = $start_hours + 3600; // add one hour
	
	// Store range
	$ranges[] = array(date('Y-m-d H:00:00',$start_hours), date('Y-m-d H:00:00',$finish_hours));
}

echo "\nRUN TYPE = ";
echo ($type == 'N') ? 'NOTIFICATION PROCESSING' : 'CONFIRMATION PROCESSING';
echo "\nRUN START = ";
echo date('Y-m-d H:i:s');
foreach ($ranges AS $range) {
	echo "\nDATE RANGE = " . $range[0] ." - ". $range[1];
}
echo "\n";

$invalid_cats = '';
//$cats_list = new wmt\Options('SMS_Exclude_Appt_Types');
//foreach ($cats_list->list AS $cat_data) {
//	if (!empty($invalid_cats)) $valid_cats .= ",";
//	$invalid_cats .= "'".$cat_data['title']."'";
//}
// CCM Billing(60), Medical Records(47), No Show(1)
$invalid_cats = "'60','47','1'"; 

//$valid_stats = '';
//$stat_list = new wmt\Options('SMS_Exclude_Appt_Statuses');
//foreach ($stat_list->list AS $stat_data) {
//	if (!empty($valid_stats)) $valid_stats .= ",";
//	$valid_stats .= "'".$sat_data['title']."'";
//}
$valid_confirm = "'-','VM','lm-verified','IV','unvins','LVM-unvins','UNCON-unvins','UNLVM-unvins','uncon-verified','[UVM]','unvm-verified','SMSN','SMSC','UVM','[UC]'";
$valid_notify = $valid_confirm . ",'APTCON-unvins','CON','con-verified'";

// Pull qualifying appointments
$sql = "SELECT pd.`pid`, pd.`phone_cell`, pd.`hipaa_allowsms`, ope.`pc_eventDate`, ope.`pc_startTime`, ope.`pc_eid` ";
$sql .= "FROM `patient_data` pd, `openemr_postcalendar_events` ope ";
$sql .= "WHERE pd.`pid` = ope.`pc_pid` AND ope.`pc_apptstatus` IN (";
if ($type == 'N') $sql .=  $valid_notify;
if ($type == 'C') $sql .=  $valid_confirm;
$sql .= ") AND ope.`pc_catid` NOT IN (". $invalid_cats .") ";
$sql_range = '';
foreach ($ranges AS $range) {
	if ($sql_range) $sql_range .= " OR ";
	$sql_range .= "( CONCAT(ope.pc_eventDate,' ',ope.pc_startTime) >= ? AND CONCAT(ope.pc_eventDate,' ',ope.pc_startTime) < ? )";
	$binds[] = $range[0];
	$binds[] = $range[1];
}
if (!empty($sql_range)) $sql .= "AND ( " . $sql_range . ") ";
if (!empty($facility) && $facility > 0) {
	$sql .= "AND ope.pc_facility = ? ";
	$binds[] = $facility;
}
//DEBUG $sql .= "AND pd.pid = '18212' ";
$result = sqlStatementNoLog($sql, $binds);

// Check if we are logging changes
$logging = sqlQuery("SHOW TABLES LIKE 'openemr_postcalendar_log'");

// Process reminders
//$sms = new wmt\Nexmo();
$sms = Smslib::getSmsObj();
while ($appt_data = sqlFetchArray($result)) {
	
	$template = 'appt_sms_notification';
	if ($type == 'C') {
		$template = 'appt_sms_confirmation';
		
		$sql = "SELECT `pc_eid` FROM `openemr_postcalendar_events` ";
		$sql .= "WHERE `pc_pid` IN (SELECT `pid` FROM `patient_data` WHERE `phone_cell` LIKE ?) ";
		$sql .= "AND `pc_apptstatus` IN ('SMSC','SMSN') ";
		$sql .= "AND `pc_eventDate` > NOW() ";
		$count = sqlStatementNoLog($sql, array($appt_data['phone_cell']));
		
		if (sqlNumRows($count) > 1) $template = 'appt_sms_unknown';
	}

	// Send appointment reminder
	$sms->apptReminder($appt_data['pc_eid'], $template, $type);
	
	// Slow down transmissions (< 1 per sec)
	sleep(2); // 2 seconds
	
	// Create journal of all event changes when log table present
	if ($logging) {
	    sqlStatement(
			"UPDATE `openemr_postcalendar_log` SET `current` = 0 WHERE `log_eid` = ?", array($appt_data['pc_eid'])
		);
		sqlStatement(
			"INSERT INTO `openemr_postcalendar_log` " .
			"(`current`,`log_eid`,`log_type`, `log_uid`,`log_catid`,`log_aid`,`log_pid`,`log_title`,`log_time`,`log_hometext`,`log_notetext`,`log_eventDate`,`log_duration`,`log_startTime`,`log_allday`,`log_facility`,`log_apptstatus`) " .
			"SELECT 1,`pc_eid`,'UPDATED',`pc_informant`,`pc_catid`,`pc_aid`,`pc_pid`,`pc_title`,`pc_time`,`pc_hometext`,`pc_notetext`,`pc_eventDate`,`pc_duration`,`pc_startTime`,`pc_alldayevent`,`pc_facility`,? " .
			"FROM `openemr_postcalendar_events` WHERE `pc_eid` = ?", array('SMS'.$type,$appt_data['pc_eid']) 
		);
	}

}


?>
