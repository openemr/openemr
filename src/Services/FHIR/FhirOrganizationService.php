<?php

namespace OpenEMR\Services\FHIR;

use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIROrganization;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\Services\FHIR\Organization\FhirOrganizationFacilityService;
use OpenEMR\Services\FHIR\Organization\FhirOrganizationInsuranceService;
use OpenEMR\Services\FHIR\Organization\FhirOrganizationProcedureProviderService;
use OpenEMR\Services\FHIR\Traits\BulkExportSupportAllOperationsTrait;
use OpenEMR\Services\FHIR\Traits\FhirBulkExportDomainResourceTrait;
use OpenEMR\Services\FHIR\Traits\MappedServiceTrait;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\SearchFieldException;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Validators\ProcessingResult;

/**
 * FHIR Organization Service
 *
 * @coversDefaultClass OpenEMR\Services\FHIR\FhirOrganizationService
 * @package            OpenEMR
 * @link               http://www.open-emr.org
 * @author             Yash Bothra <yashrajbothra786@gmail.com>
 * @author             Stephen Nielson <stephen@nielson.org>
 * @copyright          Copyright (c) 2020 Yash Bothra <yashrajbothra786@gmail.com>
 * @copyright          Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license            https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class FhirOrganizationService implements IResourceSearchableService, IResourceReadableService, IResourceUpdateableService, IResourceCreatableService, IFhirExportableResourceService
{
    use BulkExportSupportAllOperationsTrait;
    use FhirBulkExportDomainResourceTrait;
    use MappedServiceTrait;

    const ORGANIZATION_TYPE_INSURANCE = "Ins";
    const ORGANIZATION_TYPE_PAYER = "Pay";
    const ORGANIZATION_TYPE_PROVIDER = "Prov";

    /**
     * @var FhirOrganizationFacilityService
     */
    private $facilityService;

    /**
     * @var FhirOrganizationInsuranceService
     */
    private $insuranceService;

    public function __construct()
    {
        $this->facilityService = new FhirOrganizationFacilityService();
        $this->insuranceService = new FhirOrganizationInsuranceService();

        $this->addMappedService($this->facilityService);
        $this->addMappedService($this->insuranceService);
        // TODO: ask @brady.miller if we need x12 clearinghouses here for our organization list... eventually yes, but for ONC?
        // TODO: @adunsulag look at adding Pharmacies on here as well... @see C_Pharmacy class
        $this->addMappedService(new FhirOrganizationProcedureProviderService());
    }

    /**
     * Returns an array mapping FHIR Organization Resource search parameters to OpenEMR Organization search parameters
     *
     * @return array The search parameters
     */
    public function getSearchParams()
    {
        return  [
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, [new ServiceField('uuid', ServiceField::TYPE_UUID)]),
            'email' => new FhirSearchParameterDefinition('email', SearchFieldType::TOKEN, ['email']),
            'phone' => new FhirSearchParameterDefinition('phone', SearchFieldType::TOKEN, ['phone']),
            'telecom' => new FhirSearchParameterDefinition('telecom', SearchFieldType::TOKEN, ['email', 'phone']),
            'address' => new FhirSearchParameterDefinition('address', SearchFieldType::STRING, ["street", "postal_code", "city", "state", "country_code","line1", "line2"]),
            'address-city' => new FhirSearchParameterDefinition('address-city', SearchFieldType::STRING, ['city']),
            'address-postalcode' => new FhirSearchParameterDefinition('address-postalcode', SearchFieldType::STRING, ['postal_code', "zip"]),
            'address-state' => new FhirSearchParameterDefinition('address-state', SearchFieldType::STRING, ['state']),
            'name' => new FhirSearchParameterDefinition('name', SearchFieldType::STRING, ['name'])
        ];
    }

    public function getOne($fhirResourceId, $puuidBind = null): ProcessingResult
    {
        return $this->getAll(['_id' => $fhirResourceId], $puuidBind);
    }

    public function getAll($fhirSearchParameters, $puuidBind = null): ProcessingResult
    {
        $fhirSearchResult = new ProcessingResult();
        try {
            $fhirSearchResult = $this->searchAllServicesWithSupportedFields($fhirSearchParameters, $puuidBind);
        } catch (SearchFieldException $exception) {
            (new SystemLogger())->error("FhirServiceBase->getAll() exception thrown", ['message' => $exception->getMessage(),
                'field' => $exception->getField()]);
            // put our exception information here
            $fhirSearchResult->setValidationMessages([$exception->getField() => $exception->getMessage()]);
        }
        return $fhirSearchResult;
    }

    public function insert($fhirResource): ProcessingResult
    {
        if (!($fhirResource instanceof FHIROrganization)) {
            throw new \BadMethodCallException("fhir resource must be of type " . FHIROrganization::class);
        }
        // we only allow facilities to be created... currently we have no way of creating a lab provider because we
        // can't differentiate on the type.
        $concepts = $fhirResource->getType();
        foreach ($concepts as $concept) {
            foreach ($concept->getCoding() as $coding) {
                if ($coding->getCode() == self::ORGANIZATION_TYPE_INSURANCE) {
                    // insert into the insurance
                    return $this->insuranceService->insert($fhirResource);
                }
            }
        }
        // if its not an insurance company we are going to bail out
        return $this->facilityService->insert($fhirResource);
    }

    public function update($fhirResourceId, $fhirResource): ProcessingResult
    {

        if (!($fhirResource instanceof FHIROrganization)) {
            throw new \BadMethodCallException("fhir resource must be of type " . FHIROrganization::class);
        }
        try {
            $service = $this->getMappedServiceForResourceUuid($fhirResourceId);
            if (!empty($service)) {
                return $service->update($fhirResourceId, $fhirResource);
            }
        } catch (SearchFieldException $exception) {
            (new SystemLogger())->error($exception->getMessage(), ['fhirResourceId' => $fhirResourceId, 'trace' => $exception->getTraceAsString()]);
        }
        $processingResult = new ProcessingResult();
        $processingResult->setValidationMessages(['_id' => 'Invalid fhir resource id']);
        return $processingResult;
    }

    public function getPrimaryBusinessEntityReference()
    {
        $ref = $this->facilityService->getPrimaryBusinessEntityReference();
        return $ref;
    }

    /**
     * Given the uuid of a user assigned to an organization, return a FHIR Reference to the organization record.
     * @param $userUuid The unique user id of the user we are retrieving the reference for.
     * @return FHIRReference|null The reference to the organization the user belongs to
     */
    public function getOrganizationReferenceForUser($userUuid)
    {
        return $this->facilityService->getOrganizationReferenceForUser($userUuid);
    }
}
