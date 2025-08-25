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
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Events\Services\ServiceSaveEvent;
use OpenEMR\FHIR\Config\ServerConfig;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRQuestionnaire;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRQuestionnaireResponse;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRNarrative;
use OpenEMR\FHIR\R4\FHIRElement\FHIRNarrativeStatus;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\FHIR\R4\FHIRElement\FHIRString;
use OpenEMR\Services\Search\FhirSearchWhereClauseBuilder;
use OpenEMR\Validators\ProcessingResult;

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

        $parseItems = function ($parent) use (&$parseItems, &$fieldNames, &$data, &$instanceCount, $repeatingForms): void {
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
     * @return string[]
     */
    public function getUuidFields(): array
    {
        return ['questionnaire_response_uuid', 'encounter_uuid', 'puuid'];
    }

    /**
     * @param $search
     * @param $isAndCondition
     * @return ProcessingResult
     */
    public function search($search, $isAndCondition = true)
    {
        $sqlSelectIds = "SELECT DISTINCT qr.questionnaire_response_uuid ";
        $sqlSelectData = " SELECT qr.*
        ,fe.encounter_uuid
        ,pd.puuid ";
        $sql = "FROM (
                SELECT
                    uuid AS questionnaire_response_uuid
                    ,id AS id
                    ,response_id
                    ,questionnaire_foreign_id
                    ,questionnaire_id
                    ,questionnaire_name
                    ,patient_id
                    ,encounter
                    ,audit_user_id
                    ,creator_user_id
                    ,create_time
                    ,last_updated
                    ,version
                    ,status
                    ,questionnaire
                    ,questionnaire_response
                    ,form_response
                    ,form_score
                    ,tscore
                    ,error
                FROM questionnaire_response
             ) qr "
            . " LEFT JOIN form_questionnaire_assessments fqa ON fqa.response_id = qr.response_id " // TODO: @adunsulag is this field indexed?
            . " LEFT JOIN (SELECT uuid AS questionnaire_uuid,id AS q_repo_id FROM questionnaire_repository)  q_repo ON qr.questionnaire_foreign_id = q_repo.q_repo_id "
            . " LEFT JOIN forms f ON fqa.id = f.form_id AND f.formdir='questionnaire_assessments' "
            . " LEFT JOIN (
                    SELECT
                        uuid AS encounter_uuid
                        ,encounter
                    FROM form_encounter
             ) fe ON f.encounter = fe.encounter "
            . " LEFT JOIN (
                    SELECT
                        uuid AS puuid
                        ,pid
                    FROM patient_data
             ) pd ON qr.patient_id = pd.pid "
            // we only grab users that are actual Practitioners with a valid NPI number
            . " LEFT JOIN users ON qr.creator_user_id = users.id AND users.username IS NOT NULL and users.npi IS NOT NULL AND users.npi != ''";

        $whereUuidClause = FhirSearchWhereClauseBuilder::build($search, $isAndCondition);
        $sqlUuids = $sqlSelectIds . " " . $sql . " " . $whereUuidClause->getFragment();
        $uuidResults = QueryUtils::fetchTableColumn($sqlUuids, 'questionnaire_response_uuid', $whereUuidClause->getBoundValues());

        if (!empty($uuidResults)) {
            // now we are going to run through this again and grab all of our data w only the uuid search as our filter
            // this makes sure we grab the entire patient record and associated data
            $whereClause = " WHERE qr.questionnaire_response_uuid IN (" . implode(",", array_map(function ($uuid) {
                    return "?";
            }, $uuidResults)) . ") ORDER BY qr.create_time DESC ";
            $statementResults = QueryUtils::sqlStatementThrowException($sqlSelectData . $sql . $whereClause, $uuidResults);
            $processingResult = new ProcessingResult();
            foreach ($statementResults as $record) {
                $processingResult->addData($this->createResultRecordFromDatabaseResult($record));
            }
            return $processingResult;
        } else {
            return new ProcessingResult();
        }
    }

    /**
     * @param       $response
     * @param       $pid
     * @param ?int  $encounter
     * @param ?string  $qr_id
     * @param ?int  $qr_record_id
     * @param string|array|null  $q
     * @param ?string  $q_id
     * @param ?string  $form_response
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
        $q_id = $q_id ?: $this->getValue($fhirQuestionnaireOb->id);
        $q_title = $this->getValue($fhirQuestionnaireOb->title);
        $q_name = $this->getValue($fhirQuestionnaireOb->name);
        if (empty($q_title)) {
            $q_title = $q_name;
        }
        $q_record_id = $this->questionnaireService->getQuestionnaireIdAndVersion($q_title, $q_id)['id'] ?? null;

        if ($add_report) {
            $response_array = $this->fhirObjectToArray($fhirResponseOb);
            $answers = $this->flattenQuestionnaireResponse($response_array, '|', '');
            $html = $this->buildQuestionnaireResponseHtml($answers);
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
            $qr_uuid = null;
            $update_flag = true;
        }
        $id = [];
        if ($update_flag) {
            $id = $this->getQuestionnaireResourceIdAndVersion(null, $qr_id, null);
            if (empty($id)) {
                $update_flag = false;
            }
        }

        $fhirResponseOb->setId(new FHIRId($qr_id));
        $serverConfig = new ServerConfig();
        $fhirResponseOb->setQuestionnaire(new FHIRCanonical($serverConfig->getFhirUrl() . '/Questionnaire/' . $q_id));
        if (is_numeric($encounter)) {
            $encounter_uuid = $this::getUuidById($encounter, 'form_encounter', 'encounter');
            if (empty($encounter_uuid)) {
                $encounter = 0;
            } else {
                $encounter = UuidRegistry::uuidToString($encounter_uuid) ?: $encounter;
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

        $dataValues = [
            'uuid' => $qr_uuid
            , 'response_id' => $qr_id
            , 'questionnaire_foreign_id' => $q_record_id
            , 'questionnaire_id' => $q_id
            , 'questionnaire_name' => $q_title
            // if its created by a patient we won't have an authUserID
            , 'audit_user_id' => $update_flag ? ($_SESSION['authUserID'] ?? null) : null
            , 'creator_user_id' => $_SESSION['authUserID'] ?? null
            , 'version' => $update_flag ? (int)$id['version'] + 1 : 1
            , 'last_updated' => date("Y-m-d H:i:s")
            , 'patient_id' => $pid
            , 'encounter' => $encounter
            , 'status' => $r_status ?: 'in-progress'
            , 'questionnaire' => $q_content
            , 'questionnaire_response' => $r_content
            , 'form_response' => $form_response
            , 'form_score' => null
            , 'tscore' => null
            , 'error' => null
            , 'id' => $id['id'] ?? null
            , 'isNew' => !$update_flag
        ];


        $sql_insert = "INSERT INTO `questionnaire_response` (`uuid`, `response_id`, `questionnaire_foreign_id`, `questionnaire_id`, `questionnaire_name`, `audit_user_id`, `creator_user_id`, `create_time`, `last_updated`, `patient_id`,`encounter`, `version`, `status`, `questionnaire`, `questionnaire_response`, `form_response`, `form_score`, `tscore`, `error`) VALUES (?, ?, ?, ?, ?, NULL, ?, current_timestamp(), current_timestamp(), ?, ?, ?, ?, ?, ?, ?, NULL, NULL, NULL)";

        $sql_update = "UPDATE `questionnaire_response` SET `audit_user_id` = ?,`version` = ?, `last_updated` = ?, `status` = ?, `questionnaire_response` = ?, `form_response` = ?, `form_score` = ?, `tscore`= ?, `error` = ? WHERE `id` = ?";

        $preSaveEvent = new ServiceSaveEvent($this, $dataValues);
        $updatedPreSaveEvent = $this->getEventDispatcher()->dispatch($preSaveEvent, ServiceSaveEvent::EVENT_PRE_SAVE);
        if (!$updatedPreSaveEvent instanceof ServiceSaveEvent) {
            $this->getLogger()->error(self::class . "->saveQuestionnaireResponse() failed ot receive valid class for " . ServiceSaveEvent::class);
        }
        $dataValues = $updatedPreSaveEvent->getSaveData();

        if ($update_flag) {
            $bind = array(
                $dataValues['audit_user_id']
            , $dataValues['version']
            , $dataValues['last_updated']
            , $dataValues['status']
            , $dataValues['questionnaire_response']
            , $dataValues['form_response']
            , $dataValues['form_score']
            , $dataValues['tscore']
            , $dataValues['error']
            , $dataValues['id']
            );
            $result = sqlQuery($sql_update, $bind);
            $id = $id['id'];
        } else {
            $bind = array(
                $dataValues['uuid'],
                $dataValues['response_id'],
                $dataValues['questionnaire_foreign_id'],
                $dataValues['questionnaire_id'],
                $dataValues['questionnaire_name'],
                $dataValues['creator_user_id'],
                $dataValues['patient_id'],
                $dataValues['encounter'],
                $dataValues['version'],
                $dataValues['status'],
                $dataValues['questionnaire'],
                $dataValues['questionnaire_response'],
                $dataValues['form_response']
            );
            $id = sqlInsert($sql_insert, $bind) ?: 0;
            $dataValues['id'] = $id;
        }
        $postSaveEvent = new ServiceSaveEvent($this, $dataValues);
        $updatedPostSaveEvent = $this->getEventDispatcher()->dispatch($postSaveEvent, ServiceSaveEvent::EVENT_POST_SAVE);
        if (!$updatedPostSaveEvent instanceof ServiceSaveEvent) {
            $this->getLogger()->error(self::class . "->saveQuestionnaireResponse() failed to receive valid class for " . ServiceSaveEvent::class);
        }

        return ['id' => $id, 'response_id' => $qr_id, 'new' => !$update_flag];
    }

    /**
     * @param        $items
     * @param string $delimiter
     * @param string $prepend
     * @return array
     */
    public function flattenQuestionnaireResponsePairs($items, string $delimiter = '.', string $prepend = ''): array
    {
        $flatArray = [];

        foreach ($items as $key => $value) {
            // If the current item is an array, recursively process it
            if (is_array($value)) {
                if ($key === 'answer') {
                    // Extract answer value
                    $flatArray[] = ['answer' => $this->setAnswer($value, true)];
                } elseif ($key === 'item' || $key === 'linkId') {
                    // Recursively process the items or linkId
                    $flatArray = array_merge($flatArray, $this->flattenQuestionnaireResponse($value, $delimiter, $prepend));
                } else {
                    // Handle other nested arrays
                    $flatArray = array_merge($flatArray, $this->flattenQuestionnaireResponse($value, $delimiter, $prepend . $key . $delimiter));
                }
            } else {
                // Handle simple key-value pairs
                if ($key === 'text' && isset($items['answer'])) {
                    $flatArray[] = ['question' => $value];
                } elseif ($key !== 'linkId') {
                    // Exclude 'linkId' from the flat array
                    $flatArray[] = [$key => $value];
                }
            }
        }
        // Merge all elements into a flat array
        return $flatArray;
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
     * @param array $flattenedArray
     * @return array
     */
    function groupByItemsRecursively(array $flattenedArray): array
    {
        $grouped = [];

        foreach ($flattenedArray as $key => $value) {
            // Split the key by the delimiter to handle nesting
            $keys = explode('|', $key);
            $current = &$grouped;

            // Traverse through the key segments and build the nested structure
            foreach ($keys as $segment) {
                // If the segment is numeric, treat it as an array index
                if (is_numeric($segment)) {
                    $segment = (int)$segment;
                    if (!isset($current[$segment])) {
                        $current[$segment] = [];
                    }
                    $current = &$current[$segment];
                } else {
                    if (!isset($current[$segment])) {
                        $current[$segment] = [];
                    }
                    $current = &$current[$segment];
                }
            }

            // Assign the value to the final key segment
            $current = $value;
        }

        return $grouped;
    }

    /**
     * @param $response
     * @return string
     */
    function buildQuestionnaireResponseHtml($response): string
    {
        $html = '<div style="display: flex; flex-direction: column; flex-basis: 100%; line-height: 1.2 !important">';
        $html .= '<form class="form">';

        // Iterate over each top-level item (section)
        if (isset($response['item']) && is_array($response['item'])) {
            foreach ($response['item'] as $section) {
                $html .= $this->renderItem($section, 0, 1);
            }
        }

        $html .= '</form>';
        $html .= '</div>';

        return $html;
    }

    /**
     * @param $item
     * @param $indentLevel
     * @param $flag
     * @return string
     */
    function renderItem($item, $indentLevel = 0, $flag = 0): string
    {
        $html = '';
        // Render item text if it exists
        if (isset($item['text'])) {
            $margin = str_repeat('&nbsp;', $indentLevel * 1); // Indent for nested items
            $html .= "<div style='margin-left: {$indentLevel}rem; margin-top: 0.1rem;'>\n";
            if ($flag === 1) {
                $html .= "<span style='font-size: 1.15rem; font-weight: 550;'>{$margin}" . text($item['text']) . "</span>\n";
            } else {
                $html .= "<span style='font-size: 1.1rem; font-weight: 505;''>{$margin}" . text($item['text']) . "</span>\n";
            }
        }
        // Render answer if it exists
        if (isset($item['answer']) && is_array($item['answer'])) {
            foreach ($item['answer'] as $answer) {
                $answerValue = $this->extractAnswerValue($answer);
                $html .= "<span style='font-size: 1.1rem; font-weight: 500;'>{$answerValue}</span>\n";
                // Recursively render any nested items within the answer
                if (isset($answer['item']) && is_array($answer['item'])) {
                    foreach ($answer['item'] as $subItem) {
                        $html .= $this->renderItem($subItem, $indentLevel + 1);
                    }
                }
            }
        }
        // Level 2 Recursively render any nested items within the current item. Add a 3rd
        if (isset($item['item']) && is_array($item['item'])) {
            foreach ($item['item'] as $subItem) {
                $html .= $this->renderItem($subItem, $indentLevel + 1);
            }
        }
        $html .= "</div>\n"; // Close the current item

        return $html;
    }

    /**
     * @param $answer
     * @return string
     */
    function extractAnswerValue($answer)
    {
        if (isset($answer['valueCoding'])) {
            return $answer['valueCoding']['display'];
        } elseif (isset($answer['valueDecimal'])) {
            return $answer['valueDecimal'];
        } elseif (isset($answer['valueString'])) {
            return $answer['valueString'];
        } elseif (isset($answer['valueBoolean'])) {
            return $answer['valueBoolean'] ? 'Yes' : 'No';
        } elseif (isset($answer['valueInteger'])) {
            return $answer['valueInteger'];
        } elseif (isset($answer['valueDate'])) {
            return $answer['valueDate'];
        } else {
            return 'N/A';
        }
    }


    /**
     * @param      $name
     * @param ?string $q_id
     * @param ?string $uuid
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

    /**
     * @param $qr_id
     * @return array
     */
    public function fetchQuestionnaireResponseByResponseId($qr_id)
    {
        return $this->fetchQuestionnaireResponse(null, $qr_id);
    }
}
