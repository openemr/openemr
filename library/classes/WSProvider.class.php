<?php
require_once(dirname(__FILE__) . "/../patient.inc");
include_once (dirname(__FILE__) . "/../sqlconf.php");
include_once (dirname(__FILE__) . "/../../includes/config.php");

class WSProvider extends WSWrapper{
	
	var $user_id;
	var $info;
	var $_db;

	function WSProvider($user_id) {
		if (!is_numeric($user_id))
			return;

		parent::WSWrapper(null,false);

		$this->user_id = $user_id;
		$this->_db = $GLOBALS['adodb']['db'];

		if (!$this->_config['enabled']) return;

		if ($this->load_info()) {
			$function['ezybiz.add_salesman'] = array(new xmlrpcval($this->data,"struct"));
			$this->send($function);
		
			// if the remote user was added make an entry in the local mapping table to that updates can be made correctly 
			if (is_numeric($this->value)) {
				$sql = "REPLACE INTO integration_mapping set id = '" . $this->_db->GenID("sequences") . "', foreign_id ='" . $this->value . "', foreign_table ='salesman', local_id = '" . $this->user_id . "', local_table = 'users' ";
				$this->_db->Execute($sql) or die ("error: " . $this->_db->ErrorMsg());
			}
		}
	}

	function load_info() {
		$user_info = getProviderInfo($this->user_id);

		if (count($user_info)) {
			//returns array of arrays with 1 array
			$user_info = $user_info[0];
		
			$sql = "SELECT foreign_id,foreign_table FROM integration_mapping where local_table = 'users' and local_id = '" . $this->user_id . "'";
			$result = $this->_db->Execute($sql);
			if ($result && !$result->EOF) {
				$user_info['foreign_update'] = true;
				$user_info['foreign_id'] = $result->fields['foreign_id'];
				$user_info['foreign_table'] = $result->fields['foreign_table'];
			}
		} else {
			$this->data = array();
			return false;
		}

		$this->data = $user_info;
		return true;							
	}
}
?>