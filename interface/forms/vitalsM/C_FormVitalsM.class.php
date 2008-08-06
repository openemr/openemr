<?php

require_once ($GLOBALS['fileroot'] . "/library/classes/Controller.class.php");
require_once ($GLOBALS['fileroot'] . "/library/forms.inc");
require_once("FormVitalsM.class.php");

class C_FormVitalsM extends Controller {

  var $template_dir;

  function C_FormVitalsM($template_mod = "general") {
    parent::Controller();
    $returnurl = $GLOBALS['concurrent_layout'] ? 'encounter_top.php' : 'patient_encounter.php';
    $this->template_mod = $template_mod;
    $this->template_dir = dirname(__FILE__) . "/templates/vitalsM/";
    $this->assign("FORM_ACTION", $GLOBALS['web_root']);
    $this->assign("DONT_SAVE_LINK",$GLOBALS['webroot'] . "/interface/patient_file/encounter/$returnurl");
    $this->assign("STYLE", $GLOBALS['style']);
  }

  function default_action_old() {
      $vitals = new FormVitalsM();
      $this->assign("vitals",$vitals);
      $this->assign("results", $results);
      return $this->fetch($this->template_dir . $this->template_mod . "_new.html");
  }

  function default_action($form_id) {

    if (is_numeric($form_id)) {
      $vitals = new FormVitalsM($form_id);
    }
    else {
      $vitals = new FormVitalsM();
    }

    $dbconn = $GLOBALS['adodb']['db'];
    $sql = "SELECT * from form_vitalsM where id != $form_id and pid = ".$GLOBALS['pid'];
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
      $results[$i]['BMI_status'] = $result->fields['BMI_status'];
      $results[$i]['note'] = $result->fields['note'];
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

    $weight = $_POST["weight"];
    $height = $_POST["height"];
    if ($weight > 0 && $height > 0) {
      $_POST["BMI"] = ($weight/$height/$height)*10000;
    }
    if     ( $_POST["BMI"] > 42 )   $_POST["BMI_status"] = 'Obesity III';
    elseif ( $_POST["BMI"] > 34 )   $_POST["BMI_status"] = 'Obesity II';
    elseif ( $_POST["BMI"] > 30 )   $_POST["BMI_status"] = 'Obesity I';
    elseif ( $_POST["BMI"] > 27 )   $_POST["BMI_status"] = 'Overweight';
    elseif ( $_POST["BMI"] > 25 )   $_POST["BMI_status"] = 'Normal BL';
    elseif ( $_POST["BMI"] > 18.5 ) $_POST["BMI_status"] = 'Normal';
    elseif ( $_POST["BMI"] > 10 )   $_POST["BMI_status"] = 'Underweight';
    $temperature = $_POST["temperature"];
    if ($temperature == '0' || $temperature == '') {
      $_POST["temp_method"] = "";
    }

    $this->vitals = new FormVitalsM($_POST['id']);

    parent::populate_object($this->vitals);

    $this->vitals->persist();
    if ($GLOBALS['encounter'] < 1) {
      $GLOBALS['encounter'] = date("Ymd");
    }
    if(empty($_POST['id']))
    {
      addForm($GLOBALS['encounter'], "Vitals (Metric)", $this->vitals->id, "vitalsM", $GLOBALS['pid'], $_SESSION['userauthorized']);
      $_POST['process'] = "";
    }
    return;
  }

}
?>
