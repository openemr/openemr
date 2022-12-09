<?php
/** **************************************************************************
 *	PRINTAPPT.CLASS.PHP
 *	This file contains a print class for use with any print form
 *
 *  NOTES:
 *  1) __CONSTRUCT - uses the event id to retrieve data 
 *  2) GET - uses alternate selectors to find and return associated object
 * 
 *  @version 1.0
 *  @copyright Williams Medical Technologies, Inc.
 *  @author Rich Genandt <rgenandt@gmail.com>
 * 
 *************************************************************************** */

/** 
 * Provides a partial representation of the patient appointment record.
 * It does NOT include all of the fields associated with the core encounter 
 * data record(s) and should NOT be used for database updates.  It is intended 
 * for retrieval of partial appointment information primarily for display 
 * purposes (reports fnd fee sheets for example).
 *
 */
if(!isset($GLOBALS['fileroot'])) include_once('../../globals.php');
require_once($GLOBALS['srcdir'].'/wmt-v2/wmtstandard.inc');

if(!class_exists('wmtPrintAppt')) {

class wmtPrintAppt{
	public $pc_eid;
	public $pc_catid;
	public $pc_aid;
	public $pc_pid;
	public $pc_title;
	public $pc_time;
	public $pc_hometext;
	public $pc_comments;
	public $pc_eventDate;
	public $pc_endDate;
	public $pc_duration;
	public $pc_startTime;
	public $pc_endTime;
	public $pc_apptstatus;
	public $pc_facility;
	public $doctor_name;
	public $category_name;

	public $dolv;
	public $prev_enc;

	public $next_appt_dt;
	public $next_appt_time;
	public $next_appt_reason;
	public $next_appt_provider;
	public $next_appt_comment;
	
	// generated values - none in use currently
	
	/**
	 * Constructor for the 'encounter' print class which retrieves the requested 
	 * encounter information from the database.
	 * 
	 * @param int $id encounter id number 
	 * @return object instance of encounter print class
	 */
	public function __construct($id = false) {
		if(!$id) return false;

		$query = "SELECT * FROM openemr_postcalendar_events WHERE id =?";
		$data = sqlQueryNoLog($query, array($id));
	
		if ($data) {
			$this->pc_eid = $data['pc_eid'];
			$this->pc_catid = $data['pc_catid'];
			$this->pc_aid = $data['pc_aid'];
			$this->pc_pid = $data['pc_pid'];
			$this->pc_title = $data['pc_title'];
			$this->time = $data['pc_time'];
			$this->pc_hometext = $data['pc_hometext'];
			$this->pc_comments = $data['pc_comments'];
			$this->pc_eventDate = $data['pc_eventDate'];
			$this->pc_endDate = $data['pc_endDate'];
			$this->pc_duration = $data['pc_duration'];
			$this->pc_startTime = $data['pc_startTime'];
			$this->pc_endTime = $data['pc_endTime'];
			$this->pc_apptstatus = $data['pc_apptstatus'];
			$this->pc_facility = $data['pc_facility'];
		}
		else {
			throw new Exception('wmtPrintAppt::_construct - no event record with id ('.$this->id.').');
		}
		// Default facility to 3 if not set
		if(!$this->pc_facility_id) $this->pc_facility= 3;
		
		// preformat commonly used data elements	
		$signing_user = '';
		$this->doctor_name = 'No Provider Specified';
		if($this->pc_aid) {
  		$rrow = sqlQueryNoLog("SELECT id, lname, fname, mname, username ".
					"FROM users WHERE id=?", array($this->pc_aid));
  		if($rrow{'id'}) {
				$_mi = ' ';
				if(!empty($rrow{'mname'})) $_mi = ' '.$rrow{'mname'}.' ';
				$this->doctor_name = $rrow{'fname'}.$_mi.$rrow{'lname'};
  		}
		}

		$this->category_name = 'No Category Specified';
		if($this->pc_catid) {
  		$rrow = sqlQueryNoLog("SELECT pc_catname, pc_catdesc FROM ".
				"openemr_postcalendar_categories WHERE id=?", array($this->pc_catid));
			$this->category_name = $rrow{'pc_catname'};
		}

		// Try to get a date of the last visit 
		$mydate = date('Y-m-d');
		$sql = "SELECT date, encounter, form_id FROM forms WHERE pid=? ".
			"AND deleted=0 AND formdir='newpatient' AND date < ? ORDER BY date DESC";
		$rrow = sqlQuery($sql, array($data['pc_pid'], $mydate));
		if(isset($rrow['date'])) {
			if($row['date'] != '' && $row['date'] != '0000-00-00') {
				$this->dolv = substr($rrow['date'],0,10);
				$this->prev_enc = $row['encounter'];
			}
		}
	
		// Load any details for the next appointment
		$sql = "SELECT cal.pc_catid, cal.pc_aid, cal.pc_title, cal.pc_eventDate, ".
			"cal.pc_startTime, cal.pc_hometext, cat.pc_catname FROM ".
			"openemr_postcalendar_events AS cal LEFT JOIN ".
			"openemr_postcalendar_categories AS cat USING (pc_catid) WHERE pc_pid=? ".
			"AND pc_eventDate > ? ORDER BY pc_eventDate, pc_startTime ASC LIMIT 1";
		$appt = sqlQuery($sql, array($data['pc_pid'], $data['pc_eventDate']));
		$this->next_appt_dt = $appt{'pc_eventDate'};
		$this->next_appt_time = substr($appt{'pc_startTime'},0,5);
		$this->next_appt_reason = $appt{'pc_catname'};
		$this->next_appt_provider = $appt{'pc_aid'};
		$this->next_appt_comment = $appt{'pc_hometext'};
	}	

	/**
	 * Retrieve an appointment object by appointment id. Uses the base 
   * constructor for the 'appointment' print class to create and return 
   * the object.
	 * 
	 * @static
	 * @param int $appt appointment record encounter
	 * @return object instance of visit print appointment
	 */
	public static function getAppointment($appt='') {
		if($appt == '') return new wmtPrintAppt(0);
		
		return new wmtPrintAppt($appt);
	}

	/**
	 * Retrieve the most recent appointment. Uses the base constructor 
   * for the 'appointment' print class to create and return the object.
	 * 
	 * @static
	 * @param int $pid is the patient to locate
	 * @return object instance of apppointment print class
	 */
	public static function getMostRecent($pid) {
		if(!$pid) {
			throw new Exception('wmtPrintAppt::getMostRecent- no patient identifier provided.');
		}

		$this_date = date('Y-m-d');
		$this_time = date('H:i:s');
		$sql = "SELECT cal.pc_catid, cal.pc_aid, cal.pc_title, cal.pc_eventDate ".
			"FROM openemr_postcalendar_events AS cal WHERE pc_pid=? AND ".
			"pc_eventDate <= ? AND pc_startTime < ? ORDER BY ".
			"pc_eventDate, pc_startTime DESC LIMIT 1";
		$appt = sqlQuery($sql, array($data['pc_pid'], $this_date, $this_time));
		
		return new wmtPrintAppt($data['id']);
	}
	
}
                                            
}

?>
