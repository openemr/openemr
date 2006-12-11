<?php

require_once ($GLOBALS['fileroot'] . "/library/classes/Controller.class.php");
require_once ($GLOBALS['fileroot'] . "/library/forms.inc");
require_once("FormPriorAuth.class.php");

class C_FormPriorAuth extends Controller {

	var $template_dir;
	
    function C_FormPriorAuth($template_mod = "general") {
    	parent::Controller();
    	$returnurl = $GLOBALS['concurrent_layout'] ? 'encounter_top.php' : 'patient_encounter.php';
    	$this->template_mod = $template_mod;
    	$this->template_dir = dirname(__FILE__) . "/templates/prior_auth/";
    	$this->assign("FORM_ACTION", $GLOBALS['web_root']);
    	$this->assign("DONT_SAVE_LINK",$GLOBALS['webroot'] . "/interface/patient_file/encounter/$returnurl");
    	$this->assign("STYLE", $GLOBALS['style']);
    }
    
    function default_action() {
    	$prior_auth = new FormPriorAuth();
    	$this->assign("prior_auth",$prior_auth);
		return $this->fetch($this->template_dir . $this->template_mod . "_new.html");
	}
	
	function view_action($form_id) {
		if (is_numeric($form_id)) {
    		$prior_auth = new FormPriorAuth($form_id);
    	}
    	else {
    		$prior_auth = new FormPriorAuth();
    	}
    	$this->assign("VIEW",true);
    	$this->assign("prior_auth",$prior_auth);
		return $this->fetch($this->template_dir . $this->template_mod . "_new.html");

	}
	
	function default_action_process() {
		if ($_POST['process'] != "true")
			return;
		$this->prior_auth = new FormPriorAuth($_POST['id']);
		parent::populate_object($this->prior_auth);
		
		
		$this->prior_auth->persist();
		if ($GLOBALS['encounter'] == "") {
			$GLOBALS['encounter'] = date("Ymd");
		}
		addForm($GLOBALS['encounter'], "Prior Authorization Form", $this->prior_auth->id, "prior_auth", $GLOBALS['pid'], $_SESSION['userauthorized']);
		$_POST['process'] = "";
		return;
	}
    
}



?>