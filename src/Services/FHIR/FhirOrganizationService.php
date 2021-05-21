<?php

namespace OpenEMR\Services\FHIR;

use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIROrganization;
use OpenEMR\FHIR\R4\FHIRElement\FHIRAddress;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\Services\FacilityService;
use OpenEMR\Services\OrganizationService;
use OpenEMR\Services\PractitionerService;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Services\Search\TokenSearchValue;
use OpenEMR\Services\UserService;
use OpenEMR\Validators\ProcessingResult;

/**
 * FHIR Organization Service
 *
 * @coversDefaultClass OpenEMR\Services\FHIR\FhirOrganizationService
 * @package            OpenEMR
 * @link               http://www.open-emr.org
 * @author             Yash Bothra <yashrajbothra786@gmail.com>
 * @copyright          Copyright (c) 2020 Yash Bothra <yashrajbothra786@gmail.com>
 * @license            https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class FhirOrganizationService extends FhirServiceBase
{
    /**
     * @var OrganizationService
     */
    private $organizationService;

    public function __construct()
    {
        parent::__construct();
        $this->organizationService = new OrganizationService();
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

        $meta = array('versionId' => '1', 'lastUpdated' => gmdate('c'));
        $organizationResource->setMeta($meta);

        $organizationResource->setActive($dataRecord['service_location'] == "1" ? true : false);

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

        if (isset($dataRecord['name'])) {
            $organizationResource->setName($dataRecord['name']);
        } else {
//             if the name is missing we HAVE to have this
            $organizationResource->setName(UtilsService::createDataMissingExtension());
        }

        $address = new FHIRAddress();
        if (!empty($dataRecord['line1'])) {
            $address->addLine($dataRecord['line1']);
        }
        if (!empty($dataRecord['line2'])) {
            $address->addLine($dataRecord['line2']);
        }
        if (!empty($dataRecord['city'])) {
            $address->setCity($dataRecord['city']);
        }
        if (!empty($dataRecord['state'])) {
            $address->setState($dataRecord['state']);
        }
        if (!empty($dataRecord['postal_code'])) {
            $address->setPostalCode($dataRecord['postal_code']);
        }
        if (!empty($dataRecord['country_code'])) {
            $address->setCountry($dataRecord['country_code']);
        }

        $organizationResource->addAddress($address);

        if (!empty($dataRecord['phone'])) {
            $organizationResource->addTelecom(
                array(
                'system' => 'phone',
                'value' => $dataRecord['phone'],
                'use' => 'work'
                )
            );
        }

        if (!empty($dataRecord['email'])) {
            $organizationResource->addTelecom(
                array(
                'system' => 'email',
                'value' => $dataRecord['email'],
                'use' => 'work'
                )
            );
        }

        if (!empty($dataRecord['orgType'])) {
            $orgType = new FHIRCodeableConcept();
            $type = new FHIRCoding();
            $type->setSystem("http://terminology.hl7.org/CodeSystem/organization-type");
            if ($dataRecord['orgType'] == 'facility') {
                $type->setCode("prov");
            }
            if ($dataRecord['orgType'] == 'insurance') {
                $type->setCode("pay");
            }
            $orgType->addCoding($type);
            $organizationResource->addType($orgType);
        }

        if (!empty($dataRecord['fax'])) {
            $organizationResource->addTelecom(
                array(
                'system' => 'fax',
                'value' => $dataRecord['fax'],
                'use' => 'work'
                )
            );
        }

        if (!empty($dataRecord['facility_npi'])) {
            $fhirIdentifier = [
                'system' => "http://hl7.org/fhir/sid/us-npi",
                'value' => $dataRecord['facility_npi']
            ];
            $organizationResource->addIdentifier($fhirIdentifier);
        }

        if (!empty($dataRecord['cms_id'])) {
            $fhirIdentifier = [
                'system' => "http://hl7.org/fhir/v2/0203",
                'value' => $dataRecord['cms_id']
            ];
            $organizationResource->addIdentifier($fhirIdentifier);
        }

        if (!empty($dataRecord['domain_identifier'])) {
            $fhirIdentifier = [
                'system' => "urn:oid:2.16.840.1.113883.4.7",
                'value' => $dataRecord['domain_identifier']
            ];
            $organizationResource->addIdentifier($fhirIdentifier);
        }

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
            if ($identifier['system'] == "http://hl7.org/fhir/sid/us-npi") {
                $data['facility_npi'] = $identifier['value'];
            }
            if ($identifier['system'] == "urn:oid:2.16.840.1.113883.4.7") {
                $data['domain_identifier'] = $identifier['value'];
            }
        }
        return $data;
    }


    /**
     * Inserts an OpenEMR record into the sytem.
     *
     * @param  array $openEmrRecord OpenEMR organization record
     * @return ProcessingResult
     */
    public function insertOpenEMRRecord($openEmrRecord)
    {
        return $this->organizationService->insert($openEmrRecord);
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
        $processingResult = $this->organizationService->update($fhirResourceId, $updatedOpenEMRRecord);
        return $processingResult;
    }

    /**
     * Searches for OpenEMR records using OpenEMR search parameters
     *
     * @param  array openEMRSearchParameters OpenEMR search fields
     * @param $puuidBind - NOT USED
     * @return ProcessingResult
     */
    public function searchForOpenEMRRecords($openEMRSearchParameters, $puuidBind = null)
    {
        $processingResult = $this->organizationService->search($openEMRSearchParameters);
        return $processingResult;
    }
    public function createProvenanceResource($dataRecord = array(), $encode = false)
    {
        // TODO: If Required in Future
    }

    public function getPrimaryBusinessEntityReference()
    {
        $organization = $this->organizationService->getPrimaryBusinessEntity();
        if (!empty($organization)) {
            $fhirOrganization = new FHIROrganization();
            $ref = new FHIRReference();
            $ref->setType($fhirOrganization->get_fhirElementName());
            $uuid = UuidRegistry::uuidToString($organization['uuid']);
            $ref->setReference("Organization/" . $uuid);
            $ref->setId($uuid);
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
            $organization = $this->organizationService->getFacilityOrganizationById($user['facility_id']);
            if (!empty($organization)) {
                $reference = new FHIRReference();
                $fhirOrganization = new FHIROrganization();
                $reference->setType($fhirOrganization->get_fhirElementName());
                $reference->setReference(($fhirOrganization->get_fhirElementName() . $organization['uuid']));
                $reference->setId($organization['id']);
                return $reference;
            }
        }
        return null;
    }
}
