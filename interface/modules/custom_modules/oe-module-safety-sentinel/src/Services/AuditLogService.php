<?php

/**
 * Safety Sentinel Audit Log Service
 *
 * CRUD operations for the safety_audit_log table using OpenEMR's standard
 * database functions (sqlStatement, sqlQuery, sqlInsert).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ryo Iwata <ryo@example.com>
 * @copyright Copyright (c) 2026
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\SafetySentinel\Services;

use OpenEMR\Validators\ProcessingResult;

class AuditLogService
{
    private const TABLE = 'safety_audit_log';

    public function getByPatient(string $puuid, int $limit): ProcessingResult
    {
        $result = new ProcessingResult();
        $rows = sqlStatement(
            "SELECT id, drug_name, drug_rxnorm, check_timestamp,
                    interaction_severity, allergy_conflict, requires_pharmacist_review,
                    pharmacist_acknowledged, formulary_covered, prior_auth_required,
                    covered_alternative, confidence_score
             FROM `" . self::TABLE . "`
             WHERE patient_uuid = ?
             ORDER BY check_timestamp DESC
             LIMIT ?",
            [$puuid, $limit]
        );
        $data = [];
        while ($row = sqlFetchArray($rows)) {
            $row['allergy_conflict']             = (bool)$row['allergy_conflict'];
            $row['requires_pharmacist_review']   = (bool)$row['requires_pharmacist_review'];
            $row['pharmacist_acknowledged']      = (bool)$row['pharmacist_acknowledged'];
            $row['formulary_covered']            = $row['formulary_covered'] !== null ? (bool)$row['formulary_covered'] : null;
            $row['prior_auth_required']          = $row['prior_auth_required'] !== null ? (bool)$row['prior_auth_required'] : null;
            $data[] = $row;
        }
        $result->setData($data);
        return $result;
    }

    public function create(array $data): ProcessingResult
    {
        $result = new ProcessingResult();

        foreach (['patient_uuid', 'drug_name', 'interaction_severity'] as $field) {
            if (empty($data[$field])) {
                $result->addValidationError($field, "Field '$field' is required");
            }
        }
        if ($result->hasErrors()) {
            return $result;
        }

        $id = sqlInsert(
            "INSERT INTO `" . self::TABLE . "`
             (patient_uuid, drug_name, drug_rxnorm, insurance_plan,
              interaction_severity, allergy_conflict, requires_pharmacist_review,
              confidence_score, agent_summary,
              formulary_covered, formulary_tier, prior_auth_required, covered_alternative)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                substr($data['patient_uuid'], 0, 36),
                substr($data['drug_name'], 0, 255),
                isset($data['drug_rxnorm']) ? substr($data['drug_rxnorm'], 0, 50) : null,
                isset($data['insurance_plan']) ? substr($data['insurance_plan'], 0, 100) : null,
                $data['interaction_severity'],
                isset($data['allergy_conflict']) ? (int)(bool)$data['allergy_conflict'] : 0,
                isset($data['requires_pharmacist_review']) ? (int)(bool)$data['requires_pharmacist_review'] : 0,
                isset($data['confidence_score']) ? (float)$data['confidence_score'] : null,
                isset($data['agent_summary']) ? substr($data['agent_summary'], 0, 2000) : null,
                isset($data['formulary_covered']) ? (int)(bool)$data['formulary_covered'] : null,
                isset($data['formulary_tier']) && $data['formulary_tier'] > 0 ? (int)$data['formulary_tier'] : null,
                isset($data['prior_auth_required']) ? (int)(bool)$data['prior_auth_required'] : 0,
                isset($data['covered_alternative']) ? substr($data['covered_alternative'], 0, 255) : null,
            ]
        );
        $result->setData(['id' => $id]);
        return $result;
    }

    public function acknowledge(int $id, array $data): ProcessingResult
    {
        $result = new ProcessingResult();
        sqlStatement(
            "UPDATE `" . self::TABLE . "`
             SET pharmacist_acknowledged = 1,
                 pharmacist_override     = ?,
                 override_reason         = ?,
                 override_timestamp      = NOW()
             WHERE id = ?",
            [
                isset($data['pharmacist_override']) ? (int)(bool)$data['pharmacist_override'] : 0,
                $data['override_reason'] ?? null,
                $id,
            ]
        );
        $result->setData(['id' => $id, 'acknowledged' => true]);
        return $result;
    }

    public function getPending(int $limit): ProcessingResult
    {
        $result = new ProcessingResult();
        $rows = sqlStatement(
            "SELECT id, patient_uuid, drug_name, drug_rxnorm, insurance_plan,
                    check_timestamp, interaction_severity, allergy_conflict,
                    formulary_covered, prior_auth_required, covered_alternative,
                    confidence_score, agent_summary
             FROM `" . self::TABLE . "`
             WHERE requires_pharmacist_review = 1
               AND pharmacist_acknowledged    = 0
             ORDER BY check_timestamp DESC
             LIMIT ?",
            [$limit]
        );
        $data = [];
        while ($row = sqlFetchArray($rows)) {
            $row['allergy_conflict']   = (bool)$row['allergy_conflict'];
            $row['formulary_covered']  = $row['formulary_covered'] !== null ? (bool)$row['formulary_covered'] : null;
            $row['prior_auth_required'] = $row['prior_auth_required'] !== null ? (bool)$row['prior_auth_required'] : null;
            $data[] = $row;
        }
        $result->setData($data);
        return $result;
    }

    public function healthCheck(): ProcessingResult
    {
        $result = new ProcessingResult();
        $row = sqlQuery("SELECT COUNT(*) AS cnt FROM `" . self::TABLE . "`");
        $result->setData(['safety_audit_log' => 'ok', 'count' => (int)($row['cnt'] ?? 0)]);
        return $result;
    }
}
