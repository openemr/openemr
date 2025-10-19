<?php

/*
 * FhirObservationObservationFormService.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR\Observation;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidMapping;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRObservation;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;
use OpenEMR\Services\FHIR\FhirServiceBase;
use OpenEMR\Services\FHIR\IPatientCompartmentResourceService;
use OpenEMR\Services\FHIR\Observation\Trait\FhirObservationTrait;
use OpenEMR\Services\FHIR\Traits\VersionedProfileTrait;
use OpenEMR\Services\ObservationService;
use OpenEMR\Services\SDOH\HistorySdohService;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\FhirSearchWhereClauseBuilder;
use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Services\Search\SearchFieldException;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Validators\ProcessingResult;

class FhirObservationHistorySdohService extends FhirServiceBase implements IPatientCompartmentResourceService
{
    use FhirObservationTrait;
    use VersionedProfileTrait;

    const USCGI_PROFILE_PREGNANCY_STATUS = self::HTTP_HL_7_ORG_FHIR_US_CORE_STRUCTURE_DEFINITION_US_CORE_PREGNANCY_STATUS;
    const USCDI_PREGNANCY_INTENT = 'http://hl7.org/fhir/us/core/StructureDefinition/us-core-observation-pregnancyintent';
    const USCDI_PREGNANCY_STATUS = 'http://hl7.org/fhir/us/core/StructureDefinition/us-core-observation-pregnancystatus';

    const USCGI_PROFILE_SCREENING_URI = 'http://hl7.org/fhir/us/core/StructureDefinition/us-core-observation-screening-assessment';
    const CATEGORY_SOCIAL_HISTORY = 'social-history';
    const CATEGORY_SDOH = 'sdoh';
    const SUPPORTED_CATEGORIES = ['survey',self::CATEGORY_SDOH, self::CATEGORY_SOCIAL_HISTORY];
    const PREGNANCY_STATUS_LOINC_CODE = '82810-3'; // Pregnancy status
    const PREGNANCY_INTENT_LOINC_CODE = '86645-9'; // Pregnancy intent

    const OCCUPATION_LOINC_CODE = '11378-7'; // Occupation
    const COLUMN_MAPPINGS = [
        // TODO: @adunsulag need to handle the codes that are in the HistorySdohService::getDomainHealthConcernCodes()
        self::PREGNANCY_STATUS_LOINC_CODE => [
            // this code contains a lot of the other vital sign codes and is treated specially in this service.
            'fullcode' => 'LOINC:' . self::PREGNANCY_STATUS_LOINC_CODE
            ,'code' => self::PREGNANCY_STATUS_LOINC_CODE
            ,'description' => 'Pregnancy status'
            ,'column' => 'pregnancy_status'
            ,'category' => self::CATEGORY_SOCIAL_HISTORY
            ,'screening_category_code' => null
            ,'screening_category_display' => null
            ,'profiles' => [
                self::USCDI_PREGNANCY_STATUS => self::PROFILE_VERSIONS_V2
            ]
        ],
        self::PREGNANCY_INTENT_LOINC_CODE => [
            'fullcode' => 'LOINC:' . self::PREGNANCY_INTENT_LOINC_CODE
            ,'code' => self::PREGNANCY_INTENT_LOINC_CODE
            ,'description' => 'Pregnancy intent'
            ,'column' => 'pregnancy_intent'
            ,'category' => self::CATEGORY_SOCIAL_HISTORY
            ,'screening_category_code' => null
            ,'screening_category_display' => null
            ,'profiles' => [
                self::USCDI_PREGNANCY_INTENT => self::PROFILE_VERSIONS_V2
            ]
        ],
    ];
    const HTTP_HL_7_ORG_FHIR_US_CORE_STRUCTURE_DEFINITION_US_CORE_PREGNANCY_STATUS = "http://hl7.org/fhir/us/core/StructureDefinition/us-core-pregnancy-status";

    /**
     * @var array The column mappings for SDOH and pregnancy codes (populated in getColumnMappings)
     */
    private array $columnMappings;
    private HistorySdohService $sdohHistoryService;

    public function __construct($fhirApiURL = null)
    {
        parent::__construct($fhirApiURL);
        $this->sdohHistoryService = new HistorySdohService();
    }

    public function supportsCategory($category): bool
    {
        return in_array($category, self::SUPPORTED_CATEGORIES);
    }

    public function supportsCode(string $code): bool
    {
        return in_array($code, $this->getSupportedCodes());
    }


    /**
     * Returns an array mapping FHIR Resource search parameters to OpenEMR search parameters
     * @return array<string, FhirSearchParameterDefinition>
     */
    protected function loadSearchParameters(): array
    {
        return [
            'patient' => $this->getPatientContextSearchField(),
            'code' => new FhirSearchParameterDefinition('code', SearchFieldType::TOKEN, ['code']),
            'category' => new FhirSearchParameterDefinition('category', SearchFieldType::TOKEN, ['category']),
            'date' => new FhirSearchParameterDefinition('date', SearchFieldType::DATETIME, ['created_at']),
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, [
                new ServiceField('uuid', ServiceField::TYPE_UUID)
                ]),
            '_lastUpdated' => $this->getLastModifiedSearchField()
        ];
    }

    public function getLastModifiedSearchField(): ?FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('_lastUpdated', SearchFieldType::DATETIME, ['updated_at']);
    }

    /**
     * Searches for OpenEMR records using OpenEMR search parameters
     * @param array<string, ISearchField> $openEMRSearchParameters OpenEMR search fields
     * @return ProcessingResult OpenEMR records
     */
    protected function searchForOpenEMRRecords($openEMRSearchParameters): ProcessingResult
    {
        $processingResult = new ProcessingResult();

        try {
            $observationCodesToReturn = [];

            // once we've reached here (via calls to supportCategory and supportCode) we know that any category or code
            // parameters are supported by this service, so we can just remove them from the search parameters
            if (isset($openEMRSearchParameters['category']) && $openEMRSearchParameters['category'] instanceof TokenSearchField) {
                foreach ($openEMRSearchParameters['category']->getValues() as $value) {
                    $category = $value->getCode();
                    if ($category === self::CATEGORY_SOCIAL_HISTORY) {
                        $observationCodesToReturn[self::PREGNANCY_INTENT_LOINC_CODE] = self::PREGNANCY_INTENT_LOINC_CODE;
                        $observationCodesToReturn[self::PREGNANCY_STATUS_LOINC_CODE] = self::PREGNANCY_STATUS_LOINC_CODE;
                    } else if ($category === 'survey') { // base category for SDOH is 'survey' but supplemental category is 'sdoh'
                        // grab everything, but social-history codes are handled by the social-history category
                        $codes = $this->getSupportedCodes();
                        $codes = array_diff($codes, [self::PREGNANCY_INTENT_LOINC_CODE, self::PREGNANCY_STATUS_LOINC_CODE]);
                        $observationCodesToReturn = array_combine($codes, $codes);
                        break; // no need to continue since we are grabbing everything
                    }
                }
                unset($openEMRSearchParameters['category']);
            }

            if (isset($openEMRSearchParameters['code'])) {
                $codesToInclude = [];
                /**
                 * @var TokenSearchField
                 */
                $code = $openEMRSearchParameters['code'];
                if (!($code instanceof TokenSearchField)) {
                    throw new SearchFieldException('code', "Invalid code");
                }
                foreach ($code->getValues() as $value) {
                    $code = $value->getCode();
                    $codesToInclude[$code] = $code;
                }
                unset($openEMRSearchParameters['code']);
                // codes are a constraint inside the category so we have to find the intersection of the codes and the category
                if (!empty($observationCodesToReturn)) {
                    $observationCodesToReturn = array_intersect($observationCodesToReturn, $codesToInclude);
                } else {
                    // we haven't constrained by category so we can just use the codes
                    $observationCodesToReturn = $codesToInclude;
                }
            }


            if (empty($observationCodesToReturn)) {
                // grab everything
                $observationCodesToReturn = $this->getSupportedCodes();
                $observationCodesToReturn = array_combine($observationCodesToReturn, $observationCodesToReturn);
            }
            $results = $this->sdohHistoryService->search($openEMRSearchParameters);
            if ($results->hasData()) {
                $records = $results->getData();
                foreach ($records as $record) {
                    // concatenate the results of the record into many observations
                    $processingResult->addProcessingResult($this->parseRecordsIntoObservations($record, $observationCodesToReturn));
                }
            }
        } catch (SearchFieldException $exception) {
            $processingResult->setValidationMessages([$exception->getField() => $exception->getMessage()]);
        }
        return $processingResult;
    }

    public function getPatientContextSearchField(): FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('patient', SearchFieldType::REFERENCE, [new ServiceField('puuid', ServiceField::TYPE_UUID)]);
    }

    protected function parseRecordsIntoObservations(array $record, array $observationCodesToReturn): ProcessingResult
    {
        $columnMappings = $this->getColumnMappings();
        $uuidMappings = $this->getUUidMappingsForRecord(UuidRegistry::uuidToBytes($record['uuid']));
        $processingResult = new ProcessingResult();
        foreach ($observationCodesToReturn as $code) {
            if (
                !(isset($uuidMappings[$code])
                && isset($columnMappings[$code])
                // we need to make sure we have an actual value in the record for the observation to populate
                && isset($record[$columnMappings[$code]['column']]))
            ) {
                // no mapping or uuid for this code
                continue;
            }
            $mapping = $columnMappings[$code];
            $profileVersions = $mapping['profiles'] ?? [self::USCGI_PROFILE_URI => self::PROFILE_VERSIONS_ALL];
            $profiles = [];
            foreach ($profileVersions as $profile => $versions) {
                $profiles[] = $this->getProfileForVersions($profile, $versions);
            }
            $profiles = array_merge(...$profiles);
            $value = $record[$mapping['column']] ?? null;
            $valueDescription =  $mapping['description'] ?? null;
            // if we have a db interaction that populates a _codes column we will use that as the value and the display column as the description
            // as it has the master source record
            $column = $mapping['column'];
            if (isset($record[$column . '_codes'])) {
                $value = $record[$column . '_codes'];
                $valueDescription = $record[$mapping['column'] . '_display'] ?? null;
            }
            $observation = [
                "code" => $mapping['fullcode']
                , "description" => $mapping['description'] ?? null
                // TODO: @adunsulag we may need to verify this assumption
                , "ob_status" => 'final' // for social-history observations we always set this to final
                , "ob_type" => $mapping['category'] ?? 'sdoh'
                , 'screening_category_code' => $mapping['screening_category_code'] ?? null
                , 'screening_category_display' => $mapping['screening_category_display'] ?? null
                , "puuid" => $record['puuid']
                , "euuid" => $record['euuid']
                , "uuid" => UuidRegistry::uuidToString($uuidMappings[$code])
                , "user_uuid" => $record['updated_by_uuid'] // we use updated by since this is the last person to modify the observation
                , "date" => $record['created_at']
                , "last_updated" => $record['updated_at']
                , "profiles" => $profiles
                , 'value' => $value // only a single column so we can directly map it
                , 'value_code_description' => $valueDescription
            ];

            $columns = $this->getColumnsForCode($code);
            // if any value of the column is populated we will return that the record has a value.
            foreach ($columns as $column) {
                if (isset($record[$column]) && $record[$column] != "") {
                    $observation[$column] = $record[$column];
                }
            }
            $processingResult->addData($observation);
        }
        return $processingResult;
    }

    public function getUUidMappingsForRecord(string $uuid): array
    {
        // TODO: @adunsulag this creates the 1:N database fetch problem. We need to optimize this., same problem exists in vitals service
        $mappedRecords = UuidMapping::getMappedRecordsForTableUUID($uuid);
        $codeMappings = [];
        if (!empty($mappedRecords)) {
            foreach ($mappedRecords as $record) {
                $resourcePath = $record['resource_path'] ?? '';
                $code = $this->getCodeFromResourcePath($resourcePath);
                if (empty($code)) {
                    // TODO: @adunsulag handle this exception
                    continue;
                }
                $codeMappings[$code] = $record['uuid'];
            }
        }
        return $codeMappings;
    }

    // TODO: @adunsulag can we turn this into a trait and share it with Vitals and other services that need to parse the code from the resource path?
    public function getCodeFromResourcePath($resourcePath)
    {
        $query_vars = [];
        parse_str((string) $resourcePath, $query_vars);
        return $query_vars['code'] ?? null;
    }

    private function getColumnsForCode($code)
    {
        $codeMapping = self::COLUMN_MAPPINGS[$code] ?? null;
        if (isset($codeMapping)) {
            return is_array($codeMapping['column']) ? $codeMapping['column'] : [$codeMapping['column']];
        }
        return [];
    }

    public function getSupportedCodes()
    {
        return array_keys($this->getColumnMappings());
    }

    public function getColumnMappings()
    {
        if (!isset($this->columnMappings)) {
            $columnMappings = self::COLUMN_MAPPINGS;
            $sdohCodes = HistorySdohService::getDomainHealthConcernCodes();
            /**
             *self::PREGNANCY_STATUS_LOINC_CODE => [
             * // this code contains a lot of the other vital sign codes and is treated specially in this service.
             * 'fullcode' => 'LOINC:' . self::PREGNANCY_STATUS_LOINC_CODE
             * ,'code' => self::PREGNANCY_STATUS_LOINC_CODE
             * ,'description' => 'Pregnancy status'
             * ,'column' => 'pregnancy_status'
             * ,'category' => 'social-history'
             * ,'profiles' => [
             * self::USCDI_PREGNANCY_STATUS => self::PROFILE_VERSIONS_ALL
             * ]
             * ],
             * 'food_insecurity' => [
             * 'snomed' => [
             * 'code' => self::CODE_FOOD_INSECURITY_SCT,
             * 'display' => 'Food insecurity (finding)',
             * 'system' => $SNOMED_OID,
             * 'system_name' => 'SNOMED CT',
             * ],
             * 'icd10' => [
             * 'code' => self::CODE_FOOD_INSECURITY_ICD10CM,
             * 'display' => 'Food insecurity',
             * 'system' => $ICD10_OID,
             * 'system_name' => 'ICD-10-CM',
             * ],
             * ],
             */
            foreach ($sdohCodes as $column => $mapping) {
                foreach (['SNOMED-CT' => 'snomed', 'ICD-10-CM' => 'icd10'] as $systemPrefix => $codeSystem) {
                    if (isset($mapping[$codeSystem])) {
                        $fullcode = $systemPrefix . ':' . ($mapping[$codeSystem]['code'] ?? '');
                        $columnMappings[$mapping[$codeSystem]['code']] = [
                            'fullcode' => $fullcode,
                            'code' => $mapping[$codeSystem]['code'] ?? '',
                            'description' => $mapping[$codeSystem]['display'] ?? $mapping[$codeSystem]['code'],
                            'category' => 'survey',
                            'screening_category_code' => self::CATEGORY_SDOH,
                            'screening_category_display' => 'Social Determinants of Health',
                            'profiles' => [
                                self::USCGI_SCREENING_ASSESSMENT_URI => self::PROFILE_VERSIONS_ALL
                            ],
                            "{$column}_codes" => $fullcode,
                            "{$column}_display" =>  $mapping[$codeSystem]['display'] ?? $mapping[$codeSystem]['code'],
                            // TODO: until we can figure out how to treat the SDOH history as an actual QuestionnaireResponse, we will will skip the derivedFrom
                            'column' => $column,
                        ];
                    }
                }
                $this->columnMappings = $columnMappings;
            }
        }
        return $this->columnMappings;
    }
}
