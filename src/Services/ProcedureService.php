<?php

/**
 * ProcedureService
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786gmail.com>
 * @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
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
    private const PROCEDURE_SPECIMEN_TABLE = "procedure_specimen";
    private readonly ProcedureOrderRelationshipService $relationshipService;

    public function __construct()
    {
        parent::__construct(self::PROCEDURE_TABLE);
        $this->relationshipService = new ProcedureOrderRelationshipService();
        UuidRegistry::createMissingUuidsForTables([
            self::PROCEDURE_TABLE,
            self::PATIENT_TABLE,
            self::ENCOUNTER_TABLE,
            self::PRACTITIONER_TABLE,
            self::PROCEDURE_REPORT_TABLE,
            self::PROCEDURE_PROVIDER_TABLE,
            self::PROCEDURE_RESULT_TABLE,
            self::PROCEDURE_SPECIMEN_TABLE
        ]);
    }

    /**
     * @return string[]
     */
    public function getUuidFields(): array
    {
        return ['order_uuid', 'result_uuid', 'report_uuid', 'lab_uuid', 'puuid', 'euuid', 'provider_uuid', 'specimen_uuid'];
    }

    /**
     * Returns a list of procedures matching optional search criteria.
     * Search criteria is conveyed by array where key = field/column name, value = field value.
     * If no search criteria is provided, all records are returned.
     *
     * @param  $search         search array parameters
     * @param  $isAndCondition specifies if AND condition is used for multiple criteria. Defaults to true.
     * @return ProcessingResult which contains validation messages, internal error messages, and the data
     *                         payload.
     */
    public function search($search, $isAndCondition = true)
    {
        // Query structure: Start from procedure_order (the ServiceRequest)
        // and LEFT JOIN all related data so orders without reports/results still appear

        $sql = "SELECT
        porder.order_uuid
        ,porder.order_uuid AS uuid
        ,porder.procedure_order_id
        ,porder.order_provider_id
        ,porder.order_activity
        ,porder.order_activity AS activity
        ,porder.order_diagnosis
        ,porder.order_encounter_id
        ,porder.order_lab_id
        ,porder.order_patient_id
        ,porder.provider_id
        ,porder.date_ordered
        ,porder.date_collected
        ,porder.order_status
        ,porder.order_priority
        ,porder.patient_instructions
        ,porder.clinical_hx
        ,porder.procedure_order_type
        ,porder.scheduled_date
        ,porder.scheduled_start
        ,porder.scheduled_end
        ,porder.performer_type
        ,porder.order_intent
        ,porder.location_id
        ,porder.specimen_fasting

        ,preport.report_date
        ,preport.procedure_report_id
        ,preport.report_uuid
        ,preport.report_notes
        ,preport.procedure_order_seq

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
        ,order_codes.procedure_order_seq AS order_code_seq
        ,order_codes.diagnoses

        ,pcode_types.standard_code

        ,labs.lab_id
        ,labs.lab_uuid
        ,labs.lab_npi
        ,labs.lab_name

        ,patients.puuid
        ,patients.pid
        ,patients.pid AS patient_id

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

        ,location.location_id
        ,location.location_uuid
        ,location.location_name
    FROM (
        SELECT
            procedure_order_id
            ,uuid AS order_uuid
            ,provider_id AS order_provider_id
            ,encounter_id AS order_encounter_id
            ,activity AS order_activity
            ,order_diagnosis
            ,order_status
            ,order_priority
            ,patient_instructions
            ,clinical_hx
            ,lab_id as order_lab_id
            ,patient_id AS order_patient_id
            ,provider_id
            ,date_ordered
            ,date_collected
            ,procedure_order_type
            ,scheduled_date
            ,scheduled_start
            ,scheduled_end
            ,performer_type
            ,order_intent
            ,location_id
            ,specimen_fasting
        FROM procedure_order
        WHERE activity = 1
    ) porder
    LEFT JOIN (
        SELECT
            procedure_order_id
            ,procedure_order_seq
            ,procedure_code
            ,procedure_name
            ,procedure_order_title AS procedure_type
            ,diagnoses
        FROM procedure_order_code
    ) order_codes ON order_codes.procedure_order_id = porder.procedure_order_id
    LEFT JOIN (
        SELECT
            date_report AS report_date
            ,procedure_report_id
            ,procedure_order_id
            ,procedure_order_seq
            ,uuid AS report_uuid
            ,report_notes
        FROM procedure_report
    ) preport ON preport.procedure_order_id = porder.procedure_order_id
        AND preport.procedure_order_seq = order_codes.procedure_order_seq
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
        FROM `procedure_result`
    ) presult ON presult.procedure_report_id = preport.procedure_report_id
    LEFT JOIN (
        SELECT
            standard_code,
            procedure_code AS proc_code
        FROM procedure_type
    ) pcode_types ON order_codes.procedure_code = pcode_types.proc_code
    LEFT JOIN (
        SELECT
            encounter AS eid
            ,uuid AS euuid
            ,`date` AS encounter_date
        FROM form_encounter
    ) encounters ON porder.order_encounter_id = encounters.eid
    LEFT JOIN (
        SELECT
            ppid AS lab_id
            ,uuid AS lab_uuid
            ,npi AS lab_npi
            ,`name` AS lab_name
            ,`active` AS lab_active
        FROM procedure_providers
    ) labs ON labs.lab_id = porder.order_lab_id
    LEFT JOIN (
        SELECT
            pid
            ,uuid AS puuid
        FROM patient_data
    ) patients ON patients.pid = porder.order_patient_id
    LEFT JOIN (
        SELECT
           id AS doc_id
           ,uuid AS doc_uuid
        FROM documents
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
    ) provider ON provider.provider_id = porder.provider_id
    LEFT JOIN (
        SELECT
            id AS location_id
            ,uuid AS location_uuid
            ,name AS location_name
        FROM facility
    ) location ON location.location_id = porder.location_id";

        // Build WHERE clause from search parameters
        $modifiedSearch = $search;

        // Don't filter out orders without results when doing a direct UUID lookup
        $isDirectUuidLookup = isset($search['order_uuid']) && count($search) == 1;

        if (!$isDirectUuidLookup) {
            // For general searches, apply the DNR/TNP filter
            $excludeDNR_TNP = new StringSearchField('result_string', ['DNR', 'TNP'], SearchModifier::NOT_EQUALS_EXACT, true);
            if (isset($modifiedSearch['result_string']) && $modifiedSearch['result_string'] instanceof ISearchField) {
                $compoundColumn = new CompositeSearchField('result_string', [], true);
                $compoundColumn->addChild($modifiedSearch['result_string']);
            } else {
                $compoundColumn = new CompositeSearchField('result_string', [], false);
                $resultIsNull = new TokenSearchField('result_string', [new TokenSearchValue(true)]);
                $resultIsNull->setModifier(SearchModifier::MISSING);
                $compoundColumn->addChild($resultIsNull);
            }
            $compoundColumn->addChild($excludeDNR_TNP);
            $modifiedSearch['result_string'] = $compoundColumn;
        }

        $whereClause = FhirSearchWhereClauseBuilder::build($modifiedSearch, $isAndCondition);

        $sql .= $whereClause->getFragment();
        $sqlBindArray = $whereClause->getBoundValues();
        $statementResults = QueryUtils::sqlStatementThrowException($sql, $sqlBindArray);

        $r = sqlStatement("select uuid, pid as id from patient_data where pid > 0");
        foreach ($r as $row) {
            error_log($row['id'] . ' = ' . UuidRegistry::uuidToString($row['uuid']));
        }

        $processingResult = $this->hydrateSearchResultsFromQueryResource($statementResults);

        return $processingResult;
    }

    /**
     * Hydrates the search results from the query resource into a structured format
     * with procedures, reports, results, and specimens.
     *
     * @param resource $queryResource The result resource from the executed SQL query.
     * @return ProcessingResult The structured processing result containing procedures and their details.
     */
    private function hydrateSearchResultsFromQueryResource($queryResource)
    {
        $processingResult = new ProcessingResult();
        $procedureByUuid = [];
        $reportsByUuid = [];
        $procedures = [];

        // First pass: build the structure
        while ($row = sqlFetchArray($queryResource)) {
            $record = $this->createResultRecordFromDatabaseResult($row);
            $procedureUuid = $record['order_uuid'];

            if (!isset($procedureByUuid[$procedureUuid])) {
                $procedure = [
                    'name' => $record['procedure_name'] ?? null
                    ,'uuid' => $record['order_uuid']
                    ,'order_uuid' => $record['order_uuid']
                    ,'procedure_order_id' => $record['procedure_order_id']
                    ,'code' => $record['procedure_code'] ?? null
                    ,'procedure_code' => $record['procedure_code'] ?? null
                    ,'procedure_name' => $record['procedure_name'] ?? null
                    ,'procedure_order_type' => $record['procedure_order_type'] ?? null
                    ,'standard_code' => $record['standard_code'] ?? null
                    ,'diagnosis' => $record['order_diagnosis'] ?? null
                    ,'order_diagnosis' => $record['order_diagnosis'] ?? null
                    ,'diagnoses' => $record['diagnoses'] ?? null
                    ,'activity' => $record['order_activity']
                    ,'status' => $record['order_status'] ?? null
                    ,'order_status' => $record['order_status'] ?? null
                    ,'priority' => $record['order_priority'] ?? null
                    ,'order_priority' => $record['order_priority'] ?? null
                    ,'patient_instructions' => $record['patient_instructions'] ?? null
                    ,'clinical_hx' => $record['clinical_hx'] ?? null
                    ,'date_ordered' => $record['date_ordered'] ?? null
                    ,'date_collected' => $record['date_collected'] ?? null
                    ,'scheduled_date' => $record['scheduled_date'] ?? null
                    ,'scheduled_start' => $record['scheduled_start'] ?? null
                    ,'scheduled_end' => $record['scheduled_end'] ?? null
                    ,'performer_type' => $record['performer_type'] ?? null
                    ,'order_intent' => $record['order_intent'] ?? null
                    ,'specimen_fasting' => $record['specimen_fasting'] ?? null
                    ,'reports' => []
                ];

                if (!empty($record['provider_id'])) {
                    $procedure['provider_id'] = $record['provider_id'];
                    $procedure['provider'] = [
                        'id' => $record['provider_id']
                        ,'uuid' => $record['provider_uuid']
                        , 'fname' => $record['provider_fname']
                        , 'mname' => $record['provider_mname']
                        , 'lname' => $record['provider_lname']
                        ,'npi' => $record['provider_npi']
                    ];
                }

                if (!empty($record['lab_id'])) {
                    $procedure['lab_id'] = $record['lab_id'];
                    $procedure['lab'] = [
                        'id' => $record['lab_id']
                        ,'uuid' => $record['lab_uuid']
                        , 'name' => $record['lab_name']
                        ,'npi' => $record['lab_npi']
                    ];
                }

                if (!empty($record['pid'])) {
                    $procedure['patient_id'] = $record['patient_id'];
                    $procedure['patient'] = [
                        'pid' => $record['pid']
                        ,'uuid' => $record['puuid']
                    ];
                }

                if (!empty($record['eid'])) {
                    $procedure['encounter_id'] = $record['eid'];
                    $procedure['encounter'] = [
                        'id' => $record['eid']
                        ,'uuid' => $record['euuid']
                        ,'date' => $record['encounter_date']
                    ];
                }

                if (!empty($record['location_id']) && !empty($record['location_uuid'])) {
                    $procedure['location_id'] = $record['location_id'];
                    $procedure['location'] = [
                        'id' => $record['location_id']
                        ,'uuid' => UuidRegistry::uuidToString($record['location_uuid'])  // CONVERT BINARY!
                        ,'name' => $record['location_name']
                    ];
                }

                $procedures[] = $procedureUuid;
                $procedureByUuid[$procedureUuid] = $procedure;
            } else {
                $procedure = $procedureByUuid[$procedureUuid];
            }

            // Only add reports if they exist
            $reportUuid = $record['report_uuid'] ?? null;
            if (!empty($reportUuid)) {
                if (!isset($reportsByUuid[$reportUuid])) {
                    $report = [
                        'date' => $record['report_date']
                        ,'id' => $record['procedure_report_id']
                        ,'uuid' => $record['report_uuid']
                        , 'notes' => $record['report_notes']
                        ,'order_seq' => $record['procedure_order_seq']
                        ,'results' => []
                    ];

                    $procedure['reports'][] = $reportUuid;
                    $reportsByUuid[$reportUuid] = $report;
                } else {
                    $report = $reportsByUuid[$reportUuid];
                }

                // Add result if it exists
                if (!empty($record['procedure_result_id'])) {
                    $result = [
                        'id' => $record['procedure_result_id']
                        ,'uuid' => $record['result_uuid']
                        ,'code' => $record['result_code']
                        , 'text' => $record['result_text']
                        ,'units' => $record['result_units']
                        , 'result' => $record['result_result']
                        ,'range' => $record['result_range']
                        ,'abnormal' => $record['result_abnormal']
                        , 'comments' => $record['result_comments']
                        ,'document_id' => $record['doc_id']
                        ,'status' => $record['result_status']
                    ];
                    $report['results'][] = $result;
                }

                $reportsByUuid[$reportUuid] = $report;
                $procedureByUuid[$procedureUuid] = $procedure;
            }
        }

        // Second pass: fetch specimens for reports that have them
        foreach ($reportsByUuid as $reportUuid => $report) {
            if (!empty($report['order_seq'])) {
                $orderIdSql = "SELECT procedure_order_id FROM procedure_report WHERE uuid = ?";
                $orderIdResult = sqlQuery($orderIdSql, [UuidRegistry::uuidToBytes($reportUuid)]);
                $orderId = $orderIdResult['procedure_order_id'] ?? null;

                if ($orderId) {
                    $specimenSql = "SELECT
                uuid AS specimen_uuid
                ,specimen_identifier
                ,accession_identifier
                ,specimen_type_code
                ,specimen_type
                ,collection_method_code
                ,collection_method
                ,specimen_location_code
                ,specimen_location
                ,collected_date
                ,collection_date_low
                ,collection_date_high
                ,volume_value
                ,volume_unit
                ,condition_code
                ,specimen_condition
                ,comments AS specimen_comments
                ,deleted
            FROM procedure_specimen
            WHERE procedure_order_id = ? AND procedure_order_seq = ?
            ORDER BY procedure_specimen_id";

                    $specimenResults = sqlStatement($specimenSql, [$orderId, $report['order_seq']]);
                    $specimens = [];

                    while ($specimenRow = sqlFetchArray($specimenResults)) {
                        $specimens[] = [
                            'uuid' => UuidRegistry::uuidToString($specimenRow['specimen_uuid'])
                            , 'identifier' => $specimenRow['specimen_identifier']
                            , 'accession' => $specimenRow['accession_identifier']
                            ,'type_code' => $specimenRow['specimen_type_code']
                            , 'type' => $specimenRow['specimen_type']
                            ,'method_code' => $specimenRow['collection_method_code']
                            , 'method' => $specimenRow['collection_method']
                            ,'location_code' => $specimenRow['specimen_location_code']
                            , 'location' => $specimenRow['specimen_location']
                            ,'collected_date' => $specimenRow['collected_date']
                            ,'collection_start' => $specimenRow['collection_date_low']
                            ,'collection_end' => $specimenRow['collection_date_high']
                            ,'volume' => $specimenRow['volume_value']
                            ,'volume_unit' => $specimenRow['volume_unit']
                            ,'condition_code' => $specimenRow['condition_code']
                            , 'specimen_condition' => $specimenRow['specimen_condition']
                            , 'comments' => $specimenRow['specimen_comments']
                            ,'deleted' => $specimenRow['deleted']
                        ];
                    }

                    if (!empty($specimens)) {
                        $reportsByUuid[$reportUuid]['specimens'] = $specimens;
                    }
                }
            }
        }

        // Final assembly
        foreach ($procedures as $uuid) {
            $procedure = $procedureByUuid[$uuid];
            if (!empty($procedure['reports'])) {
                $procedure['reports'] = array_map(
                    fn($reportUuid): array => $reportsByUuid[$reportUuid],
                    $procedure['reports']
                );
            }
            $processingResult->addData($procedure);
        }

        return $processingResult;
    }

    /**
     * @param $search
     * @param $isAndCondition
     * @return void
     */
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
               WHERE po.`patient_id` = ? AND prs.`result` NOT IN ('DNR','TNP')";// active orders only
    }

    /**
     * @param $row
     * @return
     */
    public function createResultRecordFromDatabaseResult($row)
    {
        return parent::createResultRecordFromDatabaseResult($row); // TODO: Change the autogenerated stub
    }

    /**
     * Returns a list of procedures matching optional search criteria.
     * Search criteria is conveyed by array where key = field/column name, value = field value.
     * If no search criteria is provided, all records are returned.
     *
     * @param  $search         search array parameters
     * @param  $isAndCondition specifies if AND condition is used for multiple criteria. Defaults to true.
     * @param  $puuidBind      - Optional variable to only allow visibility of the patient with this puuid.
     * @return ProcessingResult which contains validation messages, internal error messages, and the data
     *                         payload.
     */
    public function getAll($search = [], $isAndCondition = true, $puuidBind = null): ProcessingResult
    {
        $sqlBindArray = [];

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

        // Query to get unique procedure orders with their basic info
        $sql = "SELECT DISTINCT
                porder.uuid,
                porder.procedure_order_id,
                porder.provider_id,
                porder.patient_id,
                porder.encounter_id,
                porder.date_ordered,
                porder.order_status,
                porder.order_priority,
                porder.order_diagnosis,
                porder.activity,
                porder.control_id,
                porder.lab_id,
                porder.clinical_hx,
                porder.patient_instructions,
                patient.uuid AS puuid,
                encounter.uuid AS euuid,
                practitioner.uuid AS pruuid,
                lab.uuid AS lab_uuid,
                lab.name AS lab_name
            FROM procedure_order AS porder
            LEFT JOIN patient_data AS patient
                ON patient.pid = porder.patient_id
            LEFT JOIN form_encounter AS encounter
                ON encounter.encounter = porder.encounter_id
            LEFT JOIN users AS practitioner
                ON practitioner.id = porder.provider_id
            LEFT JOIN procedure_providers AS lab
                ON lab.ppid = porder.lab_id
            LEFT JOIN
            WHERE porder.activity = 1";

        if (!empty($search)) {
            if (!empty($puuidBind)) {
                $sql .= ' AND (';
            }
            $whereClauses = [];
            foreach ($search as $fieldName => $fieldValue) {
                if ($fieldName === 'patient.uuid') {
                    array_push($whereClauses, 'patient.uuid = ?');
                } else {
                    array_push($whereClauses, 'porder.' . $fieldName . ' = ?');
                }
                array_push($sqlBindArray, $fieldValue);
            }
            $sqlCondition = ($isAndCondition == true) ? 'AND' : 'OR';
            $sql .= ' AND ' . implode(' ' . $sqlCondition . ' ', $whereClauses);
            if (!empty($puuidBind)) {
                $sql .= ") AND `patient`.`uuid` = ?";
                $sqlBindArray[] = UuidRegistry::uuidToBytes($puuidBind);
            }
        } elseif (!empty($puuidBind)) {
            $sql .= " AND `patient`.`uuid` = ?";
            $sqlBindArray[] = UuidRegistry::uuidToBytes($puuidBind);
        }

        $sql .= " ORDER BY porder.date_ordered DESC";

        $statementResults = sqlStatement($sql, $sqlBindArray);

        $processingResult = new ProcessingResult();

        while ($row = sqlFetchArray($statementResults)) {
            $procedureOrderId = $row['procedure_order_id'];

            // Convert main UUIDs
            $row['uuid'] = UuidRegistry::uuidToString($row['uuid']);
            $row['order_uuid'] = $row['uuid']; // Primary identifier

            if (!empty($row['puuid'])) {
                $row['puuid'] = UuidRegistry::uuidToString($row['puuid']);
            }
            if (!empty($row['euuid'])) {
                $row['euuid'] = UuidRegistry::uuidToString($row['euuid']);
            }
            if (!empty($row['pruuid'])) {
                $row['pruuid'] = UuidRegistry::uuidToString($row['pruuid']);
            }
            if (!empty($row['lab_uuid'])) {
                $row['lab_uuid'] = UuidRegistry::uuidToString($row['lab_uuid']);
            }

            // Get all order codes for this order
            $codesSql = "SELECT
                        procedure_order_seq,
                        procedure_code,
                        procedure_name,
                        procedure_order_title,
                        diagnoses
                     FROM procedure_order_code
                     WHERE procedure_order_id = ?
                     ORDER BY procedure_order_seq";
            $codesResult = sqlStatement($codesSql, [$procedureOrderId]);
            $orderCodes = [];
            while ($codeRow = sqlFetchArray($codesResult)) {
                $orderCodes[] = $codeRow;
            }
            $row['order_codes'] = $orderCodes;

            // Get all reports for this order
            $reportsSql = "SELECT
                          procedure_report_id,
                          procedure_order_seq,
                          uuid AS report_uuid,
                          date_report,
                          report_notes
                       FROM procedure_report
                       WHERE procedure_order_id = ?
                       ORDER BY procedure_order_seq, date_report DESC";
            $reportsResult = sqlStatement($reportsSql, [$procedureOrderId]);
            $reports = [];
            while ($reportRow = sqlFetchArray($reportsResult)) {
                $reportRow['report_uuid'] = UuidRegistry::uuidToString($reportRow['report_uuid']);

                // Get results for this report
                $resultsSql = "SELECT
                              uuid AS result_uuid,
                              result_status,
                              result_code,
                              result_text,
                              date,
                              facility,
                              units,
                              result
                           FROM procedure_result
                           WHERE procedure_report_id = ?";
                $resultsResult = sqlStatement($resultsSql, [$reportRow['procedure_report_id']]);
                $results = [];
                while ($resultRow = sqlFetchArray($resultsResult)) {
                    $resultRow['result_uuid'] = UuidRegistry::uuidToString($resultRow['result_uuid']);
                    $results[] = $resultRow;
                }
                $reportRow['results'] = $results;
                $reports[] = $reportRow;
            }
            $row['reports'] = $reports;

            // Process diagnosis
            if (!empty($row['order_diagnosis']) && $row['order_diagnosis'] != "") {
                $row['order_diagnosis'] = $this->addDiagnosis($row['order_diagnosis']);
            }

            // Add diagnosis from order codes
            if (!empty($orderCodes)) {
                foreach ($orderCodes as &$code) {
                    if (!empty($code['diagnoses']) && $code['diagnoses'] != "") {
                        $code['diagnoses'] = $this->addDiagnosis($code['diagnoses']);
                    }
                }
                // Set the first code's info as the primary (for backward compatibility)
                $row['procedure_name'] = $orderCodes[0]['procedure_name'] ?? null;
                $row['procedure_code'] = $orderCodes[0]['procedure_code'] ?? null;
                $row['procedure_order_title'] = $orderCodes[0]['procedure_order_title'] ?? null;
                if (!empty($orderCodes[0]['diagnoses'])) {
                    $row['diagnoses'] = $orderCodes[0]['diagnoses'];
                }
            }

            // Set result info from first result for backward compatibility
            if (!empty($reports) && !empty($reports[0]['results'])) {
                $firstResult = $reports[0]['results'][0];
                $row['result_status'] = $firstResult['result_status'] ?? null;
                $row['result_code'] = $firstResult['result_code'] ?? null;
                $row['result_text'] = $firstResult['result_text'] ?? null;
                $row['date'] = $firstResult['date'] ?? null;
                $row['facility'] = $firstResult['facility'] ?? null;
                $row['units'] = $firstResult['units'] ?? null;
                $row['result'] = $firstResult['result'] ?? null;
                $row['result_uuid'] = $firstResult['result_uuid'] ?? null;
            }

            $processingResult->addData($row);
        }

        return $processingResult;
    }

    /**
     * Returns a single procedure record by id.
     *
     * @param $uuid      - The procedure uuid identifier in string format.
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @return ProcessingResult which contains validation messages, internal error messages, and the data
     *                   payload.
     */
    public function getOne($uuid, $puuidBind = null): ProcessingResult
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
            $isValid = BaseValidator::validateId("uuid", self::PATIENT_TABLE, $puuidBind, true);
            if ($isValid !== true) {
                $validationMessages = [
                    'puuid' => ["invalid or nonexisting value" => " value " . $puuidBind]
                ];
                $processingResult->setValidationMessages($validationMessages);
                return $processingResult;
            }
        }

        // Get the main order record
        $sql = "SELECT
                porder.uuid,
                porder.procedure_order_id,
                porder.provider_id,
                porder.patient_id,
                porder.encounter_id,
                porder.date_ordered,
                porder.order_status,
                porder.order_priority,
                porder.order_diagnosis,
                porder.activity,
                porder.control_id,
                porder.lab_id,
                porder.clinical_hx,
                porder.patient_instructions,
                patient.uuid AS puuid,
                encounter.uuid AS euuid,
                practitioner.uuid AS pruuid,
                lab.uuid AS lab_uuid,
                lab.name AS lab_name
            FROM procedure_order AS porder
            LEFT JOIN patient_data AS patient
                ON patient.pid = porder.patient_id
            LEFT JOIN form_encounter AS encounter
                ON encounter.encounter = porder.encounter_id
            LEFT JOIN users AS practitioner
                ON practitioner.id = porder.provider_id
            LEFT JOIN procedure_providers AS lab
                ON lab.ppid = porder.lab_id
            WHERE porder.uuid = ? AND porder.activity = 1";

        $uuidBinary = UuidRegistry::uuidToBytes($uuid);
        $sqlBindArray = [$uuidBinary];

        if (!empty($puuidBind)) {
            $sql .= " AND `patient`.`uuid` = ?";
            $sqlBindArray[] = UuidRegistry::uuidToBytes($puuidBind);
        }

        $sqlResult = sqlQuery($sql, $sqlBindArray);

        if (!empty($sqlResult)) {
            $procedureOrderId = $sqlResult['procedure_order_id'];

            // Convert UUIDs
            $sqlResult['uuid'] = UuidRegistry::uuidToString($sqlResult['uuid']);
            $sqlResult['order_uuid'] = $sqlResult['uuid'];

            if (!empty($sqlResult['puuid'])) {
                $sqlResult['puuid'] = UuidRegistry::uuidToString($sqlResult['puuid']);
            }
            if (!empty($sqlResult['euuid'])) {
                $sqlResult['euuid'] = UuidRegistry::uuidToString($sqlResult['euuid']);
            }
            if (!empty($sqlResult['pruuid'])) {
                $sqlResult['pruuid'] = UuidRegistry::uuidToString($sqlResult['pruuid']);
            }
            if (!empty($sqlResult['lab_uuid'])) {
                $sqlResult['lab_uuid'] = UuidRegistry::uuidToString($sqlResult['lab_uuid']);
            }

            // Get order codes (tests within this order)
            $codesSql = "SELECT * FROM procedure_order_code WHERE procedure_order_id = ? ORDER BY procedure_order_seq";
            $codesResult = sqlStatement($codesSql, [$procedureOrderId]);
            $orderCodes = [];
            while ($codeRow = sqlFetchArray($codesResult)) {
                if (!empty($codeRow['diagnoses']) && $codeRow['diagnoses'] != "") {
                    $codeRow['diagnoses'] = $this->addDiagnosis($codeRow['diagnoses']);
                }
                $orderCodes[] = $codeRow;
            }
            $sqlResult['order_codes'] = $orderCodes;

            // Get reports and results
            $reportsSql = "SELECT * FROM procedure_report WHERE procedure_order_id = ? ORDER BY procedure_order_seq, date_report DESC";
            $reportsResult = sqlStatement($reportsSql, [$procedureOrderId]);
            $reports = [];
            while ($reportRow = sqlFetchArray($reportsResult)) {
                $reportRow['report_uuid'] = UuidRegistry::uuidToString($reportRow['uuid']);

                $resultsSql = "SELECT * FROM procedure_result WHERE procedure_report_id = ?";
                $resultsResult = sqlStatement($resultsSql, [$reportRow['procedure_report_id']]);
                $results = [];
                while ($resultRow = sqlFetchArray($resultsResult)) {
                    $resultRow['result_uuid'] = UuidRegistry::uuidToString($resultRow['uuid']);
                    $results[] = $resultRow;
                }
                $reportRow['results'] = $results;
                $reports[] = $reportRow;
            }
            $sqlResult['reports'] = $reports;

            // Set primary fields for backward compatibility
            if (!empty($orderCodes)) {
                $sqlResult['procedure_name'] = $orderCodes[0]['procedure_name'] ?? null;
                $sqlResult['procedure_code'] = $orderCodes[0]['procedure_code'] ?? null;
                $sqlResult['procedure_order_title'] = $orderCodes[0]['procedure_order_title'] ?? null;
                $sqlResult['diagnoses'] = $orderCodes[0]['diagnoses'] ?? null;
            }

            if (!empty($reports) && !empty($reports[0]['results'])) {
                $firstResult = $reports[0]['results'][0];
                $sqlResult['result_status'] = $firstResult['result_status'] ?? null;
                $sqlResult['result_code'] = $firstResult['result_code'] ?? null;
                $sqlResult['result_text'] = $firstResult['result_text'] ?? null;
                $sqlResult['result_uuid'] = $firstResult['result_uuid'] ?? null;
            }

            if (!empty($sqlResult['order_diagnosis']) && $sqlResult['order_diagnosis'] != "") {
                $sqlResult['order_diagnosis'] = $this->addDiagnosis($sqlResult['order_diagnosis']);
            }

            $processingResult->addData($sqlResult);
        }

        return $processingResult;
    }

    /**
     * @param $data
     * @return array
     */
    public function addDiagnosis($data): array
    {
        $diagnosisArray = [];
        $dataArray = explode(";", (string) $data);
        foreach ($dataArray as $diagnosis) {
            $diagnosisSplit = explode(":", $diagnosis);
            array_push($diagnosisArray, $diagnosisSplit);
        }
        return $diagnosisArray;
    }



// Add these methods to the ProcedureService class

    /**
     * Get order codes for a specific procedure order with proper UUID handling
     *
     * @param int $orderId The procedure order ID
     * @return array Array of order codes with their details
     */
    public function getOrderCodes($orderId): array
    {
        $sql = "SELECT
                procedure_order_id,
                procedure_order_id,
                procedure_order_seq,
                procedure_code,
                procedure_name,
                procedure_order_title,
                diagnoses,
                transport,
                procedure_type,
                reason_code,
                reason_description,
                reason_date_low,
                reason_date_high,
                reason_status
            FROM procedure_order_code
            WHERE procedure_order_id = ?
            ORDER BY procedure_order_seq";

        $result = sqlStatement($sql, [$orderId]);
        $codes = [];

        while ($row = sqlFetchArray($result)) {
            $codes[] = $row;
        }

        return $codes;
    }

    /**
     * Get specimens for a specific order and sequence
     *
     * @param int      $orderId  The procedure order ID
     * @param int|null $orderSeq Optional sequence number (if null, returns all for order)
     * @return array Array of specimens with UUID strings
     */
    public function getSpecimens($orderId, $orderSeq = null): array
    {
        $sql = "SELECT
                procedure_specimen_id,
                uuid,
                procedure_order_id,
                procedure_order_seq,
                specimen_identifier,
                accession_identifier,
                specimen_type_code,
                specimen_type,
                collection_method_code,
                collection_method,
                specimen_location_code,
                specimen_location,
                collected_date,
                collection_date_low,
                collection_date_high,
                volume_value,
                volume_unit,
                condition_code,
                specimen_condition,
                comments,
                created_by,
                updated_by,
                date_created,
                date_updated
            FROM procedure_specimen
            WHERE procedure_order_id = ?";

        $sqlBindArray = [$orderId];

        if ($orderSeq !== null) {
            $sql .= " AND procedure_order_seq = ?";
            $sqlBindArray[] = $orderSeq;
        }

        $sql .= " ORDER BY procedure_order_seq, procedure_specimen_id";

        $result = sqlStatement($sql, $sqlBindArray);
        $specimens = [];

        while ($row = sqlFetchArray($result)) {
            // Convert UUID bytes to string for API responses
            if (!empty($row['uuid'])) {
                $row['uuid'] = UuidRegistry::uuidToString($row['uuid']);
            }
            $specimens[] = $row;
        }

        return $specimens;
    }

    /**
     * Get specimens grouped by order sequence
     *
     * @param int $orderId The procedure order ID
     * @return array Array indexed by sequence number containing specimen arrays
     */
    public function getSpecimensBySequence($orderId): array
    {
        $specimens = $this->getSpecimens($orderId);
        $grouped = [];

        foreach ($specimens as $specimen) {
            $seq = $specimen['procedure_order_seq'];
            if (!isset($grouped[$seq])) {
                $grouped[$seq] = [];
            }
            $grouped[$seq][] = $specimen;
        }

        return $grouped;
    }

    /**
     * Get complete order details including codes and specimens
     * This is useful for edit forms and API responses
     *
     * @param int $orderId The procedure order ID
     * @return array|null Complete order structure or null if not found
     */
    public function getCompleteOrder($orderId): ?array
    {
        // Get main order
        $orderSql = "SELECT
                    po.*,
                    p.uuid AS puuid,
                    e.uuid AS euuid,
                    u.uuid AS provider_uuid,
                    pp.uuid AS lab_uuid,
                    pp.name AS lab_name,
                    pp.npi AS lab_npi
                FROM procedure_order po
                LEFT JOIN patient_data p ON p.pid = po.patient_id
                LEFT JOIN form_encounter e ON e.encounter = po.encounter_id
                LEFT JOIN users u ON u.id = po.provider_id
                LEFT JOIN procedure_providers pp ON pp.ppid = po.lab_id
                WHERE po.procedure_order_id = ?";

        $order = sqlQuery($orderSql, [$orderId]);

        if (empty($order)) {
            return null;
        }

        // Convert UUIDs
        $order['uuid'] = UuidRegistry::uuidToString($order['uuid']);
        $order['puuid'] = !empty($order['puuid']) ? UuidRegistry::uuidToString($order['puuid']) : null;
        $order['euuid'] = !empty($order['euuid']) ? UuidRegistry::uuidToString($order['euuid']) : null;
        $order['provider_uuid'] = !empty($order['provider_uuid']) ? UuidRegistry::uuidToString($order['provider_uuid']) : null;
        $order['lab_uuid'] = !empty($order['lab_uuid']) ? UuidRegistry::uuidToString($order['lab_uuid']) : null;

        // Get order codes
        $order['order_codes'] = $this->getOrderCodes($orderId);

        // Get specimens grouped by sequence
        $order['specimens_by_sequence'] = $this->getSpecimensBySequence($orderId);

        return $order;
    }

    /**
     * Update a single order code (for partial updates)
     *
     * @param int   $orderId Order ID
     * @param int   $seq     Sequence number
     * @param array $data    Data to update
     * @return bool Success status
     */
    public function updateOrderCode($orderId, $seq, $data): bool
    {
        $validFields = [
            'diagnoses', 'procedure_order_title', 'transport',
            'procedure_code', 'procedure_name', 'procedure_type',
            'reason_code', 'reason_description', 'reason_date_low',
            'reason_date_high', 'reason_status'
        ];

        $updates = [];
        $params = [];

        foreach ($data as $field => $value) {
            if (in_array($field, $validFields)) {
                $updates[] = "$field = ?";
                $params[] = $value;
            }
        }

        if (empty($updates)) {
            return false;
        }

        $params[] = $orderId;
        $params[] = $seq;

        $sql = "UPDATE procedure_order_code SET " .
            implode(', ', $updates) .
            " WHERE procedure_order_id = ? AND procedure_order_seq = ?";

        sqlStatement($sql, $params);
        return true;
    }

    /**
     * Update a single specimen (for partial updates)
     *
     * @param int   $specimenId Specimen ID
     * @param array $data       Data to update
     * @return bool Success status
     */
    public function updateSpecimen($specimenId, $data): bool
    {
        $validFields = [
            'specimen_identifier', 'accession_identifier',
            'specimen_type_code', 'specimen_type',
            'collection_method_code', 'collection_method',
            'specimen_location_code', 'specimen_location',
            'collected_date', 'collection_date_low', 'collection_date_high',
            'volume_value', 'volume_unit',
            'condition_code', 'specimen_condition', 'comments'
        ];

        $updates = [];
        $params = [];

        foreach ($data as $field => $value) {
            if (in_array($field, $validFields)) {
                $updates[] = "$field = ?";
                $params[] = $value;
            }
        }

        if (empty($updates)) {
            return false;
        }

        // Add updated_by and timestamp
        $updates[] = "updated_by = ?";
        $params[] = $_SESSION['authUserID'] ?? null;

        $params[] = $specimenId;

        $sql = "UPDATE procedure_specimen SET " .
            implode(', ', $updates) .
            " WHERE procedure_specimen_id = ?";

        sqlStatement($sql, $params);
        return true;
    }

    /**
     * Delete a specific order code and its related data
     * Note: This should cascade to specimens and answers
     *
     * @param int $orderId Order ID
     * @param int $seq     Sequence number
     * @return bool Success status
     */
    public function deleteOrderCode($orderId, $seq): bool
    {
        // Delete in proper order to maintain referential integrity

        // Delete answers first
        sqlStatement(
            "DELETE FROM procedure_answers
         WHERE procedure_order_id = ? AND procedure_order_seq = ?",
            [$orderId, $seq]
        );

        // Delete specimens
        sqlStatement(
            "DELETE FROM procedure_specimen
         WHERE procedure_order_id = ? AND procedure_order_seq = ?",
            [$orderId, $seq]
        );

        // Delete the order code
        sqlStatement(
            "DELETE FROM procedure_order_code
         WHERE procedure_order_id = ? AND procedure_order_seq = ?",
            [$orderId, $seq]
        );

        return true;
    }

    /**
     * Delete a specific specimen by ID
     *
     * @param int $specimenId Specimen ID
     * @return bool Success status
     */
    public function deleteSpecimen($specimenId): bool
    {
        sqlStatement(
            "DELETE FROM procedure_specimen WHERE procedure_specimen_id = ?",
            [$specimenId]
        );

        return true;
    }

    public function delete($uuid): ProcessingResult
    {
        $processingResult = new ProcessingResult();

        // Validate UUID first
        $isValid = BaseValidator::validateId("uuid", self::PROCEDURE_TABLE, $uuid, true);
        if ($isValid !== true) {
            $validationMessages = [
                'uuid' => ["invalid or nonexisting value" => " value " . $uuid]
            ];
            $processingResult->setValidationMessages($validationMessages);
            return $processingResult;
        }

        $uuidBinary = UuidRegistry::uuidToBytes($uuid);

        // Get the procedure_order_id before soft-deleting (need it for relationship cleanup)
        $sql = "SELECT procedure_order_id FROM procedure_order WHERE uuid = ?";
        $result = sqlQuery($sql, [$uuidBinary]);

        if (empty($result)) {
            $processingResult->addInternalError("Procedure order not found");
            return $processingResult;
        }

        $procedureOrderId = $result['procedure_order_id'];

        // Delete all relationships for this order
        try {
            $deletedCount = $this->relationshipService->deleteRelationshipsByOrderId($procedureOrderId);

            // Optional: Log the cleanup
            if ($deletedCount > 0) {
                error_log("Deleted $deletedCount relationships for procedure_order_id: $procedureOrderId");
            }
        } catch (\Exception $e) {
            error_log("Error deleting relationships for procedure_order_id $procedureOrderId: " . $e->getMessage());
        }

        // Soft delete the order (set activity = 0)
        $updateSql = "UPDATE procedure_order SET activity = 0 WHERE uuid = ?";
        sqlStatement($updateSql, [$uuidBinary]);

        $processingResult->addData(['deleted' => true, 'uuid' => $uuid]);

        return $processingResult;
    }

    /**
     * Cleanup orphaned relationships for all procedure orders
     * This can be called from a cron job or maintenance script
     *
     * @return int Number of orphaned records deleted
     */
    public function cleanupOrphanedRelationships(): int
    {
        try {
            return $this->relationshipService->cleanupOrphanedRecords();
        } catch (\Exception $e) {
            error_log("Error cleaning up orphaned relationships: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get relationship statistics
     * Useful for monitoring and debugging
     *
     * @return array Statistics about relationships
     */
    public function getRelationshipStatistics(): array
    {
        try {
            return $this->relationshipService->getStatistics();
        } catch (\Exception $e) {
            error_log("Error getting relationship statistics: " . $e->getMessage());
            return [
                'error' => $e->getMessage(),
                'total_relationships' => 0
            ];
        }
    }

    /**
     * Validate specimen data before save
     *
     * @param array $data Specimen data
     * @return array Array of validation errors (empty if valid)
     */
    public function validateSpecimenData($data): array
    {
        $errors = [];

        // At least one of the key identifiers should be present
        if (empty($data['specimen_identifier']) && empty($data['accession_identifier'])) {
            $errors[] = 'Either specimen_identifier or accession_identifier is required';
        }

        // Validate dates if present
        if (!empty($data['collected_date']) && !$this->isValidDateTime($data['collected_date'])) {
            $errors[] = 'Invalid collected_date format';
        }

        if (!empty($data['collection_date_low']) && !$this->isValidDateTime($data['collection_date_low'])) {
            $errors[] = 'Invalid collection_date_low format';
        }

        if (!empty($data['collection_date_high']) && !$this->isValidDateTime($data['collection_date_high'])) {
            $errors[] = 'Invalid collection_date_high format';
        }

        // Validate volume if present
        if (!empty($data['volume_value']) && !is_numeric($data['volume_value'])) {
            $errors[] = 'volume_value must be numeric';
        }

        if (!empty($data['volume_value']) && $data['volume_value'] < 0) {
            $errors[] = 'volume_value cannot be negative';
        }

        return $errors;
    }

    /**
     * Helper to validate datetime strings
     *
     * @param string $datetime DateTime string
     * @return bool True if valid
     */
    private function isValidDateTime($datetime): bool
    {
        if (empty($datetime)) {
            return true;
        }

        $d = \DateTime::createFromFormat('Y-m-d H:i:s', $datetime);
        if ($d && $d->format('Y-m-d H:i:s') === $datetime) {
            return true;
        }

        $d = \DateTime::createFromFormat('Y-m-d', $datetime);
        return $d && $d->format('Y-m-d') === $datetime;
    }

    /**
     * Get procedure order codes by UUID (for FHIR)
     *
     * @param string $orderUuid Order UUID string
     * @return array Array of order codes
     */
    public function getOrderCodesByUuid($orderUuid): array
    {
        $uuidBinary = UuidRegistry::uuidToBytes($orderUuid);

        $sql = "SELECT poc.*
            FROM procedure_order_code poc
            INNER JOIN procedure_order po ON po.procedure_order_id = poc.procedure_order_id
            WHERE po.uuid = ?
            ORDER BY poc.procedure_order_seq";

        $result = sqlStatement($sql, [$uuidBinary]);
        $codes = [];

        while ($row = sqlFetchArray($result)) {
            $codes[] = $row;
        }

        return $codes;
    }

    /**
     * Get specimens by order UUID (for FHIR)
     *
     * @param string   $orderUuid Order UUID string
     * @param int|null $orderSeq  Optional sequence number
     * @return array Array of specimens with UUID strings
     */
    public function getSpecimensByOrderUuid($orderUuid, $orderSeq = null): array
    {
        $uuidBinary = UuidRegistry::uuidToBytes($orderUuid);

        $sql = "SELECT ps.*
            FROM procedure_specimen ps
            INNER JOIN procedure_order po ON po.procedure_order_id = ps.procedure_order_id
            WHERE po.uuid = ?";

        $sqlBindArray = [$uuidBinary];

        if ($orderSeq !== null) {
            $sql .= " AND ps.procedure_order_seq = ?";
            $sqlBindArray[] = $orderSeq;
        }

        $sql .= " ORDER BY ps.procedure_order_seq, ps.procedure_specimen_id";

        $result = sqlStatement($sql, $sqlBindArray);
        $specimens = [];

        while ($row = sqlFetchArray($result)) {
            if (!empty($row['uuid'])) {
                $row['uuid'] = UuidRegistry::uuidToString($row['uuid']);
            }
            $specimens[] = $row;
        }

        return $specimens;
    }
}
