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
use OpenEMR\FHIR\R4\FHIRElement\FHIRContactPointSystem;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;
use OpenEMR\Services\FacilityService;
use OpenEMR\Services\FHIR\FhirCodeSystemConstants;
use OpenEMR\Services\FHIR\FhirServiceBase;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\FHIR\UtilsService;
use OpenEMR\Services\Search\CompositeSearchField;
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
        if (!isset($openEMRSearchParameters['name'])) {
            // make sure we only return records that have a name or an identifier, otherwise its invalid for FHIR

            $name = new TokenSearchField('name', [new TokenSearchValue(false)]);
            $name->setModifier(SearchModifier::MISSING);
            $domainIdentifier = new TokenSearchField('domain_identifier', [new TokenSearchValue(false)]);
            $domainIdentifier->setModifier(SearchModifier::MISSING);
            $npi = new TokenSearchField('facility_npi', [new TokenSearchValue(false)]);
            $npi->setModifier(SearchModifier::MISSING);
            $openEMRSearchParameters['identifier-name'] = new CompositeSearchField('identifier-name', [], false);
            $openEMRSearchParameters['identifier-name']->setChildren([$name, $domainIdentifier, $npi]);
        }

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

    protected function insertOpenEMRRecord($openEmrRecord)
    {
        $processingResult = $this->facilityService->insert($openEmrRecord);
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
        $organizationResource = new FHIROrganization();

        $fhirMeta = new FHIRMeta();
        $fhirMeta->setVersionId('1');
        $fhirMeta->setLastUpdated(UtilsService::getDateFormattedAsUTC());
        $organizationResource->setMeta($fhirMeta);
        // facilities have no active / inactive state
        $organizationResource->setActive(true);

        $narrativeText = trim($dataRecord['name'] ?? "");
        if (!empty($narrativeText)) {
            $text = array(
                'status' => 'generated',
                'div' => '<div xmlns="http://www.w3.org/1999/xhtml"> <p>' . $narrativeText . '</p></div>'
            );
            $organizationResource->setText($text);
        }

        $id = new FHIRId();
        $id->setValue($dataRecord['uuid']);
        $organizationResource->setId($id);

        $identifiers = [
            'facility_npi' => FhirCodeSystemConstants::PROVIDER_NPI
            ,'domain_identifier' => FhirCodeSystemConstants::OID_CLINICAL_LABORATORY_IMPROVEMENT_ACT_NUMBER
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

        $organizationResource->addType(UtilsService::createCodeableConcept([
            'prov' => [
                'code' => 'prov'
                , 'description' => "Healthcare Provider"
                , 'system' => FhirCodeSystemConstants::HL7_ORGANIZATION_TYPE]
        ]));

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
    public function parseFhirResource(FHIRDomainResource $fhirResource)
    {
        if (!$fhirResource instanceof FHIROrganization) {
            // we use get class to get the sub class type.
            throw new \BadMethodCallException("Resource expected to be of type " . FHIROrganization::class . " but instead was of type " . get_class($fhirResource));
        }

        $data = array();

        $data['uuid'] = (string)$fhirResource->getId() ?? null;
        // convert the strings to a
        $data['name'] = (string)$fhirResource->getName() ?? null;

        $addresses = $fhirResource->getAddress();
        if (!empty($addresses)) {
            $activeAddress = $addresses[0];
            $mostRecentPeriods = UtilsService::getPeriodTimestamps($activeAddress->getPeriod());
            foreach ($fhirResource->getAddress() as $address) {
                $addressPeriod = UtilsService::getPeriodTimestamps($address->getPeriod());
                if (empty($addressPeriod['end'])) {
                    $activeAddress = $address;
                } elseif (!empty($mostRecentPeriods['end']) && $addressPeriod['end'] > $mostRecentPeriods['end']) {
                    // if our current period is more recent than our most recent address we want to grab that one
                    $mostRecentPeriods = $addressPeriod;
                    $activeAddress = $address;
                }
            }

            $lineValues = array_map(function ($val) {
                return (string)$val;
            }, $activeAddress->getLine() ?? []);
            $data['street'] = implode("\n", $lineValues) ?? null;
            $data['postal_code'] = (string)$activeAddress->getPostalCode() ?? null;
            $data['city'] = (string)$activeAddress->getCity() ?? null;
            $data['state'] = (string)$activeAddress->getState() ?? null;
        }

        $telecom = $fhirResource->getTelecom();
        if (!empty($telecom)) {
            foreach ($telecom as $contactPoint) {
                $systemValue = (string)$contactPoint->getSystem() ?? "contact_other";
                $validSystems = ['phone' => 'phone', 'email' => 'email', 'fax' => 'fax'];
                if (isset($validSystems[$systemValue])) {
                    $data[$systemValue] = (string)$contactPoint->getValue() ?? null;
                }
            }
        }

        foreach ($fhirResource->getIdentifier() as $index => $identifier) {
            if ((string)$identifier->getSystem() == FhirCodeSystemConstants::PROVIDER_NPI) {
                $data['facility_npi'] = (string)$identifier->getValue() ?? null;
            }
            if ((string)$identifier->getSystem() == FhirCodeSystemConstants::OID_CLINICAL_LABORATORY_IMPROVEMENT_ACT_NUMBER) {
                $data['domain_identifier'] = (string)$identifier->getValue() ?? null;
            }
        }
        return $data;
    }
}
