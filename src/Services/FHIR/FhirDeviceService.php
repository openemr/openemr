<?php

/**
 * FhirDeviceService.php
 * @package openemr
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRDevice;
use OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDevice\FHIRDeviceUdiCarrier;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;
use OpenEMR\Services\DeviceService;
use OpenEMR\Services\FHIR\Traits\BulkExportSupportAllOperationsTrait;
use OpenEMR\Services\FHIR\Traits\FhirBulkExportDomainResourceTrait;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\FHIR\Traits\VersionedProfileTrait;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Validators\ProcessingResult;

class FhirDeviceService extends FhirServiceBase implements IResourceUSCIGProfileService, IFhirExportableResourceService, IPatientCompartmentResourceService
{
    use FhirServiceBaseEmptyTrait;
    use BulkExportSupportAllOperationsTrait;
    use FhirBulkExportDomainResourceTrait;
    use VersionedProfileTrait;

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
        '_lastUpdated' => $this->getLastModifiedSearchField(),
        ];
    }

    public function getLastModifiedSearchField(): ?FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('_lastUpdated', SearchFieldType::DATETIME, ['modifydate']);
    }

    /**
     * Parses an OpenEMR data record, returning the equivalent FHIR Resource
     *
     * @param $dataRecord The source OpenEMR data record
     * @param $encode Indicates if the returned resource is encoded into a string. Defaults to True.
     * @return the FHIR Resource. Returned format is defined using $encode parameter.
     */
    public function parseOpenEMRRecord($dataRecord = [], $encode = false)
    {
        $device = new FHIRDevice();

        $fhirMeta = new FHIRMeta();
        $fhirMeta->setVersionId('1');
        if (!empty($dataRecord['modifydate'])) {
            $fhirMeta->setLastUpdated(UtilsService::getLocalDateAsUTC($dataRecord['modifydate']));
        } else {
            $fhirMeta->setLastUpdated(UtilsService::getDateFormattedAsUTC());
        }
        $device->setMeta($fhirMeta);

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
     * @param array<string, ISearchField> $openEMRSearchParameters OpenEMR search fields
     * @return ProcessingResult OpenEMR records
     */
    protected function searchForOpenEMRRecords($openEMRSearchParameters): ProcessingResult
    {
        return $this->deviceService->search($openEMRSearchParameters);
    }

    /**
     * Parses a FHIR Device resource into the OpenEMR `lists`-table shape.
     *
     * FHIR Device.patient is a Patient reference resolved to pid in insertOpenEMRRecord.
     * FHIR Device.type SNOMED coding maps to lists.diagnosis with the SNOMED-CT prefix
     * convention used by the read side. UDI carrier identifier + HRF map to udi_data.di
     * and lists.udi respectively. Manufacturer / lot / serial / expiration / manufactureDate
     * land in udi_data.standard_elements to round-trip through the read side.
     *
     * @param FHIRDomainResource $fhirResource
     * @return array<string, mixed>
     */
    public function parseFhirResource(FHIRDomainResource $fhirResource)
    {
        if (!($fhirResource instanceof FHIRDevice)) {
            throw new \InvalidArgumentException(
                'Expected FHIRDevice resource, got ' . $fhirResource::class
            );
        }

        $json = $fhirResource->jsonSerialize();
        $data = [];

        if (!empty($json['id']) && is_string($json['id'])) {
            $data['uuid'] = $json['id'];
        }

        // patient.reference -> puuid (resolved to pid downstream)
        $patientRef = $json['patient']['reference'] ?? null;
        if (is_string($patientRef) && $patientRef !== '') {
            $parsed = UtilsService::parseReferenceString($patientRef, 'Patient');
            if (!empty($parsed['uuid']) && UuidRegistry::isValidStringUUID($parsed['uuid'])) {
                $data['puuid'] = $parsed['uuid'];
            }
        }

        // type.coding -> lists.diagnosis (SNOMED-CT:code) + lists.title (display)
        $typeCoding = $json['type']['coding'][0] ?? null;
        if (is_array($typeCoding)) {
            $code = $typeCoding['code'] ?? null;
            $display = $typeCoding['display'] ?? ($json['type']['text'] ?? null);
            if (is_string($code) && $code !== '') {
                $data['diagnosis'] = 'SNOMED-CT:' . $code;
            }
            if (is_string($display) && $display !== '') {
                $data['title'] = $display;
            }
        } elseif (!empty($json['type']['text']) && is_string($json['type']['text'])) {
            $data['title'] = $json['type']['text'];
        }

        // udiCarrier[0]: deviceIdentifier -> udi_data.standard_elements.di, carrierHRF -> lists.udi
        $udiCarrier = $json['udiCarrier'][0] ?? null;
        $standardElements = [];
        if (is_array($udiCarrier)) {
            if (!empty($udiCarrier['deviceIdentifier']) && is_string($udiCarrier['deviceIdentifier'])) {
                $standardElements['di'] = $udiCarrier['deviceIdentifier'];
            }
            if (!empty($udiCarrier['carrierHRF']) && is_string($udiCarrier['carrierHRF'])) {
                $data['udi'] = $udiCarrier['carrierHRF'];
            }
        }

        // Remaining standard_elements: companyName, manufacturingDate, expirationDate,
        // lotNumber, serialNumber, donationId
        $manufactureDate = $json['manufactureDate'] ?? null;
        if (is_string($manufactureDate) && $manufactureDate !== '') {
            $standardElements['manufacturingDate'] = $manufactureDate;
        }
        $expirationDate = $json['expirationDate'] ?? null;
        if (is_string($expirationDate) && $expirationDate !== '') {
            $standardElements['expirationDate'] = $expirationDate;
        }
        $lotNumber = $json['lotNumber'] ?? null;
        if (is_string($lotNumber) && $lotNumber !== '') {
            $standardElements['lotNumber'] = $lotNumber;
        }
        $serialNumber = $json['serialNumber'] ?? null;
        if (is_string($serialNumber) && $serialNumber !== '') {
            $standardElements['serialNumber'] = $serialNumber;
        }
        $distinctIdentifier = $json['distinctIdentifier'] ?? null;
        if (is_string($distinctIdentifier) && $distinctIdentifier !== '') {
            $standardElements['donationId'] = $distinctIdentifier;
        }
        // manufacturer (FHIR Device.manufacturer is a string)
        $manufacturer = $json['manufacturer'] ?? null;
        if (is_string($manufacturer) && $manufacturer !== '') {
            $standardElements['companyName'] = $manufacturer;
        }

        if ($standardElements !== []) {
            $data['udi_data'] = ['standard_elements' => $standardElements];
        }

        return $data;
    }

    /**
     * Resolves the FHIR Patient reference to internal pid, then delegates to DeviceService::insert.
     *
     * @param array<string, mixed> $openEmrRecord
     */
    protected function insertOpenEMRRecord($openEmrRecord): ProcessingResult
    {
        $resolved = $this->resolvePatientId($openEmrRecord);
        if ($resolved instanceof ProcessingResult) {
            return $resolved;
        }
        return $this->deviceService->insert($openEmrRecord);
    }

    /**
     * @param string $fhirResourceId
     * @param array<string, mixed> $updatedOpenEMRRecord
     */
    protected function updateOpenEMRRecord($fhirResourceId, $updatedOpenEMRRecord): ProcessingResult
    {
        // Patient is not mutable on update; drop the resolved pid path so updates only touch fields the FHIR resource carries.
        unset($updatedOpenEMRRecord['puuid']);
        return $this->deviceService->update($fhirResourceId, $updatedOpenEMRRecord);
    }

    /**
     * Mutates $record in place: puuid -> pid. Returns a ProcessingResult on failure, null on success.
     *
     * @param array<string, mixed> $record
     */
    private function resolvePatientId(array &$record): ?ProcessingResult
    {
        $puuid = $record['puuid'] ?? null;
        if (!is_string($puuid) || $puuid === '') {
            $result = new ProcessingResult();
            $result->setValidationMessages([
                'patient' => 'FHIR Device requires a resolvable Patient reference',
            ]);
            return $result;
        }
        $pid = QueryUtils::fetchSingleValue(
            'SELECT pid FROM patient_data WHERE uuid = ?',
            'pid',
            [UuidRegistry::uuidToBytes($puuid)]
        );
        if ($pid === null) {
            $result = new ProcessingResult();
            $result->setValidationMessages([
                'patient' => 'Patient reference could not be resolved: ' . $puuid,
            ]);
            return $result;
        }
        $record['pid'] = (int) $pid;
        unset($record['puuid']);
        return null;
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
    public function getProfileURIs(): array
    {
        return $this->getProfileForVersions(self::USCGI_PROFILE_URI, $this->getSupportedVersions());
    }

    public function getPatientContextSearchField(): FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('patient', SearchFieldType::REFERENCE, [new ServiceField('puuid', ServiceField::TYPE_UUID)]);
    }
}
