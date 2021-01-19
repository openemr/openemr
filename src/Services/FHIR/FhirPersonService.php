<?php

/**
 * FHIR Person Service.
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Yash Bothra <yashrajbothra786@gmail.com>
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPerson;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPractitioner;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRHumanName;
use OpenEMR\FHIR\R4\FHIRElement\FHIRAddress;
use OpenEMR\Services\UserService;
use OpenEMR\Validators\ProcessingResult;

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
class FhirPersonService extends FhirServiceBase
{
    const RESOURCE_NAME = 'Person';

    /**
     * @var UserService
     */
    private $userService;

    public function __construct()
    {
        parent::__construct();
        $this->userService = new UserService();
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
     * Parses an OpenEMR user record, returning the equivalent FHIR Person Resource
     *
     * @param array $dataRecord The source OpenEMR data record
     * @param boolean $encode Indicates if the returned resource is encoded into a string. Defaults to false.
     * @return FHIRPractitioner
     */
    public function parseOpenEMRRecord($dataRecord = array(), $encode = false)
    {
        $person = new FHIRPerson();

        $meta = array('versionId' => '1', 'lastUpdated' => gmdate('c'));
        $person->setMeta($meta);

        $person->setActive($dataRecord['active'] == "1" ? true : false);

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
        $person->setText($text);

        $id = new FHIRId();
        $id->setValue($dataRecord['uuid']);
        $person->setId($id);

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

        $person->addName($name);

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

        $person->addAddress($address);

        if (!empty($dataRecord['phone'])) {
            $person->addTelecom(array(
                'system' => 'phone',
                'value' => $dataRecord['phone'],
                'use' => 'home'
            ));
        }

        if (!empty($dataRecord['phonew1'])) {
            $person->addTelecom(array(
                'system' => 'phone',
                'value' => $dataRecord['phonew1'],
                'use' => 'work'
            ));
        }

        if (!empty($dataRecord['phonecell'])) {
            $person->addTelecom(array(
                'system' => 'phone',
                'value' => $dataRecord['phonecell'],
                'use' => 'mobile'
            ));
        }

        if (isset($dataRecord['email'])) {
            $person->addTelecom(array(
                'system' => 'email',
                'value' => $dataRecord['email'],
                'use' => 'home'
            ));
        }

        if ($encode) {
            return json_encode($person);
        } else {
            return $person;
        }
    }

    /**
     * Parses a FHIR Practitioner Resource, returning the equivalent OpenEMR person record.
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

        return $data;
    }

    /**
     * Performs a FHIR Practitioner Resource lookup by FHIR Resource ID
     * @param $fhirResourceId //The OpenEMR record's FHIR Practitioner Resource ID.
     */
    public function getOne($fhirResourceId)
    {
        $user = $this->userService->getUserByUUID($fhirResourceId);
        $processingResult = new ProcessingResult();
        if (empty($user)) {
            $validationMessages = [
                'uuid' => ["invalid or nonexisting value" => " value " . $fhirResourceId]
            ];
            $processingResult->setValidationMessages($validationMessages);
            return $processingResult;
        }
        $fhirRecord = $this->parseOpenEMRRecord($user);
        $processingResult->setData([]);
        $processingResult->addData($fhirRecord);
        return $processingResult;
    }

    /**
     * Inserts an OpenEMR record into the sytem.
     *
     * @param array $openEmrRecord OpenEMR practitioner record
     * @return ProcessingResult
     */
    public function insertOpenEMRRecord($openEmrRecord)
    {
        // implement this if we want to allow inserts on this resource
        throw new \BadMethodCallException("insert is not supported in this resource");
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
        // implement this if we want to allow updates on this resource
        throw new \BadMethodCallException("update is not supported in this resource");
    }

    /**
     * Searches for OpenEMR records using OpenEMR search parameters
     *
     * @param array openEMRSearchParameters OpenEMR search fields
     * @return ProcessingResult
     */
    public function searchForOpenEMRRecords($openEMRSearchParameters)
    {
        $records = $this->userService->getAll($openEMRSearchParameters, false);
        $records = empty($records) ? [] : $records;
        $processingResult = new ProcessingResult();
        $processingResult->setData([]);
        foreach ($records as $record) {
            $processingResult->addData($this->parseOpenEMRRecord($record));
        }

        return $processingResult;
    }
    public function createProvenanceResource($dataRecord = array(), $encode = false)
    {
        // TODO: If Required in Future
        throw new \BadMethodCallException("provenance record is not supported in this resource");
    }
}
