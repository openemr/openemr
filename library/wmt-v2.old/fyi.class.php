<?php
/** **************************************************************************
 *	fyi.class.php
 *	This file contains the standard classes for interacting with the          
 *	'Dashboard' which is a Williams Medical Technologies option for OpenEMR.  
 *	This class must be included for dashboard integration.
 *
 *  NOTES:
 *  1) __CONSTRUCT - always uses a record ID to retrieve data from the database
 *  2) GET - uses alternate selectors to find and return associated object
 *  3) FIND - returns only the object ID without data using alternate selectors
 *  4) LIST - returns an array of IDs meeting specific selector criteria
 *  5) FETCH - returns an array of data meeting specific criteria
 *   
 * 
 *  @packagefyi 
 *  @version 1.0
 *  @copyright Williams Medical Technologies, Inc.
 *  @author Rich Genandt <rgenandt@gmail.com>
 * 
 *************************************************************************** */

if(!class_exists('wmtFYI')) {

class wmtFYI{
	public $id;
	public $date;
	public $last_touch;
	public $pid;
	public $user;
	public $groupname;
	public $authorized;
	public $activity;
  public $fyi;
  public $fyi_portal;
	public $fyi_med_nt;
	public $fyi_well_nt;
	public $fyi_portal_med_nt;
	public $fyi_allergy_nt;
	public $fyi_portal_allergy_nt;
	public $fyi_pmh_nt;
	public $fyi_portal_pmh_nt;
	public $fyi_surg_nt;
	public $fyi_portal_surg_nt;
	public $fyi_medhist_nt;
	public $fyi_portal_medhist_nt;
	public $fyi_fh_nt;
	public $fyi_portal_fh_nt;
	public $fyi_action_nt;
	public $fyi_portal_action_nt;
	public $fyi_admissions_nt;
	public $fyi_portal_admissions_nt;
	public $fyi_img_nt;
	public $fyi_portal_img_nt;
	public $fyi_imm_nt;
	public $fyi_portal_imm_nt;
	public $fyi_travel_nt;
	public $fyi_portal_travel_nt;
	public $fyi_pp_nt;
	public $fyi_portal_pp_nt;
	public $fyi_sh_nt;
	public $fyi_portal_sh_nt;
	public $fyi_inj_nt;
	public $fyi_portal_inj_nt;
	public $fyi_treatment_nt;
	public $fyi_portal_treatment_nt;
	public $fyi_portal_hpi;
	public $fyi_portal_well_nt;
	
	// generated values - none in use currently
	
	/**
	 * Constructor for the 'fyi' class which retrieves the requested 
	 * information from the database or creates an empty object. Only one
   * FYI record is on file for each patient.
	 * 
	 * @param int $id fyi record identifier
	 * @return object instance of fyi class
	 */
	public function __construct($id = false) {
		$query = "SELECT * FROM form_fyi WHERE id =?";
		$result = sqlQuery($query, array($id));
	
		if($result['id']) {
			foreach($result as $key => $val) {
				if($key == 'date') $this->last_touch= $result['date'];
				$this->$key = $val;
			}
		}
		else {
			throw new Exception('wmtFYI::_construct - no FYI record with id ('.$this->id.').');
		}
		
		// preformat commonly used data elements here
	}	

	/**
	 * Retrieve an FYI object by PID value. Uses the base constructor 
   * for the 'FYI' class to create and return the object.  Since only 
   * one FYI is allowed per patient we will create a blank one if 
   * nothing is found.	 
	 * 
	 * @static
	 * @param int $pid patient record pid
	 * @return object instance of patient class
	 */
	public static function getPidFYI($pid) {
		if(!$pid) {
			throw new Exception('wmtFYI::getPidFYI - no PID provided.');
		}
		
		$results = sqlQuery("SELECT id FROM form_fyi WHERE pid=?",array($pid));
		if(!$results['id']) {
			$binds = array($pid, $_SESSION['authUser'], $_SESSION['authProvider']);
			$results['id'] = sqlInsert("INSERT INTO form_fyi SET " .
			"date = NOW(), pid = ?, user = ?, groupname = ?", $binds);
    }
		return new wmtFYI($results['id']);
	}

	/**
	 * Retrieve a shortened FYI object by PID value. Uses the base constructor 
   * for the 'FYI' class to create and return the object. We will undset all
	 * fields that are not actual note/data fields for this version. Since only 
   * one FYI is allowed per patient we will create a blank one if 
   * nothing is found.	 
	 * 
	 * @static
	 * @param int $pid patient record pid
	 * @return object instance of patient class
	 */
	public static function getPidDisplayFYI($pid) {
		if(!$pid) {
			throw new Exception('wmtFYI::getPidDisplayFYI - no PID provided.');
		}
		
		$results = sqlQuery("SELECT id FROM form_fyi WHERE pid = ?",array($pid));
		if(!$results['id']) {
			$binds = array($pid, $_SESSION['authUser'], $_SESSION['authProvider']);
			$results['id'] = sqlInsert("INSERT INTO form_fyi SET " .
			"date = NOW(), pid = ?, user = ?, groupname = ?", $binds);
    }
		$fyi = new wmtFYI($results['id']);
		unset($fyi->id);
		unset($fyi->date);
		unset($fyi->pid);
		unset($fyi->user);
		unset($fyi->groupname);
		unset($fyi->authorized);
		unset($fyi->activity);
		return $fyi;
	}
	
  /**
 * Updates the FYI information in the database.
 * 
 * @static
 * @param Errors $iderror_object
 * @return null
 */
	public function update() {
		// build query from object
		$query = '';
		$binds = array();
		$fields = sqlListFields('form_fyi');
		$fields = array_slice($fields,7);
		$current = new wmtFYI($this->id);
		foreach ($this as $key => $value) {
			$value = trim($value);
			if (!in_array($key, $fields)) continue;
			if ($query) $query .= ",";
			$query .= " $key = ?";
			$binds[] = $value;
		}
		$binds[] = $this->id;
		
		sqlInsert("UPDATE form_fyi SET $query WHERE id = ?", $binds);
		
		return;
	}

	/**
	 * Inserts data from an FYI object into the database.
	 * 
	 * @static
	 * @param Errors $iderror_object
	 * @return null
	 */
	public static function insert(wmtFYI $object) {
		if($object->id) {
			throw new Exception("wmtFYI::insert - object contains identifier");
		}

		$binds = array($object->pid, $_SESSION['authUser'], 
				$_SESSION['authProvider']);
		$object->id = sqlInsert("INSERT INTO form_fyi SET " .
			"date = NOW(), pid = ?, user = ?, groupname = ?", $binds);
		
		return $object->id;
	}

	/**
	 * Checks to see if we will update any FYI fields, if so we will 
	 * also set the form date.
	 *
	 * @static
	 * @param - an array of fields indexed by FYI field names
	 * @return - true if we need to update
	 */
	public static function change($values=array(),$object) {
		if(count($values) < 1) return false;
		$change = false;
		$flds = sqlListFields('form_fyi');
		$flds = array_slice($flds,7);
		foreach($values as $key => $val) {
			if(in_array($key, $flds)) {
				if($val && ($val != 'YYYY-MM-DD') && ($val != $object->$key)) {
					$change = true;
					$object->$key = $val;
				}
			}
		}	
	}
}
                                            
}

?>
