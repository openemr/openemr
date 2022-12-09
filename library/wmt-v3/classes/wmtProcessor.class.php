<?php
/** **************************************************************************
 *	PROCESSOR.CLASS.PHP
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
 *  @subpackage processor
 *  @version 1.0.0
 *  @category Laboratory Data Utilities
 *  @copyright Medical Technology Services
 *  @author Ron Criswell <ron.criswell@MDTechSvcs.com>
 *
 ******************************************************************************************** */

/**
 * All new classes are defined in the WMT namespace
 */
namespace wmt;

/** 
 * Provides a representation of the processor data record. Fields are dymanically
 * processed based on the current database definitions. 
 *
 * @package wmt
 * @subpackage processor
 */
class Processor {
	// Selected elements
	public $ppid;
	public $name;
	public $npi;
	public $send_app_id;
	public $send_fac_id;
	public $recv_app_id;
	public $recv_fac_id;
	public $DorP;
	public $protocol;
	public $remote_host;
	public $login;
	public $password;
	public $orders_path;
	public $results_path;
	public $notes;
	public $type;
	public $remote_port;
	public $direction;
	public $lab_director;
	
	/**
	 * Constructor for the 'processor' class which retrieves the requested 
	 * information from the database or creates an empty object.
	 * 
	 * @param int $ppid processor record identifier
	 * @return object instance of processor class
	 */
	public function __construct($ppid = false) {
		// create empty record or retrieve
		if (!$ppid) return false;

		// retrieve data
		$query = "SELECT * FROM `procedure_providers` WHERE `ppid` = ?";
		$binds = array($ppid);
		$data = sqlQuery($query,$binds);

		$fields = $this->listFields(true);
		
		if ($data && $data['name']) {
			// load everything returned into object
			foreach ($fields AS $field) {
				$this->$field = $data[$field];
			}
		}
		else {
			throw new \Exception('wmtProcessor::_construct - no provider record with id ('.$id.').');
		}
		
		return;
	}	


	/**
	 * Retrieve a processor object by NPI value. Uses the base constructor for the 'processor' class 
	 * to create and return the object. 
	 * 
	 * @static
	 * @param string $npi processor npi
	 * @return object instance of processor class
	 */
	public static function getNpiProcessor($npi) {
		if(!$npi)
			throw new \Exception('wmtProcessor::getNpiProcessor - no processor NPI provided.');
		
		$data = sqlQuery("SELECT `ppid` FROM `procedure_providers` WHERE `npi` LIKE ?", array($npi));
		if(!$data || !$data['ppid'])
			throw new \Exception("wmtProcessor::getNpiProcessor - no processor with NPI [$npi] found.");
		
		return new Processor($data['ppid']);
	}
	
	/**
	 * Retrieve list of processor objects
	 *
	 * @static
	 * @param boolean $active - active status flag
	 * @return array $list - list of processor objects
	 */
	public static function fetchProcessors($type=false, $active=true) {
		$binds = array();
		$query = "SELECT `ppid` FROM `procedure_providers` ";
		$query .= "WHERE `npi` != '' AND `npi` IS NOT NULL ";
		$query .= "AND `name` != '' AND `name` IS NOT NULL ";
		
		if ($active) $query .= "AND `DorP` LIKE 'P' ";
		
		if ($type) {
			$binds[] = $type;
			$query .= "AND `protocol` LIKE ? ";
		}
		
		$query .= "ORDER BY name";
		
		$list = array();
		$result = sqlStatementNoLog($query,$binds);
		while ($record = sqlFetchArray($result)) {
			$list[$record['ppid']] = new Processor($record['ppid']);
		}
		
		return $list;
	}

	/**
	 * Build selection list from table data.
	 *
	 * @param int $id - current entry id
	 */
	public static function getOptions($ppid, $default='') {
		$result = '';
		
		// create default if needed
		if ($default) {
			$result .= "<option value='' ";
			$result .= (!$itemId || $itemId == '')? "selected='selected'" : "";
			$result .= ">".$default."</option>\n";
		}

		// get providers
		$list = self::fetchProcessors(false, false);
		
		// build options
		foreach ($list AS $processor) {
			$result .= "<option value='" . $processor->ppid . "' ";
			if ($ppid == $processor->ppid) 
				$result .= "selected=selected ";
			$result .= ">" . $processor->name ."</option>\n";
		}
	
		return $result;
	}
	
	/**
	 * Echo selection option list from table data.
	 *
	 * @param id - current entry id
	 * @param result - string html option list
	 */
	public function showOptions($id, $default='') {
		echo self::getOptions($id, $default);
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

		$columns = sqlListFields('procedure_providers');
		foreach ($columns AS $property) {
			// skip control fields unless full requested
			if ($full || !property_exists($this, $property)) $fields[] = $property;
		}
		
		return $fields;
	}
	
}

?>