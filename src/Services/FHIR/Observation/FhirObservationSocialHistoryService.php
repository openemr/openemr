<?php

/**
 * FhirObservationSocialHistoryService.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR\Observation;

use OpenEMR\Common\Uuid\UuidMapping;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRObservation;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\Services\CodeTypesService;
use OpenEMR\Services\FHIR\FhirCodeSystemConstants;
use OpenEMR\Services\FHIR\FhirProvenanceService;
use OpenEMR\Services\FHIR\FhirServiceBase;
use OpenEMR\Services\FHIR\IPatientCompartmentResourceService;
use OpenEMR\Services\FHIR\IResourceUSCIGProfileService;
use OpenEMR\Services\FHIR\Observation\Trait\FhirObservationTrait;
use OpenEMR\Services\FHIR\OpenEMR;
use OpenEMR\Services\FHIR\openEMRSearchParameters;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\FHIR\Traits\VersionedProfileTrait;
use OpenEMR\Services\FHIR\UtilsService;
use OpenEMR\Services\ListService;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\SearchFieldException;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\SearchModifier;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Services\Search\TokenSearchValue;
use OpenEMR\Services\SocialHistoryService;
use OpenEMR\Validators\ProcessingResult;

class FhirObservationSocialHistoryService extends FhirServiceBase implements IPatientCompartmentResourceService, IResourceUSCIGProfileService
{
    use FhirObservationTrait;

    // we set this to be 'Final' which has the follow interpretation
    // 'The observation is complete and there are no further actions needed.'
    // @see http://hl7.org/fhir/R4/valueset-observation-status.html
    const DEFAULT_OBSERVATION_STATUS = "final";

    const SMOKING_CESSATION_CODE = "72166-2";

    const CATEGORY = "social-history";

    const COLUMN_MAPPINGS = [
        // @see http://hl7.org/fhir/R4/observation-vitalsigns.html
        self::SMOKING_CESSATION_CODE => [
            // this code contains a lot of the other vital sign codes and is treated specially in this service.
            'fullcode' => 'LOINC:' . self::SMOKING_CESSATION_CODE
            ,'code' => self::SMOKING_CESSATION_CODE
            ,'description' => 'Tobacco smoking status NHIS'
            // TODO: can we make this more generic? and consolidate it across the observation services?
            ,'column' => 'tobacco' // different than our services but we have to be backwards compatible with the way the underlying service is
            ,'column_codes' => 'smoking_status_codes'
            ,'profiles' => [
                self::USCGI_PROFILE_URI => self::PROFILE_VERSIONS_ALL
            ]
        ]
    ];

    const USCGI_PROFILE_SMOKING_STATUS = 'http://hl7.org/fhir/us/core/StructureDefinition/us-core-smokingstatus';

    /**
     * @var SocialHistoryService
     */
    private $service;

    public function __construct($fhirApiURL = null)
    {
        parent::__construct($fhirApiURL);
        $this->service = new SocialHistoryService();
    }

    public function getResourcePathForCode($code)
    {
        return "category=" . self::CATEGORY . "&code=" . $code;
    }
    public function getCodeFromResourcePath($resourcePath)
    {
        $query_vars = [];
        parse_str($resourcePath, $query_vars);
        return $query_vars['code'] ?? null;
    }

    public function populateResourceMappingUuidsForAll()
    {
        $resourcePathList = [];
        foreach (self::COLUMN_MAPPINGS as $mapping) {
            // TODO: @adunsulag make this a single function call so we can be more effecient
            $resourcePath = $this->getResourcePathForCode($mapping['code']);
            UuidMapping::createMissingResourceUuids('Observation', 'history_data', $resourcePath);
        }
    }

    public function supportsCategory($category)
    {
        return ($category === self::CATEGORY);
    }

    public function supportsCode($code)
    {
        return array_search($code, array_keys(self::COLUMN_MAPPINGS)) !== false;
    }


    /**
     * Returns an array mapping FHIR Resource search parameters to OpenEMR search parameters
     */
    protected function loadSearchParameters()
    {
        return [
            'patient' => $this->getPatientContextSearchField(),
            'code' => new FhirSearchParameterDefinition('code', SearchFieldType::TOKEN, ['code']),
            'category' => new FhirSearchParameterDefinition('category', SearchFieldType::TOKEN, ['category']),
            'date' => new FhirSearchParameterDefinition('date', SearchFieldType::DATETIME, ['date']),
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, [new ServiceField('uuid', ServiceField::TYPE_UUID)]),
            '_lastUpdated' => $this->getLastModifiedSearchField()
        ];
    }

    public function getLastModifiedSearchField(): ?FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('_lastUpdated', SearchFieldType::DATETIME, ['date']);
    }

    /**
     * Searches for OpenEMR records using OpenEMR search parameters
     * @param openEMRSearchParameters OpenEMR search fields
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @return OpenEMR records
     */
    protected function searchForOpenEMRRecords($openEMRSearchParameters): ProcessingResult
    {
        $processingResult = new ProcessingResult();

        try {
            $observationCodesToReturn = [];

            // we check to make sure we only have our category
            if (isset($openEMRSearchParameters['category']) && $openEMRSearchParameters['category'] instanceof TokenSearchField) {
                if (!$openEMRSearchParameters['category']->hasCodeValue(self::CATEGORY)) {
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

            // convert vital sign records from 1:many

            // only return social history where tobacco is populated
            $openEMRSearchParameters['tobacco'] = new TokenSearchField('tobacco', [new TokenSearchValue(false)]);
            $openEMRSearchParameters['tobacco']->setModifier(SearchModifier::MISSING);

            $result = $this->service->search($openEMRSearchParameters, true);
            $data = $result->getData() ?? [];

            // need to transform these into something we can consume
            foreach ($data as $record) {
                // each vital record becomes a 1 -> many record for our observations
                $this->parseSocialHistoryIntoObservationRecords($processingResult, $record, $observationCodesToReturn);
            }
        } catch (SearchFieldException $exception) {
            $processingResult->setValidationMessages([$exception->getField() => $exception->getMessage()]);
        }

        return $processingResult;
    }

    private function parseSocialHistoryIntoObservationRecords(ProcessingResult $processingResult, $record, $observationCodesToReturn)
    {
        $uuidMappings = $this->getUuidMappings(UuidRegistry::uuidToBytes($record['uuid']));
        // convert each record into it's own openEMR record array


        foreach ($observationCodesToReturn as $code) {
            $mapping = self::COLUMN_MAPPINGS[$code];
            if (!isset($mapping)) {
                continue;
            }

            $profileVersions = $mapping['profiles'] ?? [self::USCGI_PROFILE_URI => self::PROFILE_VERSIONS_ALL];
            $profiles = [];
            foreach ($profileVersions as $profile => $versions) {
                $profiles[] = $this->getProfileForVersions($profile, $versions);
            }

            $value = null;
            $valueDescription = null;
            $codes = $record[$mapping['column_codes']] ?? [];
            if (!empty($codes)) {
                $codes = array_values($codes)[0];
                $value = $codes['code_type'] . ':' . $codes['code'];
                $valueDescription = $codes['description'];
            } else {
                $value = $record[$mapping['column']];
            }
            // no value means we skip this observation as value is a required field
            if (empty($value)) {
                continue;
            }

            $observation = [
                "code" => $mapping['fullcode']
                ,"description" => $this->getDescriptionForCode($code)
                ,"ob_type" => self::CATEGORY
                ,"ob_status" => 'final' // we always set this to final as there's no in-between state
                ,"puuid" => $record['puuid']
                ,"uuid" => UuidRegistry::uuidToString($uuidMappings[$code])
                ,"user_uuid" => 'provider_uuid'
                ,"date" => $record['date']
                ,"last_updated" => $record['date']
                ,"profiles" => $this->getProfileForVersions(self::USCGI_PROFILE_SMOKING_STATUS, $this->getSupportedVersions())
                ,"value" => $value
                ,'value_code_description' => $valueDescription
            ];
            $this->addColumnsForCode($observation, $record);
            $processingResult->addData($observation);
        }
    }

    private function getUuidMappings($uuid)
    {
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

    private function populateSmokingCessation(FHIRObservation $observation, $dataRecord)
    {
        if (empty($dataRecord['smoking_status_codes'])) {
            // we are going to create our null flavor code here
            $concept = UtilsService::createDataAbsentUnknownCodeableConcept();
            $observation->setValueCodeableConcept($concept);
        } else {
            $concept = UtilsService::createCodeableConcept($dataRecord['smoking_status_codes'], FhirCodeSystemConstants::SNOMED_CT);
            $observation->setValueCodeableConcept($concept);
        }
    }

    private function addColumnsForCode(array &$observation, array $record)
    {
        $codeMapping = self::COLUMN_MAPPINGS[$observation['code']] ?? null;
        switch ($observation['code']) {
            case self::SMOKING_CESSATION_CODE:
                if (!empty($record['smoking_status_codes'])) {
                    // TODO: @adunsulag should we refactor FhirObservationTrait to take in an array for the value as a codeableconcept?
                    $observation['value'] = $record['smoking_status_codes']['code_type'] . ':' . $record['smoking_status_codes']['code'];
                    $observation['value_code_description'] = $record['smoking_status_codes']['description'];
                }

                if (isset($value)) {
                    $observation['value'] = $value;
                }
                break;
        }
        if (isset($codeMapping)) {
            return is_array($codeMapping['column']) ? $codeMapping['column'] : [$codeMapping['column']];
        }
        return [];
    }

    private function getDescriptionForCode($code)
    {
        $codeMapping = self::COLUMN_MAPPINGS[$code] ?? null;
        if (isset($codeMapping)) {
            return $codeMapping['description'];
        }
        return "";
    }

    /**
     * Creates the Provenance resource  for the equivalent FHIR Resource
     *
     * @param $dataRecord The source OpenEMR data record
     * @param $encode Indicates if the returned resource is encoded into a string. Defaults to True.
     * @return the FHIR Resource. Returned format is defined using $encode parameter.
     */
    public function createProvenanceResource($dataRecord, $encode = false)
    {
        if (!($dataRecord instanceof FHIRObservation)) {
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

    public function getPatientContextSearchField(): FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('patient', SearchFieldType::REFERENCE, [new ServiceField('puuid', ServiceField::TYPE_UUID)]);
    }

    public function getProfileURIs(): array
    {
        return $this->getProfileForVersions(self::USCGI_PROFILE_SMOKING_STATUS, $this->getSupportedVersions());
    }
}
