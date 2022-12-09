<?php
/** **************************************************************************
 *	wmtGrap.class.php
 *
 *	Copyright (c)2019 - Medical Technology Services <MDTechSvcs.com>
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
 *  @version 1.0
 *  @copyright Medical Technology Services
 *  @author Ron Criswell <ron.criswell@MDTechSvcs.com>
 *
 ******************************************************************************************** */

/**
 * All new classes are defined in the WMT namespace
 */
namespace wmt;

/**
 * Make sure standard utilities are loaded
 */
$docroot = dirname( dirname( __FILE__ ) );
require_once("$docroot/wmt.globals.php");

/**
 * Provides standardized processing for appointment records.
 *
 * @package wmt
 * @subpackage utility
 * 
 */
class Grab {
	private $language = 'Patient preferred language';
	
	private $pat_id = 'Patient identifier';
	private $pat_ext_id = 'Patient External Id';
	private $pat_full = 'Patient full name';
	private $pat_first = 'Patient first name';
	private $pat_last = 'Patient last name';
	private $pat_phone = 'Patient mobile number';
	private $pat_email = 'Patient email address';
	private $pat_address = 'Patient full address';
	private $pat_dob = 'Patient date of birth';
	
	private $doc_id = 'Provider identifier';
	private $doc_full = 'Provider full name';
	private $doc_first = 'Provider first name';
	private $doc_last = 'Provider last name';
	private $doc_username = 'Provider username';
	
	private $fac_id = 'Facility identifier';
	private $fac_full = 'Facility full name';
	private $fac_address = 'Facility full address';
	private $fac_phone = 'Facility primary phone';
	private $fac_email = 'Facility email address';
	
	private $appt_id = 'Appointment identifier';
	private $appt_type = 'Appointment type';
	private $appt_title = 'Appointment title';
	private $appt_date = 'Appointment date';
	private $appt_time = 'Appointment start time';
	private $appt_ends = 'Appointment ends time';
	private $appt_full = 'Long date format';
	private $appt_minutes = 'Appointment minutes';
	
	private $now_date = 'Current date';
	private $now_time = 'Current time';
	private $now_full = 'Long date format';
	
	private $portal_url = 'Portal URL Address';
	private $portal_pwd = 'Portal Password (special)';
	private $portal_user = 'Portal Username (special)';
	
	private $signed = 'Context Dependent Signature';
	private $created = 'Context Dependent Date';
	
	// FOR INTERNAL POINTING TO CUT DOWN ON RECORD READS
	public $pc_facility = '';
	public $pc_aid = '';
	// TO SET SOME CLINICAL DATA OUTSIDE THE CLASS - PROBABLY NEED TO EXTEND THIS CLASS WITH
	// OTHER SPECIFIC CLASSES TO DO THIS WHEN/IF IT REALLY GETS USED
	public $absence = 'Absence or Release Date';
	public $dictation = 'Notes regarding absence/release';
	public $instructions = 'Patient Instructions';

	public $all_future_appointments = 'All Future Appointments';

	public $zm_provider_url = 'Zoom Meeting Provider Url';
	public $zm_patient_url = 'Zoom Meeting Patient Url';
	public $zm_id = 'Zoom Meeting Id';
	public $zm_password = 'Zoom Meeting Password';
	public $zm_short_provider_url = 'Zoom Meeting Short Provider Url';
	public $zm_short_patient_url = 'Zoom Meeting Short Patient Url';
	
	/**
	 * Constructor for the 'wmtGrab' class which initializes a simple
	 * data object for use with template merge functions.
	 *
	 * @return object instance of wmtGrab class
	 * 
	 */
	public function __construct( $lang='English') {
		// Clear initial values
		foreach (get_object_vars($this) AS $key => $value) {
			$this->$key = '';
		}

		// Dates in the language
		$now = strtotime('NOW');
		switch ($lang) {
		    case 'Spanish':
		    	$this->language = 'Spanish';
		        setlocale(LC_TIME, array('esm','es_ES.utf8') );
				$this->now_full = strftime("%A, %e de %B %G", $now);
				$this->now_date = strftime("%d/%m/%G", $now);
				$this->now_time = strftime("%H:%M", $now);
				setlocale(LC_TIME, '');
		        break;
		    default: // system setting
		    	$this->language = 'English';
		        $this->now_full = strftime("%A, %B %e, %G", $now);
				$this->now_date = strftime("%m/%d/%G", $now);
				$this->now_time = strftime("%I:%M %p", $now);
		        break;
		}
			
		// Set portal address
		$this->portal_url = $GLOBALS['portal_onsite_address'];
		
		return;
	}

	/**
	 * Retrieve the 'patient' data for the wmtGrab class for use in the
	 * data object used with template merge functions.
	 *
	 * @param string pid  - patient record identifier
	 * @throws \Exception - not found 
	 * 
	 */
	public function loadPatient( $pid = false, $patient = FALSE ) {
		if ( !$pid ) $pid = $_SESSION['pid'];
		
		if ( !$pid ) {
			throw new \Exception("wmtGrab::loadPatient - no patient identifier provided.");
		}
		
		if(!is_object($patient)) {
		  // Retrieve patient record
		  $patient = Patient::getPidPatient($pid);
		}
		if (!$patient || empty($patient->id)) {
			throw new \Exception("wmtGrab::loadPatient - no patient record found.");
		}
			
		// Store patient information
		$this->pat_id = $patient->pid;
		$this->pat_ext_id = $patient->pubpid;
		$this->pat_full = $patient->fname;
		if (!empty($patient->mname)) $this->pat_full .= ' '.$patient->mname;
		$this->pat_full .= ' '.$patient->lname;
		$this->pat_first = $patient->fname;
		$this->pat_last = $patient->lname;
		$this->pat_phone = $patient->phone_cell;
		$this->pat_address = $patient->street;
		$this->pat_dob = $patient->DOB;
		if ($patient->street2) $this->pat_address .= ', '.$patient->street2;
		if ($patient->city) $this->pat_address .= ', '.$patient->city;
		if ($patient->state) $this->pat_address .= ' '.$patient->state;
		if ($patient->postal_code) $this->pat_address .= ', '.$patient->postal_code;
		$this->pat_email = $patient->email;
    if(!isset($GLOBALS['wmt::use_email_direct'])) $GLOBALS['wmt::use_email_direct'] = '';
		if($GLOBALS['wmt::use_email_direct']) $this->pat_email = $patient->email_direct;
		
	}

	/**
	 * Retrieve the 'provider' data for the wmtGrab class for use in the
	 * data object used with template merge functions.
	 *
	 * @param string aid  - user record identifier
	 * @throws \Exception - not found 
	 * 
	 */
	public function loadProvider( $aid = false ) {
		if ( !$aid ) {
			throw new \Exception("wmtGrab::loadProvider - no provider identifier provided.");
		}
		
		// Retrieve provider record
		$provider = new Provider($aid);
		if (!$provider || empty($provider->id)) {
			throw new \Exception("wmtGrab::loadProvider - no provider record found.");
		}
			
		// Store provider information
		$this->doc_id = $provider->id;
		$this->doc_username = $provider->username;
		if (empty($provider->lname)) {
			$this->doc_full = 'your provider';			
		} else {
			$this->doc_full = $provider->fname;
			if (!empty($provider->mname)) $this->doc_full .= ' '.$provider->mname;
			$this->doc_full .= ' '.$provider->lname;
		}
		$this->doc_first = $provider->fname;
		$this->doc_last = $provider->lname;
		
	}
	
	/**
	 * Retrieve the 'visit' data for the wmtGrab class for use in the
	 * data object used with template merge functions.
	 *
	 * @param string eid  - encouunter identifier
	 * @throws \Exception - not found
	 *
	 */
	public function loadVisit( $eid = false, $visit = FALSE ) {
	    if ( !$eid && !$visit ) {
	        throw new \Exception("wmtGrab::loadVisit - no encounter identifier provided.");
	    }
	    
	    if(is_object($visit)) {
	        $this->signed = $visit->provider_full;
	        $this->created = oeFormatShortDate($visit->encounter_date);
	    } else {
	       $visit = Encounter::getEncounter($eid);
	       if (!$visit || empty($visit->id)) {
	           throw new \Exception("wmtGrab::loadVisit - no encounter record found.");
	       }
	       // WOULD HAVE TO SET THE SIGNATURE YET
	       $this->signed = $visit->provider_id;
	       $this->created = oeFormatShortDate(substr($visit->date, 0, 10));
	       
	    }
	    
	}
	
	/**
	 * Retrieve the 'facility' data for the wmtGrab class for use in the
	 * data object used with template merge functions.
	 *
	 * @param string fid  - facility record identifier
	 * @throws \Exception - not found 
	 * 
	 */
	public function loadFacility( $fid = false ) {
		if ( !$fid ) {
			throw new \Exception("wmtGrab::loadFacility - no facility identifier provided.");
		}
		
		// Retrieve facility record
		$facility = new Facility($fid);
		if (!$facility || empty($facility->id)) {
			throw new \Exception("wmtGrab::loadFacility - no facility record found.");
		}
			
		// Store facility information
		$this->fac_id = $facility->id;
		$this->fac_full = (empty($facility->name)) ? 'our clinic' : $facility->name;
		$this->fac_address = $facility->street;
		if ($facility->street2) $this->fac_address .= ', '.$facility->street2;
		if ($facility->city) $this->fac_address .= ', '.$facility->city;
		if ($facility->state) $this->fac_address .= ' '.$facility->state;
		if ($facility->postal_code) $this->fac_address .= ', '.substr($facility->postal_code,0,5);
		$this->fac_phone = $facility->phone;
		$this->fac_email = $facility->email;
		
	}

	/**
	 * Retrieve the 'appointment' data for the wmtGrab class for use in the
	 * data object used with template merge functions.
	 *
	 * @param string eid  - appointment record identifier
	 * @throws \Exception - not found 
	 * 
	 */
	public function loadAppointment( $eid = false ) {
		if ( !$eid ) {
			throw new \Exception("wmtGrab::loadAppointment - no appointment identifier provided.");
		}
		
		// Retrieve appointment record
		$appt = new Appt($eid);
		if (!$appt || empty($appt->pc_eid)) {
			throw new \Exception("wmtGrab::loadAppointment - no appointment record found.");
		}
			
		// Store appointment information
		$this->appt_id = $appt->pc_eid;
		$this->pc_facility = $appt->pc_facility;
		$this->pc_aid = $appt->pc_aid;
		$this->appt_type = (empty($appt->category)) ? 'appointment' : $appt->category;
		$this->appt_title = (empty($appt->pc_title)) ? $this->appt_type : $appt->pc_title;

		$start = strtotime(substr($appt->pc_eventDate,0,10) .' '. $appt->pc_startTime);
		if ($start === false) {
			throw new \Exception("wmtGrab::loadAppointment - invalid appointment start date/time.");
		}
		if (substr($appt->pc_endDate,0,10) == '0000-00-00' || empty($appt->pc_endDate)) {
			$ends = strtotime(substr($appt->pc_eventDate,0,10) .' '.$appt->pc_endTime);
		} else {
			$ends = strtotime(substr($appt->pc_endDate,0,10) .' '.$appt->pc_endTime);
		}
		
		switch ($this->language) {
		    case 'Spanish':
		        setlocale(LC_TIME, array('esm','es_ES.utf8') );
				$this->appt_full = strftime("%A, %e de %B %G", $start);
				$this->appt_date = strftime("%d/%m/%G", $start);
				$this->appt_time = strftime("%H:%M", $start);
				$this->appt_ends = ($ends)? strftime("%H:%M", $ends) : '';
				setlocale(LC_TIME, '');
		        break;
		    default: // system setting
		        $this->appt_full = strftime("%A, %B %e, %G", $start);
				$this->appt_date = strftime("%m/%d/%G", $start);
				$this->appt_time = strftime("%I:%M %p", $start);
				$this->appt_ends = ($ends)? strftime("%I:%M %p", $ends) : '';
				break;
		}
		
		$this->appt_min = '';
		if ($appt->pc_duration > 0) {
			$this->appt_min = ceil($appt->pc_duration / 60);
			$this->appt_min .= ($this->language == 'Spanish')? ' minutos' : ' minutes';
		}

		/*Zoom meeting details*/
		$this->zm_provider_url = $appt->zm_start_url;
		$this->zm_patient_url = $appt->zm_join_url;
		$this->zm_id = $appt->zm_id;
		$this->zm_password = $appt->zm_password;
		$this->zm_short_provider_url = $appt->zm_start_url;
		$this->zm_short_patient_url = $appt->zm_join_url;
	}

	public function loadFutureAppointment($pid = false, $appt_date = false, $eid = false) {
		$appt = new Appt();
		$apptData = $appt->loadFutureAppointment($pid, $appt_date, $eid);

		$strHtml = "";
		foreach ($apptData as $key => $item) {
			$event_date_time = date('m/d/Y h:i:s A', strtotime($item['pc_eventDate'].' '.$item['pc_startTime']));
			$strHtml .= $event_date_time . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;". $item['pc_title'] ."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;". $item['provider_name'] ."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;". $item['facility_name'] ."<br/>";
		}

		$this->all_future_appointments = $strHtml;
	}
	
	/**
	 * Helper function to load all of the data elements.
	 *
	 * @param string pid - patient identifier
	 * @param string uid - provider indentifier
	 * @param string fid - facility identifier
	 * @param string eid - appointment identifier
	 *  
	 */
	public function loadData( $pid=false, $uid=false, $fid=false, $eid=false, $appt_date=false, $apptid = false) {
		if ($pid) $this->loadPatient($pid);
		if ($uid) $this->loadProvider($uid);
		if ($fid) $this->loadFacility($fid);
		if ($eid) $this->loadAppointment($eid);

		if ($pid && $appt_date) $this->loadFutureAppointment($pid, $appt_date, $apptid);
	}

	/**
	 * Retrieve list of all class variables (substitutable parameters).
	 *
	 * @return array $params - list of substitution parameters
	 *  
	 */
	public static function listTags() {
		$tags = get_class_vars("wmt\Grab");
		unset($tags['pc_facility']);
		unset($tags['pc_aid']);
		return $tags;
	}

	/**
	 * Retrieve substitution parameters and current values.
	 *
	 * @return array $data - list of substitution parameters
	 *  
	 */
	public function getData() {
		$data = get_object_vars( $this );
		return $data;
	}
	
}
?>
