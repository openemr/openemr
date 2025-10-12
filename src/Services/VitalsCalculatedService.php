<?php

/*
 * VitalsCalculatedService.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\Search\FhirSearchWhereClauseBuilder;
use OpenEMR\Validators\ProcessingResult;

class VitalsCalculatedService extends BaseService
{
    const TABLE_NAME = 'form_vitals_calculation';
    const TABLE_NAME_COMPONENTS = 'form_vitals_calculation_components';
    const TABLE_NAME_JOIN = 'form_vitals_calculation_form_vitals';

    private int $authUserId;
    private UuidRegistry $uuidRegistry;

    const BLOOD_PRESSURE_UNIT = 'mm[Hg]';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME);
    }

    public function setCurrentUserId(int $user): void
    {
        $this->authUserId = $user;
    }

    public function getCurrentUserId()
    {
        if (!isset($this->authUserId)) {
            $this->authUserId = $_SESSION['authUserID'];
        }
        return $this->authUserId;
    }

    public function getUuidRegistry(): UuidRegistry
    {
        if (!isset($this->uuidRegistry)) {
            $this->uuidRegistry = new UuidRegistry(['table_name' => self::TABLE_NAME]);
        }
        return $this->uuidRegistry;
    }

    public function setUuidRegistry(UuidRegistry $uuidRegistry): void
    {
        $this->uuidRegistry = $uuidRegistry;
    }

    public function search($search, $isAndCondition = true): ProcessingResult
    {
        $processingResult = new ProcessingResult();
        try {
            $sql = "SELECT fvc.uuid, fvc.date_start, fvc.date_end, fvc.created_at, fvc.updated_at,
            fvc.created_by, cu.created_by_uuid,
            fvc.updated_by, uu.updated_by_uuid,
            fvc.calculation_id, fvc.encounter, e.euuid,
            fvc.pid, p.puuid,
            vitals.vuuid as source_vital_uuid,
            comp.vitals_column, comp.value, comp.value_string, comp.value_unit, comp.component_order
            FROM form_vitals_calculation fvc
            JOIN (
                select pid AS patient_id,uuid AS puuid
                FROM patient_data
            ) p ON p.patient_id = fvc.pid
            LEFT JOIN (
                SELECT encounter AS eid, uuid AS euuid
                FROM form_encounter
            ) e ON fvc.encounter = e.eid
            LEFT JOIN (
                select id AS user_creator_id, uuid AS created_by_uuid
                FROM users
            ) cu ON fvc.created_by = cu.user_creator_id
            LEFT JOIN (
                select id AS user_updater_id, uuid AS updated_by_uuid
                FROM users
            ) uu ON fvc.updated_by = uu.user_updater_id
            LEFT JOIN form_vitals_calculation_components comp ON comp.fvc_uuid = fvc.uuid
            LEFT JOIN form_vitals_calculation_form_vitals fvcfv ON fvcfv.fvc_uuid = fvc.uuid
            LEFT JOIN (
                SELECT uuid AS vuuid, id AS vid
                FROM form_vitals
             ) vitals ON fvcfv.vitals_id = vitals.vid ";

            $whereClause = FhirSearchWhereClauseBuilder::build($search, $isAndCondition);
            $sql = $sql . " " . $whereClause->getFragment();
            $sql .= " ORDER BY fvc.date_start DESC, fvc.date_end DESC, comp.component_order ASC ";

            $records = QueryUtils::fetchRecords($sql, $whereClause->getBoundValues());

            // Group records by calculation UUID
            $groupedRecords = $this->groupCalculationRecords($records);

            foreach ($groupedRecords as $record) {
                $processingResult->addData($record);
            }
        } catch (SqlQueryException $exception) {
            $this->getLogger()->error($exception->getMessage());
            $processingResult->addInternalError($exception->getMessage());
        }
        return $processingResult;
    }

    private function groupCalculationRecords(array $records): array
    {
        $groupedRecords = [];

        foreach ($records as $record) {
            $uuid = UuidRegistry::uuidToString($record['uuid']);

            // Initialize record if first time seeing this UUID
            if (!isset($groupedRecords[$uuid])) {
                $groupedRecords[$uuid] = [
                    'uuid' => $uuid,
                    'date_start' => $record['date_start'],
                    'date_end' => $record['date_end'],
                    'created_at' => $record['created_at'],
                    'updated_at' => $record['updated_at'],
                    'created_by' => $record['created_by'],
                    'created_by_uuid' => UuidRegistry::uuidToString($record['created_by_uuid']),
                    'updated_by' => $record['updated_by'],
                    'updated_by_uuid' => UuidRegistry::uuidToString($record['updated_by_uuid']),
                    'calculation_id' => $record['calculation_id'],
                    'encounter' => $record['encounter'],
                    'euuid' => !empty($record['euuid']) ? UuidRegistry::uuidToString($record['euuid']) : null,
                    'pid' => $record['pid'],
                    'puuid' => UuidRegistry::uuidToString($record['puuid']),
                    'parent_observation_uuid' => [],
                    'components' => []
                ];
            }

            // Add source vital UUID if present and not already added
            $sourceVitalUuid = !empty($sourceVitalUuid) ? UuidRegistry::uuidToString($record['source_vital_uuid']) : null;
            if (
                !empty($sourceVitalUuid) &&
                !in_array($sourceVitalUuid, $groupedRecords[$uuid]['parent_observation_uuid'])
            ) {
                $groupedRecords[$uuid]['parent_observation_uuid'][] = $sourceVitalUuid;
            }

            // Add component if present and not already added
            if (!empty($record['vitals_column'])) {
                $componentKey = $record['vitals_column'];
                $componentExists = false;

                // Check if component already exists
                foreach ($groupedRecords[$uuid]['components'] as $existingComponent) {
                    if ($existingComponent['vitals_column'] === $componentKey) {
                        $componentExists = true;
                        break;
                    }
                }

                // Add component if it doesn't exist
                if (!$componentExists) {
                    $groupedRecords[$uuid]['components'][] = [
                        'vitals_column' => $record['vitals_column'],
                        'value' => !empty($record['value']) ? floatval($record['value']) : null,
                        'value_string' => $record['value_string'],
                        'value_unit' => $record['value_unit'],
                        'component_order' => intval($record['component_order'])
                    ];
                }
            }
        }

        // Sort components by component_order for each record
        foreach ($groupedRecords as &$record) {
            usort($record['components'], fn($a, $b): int => $a['component_order'] <=> $b['component_order']);
        }
        return array_values($groupedRecords);
    }

    public function saveCalculatedVitalsForRecord(array $vitalsRecord): void
    {
        // Updated calculations - now grouped by calculation type
        $calculations = [
            'bp-MeanEncounter',
            'bp-Mean3Day',
            'bp-MeanLast5'
        ];

        foreach ($calculations as $calculation) {
            $calculatedRecord = $this->getCalculatedRecord($vitalsRecord['pid'], $calculation, $vitalsRecord['encounter'] ?? null) ?? [];
            match ($calculation) {
                'bp-MeanLast5' => $this->saveBP_MeanLast5($vitalsRecord, $calculatedRecord),
                'bp-Mean3Day' => $this->saveBP_Mean3Day($vitalsRecord, $calculatedRecord),
                'bp-MeanEncounter' => $this->saveBP_MeanEncounter($vitalsRecord, $calculatedRecord)
            };
        }
    }

    protected function saveBP_MeanLast5($vitalsRecord, array $recordToSave): void
    {
        $calculation = $this->getBPMeanLastFive($vitalsRecord['pid']);
        if ($calculation) {
            $calculation['calculation_id'] = 'bp-MeanLast5';
            $recordToSave['pid'] = $vitalsRecord['pid'];
            $this->saveCalculationRecord($recordToSave, $calculation);
        }
    }

    protected function saveBP_Mean3Day($vitalsRecord, array $recordToSave): void
    {
        $calculation = $this->getBPMean3Day($vitalsRecord['pid']);
        if ($calculation) {
            $calculation['calculation_id'] = 'bp-Mean3Day';
            $recordToSave['pid'] = $vitalsRecord['pid'];
            $this->saveCalculationRecord($recordToSave, $calculation);
        }
    }

    protected function saveBP_MeanEncounter($vitalsRecord, array $recordToSave): void
    {
        $calculation = $this->getBPMeanEncounter($vitalsRecord['pid'], $vitalsRecord['encounter']);
        if ($calculation) {
            $calculation['calculation_id'] = 'bp-MeanEncounter';
            $recordToSave['pid'] = $vitalsRecord['pid'];
            $recordToSave['encounter'] = $vitalsRecord['encounter'];
            $this->saveCalculationRecord($recordToSave, $calculation);
        }
    }

    protected function saveCalculationRecord($record, $calculation): void
    {
        $record['date_start'] = $calculation['date_start'];
        $record['date_end'] = $calculation['date_end'];
        $record['created_by'] ??= $this->getCurrentUserId();
        $record['updated_by'] = $this->getCurrentUserId();
        $record['calculation_id'] ??= $calculation['calculation_id'];

        if (empty($record['uuid'])) {
            $record['uuid'] = $this->getUuidRegistry()->createUuid();
            $columns = $this->buildInsertColumns($record);
            $sql = "INSERT INTO " . self::TABLE_NAME . " SET " . $columns['set'] . ", created_at=NOW(),updated_at=NOW() ";
            QueryUtils::sqlInsert($sql, $columns['bind']);
        } else {
            $columns = $this->buildUpdateColumns($record);
            $sql = "UPDATE " . self::TABLE_NAME . " SET " . $columns['set'] . ", updated_at=NOW() WHERE uuid = ?";
            QueryUtils::sqlStatementThrowException($sql, array_merge($columns['bind'], [$record['uuid']]));
        }

        // Save components
        $this->saveCalculationComponents($record['uuid'], $calculation['components']);

        // Save source vitals relationships
        $this->saveCalculationVitalsRelationships($record['uuid'], $calculation['vitals']);
    }

    private function saveCalculationComponents(string $calculationUuid, array $components): void
    {
        // Clear existing components
        QueryUtils::sqlStatementThrowException("DELETE FROM " . self::TABLE_NAME_COMPONENTS . " WHERE fvc_uuid = ?", [$calculationUuid]);

        if (empty($components)) {
            return;
        }

        $sql = "INSERT INTO " . self::TABLE_NAME_COMPONENTS . " (fvc_uuid, vitals_column, value, value_string, value_unit, component_order) VALUES ";
        $insertStatements = [];
        $bind = [];

        foreach ($components as $component) {
            $insertStatements[] = "(?,?,?,?,?,?)";
            $bind[] = $calculationUuid;
            $bind[] = $component['vitals_column'];
            $bind[] = $component['value'] ?? null;
            $bind[] = $component['value_string'] ?? null;
            $bind[] = $component['value_unit'] ?? null;
            $bind[] = $component['component_order'] ?? 0;
        }

        QueryUtils::sqlStatementThrowException($sql . implode(", ", $insertStatements), $bind);
    }

    private function saveCalculationVitalsRelationships(string $calculationUuid, array $vitalIds): void
    {
        // Clear existing relationships
        QueryUtils::sqlStatementThrowException("DELETE FROM " . self::TABLE_NAME_JOIN . " WHERE fvc_uuid = ?", [$calculationUuid]);

        if (empty($vitalIds)) {
            return;
        }

        $sql = "INSERT INTO " . self::TABLE_NAME_JOIN . " (fvc_uuid, vitals_id) VALUES ";
        $insertStatements = [];
        $bind = [];

        foreach ($vitalIds as $vitalId) {
            $insertStatements[] = "(?,?)";
            $bind[] = $calculationUuid;
            $bind[] = $vitalId;
        }

        QueryUtils::sqlStatementThrowException($sql . implode(", ", $insertStatements), $bind);
    }

    private function getBPMeanLastFive(int $pid): ?array
    {
        $bpsIds = $this->getVitalIds('bps', $pid, null, 5);
        $bpdIds = $this->getVitalIds('bpd', $pid, null, 5);

        if (empty($bpsIds) || empty($bpdIds)) {
            return null;
        }

        // Use intersection to ensure we only use vitals that have both readings
        // NOTE: this assumes that component values for a calculation will always be non-empty strings and populated
        // for example a systolic blood pressure reading will always be in 'bps' column and will also have a diastolic
        // reading in 'bpd' column, otherwise the vitals mismatch.
        // If this assumption changes, the logic here will need to be updated.
        $commonIds = array_intersect($bpsIds, $bpdIds);
        if (empty($commonIds)) {
            return null;
        }

        return $this->calculateBPMeanForIds($commonIds);
    }

    private function getBPMean3Day(int $pid): ?array
    {
        $bpsIds = $this->getVitalIds('bps', $pid, 3);
        $bpdIds = $this->getVitalIds('bpd', $pid, 3);

        if (empty($bpsIds) || empty($bpdIds)) {
            return null;
        }

        $commonIds = array_intersect($bpsIds, $bpdIds);
        if (empty($commonIds)) {
            return null;
        }

        return $this->calculateBPMeanForIds($commonIds);
    }

    private function getBPMeanEncounter(int $pid, int $encounter): ?array
    {
        $sql = "SELECT form_vitals.id FROM form_vitals JOIN forms f ON form_vitals.id = f.form_id WHERE bps != '' AND bps IS NOT NULL AND bpd != '' AND bpd IS NOT NULL "
        . " AND form_vitals.pid=? AND f.encounter=? ORDER BY id";
        $vitalIds = QueryUtils::fetchRecords($sql, [$pid, $encounter]);

        if (empty($vitalIds)) {
            return null;
        }

        $ids = array_column($vitalIds, 'id');
        return $this->calculateBPMeanForIds($ids);
    }

    private function getVitalIds(string $vitalsColumn, int $pid, ?int $days = null, ?int $limit = null): array
    {
        $escapedColumnName = QueryUtils::escapeColumnName($vitalsColumn, ['form_vitals']);
        $sql = "SELECT id FROM form_vitals WHERE " . $escapedColumnName . " != '' AND " . $escapedColumnName . " IS NOT NULL AND pid=?";
        $bind = [$pid];

        if ($days !== null) {
            $sql .= " AND `date` BETWEEN DATE_SUB(NOW(), INTERVAL ? DAY) AND NOW()";
            $bind[] = $days;
        }

        $sql .= " ORDER BY id DESC";

        if ($limit !== null) {
            $sql .= " LIMIT ?";
            $bind[] = $limit;
        }

        $records = QueryUtils::fetchRecords($sql, $bind);
        return array_column($records, 'id');
    }

    private function calculateBPMeanForIds(array $ids): ?array
    {
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        $sql = "SELECT AVG(CAST(bps AS DECIMAL(10,2))) as avg_systolic, AVG(CAST(bpd AS DECIMAL(10,2))) as avg_diastolic,
                MIN(date) as date_start, MAX(date) as date_end
                FROM form_vitals WHERE id IN ($placeholders)";

        $records = QueryUtils::fetchRecords($sql, $ids);

        if (empty($records) || $records[0]['avg_systolic'] === null || $records[0]['avg_diastolic'] === null) {
            return null;
        }

        return [
            'date_start' => $records[0]['date_start'],
            'date_end' => $records[0]['date_end'],
            'vitals' => $ids,
            'components' => [
                [
                    'vitals_column' => 'bps',
                    'value' => round($records[0]['avg_systolic'], 1),
                    'value_unit' => self::BLOOD_PRESSURE_UNIT,
                    'component_order' => 0
                ],
                [
                    'vitals_column' => 'bpd',
                    'value' => round($records[0]['avg_diastolic'], 1),
                    'value_unit' => self::BLOOD_PRESSURE_UNIT,
                    'component_order' => 1
                ]
            ]
        ];
    }

    private function getCalculatedRecord(int $pid, string $calculationId, ?int $encounter): ?array
    {
        $sql = "SELECT * FROM " . self::TABLE_NAME . " WHERE pid = ? AND calculation_id = ?";
        $bind = [$pid, $calculationId];

        if (!empty($encounter)) {
            $sql .= " AND (encounter IS NULL OR encounter = ?)";
            $bind[] = $encounter;
        }

        $records = QueryUtils::fetchRecords($sql, $bind);
        return $records[0] ?? null;
    }
}
