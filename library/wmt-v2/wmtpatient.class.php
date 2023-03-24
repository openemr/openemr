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

if(!isset($GLOBALS['srcdir'])) include_once('../../globals.php');
include_once($GLOBALS['srcdir'].'/patient.inc');
include_once($GLOBALS['srcdir'].'/formatting.inc.php');
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
	public $email_direct;
	public $ethnicity;
	public $occupation;
	public $contact_relationship;
	public $phone_contact;
	public $wmt_education;
	public $family_size;
	public $monthly_income;
	public $pricelevel;
	public $language;
	public $ethnoracial;
	public $race;
	public $country_code;
	public $genericname1;
	public $genericval1;
	public $genericname2;
	public $genericval2;
	public $wmt_partner_name;
	public $wmt_partner_ph;
	public $wmt_father_name;
	public $wmt_father_ph;
	public $rh_factor;
	public $blood_type;
	public $primary;
	public $primary_unique;
	public $primary_provider;
	public $primary_id;
	public $primary_attn;
	public $primary_phone;
	public $primary_group;
	public $primary_lname;
	public $primary_fname;
	public $primary_mname;
	public $primary_DOB;
	public $primary_ss;
	public $primary_relat;
	public $primary_copay;
	public $primary_plan_type;
	public $secondary;
	public $secondary_unique;
	public $secondary_provider;
	public $secondary_id;
	public $secondary_attn;
	public $secondary_phone;
	public $secondary_group;
	public $secondary_lname;
	public $secondary_fname;
	public $secondary_mname;
	public $secondary_DOB;
	public $secondary_ss;
	public $secondary_relat;
	public $secondary_copay;
	public $secondary_plan_type;
	public $third;
	public $third_unique;
	public $third_provider;
	public $third_id;
	public $third_attn;
	public $third_phone;
	public $third_group;
	public $third_lname;
	public $third_fname;
	public $third_mname;
	public $third_DOB;
	public $third_ss;
	public $third_relat;
	public $third_copay;
	public $referral_id;
	public $ref_providerID;
	public $referral_full_name;
	public $referral_last_first;
	public $providerID;
	public $doctor_full_name;
	public $doctor_last_first;
	public $doctor_initials;
	
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
			$this->email_direct = $data['email_direct'];
			$this->ethnicity = $data['ethnicity'];
			$this->occupation = $data['occupation'];
			if(array_key_exists('wmt_education', $data)) $this->wmt_education = $data['wmt_education'];
			$this->family_size = $data['family_size'];
			$this->monthly_income = $data['monthly_income'];
			$this->pricelevel = $data['pricelevel'];
			$this->language = $data['language'];
			$this->ethnoracial = $data['ethnoracial'];
			$this->genericname1 = $data['genericname1'];
			$this->genericval1 = $data['genericval1'];
			$this->genericname2 = $data['genericname2'];
			$this->genericval2 = $data['genericval2'];
			$this->contact_relationship = $data['contact_relationship'];
			$this->phone_contact = $data['phone_contact'];
			$this->race = $data['race'];
			$this->country_code = $data['country_code'];
			$this->blood_type = $data['blood_type'];
			$this->rh_factor = $data['rh_factor'];
			$this->referral_id = $data['ref_providerID'];
			$this->ref_providerID = $data['ref_providerID'];
			$this->providerID = $data['providerID'];
			if(array_key_exists('wmt_partner_name', $data)) $this->wmt_partner_name = $data['wmt_partner_name'];
			if(array_key_exists('wmt_partner_ph', $data)) $this->wmt_partner_ph = $data['wmt_partner_ph'];
			if(array_key_exists('wmt_father_name', $data)) $this->wmt_father_name = $data['wmt_father_name'];
			if(array_key_exists('wmt_father_ph', $data)) $this->wmt_father_ph = $data['wmt_father_ph'];
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
			if($this->providerID != '' && $this->providerID != 0) {
				$query = "SELECT lname, fname, mname FROM users WHERE id=?";
				$user= sqlQuery($query, array($this->providerID));
				$this->doctor_full_name = $user['fname'].' '.$user['lname'];
				if($user['mname']) {
					$this->doctor_full_name = $user['fname'].' '.
									$user['mname'].' '.$user['lname'];
				}
				$this->doctor_last_first = $user['lname'].', '.
									$user['fname'].' '.$user['mname'];
				$this->doctor_initials = substr($user{'fname'},0,1).
					substr($user{'mname'},0,1).substr($user{'lname'},0,1);
			} else {
				$this->doctor_full_name = 'No Physician On File';	
				$this->doctor_last_first = 'No Physician On File';	
				$this->doctor_initials = 'N/A';
			}
			if($this->referral_id != '' && $this->referral_id != 0) {
				$query = "SELECT lname, fname, mname FROM users WHERE id=?";
				$user= sqlQuery($query, array($this->referral_id));
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
			$flds = sqlListFields('insurance_data');
			$binds = array('primary', $this->pid);
			$query = 'SELECT ins.*, ic.* FROM insurance_data AS ins '.
				'LEFT JOIN insurance_companies AS ic ON ins.provider = ic.id '.
				'LEFT JOIN phone_numbers AS ph ON '.
				'(ic.id = ph.foreign_id AND ph.type = 2) '.
				'WHERE ins.type = ? AND ins.pid = ? ';
			if(in_array('termination_date', $flds)) {
				$query .= 'AND (ins.termination_date IS NULL OR ' .
					'ins.termination_date = "" OR ins.termination_date = "0000-00-00") ';
			}
			if($ageWhen) {
				$query .= 'AND ins.date <= ? ';
				$binds[] = $ageWhen;
			}
			$query .= 'AND ins.provider IS NOT NULL AND ins.provider > 0 '.
				'ORDER BY ins.date DESC LIMIT 1';
			$data = sqlQuery($query, $binds);
			if($data) {
				if(!isset($data['area_code'])) $data['area_code'] = '';
				if(!isset($data['prefix'])) $data['prefix'] = '';
				if(!isset($data['number'])) $data['number'] = '';
				$phone = $data['area_code'];
				if($data['prefix']) {
					if($phone != '') $phone .= '-';
					$phone .= $data['prefix'];
				}
				if($data['number']) {
					if($phone != '') $phone .= '-';
					$phone .= $data['number'];
				}
				$this->primary = $data['name'];
				$this->primary_unique = $data['id'];
				$this->primary_provider = $data['provider'];
				$this->primary_attn = $data['attn'];
				$this->primary_phone = $phone;
				$this->primary_id = $data['policy_number'];
				$this->primary_group = $data['group_number'];
				$this->primary_lname = $data['subscriber_lname'];
				$this->primary_fname = $data['subscriber_fname'];
				$this->primary_mname = $data['subscriber_mname'];
				$this->primary_DOB = oeFormatShortDate($data['subscriber_DOB']);
				$this->primary_ss= $data['subscriber_ss'];
				$this->primary_relat = $data['subscriber_relationship'];
				$this->primary_copay = $data['copay'];
				if(array_key_exists('freeb_type', $data)) 
							$this->primary_plan_type = $data['freeb_type'];
				if(array_key_exists('ins_type_code', $data)) 
							$this->primary_plan_type = $data['ins_type_code'];
			}
			$binds[0] = 'secondary';
			$data = sqlQuery($query, $binds);
			if($data) {
				if(!isset($data['area_code'])) $data['area_code'] = '';
				if(!isset($data['prefix'])) $data['prefix'] = '';
				if(!isset($data['number'])) $data['number'] = '';
				$phone = $data['area_code'];
				if($data['prefix']) {
					if($phone != '') $phone .= '-';
					$phone .= $data['prefix'];
				}
				if($data['number']) {
					if($phone != '') $phone .= '-';
					$phone .= $data['number'];
				}
				$this->secondary = $data['name'];
				$this->secondary_unique = $data['id'];
				$this->secondary_provider = $data['provider'];
				$this->secondary_attn = $data['attn'];
				$this->secondary_phone = $phone;
				$this->secondary_id = $data['policy_number'];
				$this->secondary_group = $data['group_number'];
				$this->secondary_lname = $data['subscriber_lname'];
				$this->secondary_fname = $data['subscriber_fname'];
				$this->secondary_mname = $data['subscriber_mname'];
				$this->secondary_DOB = oeFormatShortDate($data['subscriber_DOB']);
				$this->secondary_ss= $data['subscriber_ss'];
				$this->secondary_relat = $data['subscriber_relationship'];
				$this->secondary_copay = $data['copay'];
				if(array_key_exists('freeb_type', $data)) 
					$this->secondary_plan_type = $data['freeb_type'];
				if(array_key_exists('ins_type_code', $data)) 
					$this->secondary_plan_type = $data['ins_type_code'];
			}
			$binds[0] = 'tertiary';
			$data = sqlQuery($query, $binds);
			if($data) {
				if(!isset($data['area_code'])) $data['area_code'] = '';
				if(!isset($data['prefix'])) $data['prefix'] = '';
				if(!isset($data['number'])) $data['number'] = '';
				$phone = $data['area_code'];
				if($data['prefix']) {
					if($phone != '') $phone .= '-';
					$phone .= $data['prefix'];
				}
				if($data['number']) {
					if($phone != '') $phone .= '-';
					$phone .= $data['number'];
				}
				$this->third = $data['name'];
				$this->third_unique = $data['id'];
				$this->third_provider = $data['provider'];
				$this->third_attn = $data['attn'];
				$this->third_phone = $phone;
				$this->third_id = $data['policy_number'];
				$this->third_group = $data['group_number'];
				$this->third_lname = $data['subscriber_lname'];
				$this->third_fname = $data['subscriber_fname'];
				$this->third_mname = $data['subscriber_mname'];
				$this->third_DOB = oeFormatShortDate($data['subscriber_DOB']);
				$this->third_ss= $data['subscriber_ss'];
				$this->third_relat = $data['subscriber_relationship'];
				if(array_key_exists('freeb_type', $data)) 
					$this->third_plan_type = $data['freeb_type'];
				if(array_key_exists('ins_type_code', $data)) 
					$this->third_plan_type = $data['ins_type_code'];
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
	public static function getPidPatient($pid, $ageWhen='') {
		if(!$pid) {
			throw new Exception('wmtPatData::getPidPatient - no patient identifier provided.');
		}
		
		$results = sqlStatementNoLog("SELECT id FROM patient_data WHERE pid =?",
			 array($pid));
		$data = sqlFetchArray($results);
		return new wmtPatData($data['id'], $ageWhen);
	}

	/**
	 * Retrieve a single patient policy by insurance data ID value. 
	 * 
	 * @static
	 * @param int $pid patient record pid
	 * @param int $id insurance data record id
	 * @return array instance of patient policy
	 */
	public static function getPidPolicyById($pid, $id = '') {
		if(!$pid) {
			throw new Exception('wmtPatData::getPidPolicyById - no patient identifier provided.');
		}
		if(!$id) return array();
		$query = 'SELECT ins.*, ic.`name`, ic.`attn`, ad.`line1`, ad.`line2`, '.
			'ad.`city`, ad.`state`, ad.`zip`, ad.`plus_four`, ph.`area_code`, '.
			'ph.`prefix`, ph.`number` FROM insurance_data AS ins '.
			'LEFT JOIN insurance_companies AS ic ON ins.`provider` = ic.`id` '.
			'LEFT JOIN phone_numbers AS ph ON '.
			'(ic.`id` = ph.`foreign_id` AND ph.`type` = 2) '.
			'LEFT JOIN addresses AS ad ON (ic.`id` = ad.`foreign_id`) '.
			'WHERE ins.`pid` = ? AND ins.`id` = ?';
		
		$policy = sqlQuery($query, array($pid, $id));
		return $policy;
	}

	/**
	 * Retrieve all patient policy(s) optionally by type. 
	 * 
	 * @static
	 * @param int $pid patient record pid
	 * @param optional type $type policy priority type
	 * @return array of qualifying policies 
	 */
	public static function getPidPolicies($pid, $order_by = '`date` DESC', $type = '') {
		if(!$pid) {
			throw new Exception('wmtPatData::getPidPolicies- no patient identifier provided.');
		}
		$binds = array();
		$query = 'SELECT ins.*, ic.`name`, ic.`attn`, ad.`line1`, ad.`line2`, '.
			'ad.`city`, ad.`state`, ad.`zip`, ad.`plus_four`, ph.`area_code`, '.
			'ph.`prefix`, ph.`number` FROM insurance_data AS ins '.
			'LEFT JOIN insurance_companies AS ic ON ins.`provider` = ic.`id` '.
			'LEFT JOIN phone_numbers AS ph ON '.
			'(ic.`id` = ph.`foreign_id` AND ph.`type` = 2) '.
			'LEFT JOIN addresses AS ad ON (ic.`id` = ad.`foreign_id`) '.
			'WHERE ins.`pid` = ? ';
		$binds[] = $pid;
		if($type) {
			$query .= ' AND ins.`type` = ? ';
			$binds[] = $type;
		}
		/*$query .= 'AND ins.`provider` IS NOT NULL AND ins.`provider` > 0 '.
			'AND ins.`date` IS NOT NULL AND ins.`date` != "0000-00-00" '.
			'AND ins.`date` != "" ';*/
		$query .= 'AND ins.`provider` IS NOT NULL AND ins.`provider` > 0 AND ins.inactive = 0 ';
		$query .= 'ORDER BY ' . $order_by;
		
		
		$fres = sqlStatement($query, $binds);
		$policies = array();
		while($policy = sqlFetchArray($fres)) {
			unset($policy['uuid']);
			$policies[] = $policy;
		}
		return $policies;		
	}

	/**
	 * Retrieve patient policy(s) by effective date and optionally by type. 
   * If no date is provided, we will return all patient policies on file.
	 * 
	 * @static
	 * @param int $pid patient record pid
	 * @param date $date policy was effective
	 * @param optoinal type $type policy priority type
	 * @return array of qualifying policies 
	 */
	public static function getPidPoliciesByDate($pid, $date = '', $type = '') {
		if(!$pid) {
			throw new Exception('wmtPatData::getPidPoliciesByDate - no patient identifier provided.');
		}
		$date = DateToYYYYMMDD($date);
		$flds = sqlListFields('insurance_data');
		$binds = array();
		$query = 'SELECT ins.*, ic.`name`, ic.`attn`, ad.`line1`, ad.`line2`, '.
			'ad.`city`, ad.`state`, ad.`zip`, ad.`plus_four`, ph.`area_code`, '.
			'ph.`prefix`, ph.`number` FROM insurance_data AS ins '.
			'LEFT JOIN insurance_companies AS ic ON ins.`provider` = ic.`id` '.
			'LEFT JOIN phone_numbers AS ph ON '.
			'(ic.`id` = ph.`foreign_id` AND ph.`type` = 2) '.
			'LEFT JOIN addresses AS ad ON (ic.`id` = ad.`foreign_id`) '.
			'WHERE ins.`pid` = ? ';
		$binds[] = $pid;
		if($type) {
			$query .= ' AND ins.`type` = ? ';
			$binds[] = $type;
		}
		$query .= 'AND ins.`provider` IS NOT NULL AND ins.`provider` > 0 '.
			'AND ins.`date` IS NOT NULL AND ins.`date` != "0000-00-00" '.
			'AND ins.`date` != "" AND ins.inactive = 0 ';
		if($date) {
			$query .= ' AND ins.`date` <= ? ';
			$binds[] = $date;
		}
		if(in_array('termination_date', $flds)) {
			if($date) {
				$query .= 'AND (ins.`termination_date` IS NULL OR '.
					'ins.`termination_date` = "" OR ins.`termination_date` >= ? '.
					'OR ins.`termination_date` = "0000-00-00") ';
				$binds[] = $date;
			}
		}
		$query .= 'ORDER BY ins.`type` ASC, `date` DESC';
		
		
		$fres = sqlStatement($query, $binds);
		$policies = array();
		$type = '';
		while($policy = sqlFetchArray($fres)) {
			unset($policy['uuid']);
			if($policy{'type'} != $type) {
				$type = $policy{'type'};
				$policies[$policy{'id'}] = $policy;
			}
		}
		return $policies;		
	}

	/**
	 * Create a select drop down of patient policies, usually with a list 
   * generated by getPidPolicyByDate.
	 * 
	 * @static
	 * @param integer $ins_id, the selected policy ID (if applicable).
	 * @param array $policies of any insurances we want contained here.
	 * @return nothing, echo to screen
	 */
	public static function pidPolicySelect($insId = '', $policies = array(), $emptyLabel = '') {
		if(count($policies) < 1) $empty_label = 'No Policies On File';
  	echo '<option value=""';
  	if(!$insId) echo ' selected="selected"';
  	echo ">$emptyLabel&nbsp;</option>";
  	foreach ($policies as $policy) {
    	echo '<option value="' . $policy['id'] . '"';
    	if($insId === $policy['id']) {
				echo ' selected="selected"';
			}
    	echo '>' . text($policy{'name'}, ENT_QUOTES);
    	echo ': ' . text(oeFormatShortDate($policy{'date'}));
	echo '&nbsp;&nbsp;-&nbsp;';
    	echo text($policy{'subscriber_lname'});
			echo ', ';
    	echo text($policy{'subscriber_fname'});
    	echo '</option>';
  	}
	
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
			// if($key == 'DOB') $value = DateToYYYYMMDD($value);
			$parms[] = $value;
		}
		
		$parms[] = $this->id;
		sqlInsert("UPDATE patient_data SET $query WHERE id = ?", $parms);
		
		return;
			
	}
	
 /**
 * Updates one field of the patient information in the database.
 * 
 * @static
 * @param Errors $iderror_object
 * @return null
 */
	public function updateThis($fld = '', $val = '') {
		// build query from object
		if($fld == '') return false;
		// if($fld == 'DOB')  $val = DateToYYYYMMDD($val);
		$parms = array($val, $this->id);
		
		sqlInsert("UPDATE patient_data SET $fld = ? WHERE id = ?", $parms);
		
		return true;
			
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
