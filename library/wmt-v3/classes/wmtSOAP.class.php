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
 *  @subpackage soap
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
class SOAP extends Form {
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
		parent::__construct('full_soap', $id, 'SOAP Evaluation');

		// Initialize section parameters
		if (empty($this->sections_data)) {
			
			$section_list = new Options('SOAP_Sections');
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

		return;
	}

	/**
	 * Inserts data from a form object into the database.
	 *
	 * @param wmtSOAP $object
	 * @return int $id identifier for new object
	 */
	public function store() {
		$insert = true;
		if($this->id) $insert = false;

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
	 * Display a collapsable section in the form.
	 */
	public function display($section, $toggle=0) {
		$this->sections[$section]['toggle'] = ($toggle == 0)? 'none' : 'block';
		
		echo "<div class='wmtMainContainer wmtMainColor'>\n";
		generateChapter($this->sections[$section]['title'], $section, $this->sections[$section]['toggle']);
		echo "<div id='".$section."Box' class='wmtCollapseBox wmtColorBox' style='display: ".$this->sections[$section]['toggle'].";'>\n";
		echo "	</div>\n";
		
		// CONTENT GOES HERE !!!
		
		if($this->$section['bottom']) {
			$use_bottom_bar = 2;
			generateChapter($this->sections[$section]['title'], $section, $this->sections[$section]['toggle'], 'wmtBottomBar wmtColorBar', 'wmtChapter', true, $use_bottom_bar);
		}
		echo "</div>\n";
	}
	
	
	/**
	 * Search and retrieve the modules associated with an existing form or
	 * retrieve the current modules from list for a new form.
	 *
	 * @return array $list modules for current form
	 */
	public function fetchModules() {
		if($this->id)
			// return existing list
			return explode('|', $this->modules);
	
		if(! $pid)
			throw new \Exception ("wmtSOAP::fetchEncounter - no patient identifier provided");
	
		$query = "SELECT `form_id` FROM `forms` fr ";
		$query .= "LEFT JOIN `form_full_soap` soap ON soap.id = fr.form_id ";
		$query .= "WHERE fr.`encounter` = ? AND fr.`pid` = ? AND fr.`formdir` = 'full_soap' ";
		$binds[] = $enc;
		$binds[] = $pid;
	
		$form = sqlQuery($query,$binds);
	
		// check for results
		$id = ($form['form_id'])? $form['form_id'] : false;
	
		// creates an new order
		return new SOAP($id);
	}

	/**
	 * Returns the most recent form object or an empty object based
	 * on the PID provided.
	 *
   * This one is special because if no recent form exists we will fall back
   * to the standard OEMR soap form.
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

		$query = "SELECT form_id FROM forms ";
		$query .= "LEFT JOIN form_encounter USING (encounter) ";
		$query .= "WHERE formdir = 'soap' AND forms.pid = ? ";
		if ($active) $query .= "AND deleted = 0 ";
		$query .= "ORDER BY form_encounter.date DESC, forms.id DESC";

		$hist = sqlQuery($query,array($pid));
		
		$frm = new SOAP($data['form_id']);
		if($data['form_id']) {
			if($prepopulate) {
				$frm->id = '';
				$frm->date = '';
				$frm->user = '';
				$frm->groupname = '';
				$frm->authorized = '';
				$frm->encounter = '';
				$frm->activity = '';
				$frm->approved_by = '';
				$frm->approved_dt = '';
				$frm->approved_by = '';
				$frm->sex = '';
				$frm->age = '';
				$frm->provider = '';
				$frm->section_data = '';
				$frm->visit_date = '';
			}
		} else if($hist['form_id']) {
			$old = sqlQuery("SELECT * FROM form_soap WHERE id=?",array($hist['form_id']));
			$frm->subjective_notes = $old['subjective'];
			$frm->objective_notes = $old['objective'];
			$frm->assessment_notes = $old['assessment'];
			$frm->inst_notes = $old['plan'];
		}
		return $frm;
	}
	
	
	/**
	 * Search and retrieve an order object by encounter and pid
	 *
	 * @static
	 * @param int $pid Patient identifier
	 * @param int $enc Encounter identifier
	 * @return wmtSOAP $object
	 */
	public static function fetchEncounter($pid, $enc) {
		if(! $pid)

		if(! $enc)
			throw new \Exception ("wmtSOAP::fetchEncounter - no encounter number provided");

		$binds[] = $enc;
		$binds[] = $pid;
		
		$query = "SELECT `form_id` FROM `forms` fr ";
		$query .= "LEFT JOIN `form_full_soap` soap ON soap.id = fr.form_id ";
		$query .= "WHERE fr.`encounter` = ? AND fr.`pid` = ? AND fr.`formdir` = 'full_soap' ";
		
		$form = sqlQuery($query,$binds);

		// check for results
		$id = ($form['form_id'])? $form['form_id'] : false;

		if(!$id && !checkSettingMode('wmt::noload_form_history','','full_soap')) {
			return self::fetchRecent('full_soap', $pid, true, true);
		} else {
		
			// creates an new order
			return new SOAP($id, $type);
		}
	}

	/**
	 * Returns the next available order number.
	 *
	 * @static
	 * @return int order number
	 */
	public static function nextOrdNum() {
		$ordnum = $GLOBALS['adodb']['db']->GenID('order_seq');
	
		// duplicate checking
		$dupchk = sqlQuery("SELECT `procedure_order_id` AS id FROM `procedure_order` WHERE `procedure_order_id` = ?",array($ordnum));
		while ($dupchk !== false) {
			$ordnum = $GLOBALS['adodb']['db']->GenID('order_seq');
			$dupchk = sqlQuery("SELECT `procedure_order_id` AS id FROM `procedure_order` WHERE `procedure_order_id` = ?",array($ordnum));
		}
		
		return $ordnum;
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
