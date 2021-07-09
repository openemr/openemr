<?php

/**
 * FhirOrganizationFacilityService.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR\Organization;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIROrganization;
use OpenEMR\FHIR\R4\FHIRElement\FHIRAddress;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRContactPoint;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\Services\FacilityService;
use OpenEMR\Services\FHIR\FhirCodeSystemUris;
use OpenEMR\Services\FHIR\FhirServiceBase;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\FHIR\UtilsService;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\SearchModifier;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Services\Search\TokenSearchValue;
use OpenEMR\Services\UserService;
use OpenEMR\Validators\ProcessingResult;

class FhirOrganizationFacilityService extends FhirServiceBase
{
    use FhirServiceBaseEmptyTrait;

    /**
     * @var FacilityService
     */
    private $facilityService;

    public function __construct($fhirApiURL = null)
    {
        parent::__construct($fhirApiURL);
        $this->facilityService = new FacilityService();
    }

    public function getPrimaryBusinessEntityReference()
    {

        $organization = $this->facilityService->getPrimaryBusinessEntity();
        if (!empty($organization)) {
            $fhirOrganization = new FHIROrganization();
            $ref = new FHIRReference();
            $ref->setType($fhirOrganization->get_fhirElementName());
            $uuid = $organization['uuid'];
            $ref->setReference($fhirOrganization->get_fhirElementName() . "/" . $uuid);
            return $ref;
        }
        return null;
    }

    /**
     * Given the uuid of a user assigned to an organization, return a FHIR Reference to the organization record.
     * @param $userUuid The unique user id of the user we are retrieving the reference for.
     * @return FHIRReference|null The reference to the organization the user belongs to
     */
    public function getOrganizationReferenceForUser($userUuid)
    {
        $userService = new UserService();
        $user = $userService->getUserByUUID($userUuid);
        if (!empty($user)) {
            $facilityResult = $this->facilityService->getById($user['facility_id']);
            if (!empty($facilityResult)) {
                $organization = $this->parseOpenEMRRecord($facilityResult);
                $reference = new FHIRReference();
                $reference->setType($organization->get_fhirElementName());
                $reference->setReference(($organization->get_fhirElementName() . "/" . $organization->getId()));
                return $reference;
            }
        }
        return null;
    }

    /**
     * Returns an array mapping FHIR Organization Resource search parameters to OpenEMR Organization search parameters
     *
     * @return array The search parameters
     */
    protected function loadSearchParameters()
    {
        return  [
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, [new ServiceField('uuid', ServiceField::TYPE_UUID)]),
            'email' => new FhirSearchParameterDefinition('email', SearchFieldType::TOKEN, ['email']),
            'phone' => new FhirSearchParameterDefinition('phone', SearchFieldType::TOKEN, ['phone']),
            'telecom' => new FhirSearchParameterDefinition('telecom', SearchFieldType::TOKEN, ['email', 'phone']),
            'address' => new FhirSearchParameterDefinition('address', SearchFieldType::STRING, ["street", "postal_code", "city", "state", "country_code","line1"]),
            'address-city' => new FhirSearchParameterDefinition('address-city', SearchFieldType::STRING, ['city']),
            'address-postalcode' => new FhirSearchParameterDefinition('address-postalcode', SearchFieldType::STRING, ['postal_code', "zip"]),
            'address-state' => new FhirSearchParameterDefinition('address-state', SearchFieldType::STRING, ['state']),
            'name' => new FhirSearchParameterDefinition('name', SearchFieldType::STRING, ['name'])
        ];
    }

    /**
     * Searches for OpenEMR records using OpenEMR search parameters
     * @param openEMRSearchParameters OpenEMR search fields
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @return OpenEMR records
     */
    protected function searchForOpenEMRRecords($openEMRSearchParameters): ProcessingResult
    {
        return $this->facilityService->search($openEMRSearchParameters);
    }

    /**
     * Updates an existing OpenEMR record.
     *
     * @param  $fhirResourceId       //The OpenEMR record's FHIR Resource ID.
     * @param  $updatedOpenEMRRecord //The "updated" OpenEMR record.
     * @return ProcessingResult
     */
    public function updateOpenEMRRecord($fhirResourceId, $updatedOpenEMRRecord)
    {
        $processingResult = $this->facilityService->update($fhirResourceId, $updatedOpenEMRRecord);
        return $processingResult;
    }


    /**
     * Parses an OpenEMR organization record, returning the equivalent FHIR Organization Resource
     *
     * @param  array   $dataRecord The source OpenEMR data record
     * @param  boolean $encode     Indicates if the returned resource is encoded into a string. Defaults to false.
     * @return FHIROrganization
     */
    public function parseOpenEMRRecord($dataRecord = array(), $encode = false)
    {
        if (
            (empty($dataRecord['cms_id']) || empty($dataRecord['domain_identifier']))
            && empty($dataRecord['name'])
        ) {
            // TODO: @adunsulag we need to architect what happens if we fail the organization constraint requirements
            // ie there MUST be a name OR an identifier
            // @see http://hl7.org/fhir/us/core/STU3.1.1/StructureDefinition-us-core-organization.html#summary-of-constraints
        }
        $organizationResource = new FHIROrganization();

        $fhirMeta = new FHIRMeta();
        $fhirMeta->setVersionId('1');
        $fhirMeta->setLastUpdated(gmdate('c'));
        $organizationResource->setMeta($fhirMeta);
        $organizationResource->setActive($dataRecord['active'] === true);

        $narrativeText = '';
        if (isset($dataRecord['name'])) {
            $narrativeText = $dataRecord['name'];
        }
        $text = array(
            'status' => 'generated',
            'div' => '<div xmlns="http://www.w3.org/1999/xhtml"> <p>' . $narrativeText . '</p></div>'
        );
        $organizationResource->setText($text);

        $id = new FHIRId();
        $id->setValue($dataRecord['uuid']);
        $organizationResource->setId($id);

        $identifiers = [
            'facility_npi' => FhirCodeSystemUris::PROVIDER_NPI
            ,'cms_id' => FhirCodeSystemUris::HL7_IDENTIFIER_TYPE_TABLE
            ,'domain_identifier' => FhirCodeSystemUris::OID_CLINICAL_LABORATORY_IMPROVEMENT_ACT_NUMBER
        ];
        foreach ($identifiers as $id => $system) {
            if (!empty($dataRecord[$id])) {
                $identifier = new FHIRIdentifier();
                $identifier->setSystem($system);
                $identifier->setValue($dataRecord[$id]);
                $organizationResource->addIdentifier($identifier);
            }
        }

        if (isset($dataRecord['name'])) {
            $organizationResource->setName($dataRecord['name']);
        } else {
//             if the name is missing we HAVE to have this
            $organizationResource->setName(UtilsService::createDataMissingExtension());
        }

        $organizationResource->addAddress(UtilsService::createAddressFromRecord($dataRecord));

        $contactPoints = ['phone', 'email', 'fax'];
        foreach ($contactPoints as $contact) {
            if (!empty($dataRecord[$contact])) {
                $organizationResource->addTelecom(UtilsService::createContactPoint(
                    $dataRecord[$contact],
                    $contact,
                    'work'
                ));
            }
        }

        $organizationResource->addType(UtilsService::createCodeableConcept(['prov' => "Healthcare Provider"], FhirCodeSystemUris::HL7_ORGANIZATION_TYPE));

        if ($encode) {
            return json_encode($organizationResource);
        } else {
            return $organizationResource;
        }
    }

    /**
     * Parses a FHIR Organization Resource, returning the equivalent OpenEMR organization record.
     *
     * @param  array $fhirResource The source FHIR resource
     * @return array a mapped OpenEMR data record (array)
     */
    public function parseFhirResource($fhirResource = array())
    {
        $data = array();

        if (isset($fhirResource['id'])) {
            $data['uuid'] = $fhirResource['id'];
        }

        if (isset($fhirResource['name'])) {
            $data['name'] = $fhirResource['name'];
        }

        if (isset($fhirResource['address'])) {
            if (isset($fhirResource['address'][0]['line'][0])) {
                $data['street'] = $fhirResource['address'][0]['line'][0];
            }
            if (isset($fhirResource['address'][0]['postalCode'][0])) {
                $data['postal_code'] = $fhirResource['address'][0]['postalCode'];
            }
            if (isset($fhirResource['address'][0]['city'][0])) {
                $data['city'] = $fhirResource['address'][0]['city'];
            }
            if (isset($fhirResource['address'][0]['state'][0])) {
                $data['state'] = $fhirResource['address'][0]['state'];
            }
        }
        //setting default OrgType to facility
        $data['orgType'] = 'facility';
        if (isset($fhirResource['type'])) {
            foreach ($fhirResource['type'] as $orgtype) {
                foreach ($orgtype['coding'] as $coding) {
                    if ($coding['code'] == 'pay') {
                        $data['orgType'] = 'insurance';
                    }
                    if ($coding['code'] == 'prov') {
                        $data['orgType'] = 'facility';
                    }
                }
            }
        }
        if (isset($fhirResource['telecom'])) {
            foreach ($fhirResource['telecom'] as $telecom) {
                switch ($telecom['system']) {
                    case 'phone':
                        switch ($telecom['use']) {
                            case 'work':
                                $data['phone'] = $telecom['value'];
                                break;
                        }
                        break;
                    case 'email':
                        switch ($telecom['use']) {
                            case 'work':
                                $data['email'] = $telecom['value'];
                                break;
                        }
                        break;
                    case 'fax':
                        switch ($telecom['use']) {
                            case 'work':
                                $data['fax'] = $telecom['value'];
                                break;
                        }
                        break;
                    default:
                        //Should give Error for incapability
                        break;
                }
            }
        }

        foreach ($fhirResource['identifier'] as $index => $identifier) {
            if ($identifier['system'] == FhirCodeSystemUris::PROVIDER_NPI) {
                $data['facility_npi'] = $identifier['value'];
            }
            if ($identifier['system'] == FhirCodeSystemUris::OID_CLINICAL_LABORATORY_IMPROVEMENT_ACT_NUMBER) {
                $data['domain_identifier'] = $identifier['value'];
            }
        }
        return $data;
    }
}
