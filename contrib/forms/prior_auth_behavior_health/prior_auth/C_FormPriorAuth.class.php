<?php

/** 
* 
* Copyright (C) 2008-2016 Rod Roark <rod@sunsetsystems.com> 
* 
* LICENSE: This program is free software; you can redistribute it and/or 
* modify it under the terms of the GNU General Public License 
* as published by the Free Software Foundation; either version 3 
* of the License, or (at your option) any later version. 
* This program is distributed in the hope that it will be useful, 
* but WITHOUT ANY WARRANTY; without even the implied warranty of 
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the 
* GNU General Public License for more details. 
* You should have received a copy of the GNU General Public License 
* along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;. 
* 
* @package OpenEMR 
* @author Rod Roark <rod@sunsetsystems.com> 
* @author Brady Miller <brady@sparmy.com> 
* @author Sherwin Gaddis <sherwingaddis@gmail.com> 
* @link http://www.open-emr.org 
*/

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
		$this->assign("prior_Auth_number", "");
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
		$this->assign("NoAuth", $prior_auth->get_not_req());
		$this->assign("Alert", $prior_auth->get_units());
		$this->assign("OverRide", $prior_auth->get_override());
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