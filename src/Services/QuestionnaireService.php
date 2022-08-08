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

use OpenEMR\Common\Uuid\UuidRegistry;

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

    public function getQuestionnaireIdAndVersion($name, $q_id = null, $uuid = null, $type = 'Questionnaire')
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

    public function fetchQuestionnaireResponses($pid, $id = null, $name = null, $q_id = null)
    {
        $sql = "Select * From `questionnaire_response` Where `patient_id` = ? And (`name` = ? Or `questionnaire_id` = ?)";
        $resource = sqlStatement($sql, array($pid, $name, $q_id));
        while ($row = sqlFetchArray($resource)) {
            $resource[] = $row;
        }
        return $resource ?: [];
    }

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

    /* WIP */
    public function saveQuestionnaireResponse($pid, $foreign_id, $name, $q, $response, $form_response = null)
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

    public function fetchEncounterQuestionnaireForm($name)
    {
        $sql = "Select `form_foreign_id` From `registry` Where `name` = ?";
        $q_id = sqlQuery($sql, array($name))['form_foreign_id'];
        return $this->fetchQuestionnaireById($q_id) ?: [];
    }
}
