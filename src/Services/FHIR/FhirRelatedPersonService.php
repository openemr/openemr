<?php

namespace OpenEMR\Services\FHIR;

use Mi2\Framework\ListOptions;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRRelatedPerson;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\Services\CodeTypesService;
use OpenEMR\Services\FHIR\Traits\BulkExportSupportAllOperationsTrait;
use OpenEMR\Services\FHIR\Traits\FhirBulkExportDomainResourceTrait;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\FHIR\Traits\VersionedProfileTrait;
use OpenEMR\Services\ListService;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Services\Search\TokenSearchValue;
use OpenEMR\Tests\Certification\HIT1\G10_Certification\SinglePatient700APITest;
use OpenEMR\Tests\Certification\HIT1\G10_Certification\Trait\G10ApiTestTrait;
use OpenEMR\Validators\ProcessingResult;
use Ramsey\Uuid\Rfc4122\UuidV4;

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
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, [new ServiceField('uuid', ServiceField::TYPE_UUID)]),
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
        if ('1' === $dataRecord['active']) {
            $fhirRelatedPerson->setActive(true);
        } else {
            $fhirRelatedPerson->setActive(false);
        }
    }

    public function populateMeta(FhirRelatedPerson $fhirRelatedPerson, array $dataRecord): void
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
        $relationshipCode = 'U'; // unknown
        $description = 'Unknown';


        if (!empty($dataRecord['relationship_code'])) {
            $relationshipCode = $dataRecord['relationship_code'];
            $listOptionsService = new ListService();
            $option = $listOptionsService->getListOption('personal_relationship', $relationshipCode);
            $description = $option['title'] ?? $description;
        }
        $concept = UtilsService::createCodeableConcept(
            [$relationshipCode => [
            'code' => $relationshipCode,
            'system' => FhirCodeSystemConstants::HL7_ROLE_CODE,
            'description' => $description
            ]
            ]
        );
        $fhirRelatedPerson->addRelationship($concept);
    }

    public function populateName(FHIRRelatedPerson $fhirRelatedPerson, $dataRecord): void
    {
        $humanName = UtilsService::createHumanNameFromRecord($dataRecord);
        $fhirRelatedPerson->addName($humanName);
    }

    public function populateTelecom(FHIRRelatedPerson $fhirRelatedPerson, $dataRecord): void
    {
        $fhirRelatedPerson->addTelecom(UtilsService::createContactPoint($dataRecord['phone_work'], 'phone', 'work'));
        $fhirRelatedPerson->addTelecom(UtilsService::createContactPoint($dataRecord['phone'], 'phone', 'home'));
        $fhirRelatedPerson->addTelecom(UtilsService::createContactPoint($dataRecord['email'], 'email', 'home'));
    }

    public function populateAddress(FHIRRelatedPerson $fhirRelatedPerson, $dataRecord): void
    {
        foreach ($dataRecord['addresses'] as $address) {
            $fhirRelatedPerson->addAddress(UtilsService::createAddressFromRecord($address));
        }
    }

    /**
     * @param  ISearchField[] $openEMRSearchParameters
     * @return ProcessingResult
     */
    protected function searchForOpenEMRRecords($openEMRSearchParameters): ProcessingResult
    {
        // TODO: @adunsulag we will populate these fields once we have the related service pieces working properly.
        $uuid = null;
        $patientUuid = null;
        // going to fake it
        if ($openEMRSearchParameters['uuid']) {
            /**
             * @var TokenSearchField $id
             */
            $id = $openEMRSearchParameters['uuid'];
            /**
             * @var TokenSearchValue[] $values
             */
            $values = $id->getValues();
            $uuid = $values[0]->getHumanReadableCode();
        }
        if ($openEMRSearchParameters['puuid']) {
            /**
             * @var TokenSearchField $patient
             */
            $patient = $openEMRSearchParameters['puuid'];
            /**
             * @var TokenSearchValue[] $values
             */
            $values = $patient->getValues();
            $patientUuid = $values[0]->getHumanReadableCode();
        }
        $record = $this->getSampleRelatedPerson($patientUuid, $uuid);

        $processingResult = new ProcessingResult();
        $processingResult->addData($record);
        return $processingResult;
    }

    public function getSampleRelatedPerson(?string $patientUuid, ?string $uuid)
    {
        $record = [
            'puuid' => $patientUuid ?? SinglePatient700APITest::PATIENT_ID_PRIMARY,
            'uuid' => $uuid ?? UuidV4::uuid4()->toString(),
            'active' => '1',
            'relationship_code' => 'FAMMEMB',
            'fname' => 'John',
            'lname' => 'Doe',
            'email' => 'example@open-emr.org',
            'phone_work' => '(555) 555-5555',
            'phone' => '(333) 333-3333',
            'last_updated' => date(DATE_ATOM),
            'addresses' => [
                [
                    'street' => '123 example street',
                    'city' => 'Somewhere',
                    'state' => 'CA',
                    'country' => 'US',
                    'zipcode' => '12345'
                ]
            ]
        ];
        return $record;
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
        return new FhirSearchParameterDefinition('_lastUpdated', SearchFieldType::DATETIME, ['date']);
    }
}
