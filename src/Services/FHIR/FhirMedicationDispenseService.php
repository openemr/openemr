<?php

/**
 * FHIR MedicationDispense Service
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2025 OpenEMR <info@open-emr.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR;

use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Uuid\UuidMapping;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\BaseService;
use OpenEMR\Services\FHIR\MedicationDispense\FhirMedicationDispenseLocalDispensaryService;
use OpenEMR\Services\FHIR\Traits\BulkExportSupportAllOperationsTrait;
use OpenEMR\Services\FHIR\Traits\FhirBulkExportDomainResourceTrait;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\FHIR\Traits\MappedServiceCodeTrait;
use OpenEMR\Services\FHIR\Traits\PatientSearchTrait;
use OpenEMR\Services\FHIR\Traits\VersionedProfileTrait;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\SearchFieldException;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Validators\ProcessingResult;

/**
 * FHIR MedicationDispense Service
 *
 * Coordinates between different dispense sources (local dispensary, immunizations)
 * to provide unified FHIR MedicationDispense resources.
 */
class FhirMedicationDispenseService extends FhirServiceBase implements
    IResourceSearchableService,
    IResourceUSCIGProfileService,
    IPatientCompartmentResourceService,
    IFhirExportableResourceService
{
    use FhirServiceBaseEmptyTrait;
    use MappedServiceCodeTrait;
    use PatientSearchTrait;
    use BulkExportSupportAllOperationsTrait;
    use FhirBulkExportDomainResourceTrait;
    use VersionedProfileTrait;

    /**
     * @var BaseService[]
     */
    private array $innerServices;

    const USCGI_PROFILE_URI = "http://hl7.org/fhir/us/core/StructureDefinition/us-core-medicationdispense";

    public function __construct()
    {
        parent::__construct();
        $this->innerServices = [];
        $this->addMappedService(new FhirMedicationDispenseLocalDispensaryService());
        // Note: FhirMedicationDispenseImmunizationService will be added later
    }

    /**
     * Returns an array mapping FHIR Resource search parameters to OpenEMR search parameters
     */
    protected function loadSearchParameters(): array
    {
        return [
            'patient' => $this->getPatientContextSearchField(),
            'status' => new FhirSearchParameterDefinition('status', SearchFieldType::TOKEN, ['status']),
            'type' => new FhirSearchParameterDefinition('type', SearchFieldType::TOKEN, ['type']),
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, ['uuid']),
            '_lastUpdated' => $this->getLastModifiedSearchField()
        ];
    }

    public function getLastModifiedSearchField(): ?FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('_lastUpdated', SearchFieldType::DATETIME, ['date_modified']);
    }

    /**
     * Retrieves all of the fhir medication dispense resources mapped to the underlying openemr data elements.
     * @param $fhirSearchParameters The FHIR resource search parameters
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @return ProcessingResult
     */
    public function getAll($fhirSearchParameters, $puuidBind = null): ProcessingResult
    {
        $fhirSearchResult = new ProcessingResult();
        try {
            if (isset($puuidBind)) {
                $field = $this->getPatientContextSearchField();
                $fhirSearchParameters[$field->getName()] = $puuidBind;
            }

            $servicesMap = [];
            $services = [];
            if (isset($fhirSearchParameters['type'])) {
                /**
                 * @var TokenSearchField
                 */
                $category = $fhirSearchParameters['type'];

                $catServices = $this->getServiceListForCategory(
                    new TokenSearchField('type', $category)
                );
                foreach ($catServices as $service) {
                    $servicesMap[$service::class] = $service;
                }
                $services = $servicesMap;
            }
            if (empty($services)) {
                $services = $this->getMappedServices();
            }
            $fhirSearchResult = $this->searchServices($services, $fhirSearchParameters, $puuidBind);
        } catch (SearchFieldException $exception) {
            $systemLogger = new SystemLogger();
            $systemLogger->error("FhirMedicationDispenseService->getAll() exception thrown", [
                'message' => $exception->getMessage(),
                'field' => $exception->getField(),
                'trace' => $exception->getTraceAsString()
            ]);
            // put our exception information here
            $fhirSearchResult->setValidationMessages([$exception->getField() => $exception->getMessage()]);
        }
        return $fhirSearchResult;
    }

    public function getSupportedVersions(): array
    {
        return self::PROFILE_VERSIONS_V2;
    }

    public function getProfileURIs(): array
    {
        $profileSets = [];
        foreach ($this->getMappedServices() as $service) {
            if ($service instanceof IResourceUSCIGProfileService) {
                $profileSets[] = $service->getProfileURIs();
            }
        }

        // Add the main US Core MedicationDispense profile
        $profileSets[] = $this->getProfileForVersions(
            self::USCGI_PROFILE_URI,
            ['', '7.0.0', '8.0.0']
        );

        $profiles = array_merge(...$profileSets);
        return $profiles;
    }

    /**
     * Performs a FHIR MedicationDispense Resource lookup by FHIR Resource ID
     * @param $fhirResourceId The OpenEMR uuid or mapped uuid that is to be looked up
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @return ProcessingResult
     */
    public function getOne($fhirResourceId, $puuidBind = null): ProcessingResult
    {
        $fhirSearchParameters = ['_id' => $fhirResourceId];
        $result = $this->getAll($fhirSearchParameters, $puuidBind);
        return $result;
    }
}
