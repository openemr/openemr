<?php
/** **************************************************************************
 *	PediLatout Class
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
 *  @subpackage detail
 *  @version 2.0.0
 *  @category Form Item Detail Class
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
 * @subpackage detail
 */
class PediLayout {
	public $id;
	public $date;
	public $pid;
	public $user;
	public $encounter;
	public $activity;
	public $layout_title;
	public $layout_key;
	public $layout_list;
	public $layout_data;
	
	/**
	 * Constructor for the 'form' class which retrieves the requested
	 * information from the database or creates an empty object.
	 *
	 * @param string $form_table database table
	 * @param int $id record identifier
	 * @return object instance of form class
	 */
	public function __construct($layout_key, $id = false) {
		if (!$layout_key)
			throw new \Exception('wmtPediLayout::_construct - no layout type provided.');

		// store id
		$this->id = $id;
		
		// retrieve layout data
		if (!$id) {
			// store layout type
			$this->layout_key = $layout_key;
			$this->layout_list = false;
			
			if ($layout_key == 'PE') { 
				// retrieve layout data
				$layout_key = 'PediPE_Categories';

				// retrieve category data
				$query = "SELECT `option_id` AS cat_id, `title` AS cat_name FROM `list_options` ";
				$query .= "WHERE `list_id` = ? AND `activity` = 1 ORDER BY `seq` ";
				
				$result = sqlStatement($query, array($layout_key));
		
				while ($record = sqlFetchArray($result)) {
					$this->layout_cats[$record['cat_id']] = $record;
				}
				
				// retrieve layout data
				$query = "SELECT li.`option_id` AS cat_id, li.`title` AS cat_name, la.* FROM `list_options` li ";
				$query .= "LEFT JOIN `layout_options` la ON li.option_id = la.form_id ";
				$query .= "WHERE li.`list_id` = ? AND uor > 0 ORDER BY li.`seq`, la.`group_name`, la.`seq` ";
				
			} else if ($layout_key == 'APE') { 
				
				// retrieve category data
				$query = "SELECT DISTINCT(`group_name`) FROM layout_options ";
				$query .= "WHERE form_id LIKE ? AND uor > 0 ";
				$query .= "ORDER BY group_name";
				
				$result = sqlStatement($query, array($layout_key));
		
				while ($record = sqlFetchArray($result)) {
					$cat_key = preg_split('/[^\w]/', $record['group_name'])[0];
					$cat_title = strtoupper(substr($record['group_name'],1));
					$this->layout_cats[$cat_key] = $cat_title;
				}
				
				// retrieve layout data
				$query = "SELECT * FROM layout_options ";
				$query .= "WHERE form_id LIKE ? AND uor > 0 ";
				$query .= "ORDER BY group_name, seq ";
				
			} else if ($layout_key == 'ROS') { 
				
				// retrieve category data
				$query = "SELECT `option_id`, `title`, `notes` FROM `list_options` ";
				$query .= "WHERE `list_id` = ? AND `activity` = 1 ORDER BY `seq` ";
				
				$result = sqlStatement($query, array('PediROS_Categories'));
		
				while ($record = sqlFetchArray($result)) {
					$this->layout_cats[$record['option_id']] = $record;
				}
				
				// retrieve layout data
				$layout_key = 'PediROS_Keys';
				
				$query = "SELECT * FROM `list_options` ";
				$query .= "WHERE `list_id` = ? AND (UPPER(`notes`) NOT LIKE '%DO NOT USE%') "; 
				$query .= "ORDER BY `codes`, `seq` ";
				
			} else if ($layout_key == 'AROS') { 
				
				// retrieve category data
				$query = "SELECT `option_id`, `title`, `notes` FROM `list_options` ";
				$query .= "WHERE `list_id` = ? AND `activity` = 1 ORDER BY `seq` ";
				
				$result = sqlStatement($query, array('PediAROS_Categories'));
		
				while ($record = sqlFetchArray($result)) {
					$this->layout_cats[$record['option_id']] = $record;
				}
				
				// retrieve layout data
				$layout_key = 'PediAROS_Keys';
				
				$query = "SELECT * FROM `list_options` ";
				$query .= "WHERE `list_id` = ? AND (UPPER(`notes`) NOT LIKE '%DO NOT USE%') "; 
				$query .= "ORDER BY `codes`, `seq` ";
				
			} else {
				
				// retrieve layout data only
				$query = "SELECT * FROM layout_options ";
				$query .= "WHERE form_id LIKE ? AND uor > 0 ";
				$query .= "ORDER BY group_name, seq ";
			}
			
			$this->layout_list = array();
			$result = sqlStatement($query, array($layout_key));
		
			if ($result) {
				while ($record = sqlFetchArray($result)) {
					$this->layout_list[] = $record;
				}
			}
			
			return;
		}
		
		// retrieve record
		$binds = array($layout_key);
		$query = "SELECT * FROM `form_pedi_layouts` ";
		$query .= "WHERE layout_key = ? ";
		if ($id) {
			$query .= "AND id = ? ";
			$binds[] = $id;
		}
		$data = sqlQuery($query, $binds);

		if ($data && $data['id']) {
			// load properties returned into object
			foreach ($data AS $key => $value) {
				
				if ($key == 'id') continue;
				if ($value == 'YYYY-MM-DD' || $value == "_blank") $value = "";
				if ($key == 'layout_cats' && !empty($value)) $value = json_decode($value, true);
				if ($key == 'layout_list' && !empty($value)) $value = json_decode($value, true);
				if ($key == 'layout_data' && !empty($value)) $value = json_decode($value, true);
				
				$this->$key = $value;
			}
		}
		else {
			throw new \Exception('wmtPediLayout::_construct - no record with id ('.$id.').');
		}

		// preformat commonly used data elements
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
		$fields = $this->listFields();
		
		// selective updates
		foreach ($this AS $key => $value) {
			if ($key == 'id') continue;
			if ($value == 'YYYY-MM-DD' || $value == "_blank") $value = "";
			if ($value && $key == 'layout_cats') $value = json_encode($value);
			if ($value && $key == 'layout_list') $value = json_encode($value);
			if ($value && $key == 'layout_data') $value = json_encode($value);

			// both object and database
			if (array_search($key, $fields) !== false) {
				$sql .= ($sql)? ", `$key` = ? " : "`$key` = ? ";
				$binds[] = ($value == 'null')? "" : $value;
			}
		}
		
		// run the statement
		if ($insert) { // do insert
			$this->id = sqlInsert("INSERT INTO `form_pedi_layouts` SET $sql", $binds);

		} else { // do update

			$binds[] = $this->id;		
			sqlStatement("UPDATE `form_pedi_layouts` SET $sql WHERE id = ?",$binds);
		}
		
		return $this->id;
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
	public static function fetchEncounter($layout_key, $pid, $enc, $active=true) {
		if (!$layout_key || !$pid || !$enc)
			throw new \Exception('wmtPediLayout::fetchEncounter - missing parameters');

		$query = "SELECT `id` FROM `form_pedi_layouts` ";
		$query .= "WHERE `layout_key` = ? AND `pid` = ? AND `encounter` = ? ";
		if ($active) $query .= "AND `activity` = 1 ";

		$data = sqlQuery($query, array($layout_key, $pid, $enc));

		return new PediLayout($layout_key, $data['id']);
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
		
		$columns = sqlListFields('form_pedi_layouts');
		foreach ($columns AS $property) {
			$fields[] = $property;
		}
		
		return $fields;
	}
	
}

?>