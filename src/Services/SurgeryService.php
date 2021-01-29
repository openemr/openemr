<?php

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

    public function getAll($search = array(), $isAndCondition = true)
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

        $sql = "SELECT 
                slist.id,
                slist.title,
                slist.diagnosis,
                slist.uuid,
                patient.fname,
                patient.lname,
                encounter.id,
                patient.uuid AS puuid,
                encounter.uuid AS euuid 
                from lists AS slist 
                LEFT JOIN patient_data AS patient 
                ON slist.pid = patient.id 
                LEFT JOIN form_encounter AS encounter 
                ON slist.pid = encounter.pid";

        if (!empty($search)) {
            $sql .= ' WHERE ';
            $whereClauses = array();
            foreach ($search as $fieldName => $fieldValue) {
                array_push($whereClauses, $fieldName . ' = ?');
                array_push($sqlBindArray, $fieldValue);
            }
            $sqlCondition = ($isAndCondition == true) ? 'AND' : 'OR';
            $sql .= implode(' ' . $sqlCondition . ' ', $whereClauses);
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

    public function getOne($uuid)
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
                WHERE slist.uuid = ?";

        $uuidBinary = UuidRegistry::uuidToBytes($uuid);
        $sqlResult = sqlQuery($sql, [$uuidBinary]);
        $sqlResult['uuid'] = UuidRegistry::uuidToString($sqlResult['uuid']);
        $sqlResult['puuid'] = UuidRegistry::uuidToString($sqlResult['puuid']);
        $sqlResult['euuid'] = UuidRegistry::uuidToString($sqlResult['euuid']);

        $processingResult->addData($sqlResult);
        return $processingResult;
    }
}
