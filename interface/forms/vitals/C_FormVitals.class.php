<?php

/**
 * vitals C_FormVitals.class.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once($GLOBALS['fileroot'] . "/library/forms.inc");
require_once($GLOBALS['fileroot'] . "/library/patient.inc");
require_once("FormVitals.class.php");

use OpenEMR\Common\Csrf\CsrfUtils;

class C_FormVitals extends Controller
{

    var $template_dir;
    var $form_id;

    function __construct($template_mod = "general")
    {
        parent::__construct();
        $returnurl = 'encounter_top.php';
        $this->template_mod = $template_mod;
        $this->template_dir = dirname(__FILE__) . "/templates/vitals/";
        $this->assign("FORM_ACTION", $GLOBALS['web_root']);
        $this->assign("DONT_SAVE_LINK", $GLOBALS['form_exit_url']);
        $this->assign("STYLE", $GLOBALS['style']);

      // Options for units of measurement and things to omit.
        $this->assign("units_of_measurement", $GLOBALS['units_of_measurement']);
        $this->assign("gbl_vitals_options", $GLOBALS['gbl_vitals_options']);

        // Assign the CSRF_TOKEN_FORM
        $this->assign("CSRF_TOKEN_FORM", CsrfUtils::collectCsrfToken());
    }

    function default_action_old()
    {
        //$vitals = array();
        //array_push($vitals, new FormVitals());
        $vitals = new FormVitals();
        $this->assign("vitals", $vitals);
        $this->assign("results", $results);
        return $this->fetch($this->template_dir . $this->template_mod . "_new.html");
    }

    function setFormId($form_id)
    {
        $this->form_id = $form_id;
    }

    function default_action()
    {

        $form_id = $this->form_id;

        if (is_numeric($form_id)) {
            $vitals = new FormVitals($form_id);
        } else {
            $vitals = new FormVitals();
        }

        //Combined query for retrieval of vital information which is not deleted
        $sql = "SELECT fv.*, fe.date AS encdate " .
        "FROM form_vitals AS fv, forms AS f, form_encounter AS fe WHERE " .
        "fv.id != ? and fv.pid = ? AND " .
        "f.formdir = 'vitals' AND f.deleted = 0 AND f.form_id = fv.id AND " .
        "fe.pid = f.pid AND fe.encounter = f.encounter " .
        "ORDER BY encdate DESC, fv.date DESC";
        $res = sqlStatement($sql, array($form_id, $GLOBALS['pid']));

        // get the patient's current age
        $patient_data = getPatientData($GLOBALS['pid']);
        $patient_dob = $patient_data['DOB'];
        $patient_age = getPatientAge($patient_dob);
        $this->assign("patient_age", $patient_age);
        $this->assign("patient_dob", $patient_dob);

        $i = 1;
        while ($result = sqlFetchArray($res)) {
            $results[$i]['id'] = $result['id'];
            $results[$i]['encdate'] = substr($result['encdate'], 0, 10);
            $results[$i]['date'] = $result['date'];
            $results[$i]['activity'] = $result['activity'];
            $results[$i]['bps'] = $result['bps'];
            $results[$i]['bpd'] = $result['bpd'];
            $results[$i]['weight'] = $result['weight'];
            $results[$i]['height'] = $result['height'];
            $results[$i]['temperature'] = $result['temperature'];
            $results[$i]['temp_method'] = $result['temp_method'];
            $results[$i]['pulse'] = $result['pulse'];
            $results[$i]['respiration'] = $result['respiration'];
            $results[$i]['BMI'] = $result['BMI'];
            $results[$i]['BMI_status'] = $result['BMI_status'];
            $results[$i]['note'] = $result['note'];
            $results[$i]['waist_circ'] = $result['waist_circ'];
            $results[$i]['head_circ'] = $result['head_circ'];
            $results[$i]['oxygen_saturation'] = $result['oxygen_saturation'];
            $i++;
        }

        $this->assign("vitals", $vitals);
        $this->assign("results", ($results ?? null));

        $this->assign("VIEW", true);
        return $this->fetch($this->template_dir . $this->template_mod . "_new.html");
    }

    function default_action_process()
    {
        if ($_POST['process'] != "true") {
            return;
        }

        $weight = $_POST["weight"];
        $height = $_POST["height"];
        if ($weight > 0 && $height > 0) {
            $_POST["BMI"] = ($weight / $height / $height) * 703;
        }

        if ($_POST["BMI"] > 42) {
            $_POST["BMI_status"] = 'Obesity III';
        } elseif ($_POST["BMI"] > 34) {
            $_POST["BMI_status"] = 'Obesity II';
        } elseif ($_POST["BMI"] > 30) {
            $_POST["BMI_status"] = 'Obesity I';
        } elseif ($_POST["BMI"] > 27) {
            $_POST["BMI_status"] = 'Overweight';
        } elseif ($_POST["BMI"] > 25) {
            $_POST["BMI_status"] = 'Normal BL';
        } elseif ($_POST["BMI"] > 18.5) {
            $_POST["BMI_status"] = 'Normal';
        } elseif ($_POST["BMI"] > 10) {
            $_POST["BMI_status"] = 'Underweight';
        }

        $temperature = $_POST["temperature"];
        if ($temperature == '0' || $temperature == '') {
            $_POST["temp_method"] = "";
        }

        $this->vitals = new FormVitals($_POST['id']);

        parent::populate_object($this->vitals);

        $this->vitals->persist();
        if ($GLOBALS['encounter'] < 1) {
            $GLOBALS['encounter'] = date("Ymd");
        }

        if (empty($_POST['id'])) {
            addForm($GLOBALS['encounter'], "Vitals", $this->vitals->id, "vitals", $GLOBALS['pid'], $_SESSION['userauthorized']);
            $_POST['process'] = "";
        }

        return;
    }
}
