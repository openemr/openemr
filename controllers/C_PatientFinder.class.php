<?php

require_once ($GLOBALS['fileroot'] . "/library/classes/Controller.class.php");
require_once($GLOBALS['fileroot'] ."/library/classes/Provider.class.php");
require_once($GLOBALS['fileroot'] ."/library/classes/InsuranceNumbers.class.php");

class C_PatientFinder extends Controller {

	var $template_mod;
	var $_db;
	
	function C_PatientFinder($template_mod = "general") {
		parent::Controller();
		$this->_db = $GLOBALS['adodb']['db']; 
		$this->template_mod = $template_mod;
		$this->assign("FORM_ACTION", $GLOBALS['webroot']."/controller.php?" . $_SERVER['QUERY_STRING']);
		///////////////////////////////////
		//// What should this be?????
		//////////////////////////////////
		$this->assign("CURRENT_ACTION", $GLOBALS['webroot']."/controller.php?" . "practice_settings&patient_finder&");
		/////////////////////////////////
		$this->assign("STYLE", $GLOBALS['style']);
		
	}

	function default_action($form_id='',$form_name='',$pid='') {
		return $this->find_action($form_id,$form_name,$pid);
	}
	
	/**
	* Function that will display a patient finder widged, allowing
	*	the user to input search parameters to find a patient id.
	*/
	function find_action($form_id, $form_name,$pid) {
		$isPid = false;
		//fix any magic quotes meddling
		
		if (get_magic_quotes_gpc()) {$form_id = stripslashes($form_id);}
		if (get_magic_quotes_gpc()) {$form_name = stripslashes($form_name);}
		if (get_magic_quotes_gpc()) {$pid = stripslashes($pid);}
		
        //prevent javascript injection, whitespace and semi-colons are the worry
        $form_id = preg_replace("/[^A-Za-z0-9\[\]\_\']/iS","",urldecode($form_id));
        $form_name = preg_replace("/[^A-Za-z0-9\[\]\_\']/iS","",urldecode($form_name));
        $this->assign('form_id', $form_id);
        $this->assign('form_name', $form_name);
        if(!empty($pid))
        	$isPid = true;
        $this->assign('hidden_ispid', $isPid);	
		
		return $this->fetch($GLOBALS['template_dir'] . "patient_finder/" . $this->template_mod . "_find.html");
	}
	
	/**
	* Function that will take a search string, parse it out and return all patients from the db matching.
	* @param string $search_string - String from html form giving us our search parameters
	*/
	function find_action_process() {
		
		if ($_POST['process'] != "true")
			return;
		
		$isPub = false;
		$search_string = $_POST['searchstring'];
		if(!empty($_POST['pid']))
		{
			$isPub = !$_POST['pid'];
		}			
		//get the db connection and pass it to the helper functions
		$sql = "SELECT CONCAT(lname, ' ', fname, ' ', mname) as name, DOB, pubpid, pid FROM patient_data";
		//parse search_string to determine what type of search we have
		$pos = strpos($search_string, ',');
		
		// get result set into array and pass to array
		$result_array = array();
		
		if($pos === false) {
			//no comma just last name
			$result_array = $this->search_by_lName($sql, $search_string);
		}
		else if($pos === 0){
			//first name only
			$result_array = $this->search_by_fName($sql, $search_string);
		}
		else {
			//last and first at least
			$result_array = $this->search_by_FullName($sql,$search_string);
		}
		$this->assign('search_string',$search_string);
		$this->assign('result_set', $result_array);
		$this->assign('ispub', $isPub);
		// we're done
		$_POST['process'] = "";
	}

	/**
	*	Function that returns an array containing the 
	*	Results of a LastName search
	*	@-param string $sql base sql query
	*	@-param string $search_string parsed for last name
	*/
	function search_by_lName($sql, $search_string) {
		$lName = mysql_real_escape_string($search_string);
		$sql .= " WHERE lname LIKE '$lName%' ORDER BY lname, fname";
		//print "SQL is $sql \n";
		$result_array = $this->_db->GetAll($sql);
		//print_r($result_array);
		return $result_array;
	}
	
	/**
	*	Function that returns an array containing the 
	*	Results of a FirstName search
	*	@param string $sql base sql query
	*	@param string $search_string parsed for first name
	*/
	function search_by_fName($sql, $search_string) {
		$name_array = split(",", $search_string);
		$fName = mysql_real_escape_string( trim($name_array[1]) );
		$sql .= " WHERE fname LIKE '$fName%' ORDER BY lname, fname";
		$result_array = $this->_db->GetAll($sql);
		return $result_array;
	}
	
	/**
	*	Function that returns an array containing the 
	*	Results of a Full Name search
	*	@param string $sql base sql query
	*	@param string $search_string parsed for first, last and middle name
	*/
	function search_by_FullName($sql, $search_string) {
		$name_array = split(",", $search_string);
		$lName = mysql_real_escape_string($name_array[0]);
		$fName = mysql_real_escape_string( trim($name_array[1]) );
		$sql .= " WHERE fname LIKE '%$fName%' AND lname LIKE '$lName%' ORDER BY lname, fname";
		//print "SQL is $sql \n";
		$result_array = $this->_db->GetAll($sql);
		return $result_array;
	}
}
?>
