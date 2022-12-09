<?php
/** **************************************************************************
 *	PRINTPAT.CLASS.PHP
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

if(!class_exists('wmtPrintPat')) {

class wmtPrintPat{
	public $id;
	public $pid;
	public $pubpid;
	public $lname;
	public $fname;
	public $mi;
	public $addr;
	public $street;
	public $city;
	public $state;
	public $zip;
	public $postal_code;
	public $full_name;
	public $last_first;
	public $csz;
	public $dob;
	public $DOB;
	public $age;
	public $sex;
	public $ssn;
	public $race;
	public $ethnicity;
	public $ethnoracial;
	public $language;
	public $status;
	public $hphone;
	public $phone_home;
	public $wphone;
	public $phone_biz;
	public $phone_cell;
	public $email;
	public $email_direct;
	public $primary;
	public $primary_id;
	public $primary_attn;
	public $primary_phone;
	public $primary_copay;
	public $primary_group;
	public $primary_fname;
	public $primary_mname;
	public $primary_lname;
	public $primary_DOB;
	public $primary_ss;
	public $primary_relat;
	public $secondary;
	public $secondary_id;
	public $secondary_attn;
	public $secondary_phone;
	public $secondary_copay;
	public $secondary_group;
	public $secondary_fname;
	public $secondary_mname;
	public $secondary_lname;
	public $secondary_DOB;
	public $secondary_relat;
	public $referring;
	public $blood_type;
	public $rh_factor;
	public $occupation;
	public $wmt_education;
	
	/**
	 * Constructor for the 'patient print' class which retrieves the requested 
	 * dashboard information from the database or errors.
	 * 
	 * @param int $id patient record id identifier
	 * @return object instance of patient print class
	 */
	public function __construct($id = false, $ageWhen='') {
		if(!$id) return false;

		$query = "SELECT * FROM patient_data WHERE id =?";
		$results = sqlStatementNoLog($query, array($id));
	
		if ($data = sqlFetchArray($results)) {
			$this->id = $data['id'];
			$this->pid = $data['pid'];
			$this->pubpid = $data['pubpid'];
			$this->lname = $data['lname'];
			$this->fname = $data['fname'];
			$this->mi= $data['mname'];
			$this->addr = $data['street'];
			$this->street = $data['street'];
			$this->sex= ucfirst($data['sex']);
			$this->city = $data['city'];
			$this->state = $data['state'];
			$this->zip = $data['postal_code'];
			$this->postal_code = $data['postal_code'];
			$this->dob = $data['DOB'];
			$this->DOB = $data['DOB'];
			$this->ssn = $data['ss'];
			$this->ethnicity = $data['ethnicity'];
			$this->hphone = $data['phone_home'];
			$this->phone_home = $data['phone_home'];
			$this->wphone = $data['phone_biz'];
			$this->phone_biz = $data['phone_biz'];
			$this->phone_cell = $data['phone_cell'];
			$this->blood_type = $data['blood_type'];
			$this->rh_factor = $data['rh_factor'];
			$this->race = $data['race'];
			$this->ethnoracial = $data['ethnoracial'];
			$this->ethnicity= $data['ethnicity'];
			$this->language = $data['language'];
			$this->status = $data['status'];
			$this->email = $data['email'];
			$this->email_direct = $data['email_direct'];
			$this->occupation = $data['occupation'];
			$this->wmt_education = $data['wmt_education'];
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
				$this->age = getPatientAge($data['DOB'], $ageWhen);
			}
			$query = "SELECT insurance_data.*, insurance_companies.*, " .
				"phone_numbers.* FROM ".
				"insurance_data LEFT JOIN insurance_companies ON ".
				"insurance_data.provider = insurance_companies.id ".
				"LEFT JOIN phone_numbers ON " .
				"insurance_companies.id = phone_numbers.foreign_id ".
				"WHERE insurance_data.type = ? AND insurance_data.pid = ? ".
				"ORDER BY insurance_data.date DESC LIMIT 1";
			$results = sqlStatementNoLog($query, array('primary', $this->pid));
			if($data = sqlFetchArray($results)) {
				$this->primary = $data['name'];
				$this->primary_id = $data['policy_number'];
				$this->primary_attn = $data['attn'];
				$this->primary_phone = $data['area_code'] . ' ' . $data['prefix'] . 
					'-' . $data['number'];
				$this->primary_group = $data['group_number'];
				$this->primary_copay = $data['copay'];
				$this->primary_fname = $data['subscriber_fname'];;
				$this->primary_mname = $data['subscriber_mname'];;
				$this->primary_lname = $data['subscriber_lname'];;
				$this->primary_ss = $data['subscriber_ss'];;
				$this->primary_DOB = $data['subscriber_DOB'];;
				$this->primary_relat = $data['subscriber_relationship'];;
			}
			$results = sqlStatementNoLog($query, array('secondary', $this->pid));
			if($data = sqlFetchArray($results)) {
				$this->secondary = $data['name'];
				$this->secondary_id = $data['policy_number'];
				$this->secondary_attn = $data['attn'];
				$this->secondary_phone = $data['area_code'] . ' ' . $data['prefix'] . 
					'-' . $data['number'];
				$this->secondary_group = $data['group_number'];
				$this->secondary_copay = $data['copay'];
				$this->secondary_fname = $data['subscriber_fname'];;
				$this->secondary_mname = $data['subscriber_mname'];;
				$this->secondary_lname = $data['subscriber_lname'];;
				$this->secondary_ss = $data['subscriber_ss'];;
				$this->secondary_DOB = $data['subscriber_DOB'];;
				$this->secondary_relat = $data['subscriber_relationship'];;
			}
		}
		else {
			throw new Exception('wmtPrintPat::_construct - no patient record with id ('.$this->id.').');
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
	public static function getPatient($pid, $ageWhen='') {
		if(!$pid) {
			throw new Exception('wmtPrintPat::getPidPatient - no patient identifier provided.');
		}
		
		$results = sqlStatementNoLog("SELECT id FROM patient_data WHERE pid =?",
			 array($pid));
		$data = sqlFetchArray($results);
		return new wmtPrintPat($data['id'], $ageWhen);
	}
	
}
                                            
}

?>
