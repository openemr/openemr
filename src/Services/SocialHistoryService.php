<?php

/**
 * SocialHistoryService.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\Search\FhirSearchWhereClauseBuilder;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Validators\ProcessingResult;

class SocialHistoryService extends BaseService
{
    private const TABLE_NAME = "history_data";

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME);
        UuidRegistry::createMissingUuidsForTables([self::TABLE_NAME]);
    }

    // To prevent sql injection on this function, if a variable is used for $given parameter, then
    // it needs to be escaped via whitelisting prior to using this function; see lines 2020-2121 of
    // library/clinical_rules.php script for example of this.
    function getHistoryData($pid, $given = "*", $dateStart = '', $dateEnd = '')
    {
        $where = '';
        if ($given == 'tobacco') {
            $where = 'tobacco is not null and';
        }

        if ($dateStart && $dateEnd) {
            $res = sqlQuery("select $given from history_data where $where pid = ? and date >= ? and date <= ? order by date DESC limit 0,1", array($pid,$dateStart,$dateEnd));
        } elseif ($dateStart && !$dateEnd) {
            $res = sqlQuery("select $given from history_data where $where pid = ? and date >= ? order by date DESC limit 0,1", array($pid,$dateStart));
        } elseif (!$dateStart && $dateEnd) {
            $res = sqlQuery("select $given from history_data where $where pid = ? and date <= ? order by date DESC limit 0,1", array($pid,$dateEnd));
        } else {
            $res = sqlQuery("select $given from history_data where $where pid=? order by date DESC limit 0,1", array($pid));
        }

        return $res;
    }

    public function getHistoryDataForPatientPid($pid, $limit = null)
    {
        $sql = "SELECT id, tobacco, alcohol, exercise_patterns, recreational_drugs FROM history_data WHERE pid=? ORDER BY id DESC LIMIT 1";

        $search = [
            new TokenSearchField('pid', $pid)
        ];
        $result = $this->search($search, true, $limit);
        if (!empty($result->getData())) {
            return $result->getData();
        }
        return [];
    }

    public function search($search, $isAndCondition = true, $limit = null)
    {
        // history_data contains a table record for every single insert into the database
        $sql = "
            SELECT
                history.id
                ,history.uuid
                ,history.date
                ,history.tobacco
                ,history.alcohol
                ,history.exercise_patterns
                ,history.recreational_drugs
                ,patients.pid
                ,patients.puuid
            FROM
            history_data history
            JOIN
            (
             SELECT
                    -- we could have this be max date, but this should be fine
                    max(id) AS id
                    FROM history_data
                    GROUP BY pid
            ) latest_history_records ON history.id = latest_history_records.id
            LEFT JOIN
            (
                SELECT
                    uuid AS puuid
                    ,pid
                    FROM patient_data
            ) patients ON history.pid = patients.pid";
        $whereClause = FhirSearchWhereClauseBuilder::build($search, $isAndCondition);

        $sql .= $whereClause->getFragment();

        if (is_numeric($limit)) {
            $sql .= " LIMIT " . intval($limit);
        }

        $sqlBindArray = $whereClause->getBoundValues();
        $statementResults =  QueryUtils::sqlStatementThrowException($sql, $sqlBindArray);
        $processingResult = new ProcessingResult();
        while ($row = sqlFetchArray($statementResults)) {
            $resultRecord = $this->createResultRecordFromDatabaseResult($row);
            $processingResult->addData($resultRecord);
        }
        return $processingResult;
    }

    protected function createResultRecordFromDatabaseResult($row)
    {
        $record = parent::createResultRecordFromDatabaseResult($row);


        // add in all the measurement fields

        $kgToLb = function ($val) {
            return number_format($val *  0.45359237, 2);
        };
        $cmToInches = function ($val) {
            return round(number_format($val * 2.54, 2), 1);
        };
        $fhToCelsius = function ($val) {
            return round(number_format(($val - 32) * (5 / 9), 1));
        };
        $identity = function ($val) {
            return $val;
        };

        $convertArrayValue = function ($index, $converter, $unit, &$array) {
            $array[$index] = $converter($array[$index]);
            $array[$index . "_unit"] = $unit;
        };

        if ($GLOBALS['units_of_measurement'] == 2 || $GLOBALS['units_of_measurement'] == 4) {
            $convertArrayValue('weight', $kgToLb, 'kg', $record);
            $convertArrayValue('height', $cmToInches, 'cm', $record);
            $convertArrayValue('head_circ', $cmToInches, 'cm', $record);
            $convertArrayValue('waist_circ', $cmToInches, 'cm', $record);
            $convertArrayValue('temperature', $fhToCelsius, 'Cel', $record);
        } else {
            $convertArrayValue('weight', $identity, 'lb', $record);
            $convertArrayValue('height', $identity, 'in', $record);
            $convertArrayValue('head_circ', $identity, 'in', $record);
            $convertArrayValue('waist_circ', $identity, 'in', $record);
            $convertArrayValue('temperature', $identity, 'degF', $record);
        }

        $convertArrayValue('pulse', $identity, '/min', $record);
        $convertArrayValue('respiration', $identity, '/min', $record);
        $convertArrayValue('BMI', $identity, 'kg/m2', $record);
        $convertArrayValue('bps', $identity, 'mm[Hg]', $record);
        $convertArrayValue('bpd', $identity, 'mm[Hg]', $record);

        $convertArrayValue('oxygen_saturation', $identity, '%', $record);
        $convertArrayValue('oxygen_flow_rate', $identity, 'L/min', $record);
        $convertArrayValue('ped_weight_height', $identity, '%', $record);
        $convertArrayValue('ped_bmi', $identity, '%', $record);
        $convertArrayValue('ped_head_circ', $identity, '%', $record);


        return $record;
    }

    public function create($record)
    {
        if (!is_array($record)) {
            throw new \InvalidArgumentException("argument must be a valid array");
        }
        return $this->insertRecord($record);
    }

    private function insertRecord($record)
    {
        $pid = $record['pid'] ?? null;
        if (!is_numeric($pid)) {
            throw new \InvalidArgumentException("pid must be a valid number");
        }
        $uuid = UuidRegistry::getRegistryForTable(self::TABLE_NAME)->createUuid();
        $sql = "insert into history_data set pid = ?, date = NOW(), uuid = ? ";
        $arraySqlBind = [$pid, $uuid];

        unset($record['pid']);
        unset($record['uuid']);

        if (!empty($record)) {
            $arraySqlBind = array_merge($arraySqlBind, array_values($record));
            $sql .= ", " . implode(", ", array_map(function ($key) {
                return "`$key` = ?";
            }, array_keys($record)));
        }
        return QueryUtils::sqlInsert($sql, $arraySqlBind);
    }

    function getUuidFields(): array
    {
        // note the uuid here is the uuid_mapping table's uuid since each column in the table has its own distinct uuid
        // in the system.
        return ['puuid', 'euuid', 'uuid', 'user_uuid'];
    }

    public function updateHistoryDataForPatientPid($pid, $new)
    {
        // grab our history data and replace any new values in the row
        $real = $this->getHistoryData($pid);
        foreach ($new as $key => $value) {
            $real[$key] = $value;
        }

        $real['id'] = "";
        // need to unset date, so can reset it below
        unset($real['date']);

        return $this->insertRecord($real);
    }
}
