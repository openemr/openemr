<?php

namespace OpenEMR\Services\FHIR;

use Ramsey\Uuid\Uuid;
use OpenEMR\Services\FHIR\FhirServiceBase;
use OpenEMR\Services\PatientService;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPatient;
use OpenEMR\FHIR\R4\FHIRElement\FHIRAddress;
use OpenEMR\FHIR\R4\FHIRElement\FHIRHumanName;
use OpenEMR\FHIR\R4\FHIRElement\FHIRAdministrativeGender;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;

/**
 * FHIR Patient Service
 *
 * @coversDefaultClass OpenEMR\Services\FHIR\FhirPatientService
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Dixon Whitmire <dixonwh@gmail.com>
 * @copyright Copyright (c) 2020 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */
class FhirPatientService extends FhirServiceBase
{
    private $patientService;

    public function __construct()
    {
        $this->patientService = new PatientService();
    }

    /**
     * Parses an OpenEMR patient record to a FHIR R4 Patient resource.
     * @param $data The OpenEMR Patient record
     * @param $encode Indicates if the return value (FHIR R4 Patient) is encoded to a string. Defaults to true.
     * @return The FHIR R4 Patient resource
     */
    public function parseOpenEMRRecord($data = array(), $encode = true)
    {
        $patientResource = new FHIRPatient();

        $meta = array('versionId' => '1', 'lastUpdated' => gmdate('c'));
        $patientResource->setMeta($meta);

        $patientResource->setActive(true);

        $narrativeText = '';
        if (isset($data['fname'])) {
            $narrativeText = $data['fname'];
        }
        if (isset($data['lname'])) {
            $narrativeText .= ' ' . $data['lname'];
        }
        $text = array(
            'status' => 'generated',
            'div' => '<div xmlns="http://www.w3.org/1999/xhtml"> <p>' . $narrativeText . '</p></div>'
        );
        $patientResource->setText($text);

        $fhirId = Uuid::uuid4()->toString();
        $id = new FhirId();
        $id->setValue($fhirId);
        $patientResource->setId($id);

        $name = new FHIRHumanName();
        $name->setUse('official');

        if (isset($data['title'])) {
            $name->addPrefix($data['title']);
        }
        if (isset($data['lname'])) {
            $name->setFamily($data['lname']);
        }

        $givenName = array();
        if (isset($data['fname'])) {
            array_push($givenName, $data['fname']);
        }

        if (isset($data['mname'])) {
            array_push($givenName, $data['mname']);
        }

        if (count($givenName) > 0) {
            $name->given = $givenName;
        }

        $patientResource->addName($name);

        if (isset($data['DOB'])) {
            $patientResource->setBirthDate($data['DOB']);
        }

        $address = new FHIRAddress();
        if (isset($data['street'])) {
            $address->addLine($data['street']);
        }
        if (isset($data['city'])) {
            $address->setCity($data['city']);
        }
        if (isset($data['state'])) {
            $address->setState($data['state']);
        }
        if (isset($data['postal_code'])) {
            $address->setPostalCode($data['postal_code']);
        }
        if (isset($data['country_code'])) {
            $address->setCountry($data['country_code']);
        }

        $patientResource->addAddress($address);

        if (isset($data['phone_home'])) {
            $patientResource->addTelecom(array(
                'system' => 'phone',
                'value' => $data['phone_home'],
                'use' => 'home'
            ));
        }

        if (isset($data['phone_biz'])) {
            $patientResource->addTelecom(array(
                'system' => 'phone',
                'value' => $data['phone_biz'],
                'use' => 'work'
            ));
        }

        if (isset($data['phone_cell'])) {
            $patientResource->addTelecom(array(
                'system' => 'phone',
                'value' => $data['phone_cell'],
                'use' => 'mobile'
            ));
        }

        if (isset($data['email'])) {
            $patientResource->addTelecom(array(
                'system' => 'email',
                'value' => $data['email'],
                'use' => 'home'
            ));
        }

        $gender = new FHIRAdministrativeGender();
        if (isset($data['sex'])) {
            $gender->setValue(strtolower($data['sex']));
        }
        $patientResource->setGender($gender);

        if (isset($data['ss'])) {
            $fhirIdentifier = [
                'use' => 'official',
                'type' => [
                    'coding' => [
                        [
                            'system' => 'http://terminology.hl7.org/CodeSystem/v2-0203',
                            'code' => 'SS'
                        ]
                    ]
                ],
                'system' => 'http://hl7.org/fhir/sid/us-ssn',
                'value' => $data['ss']
            ];
            $patientResource->addIdentifier($fhirIdentifier);
        }

        if (isset($data['pubpid'])) {
            $fhirIdentifier = [
                'use' => 'official',
                'type' => [
                    'coding' => [
                        [
                            'system' => 'http://terminology.hl7.org/CodeSystem/v2-0203',
                            'code' => 'PT'
                        ]
                    ]
                ],
                'system' => 'http:\\terminology.hl7.org\ValueSet\v2-0203',
                'value' => $data['pubpid']
            ];
            $patientResource->addIdentifier($fhirIdentifier);
        }

        if ($encode) {
            return json_encode($patientResource);
        } else {
            return $patientResource;
        }
    }

    /**
     * Parses a FHIR R4 Patient resource into an OpenEMR Patient record.
     * @param $fhirJson The FHIR R4 Patient resource
     * @return OpenEMR patient record
     */
    public function parseFhirResource($fhirJson = array())
    {
        $data = array();

        if (isset($fhirJson['name'])) {
            $name = [];
            foreach ($fhirJson['name'] as $sub_name) {
                if ($sub_name['use'] == 'official') {
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
        if (isset($fhirJson['address'])) {
            if (isset($fhirJson['address'][0]['line'][0])) {
                $data['street'] = $fhirJson['address'][0]['line'][0];
            }
            if (isset($fhirJson['address'][0]['postalCode'][0])) {
                $data['postal_code'] = $fhirJson['address'][0]['postalCode'];
            }
            if (isset($fhirJson['address'][0]['city'][0])) {
                $data['city'] = $fhirJson['address'][0]['city'];
            }
            if (isset($fhirJson['address'][0]['state'][0])) {
                $data['state'] = $fhirJson['address'][0]['state'];
            }
            if (isset($fhirJson['address'][0]['country'][0])) {
                $data['country'] = $fhirJson['address'][0]['country'];
            }
        }
        if (isset($fhirJson['telecom'])) {
            foreach ($fhirJson['telecom'] as $telecom) {
                switch ($telecom['system']) {
                    case 'phone':
                        switch ($telecom['use']) {
                            case 'mobile':
                                $data['phone_cell'] = $telecom['value'];
                                break;
                            case 'home':
                                $data['phone_home'] = $telecom['value'];
                                break;
                            case 'work':
                                $data['phone_biz'] = $telecom['value'];
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
        if (isset($fhirJson['birthDate'])) {
            $data['DOB'] = $fhirJson['birthDate'];
        }
        if (isset($fhirJson['gender'])) {
            $data['sex'] = $fhirJson['gender'];
        }

        foreach ($fhirJson['identifier'] as $index => $identifier) {
            if (!isset($identifier['type']['coding'][0])) {
                continue;
            }

            $code = $identifier['type']['coding'][0]['code'];
            switch ($code) {
                case 'SS':
                    $data['ss'] = $identifier['value'];
                    break;
                case 'PT':
                    $data['pubpid'] = $identifier['value'];
                    break;
            }
        }
        return $data;
    }

    public function insertOpenEMRRecord($openEmrRecord)
    {
    }

    public function updateOpenEMRRecord($openEMRLookupId, $updatedOpenEMRRecord)
    {
    }

    public function getOne($fhirResourceId)
    {
        throw new \Exception("not implemented");
    }

    public function mapSearchParameters($fhirSearchParameters)
    {
    }

    public function searchForOpenEMRRecords($openEMRSearchParameters)
    {
    }
}
