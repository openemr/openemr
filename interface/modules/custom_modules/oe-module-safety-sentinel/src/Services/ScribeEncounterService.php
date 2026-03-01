<?php

/**
 * Safety Sentinel Scribe Encounter Service
 *
 * CRUD operations for the scribe_encounters table. Stores ambient AI scribe
 * encounter data — transcript, SOAP note JSON, and accepted billing codes.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ryo Iwata <ryo@example.com>
 * @copyright Copyright (c) 2026
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\SafetySentinel\Services;

use OpenEMR\Validators\ProcessingResult;

class ScribeEncounterService
{
    private const TABLE = 'scribe_encounters';

    /**
     * Create a new scribe encounter row.
     *
     * @param array $data Encounter payload from FastAPI SaveEncounterRequest.
     * @return ProcessingResult containing ['id' => int, 'status' => string].
     */
    public function create(array $data): ProcessingResult
    {
        $result = new ProcessingResult();

        $validationMessages = [];
        foreach (['patient_uuid', 'encounter_date', 'soap_note'] as $field) {
            if (empty($data[$field])) {
                $validationMessages[$field] = "Field '$field' is required";
            }
        }
        if (!empty($validationMessages)) {
            $result->setValidationMessages($validationMessages);
            return $result;
        }

        $soapJson    = is_array($data['soap_note'])    ? json_encode($data['soap_note'])    : $data['soap_note'];
        $icd10Json   = is_array($data['accepted_icd10_codes'] ?? [])  ? json_encode($data['accepted_icd10_codes'] ?? [])  : '[]';
        $cptJson     = is_array($data['accepted_cpt_codes'] ?? [])    ? json_encode($data['accepted_cpt_codes'] ?? [])    : '[]';
        $allIcd10    = is_array($data['all_icd10_suggestions'] ?? []) ? json_encode($data['all_icd10_suggestions'] ?? []) : '[]';
        $allCpt      = is_array($data['all_cpt_suggestions'] ?? [])   ? json_encode($data['all_cpt_suggestions'] ?? [])   : '[]';

        // Decode soap_note to extract preview field
        $soapArray   = is_array($data['soap_note']) ? $data['soap_note'] : (json_decode($data['soap_note'], true) ?? []);
        $preview     = substr($soapArray['subjective'] ?? '', 0, 250);
        $instructions = $soapArray['patient_instructions'] ?? ($data['patient_instructions'] ?? null);
        $status      = $data['status'] ?? 'draft';

        $id = sqlInsert(
            "INSERT INTO `" . self::TABLE . "`
             (patient_uuid, encounter_date, status, transcript, transcript_word_count,
              transcript_duration_s, soap_note, soap_subjective_preview,
              accepted_icd10_codes, accepted_cpt_codes,
              all_icd10_suggestions, all_cpt_suggestions,
              patient_instructions, generation_model, confidence_overall)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
            [
                $data['patient_uuid'],
                $data['encounter_date'],
                $status,
                $data['transcript'] ?? null,
                $data['transcript_word_count'] ?? null,
                $data['transcript_duration_s'] ?? null,
                $soapJson,
                $preview,
                $icd10Json,
                $cptJson,
                $allIcd10,
                $allCpt,
                $instructions,
                $data['generation_model'] ?? null,
                $data['confidence_overall'] ?? null,
            ]
        );
        $result->setData([['id' => $id, 'status' => $status]]);
        return $result;
    }

    /**
     * List scribe encounters for a patient, newest first.
     *
     * @param string $patientUuid Patient UUID.
     * @param int    $limit       Max rows to return.
     * @param string $status      Filter by 'draft', 'finalized', or '' for all.
     * @return ProcessingResult containing array of encounter summary rows.
     */
    public function listByPatient(string $patientUuid, int $limit, string $status = ''): ProcessingResult
    {
        $result = new ProcessingResult();

        $sql    = "SELECT id, patient_uuid, encounter_date, status,
                          soap_subjective_preview, accepted_icd10_codes, accepted_cpt_codes,
                          created_at, updated_at
                   FROM `" . self::TABLE . "`
                   WHERE patient_uuid = ?";
        $params = [$patientUuid];

        if ($status && $status !== 'all') {
            $sql .= " AND status = ?";
            $params[] = $status;
        }
        $sql .= " ORDER BY encounter_date DESC, created_at DESC LIMIT ?";
        $params[] = $limit;

        $rows_result = sqlStatement($sql, $params);
        $rows = [];
        while ($row = sqlFetchArray($rows_result)) {
            $row['accepted_icd10_codes'] = json_decode($row['accepted_icd10_codes'] ?? '[]', true) ?? [];
            $row['accepted_cpt_codes']   = json_decode($row['accepted_cpt_codes'] ?? '[]', true) ?? [];
            $rows[] = $row;
        }

        $result->setData($rows);
        return $result;
    }

    /**
     * Partial update of a scribe encounter.
     *
     * Finalized encounters cannot be re-finalized. Draft encounters can be
     * promoted to finalized via status field.
     *
     * @param int   $id   Encounter row ID.
     * @param array $data Fields to update (partial).
     * @return ProcessingResult containing ['updated' => bool].
     */
    public function update(int $id, array $data): ProcessingResult
    {
        $result = new ProcessingResult();

        $row = sqlQuery("SELECT status FROM `" . self::TABLE . "` WHERE id = ?", [$id]);
        if (!$row) {
            $result->setValidationMessages(['id' => "Encounter $id not found"]);
            return $result;
        }

        // Immutability: finalized encounters cannot be changed
        if ($row['status'] === 'finalized') {
            $result->setValidationMessages(['status' => 'Finalized encounters cannot be modified']);
            return $result;
        }

        $fields = [];
        $vals   = [];
        $allowed = ['status', 'soap_note', 'patient_instructions',
                    'accepted_icd10_codes', 'accepted_cpt_codes'];

        foreach ($allowed as $f) {
            if (!array_key_exists($f, $data)) {
                continue;
            }
            $v = in_array($f, ['soap_note', 'accepted_icd10_codes', 'accepted_cpt_codes'], true)
                ? json_encode($data[$f])
                : $data[$f];
            $fields[] = "`$f` = ?";
            $vals[]   = $v;
        }

        if (isset($data['status']) && $data['status'] === 'finalized') {
            $fields[] = 'finalized_at = ?';
            $vals[]   = date('Y-m-d H:i:s');
        }

        if (empty($fields)) {
            $result->setData([['updated' => false]]);
            return $result;
        }

        $vals[] = $id;
        sqlStatement(
            "UPDATE `" . self::TABLE . "` SET " . implode(', ', $fields) . " WHERE id = ?",
            $vals
        );

        $result->setData([['updated' => true]]);
        return $result;
    }

    /**
     * Delete a draft encounter. Finalized encounters are protected.
     *
     * @param int $id Encounter row ID.
     * @return ProcessingResult containing ['deleted' => bool].
     */
    public function delete(int $id): ProcessingResult
    {
        $result = new ProcessingResult();

        $row = sqlQuery("SELECT status FROM `" . self::TABLE . "` WHERE id = ?", [$id]);
        if (!$row) {
            $result->setValidationMessages(['id' => "Encounter $id not found"]);
            return $result;
        }
        if ($row['status'] === 'finalized') {
            $result->setValidationMessages(['status' => 'Finalized encounters cannot be deleted']);
            return $result;
        }

        sqlStatement("DELETE FROM `" . self::TABLE . "` WHERE id = ?", [$id]);
        $result->setData([['deleted' => true]]);
        return $result;
    }
}
