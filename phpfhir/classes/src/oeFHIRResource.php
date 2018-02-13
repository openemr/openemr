<?php

namespace oeFHIR;

require_once '../../HL7/FHIR/STU3/PHPFHIRResponseParser.php';

use HL7\FHIR\STU3\FHIRDomainResource\FHIRPatient;
use HL7\FHIR\STU3\FHIRElement\FHIRAddress;
use HL7\FHIR\STU3\FHIRElement\FHIRHumanName;
use HL7\FHIR\STU3\FHIRElement\FHIRId;

class oeFHIRResource
{
    public function createBundle($pid = '', $encode = true, $resource_array = '')
    {
        $fs = new FetchLiveData();
    }

    public function createPatientResource($pid = '', $encode = true)
    {
        $fs = new FetchLiveData();
        $patientResource = new FHIRPatient();
        $id = new FhirId();
        $name = new FHIRHumanName();
        $address = new FHIRAddress();
        $oept = $fs->getDemographicsCurrent($pid);
        // maybe public id is better here?
        // fhir spec allows either create or update on update put endpoint, but only if id contains alphanumerics.
        // uri id and resource patient id must match.
        $id->setValue('oe-' . $oept['pid']);
        $name->setUse('official');
        $name->setFamily($oept['lname']);
        $name->given = [$oept['fname'], $oept['mname']];
        $address->addLine($oept['street']);
        $address->setCity($oept['city']);
        $address->setState($oept['state']);
        $address->setPostalCode($oept['postal_code']);
        $patientResource->setId($id);
        $patientResource->setActive(true);
        $patientResource->setGender(strtolower($oept['sex']));
        $patientResource->addName($name);
        $patientResource->addAddress($address);

        return json_encode($patientResource);
    }

    public function createPractionerResource($pid = '', $encode = true)
    {
        $fs = new FetchLiveData();
    }

    public function parseResource($rjson = '', $scheme = 'json')
    {
        $parser = new \HL7\FHIR\STU3\PHPFHIRResponseParser();
        if ($scheme == 'json') {
            $class_object = $parser->parse($rjson);
        } else {
            // @todo xml- not sure yet.
        }
        return $class_object; // feed to resource class or use as is object
    }
}
