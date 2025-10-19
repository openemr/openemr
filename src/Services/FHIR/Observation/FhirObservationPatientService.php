<?php

/*
 * FhirObservationPatientService.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR\Observation;

use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\R4\FHIRElement\FHIRUri;
use OpenEMR\Services\FHIR\FhirCodeSystemConstants;
use OpenEMR\Services\FHIR\FhirServiceBase;
use OpenEMR\Services\FHIR\IPatientCompartmentResourceService;
use OpenEMR\Services\FHIR\IResourceUSCIGProfileService;
use OpenEMR\Services\FHIR\Observation\Trait\FhirObservationTrait;
use OpenEMR\Services\ListService;
use OpenEMR\Services\PatientService;
use OpenEMR\Services\Search\CompositeSearchField;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Services\Search\SearchFieldException;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\SearchModifier;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Services\Search\TokenSearchValue;
use OpenEMR\Validators\ProcessingResult;

class FhirObservationPatientService extends FhirServiceBase implements IPatientCompartmentResourceService, IResourceUSCIGProfileService
{
    use FhirObservationTrait;

    const CATEGORY_SOCIAL_HISTORY = 'social-history';

    const SEXUAL_ORIENTATION_LOINC_CODE = '76690-7';

    const USCDI_PROFILE_SEXUAL_ORIENTATION_URI = 'http://hl7.org/fhir/us/core/StructureDefinition/us-core-observation-sexual-orientation';

    const COLUMN_MAPPINGS = [
        self::SEXUAL_ORIENTATION_LOINC_CODE => [
            'fullcode' => 'LOINC:' . self::SEXUAL_ORIENTATION_LOINC_CODE
            ,'code' => self::SEXUAL_ORIENTATION_LOINC_CODE
            ,'description' => 'Sexual Orientation'
            ,'column' => 'sexual_orientation'
            ,'list_id' => 'sexual_orientation'
            ,'category' => self::CATEGORY_SOCIAL_HISTORY
            ,'screening_category_code' => null
            ,'screening_category_display' => null
            ,'profiles' => [
                self::USCDI_PROFILE_SEXUAL_ORIENTATION_URI => self::PROFILE_VERSIONS_V2
            ]
        ],
    ];

    private array $listOptionsByListId = [];

    private ?ListService $listService = null;

    public function supportsCode(string $code): bool
    {
        return isset(self::COLUMN_MAPPINGS[$code]);
    }
    public function supportsCategory($category)
    {
        return $category === self::CATEGORY_SOCIAL_HISTORY;
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
            'date' => new FhirSearchParameterDefinition('date', SearchFieldType::DATETIME, ['date']),
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, [
                new ServiceField('uuid', ServiceField::TYPE_UUID)
            ]),
            '_lastUpdated' => $this->getLastModifiedSearchField()
        ];
    }

    public function getPatientContextSearchField(): FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('patient', SearchFieldType::REFERENCE, [new ServiceField('uuid', ServiceField::TYPE_UUID)]);
    }

    public function getLastModifiedSearchField(): ?FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('_lastUpdated', SearchFieldType::DATETIME, ['date']);
    }

    public function getListService(): ListService
    {
        if (!isset($this->listService)) {
            $this->listService = new ListService();
        }
        return $this->listService;
    }

    public function setListService(ListService $service): void
    {
        $this->listService = $service;
    }

    protected function getListOptionsByListId()
    {
        // need to grab all the list options for the lists we care about so we can populate the observations
        if (empty($this->listOptionsByListId)) {
            $listService = $this->getListService();
            $listOptions = $listService->getListOptionsForLists([
                'sexual_orientation'
            ]);
            foreach ($listOptions as $record) {
                $listId = $record['list_id'];
                if (!isset($this->listOptionsByListId[$listId])) {
                    $this->listOptionsByListId[$listId] = [];
                }
                $this->listOptionsByListId[$listId][$record['option_id']] = $record;
            }
        }
        return $this->listOptionsByListId;
    }

    /**
     * Get code system URL from prefix
     */
    protected function getCodeSystem(string $prefix): string
    {
        $system = $this->getCodeTypesService()->getSystemForCodeType($prefix);
        return $system ?? FhirCodeSystemConstants::LOINC;
    }

    protected function getListOption(string $listId, string $optionId)
    {
        $listOptionsByListId = $this->getListOptionsByListId();
        return $listOptionsByListId[$listId][$optionId] ?? null;
    }

    // patient_data observations currently are the following:
    // occupation, industry
    // sexual_orientation
    /**
     * @param array<string, ISearchField> $openEMRSearchParameters OpenEMR search fields
     * @return ProcessingResult
     */

    protected function searchForOpenEMRRecords($openEMRSearchParameters): ProcessingResult
    {
        $processingResult = new ProcessingResult();

        try {
            $observationCodesToReturn = [];

            // we check to make sure we only have our category
            if (isset($openEMRSearchParameters['category']) && $openEMRSearchParameters['category'] instanceof TokenSearchField) {
                if (!$openEMRSearchParameters['category']->hasCodeValue(self::CATEGORY_SOCIAL_HISTORY)) {
                    throw new SearchFieldException("category", "invalid value");
                }
                // we only support one category and then we remove it.
                unset($openEMRSearchParameters['category']);
            }

            if (isset($openEMRSearchParameters['code'])) {
                /**
                 * @var TokenSearchField
                 */
                $code = $openEMRSearchParameters['code'];
                if (!($code instanceof TokenSearchField)) {
                    throw new SearchFieldException('code', "Invalid code");
                }
                foreach ($code->getValues() as $value) {
                    $code = $value->getCode();
                    $observationCodesToReturn[$code] = $code;
                }
                unset($openEMRSearchParameters['code']);
            }

            if (empty($observationCodesToReturn)) {
                // grab everything
                $observationCodesToReturn = array_keys(self::COLUMN_MAPPINGS);
                $observationCodesToReturn = array_combine($observationCodesToReturn, $observationCodesToReturn);
            }
            // TODO: @adunsulag we can optimize this by using a compound search parameter that will ONLY return records
            // that have one of the codes we want.  This will require a new search parameter type that can handle
            // multiple token values with an OR relationship.
            // only return social history where tobacco is populated
            $observationFields = new CompositeSearchField('observation-fields', [], false);
            foreach (self::COLUMN_MAPPINGS as $code => $mapping) {
                if (in_array($code, $observationCodesToReturn)) {
                    $field = new TokenSearchField($mapping['column'], new TokenSearchValue(false));
                    $field->setModifier(SearchModifier::MISSING);
                    $observationFields->addChild($field);
                }
                if (isset($mapping['components']) && is_array($mapping['components'])) {
                    foreach ($mapping['components'] as $componentMapping) {
                        $field = new TokenSearchField($componentMapping['column'], new TokenSearchValue(false));
                        $field->setModifier(SearchModifier::MISSING);
                        $observationFields->addChild($field);
                    }
                }
            }
            // only add this if we have something to search on
            if (!empty($observationFields->getChildren())) {
                $openEMRSearchParameters[$observationFields->getName()] = $observationFields;
            }

            // generally this will return one record (patient reference), but if there is no patient reference
            // it will return ALL patient records in order to grab the specific observations that are mapped in
            // the patient_data table
            $patientService = new PatientService();
            $result = $patientService->search($openEMRSearchParameters, true);
            $data = $result->getData() ?? [];

            // need to transform these into something we can consume
            foreach ($data as $record) {
                // each vital record becomes a 1 -> many record for our observations
                $this->parsePatientDataIntoObservationRecords($processingResult, $record, $observationCodesToReturn);
            }
        } catch (SearchFieldException $exception) {
            $processingResult->setValidationMessages([$exception->getField() => $exception->getMessage()]);
        }

        return $processingResult;
    }

    private function parsePatientDataIntoObservationRecords(ProcessingResult $processingResult, array $record, array $observationCodesToReturn)
    {
        $uuidMappings = $this->getUuidMappings(UuidRegistry::uuidToBytes($record['uuid']));
        // convert each record into it's own openEMR record array
        foreach ($observationCodesToReturn as $code) {
            $mapping = self::COLUMN_MAPPINGS[$code] ?? null;
            if (!isset($mapping)) {
                continue;
            }
            if (!isset($uuidMappings[$code])) {
                $this->getSystemLogger()->errorLogCaller("No UUID mapping for patient_data record ", ['uuid' => $record['uuid'], 'code' => $code]);
                continue;
            }

            $profileVersions = $mapping['profiles'] ?? [self::USCGI_PROFILE_URI => self::PROFILE_VERSIONS_ALL];
            $profiles = [];
            foreach ($profileVersions as $profile => $versions) {
                $profiles[] = $this->getProfileForVersions($profile, $versions);
            }
            $profiles = array_merge(...$profiles);

            $value = $record[$mapping['column']] ?? null;
            if (!empty($value) && !empty($mapping['list_id'])) {
                $optionRecord = $this->getListOption($mapping['list_id'], $value);
                $value = $optionRecord['codes'] ?? $value;
                $valueDescription = $optionRecord['title'] ?? '';
            } else {
                continue; // we only support coded values at this time
            }

            $observation = [
                "code" => $mapping['fullcode']
                ,"description" => $mapping['description']
                ,"ob_type" => self::CATEGORY_SOCIAL_HISTORY
                ,"ob_status" => 'final' // we always set this to final as there's no in-between state
                ,"puuid" => $record['uuid']
                ,"uuid" => UuidRegistry::uuidToString($uuidMappings[$code])
                ,"user_uuid" => 'provider_uuid'
                ,"date" => $record['date']
                ,"last_updated" => $record['date']
                ,"profiles" => $profiles
                ,"value" => $value
                ,'value_code_description' => $valueDescription
            ];
            if (isset($mapping['components']) && is_array($mapping['components'])) {
                foreach ($mapping['components'] as $componentMapping) {
                    $componentValue = $record[$componentMapping['column']] ?? null;
                    if (!empty($componentValue) && !empty($componentMapping['list_id'])) {
                        $optionRecord = $this->getListOption($componentMapping['list_id'], $componentValue);
                        $componentValue = $optionRecord['codes'] ?? $componentValue;
                        $componentValueDescription = $optionRecord['title'] ?? '';
                    } else {
                        continue; // we only support coded values at this time
                    }
                    $observation['components'][] = [
                        'code' => $componentMapping['fullcode']
                        ,'description' => $componentMapping['description']
                        ,'value' => $componentValue
                        ,'value_code_description' => $componentValueDescription
                    ];
                }
            }
            $processingResult->addData($observation);
        }
    }

    public function getSupportedVersions()
    {
        return self::PROFILE_VERSIONS_V2;
    }

    public function getProfileURIs(): array
    {
        $profileSets = [
            $this->getProfileForVersions(self::USCDI_PROFILE_SEXUAL_ORIENTATION_URI, $this->getSupportedVersions())
        ];
        return array_merge(...$profileSets);
    }
}
