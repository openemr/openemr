<?php
/** **************************************************************************
 *	VITALS.CLASS.PHP
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
 *  @subpackage vitals
 *  @version 1.0.0
 *  @copyright Medical Technology Services
 *  @author Ron Criswell <ron.criswell@MDTechSvcs.com>
 *
 ******************************************************************************************** */

/**
 * All new classes are defined in the WMT namespace
 */
namespace wmt;

require_once 'wmtForm.class.php';
class Vitals extends Form {
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
	
	public $bps;
	public $bpd;
	public $weight;
	public $height;
	public $temperature;
	public $temp_method;
	public $pulse;
	public $respiration;
	public $note;
	public $BMI;
	public $BMI_status;
	public $waist_circ;
	public $head_circ;
	public $oxygen_saturation;
	public $arm;
	public $prone_bpd;
	public $prone_bps;
	public $diabetes_accucheck;
	public $specific_gravity;
	public $ph;
	public $leukocytes;
	public $nitrate;
	public $protein;
	public $glucose;
	public $ketones;
	public $urobilinogen;
	public $bilirubin;
	public $blood;
	public $hemoglobin;
	public $HCG;
	public $LMP;
	public $flu;
	public $HgbA1c;
	public $h_pylori;
	public $mono;
	public $strep_a;
	public $UPT;
	public $external_id;
	
	/**
	 * Constructor for the 'vitals' class which retrieves the requested
	 * information from the database or creates an empty object.
	 *
	 * @param string $form_table database table
	 * @param int $id record identifier
	 * @return object instance of form class
	 */
	public function __construct($id = false) {
		// run parent create/retrieve
		parent::__construct('vitals', $id, 'Vitals');

		return;
	}

	/**
	 * Inserts data from a form object into the database.
	 *
	 * @param wmtQuick $object
	 * @return int $id identifier for new object
	 */
	public function store() {
		$insert = true;
		if($this->id) $insert = false;

		// add to forms index
		$this->activity = 1;
		if (!$this->created) $this->created = date('Y-m-d H:i:s');
		if (!$this->user) $this->user = $_SESSION['authUser'];
		if (!$this->authorized) $this->authorized = $_SESSION['authorized'];
		if (!$this->groupname) $this->groupname = $_SESSION['authProvider'];
		if (!$this->encounter) $this->encounter = $_SESSION['encounter'];
			
		// insert form through parent
		parent::store();
				
		return $this->id;
	}

	/**
	 * Search and retrieve an order object by encounter and pid
	 *
	 * @static
	 * @param string $enc_num Encounter number for the order
	 * @param int $pid Patient identifier
	 * @param int $enc Encounter identifier
	 * @param string Form type name
	 * @return wmtVitals $object
	 */
	public static function fetchEncounter($enc, $pid) {
		if(! $enc)
			throw new \Exception ("wmtVitals::fetchEncounter - no encounter number provided");

		if(! $pid)
			throw new \Exception ("wmtVitals::fetchEncounter - no patient identifier provided");

		$query = "SELECT `form_id` FROM `forms` fr ";
		$query .= "LEFT JOIN `$this->form_table` ft ON ft.id = fr.form_id ";
		$query .= "WHERE fr.`encounter` = ? AND fr.`pid` = ? AND fr.`formdir` = 'vitals' ";
		$query .= "ORDER BY `date` DESC LIMIT 1";
		$params[] = $enc;
		$params[] = $pid;
		
		$form = sqlQuery($query,$params);

		// creates an new order
		return new Vitals($form['form_id']);
	}

	/**
	 * Returns an array of valid database fields for the object.
	 *
	 * @static
	 * @return array list of database field names
	 */
	public function listFields() {
		$fields = sqlListFields($this->form_table);
		return $fields;
	}

}

?>