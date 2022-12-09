<?php
/** *****************************************************************************************
 *	ALLERGY CLASS
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
 *  @subpackage allergy
 *  @version 1.0
 *  @category Patient Data Utilities
 *  @copyright Medical Technology Services
 *  @author Ron Criswell <ron.criswell@MDTechSvcs.com>
 *
 ****************************************************************************************** */

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
class Allergy {
	public $id;
	public $date;
	public $type;
	public $title;
	public $begdate;
	public $enddate;
	public $returndate;
	public $occurrence;
	public $classification;
	public $extrainfo;
	public $diagnosis;
	public $activity;
	public $comments;
	public $pid;
	public $user;
	public $groupname;
	public $reaction;
	public $external_allergyid;
	public $erx_source;
	public $erx_uploaded;
	public $modifydate;
	
	/**
	 * Constructor for the 'allergy' class which retrieves the requested 
	 * patient information from the database or creates an empty object.
	 * 
	 * @param int $id list record identifier
	 * @return object instance of allergy class
	 */
	public function __construct($id = false) {
		if(!$id) return false;

		$query = "SELECT * FROM `lists` WHERE `id` = ? AND `type` LIKE 'allergy'";
		$binds = array($id);
		
		$data = sqlQuery($query,$binds);
	
		if ($data && $data['id']) {
			foreach ($data as $key => $value) {
				if (property_exists($this, $key))
					$this->$key = $value;
			}
		}
		else {
			throw new \Exception('wmtAllergy::_construct - no allergy list record with id ('.$id.').');
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
			if (key_exists($key, $fields)) {
				$sql .= ($sql)? ", `$key` = ? " : "`$key` = ? ";
				$binds[] = ($value == 'NULL')? "" : $value;
			}
		}
		
		// run the statement
		if ($insert) { // do insert
			$this->activity = 1;
			$this->type = 'allergy';
			if (!$this->date) $this->date = date('Y-m-d H:i:s');
			
			// run the form insert
			$this->id = sqlInsert("INSERT INTO `list` SET $sql",$binds);
		} else { // do update
			$binds[] = $this->id;		
			sqlStatement("UPDATE `list` SET $sql WHERE id = ?",$binds);
		}
		
		return $this->id;
	}

	/**
	 * Retrieve all patient allergy objects by PID value. Uses the base constructor for the 'allergy' class 
	 * to create and return a list of objects. 
	 * 
	 * @static
	 * @param int $pid patient record identifier
	 * @return array $list object instances of allergy class
	 */
	public static function fetchPidList($pid,$active=true) {
		if(!$pid)
			throw new \Exception('wmtAllergy::fetchPidList - no patient identifier provided.');

		$list = array();
		$query = "SELECT `id` FROM `lists` WHERE `pid` = ? AND `type` = 'allergy' ";
		if ($active) $query .= "AND `activity` = 0 ";
		$query .= "ORDER BY `begdate`, `enddate`";
		
		// run the query
		$result = sqlStatement($query, array($pid));
		if ($result && sqlNumRows($result) > 0) {
			while ($record = sqlFetchArray($result)) {
				$list[] = new Allergy($record['id']);
			}
		}
		
		return $list;
	}
}

?>