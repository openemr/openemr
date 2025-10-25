<?php

namespace OpenEMR\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRRelatedPerson;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\Services\CodeTypesService;
use OpenEMR\Services\FHIR\Traits\BulkExportSupportAllOperationsTrait;
use OpenEMR\Services\FHIR\Traits\FhirBulkExportDomainResourceTrait;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\FHIR\Traits\VersionedProfileTrait;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Services\Search\TokenSearchValue;
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
        $this->populateMeta($fhirRelatedPerson, $dataRecord);
        $this->populateActive($fhirRelatedPerson, $dataRecord);
        $this->populatePatient($fhirRelatedPerson, $dataRecord);
        $this->populateRelationship($fhirRelatedPerson, $dataRecord);
        $this->populateName($fhirRelatedPerson, $dataRecord);
        $this->populateTelecom($fhirRelatedPerson, $dataRecord);
        $this->populateAddress($fhirRelatedPerson, $dataRecord);
        return $fhirRelatedPerson;
    }

    public function populateActive(FHIRRelatedPerson $fhirRelatedPerson, $dataRecord): void {
        if ('1' === $dataRecord['active']) {
            $fhirRelatedPerson->setActive(true);
        } else {
            $fhirRelatedPerson->setActive(false);
        }
    }

    public function populateMeta(FhirRelatedPerson $fhirRelatedPerson, array $dataRecord): void {
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

    public function populatePatient(FHIRRelatedPerson $fhirRelatedPerson, $dataRecord): void {
        if (!empty($dataRecord['puuid'])) {
            $fhirRelatedPerson->setPatient(UtilsService::createRelativeReference('Patient', $dataRecord['puuid']));
        }
    }

    public function populateRelationship(FHIRRelatedPerson $fhirRelatedPerson, $dataRecord): void {
        $relationshipCode = 'U'; // unknown
        $codeTypesService = new CodeTypesService();

        if (!empty($dataRecord['relationship'])) {
            $concepts = $codeTypesService->parseCodesIntoCodeableConcepts($dataRecord['relationship']);
            $fhirRelatedPerson->addRelationship(UtilsService::createCodeableConcept($concepts));
        }
    }

    public function populateName(FHIRRelatedPerson $fhirRelatedPerson, $dataRecord): void {
        $humanName = UtilsService::createHumanNameFromRecord($dataRecord);
        $fhirRelatedPerson->addName($humanName);
    }

    public function populateTelecom(FHIRRelatedPerson $fhirRelatedPerson, $dataRecord): void {
        $fhirRelatedPerson->addTelecom(UtilsService::createContactPoint($dataRecord['phone_work'], 'phone', 'work'));
        $fhirRelatedPerson->addTelecom(UtilsService::createContactPoint($dataRecord['phone'], 'phone', 'home'));
        $fhirRelatedPerson->addTelecom(UtilsService::createContactPoint($dataRecord['email'], 'email', 'home'));
    }

    public function populateAddress(FHIRRelatedPerson $fhirRelatedPerson, $dataRecord): void {
        foreach ($dataRecord['addresses'] as $address) {
            $fhirRelatedPerson->addAddress(UtilsService::createAddressFromRecord($address));
        }
    }

    /**
     * @param ISearchField[] $openEMRSearchParameters
     * @return ProcessingResult
     */
    protected function searchForOpenEMRRecords($openEMRSearchParameters): ProcessingResult
    {
        // TODO: @adunsulag we will populate these fields once we have the related service pieces working properly.
        $uuid = null;
        $patientUuid = null;
        // going to fake it
        if ($openEMRSearchParameters['_id']) {
            /**
             * @var TokenSearchField $id
             */
            $id = $openEMRSearchParameters['_id'];
            /**
             * @var TokenSearchValue[] $values
             */
            $values = $id->getValues();
            $uuid = $values[0]->getHumanReadableCode();
        }
        if ($openEMRSearchParameters['patient']) {
            /**
             * @var TokenSearchField $patient
             */
            $patient = $openEMRSearchParameters['patient'];
            /**
             * @var TokenSearchValue[] $values
             */
            $values = $patient->getValues();
            $patientUuid = $values[0]->getHumanReadableCode();
        }
        $record = [
            'puuid' => $patientUuid ?? UuidV4::uuid4()->toString(),
            'uuid' => $uuid ?? UuidV4::uuid4()->toString(),
            'active' => '1',
            'relationship_code' => CodeTypesService::CODE_TYPE_HL7_ROLE_CODE . ':FAMMEMB',
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
        $processingResult = new ProcessingResult();
        $processingResult->addData($record);
        return $processingResult;
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

    public function getLastModifiedSearchField(): FhirSearchParameterDefinition {
        return new FhirSearchParameterDefinition('_lastUpdated', SearchFieldType::DATETIME, ['date']);
    }
}
