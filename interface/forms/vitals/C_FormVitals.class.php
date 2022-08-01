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

class C_FormVitals extends Controller
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
        parent::__construct();
        $this->units_of_measurement = $GLOBALS['units_of_measurement'];
        $this->interpretationsList = $this->get_interpretation_list_options();

        $returnurl = 'encounter_top.php';
        $this->template_mod = $template_mod;
        $this->template_dir = __DIR__ . "/templates/vitals/";
        $this->assign("FORM_ACTION", $GLOBALS['web_root']);
        $this->assign("DONT_SAVE_LINK", $GLOBALS['form_exit_url']);
        $this->assign("STYLE", $GLOBALS['style']);

      // Options for units of measurement and things to omit.
        $this->assign("units_of_measurement", $this->units_of_measurement);

        $this->assign("MEASUREMENT_METRIC_ONLY", FormVitals::MEASUREMENT_METRIC_ONLY);
        $this->assign("MEASUREMENT_USA_ONLY", FormVitals::MEASUREMENT_USA_ONLY);
        $this->assign("MEASUREMENT_PERSIST_IN_METRIC", FormVitals::MEASUREMENT_PERSIST_IN_METRIC);
        $this->assign("MEASUREMENT_PERSIST_IN_USA", FormVitals::MEASUREMENT_PERSIST_IN_USA);

//        $this->assign("gbl_vitals_options", $GLOBALS['gbl_vitals_options']);
        $this->assign("hide_circumferences", $GLOBALS['gbl_vitals_options'] > 0);

        // Assign the CSRF_TOKEN_FORM
        $this->assign("CSRF_TOKEN_FORM", CsrfUtils::collectCsrfToken());
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
        $this->assign("patient_age", $patient_age);
        $this->assign("patient_dob", $patient_dob);

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

        $this->assign("vitals", $vitals);
        $this->assign("results", ($results ?? null));
        $this->assign("results_count", count(($results ?? [])));

        $this->assign('interpretation_options', $this->interpretationsList);
        $this->assign('reasonCodeStatii', $reasonCodeStatii);

        $this->assign("VIEW", true);
        return $this->fetch($this->template_dir . $this->template_mod . "_new.html");
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
        parent::populate_object($obj);

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
                } else if (isset($interpretationList[$value])) {
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
