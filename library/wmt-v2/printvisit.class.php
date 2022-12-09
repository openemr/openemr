<?php
/** **************************************************************************
 *	PRINTVISIT.CLASS.PHP
 *	This file contains a print class for use with any print form
 *
 *  NOTES:
 *  1) __CONSTRUCT - uses the encounter id to retrieve data 
 *  2) GET - uses alternate selectors to find and return associated object
 * 
 *  @version 1.0
 *  @copyright Williams Medical Technologies, Inc.
 *  @author Rich Genandt <rgenandt@gmail.com>
 * 
 *************************************************************************** */

/** 
 * Provides a partial representation of the patient encouner record This object
 * does NOT include all of the fields associated with the core encounter data
 * record and should NOT be used for database updates.  It is intended only
 * for retrieval of partial patient information primarily for display 
 * purposes (reports for example).
 *
 */
if(!isset($GLOBALS['fileroot'])) include_once('../../globals.php');
require_once($GLOBALS['srcdir'].'/wmt-v2/wmtvitals.class.php');
require_once($GLOBALS['srcdir'].'/wmt-v2/wmtstandard.inc');

if(!class_exists('wmtPrintVisit')) {

class wmtPrintVisit{
	public $encounter_id;
	public $encounter_date;
	public $encounter_time;
	public $facility_id;
	public $facility_name;
	public $billing_id;
	public $provider_id;
	public $student;
	public $interpreter;
	public $supervisor_id;
	public $provider_full;
	public $referring_id;
	public $referring_full;
	public $referring_addr1;
	public $referring_addr2;
	public $referring_csz;
	public $referring_phone;
	public $referring_fax;
	public $student_by;
	public $signed_by;
	public $approved_by;
	public $full_reason;
	public $onset_date;
	public $short_reason;
	public $dolv;
	public $prev_enc;
	public $referral_source;

	public $vital_id;
	public $timestamp;
	public $height;
	public $weight;
	public $bps;
	public $bpd;
	public $BMI;
	public $BMI_status;
	public $pulse;
	public $respiration;
	public $arm;
	public $o2;
	public $temp;
	public $temp_method;
	public $accucheck;
	public $note;
	public $waist_circ;
	public $head_circ;
	public $prone_bpd;
	public $prone_bps;
	public $standing_bpd;
	public $standing_bps;

	public $specific_gravity;
	public $ph;
	public $leukocytes;
	public $nitrite;
	public $protein;
	public $glucose;
	public $ketones;
	public $urobilinogen;
	public $bilirubin;
	public $blood;
	public $hemoglobin;
	public $HCG;
	public $LMP;

	public $appt_time;
	public $appt_reason;
	public $appt_provider;
	public $appt_comment;

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
	public function __construct($id = false, $approval_user = '', $approval_timestamp = '', $vital_mode = '') {
		if(!$id) return false;

		$query = "SELECT * FROM form_encounter WHERE id =?";
		$data = sqlQueryNoLog($query, array($id));
	
		if ($data) {
			$this->encounter_id = $data['encounter'];
			$this->encounter_date = substr($data['date'],0,10);
			$this->encounter_time = substr($data['date'],-8);
			$this->facility_id = $data['facility_id'];
			$this->billing_id = $data['billing_facility'];
			$this->provider_id = $data['provider_id'];
			$this->supervisor_id = $data['supervisor_id'];
			$this->full_reason = $data['reason'];
			$this->short_reason = $data['reason'];
			$this->onset_date = substr($data['onset_date'],0,10);
			if(isset($data['student'])) $this->student = $data['student'];
			if(isset($data['interpreter'])) $this->interpreter = $data['interpreter'];
			if($this->onset_date == '0000-00-00') $this->onset_date = '';
			if(strlen($data['reason']) > 100) {
				$this->short_reason = substr($data['reason'],0,100).'...';
			}
			$this->referral_source = $data['referral_source'];
		}
		else {
			throw new Exception('wmtPrintVisit::_construct - no encounter record with id ('.$this->id.').');
		}
		// Default facility to 3 if not set
		if(!$this->facility_id) $this->facility_id= 3;
		
		// preformat commonly used data elements	
		$signing_user = '';
		$this->signed_by = xl('No Signature on File','r');
		if($this->provider_id) {
			$sig_override = 
						checkSettingMode('wmt::signature_override',$this->provider_id);
  		$rrow = sqlQueryNoLog("SELECT id, lname, fname, mname, username, ".
					"suffix FROM users WHERE id=?", array($this->provider_id));
  		if($rrow{'id'}) {
				$signing_user = $rrow{'username'};
				$_mi = ' ';
				if(!empty($rrow{'mname'})) $_mi = ' '.$rrow{'mname'}.' ';
				$this->provider_full = $rrow{'fname'}.$_mi.$rrow{'lname'};
				if($rrow{'suffix'}) $this->provider_full .= ', ' . $rrow{'suffix'};
    		if($sig_override) $this->provider_full = $sig_override;
    		$this->signed_by = xl('Digitally Signed By ' , 'r').'&nbsp;' .
								$this->provider_full;
  		}
			if($this->student) {
				$this->student_by = 'Student: ';
				$this->student_by .= getUserDisplayName($this->student,'first','username');
				$this->student_by .= '<br>The service was supervised and directed by '.
					'the provider during the key and critical portions of the service, '.
					'includeding the management of the patient.';
			}
		}
		$this->approved_by = '';
		$suppress = checkSettingMode('wmt::suppress_same_approval');
		if($approval_user) {
			if(!$suppress || ($approval_user != $signing_user)) {
				$sig_override = checkSettingMode('wmt::signature_override',$rrow{'id'});
				$_sig = getUserDisplayName($approval_user, 'first', 'username');
    		if($sig_override) $_sig = $sig_override;
    		$this->approved_by = xl('Digitally Approved By','r').'&nbsp;'.$_sig;
			}
		}
		// Now add the timestamp to the relevant signature
		if($approval_timestamp) {
			if($this->approved_by) {
				$this->approved_by .= '&nbsp;&nbsp;On&nbsp;&nbsp;'.$approval_timestamp;
			} else if($this->signed_by) {
				$this->signed_by .= '&nbsp;&nbsp;On&nbsp;&nbsp;'.$approval_timestamp;
			}
		}

		// Get the most recent Vitals
		if($vital_mode == 'recent') {
			$vitals = wmtVitals::getVitalsByPatient($data['pid'], date('Y-m-d'));
		} else {
			// echo "Getting Vitals for Encounter [".$data['encounter']."]<br>\n";
			$vitals = wmtVitals::getVitalsByEncounter($data['encounter'], $data['pid']);
			if(!$vitals->vital_id) $vitals->timestamp = 'No Vitals Recorded for this Encounter';
		}
		foreach($vitals as $key => $val) {
			$this->$key = $val;
		}
	
		// Try to get a date of the last visit (prior to this one)
		$this_date = $data['date'];
		$sql = "SELECT date, encounter, form_id FROM forms WHERE pid=? ".
			"AND deleted=0 AND formdir='newpatient' ORDER BY date DESC";
		$res = sqlStatementNoLog($sql, array($data['pid']));
		while($row = sqlFetchArray($res)) {
			if($row['date'] < $this_date) break;
		}
		if(isset($row['date'])) {
			if($row['date'] != '' && $row['date'] != '0000-00-00') {
				$this->dolv = substr($row['date'],0,10);
				$this->prev_enc = $row['encounter'];
			}
		}
	
		// Define referring physician info if available
		$sql = "SELECT providerID, ref_providerID FROM patient_data WHERE pid=? ";
		$row = sqlQueryNoLog($sql, array($data['pid']));
		$this->referring_full = 'No Referring Physician on File';
		if($row['ref_providerID'] != '0' && $row['ref_providerID'] != '') {
			$this->referring_id = $row['ref_providerID'];
			$query = "SELECT * FROM users WHERE id=?";
			$results = sqlStatementNoLog($query, array($this->referring_id));
			$user = sqlFetchArray($results);
			$this->referring_full= $user['fname'].' '.$user['lname'];
			if($user['mname']) {
				$this->referring_full= $user['fname'].' '.
									$user['mname'].' '.$user['lname'];
			}
			$this->referring_addr1 = trim($user['street']);
			$this->referring_addr2 = trim($user['streetb']);
			if($user['city'] || $user['state'] || $user['zip']) {
				$this->referring_csz = $user['city'].', '.$user['state'].' '.$user['zip'];
			}
			$this->referring_phone = $user['phone'];
			$this->referring_fax = $user['fax'];
		}

		// Load details for this appointment
		if($this_date != '') {
			$sql = "SELECT cal.pc_catid,cal.pc_aid,cal.pc_title,cal.pc_eventDate, ".
				"cal.pc_startTime, cal.pc_hometext, cat.pc_catname FROM ".
				"openemr_postcalendar_events AS cal LEFT JOIN ".
				"openemr_postcalendar_categories AS cat USING (pc_catid) WHERE ".
				"pc_pid=? AND pc_eventDate = ? ORDER BY pc_eventDate, pc_startTime ".
				"ASC LIMIT 1";
			$appt = sqlQuery($sql, array($data['pid'], $this_date));
			$this->appt_time = substr($appt{'pc_startTime'},0,5);
			$this->appt_reason = $appt{'pc_catname'};
			$this->appt_provider = $appt{'pc_aid'};
			$this->appt_comment = $appt{'pc_hometext'};
		}
		// Load any details for the next appointment
		$sql = "SELECT cal.pc_catid, cal.pc_aid, cal.pc_title, cal.pc_eventDate, ".
			"cal.pc_startTime, cal.pc_hometext, cat.pc_catname FROM ".
			"openemr_postcalendar_events AS cal LEFT JOIN ".
			"openemr_postcalendar_categories AS cat USING (pc_catid) WHERE pc_pid=? ".
			"AND pc_eventDate > ? ORDER BY pc_eventDate, pc_startTime ASC LIMIT 1";
		$appt = sqlQuery($sql, array($data['pid'], date('Y-m-d')));
		$this->next_appt_dt = $appt{'pc_eventDate'};
		$this->next_appt_time = substr($appt{'pc_startTime'},0,5);
		$this->next_appt_reason = $appt{'pc_catname'};
		$this->next_appt_provider = $appt{'pc_aid'};
		$this->next_appt_comment = $appt{'pc_hometext'};
	}	

	/**
	 * Retrieve an encounter object by encounter value. Uses the base constructor 
   * for the 'visit' print class to create and return the object.
	 * 
	 * @static
	 * @param int $enc encounter record encounter
	 * @return object instance of visit pring class
	 */
	public static function getEncounter($enc='',$vital_mode='') {
		// if(!$enc) {
			// throw new Exception('wmtPrintVisit::getEncounter - no encounter identifier provided.');
		// }
		if($enc == '') return new wmtPrintVisit(0,'','',$vital_mode);
		
		$results = sqlStatementNoLog("SELECT id FROM form_encounter WHERE encounter=?",
			 array($enc));
		$data = sqlFetchArray($results);
		return new wmtPrintVisit($data['id'],'','',$vital_mode);
	}

	/**
	 * Retrieve the most recent encounter. Uses the base constructor 
   * for the 'visit' print class to create and return the object.
	 * 
	 * @static
	 * @param int $pid is the patient to locate
	 * @return object instance of visit pring class
	 */
	public static function getMostRecent($pid) {
		if(!$pid) {
			throw new Exception('wmtPrintVisit::getMostRecent- no patient identifier provided.');
		}
		
		$sql = "SELECT form_encounter.id, facility, facility_id, ".
			"form_encounter.date FROM form_encounter LEFT JOIN forms ON ".
			"(form_encounter.encounter = forms.encounter) ".
			"WHERE form_encounter.pid=? AND ".
			"forms.deleted != 1 ORDER BY form_encounter.date DESC";
		$results = sqlStatementNoLog($sql, array($pid));
		$data = sqlFetchArray($results);
		return new wmtPrintVisit($data['id']);
	}

	/**
	 * Retrieve the most recent form by date. Returns the id of the form
	 * 
	 * @static
	 * @param int $pid is the patient to locate
	 * @param string $frm is the form directory 
	 * @return form id for the target form
	 */
	public static function getMostRecentForm($pid, $frm) {
		if(!$pid) {
			throw new Exception('wmtPrintVisit::getMostRecentForm - no patient identifier provided.');
		}
		if(!$pid) {
			throw new Exception('wmtPrintVisit::getMostRecentForm - no form name provided.');
		}
		
  	$old = sqlQuery('SELECT form_id, formdir, fe.date AS dos FROM forms ' .
			'LEFT JOIN form_encounter AS fe USING (encounter) WHERE formdir=? '.
			'AND forms.pid=? AND deleted=0 ORDER BY dos DESC LIMIT 1', 
			array($frm, $pid));
  	return($old{'form_id'});
	}
	
	/**
	 * Retrieve an encounter object by encounter value. Uses the base constructor 
   * for the 'visit' print class to create and return the object.
	 * 
	 * @static
	 * @param int $enc encounter record encounter
	 * @return object instance of visit pring class
	 */
	public static function getEncounterByForm($form_id = false, $formdir = '', $vmode = '') {
		if(!$form_id) {
			return new wmtPrintVisit(false,'','',$vmode);
			throw new Exception('wmtPrintVisit::getEncounterByForm - no form identifier provided.');
		}
		if(!$formdir) {
			return new wmtPrintVisit(false,'','',$vmode);
			throw new Exception('wmtPrintVisit::getEncounterByForm - no form directory provided.');
		}
		/**
		*  Load the approving physician so we can see if it's different than
		*  the provider
		**/		
		$approval_user = $get_approve = $approval_timestamp = '';
		$frm_table = $formdir;
		if($frm_table == 'mc_wellsub') $frm_table = 'mc_wellness';
		// echo "Reset the form directory to $formdir<br>\n";
		$flds = sqlListFields('form_'.$frm_table);
		$get_approve = '';
		if(in_array('approved_dt', $flds)) $get_approve .= 'approved_dt, ';
		if(in_array('form_complete', $flds)) $get_approve .= 'form_complete, ';
		if(in_array('status', $flds)) $get_approve .= 'status, ';
		if(in_array('approved_by', $flds)) $get_approve .= 'approved_by, ';
		$results = sqlStatementNoLog("SELECT id, date, $get_approve user FROM ".
				"form_$frm_table WHERE id=?", array($form_id));
		$data= sqlFetchArray($results);


		$complete = '';
		if(in_array('form_complete', $flds)) $complete= $data{'form_complete'};
		if(in_array('status', $flds)) $complete= $data{'status'};
		if(strtolower($complete) == 'a') {
			if(isset($data{'approved_by'})) {
				$approval_user = $data{'approved_by'};
				if($data{'approved_by'} == '') $approval_user = $data{'user'};
			}	else {
				$approval_user = $data{'user'};
			}
			if(isset($data{'approved_dt'}) && (substr($data{'approved_dt'},0,10) != '0000-00-00')) {
				$approval_timestamp = substr($data{'approved_dt'},0,10);
				if(checkSettingMode('wmt::approve_time')) { 
					$approval_timestamp = $data{'approved_dt'};
				}
			}
			if($approval_timestamp == '') {
				$approval_timestamp = substr($data{'date'},0,10);
				if(checkSettingMode('wmt::approve_time')) {
					$approval_timestamp = $data{'date'};
				}
			}
		}	

		$results = sqlStatementNoLog("SELECT encounter FROM forms WHERE form_id=?".
			" AND formdir=?", array($form_id, $formdir));
		$data = sqlFetchArray($results);
		$results = sqlStatementNoLog("SELECT id FROM form_encounter WHERE encounter=?",
			 array($data['encounter']));
		$data = sqlFetchArray($results);
		return new wmtPrintVisit($data['id'], $approval_user, $approval_timestamp);
	}

	/**
	 * Outputs the vitals in the table format for the patient instruction /
   * patient clinical summary reports.
	 * 
	 * @static
	 * @param is the number of columns for formatted output
	 *   ha ha, that's possible for future use, for now we'll just go to 4
	 */
	public function patReportVitals($cols = 4) {
		
		echo "<fieldset style='border: solid 1px black;'><legend class='wmtPrnHeader'>&nbsp;Vitals&nbsp;</legend>\n";
		echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>\n";
		echo "	<tr>\n";
		echo "		<td colspan='4'><span class='wmtPrnLabel'>Vitals Taken:&nbsp;</span><span class='wmtPrnBody'>$this->timestamp</span></td>\n";
		echo "	</tr>\n";
		echo "	<tr>\n";
		echo "		<td><span class='wmtPrnLabel'>Height:&nbsp;</span><span class='wmtPrnBody'>$this->height</span></td>\n";
		echo "		<td><span class='wmtPrnLabel'>Weight:&nbsp;</span><span class='wmtPrnBody'>$this->weight</span></td>\n";
		echo "		<td><span class='wmtPrnLabel'>BMI:&nbsp;</span><span class='wmtPrnBody'>$this->BMI</span></td>\n";
		echo "		<td><span class='wmtPrnBody'>$this->BMI_status</span></td>\n";
		echo "	</tr>\n";
		echo "	<tr>\n";
		echo "		<td><span class='wmtPrnLabel'>Blood Pressure:&nbsp;</span><span class='wmtPrnBody'>$this->bps&nbsp;/&nbsp;$this->bpd</span></td>\n";
		echo "		<td><span class='wmtPrnLabel'>Temperature:&nbsp;</span><span class='wmtPrnBody'>$this->temp</span></td>\n";
		echo "		<td><span class='wmtPrnLabel'>Pulse:&nbsp;</span><span class='wmtPrnBody'>$this->pulse</span></td>\n";
		echo "		<td><span class='wmtPrnLabel'>Respiration:&nbsp;</span><span class='wmtPrnBody'>$this->respiration</span></td>\n";
		echo "	</tr>\n";
		echo "	</table>\n";
		echo " </fieldset>\n";
	}

}
                                            
}

?>
