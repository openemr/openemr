<?php

namespace OpenEMR\Services\FHIR;

use OpenEMR\Billing\BillingProcessor\LoggerInterface;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Uuid\UuidMapping;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\BaseService;
use OpenEMR\Services\FHIR\Observation\FhirObservationAdvanceDirectiveService;
use OpenEMR\Services\FHIR\Observation\FhirObservationCareExperiencePreferenceService;
use OpenEMR\Services\FHIR\Observation\FhirObservationEmployerService;
use OpenEMR\Services\FHIR\Observation\FhirObservationHistorySdohService;
use OpenEMR\Services\FHIR\Observation\FhirObservationLaboratoryService;
use OpenEMR\Services\FHIR\Observation\FhirObservationObservationFormService;
use OpenEMR\Services\FHIR\Observation\FhirObservationPatientService;
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
 * @link               http://www.open-emr.org
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
     * @param $fhirSearchParameters The FHIR resource search parameters
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @return processing result
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
                    new TokenSearchField('category', $category)
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
            $systemLogger = new SystemLogger();
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
}
