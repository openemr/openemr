<?php
/** **************************************************************************
 *	LIFESTYLE.CLASS.PHP
 *	This file contains the standard classes for interacting with the          
 *	'lifestyle' data which is a Williams Medical Technologies option for 
 *  OpenEMR-Pro.  
 *	This class must be included for form/dashboard integration.
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
 * @category Patient
 * 
 */

if(!class_exists('wmtLifestyle')) {

class wmtLifestyle{
	public $id;
	public $pid;
  public $form_dt;
  public $link_id;
  public $link_name;
	public $lf_ace_t;
	public $lf_ace_a;
	public $lf_ace_c;
	public $lf_ace_e;
	public $lf_ace_tot;
	public $lf_t_ace_t;
	public $lf_t_ace_a;
	public $lf_t_ace_c;
	public $lf_t_ace_e;
	public $lf_t_ace_tot;

	public $lf_sc_risks;
	public $lf_sc_tried;
	public $lf_sc_wo;
	public $lf_sc_why_start;
	public $lf_sc_treat;
	public $lf_sc_patch;
	public $lf_sc_reason;
	public $lf_sc_referred;

	public $lf_alc_often;
	public $lf_alc_many;
	public $lf_alc_often_gt;
	public $lf_alc_no_stop;
	public $lf_alc_fail;
	public $lf_alc_morning;
	public $lf_alc_guilt;
	public $lf_alc_memory;
	public $lf_alc_injure;
	public $lf_alc_concern;

	// generated values - none in use currently
	
	/**
	 * Constructor for the 'dashboard' class which retrieves the requested 
	 * dashboard information from the database or creates an empty object.
	 * 
	 * @param int $id dashboard record identifier
	 * @return object instance of dashboard class
	 */
	public function __construct($id = false) {
		if(!$id) return false;

		$query = "SELECT * FROM wmt_lifestyle WHERE id = ?";
		$results = sqlStatementNoLog($query, array($id));
	
		if ($data = sqlFetchArray($results)) {
			foreach($data as $key => $val) {
				if($key != 'date' && $key != 'user' && $key != 'groupname' &&
					$key != 'authorized' && $key != 'activity') $this->$key = $val;
			}
				
		} else {
			throw new Exception('wmtLifestyle::_construct - no lifestyle record with id ('.$id.').');
		}

	}	

	/**
	 * Retrieve a lifestyle object by linked form values (formdir, id, pid). 
	 * Uses the base constructor for the 'lifestyle' class to create and 
	 * return the object.  
	 * 
	 * @static
	 * @param int $pid patient record pid
	 * @param char $frm form directory
	 * @param int $id form id
	 * @return object instance of lifestyle class
	 */
	public static function getFormLifestyle($pid, $frm, $fid) {
		if(!$pid) {
			throw new Exception('wmtLifestyle::getFormLifestyle - no patient identifier provided.');
		}
		
		$results['id'] = '';
		if($frm && $fid) {
			$results = sqlQuery("SELECT id FROM wmt_lifestyle WHERE pid=?".
							" AND link_name=? AND link_id=?", array($pid, $frm, $fid));
		}
		if($results['id']) return new wmtLifestyle($results['id']);
		return new wmtLifestyle();
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
	public static function getRecentLifestyle($pid) {
		if(!$pid) {
			throw new Exception('wmtLifestyle::getFormLifestyle - no patient identifier provided.');
		}
		
		$results = sqlQuery("SELECT id FROM wmt_lifestyle WHERE pid=?".
			" ORDER BY form_dt DESC LIMIT 1", array($pid));
		if($results['id']) return new wmtLifestyle($results['id']);
		return new wmtLifestyle();
	}
	
	
	
  /**
 * Updates the lifestyle information in the database.
 * 
 * @static
 * @return null
 */
	public function update() {
		$binds = array($_SESSION['authUser'], $_SESSION['userauthorized']);
		$query = "date=NOW(), user=?, authorized=?, activity=1";
		$fields = sqlListFields('wmt_lifestyle');
		$fields = array_slice($fields,7);
		foreach ($this as $key => $value) {
			if (!in_array($key, $fields)) continue;
			if ($query) $query .= ", ";
			$query .= "$key=?";
			$binds[] = $value;
		}
		$binds[] = $this->id;	
		sqlStatement("UPDATE wmt_lifestyle SET $query WHERE id = ?", $binds);

		return true;
	}
	

	/**
	 * Inserts data from a lifestyle object into the database.
	 * 
	 * @static
	 * @param 
	 * @return table id of inserted data
	 */
	public static function insert(wmtLifestyle $object) {
		if($object->id) {
			throw new Exception("wmtLifestyle::insert - object already contains identifier");
		}

		$binds = array($object->pid, $_SESSION['authUser'], 
				$_SESSION['authProvider'], $object->link_id, $object->link_name);
		$object->id = sqlInsert('INSERT INTO wmt_lifestyle SET date = NOW(), ' .
			'pid = ?, user = ?, groupname = ?, link_id = ?, link_name = ?', $binds);
		
		return $object->id;
	}

}

}

?>
