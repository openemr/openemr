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

use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Validators\BaseValidator;
use OpenEMR\Validators\ProcessingResult;

class SurgeryService extends BaseService
{

    private const PATIENT_TABLE = "patient_data";
    private const ENCOUNTER_TABLE = "form_encounter";
    private const SURGERY_LIST_PATIENT = "lists";
    private $uuidRegistry;
    /**
     * Default constructor.
     */
    public function __construct()
    {
        parent::__construct(self::SURGERY_LIST_PATIENT);
        $this->uuidRegistry = new UuidRegistry([
            'table_name' => self::SURGERY_LIST_PATIENT
        ]);
        $this->uuidRegistry->createMissingUuids();
        (new UuidRegistry(['table_name' => self::PATIENT_TABLE]))->createMissingUuids();
        (new UuidRegistry(['table_name' => self::ENCOUNTER_TABLE]))->createMissingUuids();
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
}
