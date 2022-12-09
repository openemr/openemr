<?php
/** **************************************************************************
 *	SOAP.CLASS.PHP
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
 *  @package class
 *  @subpackage workup
 *  @version 1.0
 *  @copyright Medical Technology Services
 *  @author Ron Criswell <ron.criswell@MDTechSvcs.com>
 *
 ******************************************************************************************** */

/**
 * All new classes are defined in the WMT namespace
 */
namespace wmt;

require_once 'wmtForm.class.php';
class Workup extends Form {
	/* Inherited from wmtForm
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
	public $status;
	public $priority;
	public $approved_by;
	public $approved_dt;
	public $form_title;
	
	protected $form_name;
	protected $form_table;
	protected $form_title;
	*/
	
	public $sections;
	public $form_version;
	
	/**
	 * Constructor for the 'order' class which retrieves the requested
	 * information from the database or creates an empty object.
	 *
	 * @param string $form_table database table
	 * @param int $id record identifier
	 * @return object instance of form class
	 */
	public function __construct($id=false) {
		// run parent create/retrieve
		parent::__construct('workup', $id, 'Patient Workup');

		// Initialize section parameters
		if (empty($this->sections_data)) {
			
			$section_list = new Options('Workup_Sections');
			foreach ($section_list->list AS $section => $data) {
				if ($data['activity'] != 1) continue;
				
				$sec_data['key'] = $data['option_id'];
				$sec_data['title'] = $data['title'];
				$sec_data['class'] = "wmt\\".$data['notes']."Module";
				$sec_data['prefix'] = $data['codes'];
				$sec_data['open'] = $data['toggle_setting_1'];
				$sec_data['bottom'] = $data['toggle_setting_2'];
				$sec_data['name'] = $section . "_data";

				// store in array
				$this->sections[$section] = $sec_data;
			}
			
		} else {
			
			// convert to array
			$this->sections = json_decode($this->section_data, true);
			
		}

		// Load or create vitals
		$this->vitals = new Vitals($this->vital_id);
		
		return;
	}

	/**
	 * Inserts data from a form object into the database.
	 *
	 * @param wmtWorkup $object
	 * @return int $id identifier for new object
	 */
	public function store() {
		$insert = true;
		if ($this->id) $insert = false;

		// data clean up
		$sections = $this->sections;
		foreach ($sections AS $key => $value) {
			$section['open'] = $key;	
		}
		
		// add escape characters and convert to string
		$this->section_data = json_encode($sections);
		
		// insert form through parent
		parent::store();

		return $this->id;
	}

	
	/**
	 * Search and retrieve an order object by encounter and pid
	 *
	 * @static
	 * @param int $pid Patient identifier
	 * @param int $enc Encounter identifier
	 * @return wmtWorkup $object
	 */
	public static function fetchEncounter($pid, $enc) {
		if(! $pid)
			throw new \Exception ("wmtWorkup::fetchEncounter - no patient identifier provided");

		if(! $enc)
			throw new \Exception ("wmtWorkup::fetchEncounter - no encounter number provided");

		$binds[] = $enc;
		$binds[] = $pid;
		
		$query = "SELECT `form_id` FROM `forms` fr ";
		$query .= "LEFT JOIN `form_workup` wu ON wu.id = fr.form_id ";
		$query .= "WHERE fr.`encounter` = ? AND fr.`pid` = ? AND fr.`formdir` = 'workup' ";
		
		$form = sqlQuery($query,$binds);

		// check for results
		$id = ($form['form_id'])? $form['form_id'] : false;
		
		// creates an new order
		return new Workup($id);
	}


	/**
	 * Returns an array of valid database fields for the object.
	 *
	 * @static
	 * @return array list of database field names
	 */
	public function listFields($full=true) {
		$fields = sqlListFields($this->form_table);

		if (!$full) {
			// remove control fields
			$fields = array_slice($fields,18);
		}
		
		return $fields;
	}

}

?>