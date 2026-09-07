<?php

namespace OpenEMR\Services\FHIR;

use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRImmunization;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRDate;
use OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRImmunizationStatusCodes;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;
use OpenEMR\FHIR\R4\FHIRResource\FHIRImmunization\FHIRImmunizationPerformer;
use OpenEMR\Services\BaseService;
use OpenEMR\Services\FHIR\FhirServiceBase;
use OpenEMR\Services\FHIR\Traits\BulkExportSupportAllOperationsTrait;
use OpenEMR\Services\FHIR\Traits\FhirBulkExportDomainResourceTrait;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\FHIR\Traits\VersionedProfileTrait;
use OpenEMR\Services\ImmunizationService;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Validators\ProcessingResult;

/**
 * FHIR Immunization Service
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class FhirImmunizationService extends FhirServiceBase implements IResourceUSCIGProfileService, IFhirExportableResourceService, IPatientCompartmentResourceService
{
    use FhirServiceBaseEmptyTrait;
    use BulkExportSupportAllOperationsTrait;
    use FhirBulkExportDomainResourceTrait;
    use VersionedProfileTrait;

    /**
     * @var ImmunizationService
     */
    private $immunizationService;

    const USCGI_PROFILE_URI = 'http://hl7.org/fhir/us/core/StructureDefinition/us-core-immunization';

    public function __construct(?string $fhirAPIURL = null)
    {
        parent::__construct($fhirAPIURL);
        $this->immunizationService = new ImmunizationService();
    }

    /**
     * Returns an array mapping FHIR Immunization Resource search parameters to OpenEMR Immunization search parameters
     * @return array The search parameters
     */
    protected function loadSearchParameters()
    {
        return  [
            'patient' => $this->getPatientContextSearchField(),
            '_id' => new FhirSearchParameterDefinition('uuid', SearchFieldType::TOKEN, [new ServiceField('uuid', ServiceField::TYPE_UUID)]),
            '_lastUpdated' => $this->getLastModifiedSearchField(),
        ];
    }

    public function getLastModifiedSearchField(): ?FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('_lastUpdated', SearchFieldType::DATETIME, ['update_date']);
    }

    /**
     * Parses an OpenEMR immunization record, returning the equivalent FHIR Immunization Resource
     *
     * @param array $dataRecord The source OpenEMR data record
     * @param bool $encode Indicates if the returned resource is encoded into a string. Defaults to false.
     * @return FHIRImmunization
     */
    public function parseOpenEMRRecord($dataRecord = [], $encode = false)
    {
        $immunizationResource = new FHIRImmunization();

        $meta = new FHIRMeta();
        $meta->setVersionId('1');
        if (!empty($dataRecord['update_date'])) {
            $meta->setLastUpdated(UtilsService::getLocalDateAsUTC($dataRecord['update_date']));
        } else {
            $meta->setLastUpdated(UtilsService::getDateFormattedAsUTC());
        }
        $immunizationResource->setMeta($meta);

        $id = new FHIRId();
        $id->setValue($dataRecord['uuid']);
        $immunizationResource->setId($id);

        $status = new FHIRImmunizationStatusCodes();
        if ($dataRecord['added_erroneously'] != "0") {
            $status->setValue("entered-in-error");
        } elseif ($dataRecord['completion_status'] == "Completed") {
            $status->setValue("completed");
        } else {
            $status->setValue("not-done");

            // TODO: @adunsulag we need to update these codes here as we need to map better from NIP002
            // to these status codes here: https://terminology.hl7.org/3.1.0/CodeSystem-v3-ActReason.html
            //
            $code = "PATOBJ";
            $display = "patient objection";
            if (!empty($dataRecord['refusal_reason_cdc_nip_code'])) {
                // we are leaving these here just to document these values as PATOBJ corresponds to both patient or
                // guardian objection.  Other doesn't have a correspondence, and patient decision is already handled.
                switch ($dataRecord['refusal_reason_cdc_nip_code']) {
                    case '00': // Parental exemption
                        break;
                    case '01': // Religious exemption
                        $code = "RELIG";
                        $display =  "religious objection";
                        break;
                    case '02': // other
                        break;
                    case '03': // patient decision
                    default:
                        break;
                }
            }
            $statusReason = new FHIRCodeableConcept();
            $statusReasonCoding = new FHIRCoding();
            $statusReasonCoding->setSystem(FhirCodeSystemConstants::IMMUNIZATION_OBJECTION_REASON);
            $statusReasonCoding->setCode($code);
            $statusReasonCoding->setDisplay($display);
            $statusReason->addCoding($statusReasonCoding);
            $immunizationResource->setStatusReason($statusReason);
        }
        $immunizationResource->setStatus($status);
        $immunizationResource->setPrimarySource($dataRecord['primarySource']);

        if (!empty($dataRecord['cvx_code'])) {
            $vaccineCode = new FHIRCodeableConcept();
            $vaccineCode->addCoding([
                'system' => "http://hl7.org/fhir/sid/cvx",
                'code' =>  $dataRecord['cvx_code'],
                'display' => $dataRecord['cvx_code_text']
            ]);
            $immunizationResource->setVaccineCode($vaccineCode);
        }

        if (!empty($dataRecord['puuid'])) {
            $patient = new FHIRReference(['reference' => 'Patient/' . $dataRecord['puuid']]);
            $immunizationResource->setPatient($patient);
        }

        if (!empty($dataRecord['administered_date'])) {
            $occurenceDateTime = new FHIRDateTime();
            $occurenceDateTime->setValue($dataRecord['administered_date']);
            $immunizationResource->setOccurrenceDateTime($occurenceDateTime);
        }

        if (!empty($dataRecord['create_date'])) {
            $recorded = new FHIRDateTime();
            $recorded->setValue($dataRecord['create_date']);
            $immunizationResource->setRecorded($recorded);
        }

        if (!empty($dataRecord['expiration_date'])) {
            $expirationDate = new FHIRDate();
            $expirationDate->setValue($dataRecord['expiration_date']);
            $immunizationResource->setExpirationDate($expirationDate);
        }

        if (!empty($dataRecord['note'])) {
            $immunizationResource->addNote([
                'text' => $dataRecord['note']
            ]);
        }

        if (!empty($dataRecord['administration_site'])) {
            $siteCode = new FHIRCodeableConcept();
            $siteCode->addCoding([
                'system' => "http://terminology.hl7.org/CodeSystem/v3-ActSite",
                'code' =>  $dataRecord['site_code'],
                'display' => $dataRecord['site_display']
            ]);
            $immunizationResource->setSite($siteCode);
        }

        if (!empty($dataRecord['lot_number'])) {
            $immunizationResource->setLotNumber($dataRecord['lot_number']);
        }

        if (!empty($dataRecord['administration_site'])) {
            $doseQuantity = new FHIRQuantity();
            $doseQuantity->setValue($dataRecord['amount_administered']);
            $doseQuantity->setSystem(FhirCodeSystemConstants::UNITS_OF_MEASURE);
            $doseQuantity->setCode($dataRecord['amount_administered_unit']);
            $immunizationResource->setDoseQuantity($doseQuantity);
        }

        if (!empty($dataRecord['provider_uuid']) && !empty($dataRecord['provider_npi'])) {
            $performer = new FHIRImmunizationPerformer();
            $performer->setActor(UtilsService::createRelativeReference("Practitioner", $dataRecord['provider_uuid']));
            $immunizationResource->addPerformer($performer);
        }

        if (!empty($dataRecord['euuid'])) {
            $encounterReference = new FHIRReference();
            $encounterReference->setReference('Encounter/' . $dataRecord['euuid']);
            $immunizationResource->setEncounter($encounterReference);
        }

        if (!empty($dataRecord['facility_location_uuid'])) {
            $locationReference = new FHIRReference();
            $locationReference->setReference('Location/' . $dataRecord['facility_location_uuid']);
            $immunizationResource->setLocation($locationReference);
        }

        // education is failing ONC validation, since we don't need it for ONC we are going to leave it off for now.
//        if (!empty($dataRecord['education_date'])) {
//            $education = new FHIRImmunizationEducation();
//            $educationDateTime = new FHIRDateTime();
//            $educationDateTime->setValue($dataRecord['education_date']);
//            $education->setPresentationDate($educationDateTime);
//            $immunizationResource->addEducation($education);
//        }

        if ($encode) {
            return json_encode($immunizationResource);
        } else {
            return $immunizationResource;
        }
    }

    /**
     * Parses a FHIR Immunization Resource, returning the equivalent OpenEMR immunization record
     *
     * @param FHIRDomainResource $fhirResource The source FHIR resource
     * @return array The OpenEMR data record
     */
    public function parseFhirResource(FHIRDomainResource $fhirResource)
    {
        if (!($fhirResource instanceof FHIRImmunization)) {
            throw new \InvalidArgumentException(
                'Expected FHIRImmunization resource, got ' . $fhirResource::class
            );
        }

        // Use jsonSerialize() to get a normalized array representation since
        // the FHIR R4 library does not deeply hydrate nested objects
        $json = $fhirResource->jsonSerialize();
        $data = [];

        $resourceId = $json['id'] ?? null;
        if (is_string($resourceId) && $resourceId !== '') {
            $data['uuid'] = $resourceId;
        }

        // Patient reference -> patient_id
        $patientUuid = $this->referenceUuid($json['patient'] ?? null, 'Patient');
        if ($patientUuid !== '') {
            $puuidBytes = UuidRegistry::uuidToBytes($patientUuid);
            $patientId = BaseService::getIdByUuid($puuidBytes, 'patient_data', 'pid');
            if ($patientId !== false) {
                $data['patient_id'] = $patientId;
            }
        }

        // VaccineCode -> cvx_code
        $cvxCode = FhirPayloadReader::firstCodingCode($json['vaccineCode'] ?? null);
        if ($cvxCode !== '') {
            $data['cvx_code'] = $cvxCode;
        }

        // OccurrenceDateTime -> administered_date. When a dose was given is a
        // point in time, so partial precision is rejected rather than widened.
        $administeredDate = FhirDateTimeParser::toDbDate(
            $json['occurrenceDateTime'] ?? null,
            'Immunization.occurrenceDateTime'
        );
        if ($administeredDate !== null) {
            $data['administered_date'] = $administeredDate;
        }

        // Status -> completion_status / added_erroneously
        $status = $json['status'] ?? null;
        if (is_string($status) && $status !== '') {
            switch ($status) {
                case 'completed':
                    $data['completion_status'] = 'Completed';
                    break;
                case 'entered-in-error':
                    $data['added_erroneously'] = '1';
                    break;
                case 'not-done':
                    $data['completion_status'] = 'Refused';
                    break;
            }
        }

        // StatusReason -> refusal_reason
        $refusalReason = FhirPayloadReader::firstCodingCode($json['statusReason'] ?? null);
        if ($refusalReason !== '') {
            $data['refusal_reason'] = $refusalReason;
        }

        // LotNumber -> lot_number
        $lotNumber = $json['lotNumber'] ?? null;
        if (is_string($lotNumber) && $lotNumber !== '') {
            $data['lot_number'] = $lotNumber;
        }

        // ExpirationDate -> expiration_date
        $expiration = FhirDateTimeParser::toDbDate(
            $json['expirationDate'] ?? null,
            'Immunization.expirationDate'
        );
        if ($expiration !== null) {
            $data['expiration_date'] = $expiration;
        }

        // Site -> administration_site
        $administrationSite = FhirPayloadReader::firstCodingCode($json['site'] ?? null);
        if ($administrationSite !== '') {
            $data['administration_site'] = $administrationSite;
        }

        // DoseQuantity -> amount_administered, amount_administered_unit
        $doseQuantity = $json['doseQuantity'] ?? null;
        $doseValue = is_array($doseQuantity) ? ($doseQuantity['value'] ?? null) : null;
        if (is_numeric($doseValue) && (float) $doseValue !== 0.0) {
            $data['amount_administered'] = $doseValue;
        }
        $doseUnit = is_array($doseQuantity) ? ($doseQuantity['code'] ?? null) : null;
        if (is_string($doseUnit) && $doseUnit !== '') {
            $data['amount_administered_unit'] = $doseUnit;
        }

        // Note -> note
        $notes = $json['note'] ?? null;
        $firstNote = is_array($notes) ? ($notes[0] ?? null) : null;
        $noteText = is_array($firstNote) ? ($firstNote['text'] ?? null) : null;
        if (is_string($noteText) && $noteText !== '') {
            $data['note'] = $noteText;
        }

        // Performer -> administered_by_id (resolve Practitioner uuid to id)
        $performers = $json['performer'] ?? null;
        foreach (is_array($performers) ? $performers : [] as $performer) {
            $practitionerUuid = $this->referenceUuid(
                is_array($performer) ? ($performer['actor'] ?? null) : null,
                'Practitioner'
            );
            if ($practitionerUuid === '') {
                continue;
            }
            $practitionerUuidBytes = UuidRegistry::uuidToBytes($practitionerUuid);
            $practitionerId = BaseService::getIdByUuid(
                $practitionerUuidBytes,
                'users',
                'id'
            );
            if ($practitionerId !== false) {
                $data['administered_by_id'] = $practitionerId;
            }
            break;
        }

        // Encounter -> encounter_id (resolve Encounter uuid to encounter number)
        $encounterUuid = $this->referenceUuid($json['encounter'] ?? null, 'Encounter');
        if ($encounterUuid !== '') {
            $encounterUuidBytes = UuidRegistry::uuidToBytes($encounterUuid);
            $encounterId = BaseService::getIdByUuid(
                $encounterUuidBytes,
                'form_encounter',
                'encounter'
            );
            if ($encounterId !== false) {
                $data['encounter_id'] = $encounterId;
            }
        }

        // Recorded -> create_date
        $recorded = FhirDateTimeParser::toDbDateTime(
            $json['recorded'] ?? null,
            'Immunization.recorded'
        );
        if ($recorded !== null) {
            $data['create_date'] = $recorded;
        }

        // PrimarySource -> information_source
        if (isset($json['primarySource'])) {
            $data['information_source'] = $json['primarySource']
                ? 'new_immunization_record'
                : 'other_provider';
        }

        return $data;
    }

    /**
     * Resolves a FHIR Reference element to a well-formed uuid of the expected
     * resource type.
     *
     * @param mixed $reference The Reference element from the payload
     * @param string $expectedType The FHIR resource type the reference must name
     * @return string The uuid, or '' when absent, of another type, or malformed
     */
    private function referenceUuid($reference, string $expectedType): string
    {
        $referenceString = is_array($reference) ? ($reference['reference'] ?? null) : null;
        if (!is_string($referenceString) || $referenceString === '') {
            return '';
        }
        $uuid = UtilsService::parseReferenceString($referenceString, $expectedType)['uuid'] ?? null;
        if (!is_string($uuid) || $uuid === '' || !UuidRegistry::isValidStringUUID($uuid)) {
            return '';
        }

        return $uuid;
    }

    /**
     * Inserts an OpenEMR record into the system.
     *
     * @param mixed $openEmrRecord The parsed record from parseFhirResource()
     * @return ProcessingResult
     */
    protected function insertOpenEMRRecord($openEmrRecord)
    {
        if (!is_array($openEmrRecord)) {
            throw new \InvalidArgumentException('Expected a parsed OpenEMR Immunization record array');
        }

        return $this->immunizationService->insert($openEmrRecord);
    }

    /**
     * Updates an existing OpenEMR record.
     *
     * @param string $fhirResourceId The OpenEMR record's FHIR Resource ID (uuid)
     * @param array $updatedOpenEMRRecord The updated OpenEMR record
     * @return ProcessingResult
     */
    protected function updateOpenEMRRecord($fhirResourceId, $updatedOpenEMRRecord)
    {
        return $this->immunizationService->update($fhirResourceId, $updatedOpenEMRRecord);
    }

    /**
     * Searches for OpenEMR records using OpenEMR search parameters
     *
     * @param array<string, ISearchField> $openEMRSearchParameters OpenEMR search fields
     * @return ProcessingResult
     */
    protected function searchForOpenEMRRecords($openEMRSearchParameters): ProcessingResult
    {
        // puuid binding happens in the $openEMRSearchParameters
        return $this->immunizationService->getAll($openEMRSearchParameters, true);
    }

    public function createProvenanceResource($dataRecord = [], $encode = false)
    {
        if (!($dataRecord instanceof FHIRImmunization)) {
            throw new \BadMethodCallException("Data record should be correct instance class");
        }
        $fhirProvenanceService = new FhirProvenanceService();
        $author = null;
        if (!empty($dataRecord->getPerformer())) {
            $performer = current($dataRecord->getPerformer());
            $author = $performer->getActor();
        }
        $fhirProvenance = $fhirProvenanceService->createProvenanceForDomainResource($dataRecord, $author);
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
