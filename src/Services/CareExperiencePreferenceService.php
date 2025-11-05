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

    /**
     * Convert preference to FHIR Observation resource
     */
    public function convertToFHIR($id)
    {
        $pref = $this->getOne($id);
        if (empty($pref)) {
            return null;
        }

        $pref = $pref[0];

        // Get patient UUID (required for subject reference)
        $patientUuid = QueryUtils::querySingleRow("SELECT uuid FROM patient_data WHERE pid = ?", [$pref['patient_id']]);
        if (empty($patientUuid['uuid'])) {
            error_log("Cannot create FHIR Observation: Patient UUID not found for pid=" . $pref['patient_id']);
            return null;
        }

        // Get performer (user who recorded the preference)
        $performerUuid = null;
        $performerDisplay = null;
        if (!empty($pref['created_by'])) {
            $user = QueryUtils::querySingleRow("SELECT uuid, CONCAT(fname, ' ', lname) as name FROM users WHERE id = ?", [$pref['created_by']]);
            if (!empty($user)) {
                $performerUuid = $user['uuid'] ?? null;
                $performerDisplay = $user['name'] ?? null;
            }
        }

        // Build the observation resource
        $observation = [
            'resourceType' => 'Observation',
            'id' => $pref['uuid'], // Use database UUID, not integer ID
            'meta' => [
                'versionId' => '1',
                'lastUpdated' => date('c', strtotime($pref['updated_at'] ?? $pref['created_at'] ?? 'now')),
                'profile' => [
                    'http://hl7.org/fhir/us/core/StructureDefinition/us-core-care-experience-preference'
                ]
            ],
            'status' => $this->mapStatusToFHIR($pref['status']),
            'category' => [[
                'coding' => [[
                    'system' => 'http://hl7.org/fhir/us/core/CodeSystem/us-core-category',
                    'code' => 'care-experience-preference',
                    'display' => 'Care Experience Preference'
                ]],
                'text' => 'Care Experience Preference'
            ]],
            'code' => [
                'coding' => [[
                    'system' => 'http://loinc.org',
                    'code' => $pref['observation_code'],
                    'display' => $pref['observation_code_text']
                ]],
                'text' => $pref['observation_code_text']
            ],
            'subject' => [
                'reference' => 'Patient/' . $patientUuid['uuid'],
                'type' => 'Patient'
            ],
            'effectiveDateTime' => date('c', strtotime($pref['effective_datetime'])),
            'issued' => date('c', strtotime($pref['created_at'] ?? $pref['effective_datetime']))
        ];

        // Add performer if available
        if ($performerUuid && $performerDisplay) {
            $observation['performer'] = [[
                'reference' => 'Practitioner/' . $performerUuid,
                'type' => 'Practitioner',
                'display' => $performerDisplay
            ]];
        }

        // Add value based on type (one of these is required)
        $valueText = $this->addValueToObservation($observation, $pref);

        // Add note if present
        if (!empty($pref['note'])) {
            $observation['note'] = [[
                'text' => $pref['note']
            ]];
        }

        // Add text narrative (required by FHIR spec, validated by Inferno)
        $observation['text'] = $this->generateNarrative($pref, $valueText);

        return $observation;
    }

    /**
     * Map internal status codes to FHIR observation status codes
     * FHIR allows: registered | preliminary | final | amended | corrected | cancelled | entered-in-error | unknown
     */
    private function mapStatusToFHIR($status)
    {
        $statusMap = [
            'final' => 'final',
            'preliminary' => 'preliminary',
            'amended' => 'amended',
            'corrected' => 'corrected',
            'entered-in-error' => 'entered-in-error',
            'cancelled' => 'cancelled',
            'unknown' => 'unknown'
        ];

        return $statusMap[$status] ?? 'final';
    }

    /**
     * Add value[x] to observation and return text representation
     */
    private function addValueToObservation(&$observation, $pref)
    {
        $valueText = '';

        if ($pref['value_type'] == 'coded' && !empty($pref['value_code'])) {
            $observation['valueCodeableConcept'] = [
                'coding' => [[
                    'system' => $pref['value_code_system'] ?? 'http://loinc.org',
                    'code' => $pref['value_code'],
                    'display' => $pref['value_display']
                ]],
                'text' => $pref['value_display']
            ];
            $valueText = $pref['value_display'];
        } elseif ($pref['value_type'] == 'text' && !empty($pref['value_text'])) {
            $observation['valueString'] = $pref['value_text'];
            $valueText = $pref['value_text'];
        } elseif ($pref['value_type'] == 'boolean' && isset($pref['value_boolean'])) {
            $boolVal = (bool)$pref['value_boolean'];
            $observation['valueBoolean'] = $boolVal;
            $valueText = $boolVal ? 'Yes' : 'No';
        }

        return $valueText;
    }

    /**
     * Generate XHTML narrative for the observation
     * Required by FHIR spec and validated by Inferno
     */
    private function generateNarrative($pref, $valueText)
    {
        $status = ucfirst($pref['status'] ?? 'final');
        $code = htmlspecialchars($pref['observation_code_text'] ?? $pref['observation_code'], ENT_XML1, 'UTF-8');
        $value = htmlspecialchars($valueText, ENT_XML1, 'UTF-8');
        $date = date('F j, Y', strtotime($pref['effective_datetime']));

        $div = '<div xmlns="http://www.w3.org/1999/xhtml">';
        $div .= '<p><b>Care Experience Preference</b></p>';
        $div .= '<p><b>Status:</b> ' . $status . '</p>';
        $div .= '<p><b>Preference:</b> ' . $code . '</p>';
        $div .= '<p><b>Patient\'s Choice:</b> ' . $value . '</p>';
        $div .= '<p><b>Date:</b> ' . $date . '</p>';

        if (!empty($pref['note'])) {
            $note = htmlspecialchars($pref['note'], ENT_XML1, 'UTF-8');
            $div .= '<p><b>Note:</b> ' . $note . '</p>';
        }

        $div .= '</div>';

        return [
            'status' => 'generated',
            'div' => $div
        ];
    }
}
