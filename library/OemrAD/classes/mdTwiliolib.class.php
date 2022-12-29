<?php

namespace OpenEMR\OemrAd;

@include_once("../interface/globals.php");
@include_once($GLOBALS['srcdir']."/wmt-v3/wmt.globals.php");
@include_once($GLOBALS['srcdir']."/OemrAD/oemrad.globals.php");

use \wmt\Patient;
use \wmt\Template;
use \wmt\Appt;
use \wmt\Grab;
use OpenEMR\OemrAd\TwilioUtilitylib;

/**
 * Provides standardized processing for bidirectional sms messages.
 *
 * @package wmt
 * @subpackage twilio
 *
 * @version 1.0.0
 * @since 2021-01-30
 * 
 */
class Twiliolib {

	/** 
	 * Class variables
	 */
	private $account_sid;
	private $auth_token;
	private $from;
	private $to;
	private $content;
	private $site_url;

	/**
	 * Constructor for the 'SMS' class which generates all types 
	 * of sms messages sent to the Twilio servers.
	 */
	public function __construct($from='') {
		// Store "from" phone number
		if (empty($from)) $from = $GLOBALS['SMS_TWILIO_DEFAULT_FROM'];
		$this->from = preg_replace('/[^0-9]/', '', $from);
		
		// Retrieve the api information
		$this->account_sid = $GLOBALS['SMS_TWILIO_ACCOUNT_SID'];
		$this->auth_token = $GLOBALS['SMS_TWILIO_AUTH_TOKEN'];
		$this->site_url = $GLOBALS['SMS_TWILIO_SITE_URL'];

		return;
	}

	/* Check Required Details*/
	protected function checkDetails() {
		if (empty($this->auth_token))
			throw new \Exception("Twilio:construct - no 'auth_token' api key in Twilio config");

		if (empty($this->account_sid))
			throw new \Exception("Twilio:construct - no 'account_sid' api key in Twilio config");

		if (empty($this->from))
			throw new \Exception("Twilio:construct - no 'from' phone in Twilio config");
	}

	/**
	 * Abstract CURL usage.
	 *
	 * @param array $data	Array of parameters
	 * @return array 		Decoded results
	 * 
	 */
	protected function curl($url, $data) {
		// Force data object to array
		$data = $data ? (array) $data : $data;
		
		// Define header values
		$headers = [
		];
		
		// Set up client connection
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		//Set Auth Details
		if(!empty($this->account_sid) && !empty($this->auth_token)) {
			curl_setopt($ch, CURLOPT_USERPWD, $this->account_sid.':'.$this->auth_token);
		}
		
		// Specify the raw post data
		if ($data) {
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
		}
		
		// Send data
		$result = curl_exec($ch);
		$errCode = curl_errno($ch);
		$errText = curl_error($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		
		// Handle result
		//return $this->handle($result, $httpCode);
		return json_decode($result, true);
	}

	/**
	 * Package message data for transmission
	 *
	 * @param array $message The message parameters
	 * @return array
	 * 
	 */
	public function sendMessage(array $message) {
		$send_sms_url = 'https://api.twilio.com/2010-04-01/Accounts/'.$GLOBALS['SMS_TWILIO_ACCOUNT_SID'].'/Messages.json';

		$this->checkDetails();
		$response = $this->curl($send_sms_url, $message);
		
		return $response;
	}

	/**
	 * Process SMS response sent by the user to the SMS service
	 * in response to a message sent to the mobile device.
	 *
	 * @param int $toNumber application service number
	 * @param string $message content of the message
	 * @return string $msgid message identifier
	 * 
	 */
	public function smsTransmit($toNumber, &$content, $encoding='') {
		global $web_root;

		// Initialize
		$msgid = '';
		$error = '';
		$status = 0;
		
		// Set message parameters
		unset($data);
		$data = array();
		$data['From'] = '+'.$this->getPhoneNumber($this->from);
		$data['To'] = '+'.$this->getPhoneNumber(preg_replace('/[^0-9]/', '', $toNumber));
		$data['Body'] = $content;


		//Append Text
		$newContent = TwilioUtilitylib::appendExtraMessagePart($content, array(
			'rawToNumber' =>  $toNumber,
			'rawFromNumber' => $this->from,
			'toNumber' =>  $data['To'],
			'fromNumber' =>  $data['From']
		));

		if(!empty($newContent)) {
			$data['Body'] = $newContent;
		}

		$protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";  
		
		if(!empty($this->site_url)) {
			$curPageURL = $this->site_url .'/sms/twilio_listener.php?ListenerType=status_update'; 
			$data['StatusCallback'] = $curPageURL;
		}

		$msgStatus = '';

		// Send the message
		try {
			$response = $this->sendMessage($data);
			if(isset($response['sid']) && isset($response['status'])) {
				$sms_status = $response['status'];
				$status = $this->getMessageStatus($sms_status);
				$msgid = $response['sid'];
				$msgStatus = isset($response['status']) ? ucfirst($response['status']) : '';
			} else if(isset($response['message']) && isset($response['code'])) {
				$error = 'Message rejected [' .$response['status']. '] - ' .$response['message'];
			}
		} catch (\Exception $e) {
			$status = $e->getCode();
			$error = 'Message rejected [' .$status. '] - ' .$e->getMessage();
		}

		// Record error 
		if (!empty($error)) {
			error_log('wmtTwilio::smsTransmit - ' . $error);
		}
		
		$ret = array('msgid' => $msgid,
			'msgStatus' => $msgStatus,
		    'status' => $status,
		    'error' => $error
		);

		return $ret;
	}

	/**
	 * Get phone number in proper format
	 */
	function getPhoneNumber($pat_phone) {
		// Get phone numbers
		$msg_phone = $pat_phone;
		if(strlen($msg_phone) <= 10) {
			if (substr($msg_phone,0,1) != '1') $msg_phone = "1" . $msg_phone;
		}
		return $msg_phone;
	}

	/**
	 * Process SMS delivery notification sent by the SMS service when a
	 * previous message has been delivered to the mobile device.
	 *
	 * @param int $msgid message identifier 
	 * @param int $fromNumber mobile device number
	 * @param int $toNumber application service number
	 * @param string $timestamp Y-m-d H:i:s
	 * @param string $event description of event
	 *
	 */
	public function smsDelivery($fromNumber, $toNumber, $msgId, $timestamp, $clientRef, $msgStatus) {
		
		// Clean numbers
		$toNumber = preg_replace("/[^0-9]/", "", $toNumber);
		$fromNumber = preg_replace("/[^0-9]/", "", $fromNumber);
		//$msgStatus = strtoupper($msgStatus);
		$msgStatus = $this->getMessageStatus($msgStatus);
		
		// Validate time
		$delivered = (strtotime($timestamp) === false)? date('Y-m-d H:i') : date('Y-m-d H:i', strtotime($timestamp));
		
		
		// Retrieve original message
		$log_record = sqlQueryNoLog("SELECT * FROM `message_log` WHERE `msg_newid` LIKE ? AND `msg_to`=?", array($msgId, $toNumber));

		// Update existing record
		if (!empty($log_record['id'])) {
			sqlStatementNoLog("UPDATE `message_log` SET `delivered_time`=?, `msg_status`=?, `delivered_status`=? WHERE `id`=?", array($delivered, $msgStatus, $msgStatus, $log_record['id']));
		}
		
		return;
		
	}

	/*Get Message Status*/
	public function getMessageStatus($msgStatus) {
		if(!empty($msgStatus)) {
			//$msgStatus = strtoupper($msgStatus);
			//$msgStatus = 'MESSAGE_'.$msgStatus;
			$msgStatus = ucfirst($msgStatus);
		}

		return $msgStatus;
	}

	/**
	 * Process SMS response sent by the user to the SMS service
	 * in response to a message sent to the mobile device.
	 *
	 * @param int $msgId message identifier 
	 * @param int $fromNumber mobile device number
	 * @param int $toNumber application service number
	 * @param string $timestamp Y-m-d H:i:s
	 * @param string $event description of event
	 *
	 */
	public function smsReceived($fromNumber, $toNumber, $msgId, $timestamp, $message) {

		// Clean numbers
		$toNumber = preg_replace("/[^0-9]/", "", $toNumber);
		$fromNumber = preg_replace("/[^0-9]/", "", $fromNumber);

		// Find patient(s) by phone
		$test_number = (strlen($fromNumber) > 10) ? substr($fromNumber,1,10) : $fromNumber;
		$test_area = substr($test_number,0,3);
		$test_prefix = substr($test_number,3,3);
		$test_local = substr($test_number,6,4);
		$test_string = $test_area .'.?'. $test_prefix .'.?'. $test_local;

		$phoneSQl = '';
		$phoneSQl = " replace(replace(replace(replace(replace(replace(phone_cell,' ',''),'(','') ,')',''),'-',''),'/',''),'+','') REGEXP ? ";
		$binds[] = $test_string;

		$phoneSQl .= " OR replace(replace(replace(replace(replace(replace(secondary_phone_cell,' ',''),'(','') ,')',''),'-',''),'/',''),'+','') REGEXP ? ";
		$binds[] = "(^|,)(1)?($test_string)(,|$)";
			
		// Look for matches
		$pids = null;
		
		if(!empty($test_number)) {
			$result = sqlStatementNoLog("SELECT `pid` FROM `patient_data` WHERE ".$phoneSQl." ", $binds);
			if ($result) {
				while ($pat_data = sqlFetchArray($result)) {
					$pids[] = $pat_data['pid'];
				}
			}
		}
		
		// Unique patient found
		$pid = (count($pids) == 1)? $pids[0] : '';
			
		// Appears to be a confirmation message
		// Appears to be a confirmation message
		$test = strtoupper($message);
		if ($test == "'C'" || $test == '"C"' || substr($test,0,1) == 'C' || substr($test,0,1) == 'Y') $test = 'C';
		if ($test == "YES" || $test == "OK" || $test == "OKAY" || $test == "SI") $test = 'C';
		if ($test == "'R'" || $test == '"R"' || substr($test,0,1) == 'R' || substr($test,0,1) == 'N') $test = 'R';
		if ($test == "NO") $test = 'R';

		$newMsgId = "";
			
		if (count($pids) > 0 && ($test == 'C' || $test == 'R') ) {
			
			// Make pid string
			$pid_list = implode(',', $pids);
			
			/* Fetch appointment(s) for patient(s)	
			$sql = "SELECT `pc_eid`, `pc_pid` FROM `openemr_postcalendar_events` ";
			$sql .= "WHERE `pc_pid` IN (" .$pid_list. ") AND `pc_apptstatus` LIKE 'SMSC' ";
			$sql .= "AND `pc_eventDate` > NOW() ORDER BY `pc_eventDate` LIMIT 1";
			$appt = sqlStatementNoLog($sql);
			*/
			
			$sql = "SELECT ope.`pc_pid`, ope.`pc_eid` FROM `message_log` ml ";
			$sql .= "LEFT JOIN `openemr_postcalendar_events` ope ON ml.`eid` = ope.`pc_eid` and ml.`pid` = ope.`pc_pid` ";
			$sql .= "WHERE ml.`type` LIKE 'SMS' AND ml.`direction` LIKE 'out' AND ml.`eid` IS NOT NULL ";
			$sql .= "AND ml.`event` LIKE 'CONFIRM_REQUEST' AND ope.`pc_eventDate` >= DATE(NOW()) AND ope.`pc_eid` IS NOT NULL ";
			$sql .= "AND ope.`pc_pid` IS NOT NULL AND ope.`pc_apptstatus` LIKE 'SMSC' AND ml.`msg_to` = ? ";
			$appt = sqlStatementNoLog($sql, array($fromNumber));
			
			// Unique appointment found
			if (sqlNumRows($appt) == 1) {

				// Inbound confirmation for unique appointment
				$appt_data = sqlFetchArray($appt);
				$this->apptConfirm($appt_data['pc_eid'], $appt_data['pc_pid'], $fromNumber, $toNumber, $msgId, $timestamp, $message);
			
			} else {
				
				// Unable to match unique appointment
				//$message ='Appointment update received for unknown appointment';
				$newMsgId = $this->logSMS('SMS_RECEIVED', $toNumber, $fromNumber, $pid, $msgId, $timestamp, 'SMS_RECEIVED', $message, 'in', true);
				
			}
			
		} else {
		
			// Inbound message from unknown patient
			$newMsgId = $this->logSMS('SMS_RECEIVED', $toNumber, $fromNumber, $pid, $msgId, $timestamp, 'SMS_RECEIVED', $message, 'in', true);
		
		}
		
		if ($pid != "" && !empty($newMsgId)) {
			//SMS Utility
			TwilioUtilitylib::confirmApp(array(
				'pid' => $pid,
				'fromNumber' => $fromNumber,
				'reg_fromNumber' => $test_string,
				'msg_date' => $timestamp,
				'msg_id' => $newMsgId,
				'text' => $message
			));
		}
		
		return;
		
	}

	/**
	 * Send appointment notice SMS for appointment specified.
	 * A 'notice' is a send only used when appointment made.
	 *
	 * @param int $eid record identifier for the appointment
	 * @param int $template record identifier for the template
	 * @return string status of message transmission
	 */
	public function apptNotice($eid, $template) {

		// Save parameters
		$this->eid = $eid;
		
		// Validate appointment parameter
		if (empty($eid))
			throw new \Exception('wmtNexmo::ApptNotice - no appointment identifier provided');
		
		// Fetch appointment
		$appt = new Appt($eid);
		
		// Validate appointment date/time
		$appt_date = strtotime(substr($appt->pc_eventDate,0,10));
		if ($appt_date === false)
			throw new \Exception('wmtNexmo::ApptNotice - invalid appointment date/time in record');
		$appt_time = strtotime($appt->pc_startTime);
		if ($appt_time === false)
			throw new \Exception('wmtNexmo::ApptNotice - invalid appointment date/time in record');
			
		// Retrieve data
		$pat_data = Patient::getPidPatient($appt->pc_pid);
		
		// Verify if we should be sending SMS at all
		$toNumber = preg_replace("/[^0-9]/", '', $pat_data->phone_cell);
		if (strlen($toNumber) == 10) $toNumber = "1" . $toNumber;
		if (strlen($toNumber) != 11) $toNumber = '';
		if ($pat_data->hipaa_allowsms != 'YES' || empty($toNumber)) return false;
		
		// Validate template parameter
		if (empty($template))	
			throw new \Exception('wmtNexmo::ApptNotice - missing SMS template name');

		// Fetch template
		$template = Template::Lookup($template, $pat_data->language);
		
		// Fetch merge data
		$data = new Grab($pat_data->language);
		$data->loadData($appt->pc_pid, $appt->pc_aid, $appt->pc_facility, $appt->pc_eid);
		
		// Collect merge tag elements
		$elements = $data->getData();
		if ($appt->pc_alldayevent > 0) $elements['appt_time'] = "ALL DAY";
		
		// Perform data merge
		$template->Merge($elements);
		$content = $template->text_merged;
		
		// Transmit SMS message
		$result = $this->smsTransmit($toNumber, $content);
		$msgId = $result['msgid'];
		$msgStatus = $result['msgStatus'];
		
		// Do updates as appropriate
		if ($msgId) {
			$status = $msgStatus;
			sqlStatementNoLog("UPDATE `openemr_postcalendar_events` SET `pc_apptstatus` = ? WHERE `pc_eid` = ?", array('SMSN', $eid));
		} else {
			$status = 'SEND FAILED';
		}
		
		// Record message
		$this->logSMS('APPT_NOTICE', $toNumber, $this->from, $pat_data->pid, $msgId, null, $status, $content, 'out', false);

		return;
		
	}

	/**
	 * Send appointment notice SMS for appointment specified.
	 * A 'notice' is a send only used when appointment made.
	 *
	 * @param int $eid record identifier for the appointment
	 * @param int $template record identifier for the template
	 * @return string status of message transmission
	 */
	public function apptReminder($eid, $template, $type='N') {

		// Save parameters
		$this->eid = $eid;
		
		// Validate processing type
		if ($type != 'N' && $type != 'C') $type = 'N';  // default to reminder
		
		// Validate appointment parameter
		if (empty($eid))
			throw new \Exception('wmtNexmo::ApptReminder - no appointment identifier provided');
		
		// Fetch appointment
		$appt = new Appt($eid);
		
		// Validate appointment date/time
		$appt_date = strtotime($appt->pc_eventDate);
		if (!($appt_date))
			throw new \Exception('wmtNexmo::ApptReminder - invalid appointment date/time in record');
		$appt_time = strtotime($appt->pc_startTime);
		if (!($appt_time))
			throw new \Exception('wmtNexmo::ApptReminder - invalid appointment date/time in record');
			
		// Retrieve data
		$pat_data = Patient::getPidPatient($appt->pc_pid);
		
		// Verify if we should be sending SMS at all
		$toNumber = preg_replace("/[^0-9]/", '', $pat_data->phone_cell);
		if (strlen($toNumber) == 10) $toNumber = "1" . $toNumber;
		if (strlen($toNumber) != 11) $toNumber = '';
		if ($pat_data->hipaa_allowsms != 'YES' || empty($toNumber)) return false;
		
		// Validate template parameter
		if (empty($template))	
			throw new \Exception('wmtNexmo::ApptNotice - missing SMS template name');

		// Fetch template
		$template = Template::Lookup($template, $pat_data->language);
		
		// Fetch merge data
		$data = new Grab($pat_data->language);
		$data->loadData($appt->pc_pid, $appt->pc_aid, $appt->pc_facility, $appt->pc_eid);
		
		// Collect merge tag elements
		$elements = $data->getData();
		if ($appt->pc_alldayevent > 0) $elements['appt_time'] = "ALL DAY";
		
		// Perform data merge
		$template->Merge($elements);
		$content = $template->text_merged;
		
		// Transmit SMS message
		$result = $this->smsTransmit($toNumber, $content);
		$msgId = $result['msgid'];
		$msgStatus = $result['msgStatus'];
		
		// Do updates as appropriate
		if ($msgId) {
			$status = $msgStatus;
			if ($type == 'C') { // only change status for confirmation request
				sqlStatementNoLog("UPDATE `openemr_postcalendar_events` SET `pc_apptstatus` = ? WHERE `pc_eid` = ?", array('SMS'.$type, $eid));
			}
		} else {
			$status = 'SEND FAILED';
		}
		
		// Record message
		$event = ($type == 'N')? 'APPT_REMINDER' : 'CONFIRM_REQUEST';
		$this->logSMS($event, $toNumber, $this->from, $pat_data->pid, $msgId, null, $status, $content, 'out', false);

		return;
		
	}

	/**
	 * Process appointment confirmation associated with the provided refid.
	 *
	 * @param int $eid record identifier for the appointment
	 * @param int $template record identifier for the template
	 * @return string status of message transmission
	 */
	public function apptConfirm($eid, $pid, $fromNumber, $toNumber, $msgId, $timestamp, $message) {
		
		// Save parameters
		$this->eid = $eid;
		$this->pid = $pid;
		
		// Get patient language
		$pat_data = Patient::getPidPatient($pid);
		$lang = (empty($pat_data->language))? 'English' : $pat_data->language;
		
		// Log received record
		$this->logSMS('CONFIRM_RESPONSE', $toNumber, $fromNumber, $pid, $msgId, $timestamp, 'MESSAGE_RECEIVED', $message, 'in', true);
		
		// Fetch appointment
		$appt = new Appt($eid);
		
		// Validate appointment date/time
		$appt_date = strtotime(substr($appt->pc_eventDate, 0, 10));
		if ($appt_date === false)
			throw new \Exception('wmtNexmo::ApptNotice - invalid appointment date/time in record');
		$appt_time = strtotime($appt->pc_startTime);
		if ($appt_time === false)
			throw new \Exception('wmtNexmo::ApptNotice - invalid appointment date/time in record');
			
		// Process response
		$response = strtoupper(substr($message,0,1));
		if ($appt->pc_apptstatus != 'SMSC' && $appt->pc_apptstatus != 'SMSN') {
			
			// Process confirmation
			$response = '';
			$event = 'INVALID_STATUS';
 			$template = Template::Lookup('appt_sms_invalid', $lang);
 			
		} elseif ( $response == 'C') {
			
			// Process confirmation
			$event = 'APPT_CONFIRMED';
 			$template = Template::Lookup('appt_sms_confirmed', $lang);
 			
		} elseif ( $response == 'R' ) {

			// Process reschedule request
			$event = 'APPT_RESCHEDULE';
 			$template = Template::Lookup('appt_sms_reschedule', $lang);
		
		} else {
			
			// Process invalid response
			$response = '';
			$event = 'INVALID_RESPONSE';
 			$template = Template::Lookup('appt_sms_reject', $lang);
			
		}
		
		// Retrieve data
		$pat_data = Patient::getPidPatient($pid);
		
		// Fetch merge data
		$data = new Grab($pat_data->language);
		$data->loadData($appt->pc_pid, $appt->pc_aid, $appt->pc_facility, $appt->pc_eid);
		
		// Collect merge tag elements
		$elements = $data->getData();
		if ($appt->pc_alldayevent > 0) $elements['appt_time'] = "ALL DAY";
		
		// Perform data merge
		$template->Merge($elements);
		$content = $template->text_merged;
		
		// Set message parameters
		$message = array();
		$message['to'] = preg_replace('/[^0-9]/', '', array($fromNumber));
		$message['from'] = preg_replace('/[^0-9]/', '', $this->from);
		$message['content'] = $template->text_merged;
		
		// Transmit SMS message (use fromNumber since responding)
		$result = $this->smsTransmit($fromNumber, $content);
		$msgId = $result['msgid'];
		$msgStatus = $result['msgStatus'];
		
		// Do updates as appropriate
		if ($msgId) {
			$status = $msgStatus;
			
			// Confirmed
			if ($response == 'C') {
				sqlStatementNoLog("UPDATE `openemr_postcalendar_events` SET `pc_apptstatus` = ? WHERE `pc_eid` = ?", array('CON', $eid));
			}
			
			// Reschedule
			if ($response == 'R') {
 				// Update appointment record status 
				sqlStatementNoLog("UPDATE `openemr_postcalendar_events` SET `pc_apptstatus` = ? WHERE `pc_eid` = ?", array('SMSR', $eid));
			
	 			// Create an internal message 
				$note = "\n" . $pat_data->format_name ." (pid: ". $pid .") ";
				$note .= "has requested that their appointment for ";
				$note .= strftime("%A, %B %e, %G", $appt_date) . " at ";
				$note .= ($appt->pc_alldayevent > 0) ? "ALL DAY" : date('h:ia', $appt_time);
				$note .= " be cancelled and rescheduled.";
				$date = date('Y-m-d H:i:s');
	 			sqlInsert("INSERT INTO `pnotes` SET `pid`=?, `body`=?, `date`=?, `user`='SYSTEM', `groupname`='Default', `activity`=1, `authorized`=0, `title`='ReSchedule' , `assigned_to`='GRP:appt_cancel', `message_status`='New'", array($pid, $note, $date));
			}
		} else {
			$status = 'SEND FAILED';
		}
		
		// Record message (sending to fromNumber since it is a response)
		$this->logSMS($event, $fromNumber, $this->from, $pat_data->pid, $msgId, null, $status, $content, 'out', false);

		return;
		
	}
	
	
	/**
	 * Send portal lab notice SMS for new results.
	 *
	 * @param int $template record identifier for the template
	 * @return string status of message transmission
	 */
	public function labNotice($pid, $template) {
		
		// Get patient
		$pat_data = Patient::getPidPatient($pid);
		
		// Verify if we should be sending SMS at all
		$toNumber = preg_replace("/[^0-9]/", '', $pat_data->phone_cell);
		if (strlen($toNumber) == 10) $toNumber = "1" . $toNumber;
		if (strlen($toNumber) != 11) $toNumber = '';
		if ($pat_data->hipaa_allowsms != 'YES' || empty($toNumber)) return false;
		
		// Validate template parameter
		if (empty($template))	
			throw new \Exception('wmtNexmo::LabNotice - missing SMS template name');

		// Fetch template
		$template = Template::Lookup($template, $pat_data->language);
		
		// Fetch merge data
		$data = new Grab($pat_data->language);
		$data->loadData($pat_data->pid, $pat_data->providerID);
		
		// Collect merge tag elements
		$elements = $data->getData();
		
		// Perform data merge
		$template->Merge($elements);
		$content = $template->text_merged;
		
		// Transmit SMS message
		$result = $this->smsTransmit($toNumber, $content);
		$msgId = $result['msgid'];
		$msgStatus = $result['msgStatus'];

		// Record message
		$this->logSMS('LAB_NOTICE', $toNumber, $this->from, $pat_data->pid, $msgId, null, $msgStatus, $content, 'out', false);

		return;
		
	}

	/**
	 * Queue an SMS for transmitting through the background process.
	 *
	 * @param int $pid record identifier for the patient
	 * @param string $to phone number to send to
	 * @pamam string $msg message contents
	 * @param string $type of message
	 * @param string $status default of 'queued' but can set another if necessary
	 * @return boolean status of queue entry
	 */
	public function queueSMS($pid, $msg, $status = 'Queued') {
	    
	    // Validate patient id parameter
	    if (empty($pid))
	        throw new \Exception('Twilio::queueSMS - no patient identifier provided');
	    
        // Validate patient id parameter
        if (empty($msg))
	            throw new \Exception('Twilio::queueSMS - no message content provided');
        
        self::logSMS('SMS Blast', $this->to, $this->from, $pid, '', '', $status, $msg, 'out', true);
	                    
        return;
	                    
	}

	/**
	 * Create the SMS text for appointment and template specified.
	 * Intended to be used for things like the blast where the messages are all queued
	 * to be processed and throttled in the background.
	 *
	 * @param int $eid record identifier for the appointment
	 * @param int $template record identifier for the template
	 * @return string message text
	 */
	public function createSMSText($eid='', $pid='', $template='', $raw = FALSE) {
	    
	    // Save parameters
	    $this->eid = $eid;
	    
	    if($pid) {
	        $pat_data = Patient::getPidPatient($pid);
	    } else {
	       // Validate appointment parameter
	       if (empty($eid))
	           throw new \Exception('Twilio::ApptReminder - no appointment identifier provided');
	       $appt = new Appt($eid);
	       $pid = $appt->pc_pid;
	       $pat_data = Patient::getPidPatient($pid);
	    }
	    
        // Retrieve data
        $this->pat_data = $pat_data;
        
        // Fetch merge data
        $data = new Grab($pat_data->language);

        $data->loadAppointment($eid);
        $data->loadPatient($pid, $pat_data);
        $data->loadData(NULL, $data->pc_aid, $data->pc_facility);
	                
        // Verify if we should be sending SMS at all
        $toNumber = preg_replace("/[^0-9]/", '', $pat_data->phone_cell);
        if (strlen($toNumber) == 10) $toNumber = "1" . $toNumber;
        if (strlen($toNumber) != 11 && strlen($toNumber) != 12) $toNumber = '';
        if ($pat_data->hipaa_allowsms != 'YES' || empty($toNumber)) return false;
        $this->to = $toNumber;
	                
        // Validate template parameter
        if (empty($template))
             throw new \Exception('Twilio::ApptNotice - missing SMS template name');
	                    
        // Fetch template
        $template = Template::Lookup($template, $pat_data->language);
	                    
        // Collect merge tag elements
        $elements = $data->getData();
        if ($appt->pc_alldayevent > 0) $elements['appt_time'] = "ALL DAY";
                   
        // Perform data merge
        $template->Merge($elements, $raw);
        $content = $template->text_merged;
                    
        return $content;
	                    
	}

	/**
	 * The 'logSMS' method stores a copy of the messages which are exchanged
	 * along with any result parameters which may be returned.
	 */
	public function logSMS($event, $toNumber, $fromNumber, $pid, $msgId, $timestamp, $msg_status, $message, $direction='in', $active=true, $raw_data='') {

		// Create log entry
		$binds = array();
		$binds[] = $event;
		$binds[] = ($active)? '1' : '0';
		$binds[] = $direction;
		$binds[] = 'Pro-Care Bi-Directional';
		$binds[] = $_SESSION['authUserID'];
		$binds[] = (empty($pid))? $this->pid : $pid;
		$binds[] = (empty($this->eid)) ? null : $this->eid;
		$binds[] = $toNumber;
		$binds[] = $fromNumber;
		$binds[] = $msgId; // message id of current message
		$binds[] = (empty($timestamp)) ? date('Y-m-d H:i:s') : $timestamp;
		$binds[] = $msg_status;
		$binds[] = $message;
		$binds[] = $raw_data;

		// Store log record
		$sql = "INSERT INTO `message_log` SET ";
		$sql .= "type='SMS', event=?, activity=?, direction=?, gateway=?, userid=?, pid=?, eid=?, msg_to=?, msg_from=?, msg_newid=?, msg_time=?, msg_status=?, message=?, `raw_data`=?";
		//sqlStatementNoLog($sql, $binds);
		return sqlInsert($sql, $binds);
	}
}