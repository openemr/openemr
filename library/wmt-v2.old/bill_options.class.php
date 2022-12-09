<?php
/** **************************************************************************
 *	LIFESTYLE.CLASS.PHP
 *	This file contains the standard classes for interacting with the          
 *	various misc billing data tables which are optionally available for 
 *  billing as a Williams Medical Technologies option for 
 *  OpenEMR-Pro.  
 *
 *  NOTES:
 *  1) __CONSTRUCT - always uses the record ID to retrieve data from the database
 *  2) GET - uses alternate selectors to find and return associated object
 *  3) FIND - returns only the object ID without data using alternate selectors
 *  4) LIST - returns an array of IDs meeting specific selector criteria
 *  5) FETCH - returns an array of data meeting specific criteria
 *   
 * 
 *  @package OpenEmr-Pro 
 *  @version 1.0
 *  @copyright Williams Medical Technologies, Inc.
 *  @author Rich Genandt <rgenandt@gmail.com>
 * 
 *************************************************************************** */

/** 
 *
 * @package OpenEmr-Pro 
 * @subpackage Standard
 * @category Billing 
 * 
 */
include_once($GLOBALS['srcdir'].'/forms.inc');
include_once($GLOBALS['srcdir'].'/wmt-v2/wmtstandard.inc');

if(!class_exists('wmtMiscBilling')) {

class wmtMiscBilling{
	public $misc_id;
	public $misc_pid;
	public $misc_employment_related;
	public $misc_auto_accident;
	public $misc_accident_state;
	public $misc_date_initial_treatment;
	public $misc_prior_auth_number;
	public $misc_box_15_date_qual;
	public $tmhp_id;
	public $tmhp_pid;
	public $tmhp_type_of_program;
	public $tmhp_number_times_pregnant;
	public $tmhp_number_live_births;
	public $tmhp_number_living_children;
	public $tmhp_birth_control_method_before;
	public $tmhp_birth_control_method_after;
	public $tmhp_practitioner_level;
	public $tmhp_no_contraception_reason;
	public $tmhp_family_planning;
	public $tmhp_title_x_payment;
	public $tmhp_eligibility_date;
	public $tmhp_title_code_value;
	public $tmhp_client_county_code;
	public $tmhp_client_number;

	/**
	 * Constructor for the 'misc_options' class which retrieves the requested 
	 * dashboard information from the database or creates an empty object.
	 * 
	 * @param int $id dashboard record identifier
	 * @return object instance of dashboard class
	 */
	public function __construct($misc_id = false, $tmhp_id = false) {

		if($misc_id) {
			$query = "SELECT * FROM form_misc_billing_options WHERE id = ?";
			$result = sqlQuery($query, array($misc_id));
	
			if (isset($result{'id'})) {
				foreach($result as $key => $val) {
					if($key == 'date' || $key == 'user' || $key == 'groupname' ||
						$key == 'authorized' || $key == 'activity') continue;
					$key = 'misc_' . $key;
					$this->$key = $val;
				}
					
			} else {
				throw new Exception('wmtMiscBilling::_construct - no misc_billing_options record with id ('.$misc_id.').');
			}
		}

		if($tmhp_id) {
			$query = "SELECT * FROM form_tmhp_billing_options WHERE id = ?";
			$result = sqlQuery($query, array($tmhp_id));
	
			if (isset($result{'id'})) {
				foreach($result as $key => $val) {
					if($key == 'date' || $key == 'user' || $key == 'groupname' ||
						$key == 'authorized' || $key == 'activity') continue;
						$key = 'tmhp_' . $key;
						$this->$key = $val;
				}
					
			} else {
				throw new Exception('wmtMiscBilling::_construct - no tmhp_billing_options record with id ('.$tmhp_id.').');
			}
		}

	}	

	/**
	 * Retrieve a billing options object by linked values (pid, encounter). 
	 * Uses the base constructor for the 'bill options' class to create and 
	 * return the object.  
	 * 
	 * @static
	 * @param int $pid patient record pid
	 * @param int $enc desired encounter
	 * @return object instance of billing options class
	 */
	public static function getEncounterBillingOptions($pid, $enc) {
		if(!$pid) {
			throw new Exception('wmtMiscBilling::getEncounterBillingOptions - no patient identifier provided.');
		}
		if(!$enc) {
			throw new Exception('wmtMiscBilling::getEncounterBillingOptions - no encounter identifier provided.');
		}
		
  	$sql = 'SELECT form_id, formdir, encounter FROM forms WHERE formdir=? '.
			'AND pid=? AND deleted=? AND encounter=? ORDER BY date DESC LIMIT 1';
  	$result = sqlQuery($sql, array('misc_billing_options', $pid, 0, $enc));
		if(!isset($result{'form_id'})) $result{'form_id'} = false;
		$misc_id = $result['form_id'];

		$tmhp_id = false;
		if(self::useTmhp()) {
  		$sql = 'SELECT form_id, formdir, encounter FROM forms WHERE formdir=? '.
				'AND pid=? AND deleted=? AND encounter=? ORDER BY date DESC LIMIT 1';
  		$result = sqlQuery($sql, array('tmhp_billing_options', $pid, 0, $enc));
			if(!isset($result{'form_id'})) $result{'form_id'} = false;
			$tmhp_id = $result['form_id'];
		}
		return new wmtMiscBilling($misc_id, $tmhp_id);
	}

	/**
	 * Retrieve a lifestyle object by linked form values (formdir, id, pid). 
	 * Uses the base constructor for the 'lifestyle' class to create and 
	 * return the object.  
	 * 
	 * @static
	 * @param int $pid patient record pid
	 * @return object instance of lifestyle class
	 */
	public static function getPrevious($pid) {
		if(!$pid) {
			throw new Exception('wmtMiscBilling::getPrevious - no patient identifier provided.');
		}
		
  	$old = sqlQuery('SELECT form_id, formdir, encounter FROM forms WHERE '.
			'formdir=? AND pid=? AND deleted=? ORDER BY '.
			'date DESC LIMIT 1', array('misc_billing_options', $pid, 0));
		if(!isset($old{'form_id'})) $old{'form_id'} = false;
		$misc_id = $old{'form_id'};
		
		$tmhp_id = false;	
		if(self::useTmhp()) {
  		$old = sqlQuery('SELECT form_id, formdir, encounter FROM forms WHERE '.
				'formdir=? AND pid=? AND deleted=? ORDER BY '.
				'date DESC LIMIT 1', array('tmhp_billing_options', $pid, 0));
			if(!isset($old{'form_id'})) $old{'form_id'} = false;
			$tmhp_id = $old{'form_id'};
		}
		return new wmtMiscBilling($misc_id, $tmhp_id);
	}
	
/**
 * Inserts data from a misc billing object into the database.
 * 
 * @static
 * @param 
 * @return boolean for table existence
 */
	public static function useTmhp() {
		$use = sqlQuery('SHOW TABLES LIKE "form_tmhp_billing_options"');
		return $use;
	}

 /**
 * Updates the misc billin information in the database.
 * 
 * @static
 * @return null
 */
	public static function updateMisc($data) {
		$binds = array($_SESSION['authUser'], $_SESSION['userauthorized']);
		$query = "date=NOW(), user=?, authorized=?, activity=1";
		$fields = sqlListFields('form_misc_billing_options');
		$fields = array_slice($fields,7);
		foreach($data as $key => $value) {
			if($key == 'misc_id' || $key == 'misc_pid') continue;
			if(substr($key,0,5) != 'misc_') continue;
			$key = substr($key,5);
			if (!in_array($key, $fields)) continue;
			if ($query) $query .= ", ";
			$query .= "$key = ?";
			$binds[] = $value;
		}
		$binds[] = $data['misc_id'];	
		sqlStatement("UPDATE form_misc_billing_options SET $query WHERE id = ?",
			$binds);

		return true;
	}

	/**
	 * Inserts data from a misc billing object into the database.
	 * 
	 * @static
	 * @param 
	 * @return table id of inserted data
	 */
	public static function insertMisc(wmtMiscBilling $object, $enc) {
		if($object->misc_id) {
			throw new Exception("wmtMiscBilling::insertMisc - object already contains identifier");
		}

		$binds = array($object->misc_pid, $_SESSION['authUser'], 
				$_SESSION['authProvider'], $_SESSION['userauthorized'], 1);
		$object->misc_id = sqlInsert("INSERT INTO form_misc_billing_options SET " .
			"date = NOW(), pid = ?, user = ?, groupname = ?, authorized = ?, " .
			"activity = ?", $binds);
		
		addForm($enc, 'Misc Billing Options', $object->misc_id, 
			'misc_billing_options', $object->misc_pid, $_SESSION['userauthorized']);
		return $object->misc_id;
	}

	/**
	 * Updates the billing options table entries after adding if necessary
	 * 
	 * @static
	 * @param 
	 * @return table id of inserted data
	 */
	public static function addOrUpdateMisc($pid, $enc, $data) {
		if(!$pid) {
			throw new Exception('wmtMiscBilling::addOrUpdateMisc - no patient identifier provided.');
		}
		if(!$enc) {
			throw new Exception('wmtMiscBilling::addOrUpdateMisc - no encounter identifier provided.');
		}
		$existing = self::getEncounterBillingOptions($pid, $enc);
		$existing->misc_pid = $pid;
		$have_data = false;
		foreach($data as $key => $value) {
			if($value != '' && $value != 0) $have_data = true;
		}
		if(!$existing->misc_id && $have_data) 
					$existing->misc_id = self::insertMisc($existing, $enc);
		$data['misc_id'] = $existing->misc_id;
		if($existing->misc_id) self::updateMisc($data);
	}

 /**
 * Updates the tmhp billing information in the database.
 * 
 * @static
 * @return null
 */
	public static function updateTmhp($data) {
		$binds = array($_SESSION['authUser'], $_SESSION['userauthorized']);
		$query = "date=NOW(), user=?, authorized=?, activity=1";
		$fields = sqlListFields('form_tmhp_billing_options');
		$fields = array_slice($fields,7);
		foreach($data as $key => $value) {
			if($key == 'tmhp_id' || $key == 'tmhp_pid') continue;
			if(substr($key,0,5) != 'tmhp_') continue;
			$key = substr($key,5);
			if (!in_array($key, $fields)) continue;
			if ($query) $query .= ", ";
			$query .= "$key = ?";
			$binds[] = $value;
		}
		$binds[] = $data['tmhp_id'];	
		sqlStatement("UPDATE form_tmhp_billing_options SET $query WHERE id = ?",
			$binds);

		return true;
	}

	/**
	 * Inserts data from a tmhp billing object into the database.
	 * 
	 * @static
	 * @param 
	 * @return table id of inserted data
	 */
	public static function insertTmhp(wmtMiscBilling $object, $enc) {
		if($object->tmhp_id) {
			throw new Exception("wmtMiscBilling::insertTmhp - object already contains identifier");
		}

		$binds = array($object->tmhp_pid, $_SESSION['authUser'], 
				$_SESSION['authProvider'], $_SESSION['userauthorized'], 1);
		$object->misc_id = sqlInsert("INSERT INTO form_tmhp_billing_options SET " .
			"date = NOW(), pid = ?, user = ?, groupname = ?, authorized = ?, " .
			"activity = ?", $binds);
		
		addForm($enc, 'TMHP Billing Options', $object->tmhp_id, 
			'tmhp_billing_options', $object->tmhp_pid, $_SESSION['userauthorized']);
		return $object->tmhp_id;
	}

	/**
	 * Updates the billing options table entries after adding if necessary
	 * 
	 * @static
	 * @param 
	 * @return table id of inserted data
	 */
	public static function addOrUpdateTmhp($pid, $enc, $data) {
		if(!$pid) {
			throw new Exception('wmtMiscBilling::addOrUpdateTmhp - no patient identifier provided.');
		}
		if(!$enc) {
			throw new Exception('wmtMiscBilling::addOrUpdateTmhp - no encounter identifier provided.');
		}
		$existing = self::getEncounterBillingOptions($pid, $enc);
		$existing->tmhp_pid = $pid;
		$have_data = false;
		foreach($data as $key => $value) {
			if($value != '' && $value != 0) $have_data = true;
		}
		if(!$existing->tmhp_id && $have_data)
					$existing->tmhp_id = self::insertTmhp($existing, $enc);
		$data['tmhp_id'] = $existing->tmhp_id;
		if($existing->tmhp_id) self::updateTmhp($data);
	}

}

}

?>
