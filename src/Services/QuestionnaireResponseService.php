<?php

/**
 * Service for handling Questionnaires
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2022 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use Exception;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRQuestionnaire;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRQuestionnaireResponse;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRNarrative;
use OpenEMR\FHIR\R4\FHIRElement\FHIRNarrativeStatus;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\FHIR\R4\FHIRElement\FHIRString;

class QuestionnaireResponseService extends BaseService
{
    use QuestionnaireTraits;

    public const TABLE_NAME = 'questionnaire_response';
    private $repeatingForms;
    private $formDisplayNames;
    private $questionnaireService;

    /**
     * @throws Exception
     */
    public function __construct($questionnaire = null)
    {
        parent::__construct(self::TABLE_NAME);
        $this->questionnaireService = new QuestionnaireService();
        if (!empty($questionnaire)) {
            $this->setQuestionnaireForms($questionnaire);
        }
    }

    /**
     * @param $questionnaire
     * @return void
     * @throws Exception
     */
    public function setQuestionnaireForms($questionnaire): void
    {
        $forms = $this->questionnaireService->createQuestionnaireFormDictionary($questionnaire);
        $this->setFormDisplayNames($forms['form_display']['labels'] ?? []);
        $this->setRepeatingForms($forms['repeating_forms']['labels'] ?? []);
    }

    /**
     * @return mixed
     */
    public function getRepeatingForms()
    {
        return $this->repeatingForms;
    }

    /**
     * @param mixed $repeatingForms
     */
    public function setRepeatingForms($repeatingForms): void
    {
        $this->repeatingForms = $repeatingForms;
    }

    /**
     * @return mixed
     */
    public function getFormDisplayNames()
    {
        return $this->formDisplayNames;
    }

    /**
     * @param mixed $formDisplayNames
     */
    public function setFormDisplayNames($formDisplayNames): void
    {
        $this->formDisplayNames = $formDisplayNames;
    }

    /**
     * @param $source
     * @param $delimiter
     * @return string
     */
    public function buildQuestionnaireResponseFlex($source, $delimiter = '|'): string
    {
        $html = <<<head
        <html>
        <body>
        <form class='form'>
        <div class='d-flex d-lg-flex d-md-flex d-sm-flex flex-wrap'>
        head;
        $title = true;
        foreach ($source as $k => $value) {
            $v = explode($delimiter, $k);
            $last = count($v ?? []) - 1;
            $item = $v[$last];
            $margin_count = max(substr_count($k, 'item') - 1, 0);
            $margin = attr($margin_count * 2 . 'rem');
            if ($item === 'text') {
                if ($title) {
                    $html .= "<h4>" . text($value) . "</h4>";
                    $title = false;
                } else {
                    $html .= "<div class='w-100 m-0 p-0'><h5 class='my-1' style='margin-left: $margin;'>" . text($value) . "</h5></div>";
                }
            }
            if ($item === 'question') {
                $html .= "<div class='form-group my-0'><label class='my-0 font-weight-bold' style='margin-left: $margin;'>" . text($value) . ":</label>";
            }
            if ($item === 'answer') {
                if (is_array($value ?? null)) {
                    if ($value['unit'] ?? null) {
                        $value = $value['value'] . ' ' . $value['unit'];
                    } else {
                        $v = '';
                        foreach ($value as $a) {
                            $v .= $a . ' ';
                        }
                        $value = trim($v);
                    }
                }
                $html .= "<span class='my-0 ml-1'>" . text($value) . "</span></div>";
            }
        }
        $html .= <<<foot
        </div>
        </form>
        </body>
        </html>
        foot;

        return $html;
    }

    /**
     * @param $response
     * @return array
     */
    public function parseQuestionnaireResponseForms($response): array
    {
        $o = $this->parse($response);

        $fieldNames = [];
        $data = [];
        $instanceCount = 0;
        $repeatingForms = array_fill_keys($this->repeatingForms, true);

        $parseItems = function ($parent) use (&$parseItems, &$fieldNames, &$data, &$instanceCount, $repeatingForms) {
            foreach ($parent->getItem() as $item) {
                $answers = $item->getAnswer();
                $fieldText = $this->getText($item);
                if (empty($answers)) {
                    $parseItems($item);
                } else {
                    foreach ($answers as $answer) {
                        $fn = $this->getFieldName($item);
                        $fieldName = $fieldText; //use "$this->getFieldName($item)" if linkId path is wanted
                        $value = $this->getTypedValue($answer);
                        $formName = $this->getInstrumentName($parent, $item);
                        $repeatInstrument = '';
                        if ($repeatingForms[$formName]) {
                            $repeatInstrument = $formName;
                        }
                        $fieldNames[$fieldName] = true;
                        $fieldData = &$data[$repeatInstrument][$fieldName];
                        $fieldData[] = $value;

                        $instanceCount = max($instanceCount, count($fieldData));
                    }
                }
            }
        };
        $parseItems($o);
        // save to memory
        $out = fopen('php://memory', 'r+');

        $fieldNames = array_keys($fieldNames);
        fputcsv($out, array_merge(
            [
                'response_id',
                'repeat_instrument',
                'repeat_instance'
            ],
            $fieldNames
        ));
        foreach ($data as $repeatInstrument => $instancesByFieldName) {
            for ($instance = 1; $instance <= $instanceCount; $instance++) {
                $row = [
                    'placeholder',
                ];
                if (!$repeatInstrument) {
                    $row[] = 'Top Form';
                    $row[] = '';
                } else {
                    $row[] = $repeatInstrument;
                    $row[] = $instance;
                }
                $rowHasValues = false;
                foreach ($fieldNames as $fieldName) {
                    $value = @$instancesByFieldName[$fieldName][$instance - 1];
                    if (is_array($value ?? null)) {
                        if ($value['unit'] ?? null) {
                            $value = $value['value'] . ' ' . $value['unit'];
                        }
                    }
                    $row[] = $value;
                    if ($value !== null) {
                        $rowHasValues = true;
                    }
                }
                if ($rowHasValues) {
                    fputcsv($out, $row);
                }
                if (!$repeatInstrument) {
                    break; // No reason to continue iteration
                }
            }
        }

        $question_results = [];
        //rewind($out);
        //$question_results['csv_dictionary'] = stream_get_contents($out);
        rewind($out);
        $keys = fgetcsv($out, 1000);
        while (($d = fgetcsv($out, 5000)) !== false) {
            $question_results['form_repeats'][$d[1]] = $d[2];
            $question_results['form_groups'][$d[1]][$d[2]] = array_combine($keys, $d);
            $question_results['form_dictionary'][] = array_combine($keys, $d);
        }
        fclose($out);
        return $question_results;
    }

    /**
     * @param       $response
     * @param       $pid
     * @param null  $encounter
     * @param null  $qr_id
     * @param null  $qr_record_id
     * @param null  $q
     * @param null  $q_id
     * @param null  $form_response
     * @param bool  $add_report
     * @param array $scores
     * @return array|false|int|mixed
     * @throws Exception
     */
    public function saveQuestionnaireResponse(
        $response,
        $pid,
        $encounter = null,
        $qr_id = null,
        $qr_record_id = null,
        $q = null,
        $q_id = null,
        $form_response = null,
        $add_report = false,
        $scores = []
    ) {
        $q_content = null;
        $q_title = null;
        $q_record_id = null;
        $update_flag = false;
        if (is_string($q)) {
            $q = json_decode($q, true);
            $is_json = json_last_error() === JSON_ERROR_NONE;
            if (!$is_json) {
                throw new Exception(xlt("Questionnaire json is invalid"));
            }
        }
        // questionnaire. If isJason let's not reformat and use passed in json
        if (is_string($q)) {
            $q_content = $q;
            $q = json_decode($q, true);
            $is_json = json_last_error() === JSON_ERROR_NONE;
            if (!$is_json) {
                throw new Exception(xlt("Questionnaire json is invalid"));
            }
            $fhirQuestionnaireOb = new FHIRQuestionnaire($q);
        } elseif (is_array($q)) {
            $fhirQuestionnaireOb = new FHIRQuestionnaire($q);
            $q_content = $this->jsonSerialize($q);
        } else {
            throw new Exception(xlt("Questionnaire argument is invalid"));
        }
        // response
        if (is_string($response)) {
            $response = json_decode($response, true);
            $is_json = json_last_error() === JSON_ERROR_NONE;
            if (!$is_json) {
                throw new Exception(xlt("Questionnaire json is invalid"));
            }
            $fhirResponseOb = new FHIRQuestionnaireResponse($response);
        } elseif (is_array($response)) {
            $fhirResponseOb = new FHIRQuestionnaireResponse($response);
        } else {
            throw new Exception(xlt("Questionnaire response is invalid format"));
        }

        $version = 1;
        if (!empty($fhirQuestionnaireOb)) {
            $q_id = $q_id ?: $this->getValue($fhirQuestionnaireOb->id);
            $q_title = $this->getValue($fhirQuestionnaireOb->title);
            $q_name = $this->getValue($fhirQuestionnaireOb->name);
            if (empty($q_title)) {
                $q_title = $q_name;
            }
            $q_record_id = $this->questionnaireService->getQuestionnaireIdAndVersion($q_title, $q_id)['id'] ?? null;
        }

        if ($add_report) {
            $response_array = $this->fhirObjectToArray($fhirResponseOb);
            $answers = $this->flattenQuestionnaireResponse($response_array, '|', '');
            $html = $this->buildQuestionnaireResponseHtml($answers, '|');
            $report = new FHIRNarrative();
            $report->setStatus(new FHIRNarrativeStatus(['value' => 'generated']));
            $report->setDiv($html);
            $fhirResponseOb->setText($report);
        }

        if (!empty($qr_id)) {
            $update_flag = true;
        } else {
            $qr_id = $this->getValue($fhirResponseOb->id);
        }
        if (empty($qr_id)) {
            $qr_uuid = (new UuidRegistry(['table_name' => 'questionnaire_response']))->createUuid();
            // unique id for this set of answers
            $qr_id = UuidRegistry::uuidToString($qr_uuid);
            $update_flag = false;
        } else {
            $update_flag = true;
        }
        if ($update_flag) {
            $id = $this->getQuestionnaireResourceIdAndVersion(null, $qr_id, null);
            if (empty($id)) {
                $update_flag = false;
            }
        }

        $fhirResponseOb->setId(new FHIRId($qr_id));
        $fhirResponseOb->setQuestionnaire(new FHIRCanonical('fhir/Questionnaire/' . $q_id));
        if (is_numeric($encounter)) {
            $encounter_uuid = $this::getUuidById($encounter, 'form_encounter', 'encounter');
            if (!empty($encounter_uuid)) {
                $encounter = UuidRegistry::uuidToString($encounter_uuid) ?: $encounter;
            } else {
                $encounter = 0;
            }
        }
        if (!empty($encounter)) {
            $encRef = new FHIRReference();
            $encRef->setReference(new FHIRString('fhir/Encounter/' . $encounter));
            $fhirResponseOb->setEncounter($encRef);
        }
        $r_status = $fhirResponseOb->getStatus();
        // todo add author and other meta
        $r_content = $this->jsonSerialize($fhirResponseOb);

        $bind = array(
            $qr_uuid,
            $qr_id,
            $q_record_id,
            $q_id,
            $q_title,
            $_SESSION['authUserID'],
            $pid,
            $encounter,
            1,
            $r_status ?: 'in-progress',
            $q_content,
            $r_content,
            $form_response
        );

        $sql_insert = "INSERT INTO `questionnaire_response` (`uuid`, `response_id`, `questionnaire_foreign_id`, `questionnaire_id`, `questionnaire_name`, `audit_user_id`, `creator_user_id`, `create_time`, `last_updated`, `patient_id`,`encounter`, `version`, `status`, `questionnaire`, `questionnaire_response`, `form_response`, `form_score`, `tscore`, `error`) VALUES (?, ?, ?, ?, ?, NULL, ?, current_timestamp(), current_timestamp(), ?, ?, ?, ?, ?, ?, ?, NULL, NULL, NULL)";

        $sql_update = "UPDATE `questionnaire_response` SET `audit_user_id` = ?,`version` = ?, `last_updated` = ?, `status` = ?, `questionnaire_response` = ?, `form_response` = ?, `form_score` = ?, `tscore`= ?, `error` = ? WHERE `id` = ?";

        if ($update_flag) {
            $version_update = (int)$id['version'] + 1;
            $bind = array(
                $_SESSION['authUserID'],
                $version_update,
                date("Y-m-d H:i:s"),
                $r_status ?: 'in-progress',
                $r_content,
                $form_response, null, null, null,
                $id['id']
            );
            $result = sqlQuery($sql_update, $bind);
            $id = $id['id'];
        } else {
            $id = sqlInsert($sql_insert, $bind) ?: 0;
        }

        return ['id' => $id, 'response_id' => $qr_id, 'new' => !$update_flag];
    }

    /**
     * @param        $items
     * @param string $delimiter
     * @param string $prepend
     * @return array
     */
    public function flattenQuestionnaireResponse($items, string $delimiter = '.', string $prepend = ''): array
    {
        $flatArray = [];
        if (empty($items)) {
            return [];
        }
        foreach ($items as $key => $value) {
            if (is_array($value) && $value !== []) {
                if ($key === 'answer') {
                    $flatArray[] = [$prepend . $key => $this->setAnswer($value, true)];
                    continue;
                }
                $flatArray[] = $this->flattenQuestionnaireResponse($value, $delimiter, $prepend . $key . $delimiter);
            } else {
                if ($key === 'text' && isset($items['answer'])) {
                    $key = 'question';
                }
                $flatArray[] = [$prepend . $key => $value];
            }
        }

        if (count($flatArray ?? []) === 0) {
            return [];
        }

        return array_merge_recursive([], ...$flatArray);
    }

    /**
     * @param $source array - flattened
     * @param $delimiter
     * @return string
     */
    public function buildQuestionnaireResponseHtml($source, $delimiter = '|'): string
    {
        $html = <<<head
        <div style="display: flex;flex-direction: column;flex-basis: 100%;">
        <form>
        head;
        $title = true;
        foreach ($source as $k => $value) {
            $v = explode($delimiter, $k);
            $last = count($v ?? []) - 1;
            $item = $v[$last];
            $margin_count = max(substr_count($k, 'item') - 1, 0);
            $margin = attr($margin_count * 1.5 . 'rem');
            if ($item === 'text') {
                if ($title) {
                    $html .= "<h4>" . text($value) . "</h4>\n";
                    $title = false;
                } else {
                    $html .= "<div style='width:100%;margin:0 0;padding:0 0;'>\n<h5 style='margin:0.25rem auto 0.25rem $margin;'>" . text($value) . "</h5></div>\n";
                }
            }
            if ($item === 'question') {
                $html .= "<div style='margin: 0 auto 0;'>\n<label style='margin: 0 auto 0 $margin;'><strong>" . text($value) . ":</strong></label>\n";
            }
            if ($item === 'answer') {
                if (is_array($value ?? null)) {
                    if ($value['unit'] ?? null) {
                        $value = $value['value'] . ' ' . $value['unit'];
                    } else {
                        $v = '';
                        foreach ($value as $a) {
                            $v .= $a . ' ';
                        }
                        $value = trim($v);
                    }
                }
                $html .= "<span style='margin: 0 auto 0 0.5rem;'>" . text($value) . "</span></div>\n";
            }
        }
        $html .= <<<foot
        </form>
        </div>
        foot;

        return $html;
    }

    /**
     * @param      $name
     * @param null $q_id
     * @param null $uuid
     * @return array
     */
    public function getQuestionnaireResourceIdAndVersion($name, $q_id = null, $uuid = null): array
    {
        $sql = "Select `id`, `uuid`, response_id, `version` From `questionnaire_response` Where ((`questionnaire_name` IS NOT NULL And `questionnaire_name` = ?) Or (`response_id` IS NOT NULL And `response_id` = ?))";
        $bind = array($name, $q_id);
        if (!empty($uuid)) {
            $sql = "Select `id`, `uuid`, response_id, `version` From `questionnaire_response` Where `uuid` = ?";
            $bind = array($uuid);
        }
        $response = sqlQuery($sql, $bind) ?: [];
        if (is_array($response) && !empty($response['uuid'] ?? null)) {
            $response['uuid'] = UuidRegistry::uuidToString($response['uuid']);
        }
        return $response;
    }

    /**
     * @param $id
     * @param $uuid
     * @return array
     */
    public function fetchQuestionnaireResponseById($id, $qr_id, $uuid = null): array
    {
        $id = $id ?: 0;
        if (!empty($uuid)) {
            $sql = "Select * From `questionnaire_response` Where `uuid` = ?";
            $bind = array($uuid);
        } else {
            $sql = "Select * From `questionnaire_response` Where (`id` = ?) Or (`response_id` IS NOT NULL And `response_id` = ?)";
            $bind = array($id, $qr_id);
        }
        $response = sqlQuery($sql, $bind) ?: [];
        if (is_array($response) && !empty($response['uuid'])) {
            $response['uuid'] = UuidRegistry::uuidToString($response['uuid']);
        }

        return $response;
    }

    /**
     * @param $pid
     * @param $id
     * @param $name
     * @param $q_id
     * @return array
     */
    public function fetchQuestionnaireResponse($record_id = null, $qr_id = null): array
    {
        $sql = "Select * From `questionnaire_response` Where `id` = ? Or `response_id` = ?";
        $resource = sqlQuery($sql, array($record_id, $qr_id));

        return $resource ?: [];
    }
}
