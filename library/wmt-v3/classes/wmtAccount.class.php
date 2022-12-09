<?php
/** **************************************************************************
 *	ACCOUNT.CLASS.PHP
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
 *  @category Portal User Accounts
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
class Account {
	public $id;
	public $pid;
	public $portal_username;
	public $portal_pwd;
	public $portal_pwd_status;
	public $portal_salt;
	public $enroll_date;
	public $last_access;
	public $plain_pwd;

	/**
	 * Constructor for the 'account' class which retrieves the requested
	 * information from the database or creates an empty object.
	 *
	 * @param int $id record identifier
	 * @return object instance of portal account class
	 */
	public function __construct($id=false) {
		// get database fields
		$fields = $this->listFields();
		
		// create empty record
		if (!$id) {
			foreach ($fields as $key) {
				$this->$key = null;
			}
			return false;
		}

		// retrieve record
		$query = "SELECT * FROM `patient_access_onsite` WHERE id = ?";
		$data = sqlQuery($query,array($id));

		if ($data && $data['id']) {
			// load everything returned into object
			foreach ($fields AS $field) {
				$this->$field = $data[$field];
			}
		}
		else {
			throw new \Exception('wmtAccount::_construct - no record with id ('.$id.').');
		}

		$this->plan_pwd = null;
		
		return;
	}

	/**
	 * Stores data from this portal account object into the database.
	 *
	 * @return int $id identifier for object
	 */
	public function store() {
		$insert = true;
		if($this->id) $insert = false;

		// hash password
		if ($this->plain_pwd) {
			require_once($GLOBALS['srcdir']."/authentication/common_operations.php");
			$this->portal_salt = oemr_password_salt();
			$this->portal_pwd = oemr_password_hash($this->plain_pwd,$this->portal_salt);
		}
		
		// create record
		$sql = '';
		$binds = array();
		$fields = $this->listFields();
		
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
			
			// do account form insert
			$this->id = sqlInsert("INSERT INTO `patient_access_onsite` SET $sql",$binds);
			
		} else { // do update
			
			$binds[] = $this->id;		
			sqlStatement("UPDATE `patient_access_onsite` SET $sql WHERE id = ?",$binds);
		
			// run the update
			sqlStatement($sql, $binds);
		}
		
		return $this->id;
	}
	
	/**
	 * Generates a portal password.
	 * 
	 * @static
	 * @param int $length password legth
	 * @param int $strength password strength
	 * @return string portal password 
	 */
	public static function newPassword($length=6,$strength=1) {
		$consonants = 'bdghjmnpqrstvzacefiklowxy';
		$numbers = '0234561789';
		$specials = '@#$%';
		
		$password = '';
		$alt = time() % 2;
		for ($i = 0; $i < $length/3; $i++) {
			if ($alt == 1) {
				$password .= $consonants[(rand() % strlen($consonants))].$numbers[(rand() % strlen($numbers))].$specials[(rand() % strlen($specials))];
				$alt = 0;
			} else {
				$password .= $numbers[(rand() % strlen($numbers))].$specials[(rand() % strlen($specials))].$consonants[(rand() % strlen($consonants))];
				$alt = 1;
			}
		}
	
		return $password;
	}
		
	/**
	 * Validates the email before sending any messages.
	 * 
	 * @static
	 * @param string $email address to be validated
	 * @return boolean result of validation 
	 */
	public static function validEmail($email) {
		if (eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $email)) {
			return true;
		}
		return false;
	}
		
	/**
	 * Activates a portal account for this patient by assigning a username and
	 * password which are inserted into the portal onsite table.
	 * 
	 * @static
	 * @param wmt\Patient patient object
	 * @return wmt\Portal account object
	 */
	public static function doCreate($pid) {
		if (empty($pid))
			throw new \Exception('wmtPortal::newAccount - no patient identifier provided.');

		// Retrieve the patient information
		$pat_data = Patient::getPidPatient($pid);
		
		// Create the account object
		$acct_data = new Account();
		
		// Create username & password
		$fname = preg_replace("/[^A-Za-z]/", '', $pat_data->fname);
		$fname = ucfirst(strtolower($fname));
		$acct_data->portal_username = htmlspecialchars($fname.$pat_data->id,ENT_QUOTES);
		$acct_data->plain_pwd = self::newPassword();
		$acct_data->portal_pwd_status = 0;
		$acct_data->pid = $pid;
		
		// Do insert
		$acct_data->store();

		// Return new account object
		return $acct_data;
	}

	/**
	 * Resets a portal account for this patient by assigning a new
	 * password and reseting the "change password" flag.
	 *
	 * @param wmt\Patient patient object
	 * @return wmt\Portal account object
	 */
	public static function doReset() {
		// Create new password
		$this->plain_pwd = self::newPassword();
		$this->portal_pwd_status = 0;

		// Update record
		$this->store();
		
		return;
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
		$fields = array();
		
		$columns = sqlListFields('patient_access_onsite');
		foreach ($columns AS $property) {
			$fields[] = $property;
		}
		
		return $fields;
	}
	
}

?>