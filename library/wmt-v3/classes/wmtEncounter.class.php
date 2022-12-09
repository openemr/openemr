<?php
/** **************************************************************************
 *	wmtEncounter
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
 *  @subpackage form
 *  @version 2.0.0
 *  @category Form Base Class
 *  @copyright Medical Technology Services
 *  @author Ron Criswell <ron.criswell@MDTechSvcs.com>
 *
 ******************************************************************************************** */

/**
 * All new classes are defined in the WMT namespace
 */
namespace wmt;

/** 
 * Provides standardized base class for an encounter which
 * is typically extended for specific types of encounters.
 *
 * @package WMT
 * @subpackage Encounter
 */
class Encounter {
	public $id;
	public $date;
	public $reason;
	public $facility;
	public $facility_id;
	public $pid;
	public $encounter;
	public $onset_date;
	public $sensitivity;
	public $billing_note;
	public $pc_catname;
	public $pc_catid;
	public $provider_id;
	public $supervisor_id;
	public $referral_source;
	public $billing_facility;
	
	/**
	 * Constructor for the 'encounter' class which retrieves the requested 
	 * record from the database or creates an empty object.
	 * 
	 * @param int $id record identifier
	 * @return object instance of class
	 */
	public function __construct($id = false) {
		if(!$id) return false;

		$query = "SELECT fe.*, pc.pc_catname FROM form_encounter fe ";
		$query .= "LEFT JOIN openemr_postcalendar_categories pc ON fe.pc_catid = pc.pc_catid ";
		$query .= "WHERE fe.id = ? ";
		$query .= "ORDER BY fe.date, fe.id";
		$results = sqlStatement($query,array($id));
	
		if ($data = sqlFetchArray($results)) {
			// load everything returned into object
			foreach ($data as $key => $value) {
				if ($key == 'date' || $key == 'onset_date') {
					$value = date('Y-m-d', strtotime($value));
				}
				$this->$key = $value;
			}
		}
		else {
			throw new \Exception('wmtEncounter::_construct - no encounter record with id ('.$id.').');
		}
	}	
		
	/**
	 * Inserts data from an error object into the database.
	 * 
	 * @static
	 * @param Errors $iderror_object
	 * @return null
	 */
	public static function insert(Encounter $object) {
		if($object->id)
			throw new \Exception ("wmtEncounter::insert - object already contains identifier");

		// get facility name from id
		$fres = sqlQuery("SELECT name FROM facility WHERE id = ?",array($object->facility_id));
		$object->facility = $fres['name'];

		// create basic encounter
		$object->encounter = generate_id(); // in sql.inc
		
		// verify dates (strtotime returns false on invalid date)
		if (! strtotime($object->date)) $object->date = date('Y-m-d');
		if (! strtotime($object->onset_date)) $object->onset_date = $object->date;

		// build sql insert from object
		$query = '';
		$params = array();
		$fields = self::listFields();
		foreach ($object as $key => $value) {
			if (!in_array($key, $fields)) continue;
			if ($value == 'YYYY-MM-DD') continue;
			$query .= ($query)? ", `$key` = ? " : "`$key` = ? ";
			$params[] = ($value == 'NULL')? "" : $value;
		}

		// run the insert
		$object->id = sqlInsert("INSERT INTO form_encounter SET $query",$params);

		return $object->id;
	}

	/**
	 * Updates data from an object into the database.
	 * 
	 * @static
	 * @param wmtEncounter $object
	 * @return null
	 */
	public function update() {
		if(! $object->id)
			throw new \Exception ("wmtEncounter::update - no identifier provided in object");

		// build sql update from object
		$query = '';
		$fields = self::listFields();
		$params = array($this->id); // keys
		foreach ($this as $key => $value) {
			if (!in_array($key, $fields) || $key == 'id') continue;
			if ($value == 'YYYY-MM-DD') continue;
			$query .= ($query)? ", `$key` = ? " : "`$key` = ? ";
			$params[] = ($value == 'NULL')? "" : $value;
		}

		// run the update
		sqlInsert("UPDATE form_encounter SET $query WHERE id = ?",$params);

		return;
	}

	/**
	 * Retrieve the diagnoses list for this encounter.
	 * 
	 * @return array of diagnosis data
	 */
	public function getEncDiag() {
		if (!$this->encounter)
			throw new \Exception ("wmtEncounter::getEncDiag - no encounter identifier in current record.");
		
		$query = "SELECT lis.diagnosis, lis.title FROM `issue_encounter` ise ";
		$query .= "LEFT JOIN `lists` lis ON ise.list_id = lis.id AND lis.type LIKE 'medical_problem' ";
		$query .= "WHERE ise.encounter = ?";
		$result = sqlStatement($query,array($this->encounter));
		
		$data = array();
		while ($record = sqlFetchArray($result)) {
			$data[$record['diagnosis']] = $record['title'];
		}
			
		return $data;
	}
	
	/**
	 * 
	 * @param int $id lists record identifier
	 * @return object instance of lists class
	 */
	public static function listPidEncounters($pid) {
		if (!$pid) return FALSE;

		$query = "SELECT fe.encounter, fe.id FROM form_encounter fe ";
		$query .= "LEFT JOIN issue_encounter ie ON fe.id = ie.list_id ";
		$query .= "LEFT JOIN lists l ON ie.list_id = l.id ";
		$query .= "WHERE fe.pid = ? AND l.enddate IS NULL ";
		$query .= "ORDER BY fe.date, fe.encounter";

		$results = sqlStatement($query,array($pid));
	
		$txList = array();
		while ($data = sqlFetchArray($results)) {
			$txList[] = array('id' => $data['id'], 'encounter' => $data['encounter']);
		}
		
		return $txList;
	}

	/**
	 * Retrieve the encounter record by encounter number.
	 * 
	 * @param int $id lists record identifier
	 * @return object instance of lists class
	 */
	public static function getEncounter($encounter) {
		if (!$encounter)
			throw new \Exception ("wmtEncounter::getEncounter - no encounter identifier provided.");
		
		$query = "SELECT id FROM form_encounter WHERE encounter = ?";
		$data = sqlQuery($query,array($encounter));
		
		if (!$data || !$data['id'])
			throw new \Exception ("wmtEncounter::getEncounter - no encounter for provided identifier.");
			
		return new Encounter($data['id']);
	}
}

?>