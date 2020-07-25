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
use OpenEMR\Validators\ProcedureValidator;
use OpenEMR\Validators\ProcessingResult;

class ProcedureService extends BaseService
{

    private const PROCEDURE_TABLE = "procedures";
    private const PATIENT_TABLE = "patient_data";
    private $procedureValidator;
    private $uuidRegistery;

    /**
     * Default constructor.
     */
    public function __construct()
    {
        parent::__construct(self::PROCEDURE_TABLE);
        $this->uuidRegistery = new UuidRegistry(['table_name' => self::PROCEDURE_TABLE]);
        $this->uuidRegistery->createMissingUuids();
        (new UuidRegistry(['table_name' => self::PATIENT_TABLE]))->createMissingUuids();
        $this->procedureValidator = new ProcedureValidator();
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
            $isValidEncounter = $this->procedureValidator->validateId(
                'uuid',
                self::PATIENT_TABLE,
                $search['patient.uuid'],
                true
            );
            if ($isValidEncounter !== true) {
                return $isValidEncounter;
            }
            $search['patient.uuid'] = UuidRegistry::uuidToBytes($search['patient.uuid']);
        }

        $sql = "SELECT ptype.procedure_code,
                ptype.body_site,
                ptype.notes,
                pcode.procedure_name,
                porder.procedure_order_id,
                porder.patient_id,
                porder.encounter_id,
                porder.date_collected,
                porder.provider_id,
                porder.order_diagnosis,
                porder.order_status,
                presult.result_status
                FROM procedure_order AS porder 
                LEFT JOIN procedure_order_code AS pcode 
                ON porder.procedure_order_id = pcode.procedure_order_id 
                LEFT JOIN procedure_type AS ptype 
                ON pcode.procedure_code = ptype.procedure_code 
                LEFT JOIN procedure_report AS preport 
                ON preport.procedure_order_id = porder.procedure_order_id 
                LEFT JOIN procedure_result AS presult 
                ON presult.procedure_report_id = preport.procedure_report_id";

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

        $isValid = $this->procedureValidator->validateId("uuid", "procedures", $uuid, true);
        if ($isValid !== true) {
            $validationMessages = [
                'uuid' => ["invalid or nonexisting value" => " value " . $uuid]
            ];
            $processingResult->setValidationMessages($validationMessages);
            return $processingResult;
        }

        $sql = "SELECT procedures.id,
                        procedures.uuid,
                        patient.uuid as puuid,
                        administered_date,
                        cvx_code,
                        cvx.code_text as cvx_code_text,
                        manufacturer,
                        lot_number,
                        added_erroneously,
                        administered_by_id,
                        administered_by,
                        education_date,
                        note,
                        create_date,
                        amount_administered,
                        amount_administered_unit,
                        expiration_date,
                        route,
                        administration_site,
                        site.title as site_display,
                        site.notes as site_code,
                        completion_status,
                        refusal_reason,
                        IF(
                            IF(
                                information_source = 'new_procedure_record' AND
                                IF(administered_by IS NOT NULL OR administered_by_id IS NOT NULL, TRUE, FALSE),
                                TRUE,
                                FALSE
                            ) OR
                            IF(
                                information_source = 'other_provider' OR 
                                information_source = 'birth_certificate' OR 
                                information_source = 'school_record' OR 
                                information_source = 'public_agency',
                                TRUE,
                                FALSE
                            ),
                            TRUE,
                            FALSE
                        ) as primarySource
                        FROM procedures
                        LEFT JOIN patient_data as patient ON procedures.patient_id = patient.pid
                        LEFT JOIN codes as cvx ON cvx.code = procedures.cvx_code
                        LEFT JOIN list_options as site ON site.option_id = procedures.administration_site
                        WHERE procedures.uuid = ?";

        $uuidBinary = UuidRegistry::uuidToBytes($uuid);
        $sqlResult = sqlQuery($sql, [$uuidBinary]);
        $sqlResult['uuid'] = UuidRegistry::uuidToString($sqlResult['uuid']);
        $sqlResult['puuid'] = UuidRegistry::uuidToString($sqlResult['puuid']);
        $processingResult->addData($sqlResult);
        return $processingResult;
    }
}
