<?php

/**
 * FhirDeviceService.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRDevice;
use OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDevice\FHIRDeviceUdiCarrier;
use OpenEMR\Services\DeviceService;
use OpenEMR\Services\FHIR\Traits\BulkExportSupportAllOperationsTrait;
use OpenEMR\Services\FHIR\Traits\FhirBulkExportDomainResourceTrait;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Validators\ProcessingResult;

class FhirDeviceService extends FhirServiceBase implements IResourceUSCIGProfileService, IFhirExportableResourceService, IPatientCompartmentResourceService
{
    use FhirServiceBaseEmptyTrait;
    use BulkExportSupportAllOperationsTrait;
    use FhirBulkExportDomainResourceTrait;

    /**
     * @var DeviceService
     */
    private $deviceService;


    const USCGI_PROFILE_URI = 'http://hl7.org/fhir/us/core/StructureDefinition/us-core-implantable-device';

    public function __construct()
    {
        parent::__construct();
        $this->deviceService = new DeviceService();
    }

    /**
     * Returns an array mapping FHIR Resource search parameters to OpenEMR search parameters
     */
    protected function loadSearchParameters()
    {
        return [
        'patient' => $this->getPatientContextSearchField(),
        '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, [new ServiceField('uuid', ServiceField::TYPE_UUID)]),
        ];
    }

    /**
     * Parses an OpenEMR data record, returning the equivalent FHIR Resource
     *
     * @param $dataRecord The source OpenEMR data record
     * @param $encode Indicates if the returned resource is encoded into a string. Defaults to True.
     * @return the FHIR Resource. Returned format is defined using $encode parameter.
     */
    public function parseOpenEMRRecord($dataRecord = array(), $encode = false)
    {
        $device = new FHIRDevice();

        $device->setMeta(UtilsService::createFhirMeta('1', gmdate('c')));

        $id = new FHIRId();
        $id->setValue($dataRecord['uuid']);
        $device->setId($id);

        // only required field is the type and patient
        if (!empty($dataRecord['puuid'])) {
            $device->setPatient(UtilsService::createRelativeReference('Patient', $dataRecord['puuid']));
        } else {
            $device->setPatient(UtilsService::createDataMissingExtension());
        }

        if (!empty($dataRecord['code'])) {
            $codeableConcept = UtilsService::createCodeableConcept($dataRecord['code'], FhirCodeSystemConstants::SNOMED_CT);
            $device->setType($codeableConcept);
        } else {
            $device->setType(UtilsService::createDataMissingExtension());
        }

        if (!empty($dataRecord['udi_di'])) {
            $udiCarrier = new FHIRDeviceUdiCarrier();
            $udiCarrier->setDeviceIdentifier($dataRecord['udi_di']);

            if (!empty($dataRecord['udi'])) {
                $udiCarrier->setCarrierHRF($dataRecord['udi']);
            }
            $device->addUdiCarrier($udiCarrier);
        }

        if (!empty($dataRecord['manufactureDate'])) {
            $manufactureDate = new FHIRDateTime();
            $manufactureDate->setValue($dataRecord['manufactureDate']);
            $device->setManufactureDate($manufactureDate);
        }
        if (!empty($dataRecord['expirationDate'])) {
            $expirationDate = new FHIRDateTime();
            $expirationDate->setValue($dataRecord['expirationDate']);
            $device->setExpirationDate($expirationDate);
        }

        if (!empty($dataRecord['lotNumber'])) {
            $device->setLotNumber($dataRecord['lotNumber']);
        }

        if (!empty($dataRecord['serialNumber'])) {
            $device->setSerialNumber($dataRecord['serialNumber']);
        }

        if (!empty($dataRecord['distinctIdentifier'])) {
            $device->setDistinctIdentifier($dataRecord['distinctIdentifier']);
        }
        return $device;
    }

    /**
     * Searches for OpenEMR records using OpenEMR search parameters
     * @param openEMRSearchParameters OpenEMR search fields
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @return OpenEMR records
     */
    protected function searchForOpenEMRRecords($openEMRSearchParameters): ProcessingResult
    {
        return $this->deviceService->search($openEMRSearchParameters);
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
        if (!($dataRecord instanceof FHIRDevice)) {
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

    /**
     * Returns the Canonical URIs for the FHIR resource for each of the US Core Implementation Guide Profiles that the
     * resource implements.  Most resources have only one profile, but several like DiagnosticReport and Observation
     * has multiple profiles that must be conformed to.
     * @see https://www.hl7.org/fhir/us/core/CapabilityStatement-us-core-server.html for the list of profiles
     * @return string[]
     */
    function getProfileURIs(): array
    {
        return [self::USCGI_PROFILE_URI];
    }

    public function getPatientContextSearchField(): FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('patient', SearchFieldType::REFERENCE, [new ServiceField('puuid', ServiceField::TYPE_UUID)]);
    }
}
