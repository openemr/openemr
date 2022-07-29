<?php

namespace OpenEMR\Services;

class QuestionnaireService extends BaseService
{
    public const TABLE_NAME = 'questionnaire_repository';

    /**
     * Default constructor.
     */
    public function __construct($base_table = null)
    {
        parent::__construct($base_table ?? self::TABLE_NAME);
    }

    public function fetchQuestionnaireResource($name, $q_id = null, $uuid = null)
    {
        $sql = "Select * From questionnaire_repository Where (name IS NOT NULL And name = ?) Or (questionnaire_id IS NOT NULL And questionnaire_id = ?)";
        $bind =  array($name, $q_id);
        if (!empty($uuid)) {
            $sql = "Select * From questionnaire_repository Where uuid = ?";
            $bind =  array($uuid);
        }

        $resource = sqlQuery($sql, $bind);
        return $resource ?: [];
    }

    public function getQuestionnaireIdAndVersion($name, $q_id = null, $uuid = null): bool|array
    {
        $sql = "Select id, version From questionnaire_repository Where (name IS NOT NULL And name = ?) Or (questionnaire_id IS NOT NULL And questionnaire_id = ?)";
        $bind =  array($name, $q_id);
        if (!empty($uuid)) {
            $sql = "Select id From questionnaire_repository Where uuid = ?";
            $bind =  array($uuid);
        }

        return sqlQuery($sql, $bind) ?: [];
    }

    public function fetchQuestionnaireResponses($pid, $id = null, $name = null, $q_id = null): bool|array
    {
        $sql = "Select * From questionnaire_response Where patient_id = ? And (name = ? Or questionnaire_id = ?)";
        $resource = sqlStatement($sql, array($pid, $name, $q_id));
        while ($row = sqlFetchArray($resource)) {
            $resource[] = $row;
        }
        return $resource ?: [];
    }

    public function saveQuestionnaireResource($q, $name = null, $q_id = null)
    {
        $q_version = null;
        $q_last_date = null;
        $q_profile = null;
        $q_status = null;
        $content = null;
        $q_ob = null;
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
        if (is_array($q)) {
            $q_ob = $q;
            $q_id = $q_id ?? ($q_ob['id'] ?? null);
            $q_version = $q_ob['meta']['versionId'] ?? 1;
            $q_last_date = $q_ob['meta']['lastUpdated'] ?? null;
            $name = $name ?? ($q_ob['title'] ?? null);
            $q_profile = $q_ob['meta']['profile'][0] ?? null;
            $q_status = $q_ob['status'] ?? null;
            $q_code =  $q_ob['code'][0]->code ?? null;
            $q_display =  $q_ob['code'][0]->display ?? null;
        } else {
            return false;
        }
        $content = json_encode($q_ob);
        $bind = array(
            $q_id,
            $_SESSION['authUserID'],
            $q_version,
            $q_last_date,
            $name,
            $q_profile,
            $q_status,
            $q_code,
            $q_display,
            $content,
        );

        $sql_insert = "INSERT INTO `questionnaire_repository` (`id`, `uuid`, `questionnaire_id`, `provider`, `version`, `created_date`, `modified_date`, `name`, `type`, `profile`, `active`, `status`, `source_url`, `code`, `code_display`, `questionnaire`, `form_js`) VALUES (NULL, NULL, ?, ?, ?, current_timestamp(), ?, ?, 'Questionnaire', ?, '1', ?, NULL, ?, ?, ?, NULL)";

        $sql_update = "UPDATE `questionnaire_repository` SET `questionnaire_id` = ?, `provider` = ?,`version` = ?, `modified_date` = ?, `name` = ?, `profile` = ?, `status` = ?, `code` = ?, `code_display` = ?, `questionnaire` = ? WHERE `questionnaire_repository`.`id` = ?";

        $id = $this->getQuestionnaireIdAndVersion($name, $q_id);

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
                $q_code,
                $q_display,
                $content,
                $id['id']
            );
            $result = sqlQuery($sql_update, $bind);
            $id = $result ?: $id['id'];
        } else {
            $id = sqlInsert($sql_insert, $bind) ?: 0;
        }

        return $id;
    }

    public function saveQuestionnaireResponse($name, $q, $q_id = null)
    {
        return [];
    }
}
