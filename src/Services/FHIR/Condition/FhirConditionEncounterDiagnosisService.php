<?php

/*
 * FhirConditionEncounterDiagnosisService.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Public Domain for portions marked as AI Generated which were created with the assistance of Claude.AI and Microsoft Copilot
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR\Condition;

use BadMethodCallException;
use InvalidArgumentException;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRProvenance;
use OpenEMR\FHIR\R4\FHIRElement\FHIRAnnotation;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCondition;
use OpenEMR\Services\FHIR\Condition\Enum\FhirConditionCategory;
use OpenEMR\Services\FHIR\Condition\Trait\FhirConditionTrait;
use OpenEMR\Services\FHIR\FhirCodeSystemConstants;
use OpenEMR\Services\FHIR\FhirProvenanceService;
use OpenEMR\Services\FHIR\FhirServiceBase;
use OpenEMR\Services\FHIR\IPatientCompartmentResourceService;
use OpenEMR\Services\FHIR\IResourceUSCIGProfileService;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\FHIR\Traits\MappedServiceCategoryTrait;
use OpenEMR\Services\FHIR\Traits\MappedServiceTrait;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\FhirSearchWhereClauseBuilder;
use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Services\Search\TokenSearchValue;
use OpenEMR\Validators\ProcessingResult;

class FhirConditionEncounterDiagnosisService extends FhirServiceBase implements IPatientCompartmentResourceService, IResourceUSCIGProfileService
{
    use FhirServiceBaseEmptyTrait;
    use MappedServiceTrait;
    use MappedServiceCategoryTrait;
    use FhirConditionTrait;

    const USCGI_PROFILE_ENCOUNTER_DIAGNOSIS_URI = 'http://hl7.org/fhir/us/core/StructureDefinition/us-core-condition-encounter-diagnosis';

    // Date when UUID from the issue_encounters table begins
    private const UUID_CUTOVER_DATE = '2025-11-15 00:00:00';
    const CATEGORY_ENCOUNTER_DIAGNOSIS = 'encounter-diagnosis';

    public function __construct()
    {
        parent::__construct();
    }

    public function supportsCategory(string $category): bool
    {
        return $category === self::CATEGORY_ENCOUNTER_DIAGNOSIS;
    }

    public function getPatientContextSearchField(): FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('patient', SearchFieldType::REFERENCE, [new ServiceField('puuid', ServiceField::TYPE_UUID)]);
    }

    public function getLastModifiedSearchField(): ?FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('_lastUpdated', SearchFieldType::DATETIME, ['last_updated_time']);
    }

    /**
     *  AI Generated
     * @return array
     */
    protected function loadSearchParameters(): array
    {
        return [
            'patient' => $this->getPatientContextSearchField(),
            'encounter' => new FhirSearchParameterDefinition('encounter', SearchFieldType::REFERENCE, [new ServiceField('encounter_uuid', ServiceField::TYPE_UUID)]),
            'code' => new FhirSearchParameterDefinition('code', SearchFieldType::TOKEN, ['diagnosis']),
            'category' => new FhirSearchParameterDefinition('category', SearchFieldType::TOKEN, ['category']),
            // we search both the old database and the new one for backwards compatability
            // TODO: @adunsulag - eventually we will want to phase out the lists_uuid search, or have a smarter search that will filter based on the uuid_registry.table_name
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, [new ServiceField('lists_uuid', ServiceField::TYPE_UUID), new ServiceField('uuid', ServiceField::TYPE_UUID)]),
            '_lastUpdated' => $this->getLastModifiedSearchField(),
            // TODO: @adunsulag - implement clinical-status and verification-status search properly
//            'clinical-status' => new FhirSearchParameterDefinition('clinical-status', SearchFieldType::TOKEN, ['clinical_status']),
//            'verification-status' => new FhirSearchParameterDefinition('verification-status', SearchFieldType::TOKEN, ['verification_status'])
        ];
    }
    // end AI Generated

    /**
     * AI Generated
     * Searches for OpenEMR records using OpenEMR search parameters
     *
     * @param array<string, ISearchField> $openEMRSearchParameters OpenEMR search fields
     * @return ProcessingResult
     */
    protected function searchForOpenEMRRecords($openEMRSearchParameters): ProcessingResult
    {
        // once we've determined that we are the correct service for the category, we remove it from the search parameters
        // as there is only one category for this service
        if (isset($openEMRSearchParameters['category'])) {
            unset($openEMRSearchParameters['category']);
        }
        $openEMRSearchParameters['type'] = new TokenSearchField('type', [new TokenSearchValue('medical_problem')]);
        try {
            // TODO: @adunsulag this was generated by AI but we may want to shove it down to ConditionService or into an EncounterIssuesService or even into PatientIssuesService
            $sql = "
            SELECT
                l.id,
                ie.uuid,
                l.lists_uuid,
                l.pid,
                l.modifydate,
                l.type,
                l.title,
                l.begdate,
                l.enddate,
                l.diagnosis,
                l.activity,
                l.comments,
                l.occurrence,
                l.outcome,
                l.verification,
                ie.date,
                ie.encounter_uuid,
                ie.encounter_id,
                ie.encounter_date,
                ie.creator_uuid,
                ie.creator_npi,
                ie.updator_uuid,
                ie.updator_npi,
                ie.resolved,
                pd.puuid,
                l.last_updated_time
            FROM (
                SELECT
                    l.id,
                    l.date,
                    l.modifydate,
                    COALESCE(l.modifydate, l.date) as last_updated_time,
                    l.uuid AS lists_uuid,
                    l.pid,
                    l.type,
                    l.title,
                    l.begdate,
                    l.enddate,
                    l.diagnosis,
                    l.activity,
                    l.comments,
                    l.occurrence,
                    l.outcome,
                    l.verification
                FROM lists l
            ) l
            INNER JOIN (
                SELECT issue_encounter.uuid,
                       issue_encounter.list_id,
                       issue_encounter.pid,
                       issue_encounter.resolved,
                       issue_encounter.created_at AS date,
                       fe.uuid AS encounter_uuid,
                       fe.encounter as encounter_id,
                       fe.date AS encounter_date,
                       creator.creator_uuid,
                       creator.creator_npi,
                       updator.updator_uuid,
                       updator.updator_npi
                FROM issue_encounter
                INNER JOIN form_encounter fe USING(encounter,pid)
                LEFT JOIN (
                    select
                        uuid AS creator_uuid
                         ,npi AS creator_npi
                        , id AS creator_id
                    FROM users
                ) creator ON issue_encounter.created_by = creator.creator_id
                LEFT JOIN (
                    select
                        uuid AS updator_uuid
                        ,npi AS updator_npi
                        , id AS updator_id
                    FROM users
                ) updator ON issue_encounter.updated_by = updator.updator_id
            ) ie ON l.id = ie.list_id AND l.pid = ie.pid
            INNER JOIN (
                SELECT
                    pid AS patient_id
                    ,uuid AS puuid
                FROM patient_data
            ) pd ON l.pid = pd.patient_id
            ";

            $whereClause = FhirSearchWhereClauseBuilder::build($openEMRSearchParameters);
            $sql .= $whereClause->getFragment();
            $sqlBindArray = $whereClause->getBoundValues();
            $statementResults = QueryUtils::fetchRecords($sql, $sqlBindArray);
            $processingResult = new ProcessingResult();
            // end AI Generated
            foreach ($statementResults as $row) {
                // Convert UUIDs to string format
                $row['uuid'] = UuidRegistry::uuidToString($row['uuid']);
                $row['lists_uuid'] = UuidRegistry::uuidToString($row['lists_uuid']);
                $row['uuid'] = $this->getConditionFhirUuid($row);
                $row['encounter_uuid'] = UuidRegistry::uuidToString($row['encounter_uuid']);
                $row['puuid'] = UuidRegistry::uuidToString($row['puuid']);
                if (!empty($row['creator_uuid'])) {
                    $row['creator_uuid'] = UuidRegistry::uuidToString($row['creator_uuid']);
                }
                if (!empty($row['updator_uuid'])) {
                    $row['updator_uuid'] = UuidRegistry::uuidToString($row['updator_uuid']);
                }

                // Add computed clinical status for search filtering
                $row['clinical_status'] = $this->computeClinicalStatus($row);
                $row['verification_status'] = $this->computeVerificationStatus($row);
                $row['category'] = self::CATEGORY_ENCOUNTER_DIAGNOSIS;
                $processingResult->addData($row);
            }
        } catch (SqlQueryException | BadMethodCallException $exception) {
            $processingResult = new ProcessingResult();
            $processingResult->addInternalError($exception->getMessage());
        }
        return $processingResult;
    }

    /**
     * Determine which UUID to use based on condition date
     * AI Generated
     */
    private function getConditionFhirUuid($dataRecord): string
    {
        // originally uuids were on the lists table, but now are in the issue_encounter table
        // we use the condition date to determine which uuid to use
        // the old mechanism has problems in that Condition needs to represent a diagnosis tied to an encounter
        // and the lists table uuid can be connected to multiple encounters which is not correct
        $cutoverTime = strtotime(self::UUID_CUTOVER_DATE);
        $conditionTime = strtotime($dataRecord['date'] ?? $dataRecord['begdate'] ?? '1970-01-01');

        if ($conditionTime >= $cutoverTime) {
            return $dataRecord['uuid'];
        }

        // Historical condition - preserve original UUID
        return $dataRecord['lists_uuid'];
    }

    /**
     * Compute clinical status based on condition data and resolved status
     * AI Generated
     */
    protected function computeClinicalStatus($dataRecord): string
    {
        // Use resolved flag from issue_encounter first
        if ($dataRecord['resolved'] == 1) {
            return 'resolved';
        }
        if ($this->isClinicalStatusInactive($dataRecord)) {
            return 'inactive';
        }

        // Check occurrence and outcome for additional status
        if ($dataRecord['occurrence'] == 1 || $dataRecord['outcome'] == 1) {
            return 'resolved';
        } elseif ($dataRecord['occurrence'] > 1) {
            return 'recurrence';
        }

        // Default to active for ongoing problems
        return 'active';
    }


    /**
     * Parses an OpenEMR condition record, returning the equivalent FHIR Condition Resource
     *
     * @param  array   $dataRecord The source OpenEMR data record
     * @param  boolean $encode     Indicates if the returned resource is encoded into a string. Defaults to false.
     * @return FHIRCondition|string
     */
    public function parseOpenEMRRecord($dataRecord = [], $encode = false): FHIRCondition|string
    {
        $conditionResource = new FHIRCondition();

        $this->populateId($dataRecord, $conditionResource);
        $this->populateMeta($dataRecord, $conditionResource);

        // Required elements for US Core Encounter Diagnosis
        $this->populateCategory($dataRecord, $conditionResource, FhirConditionCategory::ENCOUNTER_DIAGNOSIS);
        $this->populateCode($dataRecord, $conditionResource, 'Encounter Diagnosis');
        $this->populateSubject($dataRecord, $conditionResource);
        $this->populateEncounter($dataRecord, $conditionResource);

        // Must Support elements
        $this->populateClinicalStatus($dataRecord, $conditionResource);
        $this->populateVerificationStatus($dataRecord, $conditionResource);
        $this->populateRecordedDate($dataRecord, $conditionResource);

        // US Core requirements
        $this->populateRecorder($dataRecord, $conditionResource);

        // Optional elements
        $this->populateOnsetDateTime($dataRecord, $conditionResource);
        $this->populateAbatementDateTime($dataRecord, $conditionResource);
        $this->populateAssertedDate($dataRecord, $conditionResource);
        $this->populateNote($dataRecord, $conditionResource);

        if ($encode) {
            return json_encode($conditionResource);
        } else {
            return $conditionResource;
        }
    }

    /**
     *  AI Generated
     * @param $dataRecord
     * @param FHIRCondition $conditionResource
     * @return void
     */
    private function populateEncounter($dataRecord, FHIRCondition $conditionResource): void
    {
        // This is REQUIRED for encounter diagnosis profile
        if (!empty($dataRecord['encounter_uuid'])) {
            $encounter = new FHIRReference();
            $encounter->setReference('Encounter/' . $dataRecord['encounter_uuid']);
            $conditionResource->setEncounter($encounter);
        } else {
            throw new InvalidArgumentException("EncounterDiagnosis must have valid encounter reference");
        }
    }
    // end AI Generated

    /**
     *  AI Generated
     * @param $dataRecord
     * @param FHIRCondition $conditionResource
     * @return void
     */
    private function populateNote($dataRecord, FHIRCondition $conditionResource): void
    {
        if (!empty($dataRecord['comments'])) {
            $note = new FHIRAnnotation();
            $note->setText($dataRecord['comments']);
            $conditionResource->addNote($note);
        }
    }
    // end AI Generated

    public function getSupportedVersions()
    {
        return [self::PROFILE_VERSION_NONE, '6.1.0', self::PROFILE_VERSION_7_0_0, self::PROFILE_VERSION_8_0_0];
    }

    /**
     * Returns the Canonical URIs for the FHIR resource for each of the US Core Implementation Guide Profiles that the
     * resource implements.  Most resources have only one profile, but several like DiagnosticReport and Observation
     * has multiple profiles that must be conformed to.
     * @see https://www.hl7.org/fhir/us/core/CapabilityStatement-us-core-server.html for the list of profiles
     * @return string[]
     */
    public function getProfileURIs(): array
    {
        $profileSets = [];
        $profileSets[] = $this->getProfileForVersions(self::USCGI_PROFILE_ENCOUNTER_DIAGNOSIS_URI, $this->getSupportedVersions());
        return array_merge(...$profileSets);
    }

    public function createProvenanceResource($dataRecord = [], $encode = false): FHIRProvenance|string
    {
        if (!($dataRecord instanceof FHIRCondition)) {
            throw new BadMethodCallException("Data record should be correct instance class");
        }
        $fhirProvenanceService = new FhirProvenanceService();
        $fhirProvenance = $fhirProvenanceService->createProvenanceForDomainResource($dataRecord);
        if ($encode) {
            return json_encode($fhirProvenance);
        } else {
            return $fhirProvenance;
        }
    }

    protected function populateRecorder(array $dataRecord, FHIRCondition $conditionResource)
    {
        // recorder has to be a practitioner w/ a NPI, otherwise we don't set it
        if (!empty($dataRecord['creator_uuid']) && !empty($dataRecord['creator_npi'])) {
            $recorder = new FHIRReference();
            $recorder->setReference('Practitioner/' . $dataRecord['creator_uuid']);
            $conditionResource->setRecorder($recorder);
        }
    }
}
