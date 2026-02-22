<?php

/**
 * FhirLocationService
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786@gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR;

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\FHIR\Export\ExportCannotEncodeException;
use OpenEMR\FHIR\Export\ExportException;
use OpenEMR\FHIR\Export\ExportJob;
use OpenEMR\FHIR\Export\ExportStreamWriter;
use OpenEMR\FHIR\Export\ExportWillShutdownException;
use OpenEMR\Common\Enum\PlaceOfServiceEnum;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRLocation;
use OpenEMR\FHIR\R4\FHIRElement\FHIRContactPoint;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;
use OpenEMR\Services\FHIR\Traits\BulkExportSupportAllOperationsTrait;
use OpenEMR\Services\FHIR\Traits\FhirBulkExportDomainResourceTrait;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\FHIR\Traits\VersionedProfileTrait;
use OpenEMR\Services\LocationService;
use OpenEMR\Services\Search\CompositeSearchField;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Services\Search\TokenSearchValue;
use OpenEMR\Validators\ProcessingResult;

class FhirLocationService extends FhirServiceBase implements IFhirExportableResourceService, IResourceUSCIGProfileService
{
    use FhirServiceBaseEmptyTrait;
    use BulkExportSupportAllOperationsTrait;
    use FhirBulkExportDomainResourceTrait;
    use VersionedProfileTrait;

    const USCGI_PROFILE_URI = 'http://hl7.org/fhir/us/core/StructureDefinition/us-core-location';

    /**
     * @var LocationService
     */
    private LocationService $locationService;

    /**
     * @var FhirOrganizationService
     */
    private FhirOrganizationService $fhirOrganizationService;
    /**
     * The patient uuid bound in the current request
     * @var string
     */
    private $patientUuid;

    private FhirOrganizationService $organizationService;

    public function __construct()
    {
        parent::__construct();
        $this->locationService = new LocationService();
    }

    public function getLocationService(): LocationService
    {
        return $this->locationService;
    }

    public function setLocationService(LocationService $locationService): void
    {
        $this->locationService = $locationService;
    }

    public function getOrganizationService(): FhirOrganizationService {
        if (!isset($this->fhirOrganizationService)) {
            $this->fhirOrganizationService = new FhirOrganizationService();
        }
        return $this->fhirOrganizationService;
    }

    /**
     * @param FhirOrganizationService $organizationService
     */
    public function setOrganizationService(FhirOrganizationService $organizationService): void
    {
        $this->organizationService = $organizationService;
    }

    /**
     * Returns an array mapping FHIR Location Resource search parameters to OpenEMR Location search parameters
     * @return array The search parameters
     */
    protected function loadSearchParameters()
    {
        return  [
            '_id' => new FhirSearchParameterDefinition('uuid', SearchFieldType::TOKEN, [new ServiceField('uuid', ServiceField::TYPE_UUID)]),
            'address' => new FhirSearchParameterDefinition(
                'address',
                SearchFieldType::STRING,
                ['street', 'city', 'postal_code', 'state']
            ),
            'name' => new FhirSearchParameterDefinition(
                'name',
                SearchFieldType::STRING,
                ['name']
            ),
            'address-city' => new FhirSearchParameterDefinition(
                'address-city',
                SearchFieldType::STRING,
                ['city']
            ),
            'address-state' => new FhirSearchParameterDefinition(
                'address-state',
                SearchFieldType::STRING,
                ['state']
            ),
            'address-postalcode' => new FhirSearchParameterDefinition(
                'address-postalcode',
                SearchFieldType::STRING,
                ['postal_code']
            ),
            '_lastUpdated' => $this->getLastModifiedSearchField()
        ];
    }

    public function getLastModifiedSearchField(): ?FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('_lastUpdated', SearchFieldType::DATETIME, ['last_updated']);
    }

    /**
     * Parses an OpenEMR location record, returning the equivalent FHIR Location Resource
     *
     * @param array $dataRecord The source OpenEMR data record
     * @param boolean $encode Indicates if the returned resource is encoded into a string. Defaults to false.
     * @return FHIRLocation
     */
    public function parseOpenEMRRecord($dataRecord = [], $encode = false)
    {
        $locationResource = new FHIRLocation();

        $meta = new FHIRMeta();
        $meta->setVersionId('1');
        if (!empty($dataRecord['last_updated'])) {
            $meta->setLastUpdated(UtilsService::getLocalDateAsUTC($dataRecord['last_updated']));
        } else {
            $meta->setLastUpdated(UtilsService::getDateFormattedAsUTC());
        }
        $locationResource->setMeta($meta);

        $id = new FHIRId();
        $id->setValue($dataRecord['uuid']);
        $locationResource->setId($id);

        $this->populateIdentifier($locationResource, $dataRecord);
        $this->populateServiceRoleType($locationResource, $dataRecord);

        $locationResource->setStatus("active");

        if (!empty($dataRecord['name'])) {
            $name = $dataRecord['name'];
            if ($dataRecord['type'] != 'facility') {
                $name = xlt($name);
            }
            $locationResource->setName($name);
        } else {
            $locationResource->setName(UtilsService::createDataMissingExtension());
        }

        // TODO: @brady.miller is this the right security ACL for a facilities organization?
        if ($this->shouldIncludeContactInformationForLocationType($dataRecord['type'], $dataRecord['uuid'])) {
            // TODO: @adunsulag when we handle the contact,contact_address,and address tables we can grab those fields
            // instead of overriding the type for the fhir.
            $dataRecord['type'] = 'physical';
            $locationResource->setAddress(UtilsService::createAddressFromRecord($dataRecord));

            $contactPoints = ['phone', 'fax', 'email'];
            foreach ($contactPoints as $point) {
                if (!empty($dataRecord[$point])) {
                    $contactPoint = new FHIRContactPoint();
                    $contactPoint->setSystem($point);
                    $contactPoint->setValue($dataRecord[$point]);
                    $locationResource->addTelecom($contactPoint);
                }
            }
            if (!empty($dataRecord['website'])) {
                $url = new FHIRContactPoint();
                $url->setSystem('url');
                $url->setValue($dataRecord['website']);
                $locationResource->addTelecom($url);
            }
        }

        // we set the managing organization to the primary business entity of the system
        $businessEntity = $this->getOrganizationService()->getPrimaryBusinessEntityReference();
        if (!empty($businessEntity)) {
            $locationResource->setManagingOrganization($businessEntity);
        }



        if ($encode) {
            return json_encode($locationResource);
        } else {
            return $locationResource;
        }
    }

    public function getOne($fhirResourceId, $puuidBind = null): ProcessingResult
    {
        try {
            $this->patientUuid = $puuidBind;
            return parent::getOne($fhirResourceId, $puuidBind);
        } finally {
            $this->patientUuid = null;
        }
    }

    public function getAll($fhirSearchParameters, $puuidBind = null): ProcessingResult
    {
        try {
            $this->patientUuid = $puuidBind;
            return parent::getAll($fhirSearchParameters, $puuidBind);
        } finally {
            $this->patientUuid = null;
        }
    }

    /**
     * Grabs all the objects in my service that match the criteria specified in the ExportJob.  If a
     * $lastResourceIdExported is provided, The service executes the same data collection query it used previously and
     * startes processing at the resource that is immediately after (ordered by date) the resource that matches the id of
     * $lastResourceIdExported.  This allows processing of the service to be resumed or paused.
     * @param ExportStreamWriter $writer Object that writes out to a stream any object that extend the FhirResource object
     * @param ExportJob $job The export job we are processing the request for.  Holds all of the context information needed for the export service.
     * @return void
     * @throws ExportWillShutdownException  Thrown if the export is about to be shutdown and all processing must be halted.
     * @throws ExportException  If there is an error in processing the export
     * @throws ExportCannotEncodeException Thrown if the resource cannot be properly converted into the right format (ie JSON).
     */
    public function export(ExportStreamWriter $writer, ExportJob $job, $lastResourceIdExported = null): void
    {
        $type = $job->getExportType();

        $searchParams = [];
        if ($type == ExportJob::EXPORT_OPERATION_GROUP) {
            $patientUuids = $job->getPatientUuidsToExport();
            if (empty($patientUuids)) {
                // TODO: @adunsulag do we want to handle this higher up the chain instead of creating a bunch of
                // empty files with no data?
                return; // nothing to export here as we have no patients
            }
            (new SystemLogger())->debug(
                "FhirLocationService->export() filtering by patient uuids",
                ['export-type' => 'group', 'patients' => $patientUuids, 'resource-class' => static::class]
            );
            $searchField = $this->getPatientContextSearchFieldForPatientUuids($patientUuids);
            $searchParams[$searchField->getName()] = $searchField;
        }
        $searchField = $this->getLastModifiedSearchField();
        if ($searchField !== null) {
            $searchParams[$searchField->getName()] = $job->getResourceIncludeSearchParamValue();
        }

        $processingResult = $this->getAll($searchParams);
        $records = $processingResult->getData();
        foreach ($records as $record) {
            if (!($record instanceof FHIRDomainResource)) {
                throw new ExportException(self::class . " returned records that are not a valid fhir resource type for this class", 0, $lastResourceIdExported);
            }
            $writer->append($record);
            $lastResourceIdExported = $record->getId();
        }
    }

    protected function createSearchParameterForField($fhirSearchField, $searchValue): ISearchField
    {
        // because of our unique handling of patient bound locations we need to handle this one specially
        if ($fhirSearchField === 'patient-facility-type' && $searchValue instanceof ISearchField) {
            // we handle this one specially as it is a composite field that needs to be built based on context
            return $searchValue;
        } else {
            return parent::createSearchParameterForField($fhirSearchField, $searchValue);
        }
    }

    /**
     * Searches for OpenEMR records using OpenEMR search parameters
     *
     * @param array<string, ISearchField> $openEMRSearchParameters OpenEMR search fields
     * @return ProcessingResult
     */
    protected function searchForOpenEMRRecords($openEMRSearchParameters): ProcessingResult
    {
        // even though it's not a patient compartment issue we still don't want certain location data such as clinician home addresses
        // being returned... or other patient locations...  Weird that it's not in the patient compartment
        if (!empty($this->patientUuid)) {
            $uuidSearch = $openEMRSearchParameters['uuid'] ?? null;
            if ($uuidSearch !== null) {
                unset($openEMRSearchParameters['uuid']); // remove it so we can handle it properly
            }
            $openEMRSearchParameters['patient-facility-type'] = $this->getPatientContextSearchFieldForPatientUuids(
                [$this->patientUuid],
                $uuidSearch
            );
        }
        return $this->locationService->getAll($openEMRSearchParameters);
    }

    /**
     * @param string[] $patientUuids
     * @return ISearchField
     */
    private function getPatientContextSearchFieldForPatientUuids(array $patientUuids, ?ISearchField $uuidSearch = null): ISearchField
    {
        // if there is no uuid search field this becomes
        //      (table_uuid = ? and type = 'patient') OR (type = 'facility')
        // if there is an uuid search field this becomes:
        //      (table_uuid = ? and type = 'patient' and uuid = ?) OR (type = 'facility' AND uuid = ?)

        $patientType = new CompositeSearchField('patient-type', [], true);
        // patient id is the target_uuid, the uuid column is the mapped 'Location' resource in FHIR
        $patientType->addChild(new TokenSearchField('table_uuid', $patientUuids, true));
        $patientType->addChild(new TokenSearchField('type', [new TokenSearchValue(LocationService::TYPE_PATIENT)]));

        $facilityType = new CompositeSearchField('facility-type', [], true);
        $facilityType->addChild(new TokenSearchField('type', [new TokenSearchValue(LocationService::TYPE_FACILITY)]));

        if ($uuidSearch !== null) {
            // id must match the patient type as well
            $patientType->addChild($uuidSearch);

            // or id must match the facility location
            $facilityType->addChild($uuidSearch);
        }

        // if we are patient bound we want to make sure we grab only patient locations or facility locations
        $patientFacilityType = new CompositeSearchField('patient-facility-type', [], false);
        $patientFacilityType->addChild($facilityType);
        $patientFacilityType->addChild($patientType);
        return $patientFacilityType;
    }

    private function hasAccessToUserLocationData()
    {
        return AclMain::aclCheckCore('admin', 'users', $this->getSession()->get("authUser")) !== false;
    }

    private function shouldIncludeContactInformationForLocationType($type, $recordUuid)
    {
        $isPatientBoundUuid = !empty($this->patientUuid) && $this->patientUuid == $recordUuid;
        // if its not a patient requesting their own record location information we need to check permissions on this.
        if ($type == 'patient' && !$isPatientBoundUuid) {
            // only those with access to a patient's demographic information can get their data
            return AclMain::aclCheckCore("patients", "demo",$this->getSession()->get("authUser")) !== false;
        } else if ($type == 'user') {
            // only those with access to the user information can get address information about a user.
            return $this->hasAccessToUserLocationData();
        } else {
            // facilities we just let all contact information be displayed for the location.
            return true;
        }
    }

    /**
     * Returns the Canonical URIs for the FHIR resource for each of the US Core Implementation Guide Profiles that the
     * resource implements.  Most resources have only one profile, but several like DiagnosticReport and Observation
     * has multiple profiles that must be conformed to.
     * @see https://www.hl7.org/fhir/us/core/CapabilityStatement-us-core-server.html for the list of profiles
     * @return string[]
     */
    public function getProfileURIs(): array
    {
        return $this->getProfileForVersions(self::USCGI_PROFILE_URI, $this->getSupportedVersions());
    }

    protected function populateIdentifier(FHIRLocation $locationResource, array $dataRecord)
    {
        $system = $this->getSystemForIdentifier($dataRecord['identifier_type'] ?? 'none');
        if (!empty($dataRecord['identifier'])) {
            $identifier = new FHIRIdentifier();
            if (!empty($system)) {
                $identifier->setSystem($system);
            }
            $identifier->setValue($dataRecord['identifier']);
            $locationResource->addIdentifier($identifier);
        }
    }

    protected function getSystemForIdentifier(string $identifierType): ?string
    {
        // allows for expansion of identifiers in the future
        return match ($identifierType) {
            'npi' => FhirCodeSystemConstants::PROVIDER_NPI
            ,default => null
        };
    }

    private function populateServiceRoleType(FHIRLocation $locationResource, array $dataRecord)
    {
        if (!empty($dataRecord['location_role_type'])) {
            // ensure two digit format as the codeset has some leading zeros but in the database its stored as a tinyint
            $type = str_pad((string) $dataRecord['location_role_type'], 2, '0', STR_PAD_LEFT);
            $posEnum = PlaceOfServiceEnum::tryFrom($type);
            if ($posEnum !== null) {
                $coding = UtilsService::createCodeableConcept([
                    $posEnum->value => [
                        'code' => $posEnum->value,
                        'description' => $posEnum->getName(),
                        'system' => FhirCodeSystemConstants::CMS_PLACE_OF_SERVICE
                    ]
                ]);
                $locationResource->addType($coding);
            }
        }
    }
}
