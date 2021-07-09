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
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\Services\FHIR\FhirCodeSystemUris;
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
            'cms_id' => FhirCodeSystemUris::HL7_IDENTIFIER_TYPE_TABLE
            ,'alt_cms_id' => FhirCodeSystemUris::HL7_IDENTIFIER_TYPE_TABLE
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
        $organizationResource->addType(UtilsService::createCodeableConcept(['ins' => "Insurance Company"], FhirCodeSystemUris::HL7_ORGANIZATION_TYPE));

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
        $data['orgType'] = 'insurance';

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
            if ($identifier['system'] == FhirCodeSystemUris::HL7_IDENTIFIER_TYPE_TABLE) {
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
