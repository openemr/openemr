<?php

require_once ($GLOBALS['fileroot'] . "/library/classes/Controller.class.php");
require_once ($GLOBALS['fileroot'] . "/library/forms.inc");
require_once("FormVitals.class.php");

class C_FormVitals extends Controller {

	var $template_dir;
	
    function C_FormVitals($template_mod = "general") {
    	parent::Controller();
    	$this->template_mod = $template_mod;
    	$this->template_dir = dirname(__FILE__) . "/templates/vitals/";
    	$this->assign("FORM_ACTION", $GLOBALS['web_root']);
    	$this->assign("DONT_SAVE_LINK",$GLOBALS['webroot'] . "/interface/patient_file/encounter/patient_encounter.php");
    	$this->assign("STYLE", $GLOBALS['style']);
    }
    
    function default_action_old() {
    	//$vitals = array();
    	//array_push($vitals, new FormVitals());
    	$vitals = new FormVitals();
    	$this->assign("vitals",$vitals);
    	$this->assign("results", $results);
    	return $this->fetch($this->template_dir . $this->template_mod . "_new.html");
	}
	
	function default_action($form_id) {

		if (is_numeric($form_id)) {
    		$vitals = new FormVitals($form_id);
    	}
    	else {
    		$vitals = new FormVitals();
    	}
    	
    	$dbconn = $GLOBALS['adodb']['db'];
    	$sql = "SELECT * from form_vitals where id != $form_id and pid = ".$GLOBALS['pid'];
    	$result = $dbconn->Execute($sql);
    	
    	$i = 1;
    	while($result && !$result->EOF)
    	{
    		$results[$i]['id'] = $result->fields['id'];
    		$results[$i]['date'] = $result->fields['date'];
    		$results[$i]['activity'] = $result->fields['activity'];
    		$results[$i]['bps'] = $result->fields['bps'];
    		$results[$i]['bpd'] = $result->fields['bpd'];
    		$results[$i]['weight'] = $result->fields['weight'];
    		$results[$i]['height'] = $result->fields['height'];
    		$results[$i]['temperature'] = $result->fields['temperature'];
    		$results[$i]['temp_method'] = $result->fields['temp_method'];
    		$results[$i]['pulse'] = $result->fields['pulse'];
    		$results[$i]['respiration'] = $result->fields['respiration'];
    		$results[$i]['BMI'] = $result->fields['BMI'];
    		$results[$i]['waist_circ'] = $result->fields['waist_circ'];
    		$results[$i]['head_circ'] = $result->fields['head_circ'];
    		$results[$i++]['oxygen_saturation'] = $result->fields['oxygen_saturation'];
    		$result->MoveNext();
    	}
    	
    	$this->assign("vitals",$vitals);
    	$this->assign("results", $results);
    	   	
    	$this->assign("VIEW",true);
		return $this->fetch($this->template_dir . $this->template_mod . "_new.html");

	}
	
	function default_action_process() {
		if ($_POST['process'] != "true")
			return;
		$this->vitals = new FormVitals($_POST['id']);
		
		parent::populate_object($this->vitals);
		
		$this->vitals->persist();
		if ($GLOBALS['encounter'] < 1) {
			$GLOBALS['encounter'] = date("Ymd");
		}
		if(empty($_POST['id']))
		{
			addForm($GLOBALS['encounter'], "Vitals", $this->vitals->id, "vitals", $GLOBALS['pid'], $_SESSION['userauthorized']);
			$_POST['process'] = "";
		}
		return;
	}
    
}



?>
