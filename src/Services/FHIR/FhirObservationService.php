<?php

namespace OpenEMR\Services\FHIR;

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Uuid\UuidMapping;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRObservation;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;
use OpenEMR\Services\BaseService;
use OpenEMR\Services\FHIR\Observation\FhirObservationAdvanceDirectiveService;
use OpenEMR\Services\FHIR\Observation\FhirObservationCareExperiencePreferenceService;
use OpenEMR\Services\FHIR\Observation\FhirObservationEmployerService;
use OpenEMR\Services\FHIR\Observation\FhirObservationHistorySdohService;
use OpenEMR\Services\FHIR\Observation\FhirObservationLaboratoryService;
use OpenEMR\Services\FHIR\Observation\FhirObservationObservationFormService;
use OpenEMR\Services\FHIR\Observation\FhirObservationPatientService;
use OpenEMR\Services\FHIR\Observation\FhirObservationQuestionnaireItemService;
use OpenEMR\Services\FHIR\Observation\FhirObservationSocialHistoryService;
use OpenEMR\Services\FHIR\Observation\FhirObservationTreatmentInterventionPreferenceService;
use OpenEMR\Services\FHIR\Observation\FhirObservationVitalsService;
use OpenEMR\Services\FHIR\Traits\BulkExportSupportAllOperationsTrait;
use OpenEMR\Services\FHIR\Traits\FhirBulkExportDomainResourceTrait;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\FHIR\Traits\MappedServiceCodeTrait;
use OpenEMR\Services\FHIR\Traits\PatientSearchTrait;
use OpenEMR\Services\FHIR\Traits\VersionedProfileTrait;
use OpenEMR\Services\ObservationLabService;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\SearchFieldException;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Validators\ProcessingResult;

/**
 * FHIR Observation Service
 *
 * @package            OpenEMR
 * @link               https://www.open-emr.org
 * @author             Yash Bothra <yashrajbothra786gmail.com>
 * @copyright          Copyright (c) 2020 Yash Bothra <yashrajbothra786gmail.com>
 * @license            https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class FhirObservationService extends FhirServiceBase implements IResourceSearchableService, IResourceUSCIGProfileService, IPatientCompartmentResourceService, IFhirExportableResourceService
{
    use FhirServiceBaseEmptyTrait;
    use MappedServiceCodeTrait;
    use PatientSearchTrait;
    use BulkExportSupportAllOperationsTrait;
    use FhirBulkExportDomainResourceTrait;
    use VersionedProfileTrait;

    /**
     * @var ObservationLabService
     */
    private $observationService;

    /**
     * @var BaseService[]
     */
    private $innerServices;

    public function __construct()
    {
        parent::__construct();
        $this->innerServices = [];
        $this->addMappedService(new FhirObservationSocialHistoryService());
        $this->addMappedService(new FhirObservationVitalsService());
        $this->addMappedService(new FhirObservationLaboratoryService());
        $this->addMappedService(new FhirObservationObservationFormService());
        $this->addMappedService(new FhirObservationHistorySdohService());
        $this->addMappedService(new FhirObservationPatientService());
        $this->addMappedService(new FhirObservationEmployerService());
        $this->addMappedService(new FhirObservationAdvanceDirectiveService());
        $this->addMappedService(new FhirObservationTreatmentInterventionPreferenceService());
        $this->addMappedService(new FhirObservationCareExperiencePreferenceService());
    }

    /**
     * Returns an array mapping FHIR Resource search parameters to OpenEMR search parameters
     */
    protected function loadSearchParameters(): array
    {
        return  [
            'patient' => $this->getPatientContextSearchField(),
            'code' => new FhirSearchParameterDefinition('status', SearchFieldType::TOKEN, ['code']),
            'category' => new FhirSearchParameterDefinition('category', SearchFieldType::TOKEN, ['category']),
            'date' => new FhirSearchParameterDefinition('date', SearchFieldType::DATETIME, ['date']),
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, ['uuid']),
            '_lastUpdated' => $this->getLastModifiedSearchField()
        ];
    }

    public function getLastModifiedSearchField(): ?FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('_lastUpdated', SearchFieldType::DATETIME, ['date_modified']);
    }

    /**
     * Retrieves all of the fhir observation resources mapped to the underlying openemr data elements.
     */
    public function getAll($fhirSearchParameters, $puuidBind = null): ProcessingResult
    {
        $fhirSearchResult = new ProcessingResult();
        try {
            if (isset($fhirSearchParameters['_id'])) {
                $result = $this->populateSurrogateSearchFieldsForUUID($fhirSearchParameters['_id'], $fhirSearchParameters);
                if ($result instanceof ProcessingResult) { // failed to populate so return the results
                    return $result;
                }
            }

            if (isset($puuidBind)) {
                $field = $this->getPatientContextSearchField();
                $fhirSearchParameters[$field->getName()] = $puuidBind;
            }

            $servicesMap = [];
            $services = [];
            if (isset($fhirSearchParameters['category'])) {
                /**
                 * @var TokenSearchField
                 */
                $category = $fhirSearchParameters['category'];

                $catServices = $this->getServiceListForCategory(
                    new TokenSearchField('category', explode(",",$category))
                );
                foreach ($catServices as $service) {
                    $servicesMap[$service::class] = $service;
                }
                $services = $servicesMap;
            }
            $codeMap = [];
            if (isset($fhirSearchParameters['code'])) {
                // we narrow our services down by code
                $codeServices = $this->getServiceListForCode(
                    new TokenSearchField('code', $fhirSearchParameters['code']),
                );
                $codeMap = [];
                foreach ($codeServices as $service) {
                    $codeMap[$service::class] = $service;
                }
                if (isset($fhirSearchParameters['category'])) {
                    // we have both category and code so we need to intersect the two maps
                    $services = array_intersect_key($servicesMap, $codeMap);
                } else {
                    $services = $codeMap;
                }
            }
            if (empty($services)) {
                $services = $this->getMappedServices();
            }
            $fhirSearchResult = $this->searchServices($services, $fhirSearchParameters, $puuidBind);
        } catch (SearchFieldException $exception) {
            $systemLogger = ServiceContainer::getLogger();
            $systemLogger->error("FhirObservationService->getAll() exception thrown", ['message' => $exception->getMessage(),
                'field' => $exception->getField(), 'trace' => $exception->getTraceAsString()]);
            // put our exception information here
            $fhirSearchResult->setValidationMessages([$exception->getField() => $exception->getMessage()]);
        }
        return $fhirSearchResult;
    }

    /**
     * Take our uuid surrogate key and populate the underlying data elements and grabs the mapped key for it.
     * @param $fhirResourceId The uuid search field with the 1..* values to search on
     * @param $search Hashmap of search operators
     */
    private function populateSurrogateSearchFieldsForUUID($fhirResourceId, &$search): ?ProcessingResult
    {
        $processingResult = new ProcessingResult();

        // we first grab the uuid from our registry and find out if its a mapping observation resource
        // (such as vital signs)
        $registryRecord = UuidRegistry::getRegistryRecordForUuid($fhirResourceId);

        if (empty($registryRecord)) {
            // not found need to return 404 which is an empty response
            $this->getSystemLogger()->debug("FhirObservationService->populateSurrogateSearchFieldsForUUID() - uuid not found in registry", ['_id' => $fhirResourceId]);
            return $processingResult;
        }

        // if its not mapped we will leave the _id alone and let the subsequent sub service pull the right resource
        // TODO: @adunsulag we could optimize this to go directly to the service that has the uuid but for now we'll just let it go through the normal search process
        if ($registryRecord['mapped'] != '1') {
            return null;
        }

        // we are going to get our
        $mapping = UuidMapping::getMappingForUUID($fhirResourceId);

        if (empty($mapping)) {
            $this->getSystemLogger()->debug("FhirObservationService->populateSurrogateSearchFieldsForUUID() - uuid mapping not found in registry", ['_id' => $fhirResourceId]);
            $processingResult->setValidationMessages(['_id' => 'Resource not found for that id']);
            return $processingResult;
        }

        // grab our category
        if ($mapping['resource'] !== 'Observation') {
            // we have a problem here
            $processingResult->setValidationMessages(["_id" => "Resource not found for that id"]);
            $this->getSystemLogger()->error("Requested observation resource for uuid that exists for a different resource", ['_id' => $fhirResourceId, 'mappingResource' => $mapping['resource']]);
            return $processingResult;
        }

        // grab category and code
        $query_vars = [];
        parse_str((string) $mapping['resource_path'], $query_vars);
        if (empty($query_vars['category'])) {
            $processingResult->setValidationMessages(["_id" => "Resource not found for that id"]);
            $this->getSystemLogger()->error("Requested observation with no resource_path category to parse the mapping", ['uuid' => $fhirResourceId, 'resource_path' => $mapping['resource_path']]);
            return $processingResult;
        }
        // if the search params already have code or category we need to check and make sure they match
        $category = new TokenSearchField('category', explode(",", $query_vars['category']));
        if (isset($search['category'])) {
            $searchCategory = new TokenSearchField('category', explode(",", $search['category']));
            if (!$searchCategory->containsSearchToken($category)) {
                // category does not match so return an empty result set
                $this->getSystemLogger()->error("Search categories did not not match the mapping, this could be a scope restriction, or a bad query"
                    , ['uuid' => $fhirResourceId, 'mappingCategory' => $query_vars['category'], 'searchCategory' => $search['category']]);
                return $processingResult;
            }
        }
        $code = empty($search['code']) ? $query_vars['code'] : $search['code'] . "," . $query_vars['code'];
        $search['code'] = $code;
        $search['category'] = $query_vars['category'];

        // we only want a single search value for now... not supporting combined uuids
        $search['_id'] = UuidRegistry::uuidToString($mapping['target_uuid']);
        return null;
    }

    public function getProfileURIs(): array
    {
        $profileSets = [];
        foreach ($this->getMappedServices() as $service) {
            if ($service instanceof IResourceUSCIGProfileService) {
                $profileSets[] = $service->getProfileURIs();
            }
        }

        // TODO: @adunsulag As we implement more profiles and sub-resource mappings we'll push them down to the sub-services
        $latestVersions = [
            'us-core-care-experience-preference'
            ,'us-core-medicationdispense'
            ,'us-core-observation-clinical-result'
            ,'us-core-observation-occupation'
            ,'us-core-observation-pregnancyintent'
            ,'us-core-observation-pregnancystatus'
            ,'us-core-observation-sexual-orientation'
            ,'us-core-treatment-intervention-preference'
        ];
        $v8Versions = [
            'us-core-observation-adi-documentation'
        ];
        foreach ($latestVersions as $resource) {
            $profileSets[] = $this->getProfileForVersions('http://hl7.org/fhir/us/core/StructureDefinition/' . $resource, ['', '7.0.0', '8.0.0']);
        }
        foreach ($v8Versions as $resource) {
            $profileSets[] = $this->getProfileForVersions('http://hl7.org/fhir/us/core/StructureDefinition/' . $resource, ['8.0.0']);
        }

        $profiles = array_merge(...$profileSets);
        return $profiles;
    }

    /**
     * Why each mapped sub-service cannot accept a write.
     *
     * Observation is a dispatcher over stores with very different shapes. Several of them
     * are views over demographic and history columns rather than tables of observations, so
     * there is nothing to write back through. A client is told which one it hit instead of
     * having the resource accepted and dropped -- the failure mode the read path would
     * otherwise hide, since the next GET reconstructs the resource from the untouched
     * underlying field.
     *
     * A service absent from this map accepts writes.
     *
     * @var array<class-string, string>
     */
    private const WRITE_REJECTION_REASONS = [
        FhirObservationSocialHistoryService::class =>
            'social history observations are a view over the patient history record; write them through that record',
        FhirObservationHistorySdohService::class =>
            'SDOH observations are a view over the patient history record; write them through that record',
        FhirObservationPatientService::class =>
            'patient-derived observations are a view over patient demographics; write them through Patient',
        FhirObservationEmployerService::class =>
            'employer-derived observations are a view over employer demographics; write them through Patient',
        FhirObservationLaboratoryService::class =>
            'laboratory results are written through their procedure order; Observation write for laboratory is not implemented yet',
        FhirObservationObservationFormService::class =>
            'observation form results are written through their form; Observation write for this category is not implemented yet',
        FhirObservationAdvanceDirectiveService::class =>
            'advance directive observations are written through their document; Observation write is not implemented for them',
        FhirObservationQuestionnaireItemService::class =>
            'questionnaire answers are written through QuestionnaireResponse',
        FhirObservationCareExperiencePreferenceService::class =>
            'care experience preferences are not writable through Observation yet',
        FhirObservationTreatmentInterventionPreferenceService::class =>
            'treatment intervention preferences are not writable through Observation yet',
    ];

    /**
     * Inserts an Observation by routing it to the service that owns its code.
     */
    public function insert(FHIRDomainResource $fhirResource): ProcessingResult
    {
        $service = $this->getWriteServiceForResource($fhirResource);
        if ($service instanceof ProcessingResult) {
            return $service;
        }
        return $service->insert($fhirResource);
    }

    /**
     * Updates an Observation by routing it to the service that owns its code.
     *
     * The id is checked by the sub-service rather than here: which store an id belongs to
     * is a property of that store's mapping, and the sub-service is the only place that
     * knows it.
     *
     * @param mixed $fhirResourceId
     */
    public function update($fhirResourceId, FHIRDomainResource $fhirResource): ProcessingResult
    {
        if (!is_string($fhirResourceId)) {
            $result = new ProcessingResult();
            $result->setValidationMessages(['uuid' => 'Invalid Observation id']);
            return $result;
        }
        $service = $this->getWriteServiceForResource($fhirResource);
        if ($service instanceof ProcessingResult) {
            return $service;
        }
        return $service->update($fhirResourceId, $fhirResource);
    }

    /**
     * Picks the sub-service that should handle a write, or the rejection to return instead.
     *
     * @return FhirServiceBase|ProcessingResult
     */
    private function getWriteServiceForResource(FHIRDomainResource $fhirResource): FhirServiceBase|ProcessingResult
    {
        if (!($fhirResource instanceof FHIRObservation)) {
            throw new \InvalidArgumentException(
                'Expected FHIRObservation resource, got ' . $fhirResource::class
            );
        }

        $json = $fhirResource->jsonSerialize();
        $code = FhirPayloadReader::firstCodingCode($json['code'] ?? null);
        if ($code === '') {
            $result = new ProcessingResult();
            $result->setValidationMessages(['code' => 'Observation.code is required (FHIR R4 1..1)']);
            return $result;
        }

        // getServiceListForCode() is the same lookup the read path uses, so a code routes to
        // the same store whichever direction it is travelling in.
        $candidates = $this->getServiceListForCode(new TokenSearchField('code', [$code]));
        $matched = is_array($candidates) ? ($candidates[0] ?? null) : null;

        if (!$matched instanceof FhirServiceBase) {
            $result = new ProcessingResult();
            $result->setValidationMessages([
                'code' => 'Observation.code "' . $code . '" is not a code OpenEMR stores',
            ]);
            return $result;
        }

        $reason = self::WRITE_REJECTION_REASONS[$matched::class] ?? null;
        if ($reason !== null) {
            $result = new ProcessingResult();
            $result->setValidationMessages([
                'code' => 'Observation.code "' . $code . '" cannot be written: ' . $reason,
            ]);
            return $result;
        }

        return $matched;
    }
}
