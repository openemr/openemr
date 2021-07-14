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
use OpenEMR\Services\VitalsService;
use OpenEMR\Common\Utils\MeasurementUtils;

class C_FormVitals extends Controller
{

    var $template_dir;
    var $form_id;

    const MEASUREMENT_METRIC_ONLY = 4;
    const MEASUREMENT_USA_ONLY = 3;
    const MEASUREMENT_PERSIST_IN_METRIC = 2;
    const MEASUREMENT_PERSIST_IN_USA = 1;

    const OMIT_CIRCUMFERENCES_NO = 0;
    const OMIT_CIRCUMFERENCES_YES = 1;

    public function __construct($template_mod = "general")
    {
        parent::__construct();
        $this->units_of_measurement = $GLOBALS['units_of_measurement'];

        $returnurl = 'encounter_top.php';
        $this->template_mod = $template_mod;
        $this->template_dir = __DIR__ . "/templates/vitals/";
        $this->assign("FORM_ACTION", $GLOBALS['web_root']);
        $this->assign("DONT_SAVE_LINK", $GLOBALS['form_exit_url']);
        $this->assign("STYLE", $GLOBALS['style']);

      // Options for units of measurement and things to omit.
        $this->assign("units_of_measurement", $this->units_of_measurement);

        $this->assign("MEASUREMENT_METRIC_ONLY", self::MEASUREMENT_METRIC_ONLY);
        $this->assign("MEASUREMENT_USA_ONLY", self::MEASUREMENT_USA_ONLY);
        $this->assign("MEASUREMENT_PERSIST_IN_METRIC", self::MEASUREMENT_PERSIST_IN_METRIC);
        $this->assign("MEASUREMENT_PERSIST_IN_USA", self::MEASUREMENT_PERSIST_IN_USA);

//        $this->assign("gbl_vitals_options", $GLOBALS['gbl_vitals_options']);
        $this->assign("hide_circumferences", $GLOBALS['gbl_vitals_options'] > 0);

        // Assign the CSRF_TOKEN_FORM
        $this->assign("CSRF_TOKEN_FORM", CsrfUtils::collectCsrfToken());
    }

    public function default_action_old()
    {
        //$vitals = array();
        //array_push($vitals, new FormVitals());
        $vitals = new FormVitals();
        $this->assign("vitals", $vitals);
        $this->assign("results", $results);
        return $this->fetch($this->template_dir . $this->template_mod . "_new.html");
    }

    public function setFormId($form_id)
    {
        $this->form_id = $form_id;
    }

    public function default_action()
    {

        $form_id = $this->form_id;

        if (is_numeric($form_id)) {
            $vitals = new FormVitals($form_id);
        } else {
            $vitals = new FormVitals();
        }

        // get the patient's current age
        $patient_data = getPatientData($GLOBALS['pid']);
        $patient_dob = $patient_data['DOB'];
        $patient_age = getPatientAge($patient_dob);
        $this->assign("patient_age", $patient_age);
        $this->assign("patient_dob", $patient_dob);

        $i = 1;
        $vitalsColumn = ['bps', 'bpd', 'weight', 'height', 'temperature', 'temp_method', 'pulse', 'BMI', 'BMI_status',
            'waist_circ', 'head_circ', 'respiration', 'oxygen_saturation', 'oxygen_flow_rate', 'ped_weight_height'
            , 'ped_bmi', 'ped_head_circ'
        ];
        $vitalsService = new VitalsService();
        // eventually we want this just to use the service search date but we will move this here.
        $records = $vitalsService->getVitalsHistoryForPatient($GLOBALS['pid'], $form_id);

        foreach ($records as $result) {
            $results[$i]['id'] = $result['id'];
            $results[$i]['uuid'] = $result['uuid'];
//            $results[$i]['encdate'] = substr($result['encdate'], 0, 10);
            $results[$i]['date'] = $result['date'];
            $results[$i]['activity'] = $result['activity'];
            $results[$i]['note'] = $result['note'];
            foreach ($vitalsColumn as $column)
            {
                $results[$i][$column] = $result[$column];

                if (isset($result[$column . '_interpretation_id']))
                {
                    $results[$i][$column . '_intrepration_id'] = $result[$column . '_interpretation_id'];
                    $results[$i][$column . '_intrepration_code'] = $result[$column . '_interpretation_code'];
                    $results[$i][$column . '_intrepration_text'] = $result[$column . '_interpretation_text'];
                }
            }
            $this->convertVitalsToCorrectMeasurementUnit($results[$i], $result);

            // weight has a unique lb/oz display so we will handle that here.
            $results[$i]['weight']['usa'] = $vitals->display_weight($results[$i]['weight']['usa']);
            $i++;
        }

        $this->assign("vitals", $vitals);
        $this->assign("results", ($results ?? null));

        $this->assign('interpretation_options', $this->get_interpretation_list_options());

        $this->assign("VIEW", true);
        return $this->fetch($this->template_dir . $this->template_mod . "_new.html");
    }

    private function get_interpretation_list_options()
    {
        $listService = new \OpenEMR\Services\ListService();
        $options = $listService->getOptionsByListName('proc_res_abnormal');
        return array_map(function($option) {
            return [
                'title' => $option['title']
                ,'id' => $option['option_id']
            ];
        }, $options);
    }

    private function convertVitalsToCorrectMeasurementUnit(&$resultRow, $vitalsResult)
    {
        // go through each column and run the conversion function
        $lookupTable = [
            'weight' => 'lbs'
            ,'height' => 'in'
            ,'ped_circ' => 'in'
            ,'waist_circ' => 'in'
            ,'head_circ' => 'in'
            ,'temperature' => 'Cel'
        ];
        $conversionLookup = [
            'in' => [
                'usa' => 'inchesToCm'
                ,'metric' => 'cmToInches'
            ]
            ,'lbs' => [
                'usa' => 'lbToKg'
                ,'metric' => 'kgToLb'
            ]
            ,'Cel' => [
                'usa' => 'fhToCelsius'
                ,'metric' => 'celsiusToFh'
            ]
        ];

        foreach ($lookupTable as $key => $unit) {
            $value = $resultRow[$key] ?? 0;
            $usa = $metric = $value;

            // we skip over any 0s as that is the default value... which is strange for a measurement...
            if ($value > 0 || $value < 0) { // capture floats
                // because the internal service converts all of the vitals to the correct format set in globals
                // we have to undo the conversion if we are displaying the result sets.
                if (isset($conversionLookup[$unit])
                    &&
                    ($this->units_of_measurement == self::MEASUREMENT_PERSIST_IN_METRIC
                    || $this->units_of_measurement == self::MEASUREMENT_METRIC_ONLY)) {
                    $converter = $conversionLookup[$unit]['metric'];
                    $usa = MeasurementUtils::$converter($value);
                } else {
                    $converter = $conversionLookup[$unit]['usa'];
                    $metric = MeasurementUtils::$converter($value);
                }
            }
            $resultRow[$key] = [
                'usa' => $usa
                ,'metric' => $metric
            ];
        }
    }

    public function default_action_process()
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

        $this->populate_object($this->vitals);

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
