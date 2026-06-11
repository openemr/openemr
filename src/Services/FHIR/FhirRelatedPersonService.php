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

        if (!empty($json['id']) && is_string($json['id'])) {
            $data['uuid'] = $json['id'];
        }

        // patient.reference -> puuid (resolved to pid in insertOpenEMRRecord)
        $patientRef = $json['patient']['reference'] ?? null;
        if (is_string($patientRef) && $patientRef !== '') {
            $parsed = UtilsService::parseReferenceString($patientRef, 'Patient');
            if (!empty($parsed['uuid']) && UuidRegistry::isValidStringUUID($parsed['uuid'])) {
                $data['puuid'] = $parsed['uuid'];
            }
        }

        // relationship[].coding (HL7 v3 RoleCode) -> first matching code
        foreach (($json['relationship'] ?? []) as $rel) {
            if (!is_array($rel)) {
                continue;
            }
            foreach (($rel['coding'] ?? []) as $coding) {
                if (!is_array($coding)) {
                    continue;
                }
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
        if ($name === null && !empty($names) && is_array($names[0])) {
            $name = $names[0];
        }
        if (is_array($name)) {
            if (!empty($name['family']) && is_string($name['family'])) {
                $data['last_name'] = $name['family'];
            }
            $given = is_array($name['given'] ?? null) ? $name['given'] : [];
            if (!empty($given[0]) && is_string($given[0])) {
                $data['first_name'] = $given[0];
            }
            if (!empty($given[1]) && is_string($given[1])) {
                $data['middle_name'] = $given[1];
            }
        }

        if (!empty($json['gender']) && is_string($json['gender'])) {
            $data['gender'] = $json['gender'];
        }
        if (!empty($json['birthDate']) && is_string($json['birthDate'])) {
            $dt = date_create_immutable($json['birthDate']);
            if ($dt !== false) {
                $data['birth_date'] = $dt->format('Y-m-d');
            }
        }
        if (isset($json['active'])) {
            $data['active'] = (bool) $json['active'];
        }

        $telecoms = [];
        foreach (($json['telecom'] ?? []) as $t) {
            if (!is_array($t) || empty($t['value'])) {
                continue;
            }
            $telecoms[] = [
                'system' => is_string($t['system'] ?? null) ? $t['system'] : 'phone',
                'use' => is_string($t['use'] ?? null) ? $t['use'] : 'home',
                'value' => $t['value'],
            ];
        }
        $data['telecoms'] = $telecoms;

        $addresses = [];
        foreach (($json['address'] ?? []) as $a) {
            if (!is_array($a)) {
                continue;
            }
            $line1 = $a['line'][0] ?? '';
            $addresses[] = [
                'line1' => is_string($line1) ? $line1 : '',
                'line2' => is_string($a['line'][1] ?? null) ? $a['line'][1] : '',
                'city' => is_string($a['city'] ?? null) ? $a['city'] : '',
                'state' => is_string($a['state'] ?? null) ? $a['state'] : '',
                'postal_code' => is_string($a['postalCode'] ?? null) ? $a['postalCode'] : '',
                'country' => is_string($a['country'] ?? null) ? $a['country'] : '',
                'use' => is_string($a['use'] ?? null) ? $a['use'] : 'home',
            ];
        }
        $data['addresses'] = $addresses;

        return $data;
    }

    /**
     * @param array<string, mixed> $openEmrRecord
     */
    protected function insertOpenEMRRecord($openEmrRecord): ProcessingResult
    {
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
        if ($pid === null) {
            $result = new ProcessingResult();
            $result->setValidationMessages([
                'patient' => 'Patient reference could not be resolved: ' . $puuid,
            ]);
            return $result;
        }
        $openEmrRecord['pid'] = (int) $pid;
        unset($openEmrRecord['puuid']);

        return (new ContactRelationService())->insertRelatedPerson($openEmrRecord);
    }

    /**
     * @param string $fhirResourceId
     * @param array<string, mixed> $updatedOpenEMRRecord
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
        if ($pid === null) {
            $result = new ProcessingResult();
            $result->setValidationMessages([
                'patient' => 'Patient reference could not be resolved: ' . $puuid,
            ]);
            return $result;
        }
        unset($updatedOpenEMRRecord['puuid']);
        return (new ContactRelationService())->updateRelatedPerson(
            $fhirResourceId,
            $updatedOpenEMRRecord,
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
