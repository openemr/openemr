<?php

/**
 * Care Experience Preference Service
 *
 * @package   OpenEMR
 * @link      https://www.openemr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\BaseService;
use OpenEMR\Validators\ProcessingResult;

class CareExperiencePreferenceService extends BaseService
{
    const TABLE_NAME = 'patient_care_experience_preferences';
    const LIST_ID = 'care_experience_preferences';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME);
    }

    /**
     * Get all LOINC codes for care experience preferences from list_options
     */
    public function getAvailableLoincCodes()
    {
        $sql = "SELECT option_id as loinc_code, 
                       title as display_name,
                       notes as answer_list_id,
                       codes
                FROM list_options 
                WHERE list_id = ? AND activity = 1
                ORDER BY seq, title";

        return QueryUtils::fetchRecords($sql, [self::LIST_ID]);
    }

    /**
     * Get answer list for a specific LOINC code from list_options
     */
    public function getAnswerList($answerListId)
    {
        if (empty($answerListId)) {
            return [];
        }

        $sql = "SELECT option_id as answer_code,
                       title as answer_display,
                       codes as answer_system
                FROM list_options
                WHERE list_id = ? AND activity = 1
                ORDER BY seq";

        return QueryUtils::fetchRecords($sql, [$answerListId]);
    }

    /**
     * Get preferences by patient ID
     */
    public function getPreferencesByPatient($pid)
    {
        $sql = "SELECT * FROM " . escape_table_name(self::TABLE_NAME) . "
                WHERE patient_id = ? AND status != ?
                ORDER BY effective_datetime DESC";

        return QueryUtils::fetchRecords($sql, [$pid, 'entered-in-error']);
    }

    /**
     * Get preference by ID
     */
    public function getOne($id)
    {
        $sql = "SELECT * FROM " . escape_table_name(self::TABLE_NAME) . "
                WHERE id = ?";

        return QueryUtils::fetchRecords($sql, [$id]);
    }

    /**
     * Insert new preference
     */
    public function insert($data)
    {
        $data['uuid'] = UuidRegistry::getRegistryForTable(self::TABLE_NAME)->createUuid();
        $sql = "INSERT INTO " . escape_table_name(self::TABLE_NAME) . " 
                SET patient_id = ?,
                    uuid = ?,
                    observation_code = ?,
                    observation_code_text = ?,
                    value_type = ?,
                    value_code = ?,
                    value_code_system = ?,
                    value_display = ?,
                    value_text = ?,
                    value_boolean = ?,
                    effective_datetime = ?,
                    status = ?,
                    note = ?";

        $params = [
            $data['patient_id'],
            $data['uuid'],
            $data['observation_code'] ?? null,
            $data['observation_code_text'] ?? null,
            $data['value_type'] ?? 'coded',
            $data['value_code'] ?? null,
            $data['value_code_system'] ?? null,
            $data['value_display'] ?? null,
            $data['value_text'] ?? null,
            $data['value_boolean'] ?? null,
            $data['effective_datetime'] ?? date('Y-m-d H:i:s'),
            $data['status'] ?? 'final',
            $data['note'] ?? null
        ];

        return QueryUtils::sqlInsert($sql, $params);
    }

    /**
     * Update preference
     */
    public function update($id, $data)
    {
        $sql = "UPDATE " . escape_table_name(self::TABLE_NAME) . "
                SET observation_code = ?,
                    observation_code_text = ?,
                    value_type = ?,
                    value_code = ?,
                    value_code_system = ?,
                    value_display = ?,
                    value_text = ?,
                    value_boolean = ?,
                    effective_datetime = ?,
                    status = ?,
                    note = ?
                WHERE id = ?";

        $params = [
            $data['observation_code'] ?? null,
            $data['observation_code_text'] ?? null,
            $data['value_type'] ?? 'coded',
            $data['value_code'] ?? null,
            $data['value_code_system'] ?? null,
            $data['value_display'] ?? null,
            $data['value_text'] ?? null,
            $data['value_boolean'] ?? null,
            $data['effective_datetime'] ?? date('Y-m-d H:i:s'),
            $data['status'] ?? 'final',
            $data['note'] ?? null,
            $id
        ];

        return QueryUtils::sqlStatementThrowException($sql, $params);
    }

    /**
     * Delete preference (soft delete by setting status)
     */
    public function delete($id)
    {
        $sql = "UPDATE " . escape_table_name(self::TABLE_NAME) . "
                SET status = ?
                WHERE id = ?";

        return QueryUtils::sqlStatementThrowException($sql, ['entered-in-error', $id]);
    }
}
