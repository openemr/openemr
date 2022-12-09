<?php
/** **************************************************************************
 *	PATIENT.CLASS.PHP
 *
 *	Copyright (c)2016 - Medical Technology Services <MDTechSvcs.com>
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
 *  @subpackage patient
 *  @version 2.0.0
 *  @category Patient Data Utilities
 *  @copyright Medical Technology Services
 *  @author Ron Criswell <ron.criswell@MDTechSvcs.com>
 *
 ******************************************************************************************** */

/**
 * All new classes are defined in the WMT namespace
 */
namespace wmt;

/** 
 * Provides a representation of the patient data record. Fields are dymanically
 * processed based on the current database definitions. 
 *
 * @package wmt
 * @subpackage patient
 */
class Patient {
	// generated values
	public $format_name;
	public $birth_date;
	public $age;
	
	/**
	 * Constructor for the 'patient' class which retrieves the requested 
	 * patient information from the database or creates an empty object.
	 * 
	 * @param int $id patient record identifier
	 * @return object instance of patient class
	 */
	public function __construct($id = false) {
		if(!$id) return false;

		$query = "SELECT * FROM `patient_data` WHERE `id` = ?";
		$binds = array($id);
		
		$data = sqlQuery($query,$binds);
	
		if ($data && $data['pid']) {
			// load everything returned into object
			foreach ($data as $key => $value) {
				$this->$key = $value;
			}
		}
		else {
			throw new \Exception('wmtPatient::_construct - no patient record with id ('.$id.').');
		}
		
		// preformat commonly used data elements
		$this->format_name = ($this->title)? "$this->title " : "";
		$this->format_name .= ($this->fname)? "$this->fname " : "";
		$this->format_name .= ($this->mname)? substr($this->mname,0,1).". " : "";
		$this->format_name .= ($this->lname)? "$this->lname " : "";

		if ($this->DOB && strtotime($this->DOB) !== false) { // strtotime returns FALSE
			$this->age = floor( (strtotime('today') - strtotime($this->DOB)) / 31556926 );
			$this->birth_date = date('Y-m-d', strtotime($this->DOB));
		}
		
		return;
	}	

	/**
	 * Stores data from a patient object into the database. This is the 
	 * replacement for the 'insert' and 'update' functions.
	 *
	 * @return int $id identifier for object
	 */
	public function store() {
		$insert = true;
		if($this->id) $insert = false;

		// set defaults
		$this->activity = 1;
		$this->date = date('Y-m-d H:i:s');
		
		// set pid on insert if empty
		if ($insert) {
			if (empty($this->pid)) $this->pid = self::getNewPid();
			if (empty($this->pubpid)) $this->pubpid = $this->pid;
		}
		
		// create record
		$sql = '';
		$binds = array();
		$fields = $this->listFields(true);
		
		// selective updates
		foreach ($fields AS $field) {
			if ($field == 'id') continue;
			
			$value = $this->$field;
			if ($value == 'YYYY-MM-DD' || $value == "_blank") $value = "";
			
			$sql .= ($sql)? ", `$field` = ? " : "`$field` = ? ";
			$binds[] = ($value == 'NULL')? "" : $value;
		}
		
		// run the statement
		if ($insert) { 
			// patient insert
			$this->id = sqlInsert("INSERT INTO `patient_data` SET $sql",$binds);
		} else { 
			// patient update
			$binds[] = $this->id;		
			sqlStatement("UPDATE `patient_data` SET $sql WHERE id = ?",$binds);
		}
		
		return $this->id;
	}
	
	/**
	 * Inserts data from a form object into the database. The columns of the patient table
	 * are used to select the appropriate data from the patient object provided.
	 *
	 * @static
	 * @deprecated
	 * @param wmt\Patient $object
	 * @return int $id identifier for new object
	 */
	public static function insert(Patient $object) {
		$id = $object->store();
		return $id;
	}

	/**
	 * Updates database with information from the current object.
	 *
	 * @deprecated
	 * @return boolean update success flag
	 */
	public function update() {
		$this->store();
		return true;
	}
	
	/**
	 * Returns the next PID for the patient table.
	 *
	 * @static
	 * @return int patient identifier
	 */
	public static function getNewPid() {
		$result = sqlQuery("SELECT MAX(pid)+1 AS pid FROM `patient_data`");
		$pid = ($result['pid'] > 0)? $result['pid']: '1'; 

		return $pid;
	}
	
	/**
	 * Retrieve a patient object by PID value. Uses the base constructor for the 'patient' class 
	 * to create and return the object. 
	 * 
	 * @static
	 * @param int $pid patient record identifier
	 * @return object instance of patient class
	 */
	public static function getPidPatient($pid) {
		if(!$pid)
			throw new \Exception('wmtPatient::getPidPatient - no patient identifier provided.');
		
		$data = sqlQuery("SELECT `id` FROM `patient_data` WHERE `pid` = ?", array($pid));
		if(!$data || !$data['id'])
			throw new \Exception('wmtPatient::getPidPatient - no patient with provided identifier.');
		
		return new Patient($data['id']);
	}

	/**
	 * Returns an array of valid database fields for the object. Note that this
	 * function only returns fields that are defined in the object and are
	 * columns of the specified database.
	 *
	 * @return array list of database field names
	 */
	public function listFields() {
		$fields = array();
		$columns = sqlListFields('patient_data');

		// return all of the fields
		foreach ($columns AS $property) {
			$fields[] = $property;
		}
		
		return $fields;
	}
	
}

?>