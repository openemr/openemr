<?php

namespace OpenEMR\Services\FHIR;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPractitionerRole;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\FHIR\Traits\VersionedProfileTrait;
use OpenEMR\Services\PractitionerRoleService;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Validators\ProcessingResult;

/**
 * FHIR PractitionerRole Service
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786@gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class FhirPractitionerRoleService extends FhirServiceBase implements IResourceUSCIGProfileService
{
    use VersionedProfileTrait;
    use FhirServiceBaseEmptyTrait;

    const USCGI_PROFILE_URI = 'http://hl7.org/fhir/us/core/StructureDefinition/us-core-practitionerrole';

    /**
     * @var PractitionerRoleService
     */
    private $practitionerRoleService;

    public function __construct()
    {
        parent::__construct();
        $this->practitionerRoleService = new PractitionerRoleService();
    }

    /**
     * Returns an array mapping FHIR PractitionerRole Resource search parameters to OpenEMR PractitionerRole search parameters
     * @return array The search parameters
     */
    protected function loadSearchParameters()
    {
        return  [
            'specialty' => new FhirSearchParameterDefinition('specialty', SearchFieldType::TOKEN, ['specialty_code']),
            'practitioner' => new FhirSearchParameterDefinition('practitioner', SearchFieldType::REFERENCE, [new ServiceField('provider_uuid', ServiceField::TYPE_UUID)]),
            '_id' => new FhirSearchParameterDefinition(
                '_id',
                SearchFieldType::TOKEN,
                [new ServiceField('providers.facility_role_uuid', ServiceField::TYPE_UUID)]
            ),
            '_lastUpdated' => $this->getLastModifiedSearchField()
        ];
    }

    public function getLastModifiedSearchField(): ?FhirSearchParameterDefinition
    {
        // we just go off of role as specialty gets updated at the same time
        return new FhirSearchParameterDefinition('_lastUpdated', SearchFieldType::DATETIME, ['role_last_updated']);
    }

    /**
     * Parses an OpenEMR practitionerRole record, returning the equivalent FHIR PractitionerRole Resource
     *
     * @param array $dataRecord The source OpenEMR data record
     * @param bool $encode Indicates if the returned resource is encoded into a string. Defaults to false.
     * @return FHIRPractitionerRole
     */
    public function parseOpenEMRRecord($dataRecord = [], $encode = false)
    {
        $practitionerRoleResource = new FHIRPractitionerRole();

        $meta = new FHIRMeta();
        $meta->setVersionId('1');
        if (!empty($dataRecord['role_last_updated'])) {
            $meta->setLastUpdated(UtilsService::getLocalDateAsUTC($dataRecord['role_last_updated']));
        } else {
            $meta->setLastUpdated(UtilsService::getDateFormattedAsUTC());
        }
        $practitionerRoleResource->setMeta($meta);

        $id = new FHIRId();
        $id->setValue($dataRecord['uuid']);
        $practitionerRoleResource->setId($id);

        if (!empty($dataRecord['provider_uuid'])) {
            $practitioner = new FHIRReference(
                [
                    'reference' => 'Practitioner/' . $dataRecord['provider_uuid'],
                    'display' => $dataRecord['user_name']
                ]
            );
            $practitionerRoleResource->setPractitioner($practitioner);
        }

        if (!empty($dataRecord['facility_uuid'])) {
            $organization = new FHIRReference([
                'reference' => 'Organization/' . $dataRecord['facility_uuid'],
                'display' => $dataRecord['facility_name']
            ]);
            $practitionerRoleResource->setOrganization($organization);
        }


        if (!empty($dataRecord['role_code'])) {
            $reason = new FHIRCodeableConcept();
            $reason->addCoding($dataRecord['role_code']);
            $reason->setText($dataRecord['role_title']);
            $practitionerRoleResource->addCode($reason);
        }

        if (!empty($dataRecord['specialty_code'])) {
            $reason = new FHIRCodeableConcept();
            $reason->addCoding($dataRecord['specialty_code']);
            $reason->setText($dataRecord['specialty_title']);
            $practitionerRoleResource->addCode($reason);
        }

        if (!empty($dataRecord['location_uuid'])) {
            $practitionerRoleResource->addLocation(UtilsService::createRelativeReference("Location", $dataRecord['location_uuid']));
        }
        // now let's handle the telecom pieces
        $telecoms = ['work_phone', 'fax', 'email', 'url'];
        foreach ($telecoms as $telecom) {
            if (!empty($dataRecord[$telecom])) {
                $practitionerRoleResource->addTelecom(UtilsService::createContactPoint($dataRecord[$telecom], $dataRecord[$telecom . '_system'], $dataRecord[$telecom . '_use']));
            }
        }

        if ($encode) {
            return json_encode($practitionerRoleResource);
        } else {
            return $practitionerRoleResource;
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
        return $this->practitionerRoleService->search($openEMRSearchParameters);
    }

    /**
     * Parses a FHIR PractitionerRole resource. Practitioner and Organization references
     * are required (resolved to numeric ids in insertOpenEMRRecord). For code/specialty,
     * we look for the first coding whose `code` exists in OpenEMR's list_options under
     * `us-core-provider-role` / `us-core-provider-specialty` respectively; without a
     * direct match, the raw code string is passed through and the validator will reject
     * it at insert time.
     *
     * @param FHIRDomainResource $fhirResource
     * @return array<string, mixed>
     */
    public function parseFhirResource(FHIRDomainResource $fhirResource)
    {
        if (!($fhirResource instanceof FHIRPractitionerRole)) {
            throw new \InvalidArgumentException(
                'Expected FHIRPractitionerRole resource, got ' . $fhirResource::class
            );
        }

        $json = $fhirResource->jsonSerialize();
        $data = [];

        if (!empty($json['id']) && is_string($json['id'])) {
            $data['uuid'] = $json['id'];
        }

        $practitionerRef = $json['practitioner']['reference'] ?? null;
        if (is_string($practitionerRef) && $practitionerRef !== '') {
            $parsed = UtilsService::parseReferenceString($practitionerRef, 'Practitioner');
            if (!empty($parsed['uuid']) && UuidRegistry::isValidStringUUID($parsed['uuid'])) {
                $data['provider_uuid'] = $parsed['uuid'];
            }
        }

        $organizationRef = $json['organization']['reference'] ?? null;
        if (is_string($organizationRef) && $organizationRef !== '') {
            $parsed = UtilsService::parseReferenceString($organizationRef, 'Organization');
            if (!empty($parsed['uuid']) && UuidRegistry::isValidStringUUID($parsed['uuid'])) {
                $data['facility_uuid'] = $parsed['uuid'];
            }
        }

        // code[0].coding[0].code -> role_code
        $codeCoding = $json['code'][0]['coding'][0]['code'] ?? null;
        if (is_string($codeCoding) && $codeCoding !== '') {
            $data['role_code'] = $codeCoding;
        }

        // specialty[0].coding[0].code -> specialty_code
        $specialtyCoding = $json['specialty'][0]['coding'][0]['code'] ?? null;
        if (is_string($specialtyCoding) && $specialtyCoding !== '') {
            $data['specialty_code'] = $specialtyCoding;
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $openEmrRecord
     */
    protected function insertOpenEMRRecord($openEmrRecord): ProcessingResult
    {
        $practitionerUuid = $openEmrRecord['provider_uuid'] ?? null;
        if (!is_string($practitionerUuid) || $practitionerUuid === '') {
            $result = new ProcessingResult();
            $result->setValidationMessages(['practitioner' => 'FHIR PractitionerRole requires a Practitioner reference']);
            return $result;
        }
        $providerId = QueryUtils::fetchSingleValue(
            'SELECT id FROM users WHERE uuid = ?',
            'id',
            [UuidRegistry::uuidToBytes($practitionerUuid)]
        );
        if ($providerId === null) {
            $result = new ProcessingResult();
            $result->setValidationMessages(['practitioner' => 'Practitioner reference could not be resolved: ' . $practitionerUuid]);
            return $result;
        }

        $facilityUuid = $openEmrRecord['facility_uuid'] ?? null;
        if (!is_string($facilityUuid) || $facilityUuid === '') {
            $result = new ProcessingResult();
            $result->setValidationMessages(['organization' => 'FHIR PractitionerRole requires an Organization reference']);
            return $result;
        }
        $facilityId = QueryUtils::fetchSingleValue(
            'SELECT id FROM facility WHERE uuid = ?',
            'id',
            [UuidRegistry::uuidToBytes($facilityUuid)]
        );
        if ($facilityId === null) {
            $result = new ProcessingResult();
            $result->setValidationMessages(['organization' => 'Organization reference could not be resolved: ' . $facilityUuid]);
            return $result;
        }

        $openEmrRecord['provider_id'] = (int) $providerId;
        $openEmrRecord['facility_id'] = (int) $facilityId;
        unset($openEmrRecord['provider_uuid'], $openEmrRecord['facility_uuid']);

        return $this->practitionerRoleService->insert($openEmrRecord);
    }

    /**
     * @param string $fhirResourceId
     * @param array<string, mixed> $updatedOpenEMRRecord
     */
    protected function updateOpenEMRRecord($fhirResourceId, $updatedOpenEMRRecord): ProcessingResult
    {
        // FHIR PUT cannot rebind the practitioner or organization; drop those uuids from
        // the update payload to keep PractitionerRoleService::update focused on role/specialty.
        unset($updatedOpenEMRRecord['provider_uuid'], $updatedOpenEMRRecord['facility_uuid']);
        return $this->practitionerRoleService->update($fhirResourceId, $updatedOpenEMRRecord);
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
}
