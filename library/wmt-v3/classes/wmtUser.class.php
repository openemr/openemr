<?php
/** **************************************************************************
 *	USER.CLASS.PHP
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
 * Provides standardized processing for user records including providers, clinicians, and
 * address book entries.
 *
 * @package wmt
 * @subpackage user
 */
class User {
	public $id;
	public $username;
	public $password;  // deprecated
	public $authorized;
	public $info;
	public $source;
	public $fname;
	public $mname;
	public $lname;
	public $federaltaxid;
	public $federaldrugid;
	public $upic;
	public $facility;
	public $facility_id;
	public $see_auth;
	public $active;
	public $npi;
	public $title;
	public $specialty;
	public $billname;
	public $email;
	public $url;
	public $assistant;
	public $organization;
	public $valedictory;
	public $street;
	public $streetb;
	public $city;
	public $state;
	public $zip;
	public $street2;
	public $streetb2;
	public $city2;
	public $state2;
	public $zip2;
	public $phone;
	public $fax;
	public $phonew1;
	public $phonew2;
	public $phonecell;
	public $notes;
	public $cal_ui;
	public $taxonomy;
	public $ssi_relayhealth;
	public $calendar;
	public $abook_type;
	public $pwd_expiration_date;
	public $pwd_history1;
	public $pwd_history2;
	public $default_warehouse;
	public $impool;
	public $state_license_number;
	public $newcrop_user_role;
	public $cal_name;
	public $cal_fixed;
	
	// generated values
	public $format_name;
	
	/**
	 * Constructor for the 'wmtUser' class which retrieves the requested
	 * information from the database or creates an empty object.
	 *
	 * @param int $id record identifier
	 * @return object instance of wmtUser class
	 */
	public function __construct($id = false) {
		// create empty record or retrieve
		if (!$id) return false;

		// retrieve data
		$query = "SELECT * FROM `users` WHERE id = ?";
		$binds = array($id);
		$data = sqlQuery($query,$binds);

		if ($data && $data['id']) {
			// load everything returned into object
			foreach ($data as $key => $value) {
				$this->$key = $value;
			}
		}
		else {
			throw new \Exception('wmtUser::_construct - no record with user id ('.$id.').');
		}

		// preformat commonly used data elements
		if(!isset($this->date)) $this->date = NULL;
		$this->date = (strtotime($this->date) !== false)? date('Y-m-d H:i:s',strtotime($this->date)) : date('Y-m-d H:i:s');

		$this->format_name = ($this->title)? "$this->title " : "";
		$this->format_name .= ($this->fname)? "$this->fname " : "";
		$this->format_name .= ($this->mname)? substr($this->mname,0,1).". " : "";
		$this->format_name .= ($this->lname)? "$this->lname " : "";
		
		

		return;
	}

	/**
	 * Retrieve list of user objects
	 *
	 * @static
	 * @parm string $facility - id of a specific facility
	 * @param boolean $active - active status flag
	 * @return array $list - list of provider objects
	 */
	public static function fetchUsers($active=true) {
		$query = "SELECT `id` FROM `users` WHERE `facility_id` > 0 AND `username` != '' ";
		if ($active) $query .= "AND `active` = 1 ";
		$query .= "ORDER BY lname, fname, mname";

		// collect results
		$list = array();
		$result = sqlStatementNoLog($query);
		while ($record = sqlFetchArray($result)) {
			$list[] = new User($record['id']);
		}
		
		return $list;
	}

	/**
	 * Returns a user object for the given user name.
	 *
	 * @static
	 * @param int $pid patient identifier
	 * @param string $type type of list to select
	 * @param bool $active active items only flag
	 * @return array $objectList list of selected list objects
	 */
	public static function fetchUserName($name = false, $active = false) {
		if (!$name)
			throw new \Exception('wmtUser::fetchUserName - missing user name parameter');

		$query = "SELECT `id` FROM `users` WHERE `username` LIKE ? ";
		if ($active) $query .= "AND `active` = 1 ";
		$binds = array($name);

		// run the query
		$data = sqlQuery($query, $binds);
		
		// validate
		if (!$data || !$data['id'])
			throw new \Exception('wmtUser::fetchUserName - no record with user name ('.$name.').');
				
		// create the object
		return new User($data['id']);
	}

	/**
	 * Build selection list from table data.
	 *
	 * @param int $id - current entry id
	 */
	public static function getOptions($username, $default='', $active=true) {
		$result = '';
		
		// create default if needed
		if ($default) {
			$result .= "<option value='' ";
			$result .= (!$username || $username == '')? "selected='selected'" : "";
			$result .= ">".$default."</option>\n";
		}

		// get clinicians
		$list = self::fetchUsers();
		
		// build options
		foreach ($list AS $user) {
			$result .= "<option value='" . $user->username . "' ";
			if ($username == $user->username) 
				$result .= "selected=selected ";
			$result .= ">" . $user->format_name ."</option>\n";
		}
	
		return $result;
	}
	
	/**
	 * Echo selection option list from table data.
	 *
	 * @param id - current entry id
	 * @param result - string html option list
	 */
	public static function showOptions($username, $default='', $active=true) {
		echo self::getOptions($username, $default, $active);
	}
	
	/**
	 * Build selection list from table data.
	 *
	 * @param int $id - current entry id
	 */
	public static function getIdOptions($id, $default='', $active=true) {
		$result = '';
		
		// create default if needed
		if ($default) {
			$result .= "<option value='' ";
			$result .= (!$id || $id == '')? "selected='selected'" : "";
			$result .= ">".$default."</option>\n";
		}

		// get clinicians
		$list = self::fetchUsers();
		
		// build options
		foreach ($list AS $user) {
			$result .= "<option value='" . $user->id . "' ";
			if ($id == $user->id) 
				$result .= "selected=selected ";
			$result .= ">" . $user->format_name ."</option>\n";
		}
	
		return $result;
	}
	
	/**
	 * Echo selection option list from table data.
	 *
	 * @param id - current entry id
	 * @param result - string html option list
	 */
	public static function showIdOptions($id, $default='', $active=true) {
		echo self::getIdOptions($id, $default, $active);
	}
	
}

?>
