<?php

/**
 * PrescriptionService
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786gmail.com>
 * @author    Ivan Googla <ivan.jo.dev@gmail.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786gmail.com>
 * @copyright Copyright (c) 2024 Ivan Googla
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\Search\FhirSearchWhereClauseBuilder;
use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Validators\ProcessingResult;
use Ramsey\Uuid\Exception\InvalidUuidStringException;

class PrescriptionService extends BaseService
{
    private const DRUGS_TABLE = "drugs";
    private const PRESCRIPTION_TABLE = "prescriptions";
    private const PATIENT_TABLE = "patient_data";
    private const ENCOUNTER_TABLE = "form_encounter";
    private const PRACTITIONER_TABLE = "users";

    /**
     * Default constructor.
     */
    public function __construct()
    {
        parent::__construct(self::PRESCRIPTION_TABLE);
        UuidRegistry::createMissingUuidsForTables([self::PRESCRIPTION_TABLE, self::PATIENT_TABLE, self::ENCOUNTER_TABLE,
            self::PRACTITIONER_TABLE, self::DRUGS_TABLE]);
    }

    /**
     * Returns a list of prescriptions matching search criteria. A patient
     * binding is REQUIRED (`patient.uuid` in the search array); calling
     * `getAll()` without one returns a validation-failure ProcessingResult
     * rather than a tenant-wide enumeration.
     *
     * Rationale: the REST list endpoint previously fell through to an
     * unfiltered UNION-SELECT when no filter was supplied, so any caller who
     * reached the route with `patients/rx` view access received every
     * prescription across every patient. Requiring the bind here mirrors the
     * compartment-enforcement pattern used by FHIR services and keeps the
     * scope narrowing at the data-layer boundary rather than relying on a
     * caller elsewhere to remember to bind a patient.
     *
     * After the binding is validated the per-patient ACL check applies too:
     * a `patients/rx` view grant is tenant-wide, so the same
     * `patients/demo` + `squads/<squad>` policy that gates chart access must
     * gate this listing.
     *
     * @param array<string, ISearchField|string> $search search array parameters
     * @param  $isAndCondition specifies if AND condition is used for multiple criteria. Defaults to true.
     * @return ProcessingResult which contains validation messages, internal error messages, and the data
     * payload.
     */
    public function getAll(array $search = [], $isAndCondition = true)
    {
        $patientUuidRaw = $search['patient.uuid'] ?? '';
        if (!is_string($patientUuidRaw) || $patientUuidRaw === '') {
            // The bind-builder accepts an already-parsed ISearchField for
            // this key, but every caller of PrescriptionService::getAll()
            // hands it as a bare uuid string. Reject the ISearchField shape
            // here so the ACL check and the byte translation below can rely
            // on a string.
            $processingResult = new ProcessingResult();
            $processingResult->setValidationMessages([
                'patient.uuid' => 'A patient identifier is required to list prescriptions.',
            ]);
            return $processingResult;
        }

        $patient = $this->findPatientByPatientUuid($patientUuidRaw);
        if ($patient === null) {
            $processingResult = new ProcessingResult();
            $processingResult->setValidationMessages([
                'patient.uuid' => 'Patient does not exist.',
            ]);
            return $processingResult;
        }
        $squadRaw = $patient['squad'] ?? '';
        $squad = is_string($squadRaw) ? $squadRaw : '';
        if (!$this->aclCheckUserPatientAccess($squad)) {
            $processingResult = new ProcessingResult();
            $processingResult->setValidationMessages([
                'patient.uuid' => 'User does not have access to this patient.',
            ]);
            return $processingResult;
        }

        // Translate the caller-facing FHIR-style `patient.uuid` search key to
        // the actual SELECT column alias `patient.puuid`. The previous code
        // performed the value conversion here but never rewrote the key, so
        // an accidental `patient.uuid` filter would have produced a SQL error
        // (`Unknown column patient.uuid in WHERE`). The mismatch was invisible
        // because the endpoint was previously called without any patient
        // filter at all.
        $search['patient.puuid'] = UuidRegistry::uuidToBytes($patientUuidRaw);
        unset($search['patient.uuid']);

        $sql = $this->getBaseSql();

        $whereClause = FhirSearchWhereClauseBuilder::build($search, $isAndCondition);

        $sql .= $whereClause->getFragment();
        $sqlBindArray = $whereClause->getBoundValues();
        $statementResults =  QueryUtils::sqlStatementThrowException($sql, $sqlBindArray);

        $processingResult = new ProcessingResult();
        while ($row = QueryUtils::fetchArrayFromResultSet($statementResults)) {
            $record = $this->createResultRecordFromDatabaseResult($row);
            $processingResult->addData($record);
        }
        return $processingResult;
    }

    /**
     * Returns the base SQL for prescription queries (UNION of prescriptions + lists tables
     * with all JOINs). Callers append WHERE clauses as needed.
     */
    private function getBaseSql(): string
    {
        // order comes from our MedicationRequest intent value set, since we are only reporting on completed prescriptions
        // we will put the intent down as 'order' @see http://hl7.org/fhir/R4/valueset-medicationrequest-intent.html
        return "SELECT
                combined_prescriptions.uuid
                ,combined_prescriptions.source_table
                ,combined_prescriptions.drug
                ,combined_prescriptions.active
                ,combined_prescriptions.intent
                ,combined_prescriptions.category
                ,combined_prescriptions.intent_title
                ,combined_prescriptions.category_title
                ,'Community' AS category_text
                ,combined_prescriptions.rxnorm_drugcode
                ,combined_prescriptions.date_added
                ,combined_prescriptions.unit
                ,combined_prescriptions.`interval`
                ,combined_prescriptions.route
                ,combined_prescriptions.note
                ,combined_prescriptions.status
                ,combined_prescriptions.dosage
                ,combined_prescriptions.drug_dosage_instructions
                ,combined_prescriptions.date_added
                ,combined_prescriptions.date_modified
                ,combined_prescriptions.medication_adherence_date_asserted
                ,combined_prescriptions.prescription_drug_size
                ,combined_prescriptions.quantity
                ,combined_prescriptions.diagnosis
                ,patient.puuid
                ,encounter.euuid
                ,practitioner.pruuid
                ,drug_uuid

                ,routes_list.route_id
                ,routes_list.route_title
                ,routes_list.route_codes

                ,units_list.unit_id
                ,units_list.unit_title
                ,units_list.unit_codes

                ,intervals_list.interval_id
                ,intervals_list.interval_title
                ,intervals_list.interval_codes
                ,intervals_list.interval_notes

                ,combined_prescriptions.medication_adherence
                ,med_adherence.medication_adherence_title
                ,med_adherence.medication_adherence_codes

                ,combined_prescriptions.medication_adherence_information_source
                ,med_adherence_source.medication_adherence_information_source_title
                ,med_adherence_source.medication_adherence_information_source_codes
                ,combined_prescriptions.reporting_source_record_id
                ,reporting_source.reporting_source_uuid
                ,reporting_source.reporting_source_type
                ,reporting_source.reporting_source_abook_type
                FROM (
                      SELECT
                             prescriptions.uuid
                            ,'prescriptions' AS 'source_table'
                            ,prescriptions.drug
                            ,prescriptions.active
                            ,prescriptions.end_date
                            ,COALESCE(prescriptions.request_intent, 'order') AS intent
                            ,COALESCE(prescriptions.request_intent_title, 'Order') AS intent_title
                            ,COALESCE(prescriptions.usage_category, 'community') AS category
                            ,COALESCE(prescriptions.usage_category_title, 'Home/Community') as category_title
                            ,IF(prescriptions.rxnorm_drugcode!=''
                                ,prescriptions.rxnorm_drugcode
                                ,IF(drugs.drug_code IS NULL, '', drugs.drug_code)
                            ) AS 'rxnorm_drugcode'
                            ,date_added
                            ,date_modified
                            ,COALESCE(prescriptions.unit,drugs.unit) AS unit
                            ,prescriptions.`interval`
                            ,COALESCE(prescriptions.`route`,drugs.`route`) AS 'route'
                            ,prescriptions.size AS prescription_drug_size
                            ,prescriptions.`note`
                            ,patient_id
                            ,encounter
                            ,provider_id
                            ,drugs.uuid AS drug_uuid
                            ,prescriptions.drug_dosage_instructions
                            ,prescriptions.quantity
                            ,meds.medication_adherence_date_asserted
                            ,meds.medication_adherence
                            ,meds.medication_adherence_information_source
                            ,CASE
                                WHEN prescriptions.end_date IS NOT NULL AND prescriptions.active = '1' THEN 'completed'
                                WHEN prescriptions.active = '1' THEN 'active'
                                ELSE 'stopped'
                            END as 'status'
                            ,prescriptions.dosage
                            ,diagnosis
                            ,meds.is_primary_record
                            ,meds.reporting_source_record_id
                    FROM
                        prescriptions
                    LEFT JOIN
                        -- @brady.miller so drug_id in my databases appears to always be 0 so I'm not sure I can grab anything here.. I know WENO doesn't populate this value...
                        drugs ON prescriptions.drug_id = drugs.drug_id
                    LEFT JOIN (
                        SELECT
                            id AS meds_id,
                            medication_adherence_information_source,
                            medication_adherence,
                            medication_adherence_date_asserted,
                            prescription_id AS meds_prescription_id,
                            is_primary_record,
                            reporting_source_record_id
                        FROM lists_medication
                    ) meds ON prescriptions.id = meds.meds_prescription_id
                    UNION
                    SELECT
                        lists.uuid
                        ,'lists' AS 'source_table'
                        ,lists.title AS drug
                        ,activity AS active
                        ,lists.enddate AS end_date
                        ,IF(lists_medication.request_intent IS NULL, 'plan', lists_medication.request_intent) AS intent
                        ,IF(lists_medication.request_intent_title IS NULL, 'Plan', lists_medication.request_intent_title) AS intent_title
                        ,lists_medication.usage_category AS category
                        ,lists_medication.usage_category_title AS category_title
                        -- we don't have rxnorm codes for free text meds
                        ,NULL AS rxnorm_drugcode
                        ,`date` AS date_added
                        ,`modifydate` AS date_modified
                        ,NULL as unit
                        ,NULL as 'interval'
                        ,NULL as `route`
                        ,NULL as `prescription_drug_size`
                        ,lists.comments as 'note'
                        ,pid AS patient_id
                        ,issues_encounter.issues_encounter_encounter as encounter
                        ,users.id AS provider_id
                        ,NULL as drug_uuid
                        ,lists_medication.drug_dosage_instructions
                        ,NULL as quantity
                        ,lists_medication.medication_adherence_date_asserted
                        ,lists_medication.medication_adherence
                        ,lists_medication.medication_adherence_information_source
                        ,CASE
                                WHEN lists.enddate IS NOT NULL AND lists.activity = 1 THEN 'completed'
                                WHEN lists.activity = 1 THEN 'active'
                                ELSE 'stopped'
                        END as 'status'
                        ,NULL as dosage
                        ,diagnosis
                        ,is_primary_record
                        ,reporting_source_record_id
                    FROM
                        lists
                    LEFT JOIN
                            users ON users.username = lists.user
                    LEFT JOIN
                        lists_medication ON lists_medication.list_id = lists.id
                    LEFT JOIN
                    (
                       select
                              pid AS issues_encounter_pid
                            , list_id AS issues_encounter_list_id
                            -- lists have a 0..* relationship with issue_encounters which is a problem as FHIR treats medications as a 0.1
                            -- we take the very first encounter that the issue was tied to.
                            , min(encounter) AS issues_encounter_encounter FROM issue_encounter GROUP BY pid,list_id
                    ) issues_encounter ON lists.pid = issues_encounter.issues_encounter_pid AND lists.id = issues_encounter.issues_encounter_list_id
                    WHERE
                        type = 'medication'
                        AND lists_medication.prescription_id IS NULL
                ) combined_prescriptions
                LEFT JOIN
                (
                  SELECT
                    option_id AS route_id
                    ,title AS route_title
                    ,codes AS route_codes
                  FROM list_options
                  WHERE list_id='drug_route'
                ) routes_list ON routes_list.route_id = combined_prescriptions.route
                LEFT JOIN
                (
                  SELECT
                    option_id AS interval_id
                    ,title AS interval_title
                    ,codes AS interval_codes
                    ,notes AS interval_notes
                  FROM list_options
                  WHERE list_id='drug_interval'
                ) intervals_list ON intervals_list.interval_id = combined_prescriptions.interval
                LEFT JOIN
                (
                  SELECT
                    option_id AS unit_id
                    ,title AS unit_title
                    ,codes AS unit_codes
                  FROM list_options
                  WHERE list_id='drug_units'
                ) units_list ON units_list.unit_id = combined_prescriptions.unit
                LEFT JOIN
                (
                  SELECT
                    option_id AS medication_adherence_id
                    ,title AS medication_adherence_title
                    ,codes AS medication_adherence_codes
                  FROM list_options
                  WHERE list_id='medication_adherence'
                ) med_adherence ON med_adherence.medication_adherence_id = combined_prescriptions.medication_adherence
                LEFT JOIN
                (
                  SELECT
                    option_id AS medication_adherence_information_source_id
                    ,title AS medication_adherence_information_source_title
                    ,codes AS medication_adherence_information_source_codes
                  FROM list_options
                  WHERE list_id='medication_adherence'
                ) med_adherence_source ON med_adherence_source.medication_adherence_information_source_id = combined_prescriptions.medication_adherence_information_source
                LEFT JOIN (
                    select uuid AS puuid
                    ,pid
                    FROM patient_data
                ) patient
                ON patient.pid = combined_prescriptions.patient_id
                LEFT JOIN (
                    SELECT
                        encounter,
                        uuid AS euuid
                    FROM form_encounter
                ) encounter
                ON encounter.encounter = combined_prescriptions.encounter
                LEFT JOIN (
                    SELECT
                           id AS practitioner_id
                           ,uuid AS pruuid
                    FROM users
                    WHERE users.npi IS NOT NULL AND users.npi != ''
                ) practitioner
                ON practitioner.practitioner_id = combined_prescriptions.provider_id
                LEFT JOIN (
                    SELECT
                    uuid AS reporting_source_uuid
                    ,'user' AS reporting_source_type
                    ,id AS reporting_source_user_id
                    ,abook_type AS reporting_source_abook_type
                    FROM users
                    WHERE npi IS NOT NULL AND npi != ''
                ) reporting_source ON reporting_source.reporting_source_user_id = combined_prescriptions.reporting_source_record_id";
    }

    public function getUuidFields(): array
    {
        return ['uuid', 'euuid', 'pruuid', 'drug_uuid', 'puuid', 'reporting_source_uuid'];
    }

    protected function createResultRecordFromDatabaseResult($row)
    {
        $record = parent::createResultRecordFromDatabaseResult($row); // TODO: Change the autogenerated stub

        if ($record['rxnorm_drugcode'] != "") {
            $codes = $this->addCoding($row['rxnorm_drugcode']);
            $updatedCodes = [];
            foreach ($codes as $code => $codeValues) {
                if (empty($codeValues['description'])) {
                    // use the drug name if for some reason we have no rxnorm description from the lookup
                    $codeValues['description'] = $row['drug'];
                }
                $updatedCodes[$code] = $codeValues;
            }
            $record['drugcode'] = $updatedCodes;
        }
        // TODO: @adunsulag should we change the table definitions to be null?
        // the title columns historical data was set to be empty, so fixing this
        if (empty($row['request_intent_title']) && $row['source_table'] == 'prescriptions') {
            $record['request_intent_title'] = 'Order';
        }
        if (empty($row['category_title']) && $record['source_table'] == 'prescriptions') {
            // fix for missing category title in prescriptions table
            $record['category_title'] = 'Home/Community';
        }

        return $record;
    }

    /**
     * Returns a single prescription record by uuid.
     *
     * Resolves the target's owning patient and applies the same per-patient
     * ACL that gates the rest of the service. A caller with only a
     * tenant-wide `patients/rx view` grant cannot pull records for patients
     * outside their chart-access scope.
     *
     * @param string $uuid The prescription uuid identifier in string format.
     * @return ProcessingResult which contains validation messages, internal error messages, and the data
     * payload.
     */
    public function getOne(string $uuid): ProcessingResult
    {
        $processingResult = new ProcessingResult();

        $patient = $this->findPatientForPrescription($uuid);
        if ($patient === null) {
            // Prescription does not exist, its uuid is malformed, or its
            // owning patient row is gone. Return a validation-error
            // ProcessingResult so RestControllerHelper maps this to 400
            // (matches the historical PatientValidator::validateId shape
            // that PrescriptionApiTest::testGetOneNotFound pins).
            $processingResult->setValidationMessages([
                'uuid' => ['invalid or nonexisting value' => 'value ' . $uuid],
            ]);
            return $processingResult;
        }
        $squadRaw = $patient['squad'] ?? '';
        $squad = is_string($squadRaw) ? $squadRaw : '';
        if (!$this->aclCheckUserPatientAccess($squad)) {
            $processingResult->setValidationMessages([
                'patient.uuid' => 'User does not have access to this patient.',
            ]);
            return $processingResult;
        }

        // Can't use getAll(['_id' => $uuid]) because FhirSearchWhereClauseBuilder
        // generates `WHERE _id = ?` which fails on the UNION subquery. Filter by
        // the actual column name directly.
        $sql = $this->getBaseSql() . " WHERE combined_prescriptions.uuid = ?";
        $uuidBytes = UuidRegistry::uuidToBytes($uuid);
        $statementResults = QueryUtils::sqlStatementThrowException($sql, [$uuidBytes]);

        while ($row = QueryUtils::fetchArrayFromResultSet($statementResults)) {
            $record = $this->createResultRecordFromDatabaseResult($row);
            $processingResult->addData($record);
        }
        return $processingResult;
    }

    private const REQUIRED_INSERT_FIELDS = ['drug', 'patient_id'];

    /**
     * Fields accepted from a REST client on `POST /api/prescription`. Any key
     * in the request payload that is not in this allowlist is dropped before
     * building the INSERT — the raw payload was previously fed straight into
     * `buildInsertColumns()` which accepts every column defined on the
     * prescriptions table, letting a caller populate provenance columns like
     * `created_by`, `date_added`, or `drug_id` that should be derived from
     * the server-side context rather than trusted from client input.
     *
     * The list mirrors the columns the OpenAPI schema for
     * `POST /api/prescription` documents plus the clinical fields the
     * shipping UI has always let a clinician set. Server-managed columns
     * (`uuid`, `date_added`, `date_modified`, `id`, `active`) are excluded.
     *
     * @var list<string>
     */
    private const INSERTABLE_FIELDS = [
        'patient_id',
        'provider_id',
        'encounter',
        'drug',
        'drug_id',
        'rxnorm_drugcode',
        'dosage',
        'quantity',
        'size',
        'unit',
        'route',
        'interval',
        'refills',
        'per_refill',
        'form',
        'note',
        'medication',
        'substitute',
        'start_date',
        'end_date',
        'diagnosis',
        'drug_info_erx',
        'ntx',
        'txDate',
        'indamt',
        'usage_category',
        'usage_category_title',
        'request_intent',
        'request_intent_title',
        'drug_dosage_instructions',
    ];

    /**
     * Inserts a new prescription record.
     *
     * Enforces a per-patient ACL check on the supplied `patient_id` before
     * touching the row. The route-level ACL (`patients / rx` + `write|addonly`)
     * is a tenant-wide grant, so a caller who cleared the route could
     * previously create a prescription attributed to ANY patient in the tenant
     * regardless of their normal chart-access scope. Mirrors the DELETE-side
     * ownership assertion and the `patients / demo` ACL pattern used by
     * {@see \OpenEMR\RestControllers\Authorization\BearerTokenAuthorizationStrategy::checkUserHasAccessToPatient()}
     * for the SMART launch context.
     *
     * Returns a validation-failure result on any of:
     *   - unresolved `patient_id` (no such patient)
     *   - caller lacks the base `patients / demo` ACL
     *   - patient carries a `squad` tag the caller does not hold
     *
     * @param array<string, mixed> $data The prescription data.
     * @return ProcessingResult containing the new prescription id and uuid, or validation errors.
     */
    public function insert(array $data): ProcessingResult
    {
        $processingResult = new ProcessingResult();

        $missingFields = [];
        foreach (self::REQUIRED_INSERT_FIELDS as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                $missingFields[$field] = 'This field is required';
            }
        }
        if ($missingFields !== []) {
            $processingResult->setValidationMessages($missingFields);
            return $processingResult;
        }

        // Per-patient ACL check on the supplied patient_id. Prescriptions.patient_id
        // is a pid (not a uuid — see sql/database.sql). Resolve the pid to a
        // patient row, then verify the caller holds `patients / demo` (plus
        // any patient-specific `squads/<squad>` scope). Return a validation
        // failure if the pid does not resolve or the caller lacks access — a
        // tenant-wide `patients/rx write` grant must not translate to "can
        // create a prescription for any patient".
        $patientIdRaw = $data['patient_id'];
        if (!is_numeric($patientIdRaw) || (int) $patientIdRaw <= 0) {
            $processingResult->setValidationMessages([
                'patient_id' => 'Patient id must be a positive integer.',
            ]);
            return $processingResult;
        }
        $patient = $this->findPatientByPid((int) $patientIdRaw);
        if ($patient === null) {
            $processingResult->setValidationMessages([
                'patient_id' => 'Patient does not exist.',
            ]);
            return $processingResult;
        }
        $squadRaw = $patient['squad'] ?? '';
        $squad = is_string($squadRaw) ? $squadRaw : '';
        if (!$this->aclCheckUserPatientAccess($squad)) {
            $processingResult->setValidationMessages([
                'patient_id' => 'User does not have access to this patient.',
            ]);
            return $processingResult;
        }

        // Field allowlist: drop keys the REST contract does not expose so
        // client input cannot populate server-managed columns.
        $filteredData = array_intersect_key($data, array_flip(self::INSERTABLE_FIELDS));
        $filteredData['uuid'] = UuidRegistry::getRegistryForTable(self::PRESCRIPTION_TABLE)->createUuid();

        $query = $this->buildInsertColumns($filteredData);

        /** @var string $setClause */
        $setClause = $query['set'];
        /** @var array<mixed> $binds */
        $binds = $query['bind'];

        $sql = " INSERT INTO " . self::PRESCRIPTION_TABLE . " SET " . $setClause;
        $results = QueryUtils::sqlInsert($sql, $binds);

        if ($results) {
            $processingResult->addData([
                'id' => $results,
                'uuid' => UuidRegistry::uuidToString($filteredData['uuid'])
            ]);
        } else {
            $processingResult->addInternalError("error processing SQL Insert");
        }

        return $processingResult;
    }

    /**
     * Soft-deletes a prescription record by setting active = 0.
     *
     * Always resolves the target's owning patient and applies the same
     * per-patient ACL used by insert()/getAll()/getOne(). When
     * `$expectedPatientUuid` is supplied, the stored owner must also match
     * that UUID (an additional caller-side constraint layered on top of the
     * ACL). The two checks are independent — omitting `$expectedPatientUuid`
     * does NOT skip the ACL.
     *
     * @param string      $uuid                The prescription uuid in string format.
     * @param string|null $expectedPatientUuid Optional patient UUID the record must belong to.
     * @return ProcessingResult with deletion status or validation errors.
     */
    public function delete(string $uuid, ?string $expectedPatientUuid = null): ProcessingResult
    {
        $patient = $this->findPatientForPrescription($uuid);
        if ($patient === null) {
            // Same shape as getOne — historical PatientValidator::validateId
            // format so RestControllerHelper maps to 400 (pinned by
            // PrescriptionApiTest::testDeleteNonExistent).
            $processingResult = new ProcessingResult();
            $processingResult->setValidationMessages([
                'uuid' => ['invalid or nonexisting value' => 'value ' . $uuid],
            ]);
            return $processingResult;
        }

        $uuidBytes = UuidRegistry::uuidToBytes($uuid);

        if ($expectedPatientUuid !== null && $expectedPatientUuid !== '') {
            $storedPuuid = $patient['uuid'] ?? null;
            if (!is_string($storedPuuid) || UuidRegistry::uuidToString($storedPuuid) !== $expectedPatientUuid) {
                $processingResult = new ProcessingResult();
                $processingResult->setValidationMessages([
                    'patient.uuid' => 'Prescription does not belong to the specified patient.',
                ]);
                return $processingResult;
            }
        }

        $squadRaw = $patient['squad'] ?? '';
        $squad = is_string($squadRaw) ? $squadRaw : '';
        if (!$this->aclCheckUserPatientAccess($squad)) {
            $processingResult = new ProcessingResult();
            $processingResult->setValidationMessages([
                'patient.uuid' => 'User does not have access to this patient.',
            ]);
            return $processingResult;
        }

        $sql = "UPDATE " . self::PRESCRIPTION_TABLE
             . " SET active = 0, date_modified = NOW() WHERE uuid = ?";
        QueryUtils::sqlStatementThrowException($sql, [$uuidBytes]);

        $processingResult = new ProcessingResult();
        $processingResult->addData(['message' => 'record deleted']);
        return $processingResult;
    }

    /**
     * Look up a patient row by its public uuid. Returns null when the uuid
     * does not correspond to an existing patient. Split out so isolated
     * tests can override this seam without touching the database.
     *
     * @return array<string,mixed>|null
     */
    protected function findPatientByPatientUuid(string $uuid): ?array
    {
        try {
            $uuidBytes = UuidRegistry::uuidToBytes($uuid);
        } catch (InvalidUuidStringException) {
            return null;
        }
        $rows = QueryUtils::fetchRecords(
            "SELECT pid, squad, uuid FROM " . self::PATIENT_TABLE . " WHERE uuid = ? LIMIT 1",
            [$uuidBytes]
        );
        if (!isset($rows[0])) {
            return null;
        }
        /** @var array<string,mixed> $row */
        $row = $rows[0];
        return $row;
    }

    /**
     * Given a prescription uuid, return the owning patient row (pid, squad,
     * uuid). Handles both the `prescriptions` table (patient_id column) and
     * the `lists` medication rows (pid column) via a UNION mirror of the
     * `combined_prescriptions` shape used by the SELECT SQL. Returns null
     * when the prescription has no resolvable owner (deleted patient row).
     *
     * Split out so isolated tests can override this seam without touching
     * the database.
     *
     * @return array<string,mixed>|null
     */
    protected function findPatientForPrescription(string $prescriptionUuid): ?array
    {
        try {
            $uuidBytes = UuidRegistry::uuidToBytes($prescriptionUuid);
        } catch (InvalidUuidStringException) {
            return null;
        }
        $rows = QueryUtils::fetchRecords(
            "SELECT patient_data.pid, patient_data.squad, patient_data.uuid"
            . " FROM (
                    SELECT uuid, patient_id AS owner_pid FROM prescriptions
                    UNION
                    SELECT uuid, pid AS owner_pid FROM lists WHERE type = 'medication'
                ) combined"
            . " INNER JOIN " . self::PATIENT_TABLE
            . " ON " . self::PATIENT_TABLE . ".pid = combined.owner_pid"
            . " WHERE combined.uuid = ? LIMIT 1",
            [$uuidBytes]
        );
        if (!isset($rows[0])) {
            return null;
        }
        /** @var array<string,mixed> $row */
        $row = $rows[0];
        return $row;
    }

    /**
     * Look up a patient row by its pid. Returns null when the pid does not
     * correspond to an existing patient. Split out so isolated tests can
     * override this seam without touching the database.
     *
     * The return type is deliberately widened to `array<string,mixed>|null`
     * (rather than `PatientDataRow`) so isolated tests can supply fixture rows
     * without having to satisfy every field of the underlying shape.
     *
     * `PatientService::findByPid()` declares a non-null `PatientDataRow` return
     * via `@var`, but under the hood it delegates to `QueryUtils::selectHelper()`
     * with `limit => 1`, which returns `null` when there is no matching row.
     * The `mixed` cast defeats PHPStan's docblock trust so we can honour the
     * real runtime null and return null on unresolved pids.
     *
     * @return array<string,mixed>|null
     */
    protected function findPatientByPid(int $pid): ?array
    {
        $patientService = new PatientService();
        /** @var mixed $row */
        $row = $patientService->findByPid($pid);
        if (!is_array($row) || $row === []) {
            return null;
        }
        /** @var array<string,mixed> $row */
        return $row;
    }

    /**
     * Applies OpenEMR's user-to-patient ACL policy: base `patients / demo`
     * plus, when the patient carries a squad tag, the matching `squads / <squad>`
     * ACL. Mirrors
     * {@see \OpenEMR\RestControllers\Authorization\BearerTokenAuthorizationStrategy::aclCheckUserPatientAccess()}.
     * Split out so isolated tests can stub the AclMain interaction without
     * standing up the gACL database. The caller identity is pulled from the
     * active session by `AclMain::aclCheckCore()` when no username is passed.
     *
     * @param string $squad The patient's squad tag (empty string when unset).
     */
    protected function aclCheckUserPatientAccess(string $squad): bool
    {
        if (!AclMain::aclCheckCore('patients', 'demo')) {
            return false;
        }
        if ($squad !== '' && !AclMain::aclCheckCore('squads', $squad)) {
            return false;
        }
        return true;
    }
}
