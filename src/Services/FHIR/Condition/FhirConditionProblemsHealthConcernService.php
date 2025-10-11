<?php

/*
 * FhirConditionProblemsHealthConcernService.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Public Domain for portions marked as AI Generated which were created with the assistance of Claude.AI and Microsoft Copilot
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR\Condition;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRProvenance;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCondition;
use OpenEMR\Services\ConditionService;
use OpenEMR\Services\FHIR\Condition\Enum\FhirConditionCategory;
use OpenEMR\Services\FHIR\Condition\Trait\FhirConditionTrait;
use OpenEMR\Services\FHIR\FhirProvenanceService;
use OpenEMR\Services\FHIR\FhirServiceBase;
use OpenEMR\Services\FHIR\IPatientCompartmentResourceService;
use OpenEMR\Services\FHIR\IResourceUSCIGProfileService;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\FHIR\Traits\MappedServiceCategoryTrait;
use OpenEMR\Services\FHIR\Traits\MappedServiceTrait;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\FhirSearchWhereClauseBuilder;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Validators\ProcessingResult;

class FhirConditionProblemsHealthConcernService extends FhirServiceBase implements IPatientCompartmentResourceService, IResourceUSCIGProfileService
{
    use FhirServiceBaseEmptyTrait;
    use MappedServiceTrait;
    use MappedServiceCategoryTrait;
    use FhirConditionTrait;

    const CATEGORY_SYSTEM = 'http://terminology.hl7.org/CodeSystem/condition-category';
    const CATEGORY_PROBLEM_LIST = 'problem-list-item';
    const CATEGORY_HEALTH_CONCERN = 'health-concern';

    const USCGI_PROFILE_URI_3_1_1 = 'http://hl7.org/fhir/us/core/StructureDefinition/us-core-condition';
    const USCGI_PROFILE_PROBLEMS_HEALTH_CONCERNS_URI = 'http://hl7.org/fhir/us/core/StructureDefinition/us-core-condition-problems-health-concerns';

    // Assessment condition categories for USCDI v4+
    const CATEGORY_ASSESSMENT_CONDITION_SDOH = 'sdoh';
    const CATEGORY_ASSESSMENT_CONDITION_FUNCTIONAL_STATUS = 'functional-status';
    const CATEGORY_ASSESSMENT_CONDITION_DISABILITY_STATUS = 'disability-status';
    const CATEGORY_ASSESSMENT_CONDITION_COGNITIVE_STATUS = 'cognitive-status';
    const CATEGORY_ASSESSMENT_CONDITION_TREATMENT_INTERVENTION_STATUS = 'treatment-intervention-status';
    const CATEGORY_ASSESSMENT_CONDITION_CARE_EXPERIENCE_PREFERENCE = 'care-experience-preference';

    const USCDI_PROFILE = "http://hl7.org/fhir/us/core/StructureDefinition/us-core-condition-problems-health-concerns";

    private $conditionService;

    /**
     * AI Generated
     */
    public function __construct()
    {
        parent::__construct();
        $this->conditionService = new ConditionService();
    }

    public function setConditionService(ConditionService $conditionService)
    {
        $this->conditionService = $conditionService;
    }

    public function getConditionService(): ConditionService
    {
        return $this->conditionService;
    }

    public function supportsCategory(string $category): bool
    {
        return in_array($category, [
            self::CATEGORY_PROBLEM_LIST,
            self::CATEGORY_HEALTH_CONCERN,
            self::CATEGORY_ASSESSMENT_CONDITION_SDOH,
            self::CATEGORY_ASSESSMENT_CONDITION_FUNCTIONAL_STATUS,
            self::CATEGORY_ASSESSMENT_CONDITION_DISABILITY_STATUS,
            self::CATEGORY_ASSESSMENT_CONDITION_COGNITIVE_STATUS,
            self::CATEGORY_ASSESSMENT_CONDITION_TREATMENT_INTERVENTION_STATUS,
            self::CATEGORY_ASSESSMENT_CONDITION_CARE_EXPERIENCE_PREFERENCE
        ]);
    }

    public function getPatientContextSearchField(): FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('patient', SearchFieldType::REFERENCE, [new ServiceField('puuid', ServiceField::TYPE_UUID)]);
    }

    public function getLastModifiedSearchField(): ?FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('_lastUpdated', SearchFieldType::DATETIME, ['last_updated_time']);
    }

    protected function loadSearchParameters()
    {
        return [
            'patient' => $this->getPatientContextSearchField(),
            'code' => new FhirSearchParameterDefinition('code', SearchFieldType::TOKEN, ['diagnosis']),
            'category' => new FhirSearchParameterDefinition('category', SearchFieldType::TOKEN, ['category']),
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, [new ServiceField('condition_uuid', ServiceField::TYPE_UUID)]),
            '_lastUpdated' => $this->getLastModifiedSearchField(),
            'clinical-status' => new FhirSearchParameterDefinition('clinical-status', SearchFieldType::TOKEN, ['clinical_status']),
            'verification-status' => new FhirSearchParameterDefinition('verification-status', SearchFieldType::TOKEN, ['verification_status']),
            'onset-date' => new FhirSearchParameterDefinition('onset-date', SearchFieldType::DATE, ['begdate'])
        ];
    }

    /**
     * Searches for OpenEMR records using OpenEMR search parameters
     * This handles problem list items and health concerns (NOT linked to specific encounters)
     *
     * @param  array openEMRSearchParameters OpenEMR search fields
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @return ProcessingResult
     */
    protected function searchForOpenEMRRecords($openEMRSearchParameters, $puuidBind = null): ProcessingResult
    {
        // This query finds conditions that are NOT linked to specific encounters via issue_encounter
        // These represent ongoing problems/health concerns rather than encounter-specific diagnoses
        $sql = "
        SELECT
            l.id,
            l.uuid AS condition_uuid,
            l.pid,
            l.date AS condition_date,
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
            pd.uuid AS puuid,
            COALESCE(l.modifydate, l.date) as last_updated_time
        FROM lists l
        INNER JOIN patient_data pd ON l.pid = pd.pid
        LEFT JOIN issue_encounter ie ON l.id = ie.list_id AND l.pid = ie.pid
        WHERE l.type = 'medical_problem'
        AND l.activity = 1
        AND ie.list_id IS NULL  -- Only conditions NOT linked to specific encounters
        ";

        $whereClause = FhirSearchWhereClauseBuilder::build($openEMRSearchParameters, true);
        $sql .= $whereClause->getFragment();
        $sqlBindArray = $whereClause->getBoundValues();

        $statementResults = QueryUtils::sqlStatementThrowException($sql, $sqlBindArray);
        $records = [];

        while ($row = sqlFetchArray($statementResults)) {
            // Convert UUIDs to string format
            $row['condition_uuid'] = UuidRegistry::uuidToString($row['condition_uuid']);
            $row['puuid'] = UuidRegistry::uuidToString($row['puuid']);

            // Add computed fields for search filtering
            $row['clinical_status'] = $this->computeClinicalStatus($row);
            $row['verification_status'] = $this->computeVerificationStatus($row);
            $row['category'] = self::CATEGORY_PROBLEM_LIST; // Default category for problem list items

            $records[] = $row;
        }

        $results = new ProcessingResult();
        $results->setData($records);
        return $results;
    }

    /**
     * Compute verification status
     */
    private function computeVerificationStatus($dataRecord): string
    {
        if (!empty($dataRecord['verification'])) {
            return $dataRecord['verification'];
        }
        return 'unconfirmed'; // Default for problem list items
    }

    /**
     * Parses an OpenEMR condition record, returning the equivalent FHIR Condition Resource
     *
     * @param  array   $dataRecord The source OpenEMR data record
     * @param  boolean $encode     Indicates if the returned resource is encoded into a string. Defaults to false.
     * @return FHIRCondition
     */
    public function parseOpenEMRRecord($dataRecord = [], $encode = false)
    {
        $conditionResource = new FHIRCondition();

        $this->populateId($dataRecord, $conditionResource);
        $this->populateMeta($dataRecord, $conditionResource);

        // Required elements for US Core Problems and Health Concerns
        // TODO: @adunsulag will need to differentiate between problem list items and health concerns
        $this->populateCategory($dataRecord, $conditionResource, FhirConditionCategory::PROBLEM_LIST_ITEM);
        $this->populateCategory($dataRecord, $conditionResource, FhirConditionCategory::PROBLEM_LIST_ITEM);
        $this->populateCode($dataRecord, $conditionResource, 'Problem');
        $this->populateSubject($dataRecord, $conditionResource);
        $this->populateClinicalStatus($dataRecord, $conditionResource);

        // Must Support elements
        $this->populateVerificationStatus($dataRecord, $conditionResource);
        $this->populateRecordedDate($dataRecord, $conditionResource);
        $this->populateAssertedDate($dataRecord, $conditionResource);

        // Optional elements
        $this->populateOnsetDateTime($dataRecord, $conditionResource);
        $this->populateAbatementDateTime($dataRecord, $conditionResource);
        $this->populateNote($dataRecord, $conditionResource);

        if ($encode) {
            return json_encode($conditionResource);
        } else {
            return $conditionResource;
        }
    }
    // end AI Generated

    public function getSupportedVersions()
    {
        return [self::PROFILE_VERSION_NONE, self::PROFILE_VERSION_3_1_1,'6.1.0', self::PROFILE_VERSION_7_0_0, self::PROFILE_VERSION_8_0_0];
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
        $profileSets[] = $this->getProfileForVersions(self::USCGI_PROFILE_URI_3_1_1, ['', '3.1.1']);
        $profileSets[] = $this->getProfileForVersions(self::USCGI_PROFILE_PROBLEMS_HEALTH_CONCERNS_URI, $this->getSupportedVersions());
        $profiles = array_merge(...$profileSets);
        return $profiles;
    }



    public function createProvenanceResource($dataRecord = [], $encode = false): FHIRProvenance|string
    {
        if (!($dataRecord instanceof FHIRCondition)) {
            throw new \BadMethodCallException("Data record should be correct instance class");
        }
        $fhirProvenanceService = new FhirProvenanceService();
        $fhirProvenance = $fhirProvenanceService->createProvenanceForDomainResource($dataRecord);
        if ($encode) {
            return json_encode($fhirProvenance);
        } else {
            return $fhirProvenance;
        }
    }
}
