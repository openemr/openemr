<?php

/**
 * ObservationLabService
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

class ObservationLabService extends BaseService
{

    private const PROCEDURE_RESULT_TABLE = "procedure_result";
    private const PROCEDURE_RESULT_TABLE_ID = "procedure_result_id";
    private const PATIENT_TABLE = "patient_data";
    private $uuidRegistry;

    /**
     * Default constructor.
     */
    public function __construct()
    {
        parent::__construct(self::PROCEDURE_RESULT_TABLE);
        $this->uuidRegistry = new UuidRegistry([
            'table_name' => self::PROCEDURE_RESULT_TABLE,
            'table_id' => self::PROCEDURE_RESULT_TABLE_ID
        ]);
        $this->uuidRegistry->createMissingUuids();
        (new UuidRegistry(['table_name' => self::PATIENT_TABLE]))->createMissingUuids();
    }

    /**
     * Returns a list of observation-lab matching optional search criteria.
     * Search criteria is conveyed by array where key = field/column name, value = field value.
     * If no search criteria is provided, all records are returned.
     *
     * @param  $search search array parameters
     * @param  $isAndCondition specifies if AND condition is used for multiple criteria. Defaults to true.
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @return ProcessingResult which contains validation messages, internal error messages, and the data
     * payload.
     */
    public function getAll($search = array(), $isAndCondition = true, $puuidBind = null)
    {
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

        $sqlBindArray = array();
        $sql = "SELECT presult.*,poc.procedure_name,poc.procedure_code ,
                patient.uuid AS puuid,preport.date_report
                FROM procedure_result AS presult
                LEFT OUTER JOIN procedure_report AS preport
                ON preport.procedure_report_id = presult.procedure_report_id
                LEFT OUTER JOIN procedure_order AS porder
                ON porder.procedure_order_id = preport.procedure_order_id
                LEFT OUTER JOIN procedure_order_code AS poc
                ON poc.procedure_order_id = porder.procedure_order_id
                LEFT OUTER JOIN patient_data AS patient
                ON patient.pid = porder.patient_id";

        if (!empty($search)) {
            $sql .= ' WHERE ';
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
            $sql .= " WHERE `patient`.`uuid` = ?";
            $sqlBindArray[] = UuidRegistry::uuidToBytes($puuidBind);
        }

        $statementResults = sqlStatement($sql, $sqlBindArray);

        $processingResult = new ProcessingResult();
        while ($row = sqlFetchArray($statementResults)) {
            $row['uuid'] = UuidRegistry::uuidToString($row['uuid']);
            $row['puuid'] = UuidRegistry::uuidToString($row['puuid']);
            $processingResult->addData($row);
        }

        return $processingResult;
    }

    /**
     * Returns a single observation-lab record by id.
     * @param $uuid - The observation-lab uuid identifier in string format.
     * @return ProcessingResult which contains validation messages, internal error messages, and the data
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * payload.
     */
    public function getOne($uuid, $puuidBind = null)
    {
        $processingResult = new ProcessingResult();

        $isValid = BaseValidator::validateId("uuid", self::PROCEDURE_RESULT_TABLE, $uuid, true);
        if ($isValid !== true) {
            $validationMessages = [
                'uuid' => ["invalid or nonexisting value" => " value " . $uuid]
            ];
            $processingResult->setValidationMessages($validationMessages);
            return $processingResult;
        }

        if (!empty($puuidBind)) {
            $isValid = BaseValidator::validateId("uuid", self::PATIENT_TABLE, $puuidBind, true);
            if ($isValid !== true) {
                $validationMessages = [
                    'puuid' => ["invalid or nonexisting value" => " value " . $puuidBind]
                ];
                $processingResult->setValidationMessages($validationMessages);
                return $processingResult;
            }
        }

        $sql = "SELECT presult.*,poc.procedure_name,poc.procedure_code ,
                patient.uuid AS puuid,preport.date_report
                FROM procedure_result AS presult
                LEFT OUTER JOIN procedure_report AS preport
                ON preport.procedure_report_id = presult.procedure_report_id
                LEFT OUTER JOIN procedure_order AS porder
                ON porder.procedure_order_id = preport.procedure_order_id
                LEFT OUTER JOIN procedure_order_code AS poc
                ON poc.procedure_order_id = porder.procedure_order_id
                LEFT OUTER JOIN patient_data AS patient
                ON patient.pid = porder.patient_id
                WHERE presult.uuid = ?";

        $uuidBinary = UuidRegistry::uuidToBytes($uuid);
        $sqlBindArray = [$uuidBinary];

        if (!empty($puuidBind)) {
            $sql .= " AND `patient`.`uuid` = ?";
            $sqlBindArray[] = UuidRegistry::uuidToBytes($puuidBind);
        }

        if (!empty($sqlResult)) {
            $sqlResult = sqlQuery($sql, [$uuidBinary]);
            $sqlResult['uuid'] = UuidRegistry::uuidToString($sqlResult['uuid']);
            $sqlResult['puuid'] = UuidRegistry::uuidToString($sqlResult['puuid']);
            $processingResult->addData($sqlResult);
        }
        return $processingResult;
    }
}
