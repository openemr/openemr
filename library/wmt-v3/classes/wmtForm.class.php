<?php
/** **************************************************************************
 *	FORM.CLASS.PHP
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
 * Provides standardized processing for most forms.
 *
 * @package wmt
 * @subpackage form
 */
class Form {
	public $id;
	public $created;
	public $date;
	public $pid;
	public $user;
	public $provider;
	public $encounter;
	public $groupname;
	public $authorized;
	public $activity;
	public $form_complete;
	public $form_priority;
	public $approved_by;
	public $approved_dt;
	
	// control elements
	public $form_name;
	public $form_table;
	public $form_title;
	
	/**
	 * Constructor for the 'form' class which retrieves the requested
	 * information from the database or creates an empty object.
	 *
	 * @param string $form_table database table
	 * @param int $id record identifier
	 * @return object instance of form class
	 */
	public function __construct($form_name, $id = false, $form_title = null) {
		if (!$form_name)
			throw new \Exception('wmtForm::_construct - no form name provided.');

		// store table name in object
		$this->form_name = $form_name;
		$this->form_title = $form_title;
		$this->form_table = 'form_'.$form_name;

		// done if this is a new record
		if (!$id) return false;
		
		// retrieve record
		$query = "SELECT f.*, t.* FROM $this->form_table t ";
		$query .= "LEFT JOIN forms f ON f.form_id = t.id AND f.formdir = ? ";
		$query .= "WHERE t.id = ?";
		$data = sqlQuery($query,array($form_name,$id));
		
		if ($data && $data['id']) {
			// load properties returned into object
			foreach ($data AS $key => $value) {
				if ($key == 'form_name' || $key == 'form_table') continue;
				$this->$key = $value;
			}
		}
		else {
			throw new \Exception('wmtForm::_construct - no record with id ('.$this->form_table.' - '.$id.').');
		}

		// preformat commonly used data elements
		$this->created = (strtotime($this->created) !== false)? date('Y-m-d H:i:s',strtotime($this->created)) : date('Y-m-d H:i:s');
		$this->date = (strtotime($this->date) !== false)? date('Y-m-d H:i:s',strtotime($this->date)) : date('Y-m-d H:i:s');

		return;
	}

	/**
	 * Stores data from a form object into the database.
	 *
	 * @return int $id identifier for object
	 */
	public function store() {
		$insert = true;
		if ($this->id) $insert = false;

		// create record
		$sql = '';
		$binds = array();
		$this->activity = 1;
		$fields = $this->listFields(true);
		
		if (empty($this->activity)) $this->activity = 1;
		if (empty($this->created)) $this->created = date('Y-m-d H:i:s');
		if (empty($this->user)) $this->user = $_SESSION['authUser'];
		if (empty($this->authorized)) $this->authorized = $_SESSION['authorized'];
		if (empty($this->groupname)) $this->groupname = $_SESSION['authProvider'];
		if (empty($this->encounter)) $this->encounter = $_SESSION['encounter'];

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
			// insert into form table
			$this->id = sqlInsert("INSERT INTO $this->form_table SET $sql",$binds);

			// insert into form index
			$sql = "INSERT INTO `forms` ";
			$sql .= "(date, encounter, form_name, form_id, pid, user, groupname, authorized, formdir) ";
			$sql .= "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
			
			$binds = array();
			$binds[] = $this->created;
			$binds[] = $this->encounter;
			$binds[] = $this->form_title;
			$binds[] = $this->id;
			$binds[] = $this->pid;
			$binds[] = $this->user;
			$binds[] = $this->groupname;
			$binds[] = $this->authorized;
			$binds[] = $this->form_name;
			
			// run the insert
			sqlInsert($sql, $binds);

		} else { // do update
			$binds[] = $this->id;		
			sqlStatement("UPDATE $this->form_table SET $sql WHERE id = ?",$binds);
		}
		
		return $this->id;
	}

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
	 * @param bool $prepopulate if loading form history items only
	 * @return object $form selected object
	 */
	public static function fetchRecent($form_name, $pid, $active=true, $prepopulate=false) {
		if (!$form_name || !$pid)
			throw new \Exception('wmtForm::fetchRecent - missing parameters');

		$query = "SELECT form_id FROM forms ";
		$query .= "LEFT JOIN form_encounter USING (encounter) ";
		$query .= "WHERE formdir = ? AND forms.pid = ? ";
		if ($active) $query .= "AND deleted = 0 ";
		$query .= "ORDER BY form_encounter.date DESC, forms.id DESC";

		$data = sqlQuery($query,array($form_name,$pid));
		
		$frm = new Form($form_name,$data['form_id']);
		if($prepopulate) {
			$frm->id = '';
			$frm->date = '';
			$frm->user = '';
			$frm->groupname = '';
			$frm->created = '';
			$frm->authorized = '';
			$frm->encounter = '';
			$frm->activity = '';
			$frm->pid = '';
			$frm->approved_by = '';
			$frm->approved_dt = '';
			$frm->form_priority = '';
			$frm->form_complete = '';
			$frm->approved_by = '';
		}
		return $frm;
	}

	/**
	 * Returns the most recent form object or an empty object based
	 * on the form and encounter provided.
	 *
	 * @static
	 * @param string $form_name form type name 
	 * @param int $encounter unique encounter identifier
	 * @param bool $active active items only flag
	 * @param int $pid patient identifier (optional)
	 * @return object $form selected object
	 */
	public static function fetchEncounter($form_name, $encounter, $active=true, $pid='') {
		if (!$form_name || !$encounter)
			throw new \Exception('wmtForm::fetchEncounter - missing parameters');

		$query = "SELECT form_id FROM forms ";
		$query .= "LEFT JOIN form_encounter USING (encounter) ";
		$query .= "WHERE formdir = ? AND encounter = ? ";
		if ($active) $query .= "AND deleted = 0 ";
		$query .= "ORDER BY form_encounter.date DESC, forms.id DESC";

		$data = sqlQuery($query,array($form_name,$encounter));

		if(!$data['form_id'] && !checkSettingMode('wmt::noload_form_history','',$form_name) && $pid) {

			return self::fetchRecent($form_name, $pid, true, true);
		}
		
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
		if (!$this->form_table)
			throw new \Exception('wmtForm::listFields - no form table name available.');
		
		$fields = array();
		
		$columns = sqlListFields($this->form_table);
		foreach ($columns AS $property) {
			$fields[] = $property;
		}
		
		return $fields;
	}
	
}

?>
