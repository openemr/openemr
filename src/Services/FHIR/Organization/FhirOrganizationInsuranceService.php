<?php

/**
 * FhirOrganizationInsuranceService.php
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
use OpenEMR\Services\FHIR\FhirCodeSystemConstants;
use OpenEMR\Services\FHIR\FhirServiceBase;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\FHIR\UtilsService;
use OpenEMR\Services\InsuranceCompanyService;
use OpenEMR\Services\Search\CompositeSearchField;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\SearchModifier;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Services\Search\StringSearchField;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Services\Search\TokenSearchValue;
use OpenEMR\Validators\ProcessingResult;

class FhirOrganizationInsuranceService extends FhirServiceBase
{
    use FhirServiceBaseEmptyTrait;

    /**
     * @var InsuranceCompanyService
     */
    private $insuranceService;

    public function __construct($fhirApiURL = null)
    {
        parent::__construct($fhirApiURL);
        $this->insuranceService = new InsuranceCompanyService();
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
            'address' => new FhirSearchParameterDefinition('address', SearchFieldType::STRING, ["street", "postal_code", "city", "state", "country","line1", "line2"]),
            'address-city' => new FhirSearchParameterDefinition('address-city', SearchFieldType::STRING, ['city']),
            'address-postalcode' => new FhirSearchParameterDefinition('address-postalcode', SearchFieldType::STRING, ["zip"]),
            'address-state' => new FhirSearchParameterDefinition('address-state', SearchFieldType::STRING, ['state']),
            'name' => new FhirSearchParameterDefinition('name', SearchFieldType::STRING, ['name'])
        ];
    }

    protected function searchForOpenEMRRecords($openEMRSearchParameters): ProcessingResult
    {
        if (!isset($openEMRSearchParameters['name'])) {
            // make sure we only return records that have a name or an identifier, otherwise its invalid for FHIR

            $name = new TokenSearchField('name', [new TokenSearchValue(false)]);
            $name->setModifier(SearchModifier::MISSING);
            $cms = new TokenSearchField('cms_id', [new TokenSearchValue(false)]);
            $cms->setModifier(SearchModifier::MISSING);
            $cmsAlt = new TokenSearchField('alt_cms_id', [new TokenSearchValue(false)]);
            $cmsAlt->setModifier(SearchModifier::MISSING);
            $openEMRSearchParameters['identifier-name'] = new CompositeSearchField('identifier-name', [], false);
            $openEMRSearchParameters['identifier-name']->setChildren([$name, $cms, $cmsAlt]);
        }
        return $this->insuranceService->search($openEMRSearchParameters);
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
        $fhirMeta->setLastUpdated(gmdate('c'));
        $organizationResource->setMeta($fhirMeta);
        $organizationResource->setActive($dataRecord['inactive'] == '0');

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
            'cms_id' => FhirCodeSystemConstants::HL7_IDENTIFIER_TYPE_TABLE
            ,'alt_cms_id' => FhirCodeSystemConstants::HL7_IDENTIFIER_TYPE_TABLE
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

        $contactPoints = ['work_number' => 'work', 'fax_number' => 'fax'];
        foreach ($contactPoints as $field => $contact) {
            if (!empty($dataRecord[$field])) {
                $organizationResource->addTelecom(UtilsService::createContactPoint(
                    $dataRecord[$field],
                    $contact,
                    'work'
                ));
            }
        }

        $organizationResource->addType(UtilsService::createCodeableConcept(['ins' => [
            'code' => 'ins', 'description' => "Insurance Company"
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
    public function parseFhirResource($fhirResource = array())
    {
        if (!$fhirResource instanceof FHIROrganization) {
            // we use get class to get the sub class type.
            throw new \BadMethodCallException("Resource expected to be of type " . FHIROrganization::class . " but instead was of type " . get_class($fhirResource));
        }

        $data = array();

        $data['uuid'] = $fhirResource->getId() ?? null;
        $data['name'] = !empty($fhirResource->getName()) ? $fhirResource->getName()->getValue() : null;

        $addresses = $fhirResource->getAddress();
        if (!empty($addresses)) {
            $activeAddress = $addresses[0];
            $mostRecentPeriods = UtilsService::getPeriodTimestamps($activeAddress->getPeriod());
            foreach ($fhirResource->getAddress() as $address) {
                $addressPeriod = UtilsService::getPeriodTimestamps($address->getPeriod());
                if (empty($addressPeriod['end'])) {
                    $activeAddress = $address;
                } else if (!empty($mostRecentPeriods['end']) && $addressPeriod['end'] > $mostRecentPeriods['end']) {
                    // if our current period is more recent than our most recent address we want to grab that one
                    $mostRecentPeriods = $addressPeriod;
                    $activeAddress = $address;
                }
            }

            $lineValues = array_map(function ($val) {
                return $val->getValue();
            }, $activeAddress->getLine() ?? []);
            $data['street'] = implode("\n", $lineValues) ?? null;
            $data['postal_code'] = !empty($activeAddress->getPostalCode()) ? $activeAddress->getPostalCode()->getValue() : null;
            $data['city'] = !empty($activeAddress->getCity()) ? $activeAddress->getCity()->getValue() : null;
            $data['state'] = !empty($activeAddress->getState()) ? $activeAddress->getState()->getValue() : null;
        }

        foreach ($fhirResource['identifier'] as $index => $identifier) {
            if ($identifier['system'] == FhirCodeSystemConstants::HL7_IDENTIFIER_TYPE_TABLE) {
                if (empty($data['cms_id'])) {
                    $data['cms_id'] = $identifier['value'];
                } else {
                    $data['alt_cms_id'] = $identifier['value'];
                }
            }
        }
        return $data;
    }
}
