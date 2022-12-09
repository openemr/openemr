<?php
/** **************************************************************************
 *	ORDER.CLASS.PHP
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
 *  @subpackage order
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
class Order extends Form {
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
	
	public $procedure_order_id;
	public $provider_id; // references users.id, the ordering provider
	public $patient_id; // references patient_data.pid
	public $encounter_id; // references form_encounter.encounter
	public $date_collected;
	public $date_ordered;
	public $order_priority;
	public $order_number;
	public $order_status; // pending,routed,complete,canceled
	public $order_notes;
	public $patient_instructions;
	public $activity; // 0 if deleted
	public $control_id; // CONTROL ID that is sent back from lab
	public $lab_id; // references procedure_providers.ppid
	public $specimen_type; // from the Specimen_Type list
	public $specimen_location; // from the Specimen_Location list
	public $specimen_volume; // from a text input field
	public $date_transmitted; // time of order transmission, null if unsent
	public $clinical_hx; // clinical history text that may be relevant to the order
	
	/**
	 * Constructor for the 'order' class which retrieves the requested
	 * information from the database or creates an empty object.
	 *
	 * @param string $form_table database table
	 * @param int $id record identifier
	 * @return object instance of form class
	 */
	public function __construct($form, $id = false) {
		// run parent create/retrieve
		parent::__construct($form, $id, 'Laboratory Order');

		// create empty record with no id
		if (!$id) return false;

		// retrieve remaining data
		if (!$this->order_number)
			throw new \Exception('wmtOrder::_construct - no procedure order number.');
		
		$query = "SELECT * FROM procedure_order WHERE procedure_order_id = ?";
		$data = sqlQuery($query,array($this->order_number));
		if (!$data['procedure_order_id'])
			throw new \Exception('wmtOrder::_construct - no procedure order record with procedure_order_id ('.$this->order_number.').');
		
		// load everything returned into object
		foreach ($data as $key => $value) {
			$this->$key = $value;
		}

		return;
	}

	/**
	 * Inserts data from a form object into the database.
	 *
	 * @param wmtOrder $object
	 * @return int $id identifier for new object
	 */
	public function store() {
		$insert = true;
		if($this->id) $insert = false;

		// insert form through parent
		parent::store();
				
		// need text values for these lists
		$status_list = array('i'=>'pending','c'=>'processed','a'=>'complete');
		$priority_list = array('l'=>'low','n'=>'normal','h'=>'stat');
		
		// build sql insert for child
		$sql = '';
		$binds = array();
		$fields = sqlListFields('procedure_order'); // need only sup rec fields
		
		foreach ($this as $key => $value) {
			if ($key == 'id') continue;
			if ($value == 'YYYY-MM-DD' || $value == "_blank") $value = "";
				
			if ($key == 'procedure_order_id') $value = $this->order_number;
			if ($key == 'patient_id') $value = $this->pid; 
			if ($key == 'encounter_id') $value = $this->encounter_id;
			
			if ($key == 'order_priority') $value = $priority_list[$this->form_priority];
			if ($key == 'order_status') $value = $status_list[$this->form_complete];
				
			// both object and database
			if (array_search($key, $fields) !== false) {
				$sql .= ($sql)? ", `$key` = ? " : "`$key` = ? ";
				$binds[] = ($value == 'null')? "" : $value;
			}
		}

		// run the child insert
		if ($insert) { // do insert
			sqlInsert("REPLACE procedure_order SET $sql",$binds);
		} else { // do update
			$binds[] = $this->order_number;
			sqlStatement("UPDATE procedure_order SET $sql WHERE procedure_order_id = ?",$binds);
		}
				
		return $this->id;
	}


	/**
	 * Search and retrieve an order object by order number
	 *
	 * @static
	 * @parm string $order_num Order number for the order
	 * @return wmtOrder $object
	 */
	public static function fetchOrder($form_name = "order", $order_num, $lab_id, $pid, $pat_DOB = false) {
		if(! $order_num)
			throw new \Exception ("wmtOrder::fetchOrder - no order number provided");

		if(! $lab_id)
			throw new \Exception ("wmtOrder::fetchOrder - no lab identifier provided");

		$table = "form_".$form_name;

		$query = ("SELECT id FROM $table WHERE order_number = ? AND lab_id = ? AND (pid > 999999990 OR pid = ?) ");
		$params[] = $order_num;
		$params[] = $lab_id;
		$params[] = $pid;

		if ($pat_DOB) { 
			$query .= "AND pat_DOB = ? ";
			$params[] = $pat_DOB;
		}
		
		$order = sqlQuery($query,$params);
		if (!$order || !$order['id']) return false;
		
		return new Order($form_name, $order['id']);
	}

	/**
	 * Search and retrieve an order object by encounter and pid
	 *
	 * @static
	 * @param string $enc_num Encounter number for the order
	 * @param int $pid Patient identifier
	 * @param int $enc Encounter identifier
	 * @param int $lab Lab identifier
	 * @param string Form type name
	 * @return wmtOrder $object
	 */
	public static function fetchEncounter($enc, $pid, $lab, $form) {
		if(! $enc)
			throw new \Exception ("wmtOrder::fetchEncounter - no encounter number provided");

		if(! $form)
			throw new \Exception ("wmtOrder::fetchEncounter - no form name provided");

		if(! $pid)
			throw new \Exception ("wmtOrder::fetchEncounter - no patient identifier provided");

		if(! $lab)
			throw new \Exception ("wmtOrder::fetchEncounter - no laboratory identifier provided");

		if ($form == 'internal') $form = 'laboratory';
		$form_table = 'form_' . $form;
		
		$query = "SELECT `form_id` FROM `forms` fr ";
		$query .= "LEFT JOIN `$form_table` ft ON ft.id = fr.form_id ";
		$query .= "WHERE fr.`encounter` = ? AND fr.`pid` = ? AND fr.`formdir` = ? AND ft.`lab_id` = ? ";
		$params[] = $enc;
		$params[] = $pid;
		$params[] = $form;
		$params[] = $lab;
		
		$order = sqlQuery($query,$params);

		// check for results
		$id = ($order['form_id'])? $order['form_id'] : false;
		
		// creates an new order
		return new Order($form, $id);
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
	public function listFields() {
		$fields = sqlListFields($this->form_table);
		return $fields;
	}

}

?>