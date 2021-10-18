<?php

/**
 * PrescriptionService
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
use OpenEMR\Services\Search\FhirSearchWhereClauseBuilder;
use OpenEMR\Validators\ProcessingResult;
use OpenEMR\Validators\BaseValidator;
use OpenEMR\Validators\PatientValidator;

class PrescriptionService extends BaseService
{
    private const DRUGS_TABLE = "drugs";
    private const PRESCRIPTION_TABLE = "prescriptions";
    private const PATIENT_TABLE = "patient_data";
    private const ENCOUNTER_TABLE = "form_encounter";
    private const PRACTITIONER_TABLE = "users";
    private $patientValidator;

    /**
     * Default constructor.
     */
    public function __construct()
    {
        parent::__construct(self::PRESCRIPTION_TABLE);
        UuidRegistry::createMissingUuidsForTables([self::PRESCRIPTION_TABLE, self::PATIENT_TABLE, self::ENCOUNTER_TABLE,
            self::PRACTITIONER_TABLE, self::DRUGS_TABLE]);
        $this->patientValidator = new PatientValidator();
    }

    /**
     * Returns a list of prescription matching optional search criteria.
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
            $isValidPatient = $this->patientValidator->validateId(
                'uuid',
                self::PATIENT_TABLE,
                $search['patient.uuid'],
                true
            );
            if ($isValidPatient != true) {
                return $isValidPatient;
            }
            $search['patient.uuid'] = UuidRegistry::uuidToBytes($search['patient.uuid']);
        }

        if (!empty($puuidBind)) {
            // code to support patient binding
            $isValidPatient = $this->patientValidator->validateId(
                'uuid',
                self::PATIENT_TABLE,
                $puuidBind,
                true
            );
            if ($isValidPatient != true) {
                return $isValidPatient;
            }
        }

        // order comes from our MedicationRequest intent value set, since we are only reporting on completed prescriptions
        // we will put the intent down as 'order' @see http://hl7.org/fhir/R4/valueset-medicationrequest-intent.html
        $sql = "SELECT 
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
                ,combined_prescriptions.drug_dosage_instructions
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

                FROM (
                      SELECT
                             prescriptions.uuid
                            ,'prescriptions' AS 'source_table'
                            ,prescriptions.drug
                            ,prescriptions.active
                            ,prescriptions.end_date
                            ,'order' AS intent
                            ,'Order' AS intent_title
                            ,'community' AS category
                            ,'Home/Community' as category_title
                            ,IF(prescriptions.rxnorm_drugcode!=''
                                ,prescriptions.rxnorm_drugcode
                                ,IF(drugs.drug_code IS NULL, '', concat('RXCUI:',drugs.drug_code))
                            ) AS 'rxnorm_drugcode'
                            ,date_added
                            ,COALESCE(prescriptions.unit,drugs.unit) AS unit
                            ,prescriptions.`interval`
                            ,COALESCE(prescriptions.`route`,drugs.`route`) AS 'route'
                            ,prescriptions.`note`
                            ,patient_id
                            ,encounter
                            ,provider_id
                            ,drugs.uuid AS drug_uuid
                            ,prescriptions.drug_dosage_instructions
                            ,CASE 
                                WHEN prescriptions.end_date IS NOT NULL AND prescriptions.active = '1' THEN 'completed'
                                WHEN prescriptions.active = '1' THEN 'active'
                                ELSE 'stopped'
                            END as 'status'
                            
                    FROM
                        prescriptions
                    LEFT JOIN
                        -- @brady.miller so drug_id in my databases appears to always be 0 so I'm not sure I can grab anything here.. I know WENO doesn't populate this value...
                        drugs ON prescriptions.drug_id = drugs.drug_id
                    UNION
                    SELECT
                        lists.uuid
                        ,'lists' AS 'source_table'
                        ,lists.title AS drug
                        ,activity AS active
                        ,lists.enddate AS end_date
                        ,lists_medication.request_intent AS intent
                        ,lists_medication.request_intent_title AS intent_title
                        ,lists_medication.usage_category AS category
                        ,lists_medication.usage_category_title AS category_title
                        ,lists.diagnosis AS rxnorm_drugcode
                        ,`date` AS date_added
                        ,NULL as unit
                        ,NULL as 'interval'
                        ,NULL as `route`
                        ,lists.comments as 'note'
                        ,pid AS patient_id
                        ,issues_encounter.issues_encounter_encounter as encounter
                        ,users.id AS provider_id
                        ,NULL as drug_uuid
                        ,lists_medication.drug_dosage_instructions
                        ,CASE 
                                WHEN lists.enddate IS NOT NULL AND lists.activity = 1 THEN 'completed'
                                WHEN lists.activity = 1 THEN 'active'
                                ELSE 'stopped'
                        END as 'status'
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
                  FROM list_options
                  WHERE list_id='drug_route'      
                ) intervals_list ON intervals_list.interval_id = combined_prescriptions.interval
                LEFT JOIN
                (
                  SELECT
                    option_id AS unit_id
                    ,title AS unit_title
                    ,codes AS unit_codes
                  FROM list_options
                  WHERE list_id='drug_route'
                ) units_list ON units_list.unit_id = combined_prescriptions.unit
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
                ON practitioner.practitioner_id = combined_prescriptions.provider_id";

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

    public function getUuidFields(): array
    {
        return ['uuid', 'euuid', 'pruuid', 'drug_uuid', 'puuid'];
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

        return $record;
    }

    /**
     * Returns a single prescription record by id.
     * @param $uuid - The prescription uuid identifier in string format.
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @return ProcessingResult which contains validation messages, internal error messages, and the data
     * payload.
     */
    public function getOne($uuid, $puuidBind = null)
    {
        return $this->getAll(['_id' => $uuid], $puuidBind);
    }
}
