<?php

namespace OpenEMR\Services\FHIR;

use OpenEMR\Services\FHIR\FhirServiceBase;
use OpenEMR\Services\PractitionerService;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPractitioner;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRHumanName;
use OpenEMR\FHIR\R4\FHIRElement\FHIRAddress;

/**
 * FHIR Practitioner Service
 *
 * @coversDefaultClass OpenEMR\Services\FHIR\FhirPractitionerService
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Yash Bothra <yashrajbothra786@gmail.com>
 * @copyright Copyright (c) 2020 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */
class FhirPractitionerService extends FhirServiceBase
{
    /**
     * @var PractitionerService
     */
    private $practitionerService;

    public function __construct()
    {
        parent::__construct();
        $this->practitionerService = new PractitionerService();
    }

    /**
     * Returns an array mapping FHIR Practitioner Resource search parameters to OpenEMR Practitioner search parameters
     * @return array The search parameters
     */
    protected function loadSearchParameters()
    {
        return  [
            "active" => ["active"],
            "email" => ["email"],
            "phone" => ["phonew1", "phone", "phonecell"],
            "telecom" => ["email", "phone", "phonew1", "phonecell"],
            "address" => ["street", "streetb", "zip", "city", "state"],
            "address-city" => ["city"],
            "address-postalcode" => ["zip"],
            "address-state" => ["state"],
            "family" => ["lname"],
            "given" => ["fname", "mname"],
            "name" => ["title", "fname", "mname", "lname"]
        ];
    }


    /**
     * Parses an OpenEMR practitioner record, returning the equivalent FHIR Practitioner Resource
     *
     * @param array $dataRecord The source OpenEMR data record
     * @param boolean $encode Indicates if the returned resource is encoded into a string. Defaults to false.
     * @return FHIRPractitioner
     */
    public function parseOpenEMRRecord($dataRecord = array(), $encode = false)
    {
        $practitionerResource = new FHIRPractitioner();

        $meta = array('versionId' => '1', 'lastUpdated' => gmdate('c'));
        $practitionerResource->setMeta($meta);

        $practitionerResource->setActive($dataRecord['active'] == "1" ? true : false);

        $narrativeText = '';
        if (isset($dataRecord['fname'])) {
            $narrativeText = $dataRecord['fname'];
        }
        if (isset($dataRecord['lname'])) {
            $narrativeText .= ' ' . $dataRecord['lname'];
        }
        $text = array(
            'status' => 'generated',
            'div' => '<div xmlns="http://www.w3.org/1999/xhtml"> <p>' . $narrativeText . '</p></div>'
        );
        $practitionerResource->setText($text);

        $id = new FHIRId();
        $id->setValue($dataRecord['uuid']);
        $practitionerResource->setId($id);

        $name = new FHIRHumanName();
        $name->setUse('official');

        if (isset($dataRecord['title'])) {
            $name->addPrefix($dataRecord['title']);
        }
        if (isset($dataRecord['lname'])) {
            $name->setFamily($dataRecord['lname']);
        }

        $givenName = array();
        if (isset($dataRecord['fname'])) {
            array_push($givenName, $dataRecord['fname']);
        }

        if (isset($dataRecord['mname'])) {
            array_push($givenName, $dataRecord['mname']);
        }

        if (count($givenName) > 0) {
            $name->given = $givenName;
        }

        $practitionerResource->addName($name);

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
        if (!empty($dataRecord['zip'])) {
            $address->setPostalCode($dataRecord['zip']);
        }

        $practitionerResource->addAddress($address);

        if (!empty($dataRecord['phone'])) {
            $practitionerResource->addTelecom(array(
                'system' => 'phone',
                'value' => $dataRecord['phone'],
                'use' => 'home'
            ));
        }

        if (!empty($dataRecord['phonew1'])) {
            $practitionerResource->addTelecom(array(
                'system' => 'phone',
                'value' => $dataRecord['phonew1'],
                'use' => 'work'
            ));
        }

        if (!empty($dataRecord['phonecell'])) {
            $practitionerResource->addTelecom(array(
                'system' => 'phone',
                'value' => $dataRecord['phonecell'],
                'use' => 'mobile'
            ));
        }

        if (isset($dataRecord['email'])) {
            $practitionerResource->addTelecom(array(
                'system' => 'email',
                'value' => $dataRecord['email'],
                'use' => 'home'
            ));
        }

        if (isset($dataRecord['npi'])) {
            $fhirIdentifier = [
                'system' => "http://hl7.org/fhir/sid/us-npi",
                'value' => $dataRecord['npi']
            ];
            $practitionerResource->addIdentifier($fhirIdentifier);
        }

        if ($encode) {
            return json_encode($practitionerResource);
        } else {
            return $practitionerResource;
        }
    }

    /**
     * Parses a FHIR Practitioner Resource, returning the equivalent OpenEMR practitioner record.
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
            $name = [];
            foreach ($fhirResource['name'] as $sub_name) {
                if ($sub_name['use'] === 'official') {
                    $name = $sub_name;
                    break;
                }
            }
            if (isset($name['family'])) {
                $data['lname'] = $name['family'];
            }
            if ($name['given'][0]) {
                $data['fname'] = $name['given'][0];
            }
            if (isset($name['given'][1])) {
                $data['mname'] = $name['given'][1];
            }
            if (isset($name['prefix'][0])) {
                $data['title'] = $name['prefix'][0];
            }
        }
        if (isset($fhirResource['address'])) {
            if (isset($fhirResource['address'][0]['line'][0])) {
                $data['street'] = $fhirResource['address'][0]['line'][0];
            }
            if (isset($fhirResource['address'][0]['postalCode'][0])) {
                $data['zip'] = $fhirResource['address'][0]['postalCode'];
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
                            case 'mobile':
                                $data['phonecell'] = $telecom['value'];
                                break;
                            case 'home':
                                $data['phone'] = $telecom['value'];
                                break;
                            case 'work':
                                $data['phonew1'] = $telecom['value'];
                                break;
                        }
                        break;
                    case 'email':
                        $data['email'] = $telecom['value'];
                        break;
                    default:
                        //Should give Error for incapability
                        break;
                }
            }
        }
        if (isset($fhirResource['gender'])) {
            $data['sex'] = $fhirResource['gender'];
        }

        foreach ($fhirResource['identifier'] as $index => $identifier) {
            if ($identifier['system'] == "http://hl7.org/fhir/sid/us-npi") {
                $data['npi'] = $identifier['value'];
            }
        }
        return $data;
    }

    /**
     * Inserts an OpenEMR record into the sytem.
     *
     * @param array $openEmrRecord OpenEMR practitioner record
     * @return ProcessingResult
     */
    public function insertOpenEMRRecord($openEmrRecord)
    {
        return $this->practitionerService->insert($openEmrRecord);
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
        $processingResult = $this->practitionerService->update($fhirResourceId, $updatedOpenEMRRecord);
        return $processingResult;
    }

    /**
     * Performs a FHIR Practitioner Resource lookup by FHIR Resource ID
     * @param $fhirResourceId //The OpenEMR record's FHIR Practitioner Resource ID.
     */
    public function getOne($fhirResourceId)
    {
        $processingResult = $this->practitionerService->getOne($fhirResourceId);
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
        return $this->practitionerService->getAll($openEMRSearchParameters, false);
    }
    public function createProvenanceResource($dataRecord = array(), $encode = false)
    {
        // TODO: If Required in Future
    }
}
