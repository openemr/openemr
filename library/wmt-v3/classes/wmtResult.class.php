<?php
/** **************************************************************************
 *	RESULT.CLASS.PHP
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

/**
 * Provides standardized processing for procedure result forms.
 *
 * @package wmt
 * @subpackage results
 */
class Result {
	public $procedure_report_id;
	public $procedure_order_id;
	public $procedure_order_seq;
	public $date_collected;
	public $date_report;
	public $source;
	public $specimen_num;
	public $report_status;
	public $review_status;
	public $report_notes;

	/**
	 * @param int $id record identifier
	 * @return object instance of result class
	 */
	public function __construct($id = false) {
		// create empty record with no id
		if (!$id) return false;

		$query = "SELECT * FROM procedure_report WHERE procedure_report_id = ?";
		$data = sqlQuery($query,array($id));
		if (!$data['procedure_report_id'])
			throw new \Exception('wmtResult::_construct - no procedure report record with procedure_report_id ('.$id.').');

			// load everything returned into object
			foreach ($data as $key => $value) {
				$this->$key = $value;
			}

			return;
	}

	/**
	 * Inserts data from a form object into the database.
	 *
	 * @param wmtResult $object
	 * @return int $id identifier for new object
	 */
	public function store() {
		$insert = true;
		if($this->id) $insert = false;

		// build sql insert
		$sql = '';
		$binds = array();
		$fields = self::listFields();
		
		foreach ($this as $key => $value) {
			if (!in_array($key, $fields) || $key == 'procedure_report_id') continue;
			if ($value == 'YYYY-MM-DD') continue;
				
			$sql .= ($sql)? ", `$key` = ? " : "`$key` = ? ";
			$binds[] = ($value == 'null')? "" : $value;
		}

		// run the process
		if ($insert) { // do insert
			$this->id = sqlInsert("REPLACE procedure_report SET $sql",$binds);
		} else { // do update
			$binds[] = $this->procedure_report_id;
			sqlStatement("UPDATE procedure_report SET $sql WHERE procedure_report_id = ?",$binds);
		}
				
		return $this->id;
	}

	/**
	 * Search and retrieve an result object by order number
	 *
	 * @static
	 * @parm string $order_num Order number for the order
	 * @return Result $object
	 */
	public static function fetchResult($order_num, $order_seq) {
		if(!$order_num) return false;

		$result = sqlQuery("SELECT procedure_report_id FROM procedure_report WHERE procedure_order_id = ? AND procedure_order_seq = ?",
				array($order_num, $order_seq));

		if (!$result['procedure_report_id']) return false;
		$result_data = new Result($result['procedure_report_id']);

		return $result_data;
	}

	/**
	 * Search and retrieve an result object by order number
	 *
	 * @static
	 * @parm string $order_num Order number for the order
	 * @return Result $object
	 */
	public static function fetchReflex($order_num, $reflex_code, $reflex_set) {
		if(!$order_num || !$reflex_code) return false;

		$query = "SELECT procedure_result_id FROM procedure_report rep ";
		$query .= "LEFT JOIN procedure_result res ON rep.procedure_report_id = res.procedure_report_id ";
		$query .= "WHERE rep.procedure_order_id = ? AND res.result_code = ? AND res.result_set = ? ";
		$result = sqlQuery($query,array($order_num, $reflex_code, $reflex_set));

		if (!$result['procedure_result_id']) return false;
		$result_data = new ResultItem($result['procedure_result_id']);

		return $result_data;
	}

	/**
	 * Returns an array of valid database fields for the object.
	 *
	 * @static
	 * @return array list of database field names
	 */
	public static function listFields() {
		$fields = sqlListFields('procedure_report');
		return $fields;
	}

}
?>