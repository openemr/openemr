<?php

namespace OpenEMR\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\Services\FHIR\FhirServiceBase;
use OpenEMR\Services\PractitionerService;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPractitioner;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRHumanName;
use OpenEMR\FHIR\R4\FHIRElement\FHIRAddress;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;

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
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, [new ServiceField('uuid', ServiceField::TYPE_UUID)]),
            'active' => new FhirSearchParameterDefinition('active', SearchFieldType::TOKEN, ['active']),
            'email' => new FhirSearchParameterDefinition('email', SearchFieldType::TOKEN, ['email']),
            'phone' => new FhirSearchParameterDefinition('phone', SearchFieldType::TOKEN, ["phonew1", "phone", "phonecell"]),
            'telecom' => new FhirSearchParameterDefinition('telecom', SearchFieldType::TOKEN, ["email", "phone", "phonew1", "phonecell"]),
            'address' => new FhirSearchParameterDefinition('address', SearchFieldType::STRING, ["street", "streetb", "zip", "city", "state"]),
            'address-city' => new FhirSearchParameterDefinition('address-city', SearchFieldType::STRING, ['city']),
            'address-postalcode' => new FhirSearchParameterDefinition('address-postalcode', SearchFieldType::STRING, ['zip']),
            'address-state' => new FhirSearchParameterDefinition('address-state', SearchFieldType::STRING, ['state']),
            'family' => new FhirSearchParameterDefinition('family', SearchFieldType::STRING, ["lname"]),
            'given' => new FhirSearchParameterDefinition('given', SearchFieldType::STRING, ["fname", "mname"]),
            'name' => new FhirSearchParameterDefinition('name', SearchFieldType::STRING, ["title", "fname", "mname", "lname"])
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

        $meta = new FHIRMeta();
        $meta->setVersionId('1');
        $meta->setLastUpdated(gmdate('c'));
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

        $practitionerResource->addName(UtilsService::createHumanNameFromRecord($dataRecord));
        $address = UtilsService::createAddressFromRecord($dataRecord);
        if (isset($address)) {
            $practitionerResource->addAddress($address);
        }

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

        if (!empty($dataRecord['email'])) {
            $practitionerResource->addTelecom(array(
                'system' => 'email',
                'value' => $dataRecord['email'],
                'use' => 'home'
            ));
        }

        if (!empty($dataRecord['npi'])) {
            $identifier = new FHIRIdentifier();
            $identifier->setSystem(FhirCodeSystemUris::PROVIDER_NPI);
            $identifier->setValue($dataRecord['npi']);
            $practitionerResource->addIdentifier($identifier);
        } else {
            $practitionerResource->addIdentifier(UtilsService::createDataMissingExtension());
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
     * Searches for OpenEMR records using OpenEMR search parameters
     *
     * @param array openEMRSearchParameters OpenEMR search fields
     * @param $puuidBind - NOT USED
     * @return ProcessingResult
     */
    public function searchForOpenEMRRecords($openEMRSearchParameters, $puuidBind = null)
    {
        return $this->practitionerService->getAll($openEMRSearchParameters, true);
    }
    public function createProvenanceResource($dataRecord = array(), $encode = false)
    {
        // TODO: If Required in Future
    }
}
