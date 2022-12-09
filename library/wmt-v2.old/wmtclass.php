<?php
/** **************************************************************************
 *	WMTPATIENT.CLASS.PHP
 *	This file contains a print class for use with any print form
 *
 *  NOTES:
 *  1) __CONSTRUCT - always uses the ID to retrieve data 
 *  2) GET - uses alternate selectors to find and return associated object
 * 
 *  @version 1.0
 *  @copyright Williams Medical Technologies, Inc.
 *  @author Rich Genandt <rgenandt@gmail.com>
 * 
 *************************************************************************** */

/** 
 * Provides a partial representation of the patient data record This object
 * does NOT include all of the fields associated with the core patient data
 * record and should NOT be used for database updates.  It is intended only
 * for retrieval of partial patient information primarily for display 
 * purposes (reports for example).
 *
 */

if(!class_exists('wmtPatData')) {

class wmtPatData{
	public $id;
	public $pid;
	public $pubpid;
	public $lname;
	public $fname;
	public $mname;
	public $street;
	public $city;
	public $state;
	public $postal_code;
	public $full_name;
	public $last_first;
	public $csz;
	public $DOB;
	public $age;
	public $sex;
	public $ss;
	public $status;
	public $phone_home;
	public $phone_biz;
	public $phone_cell;
	public $email;
	public $ethnicity;
	public $occupation;
	public $contact_relationship;
	public $phone_contact;
	public $wmt_education;
	public $family_size;
	public $monthly_income;
	public $language;
	public $ethnoracial;
	public $race;
	public $country_code;
	public $rh_factor;
	public $blood_type;
	public $primary;
	public $primary_id;
	public $primary_group;
	public $primary_lname;
	public $primary_fname;
	public $primary_mname;
	public $primary_DOB;
	public $primary_ss;
	public $primary_relat;
	public $secondary;
	public $secondary_id;
	public $secondary_group;
	public $secondary_lname;
	public $secondary_fname;
	public $secondary_mname;
	public $secondary_DOB;
	public $secondary_ss;
	public $secondary_relat;
	public $third;
	public $third_id;
	public $third_group;
	public $third_lname;
	public $third_fname;
	public $third_mname;
	public $third_DOB;
	public $third_ss;
	public $third_relat;
	public $referral_id;
	public $referral_full_name;
	public $referral_last_first;
	
	/**
	 * Constructor for the 'patient print' class which retrieves the requested 
	 * dashboard information from the database or errors.
	 * 
	 * @param int $id patient record id identifier
	 * @return object instance of patient print class
	 */
	public function __construct($id = false) {
		if(!$id) return false;

		$query = "SELECT * FROM patient_data WHERE id =?";
		$results = sqlStatementNoLog($query, array($id));
	
		if ($data = sqlFetchArray($results)) {
			$this->id = $data['id'];
			$this->pid = $data['pid'];
			$this->pubpid = $data['pubpid'];
			$this->lname = $data['lname'];
			$this->fname = $data['fname'];
			$this->mname = $data['mname'];
			$this->street = $data['street'];
			$this->sex= $data['sex'];
			$this->city = $data['city'];
			$this->state = $data['state'];
			$this->postal_code = $data['postal_code'];
			$this->DOB = $data['DOB'];
			$this->ss = $data['ss'];
			$this->status = $data['status'];
			$this->phone_home = $data['phone_home'];
			$this->phone_biz = $data['phone_biz'];
			$this->phone_cell= $data['phone_cell'];
			$this->email = $data['email'];
			$this->ethnicity = $data['ethnicity'];
			$this->occupation = $data['occupation'];
			$this->wmt_education = $data['wmt_education'];
			$this->family_size = $data['family_size'];
			$this->monthly_income = $data['monthly_income'];
			$this->language = $data['language'];
			$this->ethnoracial = $data['ethnoracial'];
			$this->contact_relationship = $data['contact_relationship'];
			$this->phone_contact = $data['phone_contact'];
			$this->race = $data['race'];
			$this->country_code = $data['country_code'];
			$this->blood_type = $data['blood_type'];
			$this->rh_factor = $data['rh_factor'];
			$this->referral_id = $data['ref_providerID'];
			// preformat commonly used data elements	
			$this->full_name = $data['fname'].' '.$data['lname'];
			if($data['mname']) {
				$this->full_name = $data['fname'].' '.$data['mname'].' '.$data['lname'];
			}
			$this->last_first= $data['lname'].', '.$data['fname'].' '.$data['mname'];
			if($data['city'] || $data['state'] || $data['postal_code']) {
				$this->csz= $data['city'].', '.$data['state'].' '.$data['postal_code'];
			}
			if($data['DOB'] && $data['DOB'] != '0000-00-00') {
				$this->age = getPatientAge($data['DOB']);
			}
			if($this->referral_id != '' && $this->referral_id != 0) {
				$query = "SELECT lname, fname, mname FROM users WHERE id=?";
				$results = sqlStatementNoLog($query, array($this->referral_id));
				$user = sqlFetchArray($results);
				$this->referral_full_name = $user['fname'].' '.$user['lname'];
				if($user['mname']) {
					$this->referral_full_name = $user['fname'].' '.
									$user['mname'].' '.$user['lname'];
				}
				$this->referral_last_first = $user['lname'].', '.
									$user['fname'].' '.$user['mname'];
			} else {
				$this->referral_full_name = 'No Referring Physician On File';	
				$this->referral_last_first = 'No Referring Physician On File';	
			}
			$query = "SELECT insurance_data.*, insurance_companies.* FROM ".
				"insurance_data LEFT JOIN insurance_companies ON ".
				"insurance_data.provider = insurance_companies.id ".
				"WHERE insurance_data.type=? AND insurance_data.pid=? ".
				"ORDER BY insurance_data.date DESC LIMIT 1";
			$results = sqlStatementNoLog($query, array('primary', $this->pid));
			if($data = sqlFetchArray($results)) {
				$this->primary = $data['name'];
				$this->primary_id = $data['policy_number'];
				$this->primary_group = $data['group_number'];
				$this->primary_lname = $data['subscriber_lname'];
				$this->primary_fname = $data['subscriber_fname'];
				$this->primary_mname = $data['subscriber_mname'];
				$this->primary_DOB = $data['subscriber_DOB'];
				$this->primary_ss= $data['subscriber_ss'];
				$this->primary_relat = $data['subscriber_relationship'];
			}
			$results = sqlStatementNoLog($query, array('secondary', $this->pid));
			if($data = sqlFetchArray($results)) {
				$this->secondary = $data['name'];
				$this->secondary_id = $data['policy_number'];
				$this->secondary_group = $data['group_number'];
				$this->secondary_lname = $data['subscriber_lname'];
				$this->secondary_fname = $data['subscriber_fname'];
				$this->secondary_mname = $data['subscriber_mname'];
				$this->secondary_DOB = $data['subscriber_DOB'];
				$this->secondary_ss= $data['subscriber_ss'];
				$this->secondary_relat = $data['subscriber_relationship'];
			}
			$results = sqlStatementNoLog($query, array('tertiary', $this->pid));
			if($data = sqlFetchArray($results)) {
				$this->third = $data['name'];
				$this->third_id = $data['policy_number'];
				$this->third_group = $data['group_number'];
				$this->third_lname = $data['subscriber_lname'];
				$this->third_fname = $data['subscriber_fname'];
				$this->third_mname = $data['subscriber_mname'];
				$this->third_DOB = $data['subscriber_DOB'];
				$this->third_ss= $data['subscriber_ss'];
				$this->third_relat = $data['subscriber_relationship'];
			}
		}
		else {
			throw new Exception('wmtPatData::_construct - no patient record with id ('.$this->id.').');
		}
		
	}	

	/**
	 * Retrieve a patient print object by PID value. Uses the base constructor 
   * for the 'patient print' class to create and return the object.
	 * 
	 * @static
	 * @param int $pid patient record pid
	 * @return object instance of patient print class
	 */
	public static function getPidPatient($pid) {
		if(!$pid) {
			throw new Exception('wmtPatData::getPidPatient - no patient identifier provided.');
		}
		
		$results = sqlStatementNoLog("SELECT id FROM patient_data WHERE pid =?",
			 array($pid));
		$data = sqlFetchArray($results);
		return new wmtPatData($data['id']);
	}
	
	
  /**
 * Updates the patient information in the database.
 * 
 * @static
 * @param Errors $iderror_object
 * @return null
 */
	public function update() {
		// build query from object
		$query = '';
		$parms = array();
		$fields = wmtPatData::listFields();
		foreach ($this as $key => $value) {
			if (!in_array($key, $fields)) continue;
			if ($query) $query .= ', ';
			$query .= " $key = ?";
			$parms[] = $value;
		}
		
		$parms[] = $this->id;
		sqlInsert("UPDATE patient_data SET $query WHERE id = ?", $parms);
		
		return;
			
	}
	
	/**
	 * Returns an array of valid database fields for the object.
	 * 
	 * @static
	 * @return array list of database field names
	 */
	public static function listFields() {
		return sqlListFields('patient_data');
	}
                                            
}

}

?>
