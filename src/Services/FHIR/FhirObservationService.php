<?php

namespace OpenEMR\Services\FHIR;

use OpenEMR\Billing\BillingProcessor\LoggerInterface;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Uuid\UuidMapping;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\BaseService;
use OpenEMR\Services\FHIR\Observation\FhirObservationLaboratoryService;
use OpenEMR\Services\FHIR\Observation\FhirObservationSocialHistoryService;
use OpenEMR\Services\FHIR\Observation\FhirObservationVitalsService;
use OpenEMR\Services\FHIR\Traits\BulkExportSupportAllOperationsTrait;
use OpenEMR\Services\FHIR\Traits\FhirBulkExportDomainResourceTrait;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\FHIR\Traits\MappedServiceCodeTrait;
use OpenEMR\Services\FHIR\Traits\PatientSearchTrait;
use OpenEMR\Services\ObservationLabService;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\SearchFieldException;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Validators\ProcessingResult;

/**
 * FHIR Observation Service
 *
 * @coversDefaultClass OpenEMR\Services\FHIR\FhirObservationService
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

    /**
     * @var ObservationLabService
     */
    private $observationService;

    /**
     * @var BaseService[]
     */
    private $innerServices;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct()
    {
        parent::__construct();
        $this->innerServices = [];
        $this->addMappedService(new FhirObservationSocialHistoryService());
        $this->addMappedService(new FhirObservationVitalsService());
        $this->addMappedService(new FhirObservationLaboratoryService());
        $this->logger = new SystemLogger();
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
        ];
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

            if (isset($fhirSearchParameters['category'])) {
                /**
                 * @var TokenSearchField
                 */
                $category = $fhirSearchParameters['category'];

                $service = $this->getServiceForCategory(
                    new TokenSearchField('category', $fhirSearchParameters['category']),
                    'vital-signs'
                );
                $fhirSearchResult = $service->getAll($fhirSearchParameters, $puuidBind);
            } else if (isset($fhirSearchParameters['code'])) {
                $service = $this->getServiceForCode(
                    new TokenSearchField('code', $fhirSearchParameters['code']),
                    FhirObservationVitalsService::VITALS_PANEL_LOINC_CODE
                );
                // if we have a service let's search on that
                if (isset($service)) {
                    $fhirSearchResult = $service->getAll($fhirSearchParameters, $puuidBind);
                } else {
                    $fhirSearchResult = $this->searchAllServices($fhirSearchParameters, $puuidBind);
                }
            } else {
                $fhirSearchResult = $this->searchAllServices($fhirSearchParameters, $puuidBind);
            }
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
    private function populateSurrogateSearchFieldsForUUID($fhirResourceId, &$search)
    {
        $processingResult = new ProcessingResult();

        // we first grab the uuid from our registry and find out if its a mapping observation resource
        // (such as vital signs)
        $registryRecord = UuidRegistry::getRegistryRecordForUuid($fhirResourceId);

        if (empty($registryRecord)) {
            $processingResult->setValidationMessages(['_id' => 'Resource not found for that id']);
            return $processingResult;
        }

        // if its not mapped we will leave the _id alone and let the subsequent sub service pull the right resource
        if ($registryRecord['mapped'] != '1') {
            return;
        }

        // we are going to get our
        $mapping = UuidMapping::getMappingForUUID($fhirResourceId);

        if (empty($mapping)) {
            $processingResult->setValidationMessages(['_id' => 'Resource not found for that id']);
            return $processingResult;
        }

        // grab our category
        if ($mapping['resource'] !== 'Observation') {
            // we have a problem here
            $processingResult->setValidationMessages(["_id" => "Resource not found for that id"]);
            $this->logger->error("Requested observation resource for uuid that exists for a different resource", ['_id' => $fhirResourceId, 'mappingResource' => $mapping['resource']]);
            return $processingResult;
        }

        // grab category and code
        $query_vars = [];
        parse_str($mapping['resource_path'], $query_vars);
        if (empty($query_vars['category'])) {
            $processingResult->setValidationMessages(["_id" => "Resource not found for that id"]);
            $this->logger->error("Requested observation with no resource_path category to parse the mapping", ['uuid' => $fhirResourceId, 'resource_path' => $mapping['resource_path']]);
            return $processingResult;
        }

        $code = empty($search['code']) ? $query_vars['code'] : $search['code'] . "," . $query_vars['code'];
        $search['code'] = $code;
        $search['category'] = $query_vars['category'];

        // we only want a single search value for now... not supporting combined uuids
        $search['_id'] = UuidRegistry::uuidToString($mapping['target_uuid']);
    }

    public function getProfileURIs(): array
    {
        return [
            'http://hl7.org/fhir/R4/observation-vitalsigns'
            ,'http://hl7.org/fhir/us/core/StructureDefinition/us-core-observation-lab'
            ,'http://hl7.org/fhir/us/core/StructureDefinition/pediatric-bmi-for-age'
            ,'http://hl7.org/fhir/us/core/StructureDefinition/pediatric-weight-for-height'
            ,'http://hl7.org/fhir/us/core/StructureDefinition/us-core-pulse-oximetry'
            ,'http://hl7.org/fhir/us/core/StructureDefinition/us-core-smokingstatus'
            ,'http://hl7.org/fhir/StructureDefinition/bp'
            ,'http://hl7.org/fhir/StructureDefinition/bodyheight'
            ,'http://hl7.org/fhir/StructureDefinition/bodyweight'
            ,'http://hl7.org/fhir/StructureDefinition/heartrate'
            ,'http://hl7.org/fhir/StructureDefinition/resprate'
            ,'http://hl7.org/fhir/StructureDefinition/bodytemp'
            ,'http://hl7.org/fhir/us/core/StructureDefinition/head-occipital-frontal-circumference-percentile'
        ];
    }
}
