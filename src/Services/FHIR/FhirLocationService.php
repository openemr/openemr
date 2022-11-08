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
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRLocation;
use OpenEMR\FHIR\R4\FHIRElement\FHIRContactPoint;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\Services\FHIR\Traits\BulkExportSupportAllOperationsTrait;
use OpenEMR\Services\FHIR\Traits\FhirBulkExportDomainResourceTrait;
use OpenEMR\Services\LocationService;
use OpenEMR\Services\Search\CompositeSearchField;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\SearchModifier;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Services\Search\TokenSearchValue;
use OpenEMR\Validators\ProcessingResult;

class FhirLocationService extends FhirServiceBase implements IFhirExportableResourceService, IResourceUSCIGProfileService
{
    use BulkExportSupportAllOperationsTrait;
    use FhirBulkExportDomainResourceTrait;

    /**
     * @var LocationService
     */
    private $locationService;

    /**
     * The patient uuid bound in the current request
     * @var string
     */
    private $patientUuid;

    public function __construct()
    {
        parent::__construct();
        $this->locationService = new LocationService();
    }

    /**
     * Returns an array mapping FHIR Location Resource search parameters to OpenEMR Location search parameters
     * @return array The search parameters
     */
    protected function loadSearchParameters()
    {
        return  [
            '_id' => new FhirSearchParameterDefinition('uuid', SearchFieldType::TOKEN, [new ServiceField('uuid', ServiceField::TYPE_UUID)])
        ];
    }

    /**
     * Parses an OpenEMR location record, returning the equivalent FHIR Location Resource
     *
     * @param array $dataRecord The source OpenEMR data record
     * @param boolean $encode Indicates if the returned resource is encoded into a string. Defaults to false.
     * @return FHIRLocation
     */
    public function parseOpenEMRRecord($dataRecord = array(), $encode = false)
    {
        $locationResource = new FHIRLocation();

        $meta = new FHIRMeta();
        $meta->setVersionId('1');
        $meta->setLastUpdated(UtilsService::getDateFormattedAsUTC());
        $locationResource->setMeta($meta);

        $id = new FHIRId();
        $id->setValue($dataRecord['uuid']);
        $locationResource->setId($id);

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

            if (!empty($dataRecord['phone'])) {
                $phone = new FHIRContactPoint();
                $phone->setSystem('phone');
                $phone->setValue($dataRecord['phone']);
                $locationResource->addTelecom($phone);
            }

            if (!empty($dataRecord['fax'])) {
                $fax = new FHIRContactPoint();
                $fax->setSystem('fax');
                $fax->setValue($dataRecord['fax']);
                $locationResource->addTelecom($fax);
            }

            if (!empty($dataRecord['website'])) {
                $url = new FHIRContactPoint();
                $url->setSystem('website');
                $url->setValue($dataRecord['website']);
                $locationResource->addTelecom($url);
            }

            if (!empty($dataRecord['email'])) {
                $email = new FHIRContactPoint();
                $email->setSystem('email');
                $email->setValue($dataRecord['email']);
                $locationResource->addTelecom($email);
            }
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
     * Searches for OpenEMR records using OpenEMR search parameters
     *
     * @param  array openEMRSearchParameters OpenEMR search fields
     * @return ProcessingResult
     */
    protected function searchForOpenEMRRecords($openEMRSearchParameters): ProcessingResult
    {
        // even though its not a patient compartment issue we still don't want certain location data such as clinician home addresses
        // being returned... or other patient locations...  Wierd that its not in the patient compartment
        if (!empty($this->patientUuid)) {
            // when we are patient bound we only want facility data returned or return just that patient's information.
            $patientType = new CompositeSearchField('patient-type', [], false);
            // patient id is the target_uuid, the uuid column is the mapped 'Location' resource in FHIR
            $patientType->addChild(new TokenSearchField('table_uuid', [new TokenSearchValue($this->patientUuid, null, true)]));
            $patientType->addChild(new TokenSearchField('type', [new TokenSearchValue(LocationService::TYPE_FACILITY)]));
            $openEMRSearchParameters['patient-type'] = $patientType;
        }
        return $this->locationService->getAll($openEMRSearchParameters, false);
    }

    public function parseFhirResource($fhirResource = array())
    {
        // TODO: If Required in Future
    }

    public function insertOpenEMRRecord($openEmrRecord)
    {
        // TODO: If Required in Future
    }

    public function updateOpenEMRRecord($fhirResourceId, $updatedOpenEMRRecord)
    {
        // TODO: If Required in Future
    }
    public function createProvenanceResource($dataRecord = array(), $encode = false)
    {
        // TODO: If Required in Future
    }

    private function hasAccessToUserLocationData()
    {
        return AclMain::aclCheckCore('admin', 'users') !== false;
    }

    private function shouldIncludeContactInformationForLocationType($type, $recordUuid)
    {
        $isPatientBoundUuid = !empty($this->patientUuid) && $this->patientUuid == $recordUuid;
        // if its not a patient requesting their own record location information we need to check permissions on this.
        if ($type == 'patient' && !$isPatientBoundUuid) {
            // only those with access to a patient's demographic information can get their data
            return AclMain::aclCheckCore("patients", "demo") !== false;
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
    function getProfileURIs(): array
    {
        return [
            'http://hl7.org/fhir/us/core/StructureDefinition/us-core-location'
        ];
    }
}
