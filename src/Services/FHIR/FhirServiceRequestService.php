<?php

/**
 * FhirServiceRequestService.php: the ServiceRequest resource. A search reaches, by category,
 * the services that hold each kind of request, as the mapped services (FhirConditionService,
 * FhirObservationService) do: procedure orders (FhirServiceRequestProcedureOrderService) and
 * referrals from the Transactions screen's referral form (FhirServiceRequestReferralService).
 *
 * @package   openemr
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2025-2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR;

use OpenEMR\BC\ServiceContainer;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;
use OpenEMR\Services\FHIR\ServiceRequest\FhirServiceRequestProcedureOrderService;
use OpenEMR\Services\FHIR\ServiceRequest\FhirServiceRequestReferralService;
use OpenEMR\Services\FHIR\Traits\BulkExportSupportAllOperationsTrait;
use OpenEMR\Services\FHIR\Traits\FhirBulkExportDomainResourceTrait;
use OpenEMR\Services\FHIR\Traits\PatientSearchTrait;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\SearchFieldException;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Services\Search\TokenSearchValue;
use OpenEMR\Validators\ProcessingResult;

class FhirServiceRequestService extends FhirServiceBase implements
    IResourceUSCIGProfileService,
    IFhirExportableResourceService,
    IPatientCompartmentResourceService
{
    use PatientSearchTrait;
    use BulkExportSupportAllOperationsTrait;
    use FhirBulkExportDomainResourceTrait;

    private ?FhirServiceRequestProcedureOrderService $orderService = null;

    /**
     * @var list<FhirServiceBase>
     */
    private array $mappedServices = [];

    /**
     * Procedure orders are the only ServiceRequests written through the API, so create
     * and update go to them, as before referrals were added.
     */
    private function getOrderService(): FhirServiceRequestProcedureOrderService
    {
        return $this->orderService ??= new FhirServiceRequestProcedureOrderService();
    }

    /**
     * The services a search can reach, built on first use so constructing this service reads nothing.
     *
     * @return list<FhirServiceBase>
     */
    public function getMappedServices(): array
    {
        if ($this->mappedServices === []) {
            $this->mappedServices = [$this->getOrderService(), new FhirServiceRequestReferralService()];
        }
        return $this->mappedServices;
    }

    /**
     * @param list<FhirServiceBase> $services
     */
    public function setMappedServices(array $services): void
    {
        $this->mappedServices = $services;
    }

    protected function loadSearchParameters(): array
    {
        return [
            'patient' => $this->getPatientContextSearchField(),
            'category' => new FhirSearchParameterDefinition('category', SearchFieldType::TOKEN, ['category']),
            'code' => new FhirSearchParameterDefinition('code', SearchFieldType::TOKEN, ['code']),
            'authored' => new FhirSearchParameterDefinition('authored', SearchFieldType::DATETIME, ['authored']),
            'status' => new FhirSearchParameterDefinition('status', SearchFieldType::TOKEN, ['status']),
            'intent' => new FhirSearchParameterDefinition('intent', SearchFieldType::TOKEN, ['intent']),
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, [new ServiceField('uuid', ServiceField::TYPE_UUID)]),
            '_lastUpdated' => $this->getLastModifiedSearchField(),
        ];
    }

    public function getLastModifiedSearchField(): FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('_lastUpdated', SearchFieldType::DATETIME, ['last_modified']);
    }

    /**
     * A category search reaches the services that support one of its categories; any other
     * search, or a category none of them supports, reaches them all, and each answers only
     * for its own records. A read by id is a search by _id, so it reaches both.
     *
     * @param mixed $fhirSearchParameters
     * @param mixed $puuidBind
     */
    public function getAll($fhirSearchParameters, $puuidBind = null): ProcessingResult
    {
        $searchParameters = is_array($fhirSearchParameters) ? $fhirSearchParameters : [];
        $bind = is_string($puuidBind) ? $puuidBind : null;
        $fhirSearchResult = new ProcessingResult();
        try {
            if ($bind !== null) {
                $searchParameters[$this->getPatientContextSearchField()->getName()] = $bind;
            }

            $services = [];
            if (isset($searchParameters['category'])) {
                $category = TokenSearchField::fromEscapedFHIRString('category', $searchParameters['category']);
                $services = $this->servicesForCategory($category);
            }
            if ($services === []) {
                $services = $this->getMappedServices();
            }
            $fhirSearchResult = $this->searchServices($services, $searchParameters, $bind);
        } catch (SearchFieldException $exception) {
            ($this->logger ?? ServiceContainer::getLogger())->error("FhirServiceRequestService->getAll() exception thrown", [
                'exception' => $exception,
                'field' => $exception->getField(),
            ]);
            $fhirSearchResult->setValidationMessages([$exception->getField() => $exception->getMessage()]);
        }
        return $fhirSearchResult;
    }

    /**
     * The services that support any of the searched categories, as the mapped services'
     * getServiceListForCategory() works it out.
     *
     * @return list<FhirServiceBase>
     */
    private function servicesForCategory(TokenSearchField $category): array
    {
        $services = [];
        foreach ($this->getMappedServices() as $service) {
            if (!method_exists($service, 'supportsCategory')) {
                continue;
            }
            foreach ($category->getValues() as $value) {
                if ($value instanceof TokenSearchValue && $service->supportsCategory((string) $value->getCode()) === true) {
                    $services[] = $service;
                    break;
                }
            }
        }
        return $services;
    }

    /**
     * Each service's results in turn; on the first error, the errors and no data, as the mapped
     * services' searchServices() does.
     *
     * @param list<FhirServiceBase> $services
     * @param array<mixed> $searchParameters
     */
    private function searchServices(array $services, array $searchParameters, ?string $bind): ProcessingResult
    {
        $processingResult = new ProcessingResult();
        foreach ($services as $service) {
            $processingResult->addProcessingResult($service->getAll($searchParameters, $bind));
            if ($processingResult->hasErrors()) {
                $processingResult->clearData();
                return $processingResult;
            }
        }
        return $processingResult;
    }

    /**
     * getAll() searches the mapped services, so nothing searches this service directly.
     *
     * @param mixed $openEMRSearchParameters
     */
    protected function searchForOpenEMRRecords($openEMRSearchParameters): ProcessingResult
    {
        return new ProcessingResult();
    }

    /**
     * @param mixed $dataRecord
     * @param mixed $encode
     */
    public function parseOpenEMRRecord($dataRecord = [], $encode = false)
    {
        return $this->getOrderService()->parseOpenEMRRecord(is_array($dataRecord) ? $dataRecord : [], $encode === true);
    }

    /**
     * @return array<string, mixed>
     */
    public function parseFhirResource(FHIRDomainResource $fhirResource): array
    {
        return $this->getOrderService()->parseFhirResource($fhirResource);
    }

    public function insert(FHIRDomainResource $fhirResource): ProcessingResult
    {
        return $this->getOrderService()->insert($fhirResource);
    }

    /**
     * @param mixed $fhirResourceId
     */
    public function update($fhirResourceId, FHIRDomainResource $fhirResource): ProcessingResult
    {
        if (!is_string($fhirResourceId)) {
            $processingResult = new ProcessingResult();
            $processingResult->setValidationMessages(['_id' => 'The resource id must be a string']);
            return $processingResult;
        }
        return $this->getOrderService()->update($fhirResourceId, $fhirResource);
    }

    /**
     * insert() and update() go to the procedure-order service, which writes the record.
     *
     * @param mixed $openEmrRecord
     */
    protected function insertOpenEMRRecord($openEmrRecord): ProcessingResult
    {
        throw new \LogicException('ServiceRequests are written through FhirServiceRequestProcedureOrderService');
    }

    /**
     * insert() and update() go to the procedure-order service, which writes the record.
     *
     * @param mixed $fhirResourceId
     * @param mixed $updatedOpenEMRRecord
     */
    protected function updateOpenEMRRecord($fhirResourceId, $updatedOpenEMRRecord): ProcessingResult
    {
        throw new \LogicException('ServiceRequests are written through FhirServiceRequestProcedureOrderService');
    }

    /**
     * @param mixed $dataRecord
     * @param mixed $encode
     */
    public function createProvenanceResource($dataRecord, $encode = false)
    {
        return $this->getOrderService()->createProvenanceResource($dataRecord, $encode === true);
    }

    public function getProfileURIs(): array
    {
        return $this->getOrderService()->getProfileURIs();
    }
}
