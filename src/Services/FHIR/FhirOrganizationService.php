<?php

namespace OpenEMR\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIROrganization;
use OpenEMR\FHIR\R4\FHIRElement\FHIRAddress;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\Services\FacilityService;

/**
 * FHIR Organization Service
 *
 * @coversDefaultClass OpenEMR\Services\FHIR\FhirOrganizationService
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786@gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */
class FhirOrganizationService extends FhirServiceBase
{
    /**
     * @var FacilityService
     */
    private $organizationService;

    public function __construct()
    {
        parent::__construct();
        $this->organizationService = new FacilityService();
    }

    /**
     * Returns an array mapping FHIR Organization Resource search parameters to OpenEMR Organization search parameters
     * @return array The search parameters
     */
    protected function loadSearchParameters()
    {
        return  [
            "email" => ["email"],
            "phone" => ["phone"],
            "telecom" => ["email", "phone",],
            "address" => ["street", "postal_code", "city", "state", "country_code"],
            "address-city" => ["city"],
            "address-postalcode" => ["postal_code"],
            "address-state" => ["state"],
            "name" => ["name"],
            "active" => ["service_location"]
        ];
    }

    /**
     * Parses an OpenEMR organization record, returning the equivalent FHIR Organization Resource
     *
     * @param array $dataRecord The source OpenEMR data record
     * @param boolean $encode Indicates if the returned resource is encoded into a string. Defaults to false.
     * @return FHIROrganization
     */
    public function parseOpenEMRRecord($dataRecord = array(), $encode = false)
    {
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
        }

        $address = new FHIRAddress();
        if (!empty($dataRecord['street'])) {
            $address->addLine($dataRecord['street']);
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
        if (isset($dataRecord['country_code'])) {
            $address->setCountry($dataRecord['country_code']);
        }

        $organizationResource->addAddress($address);

        if (!empty($dataRecord['phone'])) {
            $organizationResource->addTelecom(array(
                'system' => 'phone',
                'value' => $dataRecord['phone'],
                'use' => 'work'
            ));
        }

        if (isset($dataRecord['email'])) {
            $organizationResource->addTelecom(array(
                'system' => 'email',
                'value' => $dataRecord['email'],
                'use' => 'work'
            ));
        }

        if (isset($dataRecord['fax'])) {
            $organizationResource->addTelecom(array(
                'system' => 'fax',
                'value' => $dataRecord['fax'],
                'use' => 'work'
            ));
        }

        if (isset($dataRecord['facility_npi'])) {
            $fhirIdentifier = [
                'system' => "http://hl7.org/fhir/sid/us-npi",
                'value' => $dataRecord['facility_npi']
            ];
            $organizationResource->addIdentifier($fhirIdentifier);
        }

        if (isset($dataRecord['domain_identifier'])) {
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
     * @param array $fhirResource The source FHIR resource
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
     * @param array $openEmrRecord OpenEMR organization record
     * @return ProcessingResult
     */
    public function insertOpenEMRRecord($openEmrRecord)
    {
        return $this->organizationService->insert($openEmrRecord);
    }


    /**
     * Updates an existing OpenEMR record.
     *
     * @param $fhirResourceId //The OpenEMR record's FHIR Resource ID.
     * @param $updatedOpenEMRRecord //The "updated" OpenEMR record.
     * @return ProcessingResult
     */
    public function updateOpenEMRRecord($fhirResourceId, $updatedOpenEMRRecord)
    {
        $processingResult = $this->organizationService->update($fhirResourceId, $updatedOpenEMRRecord);
        return $processingResult;
    }

    /**
     * Performs a FHIR Organization Resource lookup by FHIR Resource ID
     * @param $fhirResourceId //The OpenEMR record's FHIR Organization Resource ID.
     */
    public function getOne($fhirResourceId)
    {
        $processingResult = $this->organizationService->getOne($fhirResourceId);
        if (!$processingResult->hasErrors()) {
            if (count($processingResult->getData()) > 0) {
                $openEmrRecord = $processingResult->getData()[0];
                $fhirRecord = $this->parseOpenEMRRecord($openEmrRecord);
                $processingResult->setData([]);
                $processingResult->addData($fhirRecord);
            }
        }
        return $processingResult;
    }

    /**
     * Searches for OpenEMR records using OpenEMR search parameters
     *
     * @param array openEMRSearchParameters OpenEMR search fields
     * @return ProcessingResult
     */
    public function searchForOpenEMRRecords($openEMRSearchParameters)
    {
        return $this->organizationService->getAll($openEMRSearchParameters, false);
    }
    public function createProvenanceResource($dataRecord = array(), $encode = false)
    {
        // TODO: If Required in Future
    }
}
