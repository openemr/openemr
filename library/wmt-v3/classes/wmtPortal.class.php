<?php
/** **************************************************************************
 *	PORTAL.CLASS.PHP
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
 *  @subpackage portal
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
 * Provides standardized processing for most portal forms.
 *
 * @package wmt
 * @subpackage portal
 */
class Portal {
	public $id;
	public $created;
	public $date;
	public $pid;
	public $userid;
	public $status;

	// control elements
	protected $portal_name;
	protected $portal_table;
	protected $portal_title;

	/**
	 * Constructor for the 'portal' class which retrieves the requested
	 * information from the database or creates an empty object.
	 *
	 * @param string $portal_table database table
	 * @param int $id record identifier
	 * @return object instance of portal class
	 */
	public function __construct($portal_name, $id=false) {
		if (!$portal_name)
			throw new \Exception('wmtPortal::_construct - no portal form name provided.');

		// store table name in object
		$this->portal_name = $portal_name;
		$this->portal_table = 'portal_'.$portal_name;

		// get database fields
		$fields = $this->listFields(true);
		
		// create empty record
		if (!$id) {
			foreach ($fields as $key) {
				$this->$key = null;
			}
			return false;
		}

		// retrieve record
		$query = "SELECT pf.*, pt.* FROM $this->portal_table pt ";
		$query .= "LEFT JOIN portal_forms pf ON pf.form_id = pt.id AND pf.form_name = ? ";
		$query .= "WHERE pt.id = ?";
		$data = sqlQuery($query,array($portal_name, $id));

		if ($data && $data['id']) {
			// load everything returned into object
			foreach ($fields AS $field) {
				$this->$field = $data[$field];
			}
		}
		else {
			throw new \Exception('wmtPortal::_construct - no record with id ('.$this->portal_table.' - '.$id.').');
		}

		// preformat commonly used data elements
		$this->created = (strtotime($this->created) !== false)? date('Y-m-d H:i:s',strtotime($this->created)) : date('Y-m-d H:i:s');
		$this->date = (strtotime($this->date) !== false)? date('Y-m-d H:i:s',strtotime($this->date)) : date('Y-m-d H:i:s');

		return;
	}

	/**
	 * Stores data from a portal object into the database.
	 *
	 * @return int $id identifier for object
	 */
	public function store() {
		$insert = true;
		if($this->id) $insert = false;

		// set defaults
		$this->date = date('Y-m-d H:i:s');
		if (!$this->status) $this->status = 'C';
		if (!$this->priority) $this->priority = 'N';
		if (!$this->created) $this->created = date('Y-m-d H:i:s');
		
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
			
			// do portal form insert
			$this->id = sqlInsert("INSERT INTO $this->portal_table SET $sql",$binds);
			
			// insert into form index
			$sql = "INSERT INTO `portal_forms` ";
			$sql .= "(`date`, `userid`, `pid`, `form_name`, `form_id`, `form_status`, `form_pdf`, `deleted`) ";
			$sql .= "VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
			
			$binds = array();
			$binds[] = $this->created;
			$binds[] = $this->userid;
			$binds[] = $this->pid;
			$binds[] = $this->portal_name;
			$binds[] = $this->id;
			$binds[] = $this->status;
			$binds[] = $this->form_pdf;
			$binds[] = 0;
				
			// run the insert
			sqlInsert($sql, $binds);

		} else { // do update
			
			$binds[] = $this->id;		
			sqlStatement("UPDATE $this->portal_table SET $sql WHERE id = ?",$binds);
		
			// update portal form index
			$sql = "UPDATE `portal_forms` SET ";
			$sql .= "`userid` = ?, `pid` = ?, `form_status` = ?, `form_pdf` = ?, `deleted` = ? ";
			$sql .= "WHERE `form_name` = ? AND `form_id` = ?";
			
			$binds = array();
			$binds[] = $this->userid;
			$binds[] = $this->pid;
			$binds[] = $this->status;
			$binds[] = $this->form_pdf;
			$binds[] = 0;
			$binds[] = $this->portal_name;
			$binds[] = $this->id;
				
			// run the update
			sqlStatement($sql, $binds);
		}
		
		return $this->id;
	}
	
	/**
	 * Activates a portal account for this patient by assigning a username and
	 * password which are inserted into the portal onsite table.
	 * 
	 * 
	 */

	/**
	 * Returns an array list objects associated with the
	 * given PATIENT and optionally a given TYPE. If no TYPE is given
	 * then all forms for the PATIENT are returned.
	 *
	 * @static
	 * @param int $pid patient identifier
	 * @param string $type type of list to select
	 * @param bool $active active items only flag
	 * @return array $objectList list of selected list objects
	 */
	public static function fetchPidList($form_name, $pid, $active=true, $order=false) {
		if (!$form_name || !$pid)
			throw new \Exception('wmtForm::fetchPidItem - missing parameters');

		if (empty($order)) $order = 'date';
		
		$query = "SELECT form_id FROM forms ";
		$query .= "WHERE formdir = ? AND pid = ? ";
		if ($active) $query .= "AND deleted = 0 ";
		$query .= "ORDER BY $order";

		$results = sqlStatement($query, array($form_name,$pid));

		$objectList = array();
		while ($data = sqlFetchArray($results)) {
			$objectList[] = new Form($form_name,$data['form_id']);
		}

		return $objectList;
	}

	/**
	 * Returns an array list objects associated with the
	 * given ENCOUNTER and optionally a given TYPE. If no TYPE is given
	 * then all issues for the ENCOUNTER are returned.
	 *
	 * @static
	 * @param int $encounter encounter identifier
	 * @param string $type type of list to select
	 * @param bool $active active items only flag
	 * @return array $objectList list of selected list objects
	 */
	public static function fetchEncounterList($form_name, $encounter, $active=true) {
		if (!$form_name || !$encounter)
			throw new \Exception('wmtForm::fetchEncounterItem - missing parameters');

		$query = "SELECT form_id FROM forms ";
		$query .= "WHERE formdir = ? AND encounter = ? ";
		if ($active) $query .= "AND deleted = 0 ";
		$query .= "ORDER BY date, id";

		$results = sqlStatement($query,array($form_name,$encounter));

		$objectList = array();
		while ($data = sqlFetchArray($results)) {
			$objectList[] = new Form($form_name,$data['form_id']);
		}

		return $objectList;
	}

	/**
	 * Returns the most recent form object or an empty object based
	 * on the PID provided.
	 *
	 * @static
	 * @param string $form_name form type name
	 * @param int $pid patient identifier
	 * @param bool $active active items only flag
	 * @return object $form selected object
	 */
	public static function fetchRecent($form_name, $pid, $active=true) {
		if (!$form_name || !$pid)
			throw new \Exception('wmtForm::fetchRecent - missing parameters');

		$query = "SELECT form_id FROM forms ";
		$query .= "WHERE formdir = ? AND pid = ? ";
		if ($active) $query .= "AND deleted = 0 ";
		$query .= "ORDER BY date DESC, id DESC";

		$data = sqlQuery($query,array($form_name,$pid));
		
		return new Form($form_name,$data['form_id']);
	}

	/**
	 * Returns the most recent form object or an empty object based
	 * on the PID provided.
	 *
	 * @static
	 * @param string $form_name form type name
	 * @param int $pid patient identifier
	 * @param bool $active active items only flag
	 * @return object $form selected object
	 */
	public static function fetchEncounter($form_name, $encounter, $active=true) {
		if (!$form_name || !$encounter)
			throw new \Exception('wmtForm::fetchEncounter - missing parameters');

		$query = "SELECT form_id FROM forms ";
		$query .= "WHERE formdir = ? AND encounter = ? ";
		if ($active) $query .= "AND deleted = 0 ";
		$query .= "ORDER BY date DESC, id DESC";

		$data = sqlQuery($query,array($form_name,$encounter));
		
		return new Form($form_name,$data['form_id']);
	}

	/**
	 * Returns an array of valid database fields for the object. Note that this
	 * function only returns fields that are defined in the object and are
	 * columns of the specified database.
	 *
	 * @return array list of database field names
	 */
	public function listFields() {
		if (!$this->portal_table)
			throw new \Exception('wmtPortal::listFields - no portal table name available.');
		
		$fields = array();
		
		$columns = sqlListFields($this->portal_table);
		foreach ($columns AS $property) {
			$fields[] = $property;
		}
		
		return $fields;
	}
	
}

?>