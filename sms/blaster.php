<?php
/** **************************************************************************
 *	sms/reminder.php
 *
 *	Copyright (c)2019 - Williams Medical Technologies (williamsmedtech.com)
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
 *  @copyright Williams Medical Technologies, Inc.
 *  @author Rich Genandt <rich@williamsmedtech.net>
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
 
if(empty($_GET['site'])) $_GET['site'] = 'default';
if(!isset($_GET['max_to_process'])) $_GET['max_to_process'] = '';
$max_to_process = intval(strip_tags($_GET['max_to_process']));

$docroot = dirname(dirname(__FILE__));
require_once("$docroot/interface/globals.php");
require_once("$docroot/library/wmt-v3/wmt.globals.php");
require_once("$docroot/library/OemrAD/oemrad.globals.php");

use OpenEMR\OemrAd\Smslib;
use OpenEMR\OemrAd\Twiliolib;

echo "\nSMS Blast Background Process Started - ";
echo date('Y-m-d H:i:s');
echo "\n";

$queue_drop_statuses = array(6, 7, 22, 29);

// READ THE MESSAGE LOG FOR QUEUED MESSAGES
$sql = 'SELECT * FROM `message_log` WHERE `msg_status` = "Queued" AND `type` = "SMS" AND ' .
    '`activity` = 1 AND `event` = "SMS Blast"';
if($max_to_process > 0) $sql .= ' LIMIT ' . $max_to_process;
$result = sqlStatementNoLog($sql);

// CHECK IF WE ARE LOGGING CHANGES - NOT ALWAYS APPLICABLE FOR A BLAST
// $logging = sqlQuery("SHOW TABLES LIKE 'openemr_postcalendar_log'");
$logging = FALSE;

// PROCESS BLAST QUEUE
//$sms = new wmt\Nexmo();
$sms = Smslib::getSmsObj();
while ($msg = sqlFetchArray($result)) {
    $result = $sms->smsTransmit($msg{'msg_to'}, $msg{'message'}, 'text');
    $msgId = $result['msgid'];
    $status = $result['status'];
    $activity = 1;
    $error = '';
    // UPDATE THE MESSAGE LOG AS APPROPRIATE
    if ($msgId) {
        $status = 'MESSAGE_SENT';
        $activity = 0;
        // MAYBE WE NEED TO LOG THIS ON THE APPOINTMENT RECORD ALSO?
        // sqlStatementNoLog("UPDATE `openemr_postcalendar_events` SET `pc_apptstatus` = ? WHERE `pc_eid` = ?", array('SMSN', $eid));
    } else {
        $status = 'Queued';
    }
    // THESE STATUSES NEED TO DROP THE MESSAGE FROM THE ACTIVE QUEUE
    if(in_array($status, $queue_drop_statuses)) {
        $status = 'MESSAGE_UNDELIVERABLE';
        if($error = $result['error'];
    }
    
    // UPDATE THE MESSAGE LOG
    $sql = 'UPDATE `message_log` SET `msg_status` = ?, `msg_newid` = ?, `msg_time` = NOW(), ' .
        '`activity` = ? WHERE `id` = ?';
    $binds = array($status, $msgId, $activity, $msg{'id'});
    if($error) {
        $sql = 'UPDATE `message_log` SET `msg_status` = ?, `msg_newid` = ?, `msg_time` = NOW(), ' .
            '`activity` = ?, `delivered_status` = ? WHERE `id` = ?';
        $binds = array($status, $msgId, $activity, substr($error, 0, 254), $msg{'id'});
    }
    sqlStatement($sql, $binds);
	sleep(2);
	
	// DO A JOURNAL ENTRY FOR LOGGING WHEN PRESENT / NECESSARY
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
