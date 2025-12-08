<?php

/*
 * FhirObservationEmploymentService.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR\Observation;

use OpenEMR\Services\EmployerService;
use OpenEMR\Services\FHIR\FhirCodeSystemConstants;
use OpenEMR\Services\FHIR\FhirServiceBase;
use OpenEMR\Services\FHIR\IPatientCompartmentResourceService;
use OpenEMR\Services\FHIR\IResourceUSCIGProfileService;
use OpenEMR\Services\FHIR\Observation\Trait\FhirObservationTrait;
use OpenEMR\Services\ListService;
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

class FhirObservationEmployerService extends FhirServiceBase implements IPatientCompartmentResourceService, IResourceUSCIGProfileService
{
    use FhirObservationTrait;

    const CATEGORY_SOCIAL_HISTORY = 'social-history';

    const OCCUPATION_LOINC_CODE = '11341-5';
    const OCCUPATION_INDUSTRY_LOINC_CODE = '86188-0';

    const USCDI_PROFILE_OCCUPATION_URI = 'http://hl7.org/fhir/us/core/StructureDefinition/us-core-observation-occupation';

    const COLUMN_MAPPINGS = [
        self::OCCUPATION_LOINC_CODE => [
            'fullcode' => 'LOINC:' . self::OCCUPATION_LOINC_CODE
            ,'code' => self::OCCUPATION_LOINC_CODE
            ,'description' => 'Occupation'
            ,'column' => 'occupation'
            ,'list_id' => 'OccupationODH'
            ,'category' => self::CATEGORY_SOCIAL_HISTORY
            ,'screening_category_code' => null
            ,'screening_category_display' => null
            ,'profiles' => [
                self::USCDI_PROFILE_OCCUPATION_URI => self::PROFILE_VERSIONS_V2
            ]
            ,'components' => [
                self::OCCUPATION_INDUSTRY_LOINC_CODE => [
                    'fullcode' => 'LOINC:' . self::OCCUPATION_INDUSTRY_LOINC_CODE
                    ,'code' => self::OCCUPATION_INDUSTRY_LOINC_CODE
                    ,'description' => 'History of Occupation industry'
                    ,'column' => 'industry'
                    ,'list_id' => 'IndustryODH'
                ]
            ]
        ],
    ];

    private array $listOptionsByListId = [];

    private ?ListService $listService = null;

    private ?EmployerService $employerService = null;

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
            'date' => new FhirSearchParameterDefinition('date', SearchFieldType::DATETIME, ['start_date']),
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, [
                new ServiceField('uuid', ServiceField::TYPE_UUID)
            ]),
            '_lastUpdated' => $this->getLastModifiedSearchField()
        ];
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

    public function getEmployerService(): EmployerService
    {
        if (!isset($this->employerService)) {
            $this->employerService = new EmployerService();
        }
        return $this->employerService;
    }

    public function setEmployerService(EmployerService $service): void
    {
        $this->employerService = $service;
    }

    protected function getListOptionsByListId(): array
    {
        // need to grab all the list options for the lists we care about so we can populate the observations
        if (empty($this->listOptionsByListId)) {
            $listService = $this->getListService();
            $listOptions = $listService->getListOptionsForLists([
                'OccupationODH'
                ,'Occupation'
                ,'IndustryODH'
                ,'Industry'
            ]);
            foreach ($listOptions as $record) {
                $listId = $record['list_id'];
                if (!isset($this->listOptionsByListId[$listId])) {
                    $this->listOptionsByListId[$listId] = [];
                }
                $this->listOptionsByListId[$listId][$record['option_id']] = $record;
            }
            foreach (['IndustryODH', 'Industry'] as $listId) {
                foreach ($this->listOptionsByListId[$listId] as $index => $record) {
                    $this->listOptionsByListId[$listId][$index]['codes'] = '2.16.840.1.114222.4.11.7900' . ':' . $record['codes'];
                }
            }
            foreach (['OccupationODH', 'Occupation'] as $listId) {
                foreach ($this->listOptionsByListId[$listId] as $index => $record) {
                    $this->listOptionsByListId[$listId][$index]['codes'] = '2.16.840.1.114222.4.11.7901' . ':' . $record['codes'];
                }
            }
        }
        return $this->listOptionsByListId;
    }

    /**
     * Get code system URL from prefix
     */
    protected function getCodeSystem(string $prefix): string
    {
        // special handling for codes that have a dot in them, we can assume these are Industry or Occupation codes
        if (str_contains($prefix, '.')) {
            if (str_contains(FhirCodeSystemConstants::INDUSTRY_NAICS_DETAIL_ODH, $prefix)) {
                // special case for industry codes
                return FhirCodeSystemConstants::INDUSTRY_NAICS_DETAIL_ODH;
            } else if (str_contains(FhirCodeSystemConstants::OCCUPATION_ODH, $prefix)) {
                // special case for occupation codes
                return FhirCodeSystemConstants::OCCUPATION_ODH;
            }
        }

        $system = $this->getCodeTypesService()->getSystemForCodeType($prefix);
        return $system ?? FhirCodeSystemConstants::LOINC;
    }

    protected function getListOption(string $listId, string $optionId): ?array
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

            // we only search for records that have something in occupation OR industry
            $occupationIndustry = new CompositeSearchField('occupation-industry', [], false);
            $occupationField = new TokenSearchField('occupation', [new TokenSearchValue(false)]);
            $occupationField->setModifier(SearchModifier::MISSING);
            $occupationIndustry->addChild($occupationField);
            $industryField = new TokenSearchField('industry', [new TokenSearchValue(false)]);
            $industryField->setModifier(SearchModifier::MISSING);
            $occupationIndustry->addChild($industryField);
            $openEMRSearchParameters[$occupationIndustry->getName()] = $occupationIndustry;

            // generally this will return one record (patient reference), but if there is no patient reference
            // it will return ALL patient records in order to grab the specific observations that are mapped in
            // the patient_data table
            $service = $this->getEmployerService();
            $result = $service->search($openEMRSearchParameters);
            $data = $result->getData() ?? [];

            // need to transform these into something we can consume
            foreach ($data as $record) {
                // each vital record becomes a 1 -> many record for our observations
                $this->parseEmployerDataIntoObservationRecords($processingResult, $record, $observationCodesToReturn);
            }
        } catch (SearchFieldException $exception) {
            $processingResult->setValidationMessages([$exception->getField() => $exception->getMessage()]);
        }

        return $processingResult;
    }

    private function parseEmployerDataIntoObservationRecords(ProcessingResult $processingResult, array $record, array $observationCodesToReturn): void
    {
        // convert each record into it's own openEMR record array
        foreach ($observationCodesToReturn as $code) {
            $mapping = self::COLUMN_MAPPINGS[$code] ?? null;
            if (!isset($mapping)) {
                continue;
            }

            $profileVersions = $mapping['profiles'] ?? [self::USCGI_PROFILE_URI => self::PROFILE_VERSIONS_ALL];
            $profiles = [];
            foreach ($profileVersions as $profile => $versions) {
                $profiles[] = $this->getProfileForVersions($profile, $versions);
            }
            $profiles = array_merge(...$profiles);

            // for employer data if the value is empty we use a data absent reason, which is handled by FhirObservationTrait
            $value = $record[$mapping['column']] ?? null;
            $valueDescription = null;
            if (!empty($value) && !empty($mapping['list_id'])) {
                $optionRecord = $this->getListOption($mapping['list_id'], $value);
                $value = $optionRecord['codes'] ?? $value;
                $valueDescription = $optionRecord['title'] ?? '';
            }

            $observation = [
                "code" => $mapping['fullcode']
                ,"description" => $mapping['description']
                ,"ob_type" => self::CATEGORY_SOCIAL_HISTORY
                ,"ob_status" => 'final' // we always set this to final as there's no in-between state
                ,"puuid" => $record['puuid']
                ,"uuid" => $record['uuid']
                ,"user_uuid" => $record['user_uuid']
                ,"date" => $record['start_date'] ?? $record['date']
                ,"date_end" => $record['end_date']
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
                        continue; // we only support coded values at this time, so we omit if there's no value
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

    public function getSupportedVersions(): array
    {
        return self::PROFILE_VERSIONS_V2;
    }

    public function getProfileURIs(): array
    {
        $profileSets = [
            $this->getProfileForVersions(self::USCDI_PROFILE_OCCUPATION_URI, $this->getSupportedVersions())
        ];
        return array_merge(...$profileSets);
    }
}
