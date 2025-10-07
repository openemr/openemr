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

class VitalsCalculatedService extends BaseService {

    const TABLE_NAME = 'form_vitals_calculation';
    const TABLE_NAME_JOIN = 'form_vitals_calculation_form_vitals';

    private int $authUserId;

    private UuidRegistry $uuidRegistry;

    const BLOOD_PRESSURE_UNIT = 'mm[Hg]';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME);
    }

    public function setCurrentUserId(int $user): void {
        $this->authUserId = $user;
    }
    public function getCurrentUserId() {
        if (!isset($this->authUserId)) {
            $this->authUserId = $_SESSION['authUserID'];
        }
        return $this->authUserId;
    }

    public function getUuidRegistry(): UuidRegistry {
        if (!isset($this->uuidRegistry)) {
            $this->uuidRegistry = new UuidRegistry(['table_name' => self::TABLE_NAME]);
        }
        return $this->uuidRegistry;
    }

    public function setUuidRegistry(UuidRegistry $uuidRegistry): void {
        $this->uuidRegistry = $uuidRegistry;
    }

    public function search($search, $isAndCondition = true): ProcessingResult
    {
        $processingResult = new ProcessingResult();
        try {
            $sql = "SELECT fvc.uuid, fvc.date_start, fvc.date_end, fvc.created_at, fvc.updated_at,
                fvc.created_by, cu.created_by_uuid,
                fvc.updated_by, uu.updated_by_uuid,
                fvc.calculation_id,fvc.vitals_column,vitals.vuuid,
                fvc.value, fvc.value_unit,
                fvc.encounter, e.euuid,
                fvc.pid, p.puuid,
                vitals.vuuid
                FROM form_vitals_calculation fvc
                JOIN (
                    select pid AS patient_id,uuid AS puuid
                    FROM patient_data
                ) p ON p.patient_id = fvc.pid
                LEFT JOIN (
                    SELECT encounter AS eid
                    ,uuid AS euuid
                ) e ON fvc.encounter = e.eid
                LEFT JOIN (
                    select id AS user_creator_id
                    ,uuid AS created_by_uuid
                    FROM users
                ) cu ON fvc.created_by = cu.user_creator_id
                LEFT JOIN (
                    select id AS user_updater_id
                    ,uuid AS updated_by_uuid
                    FROM users
                ) uu ON fvc.updated_by = uu.user_updater_id
                JOIN form_vitals_calculation_form_vitals fvcfv ON fvcfv.fvc_uuid = fvc.uuid
                JOIN (
                    SELECT uuid AS vuuid
                    ,id AS vid
                    FROM form_vitals
                 ) vitals ON fvcfv.vitals_id = vitals.vid ";

            $whereClause = FhirSearchWhereClauseBuilder::build($search, $isAndCondition);
            // lets combine our columns, table selects, and the vitals interpretation clauses
            $sql = $sql . " " . $whereClause->getFragment();
            $sql .= " ORDER BY date_start DESC, `date_end` DESC ";
            $records = QueryUtils::fetchRecords($sql, $whereClause->getBoundValues());
            $groupedRecordsByUuid = [];
            foreach ($records as $record) {
                $uuid = $record['uuid'];
                if (!isset($groupedRecordsByUuid[$uuid])) {
                    $groupedRecordsByUuid[$uuid] = $record;
                    $groupedRecordsByUuid[$uuid]['parent_observation_uuid'] = [];
                }
                $groupedRecordsByUuid[$uuid]['parent_observation_uuid'][] = $record['vuuid'];
                unset($groupedRecordsByUuid[$uuid]['vuuid']);
            }
            foreach ($groupedRecordsByUuid as $record) {
                $processingResult->addData($record);
            }
        }
        catch (SqlQueryException $exception) {
            $this->getLogger()->error($exception->getMessage());
            $processingResult->addInternalError($exception->getMessage());
        }
        return $processingResult;
    }

    public function saveCalculatedVitalsForRecord(array $vitalsRecord): void {
        // calculations
        $calculations = [
            'bps-MeanEncounter'
            ,'bps-Mean3Day'
            ,'bps-MeanLast5'
            ,'bpd-MeanEncounter'
            ,'bpd-Mean3Day'
            ,'bpd-MeanLast5'
        ];

        // given a vitals record... go through and save any calculated vital records or statistics we need to process that may cover
        // multiple records.

        // we need to save off average systolic readings for all vitals in this encounter
        // we need to save off average diastolic readings for last 5 days for this patient

        // have a uuid, start datetime, end datetime, calculation_id, value, unit, encounter (optional), pid (required), notes
        // save off form_vital_calculation_form_vitals (1:m relationship) with 1 calculation to many ids
        //      calculation_id, vitals_id
        //      used for populating the derivedFrom field of observation
        foreach ($calculations as $calculation) {
            $calculatedRecord = $this->getCalculatedRecord($vitalsRecord['pid'], $calculation, $vitalsRecord['encounter'] ?? null) ?? [];
            match($calculation) {
                'bps-MeanLast5' => $this->saveBPS_MeanLast5($vitalsRecord, $calculatedRecord)
                ,'bpd-MeanLast5' => $this->saveBPD_MeanLast5($vitalsRecord, $calculatedRecord)
                ,'bps-Mean3Day' => $this->saveBPS_Mean3Day($vitalsRecord, $calculatedRecord)
                ,'bpd-Mean3Day' => $this->saveBPD_Mean3Day($vitalsRecord, $calculatedRecord)
                ,'bps-MeanEncounter' => $this->saveBPS_MeanEncounter($vitalsRecord, $calculatedRecord)
                ,'bpd-MeanEncounter' => $this->saveBPD_MeanEncounter($vitalsRecord, $calculatedRecord)
            };
        }

    }

    protected function saveBPS_MeanLast5($vitalsRecord, array $recordToSave): void {

        $calculation = $this->getMeanLastFive('bps', $vitalsRecord['pid']);
        // we need to update the record
        $calculation['value_unit'] = self::BLOOD_PRESSURE_UNIT;
        $calculation['vitals_column'] = 'bps';
        $calculation['calculation_id'] = 'bps-MeanLast5';
        $recordToSave['pid'] = $vitalsRecord['pid'];
        $this->saveCalculationRecord($recordToSave, $calculation);
    }

    protected function saveBPD_MeanLast5($vitalsRecord, array $recordToSave): void {

        $calculation = $this->getMeanLastFive('bpd', $vitalsRecord['pid']);
        // we need to update the record
        $calculation['calculation_id'] = 'bpd-MeanLast5';
        $calculation['vitals_column'] = 'bpd';
        $calculation['value_unit'] = self::BLOOD_PRESSURE_UNIT;
        $recordToSave['pid'] = $vitalsRecord['pid'];
        $this->saveCalculationRecord($recordToSave, $calculation);
    }

    protected function saveBPD_Mean3Day($vitalsRecord, array $recordToSave): void {

        $calculation = $this->getMean3Day('bpd', $vitalsRecord['pid']);
        // we need to update the record
        $calculation['calculation_id'] = 'bpd-Mean3Day';
        $calculation['vitals_column'] = 'bpd';
        $calculation['value_unit'] = self::BLOOD_PRESSURE_UNIT;
        $recordToSave['pid'] = $vitalsRecord['pid'];
        $this->saveCalculationRecord($recordToSave, $calculation);
    }

    protected function saveBPS_Mean3Day($vitalsRecord, array $recordToSave): void {

        $calculation = $this->getMean3Day('bps', $vitalsRecord['pid']);
        // we need to update the record
        $calculation['value_unit'] = self::BLOOD_PRESSURE_UNIT;
        $calculation['vitals_column'] = 'bps';
        $calculation['calculation_id'] = 'bps-Mean3Day';
        $recordToSave['pid'] = $vitalsRecord['pid'];
        $this->saveCalculationRecord($recordToSave, $calculation);
    }

    private function saveBPS_MeanEncounter($vitalsRecord, array $recordToSave): void {
        $calculation = $this->getMeanEncounter('bps', $vitalsRecord['pid'], $vitalsRecord['encounter']);
        // we need to update the record
        $calculation['value_unit'] = self::BLOOD_PRESSURE_UNIT;
        $calculation['vitals_column'] = 'bps';
        $calculation['calculation_id'] = 'bps-MeanEncounter';
        $recordToSave['pid'] = $vitalsRecord['pid'];
        $this->saveCalculationRecord($recordToSave, $calculation);
    }

    private function saveBPD_MeanEncounter($vitalsRecord, array $recordToSave): void {
        $calculation = $this->getMeanEncounter('bpd', $vitalsRecord['pid'], $vitalsRecord['encounter']);
        // we need to update the record
        $calculation['value_unit'] = self::BLOOD_PRESSURE_UNIT;
        $calculation['vitals_column'] = 'bpd';
        $calculation['calculation_id'] = 'bpd-MeanEncounter';
        $recordToSave['pid'] = $vitalsRecord['pid'];
        $this->saveCalculationRecord($recordToSave, $calculation);
    }


    protected function saveCalculationRecord($record, $calculation): void {

        $record['date_start'] = $calculation['date_start'];
        $record['date_end'] = $calculation['date_end'];
        $record['value'] = $calculation['value'];
        $record['value_unit'] = $calculation['value_unit'];
        $record['created_by'] ??= $this->getCurrentUserId();
        $record['updated_by'] = $this->getCurrentUserId();
        $record['calculation_id'] ??= $calculation['calculation_id'];
        $record['vitals_column'] ??= $calculation['vitals_column'];

        if (empty($record['uuid'])) {
            $record['uuid'] = $this->getUuidRegistry()->createUuid();
            $columns = $this->buildInsertColumns($record);
            $sql = "INSERT INTO " . self::TABLE_NAME . " SET " . $columns['set'] . ", created_at=NOW(),updated_at=NOW() ";
            QueryUtils::sqlInsert($sql, $columns['bind']);
        } else {
            $columns = $this->buildUpdateColumns($record);
            $sql = "UPDATE " . self::TABLE_NAME . " SET " . $columns['set'] . ", updated_at=NOW() ";
            QueryUtils::sqlStatementThrowException($sql, $columns['bind']);
        }

        // now do derived from observations
        QueryUtils::sqlStatementThrowException("DELETE FROM " . self::TABLE_NAME_JOIN . " WHERE fvc_uuid = ? ", $record['uuid']);
        $sql = "INSERT INTO `" . self::TABLE_NAME_JOIN . "` (`fvc_uuid`, `vitals_id`) VALUES ";
        $insertStatements = [];
        $bind = [];
        foreach ($calculation['vitals'] as $vitalId) {
            $insertStatements[] = "(?,?)";
            $bind[] = $record['uuid'];
            $bind[] = $vitalId;
        }
        QueryUtils::sqlStatementThrowException($sql . implode(", ", $insertStatements), $bind);
    }

    private function getMeanLastFive(string $vitalsColumn, $pid): ?array
    {
        // we could make this more efficient with GROUP_CONCAT but its not ANSI SQL standard...
        // so we'll do it the old fashioned way in case we ever change databases
        // downside of this approach is there could be vitals added between the first and second query
        // but that is a very small window and not likely to be an issue in practice
        $escapedColumnName = QueryUtils::escapeColumnName($vitalsColumn, ['form_vitals']);
        // First get the IDs of the last 5 vitals
        // AI GENERATED
        $idSql = "SELECT id FROM form_vitals WHERE " . $escapedColumnName . " != '' AND " . $escapedColumnName . " IS NOT NULL AND pid=? ORDER BY id DESC LIMIT 5";
        $vitalIds = QueryUtils::fetchRecords($idSql, [$pid]);

        if (empty($vitalIds)) {
            return null;
        }
        $ids = array_column($vitalIds, 'id');
        return $this->getMeanCalculationForIds($escapedColumnName, $ids);
    }
    private function getMeanEncounter(string $vitalsColumn, int $pid, int $encounter): ?array {
        $escapedColumnName = QueryUtils::escapeColumnName($vitalsColumn, ['form_vitals']);
        $idSql = "SELECT id FROM form_vitals WHERE " . $escapedColumnName . " != '' AND " . $escapedColumnName . " IS NOT NULL AND pid=? and encounter = ? ORDER BY id";
        $vitalIds = QueryUtils::fetchRecords($idSql, [$pid, $encounter]);

        if (empty($vitalIds)) {
            return null;
        }
        $ids = array_column($vitalIds, 'id');
        return $this->getMeanCalculationForIds($escapedColumnName, $ids);
    }

    private function getMean3Day(string $vitalsColumn, int $pid): ?array {
        $escapedColumnName = QueryUtils::escapeColumnName($vitalsColumn, ['form_vitals']);
        $idSql = "SELECT id FROM form_vitals WHERE " . $escapedColumnName . " != '' AND " . $escapedColumnName
            . " IS NOT NULL AND pid=? and `date` BETWEEN DATE_SUB(NOW(), INTERVAL 3 DAY) "
            . " AND NOW() ORDER BY id";
        $vitalIds = QueryUtils::fetchRecords($idSql, [$pid]);

        if (empty($vitalIds)) {
            return null;
        }
        $ids = array_column($vitalIds, 'id');
        return $this->getMeanCalculationForIds($escapedColumnName, $ids);
    }

    private function getMeanCalculationForIds(string $escapedColumnName, array $ids): ?array {
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';

        $sql = "select pid,avg(" . $escapedColumnName . ") AS value,min(date) AS date_start,max(date) AS date_end "
            . " FROM form_vitals "
            . " WHERE id IN ($placeholders) ";
        $records = QueryUtils::fetchRecords($sql, $ids);
        $calculation = null;
        if (!empty($records)) {
            $calculation = [
                'date_start' => $records[0]['date_start']
                ,'date_end' => $records[0]['date_end']
                ,'value' => $records[0]['value']
                ,'vitals' => $ids
            ];
        }
        return $calculation;
    }

    private function getCalculatedRecord(int $pid, string $calculationId, ?int $encounter): ?array
    {
        $sql ="SELECT * FROM " . self::TABLE_NAME . " calc JOIN " . self::TABLE_NAME_JOIN
            . " calc_join ON calc.uuid = calc_join.fvc_uuid "
        . " WHERE calc.pid = ? AND calc.calculation_id = ? ";
        $bind = [$pid, $calculationId];
        if (!empty($encounter)) {
            $sql .= " AND (calc.encounter IS NULL OR calc.encounter = ?) ";
            $bind[] = $encounter;
        }
        $records = QueryUtils::fetchRecords($sql, $bind);
        return $records[0] ?? null;
    }
}
