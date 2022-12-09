<?php
/** **************************************************************************
 *	PROVIDER.CLASS.PHP
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
 *  @subpackage provider
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
 * Provides a representation of the patient data record. Fields are dymanically
 * processed based on the current database definitions. 
 *
 * @package wmt
 * @subpackage provider
 */
class Provider {
	// Selected elements
	public $id;
	public $username;
	public $authorized;
	public $fname;
	public $mname;
	public $lname;
	public $facility;
	public $facility_id;
	public $active;
	public $specialty;
	public $email;
	public $phone;
	public $calendar;
	
	// Generated values
	public $format_name;
	
	/**
	 * Constructor for the 'provider' class which retrieves the requested 
	 * patient information from the database or creates an empty object.
	 * 
	 * @param int $id provider record identifier
	 * @return object instance of provider class
	 */
	public function __construct($id = false) {
		// create empty record or retrieve
		if (!$id) return false;

		// retrieve data
		$query = "SELECT * FROM `users` WHERE `id` = ?";
		$binds = array($id);
		$data = sqlQuery($query,$binds);

		$fields = $this->listFields(true);
		
		if ($data && $data['username']) {
			// load everything returned into object
			foreach ($fields AS $field) {
				$this->$field = $data[$field];
			}
		}
		else {
			throw new \Exception('wmtProvider::_construct - no provider record with id ('.$id.').');
		}
		
		// preformat commonly used data elements
		$this->format_name = ($this->title)? "$this->title " : "";
		$this->format_name .= ($this->fname)? "$this->fname " : "";
		$this->format_name .= ($this->mname)? substr($this->mname,0,1).". " : "";
		$this->format_name .= ($this->lname)? "$this->lname " : "";

		return;
	}	

	/**
	 * Retrieve a provider object by USERNAME value. Uses the base constructor for the 'provider' class 
	 * to create and return the object. 
	 * 
	 * @static
	 * @param string $username provider user name
	 * @return object instance of provider class
	 */
	public static function getUserProvider($username) {
		if(!$username)
			throw new \Exception('wmtProvider::getUserProvider - no provider username provided.');
		
		$data = sqlQuery("SELECT `id` FROM `users` WHERE `username` LIKE ?", array($username));
		if(!$data || !$data['id'])
			throw new \Exception('wmtProvider::getUserProvider - no provider with username provided.');
		
		return new Provider($data['id']);
	}


	/**
	 * Retrieve a provider object by NPI value. Uses the base constructor for the 'provider' class 
	 * to create and return the object. 
	 * 
	 * @static
	 * @param string $npi provider npi
	 * @return object instance of provider class
	 */
	public static function getNpiProvider($npi) {
		if(!$npi)
			throw new \Exception('wmtProvider::getNpiProvider - no provider NPI provided.');
		
		$data = sqlQuery("SELECT `id` FROM `users` WHERE `npi` LIKE ?", array($npi));
		if(!$data || !$data['id'])
			throw new \Exception('wmtProvider::getNpiProvider - no provider with NPI provided.');
		
		return new Provider($data['id']);
	}
	
	/**
	 * Retrieve list of provider objects
	 *
	 * @static
	 * @parm string $facility - id of a specific facility
	 * @param boolean $active - active status flag
	 * @return array $list - list of provider objects
	 */
	public static function fetchProviders($facility=false,$active=true) {
		$binds = null;
		$query = "SELECT `id` FROM `users` WHERE `authorized` = 1 ";
		$query .= "AND `npi` != '' AND `npi` IS NOT NULL ";
		$query .= "AND `username` != '' AND `username` IS NOT NULL ";
		
		if ($facility) {
			$query .= "AND `facility_id` = ? ";
			$binds[] = $facility;
		}

		if ($active) $query .= "AND `active` = 1 ";
		
		$query .= "ORDER BY lname, fname, mname";
		
		$list = array();
		$result = sqlStatementNoLog($query,$binds);
		while ($record = sqlFetchArray($result)) {
			$list[$record['id']] = new Provider($record['id']);
		}
		
		return $list;
	}

	/**
	 * Build selection list from table data.
	 *
	 * @param int $id - current entry id
	 */
	public function getOptions($id, $default='') {
		$result = '';
		
		// create default if needed
		if ($default) {
			$result .= "<option value='' ";
			$result .= (!$id || $id == '')? "selected='selected'" : "";
			$result .= ">".$default."</option>\n";
		}

		// get providers
		$list = self::fetchProviders();
		
		// build options
		foreach ($list AS $provider) {
			$result .= "<option value='" . $provider->id . "' ";
			if ($id == $provider->id) 
				$result .= "selected=selected ";
			$result .= ">" . $provider->format_name ."</option>\n";
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

		$columns = sqlListFields('users');
		foreach ($columns AS $property) {
			// skip control fields unless full requested
			if ($full || !property_exists($this, $property)) $fields[] = $property;
		}
		
		return $fields;
	}
	
}

?>