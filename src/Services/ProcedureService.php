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

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\Search\CompositeSearchField;
use OpenEMR\Services\Search\FhirSearchWhereClauseBuilder;
use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Services\Search\SearchModifier;
use OpenEMR\Services\Search\StringSearchField;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Services\Search\TokenSearchValue;
use OpenEMR\Validators\BaseValidator;
use OpenEMR\Validators\ProcessingResult;

class ProcedureService extends BaseService
{
    private const PROCEDURE_TABLE = "procedure_order";
    private const PATIENT_TABLE = "patient_data";
    private const ENCOUNTER_TABLE = "form_encounter";
    private const PRACTITIONER_TABLE = "users";
    private const PROCEDURE_PROVIDER_TABLE = "procedure_providers";
    private const PROCEDURE_REPORT_TABLE = "procedure_report";
    private const PROCEDURE_RESULT_TABLE = "procedure_result";

    /**
     * Default constructor.
     */
    public function __construct()
    {
        parent::__construct(self::PROCEDURE_TABLE);
        UuidRegistry::createMissingUuidsForTables([self::PROCEDURE_TABLE, self::PATIENT_TABLE, self::ENCOUNTER_TABLE
            , self::PRACTITIONER_TABLE, self::PROCEDURE_REPORT_TABLE, self::PROCEDURE_PROVIDER_TABLE
            , self::PROCEDURE_RESULT_TABLE]);
    }

    public function getUuidFields(): array
    {
        return ['result_uuid','report_uuid', 'lab_uuid','puuid', 'order_uuid', 'euuid', 'provider_uuid'];
    }

    public function search($search, $isAndCondition = true)
    {
        // note that these are Laboratory tests & values/results as mapped in USCDI Data elements v1
        // @see https://www.healthit.gov/isa/sites/isa/files/2020-07/USCDI-Version-1-July-2020-Errata-Final.pdf
        // To see the mappings you can see here: https://www.hl7.org/fhir/us/core/general-guidance.html
        $sql = "SELECT
                    porder.order_uuid
                    ,porder.order_provider_id
                    ,porder.order_activity
                    ,porder.order_diagnosis
                    ,porder.order_encounter_id
                    ,porder.order_lab_id
     
                    ,preport.report_date
                    ,preport.procedure_report_id
                    ,preport.report_uuid
                    ,preport.report_notes
       
                    ,presult.procedure_result_id
                    ,presult.result_uuid 
                    ,presult.result_code
                    ,presult.result_text
                    ,presult.result_units
                    ,presult.result_result
                    ,presult.result_range
                    ,presult.result_abnormal
                    ,presult.result_comments
                    ,presult.result_status
     
                    ,order_codes.procedure_name
                    ,order_codes.procedure_code
                    ,order_codes.procedure_type
     
                    ,pcode_types.standard_code

                    ,labs.lab_id
                    ,labs.lab_uuid
                    ,labs.lab_npi
                    ,labs.lab_name
     
                    ,patients.puuid
                    ,patients.pid

                    ,encounters.eid
                    ,encounters.euuid
                    ,encounters.encounter_date

                    ,docs.doc_id
                    ,docs.doc_uuid
                    
                    ,provider.provider_uuid
                    ,provider.provider_id
                    ,provider.provider_fname
                    ,provider.provider_mname
                    ,provider.provider_lname
                    ,provider.provider_npi
                FROM (
                    SELECT
                        date_report AS report_date
                        ,procedure_report_id
                        ,procedure_order_id
                        ,procedure_order_seq
                        ,uuid AS report_uuid
                        ,report_notes
                    FROM
                    procedure_report
                ) preport
                LEFT JOIN (
                    SELECT 
                        procedure_result_id
                         ,procedure_report_id
                         ,uuid AS result_uuid
                        ,result AS result_quantity
                        ,result AS result_string
                        ,result AS result_result
                        ,units AS result_units
                        ,result_status
                        ,result_code
                        ,result_text
                        ,result_data_type
                        ,`range` AS result_range
                        ,`abnormal` AS result_abnormal
                        ,`comments` AS result_comments
                        ,`document_id` AS result_document_id
                    FROM
                        `procedure_result`
                ) presult
                ON 
                    preport.procedure_report_id = presult.procedure_report_id
                LEFT JOIN (
                    SELECT 
                        procedure_order_id
                        ,uuid AS order_uuid
                        ,provider_id AS order_provider_id
                        ,encounter_id AS order_encounter_id
                        ,activity AS order_activity
                        ,order_diagnosis
                        ,lab_id as order_lab_id
                        ,procedure_order_id AS order_id
                        ,patient_id AS order_patient_id
                        ,provider_id
                    FROM
                        procedure_order
                ) porder
                ON 
                    porder.procedure_order_id = preport.procedure_order_id
                LEFT JOIN
                (
                     select
                        encounter AS eid
                        ,uuid AS euuid
                        ,`date` AS encounter_date
                    FROM
                        form_encounter
                ) encounters ON porder.order_encounter_id = encounters.eid
                LEFT JOIN
                    (
                        SELECT
                               ppid AS lab_id
                               ,uuid AS lab_uuid
                               ,npi AS lab_npi
                               ,`name` AS lab_name
                               ,`active` AS lab_active
                        FROM 
                             procedure_providers
                    ) labs
                ON 
                    labs.lab_id = porder.order_lab_id
                LEFT JOIN 
                    (
                        select
                            procedure_order_id
                        ,procedure_order_seq
                        ,procedure_code
                        ,procedure_name
                        -- we exclude the legacy procedure_type and use procedure_order_title
                        ,procedure_order_title AS procedure_type
                        FROM procedure_order_code
                    )
                    order_codes
                ON 
                    order_codes.procedure_order_id = porder.procedure_order_id AND order_codes.procedure_order_seq = preport.procedure_order_seq
                LEFT JOIN (
                    select
                        standard_code,
                        procedure_code AS proc_code
                    FROM procedure_type
                ) pcode_types ON order_codes.procedure_code = pcode_types.proc_code
                LEFT JOIN (
                    select 
                        pid
                        ,uuid AS puuid
                    FROM
                        patient_data
                ) patients
                ON 
                    patients.pid = porder.order_patient_id 
                
                LEFT JOIN (
                    select 
                       id AS doc_id
                       ,uuid AS doc_uuid
                    FROM
                        documents
                ) docs ON presult.result_document_id = docs.doc_id
                LEFT JOIN (
                    SELECT
                        users.uuid AS provider_uuid
                        ,users.id AS provider_id
                        ,users.fname AS provider_fname
                        ,users.mname AS provider_mname
                        ,users.lname AS provider_lname
                        ,users.npi AS provider_npi
                    FROM users
                    WHERE npi IS NOT NULL AND npi != ''
                ) provider ON provider.provider_id = porder.provider_id ";

        $excludeDNR_TNP = new StringSearchField('result_string', ['DNR','TNP'], SearchModifier::NOT_EQUALS_EXACT, true);
        if (isset($search['result_string']) && $search['result_string'] instanceof ISearchField) {
            $compoundColumn = new CompositeSearchField('result_string', [], true);
            $compoundColumn->addChild($search['result_string']);
            $compoundColumn->addChild($excludeDNR_TNP);
            $search['result_string'] = $compoundColumn;
        } else {
            $compoundColumn = new CompositeSearchField('result_string', [], false);
            // we have to have an optional is null due to the way the joins are setup.
            $resultIsNull = new TokenSearchField('result_string', [new TokenSearchValue(true)]);
            $resultIsNull->setModifier(SearchModifier::MISSING);
            $compoundColumn->addChild($resultIsNull);
            $compoundColumn->addChild($excludeDNR_TNP);
            $search['result_string'] = $compoundColumn;
        }

        $whereClause = FhirSearchWhereClauseBuilder::build($search, $isAndCondition);

        $sql .= $whereClause->getFragment();
        $sqlBindArray = $whereClause->getBoundValues();
        $statementResults =  QueryUtils::sqlStatementThrowException($sql, $sqlBindArray);

        $processingResult = $this->hydrateSearchResultsFromQueryResource($statementResults);
        return $processingResult;
    }

    public function searchProcedureReports($search, $isAndCondition)
    {
        $query = "SELECT CONCAT_WS('',po.procedure_order_id,poc.`procedure_order_seq`) AS tcode,
                      prs.result AS result_value,
                      prs.units, prs.range,
                      poc.procedure_name AS order_title,
                      prs.result_code as result_code,
                      prs.result_text as result_desc,
                      po.date_ordered,
                      prs.date AS result_time,
                      prs.abnormal AS abnormal_flag,
                      prs.procedure_result_id AS result_id
               FROM procedure_order AS po
               JOIN procedure_order_code AS poc ON poc.`procedure_order_id`=po.`procedure_order_id`
               JOIN procedure_report AS pr ON pr.procedure_order_id = po.procedure_order_id
                    AND pr.`procedure_order_seq`=poc.`procedure_order_seq`
               JOIN procedure_result AS prs ON prs.procedure_report_id = pr.procedure_report_id
               WHERE po.patient_id = ? AND prs.result NOT IN ('DNR','TNP')";
    }

    private function hydrateSearchResultsFromQueryResource($queryResource)
    {
        $processingResult = new ProcessingResult();
        $procedureByUuid = [];
        $reportsByUuid = [];
        $procedures = [];
        while ($row = sqlFetchArray($queryResource)) {
            $record = $this->createResultRecordFromDatabaseResult($row);
            $procedureUuid = $record['order_uuid'];
            if (!isset($procedureByUuid[$procedureUuid])) {
                // setup the table here
                $procedure = [
                    'name' => $record['procedure_name']
                    ,'uuid' => $record['order_uuid']
                    , 'code' => $record['procedure_code']
                    , 'standard_code' => $record['standard_code']
                    , 'diagnosis' => $record['order_diagnosis']
                    , 'activity' => $record['order_activity']

                    , 'reports' => []
                ];
                if (!empty($record['provider_id'])) {
                    $procedure['provider'] = [
                        'id' => $record['provider_id']
                        ,'uuid' => $record['provider_uuid']
                        ,'fname' => $record['fname']
                        ,'mname' => $record['mname']
                        ,'lname' => $record['lname']
                        ,'npi' => $record['npi']
                    ];
                }
                if (!empty($record['lab_id'])) {
                    $procedure['lab'] = [
                        'id' => $record['lab_id'] ?? null
                        ,'uuid' => $record['lab_uuid'] ?? null
                        ,'name' => $record['lab_name'] ?? null
                        ,'npi' => $record['lab_npi'] ?? null
                    ];
                }
                if (!empty($record['pid'])) {
                    $procedure['patient'] = [
                        'pid' => $record['pid']
                        ,'uuid' => $record['puuid']
                    ];
                }
                if (!empty($record['eid'])) {
                    $procedure['encounter'] = [
                        'id' => $record['eid']
                        ,'uuid' => $record['euuid']
                        ,'date' => $record['encounter_date']
                    ];
                }
                $procedures[] = $procedureUuid;
            } else {
                $procedure = $procedureByUuid[$procedureUuid];
            }

            $reportUuid = $record['report_uuid'];
            if (!isset($reportsByUuid[$reportUuid])) {
                $report = [
                    'date' => $record['report_date']
                    , 'id' => $record['procedure_report_id']
                    , 'uuid' => $record['report_uuid']
                    , 'notes' => $record['report_notes']
                    , 'results' => []
                ];
                $procedure['reports'][] = $reportUuid;
            }
            // now add our result
            /**
             *  presult.procedure_result_id
             * ,presult.uuid
             * ,presult.result_code
             * ,presult.result_text
             * ,presult.units
             * ,presult.result
             * ,presult.range
             * ,presult.abnormal
             * ,presult.comments
             * ,presult.document_id
             * ,presult.result_status
             */
            if (!empty($record['procedure_result_id'])) {
                $result = [
                    'id' => $record['procedure_result_id']
                    , 'uuid' => $record['result_uuid']
                    , 'code' => $record['result_code']
                    , 'text' => $record['result_text']
                    , 'units' => $record['result_units']
                    , 'result' => $record['result_result']
                    , 'range' => $record['result_range']
                    , 'abnormal' => $record['result_abnormal']
                    , 'comments' => $record['result_comments']
                    , 'document_id' => $record['result_document_id']
                    , 'status' => $record['result_status']
                ];
                $report['results'][] = $result;
            }
            // need to copy back in since we don't have a copy by reference here
            $reportsByUuid[$reportUuid] = $report;
            $procedureByUuid[$procedureUuid] = $procedure;
        }


        // now go through our ordered list of procedures and let's map the procedure uuids to the actual procedures
        // map each of the procedure report uuids to their corresponding report arrays
        // TODO: if we want to optimize all of this for memory efficiency we should probably use objects instead of arrays
        // that way we can eliminate the implicy copy by reference of php arrays.  This may not be a problem in future
        // versions of php.
        foreach ($procedures as $uuid) {
            $procedure = $procedureByUuid[$uuid];
            $procedure['reports'] = array_map(function ($reportUuid) use ($reportsByUuid) {
                return $reportsByUuid[$reportUuid];
            }, $procedure['reports']);
            $processingResult->addData($procedure);
        }
        return $processingResult;
    }

    public function createResultRecordFromDatabaseResult($row)
    {

        return parent::createResultRecordFromDatabaseResult($row); // TODO: Change the autogenerated stub
    }

    /**
     * Returns a list of procedures matching optional search criteria.
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
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @return ProcessingResult which contains validation messages, internal error messages, and the data
     * payload.
     */
    public function getOne($uuid, $puuidBind = null)
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
            $sqlResult['pruuid'] = UuidRegistry::uuidToString($sqlResult['pruuid']);
            if ($sqlResult['order_diagnosis'] != "") {
                $sqlResult['order_diagnosis'] = $this->addDiagnosis($sqlResult['order_diagnosis']);
            }
            if ($sqlResult['diagnoses'] != "") {
                $sqlResult['diagnoses'] = $this->addDiagnosis($sqlResult['diagnoses']);
            }
            $processingResult->addData($sqlResult);
        }
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
