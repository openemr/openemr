<?php
/** **************************************************************************
 *	msg.class.php
 *	This file contains the standard classes for interacting with the notes      
 *	user status popup which is a Williams Medical Technologies option for 
 *  OpenEMR.  
 *	This class must be included for status popup integration.
 *
 *  NOTES:
 *  1) __CONSTRUCT - always uses a record ID (user) to retrieve data 
 *  2) GET - uses alternate selectors to find and return associated object
 *  3) FIND - returns only the object ID without data using alternate selectors
 *  4) LIST - returns an array of IDs meeting specific selector criteria
 *  5) FETCH - returns an array of data meeting specific criteria
 *   
 * 
 *  @package msg 
 *  @version 1.0
 *  @copyright Williams Medical Technologies, Inc.
 *  @author Rich Genandt <rgenandt@gmail.com>
 * 
 *************************************************************************** */

class wmtMsgStatus{
	public $user_id;
	public $user_msg;
	public $timestamp;
  public $set_by;
	public $status;
	public $until;
	public $groups;
	public $group_desc;
	
	/**
	* Constructor for the 'msg' class which retrieves the requested 
	* information from the database or creates an empty object.
	* 
	* @param int $id message status  record identifier
	* @return object instance of status class
	*/
	public function __construct($id = false) {
		if(!$id) return false;

		$query = "SELECT * FROM msg_status WHERE user_id=?";
		$data = sqlQuery($query, array($id));
	
		if ($data) {
			$this->user_id = $data['user_id'];
			$this->user_msg = $data['user_msg'];
      $this->timestamp = $data['timestamp'];
      $this->set_by = $data['set_by'];
      $this->status = $data['status'];
      $this->until = $data['until'];
		}
		else {
			$new_msg = new wmtMsgStatus();
			$this->user_id = $id;
			$this->status = 'unk';
		}
		
		// Set the current group list for the hidden input and the display
		$sql = 'SELECT link.group_id, list.title FROM msg_group_link AS link '.
		'LEFT JOIN list_options AS list ON (link.group_id = list.option_id AND '.
		'list.list_id = "Messaging_Groups") WHERE link.user_id=?';
		$fres = sqlStatement($sql, array($id));
		$this->groups = '';
		$this->group_desc = '';
		while($frow = sqlFetchArray($fres)) {
			if($this->groups != '') $this->groups .= '~|';
			if($this->group_desc != '') $this->group_desc .= ', ';
			$this->groups .= $frow{'group_id'};
			$this->group_desc .= $frow{'title'};
		}

	}	

  /**
  * Adds or updates the user message status in the database.
  * 
  * @static
  * @param Errors $iderror_object
  * @return null
  */
	public function update() {
		$query = '';
		$binds = array();
		$fields = sqlListFields('msg_status');
		foreach ($this as $key => $value) {
			if (!in_array($key, $fields)) continue;
			if ($query) $query .= ",";
			if($key == 'set_by') {
				if($value == '') $value = $_SESSION['authUserID'];
			}
			if($key == 'timestamp') {
				$query .= " $key = NOW()";
			} else {
				$query .= " $key = ?";
				$binds[] = $value;
			}
		}
		
		sqlInsert("REPLACE INTO msg_status SET $query", $binds);

		$curr_groups = explode('~|', $this->groups);
		$del = "DELETE FROM msg_group_link WHERE id=?";
		$ins = "INSERT INTO msg_group_link (timestamp, user_id, set_by, group_id) ".
			"VALUES (NOW(), ?, ?, ?) ON DUPLICATE KEY UPDATE ".
			"timestamp = NOW(), set_by = VALUES(set_by)";
		$log = "INSERT INTO msg_group_history (timestamp, user_id, set_by, ".
			"group_id, event) VALUES (NOW(), ?, ?, ?, ?)";
		$prev = "SELECT * FROM msg_group_history WHERE user_id=? AND group_id=? ".
			"ORDER BY timestamp DESC LIMIT 1";

		$sql = "SELECT * FROM msg_group_link WHERE user_id=?";
		$fres = sqlStatement($sql, array($this->user_id));
		while($frow = sqlFetchArray($fres)) {
			if(!in_array($frow{'group_id'}, $curr_groups)) {
				sqlStatement($del,array($frow{'id'}));
				sqlStatement($log,array($this->user_id, $_SESSION['authUserID'], 
					$frow{'group_id'}, 'exit'));
			}
		}
		foreach($curr_groups as $grp) {
			if($grp) {
				sqlStatement($ins, array($this->user_id,$_SESSION['authUserID'],$grp));
				$last_entry = sqlQuery($prev, array($this->user_id, $grp));
				if($last_entry{'event'} != 'enter') {
					sqlStatement($log,array($this->user_id, $_SESSION['authUserID'], 
						$grp, 'enter'));
				}
			}
		}

		return;
	}

}
                                            
?>
