<?php

require_once ($GLOBALS['fileroot'] . "/library/classes/Controller.class.php");
require_once($GLOBALS['fileroot'] ."/library/classes/X12Partner.class.php");

class C_X12Partner extends Controller {

	var $template_mod;
	var $providers;
	var $x12_partners;

	function C_X12Partner($template_mod = "general") {
		parent::Controller();
		$this->x12_partner = array();
		$this->template_mod = $template_mod;
		$this->assign("FORM_ACTION", $GLOBALS['webroot']."/controller.php?" . $_SERVER['QUERY_STRING']);
		$this->assign("CURRENT_ACTION", $GLOBALS['webroot']."/controller.php?" . "practice_settings&x12_partner&");
		$this->assign("STYLE", $GLOBALS['style']);
        $this->assign("WEB_ROOT", $GLOBALS['webroot'] );
	}

	function default_action() {
		return $this->list_action();
	}
	
	function edit_action($id = "",$x_obj = null) {
		if ($x_obj != null && get_class($x_obj) == "x12partner") {
			$this->x12_partners[0] = $x_obj;
		}
		elseif (is_numeric($id)) {
			$this->x12_partners[0] = new X12Partner($id);
		}
		else {
			$this->x12_partners[0] = new X12Partner();
		}
		$this->assign("partner", $this->x12_partners[0]);
		return $this->fetch($GLOBALS['template_dir'] . "x12_partners/" . $this->template_mod . "_edit.html");
	}
	
	function list_action() {

		$x = new X12Partner();
		//$x->set_name("Medi-Cal");
		//$x->set_x12_sender_id("123454");
		//$x->set_x12_receiver_id("123454");
		//$x->persist();
		//$x->populate();
		
		$this->assign("partners", $x->x12_partner_factory());
		return $this->fetch($GLOBALS['template_dir'] . "x12_partners/" . $this->template_mod . "_list.html");
	}


	function edit_action_process() {
		if ($_POST['process'] != "true")
			return;
		//print_r($_POST);
		if (is_numeric($_POST['id'])) {
			$this->x12_partner[0] = new X12Partner($_POST['id']);
		}
		else {
			$this->x12_partner[0] = new X12Partner();
		}
  		
  		parent::populate_object($this->x12_partner[0]);
		
		$this->x12_partner[0]->persist();
		//insurance numbers need to be repopulated so that insurance_company_name recieves a value
		$this->x12_partner[0]->populate();
		
		//echo "action processeed";
		$_POST['process'] = "";
		$this->_state = false;
		header('Location:'.$GLOBALS['webroot']."/controller.php?" . "practice_settings&x12_partner&action=list");//Z&H
		//return $this->edit_action(null,$this->x12_partner[0]);
	}

}

?>
