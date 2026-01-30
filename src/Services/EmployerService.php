<?php

/**
 * EmployerService handles the data retrieval for the employer data for a patient
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2024 Care Management Solutions, Inc. <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\Search\FhirSearchWhereClauseBuilder;
use OpenEMR\Services\Search\SearchQueryConfig;
use OpenEMR\Validators\ProcessingResult;

class EmployerService extends BaseService
{
    const TABLE_NAME = "employer_data";
    private UuidRegistry $uuidRegistry;

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME);
    }

    public function getUuidFields(): array
    {
        return ['uuid', 'puuid', 'user_uuid'];
    }

    // we want to grab the puuid from the patient table so people can search on it
    public function getSelectFields(string $tableAlias = '', string $columnPrefix = ""): array
    {
        $fields = parent::getSelectFields($tableAlias, $columnPrefix);
        $fields[] = '`patient`.`puuid`';
        return $fields;
    }
    // used in the search clause for EmployerService
    public function getSelectJoinTables(): array
    {
        return [
            'patient' => [
                'type' => 'INNER JOIN',
                'table' => '(select `pid` AS `patient_pid`, `uuid` AS puuid FROM `patient_data`)',
                'alias' => 'patient',
                'join_clause' => '`patient`.`patient_pid` = `' . self::TABLE_NAME . '`.`pid`',
            ]
        ];
    }

    /**
     * @param $openEMRSearchParameters
     * @param $isAndCondition
     * @return \OpenEMR\Validators\ProcessingResult
     */
    public function search($openEMRSearchParameters, $isAndCondition = true): ProcessingResult
    {
        try {
            $sql = "SELECT
                    emp.id, emp.uuid, emp.name, emp.street, emp.street_line_2, emp.postal_code, emp.city, emp.state, emp.country
                    ,emp.date, emp.start_date, emp.end_date
                    ,emp.pid, p.puuid
                    ,emp.created_by, u.user_uuid
                    ,emp.occupation, lo_occ.occupation_codes, lo_ind.industry_codes_display
                    , emp.industry, lo_ind.industry_codes, lo_occ.occupation_codes_display
                FROM employer_data emp
                LEFT JOIN (
                    select
                        id, uuid AS user_uuid
                    FROM users u
                ) u ON u.id = emp.created_by
                LEFT JOIN (
                    select
                        pid AS patient_pid,
                        uuid AS puuid
                    FROM patient_data
                ) p ON p.patient_pid = emp.pid
                LEFT JOIN (
                    select
                        option_id AS occupation_option_id,
                        codes AS occupation_codes,
                        title AS occupation_codes_display
                    FROM list_options
                    WHERE list_id = 'OccupationODH'
                ) lo_occ ON lo_occ.occupation_option_id = emp.occupation
                LEFT JOIN (
                    select
                        option_id AS industry_option_id,
                        codes AS industry_codes,
                        title AS industry_codes_display
                    FROM list_options
                    WHERE list_id = 'IndustryODH'
                ) lo_ind ON lo_ind.industry_option_id = emp.industry
            ";
            $whereClause = FhirSearchWhereClauseBuilder::build($openEMRSearchParameters, $isAndCondition);
            $sql .= $whereClause->getFragment();
            $results = QueryUtils::fetchRecords($sql, $whereClause->getBoundValues());
            $processingResult = new ProcessingResult();
            foreach ($results as $record) {
                $processingResult->addData($this->createResultRecordFromDatabaseResult($record));
            }
        } catch (SqlQueryException $exception) {
            $processingResult = new ProcessingResult();
            $processingResult->addInternalError($exception->getMessage());
        }
        return $processingResult;
    }

    public function updateEmployerData($pid, $new, $create = false, ?array $patientData = null)
    {
        // employer_data keeps a history of changes so by inserting occupation and industry we keep a record
        // of what the patient had at the time of the change. If patientData is passed in, use it to set occupation and industry.
        // at some point we may want to consider moving occupation and industry completely over, but this has the most minimal impact.
        if ($patientData !== null) {
            $new['occupation'] = empty($patientData['occupation']) ? '' : $patientData['occupation'];
            $new['industry'] = empty($patientData['industry']) ? '' : $patientData['industry'];
        }

        $new['pid'] = $pid;
        $createdBy = $_SESSION['authUserID'] ?? null; // we don't let anyone else but the current user be the createdBy
        $new['created_by'] = $createdBy;

        if (!$create) {
            $old = $this->getMostRecentEmployerData($pid);
            $valuesToSave = [];
            foreach ($old as $key => $oldValue) {
                $newValue = empty($new[$key]) ? '' : $new[$key];
                $valuesToSave[$key] = strcmp((string) $newValue, (string) $oldValue) != 0 ? $newValue : $oldValue;
            }
            $new = $valuesToSave;
        }
        // we ignore any date that is passed in, we always set it to now
        if (isset($new['date'])) {
            unset($new['date']);
        }
        if (!empty($new)) {
            $uuid = $this->getUuidRegistry()->createUuid();
            $new['uuid'] = $uuid;
            $insert = $this->buildInsertColumns($new);
            $sql = "INSERT INTO employer_data SET " . $insert['set'] . ", `date` = NOW()";
            $insert = QueryUtils::sqlInsert($sql, $insert['bind']);
            return $insert;
        } else {
            return '';
        }
    }

    // To prevent sql injection on this function, if a variable is used for $given parameter, then
    // it needs to be escaped via whitelisting prior to using this function.
    public function getMostRecentEmployerData($pid, $given = "*"): array|false
    {
        $sql = "select $given from employer_data where pid = ? order by date DESC limit 0,1";
        $resultSet = QueryUtils::fetchRecords($sql, [$pid]);
        if (!empty($resultSet)) {
            return $resultSet[0];
        } else {
            return false;
        }
    }

    public function getUuidRegistry(): UuidRegistry
    {
        if (!isset($this->uuidRegistry)) {
            $this->uuidRegistry = new UuidRegistry(['table_name' => self::TABLE_NAME]);
        }
        return $this->uuidRegistry;
    }

    public function setUuidRegistry(UuidRegistry $uuidRegistry)
    {
        $this->uuidRegistry = $uuidRegistry;
    }
}
