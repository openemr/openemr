<?php
/** **************************************************************************
 *	BILLING CLASS
 *
 *	Copyright (c)2017 - Medical Technology Services <MDTechSvcs.com>
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
 *  @subpackage billing
 *  @version 2.0.0
 *  @category General List Utility Functions
 *  @copyright Medical Technology Services
 *  @author Ron Criswell <ron.criswell@MDTechSvcs.com>
 *
 ******************************************************************************************** */

/**
 * All new classes are defined in the WMT namespace
 */
namespace wmt;

/** 
 * Provides general utility functions related to fees and billing.
 *
 * @package wmt
 * @subpackage billing
 */
class Billing {
	public $id; 
	public $date;
	public $code_type;
	public $code;
	public $pid;
	public $provider_id;
	public $user;
	public $groupname;
	public $authorized;
	public $encounter;
	public $code_text;
	public $billed;
	public $activity;
	public $payer_id;
	public $bill_process;
	public $bill_date;
	public $process_date;
	public $process_file;
	public $modifier;
	public $units;
	public $fee;
	public $justify;
	public $target;
	public $x12_partner_id;
	public $ndc_info;
	public $notecodes;
	public $exclude;
	public $external_id;
	public $pricelevel;
	
	/**
	 * Constructor for the 'billing' class which retrieves the requested 
	 * fee information from the database or creates an empty object.
	 * 
	 * @param int $id list record identifier
	 * @return object instance of billing class
	 */
	public function __construct($id=false) {
		if(!$id) return false;

		$query = "SELECT * FROM `billing` WHERE `id` = ?";
		$binds = array($id, $type);
		
		$data = sqlQuery($query,$binds);
	
		if ($data && $data['id']) {
			foreach ($data as $key => $value) {
				if (property_exists($this, $key))
					$this->$key = $value;
			}
		}
		else {
			throw new \Exception('wmtBilling::_construct - no billing fee record with id ('.$id.').');
		}
		
		return;
	}	

	/**
	 * Stores data from a form object into the database.
	 *
	 * @return int $id identifier for object
	 */
	public function store() {
		$insert = true;
		if($this->id) $insert = false;

		// create record
		$sql = '';
		$binds = array();
		$fields = $this->listFields(true);
		
		// selective updates
		foreach ($this AS $key => $value) {
			if ($key == 'id') continue;
			if ($value == 'YYYY-MM-DD' || $value == "_blank") $value = "";

			// both object and database
			if (array_search($key, $fields) !== false) {
				$sql .= ($sql)? ", `$key` = ? " : "`$key` = ? ";
				$binds[] = ($value == 'null')? "" : $value;
			}
		}
		
		// run the statement
		if ($insert) { // do insert
			$this->activity = 1;
			if (!$this->date) $this->date = date('Y-m-d H:i:s');
			$this->id = sqlInsert("INSERT INTO `billing` SET $sql", $binds);
		} else { // do update
			$binds[] = $this->id;		
			sqlStatement("UPDATE `billing` SET $sql WHERE id = ?", $binds);
		}
		
		return $this->id;
	}

	/**
	 * Returns an array of valid database fields for the object.
	 *
	 * @static
	 * @return array list of database field names
	 */
	public function listFields() {
		$fields = sqlListFields('billing');
		return $fields;
	}
	
}
?>