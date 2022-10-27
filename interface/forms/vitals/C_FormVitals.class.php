<?php

/**
 * vitals C_FormVitals.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once($GLOBALS['fileroot'] . "/library/forms.inc");
require_once($GLOBALS['fileroot'] . "/library/patient.inc");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Forms\FormVitals;
use OpenEMR\Common\Forms\FormVitalDetails;
use OpenEMR\Common\Forms\ReasonStatusCodes;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\VitalsService;
use OpenEMR\Services\ListService;
use OpenEMR\Common\Twig\TwigContainer;

class C_FormVitals
{
    /**
     * @var FormVitals
     */
    public $vitals;

    var $template_dir;
    var $form_id;


    const OMIT_CIRCUMFERENCES_NO = 0;
    const OMIT_CIRCUMFERENCES_YES = 1;

    /**
     * @var array Hashmap of list option vital interpretations where the key is the option_id from list_options
     */
    private $interpretationsList = [];

    public function __construct($template_mod = "general")
    {
        $this->units_of_measurement = $GLOBALS['units_of_measurement'];
        $this->interpretationsList = $this->get_interpretation_list_options();
        $this->template_mod = $template_mod;
        $this->template_dir = __DIR__ . "/templates/vitals/";
    }

    public function setFormId($form_id)
    {
        $this->form_id = $form_id;
    }

    public function default_action()
    {
        $vitalsService = new VitalsService();
        $vitalsService->setShouldConvertVitalMeasurementsFlag(false);
        $form_id = $this->form_id;

        $vitals = new FormVitals();
        if (is_numeric($form_id)) {
            // TODO: Do we want to throw an error if the form has been deleted or is empty?
            $vitalsArray = $vitalsService->getVitalsForForm($form_id) ?? [];
            // vitals form returns string representation of uuid, need to convert it back to binary
            if (isset($vitalsArray['uuid'])) {
                $vitalsArray['uuid'] = UuidRegistry::uuidToBytes($vitalsArray['uuid']);
            }
            $vitals->populate_array($vitalsArray);
        } else {
            $this->populate_session_user_information($vitals);
        }

        // get the patient's current age
        $patient_data = getPatientData($GLOBALS['pid']);
        $patient_dob = $patient_data['DOB'];
        $patient_age = getPatientAge($patient_dob);

        $i = 1;
        // eventually we want this just to use the service search date but we will move this here.
        $records = $vitalsService->getVitalsHistoryForPatient($GLOBALS['pid'], $form_id);

        foreach ($records as $result) {
            $historicalVitals = new FormVitals();
            // TODO: @brady.miller note we dropped the inconsistency of displaying historical usa weights in lbs&oz and entering them in via decimal format.
            // Everything now is consistent decimal format, I can put it back but its a bit of work to treat weight special over everything else and seems
            // odd the inconsistency
            $historicalVitals->populate_array($result);
            $results[$i] = $historicalVitals;
            $i++;
        }

        $reasonCodeStatii = ReasonStatusCodes::getCodesWithDescriptions();
        $reasonCodeStatii[ReasonStatusCodes::NONE]['description'] = xl("Select a status code");

        $show_pediatric_fields = ($patient_age <= 20 || (preg_match('/month/', $patient_age)));
        $vitalFields = [
            [
                'type' => 'textbox_conversion'
                ,'title' => xl('Weight')
                ,'input' => 'weight'
                // eventually we could just grab the raw values...
                ,'vitalsValue' => "get_weight"
                ,'vitalsValueMetric' => "get_weight_metric"
                ,'unit' => xl('lbs')
                ,'unitMetric' => xl('kg')
                ,'precision' => 2
                ,'vitalsValueUSAHelpTitle' => xl("Decimal pounds or pounds and ounces separated by #(e.g. 5#4)")
                ,'codes' => 'LOINC:29463-7'
            ]
            ,[
                'type' => 'textbox_conversion'
                ,'title' => xl('Height/Length')
                // eventually we could just grab the raw values...
                ,'vitalsValue' => "get_height"
                ,'vitalsValueMetric' => "get_height_metric"
                ,'input' => 'height'
                ,'unit' => xl('in')
                ,'unitMetric' => xl('cm')
                ,'precision' => 2
                ,'codes' => 'LOINC:8302-2'
            ]
            ,[
                'type' => 'textbox'
                ,'title' => xl('BP Systolic')
                // eventually we could just grab the raw values...
                ,'vitalsValue' => "get_bps"
                ,'input' => 'bps'
                ,'unit' => xl('mmHg')
                ,'codes' => 'LOINC:8480-6'
            ]
            ,[
                'type' => 'textbox'
                ,'title' => xl('BP Diastolic')
                // eventually we could just grab the raw values...
                ,'vitalsValue' => "get_bpd"
                ,'input' => 'bpd'
                ,'unit' => xl('mmHg')
                ,'codes' => 'LOINC:8462-4'
            ]
            ,[
                'type' => 'textbox'
                ,'title' => xl('Pulse')
                // eventually we could just grab the raw values...
                ,'vitalsValue' => "get_pulse"
                ,'precision' => 0
                ,'input' => 'pulse'
                ,'unit' => xl('per min')
                ,'codes' => 'LOINC:8867-4'
            ]
            ,[
                'type' => 'textbox'
                ,'title' => xl('Respiration')
                // eventually we could just grab the raw values...
                ,'vitalsValue' => "get_respiration"
                ,'precision' => 0
                ,'input' => 'respiration'
                ,'unit' => xl('per min')
                ,'codes' => 'LOINC:9279-1'
            ]
            ,[
                'type' => 'textbox_conversion'
                ,'title' => xl('Temperature')
                // eventually we could just grab the raw values...
                ,'vitalsValue' => "get_temperature"
                ,'vitalsValueMetric' => "get_temperature_metric"
                ,'input' => 'temperature'
                ,'unit' => xl('F')
                ,'unitMetric' => xl('C')
                ,'precision' => 2
                ,'codes' => 'LOINC:8310-5'
            ]
            ,[
                'type' => 'template'
                ,'templateName' => 'vitals_temp_method.html.twig'
            ]
            ,[
                'type' => 'textbox'
                ,'title' => xl('Oxygen Saturation')
                // eventually we could just grab the raw values...
                ,'vitalsValue' => "get_oxygen_saturation"
                ,'precision' => 2
                ,'input' => 'oxygen_saturation'
                ,'unit' => '%'
                ,'codes' => 'LOINC:59408-5'
            ]
            ,[
                'type' => 'textbox'
                ,'title' => xl('Oxygen Flow Rate')
                // eventually we could just grab the raw values...
                ,'vitalsValue' => "get_oxygen_flow_rate"
                ,'precision' => 2
                ,'input' => 'oxygen_flow_rate'
                ,'unit' => xl('l/min')
                ,'codes' => 'LOINC:3151-8'
            ]
            ,[
                'type' => 'textbox'
                ,'title' => xl('Inhaled Oxygen Concentration')
                // eventually we could just grab the raw values...
                ,'vitalsValue' => "get_inhaled_oxygen_concentration"
                ,'precision' => 0
                ,'input' => 'inhaled_oxygen_concentration'
                ,'unit' => '%'
                ,'codes' => 'LOINC:3150-0'
            ]
            ,[
                'type' => 'textbox_conversion'
                ,'title' => xl('Head Circumference')
                // eventually we could just grab the raw values...
                ,'vitalsValue' => "get_head_circ"
                ,'vitalsValueMetric' => "get_head_circ_metric"
                ,'input' => 'head_circ'
                ,'unit' => xl('in')
                ,'unitMetric' => xl('cm')
                ,'precision' => 2
                // hide_circumferences
                ,'hide' => $GLOBALS['gbl_vitals_options'] > 0
                ,'codes' => "LOINC:9843-4"
            ]
            ,[
                'type' => 'textbox_conversion'
                ,'title' => xl('Waist Circumference')
                // eventually we could just grab the raw values...
                ,'vitalsValue' => "get_waist_circ"
                ,'vitalsValueMetric' => "get_waist_circ_metric"
                ,'input' => 'waist_circ'
                ,'unit' => xl('in')
                ,'unitMetric' => xl('cm')
                ,'precision' => 2
                // hide_circumferences
                ,'hide' => $GLOBALS['gbl_vitals_options'] > 0
                ,'codes' => "LOINC:9843-4"
            ]
            ,[
                'type' => 'template'
                ,'templateName' => 'vitals_bmi.html.twig'
            ]
            ,[
                'type' => 'template'
                ,'templateName' => 'vitals_bmi_status.html.twig'
            ]
            ,[
                'type' => 'textbox'
                ,'title' => xl('Pediatric Weight Height Percentile')
                // eventually we could just grab the raw values...
                ,'vitalsValue' => "get_ped_weight_height"
                ,'input' => 'ped_weight_height'
                ,'unit' => '%'
                ,'codes' => 'LOINC:77606-2'
                ,'hide' => !$show_pediatric_fields
            ]
            ,[
                'type' => 'textbox'
                ,'title' => xl('Pediatric BMI Percentile')
                // eventually we could just grab the raw values...
                ,'vitalsValue' => "get_ped_bmi"
                ,'input' => 'ped_bmi'
                ,'unit' => '%'
                ,'codes' => 'LOINC:59576-9'
                ,'hide' => !$show_pediatric_fields
            ]
            ,[
                'type' => 'textbox'
                ,'title' => xl('Pediatric Head Circumference Percentile')
                // eventually we could just grab the raw values...
                ,'vitalsValue' => "get_ped_head_circ"
                ,'input' => 'ped_head_circ'
                ,'unit' => '%'
                ,'codes' => 'LOINC:8289-1'
                ,'hide' => !$show_pediatric_fields
            ]
            ,[
                'type' => 'template'
                ,'templateName' => 'vitals_notes.html.twig'
                ,'title' => xl('Other Notes')
                ,'input' => 'note'
                ,'vitalsValue' => 'get_note'
            ]
            ,[
                'type' => 'template'
                ,'templateName' => 'vitals_growthchart_actions.html.twig'
                ,'hide' => !$show_pediatric_fields
            ]
        ];

        $resultsCount = count($results ?? []);
        $hasMoreVitals = false;
        $vitalsHistoryLookback = [];
        $maxHistoryCols = $GLOBALS['gbl_vitals_max_history_cols'] ?? 2;
        if ($maxHistoryCols > 0 && $resultsCount > $maxHistoryCols) {
            $vitalsHistoryLookback = array_slice($results, 0, $maxHistoryCols);
            $hasMoreVitals = true;
        } else {
            $vitalsHistoryLookback = $results ?? null;
        }

        $data = [
            'vitals' => $vitals
            ,'vitalFields' => $vitalFields
            ,'FORM_ACTION' => $GLOBALS['web_root']
            ,'DONT_SAVE_LINK' => $GLOBALS['form_exit_url']
            ,'STYLE' => $GLOBALS['style']
            ,'units_of_measurement' => $this->units_of_measurement
            ,'MEASUREMENT_METRIC_ONLY' => FormVitals::MEASUREMENT_METRIC_ONLY
            ,'MEASUREMENT_USA_ONLY' => FormVitals::MEASUREMENT_USA_ONLY
            ,'MEASUREMENT_PERSIST_IN_METRIC' => FormVitals::MEASUREMENT_PERSIST_IN_METRIC
            ,'MEASUREMENT_PERSIST_IN_USA' => FormVitals::MEASUREMENT_PERSIST_IN_USA
            ,'hide_circumferences' => $GLOBALS['gbl_vitals_options'] > 0
            ,'CSRF_TOKEN_FORM' => CsrfUtils::collectCsrfToken()
            ,'results' => $results ?? null
            ,'vitalsHistoryLookback' => $vitalsHistoryLookback
            ,'hasMoreVitals' => $hasMoreVitals
            ,'results_count' => count(($results ?? []))
            ,'reasonCodeStatii' => $reasonCodeStatii
            ,'interpretation_options' => $this->interpretationsList
            ,'VIEW' => true
            ,'patient_age' => $patient_age
            ,'patient_dob' => $patient_dob
            ,'show_pediatric_fields' => ($patient_age <= 20 || (preg_match('/month/', $patient_age)))
        ];
        $twig = (new TwigContainer($this->template_dir, $GLOBALS['kernel']))->getTwig();

        echo $twig->render("vitals.html.twig", $data);
    }

    private function get_interpretation_list_options()
    {
        $listService = new ListService();
        $options = $listService->getOptionsByListName(FormVitals::LIST_OPTION_VITALS_INTERPRETATION);
        $orderedList = [];
        foreach ($options as $option) {
            $item = [
                'title' => $option['title']
                ,'id' => $option['option_id']
                ,'code' => $option['codes']
                ,'is_default' => $option['is_default'] == '1'
            ];
            $orderedList[] = $item;
        }
        return $orderedList;
    }

    private function get_interpretation_list_as_hash()
    {
        $hashList = [];
        foreach ($this->interpretationsList as $option) {
            $hashList[$option['id']] = $option;
        }
        return $hashList;
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

        // TODO: this should go into the vitals form...
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

        // grab our vitals data and then populate what is in the post
        $vitalsService = new VitalsService();
        $vitalsArray = $vitalsService->getVitalsForForm($_POST['id']) ?? [];
        // vitals form returns string representation of uuid, need to convert it back to binary
        if (isset($vitalsArray['uuid'])) {
            $vitalsArray['uuid'] = UuidRegistry::uuidToBytes($vitalsArray['uuid']);
        }

        $this->vitals = new FormVitals();
        $this->vitals->populate_array($vitalsArray);
        $this->populate_object($this->vitals);

        $vitalsService->saveVitalsForm($this->vitals);
        return;
    }

    public function populate_object(&$obj)
    {
        if (!is_object($obj)) {
            throw new \InvalidArgumentException("populate_object called with invalid argument");
        }

        // so we can get rid of smarty we are going to bring this from the controller class in here
        foreach ($_POST as $varname => $var) {
            $varname = preg_replace("/[^A-Za-z0-9_]/", "", $varname);
            $func = "set_" . $varname;
            //ensure compliant wth php 7.4 (no str_starts_with() function in 7.4)
            if (
                ((!function_exists('str_starts_with') && !(strpos("_", $varname) === 0)) || (function_exists('str_starts_with') && !(str_starts_with("_", $varname))))
                && is_callable(array($obj,$func))
            ) {
                //echo "c: $func on w: "  . $var . "<br />";

                $obj->$func($var, $_POST);
            }
        }

        $this->populate_session_user_information($obj);

        if ($GLOBALS['encounter'] < 1) {
            $GLOBALS['encounter'] = date("Ymd");
        }

        // have to set these global settings in order for us to save.
        $obj->set_encounter($GLOBALS['encounter']);
        $obj->set_pid($GLOBALS['pid']);
        $obj->set_authorized($_SESSION['userauthorized']);

        // handle all of the vital details that we need here.
        $detailsToUpdate = array();
        if (isset($_POST['interpretation'])) {
            $interpretationList = $this->get_interpretation_list_as_hash();
            // grab our default list options
            foreach ($_POST['interpretation'] as $column => $value) {
                $details = $this->vitals->get_details_for_column($column) ?? new FormVitalDetails();

                // if we have nothing set and nothing saved we are going to leave these values as empty
                if (empty($value) && empty($details->get_id())) {
                    continue;
                }

                if (empty($value)) {
                    $details->clear_interpretation();
                } elseif (isset($interpretationList[$value])) {
                    $interpretation = $interpretationList[$value];

                    // we save off both the code and the text value here which duplicates the data.  However, since
                    // users can edit the code / text values of these values through list_options we have to have
                    // a historical record so we save these off
                    // TODO: change this if list_options ever saves historical records and keeps the id uniquely the same
                    // then we can remove code/text
                    $details->set_interpretation_list_id(FormVitals::LIST_OPTION_VITALS_INTERPRETATION);
                    $details->set_interpretation_option_id($value);
                    $details->set_interpretation_codes($value); // for now the option_id is the code
                    $details->set_interpretation_title($interpretation['title']);
                } else {
                    (new SystemLogger())->error(
                        "Passed in interpretation does not exist in list options, clearing interpretation id",
                        ['form_id' => $this->vitals->get_id(), 'column' => $column, 'interpretation' => $value]
                    );
                    $details->clear_interpretation();
                }

                $details->set_vitals_column($column);
                $detailsToUpdate[$column] = $details;
            }
        }

        // now let's populate our reason codes if we have them.  Requires a reason code and a status code
        if (isset($_POST['reasonCode'])) {
            foreach ($_POST['reasonCode'] as $column => $value) {
                $details = $detailsToUpdate[$column] ?? $this->vitals->get_details_for_column($column) ?? new FormVitalDetails();
                if (empty($value) && empty($_POST['reasonCodeStatus'][$column]) && empty($details->get_id())) {
                    continue; // nothing to do here if we don't have a code and a status
                }
                if (empty($value) || empty($_POST['reasonCodeStatus'][$column])) {
                    $details->clear_reason();
                } else {
                    $details->set_reason_code($value);
                    $details->set_reason_status($_POST['reasonCodeStatus'][$column] ?? '');
                    $details->set_reason_description($_POST['reasonCodeText'][$column] ?? '');
                }
                $details->set_vitals_column($column);
                $detailsToUpdate[$column] = $details;
            }
        }

        foreach ($detailsToUpdate as $column => $details) {
            $this->vitals->set_details_for_column($column, $details);
        }
    }

    private function populate_session_user_information(FormVitals $vitals)
    {
        $vitals->set_groupname($_SESSION['authProvider']);
        $vitals->set_user($_SESSION['authUser']);
    }
}
