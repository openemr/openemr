<?php

/**
 * ProcedureService
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Validators\BaseValidator;
use OpenEMR\Validators\ProcessingResult;

class ProcedureService extends BaseService
{

    private const PROCEDURE_TABLE = "procedure_order";
    private const PROCEDURE_TABLE_ID = "procedure_order_id";
    private const PATIENT_TABLE = "patient_data";
    private const ENCOUNTER_TABLE = "form_encounter";
    private const PRACTITIONER_TABLE = "users";
    private $uuidRegistry;

    /**
     * Default constructor.
     */
    public function __construct()
    {
        parent::__construct(self::PROCEDURE_TABLE);
        $this->uuidRegistry = new UuidRegistry([
            'table_name' => self::PROCEDURE_TABLE,
            'table_id' => self::PROCEDURE_TABLE_ID
        ]);
        $this->uuidRegistry->createMissingUuids();
        (new UuidRegistry(['table_name' => self::PATIENT_TABLE]))->createMissingUuids();
        (new UuidRegistry(['table_name' => self::ENCOUNTER_TABLE]))->createMissingUuids();
        (new UuidRegistry(['table_name' => self::PRACTITIONER_TABLE]))->createMissingUuids();
    }

    /**
     * Returns a list of procedures matching optional search criteria.
     * Search criteria is conveyed by array where key = field/column name, value = field value.
     * If no search criteria is provided, all records are returned.
     *
     * @param  $search search array parameters
     * @param  $isAndCondition specifies if AND condition is used for multiple criteria. Defaults to true.
     * @return ProcessingResult which contains validation messages, internal error messages, and the data
     * payload.
     */
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

        $sql = "SELECT porder.*,
                pcode.diagnoses,
                pcode.procedure_order_title,
                pcode.procedure_name,
                pcode.procedure_code,
                presult.result_status,
                presult.result_code,
                presult.result_text,
                presult.date,
                presult.facility,
                presult.units,
                presult.result,
                patient.uuid AS puuid,
                encounter.uuid AS euuid,
                practitioner.uuid AS pruuid
                FROM procedure_order AS porder
                LEFT JOIN procedure_order_code AS pcode
                ON porder.procedure_order_id = pcode.procedure_order_id
                LEFT JOIN procedure_report AS preport
                ON preport.procedure_order_id = porder.procedure_order_id
                LEFT JOIN procedure_result AS presult
                ON presult.procedure_report_id = preport.procedure_report_id
                LEFT JOIN patient_data AS patient
                ON patient.pid = porder.patient_id
                LEFT JOIN form_encounter AS encounter
                ON encounter.encounter = porder.encounter_id
                LEFT JOIN users AS practitioner
                ON practitioner.id = porder.provider_id";

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

        $statementResults = sqlStatement($sql, $sqlBindArray);

        $processingResult = new ProcessingResult();
        while ($row = sqlFetchArray($statementResults)) {
            $row['uuid'] = UuidRegistry::uuidToString($row['uuid']);
            $row['puuid'] = UuidRegistry::uuidToString($row['puuid']);
            $row['euuid'] = UuidRegistry::uuidToString($row['euuid']);
            $row['pruuid'] = UuidRegistry::uuidToString($row['pruuid']);
            if ($row['order_diagnosis'] != "") {
                $row['order_diagnosis'] = $this->addDiagnosis($row['order_diagnosis']);
            }
            if ($row['diagnoses'] != "") {
                $row['diagnoses'] = $this->addDiagnosis($row['diagnoses']);
            }
            $processingResult->addData($row);
        }

        return $processingResult;
    }

    /**
     * Returns a single procedure record by id.
     * @param $uuid - The procedure uuid identifier in string format.
     * @return ProcessingResult which contains validation messages, internal error messages, and the data
     * payload.
     */
    public function getOne($uuid)
    {
        $processingResult = new ProcessingResult();

        $isValid = BaseValidator::validateId("uuid", "procedure_order", $uuid, true);
        if ($isValid !== true) {
            $validationMessages = [
                'uuid' => ["invalid or nonexisting value" => " value " . $uuid]
            ];
            $processingResult->setValidationMessages($validationMessages);
            return $processingResult;
        }
        $sql = "SELECT porder.*,
                pcode.diagnoses,
                pcode.procedure_order_title,
                pcode.procedure_name,
                pcode.procedure_code,
                presult.result_status,
                presult.result_code,
                presult.result_text,
                presult.date,
                presult.facility,
                presult.units,
                presult.result,
                patient.uuid AS puuid,
                encounter.uuid AS euuid,
                practitioner.uuid AS pruuid
                FROM procedure_order AS porder
                LEFT JOIN procedure_order_code AS pcode
                ON porder.procedure_order_id = pcode.procedure_order_id
                LEFT JOIN procedure_report AS preport
                ON preport.procedure_order_id = porder.procedure_order_id
                LEFT JOIN procedure_result AS presult
                ON presult.procedure_report_id = preport.procedure_report_id
                LEFT JOIN patient_data AS patient
                ON patient.pid = porder.patient_id
                LEFT JOIN form_encounter AS encounter
                ON encounter.encounter = porder.encounter_id
                LEFT JOIN users AS practitioner
                ON practitioner.id = porder.provider_id
                WHERE porder.uuid = ?";

        $uuidBinary = UuidRegistry::uuidToBytes($uuid);
        $sqlResult = sqlQuery($sql, [$uuidBinary]);
        $sqlResult['uuid'] = UuidRegistry::uuidToString($sqlResult['uuid']);
        $sqlResult['puuid'] = UuidRegistry::uuidToString($sqlResult['puuid']);
        $sqlResult['euuid'] = UuidRegistry::uuidToString($sqlResult['euuid']);
        $sqlResult['pruuid'] = UuidRegistry::uuidToString($sqlResult['pruuid']);
        if ($sqlResult['order_diagnosis'] != "") {
            $sqlResult['order_diagnosis'] = $this->addDiagnosis($sqlResult['order_diagnosis']);
        }
        if ($sqlResult['diagnoses'] != "") {
            $sqlResult['diagnoses'] = $this->addDiagnosis($sqlResult['diagnoses']);
        }
        $processingResult->addData($sqlResult);
        return $processingResult;
    }

    public function addDiagnosis($data)
    {
        $diagnosisArray = array();
        $dataArray = explode(";", $data);
        foreach ($dataArray as $diagnosis) {
            $diagnosisSplit = explode(":", $diagnosis);
            array_push($diagnosisArray, $diagnosisSplit);
        }
        return $diagnosisArray;
    }
}
