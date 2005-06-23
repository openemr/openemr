<?php

require_once ($GLOBALS['fileroot'] . "/library/classes/Controller.class.php");
require_once ($GLOBALS['fileroot'] . "/library/forms.inc");
require_once ($GLOBALS['fileroot'] . "/library/sql.inc");
require_once("FormAdultProgressNote.class.php");

class C_FormAdultProgressNote extends Controller {

	var $template_dir;
	
    function C_FormAdultProgressNote($template_mod = "general") {
    	parent::Controller();
    	$this->template_mod = $template_mod;
    	$this->template_dir = dirname(__FILE__) . "/templates/adult_progress_note/";
    	$this->assign("FORM_ACTION", $GLOBALS['web_root']);
    	$this->assign("DONT_SAVE_LINK",$GLOBALS['webroot'] . "/interface/patient_file/encounter/patient_encounter.php");
    	$this->assign("STYLE", $GLOBALS['style']);
    }
    
    function default_action() {
    	$adult_progress_note = new FormAdultProgressNote();
    	$this->assign("checks",$adult_progress_note->_form_layout());
    	$this->assign("checks2",$adult_progress_note->_form_layout2());
    	$this->assign("adult_progress_note",$adult_progress_note);
		return $this->fetch($this->template_dir . $this->template_mod . "_new.html");
	}
	
	function view_action($form_id) {
		if (is_numeric($form_id)) {
    		$adult_progress_note = new FormAdultProgressNote($form_id);
    	}
    	else {
    		$adult_progress_note = new FormAdultProgressNote();
    	}
    	$this->assign("VIEW",true);
    	$this->assign("checks",$adult_progress_note->_form_layout());
    	$this->assign("checks2",$adult_progress_note->_form_layout2());
    	$this->assign("adult_progress_note",$adult_progress_note);
		return $this->fetch($this->template_dir . $this->template_mod . "_new.html");

	}
	
	function default_action_process() {
		if ($_POST['process'] != "true")
			return;
		$this->adult_progress_note = new FormAdultProgressNote($_POST['id']);
		parent::populate_object($this->adult_progress_note);
		
		$new_form = false;
		if (empty($_POST['id'])) {
		  $new_form = true;
		}
		
		$this->adult_progress_note->persist();
		if ($GLOBALS['encounter'] == "") {
			$GLOBALS['encounter'] = date("Ymd");
		}
		if ($new_form) {
		addForm($GLOBALS['encounter'], "AdultProgressNote Form", $this->adult_progress_note->id, "adult_progress_note", $GLOBALS['pid'], $_SESSION['userauthorized']);
		}
		if (!empty($_POST['cpt_code'])) {
			$sql = "select * from codes where code ='" . mysql_real_escape_string($_POST['cpt_code']) . "' order by id";
			
			$results = sqlQ($sql);	
			
			$row = mysql_fetch_array($results);
			if (!empty($row)) {
				addBilling(	date("Ymd"), 	'CPT4', 	$row['code'],	$row['code_text'],  $_SESSION['pid'], 	$_SESSION['userauthorized'], 	$_SESSION['authUserID'],$row['modifier'],$row['units'],$row['fee']);
			}
			
		}
		
		$_POST['process'] = "";
		return;
	}
    
}



?>
