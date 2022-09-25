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
     * @param $source array - flattened
     * @param $delimiter
     * @return string
     */
    public function buildQuestionnaireResponseHtml($source, $delimiter = '|'): string
    {
        $html = <<<head
        <html>
        <body>
        <div class='container-fluid'>
        <form class='form'>
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
        </form>
        </div>
        </body>
        </html>
        foot;

        return $html;
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
     * @param $items
     * @param $delimiter
     * @param $prepend
     * @return array
     */
    public function flattenQuestionnaireResponse($items, $delimiter = '.', $prepend = ''): array
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

    /* WIP */
    /**
     * @param $pid
     * @param $foreign_id
     * @param $name
     * @param $q
     * @param $response
     * @param $form_response
     * @return array|false|int|mixed
     */
    public function saveQuestionnaireResponse($pid, $foreign_id, $name, $q, $response, $form_response = null)
    {
        if (is_string($q)) {
            $q_ob = json_decode($q, true);
            $is_json = json_last_error() === JSON_ERROR_NONE;
            if (!$is_json) {
                return false;
            }
            $q = $q_ob;
        } else {
            if (is_object($q)) {
                $q = (array)$q;
            }
        }

        if (is_string($response)) {
            $r_ob = json_decode($q, true);
            $is_json = json_last_error() === JSON_ERROR_NONE;
            if (!$is_json) {
                return false;
            }
            $r = $r_ob;
        } else {
            if (is_object($response)) {
                $r = (array)$response;
            }
        }
        $q_uuid = (new UuidRegistry(['table_name' => 'questionnaire_response']))->createUuid();
        if (is_array($q)) {
            $q_ob = $q;
            $q_id = $q_id ?? ($q_ob['id'] ?? null);
            $q_version = $q_ob['meta']['versionId'] ?? 1;
            $q_last_date = $q_ob['meta']['lastUpdated'] ?? null;
            $name = $name ?? ($q_ob['title'] ?? null);
            $q_profile = $q_ob['meta']['profile'][0] ?? null;
            $q_status = $q_ob['status'] ?? null;
        } else {
            return false;
        }
        $q_content = json_encode($q_ob);
        $r_content = json_encode($r_ob);
        $bind = array(
            $q_uuid,
            $q_id,
            $_SESSION['authUserID'],
            $q_version,
            $q_last_date,
            $name,
            $q_profile,
            $q_status,
            $q_content,
            $r_content,
        );

        $sql_insert = "INSERT INTO `questionnaire_response` (`uuid`, `questionnaire_foreign_id`, `questionnaire_id`, `questionnaire_name`, `audit_user_id`, `creator_user_id `, `create_time`, `last_updated`, `patient_id`, `version`, `status`, `questionnaire`, `questionnaire_response`, `form_response`, `form_score`, `tscore`, `error`) VALUES (?, ?, ?, ?, NULL, current_timestamp(), current_timestamp(), ?, ?, NULL, ?, ?, ?, ?, ?, ?)";

        $sql_update = "UPDATE `questionnaire_response` SET `audit_user_id` = ?,`version` = ?, `modified_date` = ?, `questionnaire_name` = ?, `status` = ?, `code` = ?, `code_display` = ?, `questionnaire` = ?, `questionnaire_response` = ?, `form_response` = ? WHERE `id` = ?";

        $id = $this->questionnaireService->getQuestionnaireIdAndVersion($name, $q_id);

        if (!empty($id)) {
            $version_update = (int)$id['version'] + 1;
            $bind = array(
                $q_id,
                $_SESSION['authUserID'],
                $version_update,
                date("Y-m-d H:i:s"),
                $name,
                $q_profile,
                $q_status,
                //$q_code,
                //$q_display,
                //$content,
                $id['id']
            );
            $result = sqlQuery($sql_update, $bind);
            $id = $result ?: $id['id'];
        } else {
            $id = sqlInsert($sql_insert, $bind) ?: 0;
        }

        return $id;
    }

    /**
     * @param $id
     * @param $uuid
     * @return array
     */
    public function fetchQuestionnaireResponseById($id, $uuid = null): array
    {
        $id = $id ?: 0;
        $sql = "Select * From `questionnaire_response` Where (`id` = ?) Or (`uuid` IS NOT NULL And `uuid` = ?)";
        $bind = array($id, $uuid);
        if (!empty($uuid)) {
            $sql = "Select * From `questionnaire_response` Where `uuid` = ?";
            $bind = array($uuid);
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
     * @return array|\recordset
     */
    public function fetchQuestionnaireResponses($pid, $id = null, $name = null, $q_id = null)
    {
        $sql = "Select * From `questionnaire_response` Where `patient_id` = ? And (`name` = ? Or `questionnaire_id` = ?)";
        $resource = sqlStatement($sql, array($pid, $name, $q_id));
        while ($row = sqlFetchArray($resource)) {
            $resource[] = $row;
        }

        return $resource ?: [];
    }
}
