<?php

/**
 * SurgeryService
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Shubham Pandey <shubham.pandey1706gmail.com>
 * @copyright Copyright (c) 2021 Shubham Pandey <shubham.pandey1706gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\Search\FhirSearchWhereClauseBuilder;
use OpenEMR\Services\Search\SearchModifier;
use OpenEMR\Services\Search\StringSearchField;
use OpenEMR\Validators\BaseValidator;
use OpenEMR\Validators\ProcessingResult;

class SurgeryService extends BaseService
{
    private const PATIENT_TABLE = "patient_data";
    private const ENCOUNTER_TABLE = "form_encounter";
    private const SURGERY_LIST_PATIENT = "lists";

    /**
     * Default constructor.
     */
    public function __construct()
    {
        parent::__construct(self::SURGERY_LIST_PATIENT);
        UuidRegistry::createMissingUuidsForTables([self::SURGERY_LIST_PATIENT, self::PATIENT_TABLE, self::ENCOUNTER_TABLE]);
    }

    public function getUuidFields(): array
    {
        return ['uuid', 'euuid', 'puuid', 'recorder_uuid'];
    }

    public function search($search, $isAndCondition = true)
    {
        $sql = "SELECT
                    surgeries.id,
                    surgeries.`date`,
                    surgeries.`begdate`,
                    surgeries.`enddate`,
                    surgeries.title,
                    surgeries.diagnosis,
                    surgeries.uuid,
                    surgeries.`comments`,
                    patient.fname,
                    patient.lname,
                    patient.puuid,
                    encounter.eid,
                    encounter.euuid,
                    recorders.recorder_npi,
                    recorders.recorder_uuid,
                    recorders.recorder_username
                FROM (
                    SELECT
                        id
                        ,`date`
                        ,`begdate`
                        ,`enddate`
                        ,`title`
                        ,`diagnosis`
                        ,`uuid`
                        ,`pid`
                        ,`comments`
                        ,`user` as surgery_recorder
                    FROM lists
                    WHERE
                        `type` = 'surgery'
                ) surgeries
                LEFT JOIN (
                    SELECT
                        fname,
                        lname,
                        uuid AS puuid,
                        pid
                    FROM
                        patient_data
                ) patient ON surgeries.pid = patient.pid
                LEFT JOIN (
                    select
                           uuid AS recorder_uuid
                            ,username AS recorder_username
                            ,id AS recorder_id
                            ,npi AS recorder_npi
                    FROM users
                ) recorders ON recorders.recorder_username = surgeries.surgery_recorder
                LEFT JOIN (
                    SELECT
                        pid AS ie_pid
                        ,list_id AS ie_list_id
                        ,encounter AS ie_encounter_id
                    FROM
                        issue_encounter
                ) list_encounters ON list_encounters.ie_list_id = surgeries.id
                                         AND list_encounters.ie_pid = patient.pid
                LEFT JOIN (
                    SELECT
                        id AS eid
                        ,uuid AS euuid
                        ,pid AS encounter_pid
                    FROM form_encounter
                ) encounter
                ON list_encounters.ie_encounter_id = encounter.eid";

        $whereClause = FhirSearchWhereClauseBuilder::build($search, $isAndCondition);

        $sql .= $whereClause->getFragment();
        $sqlBindArray = $whereClause->getBoundValues();
        $statementResults =  QueryUtils::sqlStatementThrowException($sql, $sqlBindArray);
        $processingResult = new ProcessingResult();
        while ($row = sqlFetchArray($statementResults)) {
            $record = $this->createResultRecordFromDatabaseResult($row);
            $processingResult->addData($record);
        }

        return $processingResult;
    }

    /**
     * Returns a list of surgeries matching optional search criteria.
     * Search criteria is conveyed by array where key = field/column name, value = field value.
     * If no search criteria is provided, all records are returned.
     *
     * @param  $search search array parameters
     * @param  $isAndCondition specifies if AND condition is used for multiple criteria. Defaults to true.
     * @return ProcessingResult which contains validation messages, internal error messages, and the data
     * payload.
     */
    public function getAll($search = array(), $isAndCondition = true, $puuidBind = null)
    {
        $sqlBindArray = array();

        if (isset($search['patient.uuid'])) {
            $isValidPatient = BaseValidator::validateId(
                'uuid',
                self::PATIENT_TABLE,
                $search['patient.uuid'],
                true
            );
            if ($isValidPatient !== true) {
                return $isValidPatient;
            }
            $search['patient.uuid'] = UuidRegistry::uuidToBytes($search['patient.uuid']);
        }

        if (!empty($puuidBind)) {
            // code to support patient binding
            $isValidPatient = BaseValidator::validateId(
                'uuid',
                self::PATIENT_TABLE,
                $puuidBind,
                true
            );
            if ($isValidPatient !== true) {
                return $isValidPatient;
            }
        }

        $sql = "SELECT
                slist.id,
                slist.title,
                slist.diagnosis,
                slist.uuid,
                slist.type,
                patient.fname,
                patient.lname,
                encounter.id,
                patient.uuid AS puuid,
                encounter.uuid AS euuid
                from lists AS slist
                LEFT JOIN patient_data AS patient
                ON slist.pid = patient.id
                LEFT JOIN form_encounter AS encounter
                ON slist.pid = encounter.pid
                WHERE slist.type = 'surgery'";

        if (!empty($search)) {
            $sql .= ' AND ';
            if (!empty($puuidBind)) {
                // code to support patient binding
                $sql .= '(';
            }
            $whereClauses = array();
            foreach ($search as $fieldName => $fieldValue) {
                array_push($whereClauses, $fieldName . ' = ?');
                array_push($sqlBindArray, $fieldValue);
            }
            $sqlCondition = ($isAndCondition == true) ? 'AND' : 'OR';
            $sql .= implode(' ' . $sqlCondition . ' ', $whereClauses);
            if (!empty($puuidBind)) {
                // code to support patient binding
                $sql .= ") AND `patient`.`uuid` = ?";
                $sqlBindArray[] = UuidRegistry::uuidToBytes($puuidBind);
            }
        } elseif (!empty($puuidBind)) {
            // code to support patient binding
            $sql .= " AND `patient`.`uuid` = ?";
            $sqlBindArray[] = UuidRegistry::uuidToBytes($puuidBind);
        }
        $statementResult = sqlStatement($sql, $sqlBindArray);

        $processingResult = new ProcessingResult();

        while ($row = sqlFetchArray($statementResult)) {
            $row['uuid'] = UuidRegistry::uuidToString($row['uuid']);
            $row['puuid'] = UuidRegistry::uuidToString($row['puuid']);
            $row['euuid'] = UuidRegistry::uuidToString($row['euuid']);
            $processingResult->addData($row);
        }

        return $processingResult;
    }

    /**
     * Returns a single surgery record by id.
     * @param $uuid - The procedure uuid identifier in string format.
     * @return ProcessingResult which contains validation messages, internal error messages, and the data
     * payload.
     */
    public function getOne($uuid, $puuidBind = null)
    {
        $processingResult = new ProcessingResult();

        $isValid = BaseValidator::validateId("uuid", "lists", $uuid, true);
        if ($isValid !== true) {
            $validationMessages = [
                'uuid' => ["invalid or nonexisting value" => " value " . $uuid]
            ];
            $processingResult->setValidationMessages($validationMessages);
            return $processingResult;
        }

        if (!empty($puuidBind)) {
            // code to support patient binding
            $isValid = BaseValidator::validateId("uuid", self::PATIENT_TABLE, $puuidBind, true);
            if ($isValid !== true) {
                $validationMessages = [
                    'puuid' => ["invalid or nonexisting value" => " value " . $puuidBind]
                ];
                $processingResult->setValidationMessages($validationMessages);
                return $processingResult;
            }
        }

        $sql = "SELECT
                slist.id,
                slist.title,
                slist.diagnosis,
                slist.uuid,
                encounter.id,
                patient.fname,
                patient.lname,
                encounter.id,
                patient.uuid AS puuid,
                encounter.uuid AS euuid
                FROM lists AS slist
                LEFT JOIN patient_data AS patient
                ON slist.pid = patient.id
                LEFT JOIN form_encounter AS encounter
                ON slist.pid = encounter.pid
                WHERE slist.type = 'surgery' AND slist.uuid = ?";

        $uuidBinary = UuidRegistry::uuidToBytes($uuid);
        $sqlBindArray = [$uuidBinary];

        if (!empty($puuidBind)) {
            // code to support patient binding
            $sql .= " AND `patient`.`uuid` = ?";
            $sqlBindArray[] = UuidRegistry::uuidToBytes($puuidBind);
        }

        $sqlResult = sqlQuery($sql, $sqlBindArray);
        if (!empty($sqlResult)) {
            $sqlResult['uuid'] = UuidRegistry::uuidToString($sqlResult['uuid']);
            $sqlResult['puuid'] = UuidRegistry::uuidToString($sqlResult['puuid']);
            $sqlResult['euuid'] = UuidRegistry::uuidToString($sqlResult['euuid']);
            $processingResult->addData($sqlResult);
        }
        return $processingResult;
    }

    protected function createResultRecordFromDatabaseResult($row)
    {
        $record = parent::createResultRecordFromDatabaseResult($row); // TODO: Change the autogenerated stub
        if (empty($record['enddate'])) {
            $record['status'] = 'inactive';
        } else {
            $record['status'] = 'active';
        }
        return $record;
    }
}
