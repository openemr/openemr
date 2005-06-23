<?php

require_once ($GLOBALS['fileroot'] . "/library/classes/Controller.class.php");
require_once ($GLOBALS['fileroot'] . "/library/forms.inc");
require_once ($GLOBALS['fileroot'] . "/library/sql.inc");
require_once("FormReviewOfSystems.class.php");

class C_FormReviewOfSystems extends Controller {

	var $template_dir;
	
    function C_FormReviewOfSystems($template_mod = "general") {
    	parent::Controller();
    	$this->template_mod = $template_mod;
    	$this->template_dir = dirname(__FILE__) . "/templates/review_of_systems/";
    	$this->assign("FORM_ACTION", $GLOBALS['web_root']);
    	$this->assign("DONT_SAVE_LINK",$GLOBALS['webroot'] . "/interface/patient_file/encounter/patient_encounter.php");
    	$this->assign("STYLE", $GLOBALS['style']);
    }
    
    function default_action() {
    	$review_of_systems = new FormReviewOfSystems();
    	$this->assign("review_of_systems",$review_of_systems);
    	$this->assign("checks",$review_of_systems->_form_layout());
		return $this->fetch($this->template_dir . $this->template_mod . "_new.html");
	}
	
	function view_action($form_id) {
		if (is_numeric($form_id)) {
    		$review_of_systems = new FormReviewOfSystems($form_id);
    	}
    	else {
    		$review_of_systems = new FormReviewOfSystems();
    	}
    	$this->assign("VIEW",true);
    	$this->assign("review_of_systems",$review_of_systems);
    	$this->assign("checks",$review_of_systems->_form_layout());
		return $this->fetch($this->template_dir . $this->template_mod . "_new.html");

	}
	
	function default_action_process() {
		if ($_POST['process'] != "true")
			return;
		$this->review_of_systems = new FormReviewOfSystems($_POST['id']);
		parent::populate_object($this->review_of_systems);
		$this->review_of_systems->persist();
		if ($GLOBALS['encounter'] == "") {
			$GLOBALS['encounter'] = date("Ymd");
		}
		addForm($GLOBALS['encounter'], "Review Of Systems", $this->review_of_systems->id, "review_of_systems", $GLOBALS['pid'], $_SESSION['userauthorized']);
		
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