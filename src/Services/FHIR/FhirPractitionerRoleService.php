<?php

namespace OpenEMR\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPractitionerRole;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
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
 * @link      http://www.open-emr.org
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
            'practitioner' => new FhirSearchParameterDefinition('practitioner', SearchFieldType::STRING, ['user_name']),
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
     * @param boolean $encode Indicates if the returned resource is encoded into a string. Defaults to false.
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
