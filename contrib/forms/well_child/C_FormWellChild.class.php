<?php

require_once ($GLOBALS['fileroot'] . "/library/classes/Controller.class.php");
require_once ($GLOBALS['fileroot'] . "/library/forms.inc");
require_once ($GLOBALS['fileroot'] . "/library/sql.inc");
require_once("FormWellChild.class.php");

class C_FormWellChild extends Controller {

	var $template_dir;
	
    function C_FormWellChild($template_mod = "general") {
    	parent::Controller();
    	$this->template_mod = $template_mod;
    	$this->template_dir = dirname(__FILE__) . "/templates/well_child/";
    	$this->assign("FORM_ACTION", $GLOBALS['web_root']);
    	$this->assign("DONT_SAVE_LINK", $GLOBALS['form_exit_url']);
    	$this->assign("STYLE", $GLOBALS['style']);
    }
    
    function default_action() {
    	$well_child = new FormWellChild();
    	$this->assign("checks",$well_child->_form_layout());
    	$this->assign("checks2",$well_child->_form_layout2());
    	$this->assign("checks3",$well_child->_form_layout3());
    	$this->assign("well_child",$well_child);
		return $this->fetch($this->template_dir . $this->template_mod . "_new.html");
	}
	
	function view_action($form_id) {
		if (is_numeric($form_id)) {
    		$well_child = new FormWellChild($form_id);
    	}
    	else {
    		$well_child = new FormWellChild();
    	}
    	$this->assign("VIEW",true);
    	$this->assign("checks",$well_child->_form_layout());
    	$this->assign("checks2",$well_child->_form_layout2());
    	$this->assign("checks3",$well_child->_form_layout3());
    	$this->assign("well_child",$well_child);
		return $this->fetch($this->template_dir . $this->template_mod . "_new.html");

	}
	
	function default_action_process() {
		if ($_POST['process'] != "true")
			return;
		$this->well_child = new FormWellChild($_POST['id']);
		parent::populate_object($this->well_child);
	
		 $new_form = false;
                 if (empty($_POST['id'])) {
                   $new_form = true;
                 }
	
		$this->well_child->persist();
		if ($GLOBALS['encounter'] == "") {
			$GLOBALS['encounter'] = date("Ymd");
		}
		
		if ($new_form) {
		  addForm($GLOBALS['encounter'], "Well Child Visit", $this->well_child->id, "well_child", $GLOBALS['pid'], $_SESSION['userauthorized']);
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
