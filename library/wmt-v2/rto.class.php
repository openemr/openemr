<?php
/** **************************************************************************
 *	RTO.CLASS.PHP
 *	This file contains a class for use with the Willams Medical Technologies
 *  Order / RTO module
 *
 *  NOTES:
 *  1) __CONSTRUCT - always uses the ID to retrieve data 
 *  2) GET - uses alternate selectors to find and return associated object
 * 
 *  @version 1.0
 *  @copyright Williams Medical Technologies, Inc.
 *  @author Rich Genandt <rgenandt@gmail.com>
 * 
 *************************************************************************** */

if(!class_exists('wmtRTOData')) {

class wmtRTOData{
	public $id;
	public $pid;
	public $rto_date;
	public $rto_type;
	public $rto_num;
	public $rto_frame;
	public $rto_prn;
	public $rto_notes;
	public $rto_status;
	public $rto_resp_user;
	public $rto_action;
	public $rto_last_action;
	public $rto_last_resp_user;
	public $rto_last_touch;
	public $rto_target_date;
	public $rto_ordered_by;
	public $rto_extra;
	public $rto_msg_trail;
	public $rto_action_trail;
	public $rto_repeat;
	public $rto_stop_date;
	
	/**
	 * Constructor for the RTO class which retrieves the requested 
	 * information from the database or errors.
	 * 
	 * @param int $id rto record id identifier
	 * @return object instance of the rto 
	 */
	public function __construct($id = false) {
		if(!$id) return $this;

		$query = "SELECT * FROM form_rto WHERE id =?";
		$results = sqlStatementNoLog($query, array($id));
	
		if ($data = sqlFetchArray($results)) {
			foreach($data as $key => $val) {
				$this->$key = $val;
			}
		}
		else {
			throw new Exception('wmtRTOData::_construct - no RTO record with id ('.$this->id.').');
		}
	}	

	/**
	 * Retrieve an RTO object by ID value. Uses the base constructor 
   * for the class to create and return the object.
	 * 
	 * @static
	 * @param int $id rto record id
	 * @return object instance of rto class
	 */
	public static function getRTObyID($id) {
		if(!$id) {
			throw new Exception('wmtRTOData::getRTObyID - no RTO identifier provided.');
		}
		
		return new wmtRTOData($id);
	}


	/**
	 * Retrieve or create an RTO object by action type. This is for 
   * attempting to match the type of order optionally within a time period
   * and create the order if an existing order can not be found.
   * The purpose is to link the orders to forms try to eliminate duplicates.
	 * Uses the base constructor for the class to create and return 
	 * the object.
	 * 
	 * @static
	 * @param int $pid link record pid
	 * @param varchar $name linked form directory (optional)
	 * @param int $id of the calling form, but not used for the query (optional)
	 * @param varchar $category linked form rto type category 
	 * @param text $nt text of the comments  (optional) 
	 * @param boolean $create to force creation of the rto and link (optional) 
	 * @param boolean $status Order status to filter by (optional) 
	 * @return object instance of rto class
	 */
	public static function findOrCreateRTO($pid, $options, $create = false) {
		if(!$pid) { 
			throw new Exception('wmtRTOData::findOrCreateRTO - no pid provided.');
		}
		if(!$options['rto_action']) {
			throw new Exception('wmtRTOData::findOrCreateRTO - no action type provided.');
		}

		if(!isset($options['rto_target_date'])) $options['rto_target_date'] = '';
		if(!isset($options['rto_resp_user'])) $options['rto_resp_user'] = '';
		if(!isset($options['rto_date'])) $options['rto_date'] = date('Y-m-d');
		if(!isset($options['rto_status'])) $options['rto_status'] = 'p';
		if(!isset($options['window'])) $options['window'] = '';
		if(!isset($options['interval'])) $options['interval'] = '';
		$target_date = self::calculateTarget($options['rto_action'], $options['rto_date'], $options['interval']);
		$binds = array($pid, $options['rto_action'], $options['rto_status']);
		$sql = 'SELECT rto.* FROM form_rto AS rto WHERE '.
			'rto.pid = ? AND rto_action = ? AND rto_status = ? ';
		if($options['window'] != '') {
			list($num, $frame) = explode('|', $options['window']);
			$from = self::calculateOffsetDate((-1 * $num), $frame, $target_date);
			$to = self::calculateOffsetDate($num, $frame, $target_date);
			$sql .= 'AND rto_target_date >= ? AND rto_target_date <= ? ';
			$binds[] = $from;
			$binds[] = $to;
		}
		$sql .= 'ORDER BY date DESC LIMIT 1';
		// echo "Sql: $sql<br>\n";
		// echo "Binds: ";
		// print_r($binds);
		// echo "<br>\n";
		$frow = sqlQuery($sql, $binds);
		if(!isset($frow{'id'})) $frow{'id'} = '';
		$ret = $frow{'id'};
		// echo "We have RTO IS ($ret) Already Existing<br>\n";
		if($ret) return new wmtRTOData($ret);

		$default = sqlQuery('SELECT * FROM list_options WHERE list_id=? AND '.
			'option_id=?', array('RTO_Action',$options['rto_action']));
		if(!isset($default['notes'])) $default['notes'] = '';
		$set = explode(';', $default['notes']);
		foreach($set as $setting) {
			if(strpos($setting, ':') !== false) {
				list($label, $val) = explode(':', $setting);
			}
		}
		// echo "Loaded Defaults: ";
		// print_r($default);
		// echo "<br>\n";
		$frame = $num = '';
		if($options['interval'] == '') $options['interval'] = $default['codes'];
		if($options['interval']) list($num, $frame) = explode('|', $options['interval']);
		if($create) { 
			$new_rto = new wmtRTOData();
			$new_rto->pid = $pid;
			$new_rto->rto_date = $options['rto_date'];
			$new_rto->rto_num = $num;
			$new_rto->rto_frame = $frame;
			$new_rto->rto_status = $options['rto_status'];
			$new_rto->rto_action = $options['rto_action'];
			$new_rto->rto_notes = $options['rto_notes'];
			$new_rto->rto_target_date = $target_date;
			$new_rto->rto_resp_user = $options['rto_resp_user'];
			$ret = self::insert($new_rto);
			// echo "Created New RTO [$ret]<br>\n";
			return new wmtRTOData($ret);
		}
		return new wmtRTOData();
	}

	/**
	 * Retrieve an RTO object by link. This is for orders that are unique to 
	 * a form, one to one correlation. Uses the base constructor 
   * for the class to create and return the object.
	 * 
	 * @static
	 * @param int $pid link record pid
	 * @param varchar $name linked form directory
	 * @param int $id linked form record id
	 * @param varchar $category linked form rto type category (optional) 
	 * @param text $nt text of the comments  (optional) 
	 * @param boolean $create to force creation of the rto and link (optional) 
	 * @return object instance of rto class
	 */
	public static function LinkedRTO($pid,$name,$id,$category='',$nt='',$create=false) {
		if(!$pid) { 
			throw new Exception('wmtRTOData::LinkedRTO - no patient identifier provided.');
		}
		if(!$name) {
			throw new Exception('wmtRTOData::LinkedRTO - no form name identifier provided.');
		}
		if(!$id) {
			throw new Exception('wmtRTOData::LinkedRTO - no form identifier provided.');
		}
		$binds = array($name, $id, $pid, $category);
		$fres = sqlStatement("SELECT * FROM wmt_rto_links WHERE form_name=?".
			" AND form_id=? AND pid=? AND link_category=?",$binds);
		$frow = sqlFetchArray($fres);
		$ret = $frow{'rto_id'};
		if($ret) return new wmtRTOData($ret);
		if($create) { 
			$txt = ListLook($category, 'RTO_Link_Text');
			if($txt) $txt .= "\n";
			// $nt = "Surgical Case $nt -> $txt This Referral Order Was Auto-Created";
			$new_rto =  new wmtRTOData();
			$new_rto->pid = $pid;
			$new_rto->rto_action = $category;
			$new_rto->rto_status = 'p';
			$new_rto->rto_notes = $nt;
			$ret = self::insert($new_rto);
			self::addLink($pid, $name, $id, $ret, $category);
			return new wmtRTOData($ret);
		}
		return new wmtRTOData();
	}

	/**
	 * Retrieve an RTO object by type. This is for types that are unique
	 * but can be attached to multiple forms basically and the search is based
   * on the links rather than the RTO, surgery form used it - maybe not
   * the best method though.
	 * Uses the base constructor for the class to create and return 
	 * the object.
	 * 
	 * @static
	 * @param int $pid link record pid
	 * @param varchar $name linked form directory (optional)
	 * @param int $id of the calling form, but not used for the query (optional)
	 * @param varchar $category linked form rto type category 
	 * @param text $nt text of the comments  (optional) 
	 * @param boolean $create to force creation of the rto and link (optional) 
	 * @param boolean $status Order status to filter by (optional) 
	 * @return object instance of rto class
	 */
	public static function CategoryRTO($pid, $name='', $id='', $category, $nt='', $create=false, $status='p', $dt='', $window = '') {
		if(!$pid) { 
			throw new Exception('wmtRTOData::CategoryRTO - no patient identifier provided.');
		}
		if(!$category) {
			throw new Exception('wmtRTOData::CategoryRTO - no category identifier provided.');
		}

		$target_date = self::calculateTarget($category, $dt);
		$binds = array($pid, $category, $status);
		$sql = 'SELECT wrl.*, form_rto.rto_status, form_rto.rto_target_date '.
			'FROM wmt_rto_links AS wrl LEFT JOIN form_rto ON '.
			'(wrl.rto_id = form_rto.id) WHERE '.
			'wrl.pid=? AND link_category=? AND rto_status=? ';
		if($window != '') {
			list($num, $frame) = explode('|', $window);
			$from = self::calculateOffsetDate((-1 * $num), $frame, $target_date);
			$to = self::calculateOffsetDate($num, $frame, $target_date);
			$sql .= 'AND rto_target_date >= ? AND rto_target_date <= ? ';
			$binds[] = $from;
			$binds[] = $to;
		}
		$sql .= 'ORDER BY date DESC LIMIT 1';
		// echo "Sql: $sql<br>\n";
		// echo "Binds: ";
		// print_r($binds);
		// echo "<br>\n";
		$frow = sqlQuery($sql, $binds);
		$ret = $frow{'rto_id'};
		if($ret) return new wmtRTOData($ret);
		if($create) { 
			$new_rto =  new wmtRTOData();
			$new_rto->pid = $pid;
			$new_rto->rto_status = 'p';
			$new_rto->rto_action = $category;
			$new_rto->rto_notes = $nt;
			$new_rto->rto_target_date = $target_date;
			$ret = self::insert($new_rto);
			self::addLink($pid, $name, $id, $ret, $category);
			return new wmtRTOData($ret);
		}
		return new wmtRTOData();
	}
	
	 /* Inserts a new RTO record into the database.
	 *
	 * @static
	 * @param Errors $iderror_object
	 * @return ID of created object 
	 */
	public static function insert(wmtRTOData $object) {
		if($object->id) {
			throw new Exception ("wmtRTOData::insert - object already contains identifier");
		}
		if($object->rto_resp_user == '') $object->rto_resp_user = $_SESSION['authUser'];
		if($object->rto_ordered_by == '') $object->rto_ordered_by = $_SESSION['authUser'];
		if($object->rto_repeat == '') $object->rto_repeat = '0';
		$binds = array();
		$sql = "INSERT INTO form_rto SET date=NOW(), activity=1, ";
		foreach($object as $key => $val) {
			if($key == 'id' || $key == 'rto_last_touch') continue;
			$sql .= "$key=?, ";
			$binds[] = $val;
		}
		$sql .= "rto_last_touch=NOW()";
		$object->id = sqlInsert($sql, $binds);

		return $object->id;
	}
	
  /**
 * Updates the RTO information in the database.
 * 
 * @static
 * @return null
 */
	public function update() {
		// build query from object
		$query = 'user=?, activity=1, groupname=?, authorized=?';
		$binds = array($_SESSION['authUser'], $_SESSION['authProvider'], 
					$_SESSION['userauthorized']);
		$fields = wmtRTOData::listFields();
		foreach ($this as $key => $value) {
			if (!in_array($key, $fields)) continue;
			if ($query) $query .= ', ';
			$query .= " $key=?";
			$binds[] = $value;
		}
		if ($query) $query .= ', ';
		$query .= " rto_last_touch = NOW()";
		$binds[] = $this->id;
		sqlStatement("UPDATE form_rto SET $query WHERE id = ?", $binds);
		return;
	}
	
	/**
	 * Returns an array of valid database fields for the object.
	 * 
	 * @static
	 * @return array list of database field names
	 */
	public static function listFields() {
		return sqlListFields('form_rto');
	}
	
	/**
	 * Returns the target date calculated from the RTO Action settings
	 * 
	 * @static
	 * $parm $num is the number of units
	 * $parm $frame is the unit interval
	 * $parm $dt is the date to work from if not today
	 * @return 
	 */
	public static function calculateOffsetDate($num='', $frame='', $dt='') {
		if($dt == '') $dt = date('Y-m-d');
		if(!$num || !$frame) return $dt;
		if(strtolower($frame) == 'w') {
			$num = $num * 7;
			$frame = 'd';
		}
		$y = substr($dt,0,4);
		$m = substr($dt,5,2);
		$d = substr($dt,-2);
		if(strtolower($frame) == 'd') {
			$date = mktime(0,0,0,$m,($d + $num),$y);
		} else if(strtolower($frame) == 'm') {
			$date = mktime(0,0,0,$m + $num,$d,$y);
		} else if(strtolower($frame) == 'y') {
			$date = mktime(0,0,0,$m,$d,$y + $num);
		}
		$offset = date('Y-m-d', $date);
		return($offset);
	}
	
	/**
	 * Returns the target date calculated from the RTO Action settings
	 * 
	 * @static
	 * $parm $type is the RTO action code to look up
	 * $parm $dt is the date to work from if not today
	 * @return 
	 */
	public static function calculateTarget($event, $dt, $interval='') {
		if(!$event) { 
			throw new Exception('wmtRTOData::calculateTarget - no type provided.');
		}
		if($dt == '') $dt = date('Y-m-d');
		if($interval == '') {
			$options = sqlQuery('SELECT * FROM list_options WHERE list_id=? AND '.
					'option_id=?', array('RTO_Action',$event));
			if(!isset($options['codes'])) $options['codes'] = '';
			$interval = $options['codes'];
		}
		$num = $frame = '';
		if($interval) list($num, $frame) = explode('|', $interval);
		if(!$num || !$frame) return $dt;
		$target = self::calculateOffsetDate($num, $frame, $dt);
		return($target);
	}
	
  /**
 * Creates the RTO / form link in the database.
 * 
 * @param int patient ID
 * @param varchar form directory
 * @param int form ID
 * @param int RTO ID
 * @param varchar link category
 * @static
 * @return null
 */
	public function addLink($pid, $name, $form_id, $rto_id, $category='') {
		if(!$pid) { 
			throw new Exception('wmtRTOData::addLink - no patient identifier provided.');
		}
		if(!$name) {
			throw new Exception('wmtRTOData::LinkedRTO - no form name identifier provided.');
		}
		if(!$form_id) {
			throw new Exception('wmtRTOData::LinkedRTO - no form identifier provided.');
		}
		if(!$rto_id) {
			throw new Exception('wmtRTOData::LinkedRTO - no RTO identifier provided.');
		}
		$binds = array($name, $form_id, $rto_id, $pid, $category);
		$sql = "INSERT INTO wmt_rto_links (form_name, form_id, rto_id, ".
					"pid, link_category) VALUES (?,?,?,?,?) ON DUPLICATE KEY ".
					"UPDATE pid=VALUES(pid)";
		$test = sqlInsert($sql, $binds);
		return;
	}
	
 /**
 * Deletes an RTO / form link(s) from the database.
 * 
 * @param int patient ID
 * @param varchar form directory
 * @param int form ID
 * @param varchar link category
 * @static
 * @return test value
 */
	public function deleteLinkedRTO($pid, $name, $form_id, $category='', $flag=true) {
		if(!$pid) { 
			throw new Exception('wmtRTOData::deleteLinkedRTO - no patient identifier provided.');
		}
		if(!$name) {
			throw new Exception('wmtRTOData::deleteLinkedRTO - no form name identifier provided.');
		}
		if(!$form_id) {
			throw new Exception('wmtRTOData::deleteLinkedRTO - no form identifier provided.');
		}
		// Get the associated RTO id - only one per item for now
		$binds = array($name, $form_id, $pid, $category);
		$fres = sqlStatementNoLog("SELECT * FROM wmt_rto_links WHERE form_name=? ".
			"AND form_id=? AND pid=? AND link_category=?",$binds);
		$frow = sqlFetchArray($fres);
		$ret = $frow{'rto_id'};
		$test = false;
		if(!$ret) {
			if($flag) {
				throw new Exception('wmtRTOData::deleteLinkedRTO - no RTO associated.');
			}
		} else {
			$sql = "DELETE FROM form_rto WHERE id=?";
			sqlStatement($sql, array($ret));
		}

		if($frow{'id'}) {
			$binds = array($name, $form_id, $pid, $category);
			$sql = "DELETE FROM wmt_rto_links WHERE form_name=? AND form_id=? AND ".
						"pid=? AND link_category=?";
			sqlStatement($sql, $binds);
		}
		return $test;
	}
}

/**
 *  A collection of tasks associated with a form for an encounter
 * 
 * @param int patient ID
 * @param varchar form directory
 * @param int form ID
 * @param varchar link category
 * @static
 * @return test value
**/
class wmtFormTasks{

	public $tasks = array();

	public function addTask($task, $key=null) {
		$this->tasks[] = $task;
	}

	public function getTask($key) {
		if(isset($this->tasks[$key])) {
			return $this->tasks[$key];
		} else {
			throw new Exception("wmtFormTasks::getTask -> Invalid Key <$key>");
		}
	}

	public function deleteTask($key) {
		if(isset($this->tasks[$key])) {
			unset($this->tasks[$key]);
		} else {
			throw new Exception("wmtFormTasks::deleteTask -> Invalid Key <$key>");
		}
	}

	public function getFormTasks($pid, $dir, $fid, $order='DESC', $type=null) {
		if(!$pid) {
			throw new Exception("wmtFormTasks::getFormTasks -> No PID");
		}
		if(!$dir) {
			throw new Exception("wmtFormTasks::getFormTasks -> No Directory");
		}
		if(!$fid) {
			throw new Exception("wmtFormTasks::getFormTasks -> No Form ID");
		}
		$binds = array($pid, $dir, $fid);
		$sql = "SELECT * FROM wmt_rto_links WHERE wmt_rto_links.pid=? AND ".
			"wmt_rto_links.form_name=? AND wmt_rto_links.form_id=?";
		if($type != null) {
			$sql .= " AND link_category=?";
			$binds[] = $type;
		}
		$fres = sqlStatement($sql, $binds);
		while($frow = sqlFetchArray($fres)) {
			if($frow{'rto_id'}) { 
				addTask(new wmtRTOData($frow{'rto_id'}));
			}
		}
	}
}

}

?>
