<?php

require_once ($GLOBALS['fileroot'] . "/library/classes/Controller.class.php");
require_once ($GLOBALS['fileroot'] . "/library/forms.inc");
require_once("FormHpTjePrimary.class.php");

class C_FormHpTje extends Controller {

	var $template_dir;
	
    function C_FormHPTje($template_mod = "general") {
    	parent::Controller();
    	$this->template_mod = $template_mod;
    	$this->template_dir = dirname(__FILE__) . "/templates/hp_tje/";
    	$this->assign("FORM_ACTION", $GLOBALS['web_root']);
    	$this->assign("DONT_SAVE_LINK",$GLOBALS['webroot'] . "/interface/patient_file/encounter/patient_encounter.php");
    	$this->assign("STYLE", $GLOBALS['style']);
    }
    
    function default_action() {
    	$hptje_primary = new FormHpTjePrimary();
    	$this->assign("hptje_primary",$hptje_primary);
    	$this->assign("checks",$hptje_primary->_form_layout());
		return $this->fetch($this->template_dir . $this->template_mod . "_new.html");
	}
	
	function view_action($form_id) {
		if (is_numeric($form_id)) {
    		$hptje_primary = new FormHpTjePrimary($form_id);
    	}
    	else {
    		$hptje_primary = new FormHpTjePrimary();
    	}
    	
    	$this->assign("hptje_primary",$hptje_primary);
    	$this->assign("checks",$hptje_primary->_form_layout());
    	$this->assign("VIEW",true);
		return $this->fetch($this->template_dir . $this->template_mod . "_new.html");

	}
	
	function default_action_process() {
		if ($_POST['process'] != "true")
			return;
		$this->hptje_primary = new FormHpTjePrimary($_POST['id']);
		parent::populate_object($this->hptje_primary);
		
		$this->hptje_primary->persist();
		if ($GLOBALS['encounter'] == "") {
			$GLOBALS['encounter'] = date("Ymd");
		}
		addForm($GLOBALS['encounter'], "Head Pain TJE", $this->hptje_primary->id, "hp_tje_primary", $GLOBALS['pid'], $_SESSION['userauthorized']);
		$_POST['process'] = "";
		return;
	}
    
}



?>