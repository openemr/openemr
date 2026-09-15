<?php

namespace OpenEMR\Services\FHIR;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRRelatedPerson;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;
use OpenEMR\Services\ContactRelationService;
use OpenEMR\Services\FHIR\Traits\BulkExportSupportAllOperationsTrait;
use OpenEMR\Services\FHIR\Traits\FhirBulkExportDomainResourceTrait;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\FHIR\Traits\VersionedProfileTrait;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Validators\ProcessingResult;

class FhirRelatedPersonService extends FhirServiceBase implements IResourceUSCIGProfileService, IPatientCompartmentResourceService, IFhirExportableResourceService
{
    use FhirServiceBaseEmptyTrait;
    use BulkExportSupportAllOperationsTrait;
    use FhirBulkExportDomainResourceTrait;
    use VersionedProfileTrait;

    const RESOURCE_NAME="RelatedPerson";

    const USCGI_PROFILE_URI = 'http://hl7.org/fhir/us/core/StructureDefinition/us-core-relatedperson';


    /**
     * @inheritDoc
     */
    protected function loadSearchParameters()
    {
        return  [
            'patient' => $this->getPatientContextSearchField(),
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, [new ServiceField('person_uuid', ServiceField::TYPE_UUID)]),
            '_lastUpdated' => $this->getLastModifiedSearchField(),
        ];
    }

    /**
     * @inheritDoc
     */
    public function parseOpenEMRRecord($dataRecord = [], $encode = false)
    {
        $fhirRelatedPerson = new FHIRRelatedPerson();
        $this->populateId($fhirRelatedPerson, $dataRecord);
        $this->populateMeta($fhirRelatedPerson, $dataRecord);
        $this->populateActive($fhirRelatedPerson, $dataRecord);
        $this->populatePatient($fhirRelatedPerson, $dataRecord);
        $this->populateRelationship($fhirRelatedPerson, $dataRecord);
        $this->populateName($fhirRelatedPerson, $dataRecord);
        $this->populateTelecom($fhirRelatedPerson, $dataRecord);
        $this->populateAddress($fhirRelatedPerson, $dataRecord);
        return $fhirRelatedPerson;
    }

    public function populateId(FHIRRelatedPerson $fhirRelatedPerson, $dataRecord): void
    {
        if (empty($dataRecord['uuid'])) {
            // this should never happen
            throw new \InvalidArgumentException('UUID cannot be empty.');
        }
        $fhirId = new FHIRId();
        $fhirId->setValue($dataRecord['uuid']);
        $fhirRelatedPerson->setId($fhirId);
    }

    public function populateActive(FHIRRelatedPerson $fhirRelatedPerson, $dataRecord): void
    {
        if (1 === $dataRecord['active']) {
            $fhirRelatedPerson->setActive(true);
        } else {
            $fhirRelatedPerson->setActive(false);
        }
    }

    public function populateMeta(FHIRRelatedPerson $fhirRelatedPerson, array $dataRecord): void
    {
        $meta = new FHIRMeta();
        $meta->setVersionId('1');
        if (!empty($dataRecord['last_updated'])) {
            $meta->setLastUpdated(UtilsService::getLocalDateAsUTC($dataRecord['last_updated']));
        } else {
            $meta->setLastUpdated(UtilsService::getDateFormattedAsUTC());
        }
        foreach ($this->getProfileForVersions(self::USCGI_PROFILE_URI, $this->getSupportedVersions()) as $profile) {
            $meta->addProfile($profile);
        }
        $fhirRelatedPerson->setMeta($meta);
    }

    public function populatePatient(FHIRRelatedPerson $fhirRelatedPerson, $dataRecord): void
    {
        if (!empty($dataRecord['puuid'])) {
            $fhirRelatedPerson->setPatient(UtilsService::createRelativeReference('Patient', $dataRecord['puuid']));
        }
    }

    public function populateRelationship(FHIRRelatedPerson $fhirRelatedPerson, $dataRecord): void
    {
        $relationshipCode = $dataRecord['relationship_code'] ?? 'U'; // unknown
        $description = $dataRecord['relationship_code_title'] ?? 'Unknown';
        $concept = UtilsService::createCodeableConcept(
            [
                $relationshipCode => [
                    'code' => $relationshipCode,
                    'system' => FhirCodeSystemConstants::HL7_ROLE_CODE,
                    'description' => $description
                ]
            ]);
        $fhirRelatedPerson->addRelationship($concept);
    }

    public function populateName(FHIRRelatedPerson $fhirRelatedPerson, $dataRecord): void
    {
        $humanName = UtilsService::createHumanNameFromRecord($dataRecord);
        $fhirRelatedPerson->addName($humanName);
    }

    public function populateTelecom(FHIRRelatedPerson $fhirRelatedPerson, $dataRecord): void
    {
        if (!empty($dataRecord['telecom'])) {
            foreach ($dataRecord['telecom'] as $telecom) {
                $contactPoint = UtilsService::createContactPoint($telecom['value']
                    , $telecom['system'], $telecom['use']);
                $fhirRelatedPerson->addTelecom($contactPoint);
            }
        }
    }

    public function populateAddress(FHIRRelatedPerson $fhirRelatedPerson, $dataRecord): void
    {
        foreach (($dataRecord['addresses'] ?? []) as $address) {
            $fhirRelatedPerson->addAddress(UtilsService::createAddressFromRecord($address));
        }
    }

    /**
     * @param  ISearchField[] $openEMRSearchParameters
     * @return ProcessingResult
     */
    protected function searchForOpenEMRRecords($openEMRSearchParameters): ProcessingResult
    {
        $contactRelationService = new ContactRelationService();
        return $contactRelationService->searchPatientRelationships($openEMRSearchParameters);
    }

    /**
     * Parses a FHIR RelatedPerson into the OpenEMR shape consumed by
     * ContactRelationService::insertRelatedPerson / updateRelatedPerson.
     *
     * The FHIR.patient reference is REQUIRED for inserts (the relationship cannot exist
     * without a patient owner). Relationship code uses the HL7 v3 RoleCode values
     * (system http://terminology.hl7.org/CodeSystem/v3-RoleCode or the read-side
     * FhirCodeSystemConstants::HL7_ROLE_CODE alias), which map 1:1 to OpenEMR's
     * `related_person_relationship` list_options.option_id values (MTH, FTH, SPS, etc.).
     *
     * @param FHIRDomainResource $fhirResource
     * @return array<string, mixed>
     */
    public function parseFhirResource(FHIRDomainResource $fhirResource)
    {
        if (!($fhirResource instanceof FHIRRelatedPerson)) {
            throw new \InvalidArgumentException(
                'Expected FHIRRelatedPerson resource, got ' . $fhirResource::class
            );
        }

        $json = $fhirResource->jsonSerialize();
        $data = [];

        $resourceId = $json['id'] ?? null;
        if (is_string($resourceId) && $resourceId !== '') {
            $data['uuid'] = $resourceId;
        }

        // patient.reference -> puuid (resolved to pid in insertOpenEMRRecord)
        $patientRef = FhirPayloadReader::reference($json['patient'] ?? null);
        if ($patientRef !== null) {
            $patientUuid = UtilsService::parseReferenceString($patientRef, 'Patient')['uuid'] ?? null;
            if (is_string($patientUuid) && $patientUuid !== '' && UuidRegistry::isValidStringUUID($patientUuid)) {
                $data['puuid'] = $patientUuid;
            }
        }

        // relationship[].coding (HL7 v3 RoleCode) -> first matching code
        $relationships = $json['relationship'] ?? null;
        foreach (is_array($relationships) ? $relationships : [] as $rel) {
            foreach (FhirPayloadReader::codings($rel) as $coding) {
                $system = $coding['system'] ?? null;
                $code = $coding['code'] ?? null;
                $isV3 = $system === 'http://terminology.hl7.org/CodeSystem/v3-RoleCode'
                    || $system === FhirCodeSystemConstants::HL7_ROLE_CODE;
                if ($isV3 && is_string($code) && $code !== '') {
                    $data['relationship'] = $code;
                    break 2;
                }
            }
        }

        // name[] -> first_name / last_name / middle_name (prefer use=official, else first)
        $names = is_array($json['name'] ?? null) ? $json['name'] : [];
        $name = null;
        foreach ($names as $candidate) {
            if (is_array($candidate) && ($candidate['use'] ?? null) === 'official') {
                $name = $candidate;
                break;
            }
        }
        if ($name === null && is_array($names[0] ?? null)) {
            $name = $names[0];
        }
        if (is_array($name)) {
            $family = $name['family'] ?? null;
            if (is_string($family) && $family !== '') {
                $data['last_name'] = $family;
            }
            $given = is_array($name['given'] ?? null) ? $name['given'] : [];
            $firstName = $given[0] ?? null;
            if (is_string($firstName) && $firstName !== '') {
                $data['first_name'] = $firstName;
            }
            $middleName = $given[1] ?? null;
            if (is_string($middleName) && $middleName !== '') {
                $data['middle_name'] = $middleName;
            }
        }

        $gender = $json['gender'] ?? null;
        if (is_string($gender) && $gender !== '') {
            $data['gender'] = $gender;
        }
        // A partial birthDate is legal FHIR but would be stored as a fabricated
        // 1 January, so it is rejected rather than widened.
        $birthDate = FhirDateTimeParser::toDbDate(
            $json['birthDate'] ?? null,
            'RelatedPerson.birthDate'
        );
        if ($birthDate !== null) {
            $data['birth_date'] = $birthDate;
        }
        if (isset($json['active'])) {
            $data['active'] = (bool) $json['active'];
        }

        $telecoms = [];
        $telecomEntries = $json['telecom'] ?? null;
        foreach (is_array($telecomEntries) ? $telecomEntries : [] as $t) {
            $value = FhirPayloadReader::getString($t, 'value');
            if ($value === null) {
                continue;
            }
            $telecoms[] = [
                'system' => FhirPayloadReader::getString($t, 'system') ?? 'phone',
                'use' => FhirPayloadReader::getString($t, 'use') ?? 'home',
                'value' => $value,
            ];
        }
        $data['telecoms'] = $telecoms;

        $addresses = [];
        $addressEntries = $json['address'] ?? null;
        foreach (is_array($addressEntries) ? $addressEntries : [] as $a) {
            if (!is_array($a)) {
                continue;
            }
            $lines = $a['line'] ?? null;
            $addresses[] = [
                'line1' => FhirPayloadReader::getString($lines, 0) ?? '',
                'line2' => FhirPayloadReader::getString($lines, 1) ?? '',
                'city' => FhirPayloadReader::getString($a, 'city') ?? '',
                'state' => FhirPayloadReader::getString($a, 'state') ?? '',
                'postal_code' => FhirPayloadReader::getString($a, 'postalCode') ?? '',
                'country' => FhirPayloadReader::getString($a, 'country') ?? '',
                'use' => FhirPayloadReader::getString($a, 'use') ?? 'home',
            ];
        }
        $data['addresses'] = $addresses;

        return $data;
    }

    /**
     * @param mixed $openEmrRecord The parsed record from parseFhirResource()
     */
    protected function insertOpenEMRRecord($openEmrRecord): ProcessingResult
    {
        if (!is_array($openEmrRecord)) {
            throw new \InvalidArgumentException('Expected a parsed OpenEMR RelatedPerson record array');
        }

        $puuid = $openEmrRecord['puuid'] ?? null;
        if (!is_string($puuid) || $puuid === '') {
            $result = new ProcessingResult();
            $result->setValidationMessages([
                'patient' => 'FHIR RelatedPerson requires a resolvable Patient reference',
            ]);
            return $result;
        }
        $pid = QueryUtils::fetchSingleValue(
            'SELECT pid FROM patient_data WHERE uuid = ?',
            'pid',
            [UuidRegistry::uuidToBytes($puuid)]
        );
        if (!is_numeric($pid)) {
            $result = new ProcessingResult();
            $result->setValidationMessages([
                'patient' => 'Patient reference could not be resolved: ' . $puuid,
            ]);
            return $result;
        }
        $openEmrRecord['pid'] = (int) $pid;
        unset($openEmrRecord['puuid']);

        return (new ContactRelationService())->insertRelatedPerson(
            FhirPayloadReader::stringKeyed($openEmrRecord)
        );
    }

    /**
     * @param string $fhirResourceId
     * @param array<array-key, mixed> $updatedOpenEMRRecord
     */
    protected function updateOpenEMRRecord($fhirResourceId, $updatedOpenEMRRecord): ProcessingResult
    {
        // The owning patient must be supplied — without it, the underlying UPDATE
        // would target every contact_relation row pointing at this person, leaking
        // mutations across patients. We resolve puuid -> pid here and pass through.
        $puuid = $updatedOpenEMRRecord['puuid'] ?? null;
        if (!is_string($puuid) || $puuid === '' || !UuidRegistry::isValidStringUUID($puuid)) {
            $result = new ProcessingResult();
            $result->setValidationMessages([
                'patient' => 'FHIR RelatedPerson PUT requires a patient reference identifying '
                    . 'which patient owns this relationship',
            ]);
            return $result;
        }
        $pid = QueryUtils::fetchSingleValue(
            'SELECT pid FROM patient_data WHERE uuid = ?',
            'pid',
            [UuidRegistry::uuidToBytes($puuid)]
        );
        if (!is_numeric($pid)) {
            $result = new ProcessingResult();
            $result->setValidationMessages([
                'patient' => 'Patient reference could not be resolved: ' . $puuid,
            ]);
            return $result;
        }
        unset($updatedOpenEMRRecord['puuid']);
        return (new ContactRelationService())->updateRelatedPerson(
            $fhirResourceId,
            FhirPayloadReader::stringKeyed($updatedOpenEMRRecord),
            (int) $pid
        );
    }

    public function getPatientContextSearchField(): FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('patient', SearchFieldType::REFERENCE, [new ServiceField('puuid', ServiceField::TYPE_UUID)]);
    }

    public function getSupportedVersions(): array
    {
        return self::PROFILE_VERSIONS_V2;
    }

    public function getProfileURIs(): array
    {
        return $this->getProfileForVersions(self::USCGI_PROFILE_URI, $this->getSupportedVersions());
    }

    public function getLastModifiedSearchField(): FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('_lastUpdated', SearchFieldType::DATETIME, ['updated_date']);
    }
}
