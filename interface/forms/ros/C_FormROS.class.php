<?php
/** Copyright (C) 2016 Sherwin Gaddis <sherwingaddis@gmail.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * Sherwin Gaddis <sherwingaddis@gmail.com>
 * @link    http://www.open-emr.org
 */
 
require_once ($GLOBALS['fileroot'] . "/library/classes/Controller.class.php");
require_once ($GLOBALS['fileroot'] . "/library/forms.inc");
require_once("FormROS.class.php");

class C_FormROS extends Controller {

	public $template_dir;
	
    public function __construct($template_mod = "general") {
    	parent::__construct();
    	$returnurl = $GLOBALS['concurrent_layout'] ? 'encounter_top.php' : 'patient_encounter.php';
    	$this->template_mod = $template_mod;
    	$this->template_dir = dirname(__FILE__) . "/templates/ros/";
    	$this->assign("FORM_ACTION", $GLOBALS['web_root']);
    	$this->assign("DONT_SAVE_LINK",$GLOBALS['webroot'] . "/interface/patient_file/encounter/$returnurl");
    	$this->assign("STYLE", $GLOBALS['style']);
    }
    
    public function default_action() {
    	$ros = new FormROS();
    	$this->assign("form",$ros);
		return $this->fetch($this->template_dir . $this->template_mod . "_new.html");
	}
	
	public function view_action($form_id) {
		
		if (is_numeric($form_id)) {
    		$ros = new FormROS($form_id);
    	}
    	else {
    		$ros = new FormROS();
    	}
    	
    	$this->assign("form",$ros);
    	return $this->fetch($this->template_dir . $this->template_mod . "_new.html");

	}
	
	public function default_action_process() {
		if ($_POST['process'] != "true"){
					
			return;
		}
		$this->ros = new FormROS($_POST['id']);
		
		parent::populate_object($this->ros);
		$this->ros->persist();
		
		if ($GLOBALS['encounter'] == "") {
			$GLOBALS['encounter'] = date("Ymd");
		}
		if(empty($_POST['id']))
		{
			addForm($GLOBALS['encounter'], "Review Of Systems", $this->ros->id, "ros", $GLOBALS['pid'], $_SESSION['userauthorized']);
			$_POST['process'] = "";
		}
		return;
	}
    
}



?>
