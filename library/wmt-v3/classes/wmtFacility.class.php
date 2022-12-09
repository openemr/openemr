<?php
/** *******************************************************************************************
 *	FACILITY CLASS
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
 *  @subpackage facility
 *  @version 1.0.0
 *  @category Provider Data Utilities
 *  @copyright Medical Technology Services
 *  @author Ron Criswell <ron.criswell@MDTechSvcs.com>
 *
 ******************************************************************************************** */

/**
 * All new classes are defined in the WMT namespace
 */
namespace wmt;

/** 
 * Provides a representation of the facility data record. Fields are dymanically
 * processed based on the current database definitions. 
 *
 * @package wmt
 * @subpackage facility
 */
class Facility {
	// Selected elements
	public $id;
	public $name;
	public $phone;
	public $fax;
	public $street;
	public $city;
	public $state;
	public $postal_code;
	public $service_location;
	public $billing_location;
	
	/**
	 * Constructor for the 'facility' class which retrieves the requested 
	 * patient information from the database or creates an empty object.
	 * 
	 * @param int $id provider record identifier
	 * @return object instance of provider class
	 */
	public function __construct($id = false) {
		// create empty record or retrieve
		if (!$id) return false;

		// retrieve data
		$query = "SELECT * FROM `facility` WHERE `id` = ?";
		$binds = array($id);
		$data = sqlQuery($query,$binds);

		if ($data && $data['id']) {
			// load properties returned into object
			foreach ($data AS $key => $value) {
 				if (property_exists($this, $key))
					$this->$key = $value;
			}
		}
		else {
			throw new \Exception('wmtFacility::_construct - no record with id ('.$id.').');
		}

		return;
	}	
	
	/**
	 * Retrieve list of provider objects
	 *
	 * @static
	 * @parm string $facility - id of a specific facility
	 * @param boolean $active - active status flag
	 * @return array $list - list of provider objects
	 */
	public static function fetchFacilities($service=true,$billing=false) {
		$query = "SELECT `id` FROM `facility` WHERE 1 = 1 ";
		if ($service) $query .= "AND `service_location` = 1 ";
		if ($billing) $query .= "AND `billing_location` = 1 ";
		$query .= "ORDER BY name";
		
		$list = array();
		$result = sqlStatementNoLog($query);
		while ($record = sqlFetchArray($result)) {
			$list[$record['id']] = new Facility($record['id']);
		}
		
		return $list;
	}

	/**
	 * Build selection list from table data.
	 *
	 * @param int $id - current entry id
	 */
	public function getOptions($id, $default='', $service=true, $billing=false) {
		$result = '';
		
		// create default if needed
		if ($default) {
			$result .= "<option value='' ";
			$result .= (!$itemId || $itemId == '')? "selected='selected'" : "";
			$result .= ">".$default."</option>\n";
		}

		// get providers
		$list = self::fetchFacilities($service, $billing);
		
		// build options
		foreach ($list AS $facility) {
			$result .= "<option value='" . $facility->id . "' ";
			if ($id == $facility->id) 
				$result .= "selected=selected ";
			$result .= ">" . $facility->name ."</option>\n";
		}
	
		return $result;
	}
	
	/**
	 * Echo selection option list from table data.
	 *
	 * @param id - current entry id
	 * @param result - string html option list
	 */
	public function showOptions($id, $default='', $service=true, $billing=false) {
		echo self::getOptions($id, $default, $service, $billing);
	}
	
	/**
	 * Returns an array of valid database fields for the object. Note that this
	 * function only returns fields that are defined in the object and are
	 * columns of the specified database.
	 *
	 * @return array list of database field names
	 */
	public function listFields($full=false) {
		$fields = array();

		$columns = sqlListFields('facility');
		foreach ($columns AS $property) {
			// skip control fields unless full requested
			if ($full || !property_exists($this, $property)) $fields[] = $property;
		}
		
		return $fields;
	}
	
}

?>