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

class QuestionnaireService extends BaseService
{
    use QuestionnaireTraits;

    public const TABLE_NAME = 'questionnaire_repository';

    /**
     * Default constructor.
     */
    public function __construct($base_table = null)
    {
        parent::__construct($base_table ?? self::TABLE_NAME);
    }

    /**
     * @param $name
     * @param $q_id
     * @param $uuid
     * @param $type
     * @return array
     */
    public function getQuestionnaireIdAndVersion($name, $q_id = null, $uuid = null, $type = 'Questionnaire'): array
    {
        $sql = "Select `id`, `uuid`, `version` From `questionnaire_repository` Where ((`name` IS NOT NULL And `name` = ?) Or (`questionnaire_id` IS NOT NULL And `questionnaire_id` = ?)) And `type` = ?";
        $bind = array($name, $q_id, $type);
        if (!empty($uuid)) {
            $sql = "Select `id`, `uuid`, `version` From `questionnaire_repository` Where `uuid` = ?";
            $bind = array($uuid);
        }
        $response = sqlQuery($sql, $bind) ?: [];
        if (is_array($response) && !empty($response['uuid'] ?? null)) {
            $response['uuid'] = UuidRegistry::uuidToString($response['uuid']);
        }
        return $response;
    }

    /**
     * @param $name
     * @param $q_id
     * @param $uuid
     * @return array
     */
    public function fetchQuestionnaireResource($name, $q_id = null, $uuid = null)
    {
        $sql = "Select * From `questionnaire_repository` Where (`name` IS NOT NULL And `name` = ?) Or (`questionnaire_id` IS NOT NULL And `questionnaire_id` = ?)";
        $bind = array($name, $q_id);
        if (!empty($uuid)) {
            $sql = "Select * From questionnaire_repository Where uuid = ?";
            $bind = array($uuid);
        }
        $response = sqlQuery($sql, $bind) ?: [];
        if (is_array($response) && !empty($response['uuid'])) {
            $response['uuid'] = UuidRegistry::uuidToString($response['uuid']);
        }
        return $response;
    }

    /**
     * @param $q
     * @param $lform
     * @param $name
     * @param $q_id
     * @param $type
     * @return false|int|mixed
     */
    public function saveQuestionnaireResource($q, $lform = null, $name = null, $q_id = null, $type = null)
    {
        $type = $type ?? 'Questionnaire';

        if (is_string($q)) {
            $q_ob = json_decode($q, true);
            $is_json = json_last_error() === JSON_ERROR_NONE;
            if (!$is_json) {
                return false;
            }
            $q = $q_ob;
        } else {
            // in case an object, cast it to array
            if (is_object($q)) {
                $q = (array)$q;
            }
        }
        $q_uuid = (new UuidRegistry(['table_name' => 'questionnaire_repository']))->createUuid();
        if (is_array($q)) {
            $q_ob = $q;
            $q_id = $q_id ?? ($q_ob['id'] ?? null);
            $q_version = $q_ob['meta']['versionId'] ?? 1;
            $q_last_date = $q_ob['meta']['lastUpdated'] ?? null;
            $name = $name ?? ($q_ob['title'] ?? null);
            $q_profile = $q_ob['meta']['profile'][0] ?? null;
            $q_status = $q_ob['status'] == 'draft' ? 'active' : $q_ob['status'];
            $q_code = $q_ob["code"][0]['code'] ?? null;
            $q_display = $q_ob['code'][0]['display'] ?? null;
        } else {
            return false;
        }
        $content = json_encode($q_ob);
        $bind = array(
            $q_uuid,
            $q_id,
            $_SESSION['authUserID'],
            $q_version,
            $q_last_date,
            $name,
            $type,
            $q_profile,
            $q_status,
            $q_code,
            $q_display,
            $content,
            $lform
        );

        $sql_insert = "INSERT INTO `questionnaire_repository` (`id`, `uuid`, `questionnaire_id`, `provider`, `version`, `created_date`, `modified_date`, `name`, `type`, `profile`, `active`, `status`, `source_url`, `code`, `code_display`, `questionnaire`, `lform`) VALUES (NULL, ?, ?, ?, ?, current_timestamp(), ?, ?, ?, ?, '1', ?, NULL, ?, ?, ?, ?)";
        $sql_update = "UPDATE `questionnaire_repository` SET `questionnaire_id` = ?, `provider` = ?,`version` = ?, `modified_date` = ?, `name` = ?, `type` = ?, `profile` = ?, `status` = ?, `code` = ?, `code_display` = ?, `questionnaire` = ?, `lform` = ? WHERE `questionnaire_repository`.`id` = ?";

        $id = $this->getQuestionnaireIdAndVersion($name, $q_id, null, $type);

        if (!empty($id)) {
            $version_update = (int)$id['version'] + 1;
            $bind = array(
                $q_id,
                $_SESSION['authUserID'],
                $version_update,
                date("Y-m-d H:i:s"),
                $name,
                $type,
                $q_profile,
                $q_status,
                $q_code,
                $q_display,
                $content,
                $lform,
                $id['id']
            );
            $result = sqlInsert($sql_update, $bind);
            $id = $id['id'];
        } else {
            $id = sqlInsert($sql_insert, $bind) ?: 0;
        }

        return $id;
    }

    /**
     * @param $name
     * @return array
     */
    public function fetchEncounterQuestionnaireForm($name): array
    {
        $sql = "Select `form_foreign_id` From `registry` Where `name` = ?";
        $q_id = sqlQuery($sql, array($name))['form_foreign_id'];

        return $this->fetchQuestionnaireById($q_id) ?: [];
    }

    /**
     * @param $id
     * @param $uuid
     * @return array
     */
    public function fetchQuestionnaireById($id, $uuid = null): array
    {
        $sql = "Select * From `questionnaire_repository` Where (`id` = ?) Or (`uuid` IS NOT NULL And `uuid` = ?)";
        $bind = array($id, $uuid);
        if (!empty($uuid)) {
            $sql = "Select * From questionnaire_repository Where uuid = ?";
            $bind = array($uuid);
        }
        $response = sqlQuery($sql, $bind) ?: [];
        if (is_array($response) && !empty($response['uuid'])) {
            $response['uuid'] = UuidRegistry::uuidToString($response['uuid']);
        }
        return $response;
    }

    /**
     * @param $questionnaire
     * @return array
     * @throws Exception
     */
    function createQuestionnaireFormDictionary($questionnaire): array
    {
        $q = $this->parse($questionnaire);

        if ($q->get_fhirElementName() !== 'Questionnaire') {
            throw new Exception(xlt("Wrong type require Questionnaire"));
        }
        $forms = [];
        $formDisplayNames = [];
        $repeatingFormNames = [];
        $this->walkQuestionnaireForms(
            $q,
            function ($parents, $item) use (&$forms, &$formDisplayNames, &$repeatingFormNames, &$repeatingFormPaths, &$formDisplayPaths) {
                $fieldName = $this->getFieldName($item);
                $parent = end($parents);
                $instrumentName = $this->getInstrumentName($parent, $item);
                $path = $this->getQuestionnairePath($parents, $instrumentName);
                $form = &$forms[$path];
                $linkPath = $this->getInstrumentPath($parent, $item);
                if (empty($form)) {
                    if ($formDisplayNames[$instrumentName]) {
                        throw new Exception("Two forms exist with the following name: $instrumentName");
                    }
                    $formDisplayNames[$instrumentName] = $this->getInstrumentName($parent, $item, true);
                    $formDisplayPaths[$instrumentName]['path'] = $linkPath;
                    $form = [
                        'fields' => [],
                        'formName' => $instrumentName
                    ];
                    $forms[$path] = &$form;
                    if ($this->isRepeating($parent)) {
                        $c = $this->checkForNestedRepeatingGroups($parents);
                        $repeatingFormNames[] = $instrumentName; //for response parsing;
                        $repeatingFormPaths[] = ['name' => $instrumentName, 'path' => $linkPath, 'depth' => $c];
                    }
                }
                $form['fields'][$fieldName] = [
                    'type' => $this->getType($item),
                    'label' => $this->getText($item),
                    'choices' => $this->getAnswerChoices($item)
                ];
            }
        );

        $out = fopen('php://memory', 'r+');
        $firstForm = reset($forms);
        // write form keys
        fputcsv($out, ["field_path", "form_name", "section_header", "field_type", "field_label", "field_choices"]);
        // currently unsure if a response id is needed, so allowing for future use.
        fputcsv($out, ['response_id', $firstForm['formName'], '', 'text', '0', '']);
        // write out each form field with appropriate form info.
        foreach ($forms as $form) {
            foreach ($form['fields'] as $name => $field) {
                fputcsv($out, [$name, $form['formName'], 'reserved', $field['type'], $field['label'], $field['choices']]);
            }
        }
        rewind($out);
        // build an array from csv dictionary
        $keys = fgetcsv($out, 1000);
        $formDictionary = null;
        while (($d = fgetcsv($out, 1000)) !== false) {
            $formDictionary[] = array_combine($keys, $d);
        }
        // grab our dictionary
        //rewind($out);
        //$csv = stream_get_contents($out);
        //$form_out['csv_dictionary'] = $csv; // Just not sure if we'll need, but I may store anyway.
        fclose($out);
        $form_out['form_display']['labels'] = $formDisplayNames;
        $form_out['form_display']['paths'] = $formDisplayPaths;
        $form_out['repeating_forms']['labels'] = $repeatingFormNames;
        $form_out['repeating_forms']['paths'] = $repeatingFormPaths;
        $form_out['form_dictionary'] = $formDictionary;
        // all done. return represents the questionnaire forms(form can be a section).
        return $form_out;
    }

    /**
     * @param $parents
     * @param $fieldAction
     * @return void
     * @throws Exception
     */
    private function walkQuestionnaireForms($parents, $fieldAction): void
    {
        if (!is_array($parents)) {
            if ($parents->get_fhirElementName() === 'Questionnaire') {
                // Expected on first call.
                $parents = [$parents];
            } else {
                throw new Exception(xlt("An array of parent resources was expected."));
            }
        }

        $group = end($parents);
        foreach ($group->getItem() as $item) {
            $type = $this->getValue($item->getType());
            if (in_array($type, ['group', 'display'])) {
                $newParents = $parents;
                $newParents[] = $item;
                $this->walkQuestionnaireForms($newParents, $fieldAction);
            } else {
                $fieldAction($parents, $item);
            }
        }
    }

    /**
     * @param $parents
     * @param $formName
     * @return string
     */
    private function getQuestionnairePath($parents, $formName): string
    {
        $parts = [];
        foreach ($parents as $parent) {
            $parts[] = $this->getInstrumentName($parent);
        }
        $parts[] = $formName;

        return implode('/', $parts);
    }

    /**
     * @param $groups
     * @return int
     */
    private function checkForNestedRepeatingGroups($groups): int
    {
        $repeatingCount = 0;
        foreach ($groups as $group) {
            if ($this->isRepeating($group)) {
                $repeatingCount++;
            }
        }

        return $repeatingCount;
    }

    /**
     * @param $item
     * @return string|null
     */
    private function getAnswerChoices($item): ?string
    {
        if (empty($item->getAnswerOption())) {
            return null;
        }
        $choices = [];
        foreach ($this->getQuestionnaireAnswers($item) as $code => $display) {
            $choices[] = "$code, $display";
        }

        return implode('|', $choices);
    }

    /**
     * @param $item
     * @return array
     */
    private function getQuestionnaireAnswers($item): array
    {
        // TODO add enableWhen properties to results
        $answers = [];
        foreach ($item->getAnswerOption() as $option) {
            $coding = $option->getValueCoding();
            $code = $this->getValue($coding->getCode());
            $display = $this->getValue($coding->getDisplay());

            $answers[$code] = $display;
        }

        return $answers;
    }
}
