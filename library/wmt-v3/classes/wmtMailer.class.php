<?php
/** **************************************************************************
 *	EMAIL.CLASS.PHP
 *
 *	Copyright (c)2016 - Medical Technology Services <MDTechSvcs.com>
 *
 *	This program is free software: you can redistribute it and/or modify it under the 
 *  terms of the GNU General Public License as published by the Free Software Foundation, 
 *  either version 3 of the License, or (at your option) any later version.
 *
 *	This program is distributed in the hope that it will be useful, but WITHOUT ANY
 *	WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
 *  PARTICULAR PURPOSE. DISTRIBUTOR IS NOT LIABLE TO USER FOR ANY DAMAGES, INCLUDING 
 *  COMPENSATORY, SPECIAL, INCIDENTAL, EXEMPLARY, PUNITIVE, OR CONSEQUENTIAL DAMAGES, 
 *  CONNECTED WITH OR RESULTING FROM THIS AGREEMENT OR USE OF THIS SOFTWARE.
 *
 *	See the GNU General Public License <http://www.gnu.org/licenses/> for more details.
 *
 *  @package wmt
 *  @subpackage email
 *  @version 1.0.0
 *  @category Email Base Class
 *  @copyright Medical Technology Services
 *  @author Ron Criswell <ron.criswell@MDTechSvcs.com>
 *
 ******************************************************************************************** */


/**
 * All new classes are defined in the WMT namespace
 */
namespace wmt;

/**
 * Must have access to the PHPMailer class
 */
if (!class_exists('PHPMailer'))
	require_once ($GLOBALS['srcdir'] . "/classes/class.phpmailer.php");

/**
 * Provides standardized processing for email messages.
 *
 * @package wmt
 * @subpackage email
 */
class Mailer extends \PHPMailer {
	
	/**
	 * Constructor for the 'email' class which generates all types 
	 * of email messages sent to external email servers.
	 *
	 * @return object instance of form class
	 * 
	 */
	public function __construct() {
		global $HTML_CHARSET;
		$this->CharSet = $HTML_CHARSET;
		switch($GLOBALS['EMAIL_METHOD']) {
			case "PHPMAIL" :
				$this->Mailer = "mail";
				break;
			case "SMTP" :
				global $SMTP_Auth;
				$this->Mailer = "smtp";
				$this->SMTPAuth = $SMTP_Auth;
				$this->Host = $GLOBALS['SMTP_HOST'];
				$this->Port = $GLOBALS['SMTP_PORT'];
				if ($GLOBALS['SMTP_USER']) {
					$this->Username = $GLOBALS['SMTP_USER'];
					$this->Password = $GLOBALS['SMTP_PASS'];
					$this->SMTPAuth = false;
				}
				break;
			case "SENDMAIL" :
				$this->Mailer = "sendmail";
				break;
		}
	}

	/**
	 * Generate an appointment email using the data provided.
	 *
	 * @param array $elements data elements to be inserted
	 * @param int $id record identifier for the template
	 * @return string merged template content
	 */
	public function MailAppt(&$data) {
		if (is_array($data) === false)
			throw new \Exception('wmtEmail::MailAppt - data parameters must be an array');
		
		if (!$data['patient'] || !$data['email'] || !$data['scheduled'])	
			throw new \Exception('wmtEmail::MailAppt - missing required data parameters');

		// grab the parameters
		$patient = $data['patient'];
		$email = $data['email'];
		$scheduled = $data['scheduled'];
		$duration = $data['duration'];
		$location = $data['facility'];
		$summary = $data['summary'];
		$text_body = $data['text'];
		$html_body = $data['html'];
			
		// default missing parameters
		if (!$summary) $summary = $this->Subject;
		if (!$text_body) $text_body = $summary; 
		if (!$html_body) $html_body = $text_body;
		
		// format times		
		$dtstart = gmdate("Ymd\THis\Z", $scheduled);
		$dtend = gmdate("Ymd\THis\Z", $scheduled + $duration);
		$dttoday = gmdate("Ymd\THis\Z");
		$status = "CONFIRMED";
		
		// generate uid
		list($user, $domain) = explode('@', $this->From);
		$cal_uid = DATE('Ymd').'T'.DATE('His')."-".RAND().$domain;

		// generate mime boundry
		$outer_boundary = md5(time());
		$inner_boundary = md5(time()+100);
		
		// important parameters
		$this->IsHTML(true);
		$this->ContentType = 'multipart/mixed; boundary='.$outer_boundary;

  		$message .= "--$outer_boundary\n";
  		$message .= "Content-Type: multipart/alternative; boundary=".$inner_boundary."\n\n";
  		
  		$message .= "--$inner_boundary\n";
  		$message .= "Content-Type: text/plain; charset=UTF-8; format=flowed; delsp=yes\n";
  		$message .= "Content-Transfer-Encoding: 7bit\n\n";
  		$message .= $text_body . "\n\n";
  		
  		$message .= "--$inner_boundary\n";
  		$message .= "Content-Type: text/html; charset=UTF-8\n";
    	$message .= "Content-Transfer-Encoding: quoted-printable\n\n";
    	$message .= $html_body . "\n\n";
    	 
    	$message .= "--$inner_boundary\n";
		
		// add calendar content data
		$ical = "BEGIN:VCALENDAR\n";
		$ical .= "VERSION:2.0\n";
		$ical .= "METHOD:REQUEST\n";
		$ical .= "BEGIN:VEVENT\n";
		$ical .= "DTSTART:$dtstart\n";
		$ical .= "DTEND:$dtend\n";
		$ical .= "DTSTAMP:$dttoday\n";
		$ical .= "ORGANIZER;ROLE=REQ-PARTICIPANT;PARTSTAT=ACCEPTED;CN=".$this->FromName.":MAILTO:".$this->From."\n";
		$ical .= "UID:$cal_uid\n";
		$ical .= "ATTENDEE;CUTYPE=INDIVIDUAL;ROLE=REQ-PARTICIPANT;PARTSTAT=ACCEPTED;RSVP=FALSE;CN=$patient:MAILTO:$email\n";
		$ical .= "DESCRIPTION:$summary\n";
		if ($location) $ical .= "LOCATION:$location\n";
		$ical .= "SEQUENCE:0\n";
		$ical .= "STATUS:CONFIRMED\n";
		$ical .= "SUMMARY:".$this->Subject."\n";
		$ical .= "BEGIN:VALARM\n";
		$ical .= "TRIGGER:-PT60M\n";
		$ical .= "ACTION:DISPLAY\n";
		$ical .= "DESCRIPTION:Reminder\n";
		$ical .= "END:VALARM\n";
		$ical .= "END:VEVENT\n";
		$ical .= "END:VCALENDAR\n";

		$message .= "Content-Type: text/calendar; method=REQUEST; charset=utf-8\n";
		$message .= "Content-Transfer-Encoding: 7bit\n\n";
		$message .= $ical;
		
		$message .= "\n--$inner_boundary--\n";
		$message .= "--$outer_boundary\n";
		$message .= "Content-Type: application/ics; name='meeting.ics'\n";
		$message .= "Content-Disposition: attachment; filename='meeting.ics\n";
		$message .= "Content-Transfer-Encoding: 7bit\n\n";
		$message .= $ical;
		$message .= "--$outer_boundary--\n";
		
		$this->Body = $message;
	}
}
