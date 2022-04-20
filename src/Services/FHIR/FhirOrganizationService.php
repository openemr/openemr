<?php

namespace OpenEMR\Services\FHIR;

use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIROrganization;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPatient;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPerson;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPractitioner;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\Services\FHIR\Organization\FhirOrganizationFacilityService;
use OpenEMR\Services\FHIR\Organization\FhirOrganizationInsuranceService;
use OpenEMR\Services\FHIR\Organization\FhirOrganizationProcedureProviderService;
use OpenEMR\Services\FHIR\Traits\BulkExportSupportAllOperationsTrait;
use OpenEMR\Services\FHIR\Traits\FhirBulkExportDomainResourceTrait;
use OpenEMR\Services\FHIR\Traits\MappedServiceTrait;
use OpenEMR\Services\PatientService;
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
class FhirOrganizationService implements IResourceSearchableService, IResourceReadableService, IResourceUpdateableService, IResourceCreatableService, IFhirExportableResourceService, IResourceUSCIGProfileService
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
        $types = $fhirResource->getType() ?? [];
        foreach ($types as $type) {
            // in theory, $type should be a object, but for some reason can also come back as an array
            //  so will support both the array and the object
            if (is_array($type)) {
                foreach ($type['coding'] as $coding) {
                    if ($coding['code'] == self::ORGANIZATION_TYPE_INSURANCE) {
                        // insert into the insurance
                        return $this->insuranceService->insert($fhirResource);
                    }
                }
            } else {
                $codings = $type->getCoding() ?? [];
                foreach ($codings as $coding) {
                    if ($coding->getCode() == self::ORGANIZATION_TYPE_INSURANCE) {
                        // insert into the insurance
                        return $this->insuranceService->insert($fhirResource);
                    }
                }
            }
        }
        // if its not an insurance company we are going to insert as a facility
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
     * Returns the organization that we can find connected to the user.
     * @param FHIRReference $user Practitioner that we want to return the corresponding organization
     * @return FHIRReference|null
     */
    public function getOrganizationReferenceFromUserReference(FHIRReference $user)
    {
        $referenceUuid = UtilsService::getUuidFromReference($user);
        if ($user->getType() == (new FHIRPractitioner())->get_fhirElementName()) {
            return $this->facilityService->getOrganizationReferenceForUser($referenceUuid);
        } else {
            // TODO: if we need to support person, patient, or another mapping put that here
            return null;
        }
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

    /**
     * Returns the Canonical URIs for the FHIR resource for each of the US Core Implementation Guide Profiles that the
     * resource implements.  Most resources have only one profile, but several like DiagnosticReport and Observation
     * has multiple profiles that must be conformed to.
     * @see https://www.hl7.org/fhir/us/core/CapabilityStatement-us-core-server.html for the list of profiles
     * @return string[]
     */
    function getProfileURIs(): array
    {
        return [
            'http://hl7.org/fhir/us/core/StructureDefinition/us-core-organization'
        ];
    }
}
