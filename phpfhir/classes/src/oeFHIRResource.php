<?php
/**
 * oeFHIRResource class
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2018 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://www.gnu.org/licenses/agpl-3.0.en.html GNU Affero General Public License 3
 */

namespace oeFHIR;

use HL7\FHIR\STU3\FHIRDomainResource\FHIREncounter;
use HL7\FHIR\STU3\FHIRDomainResource\FHIRPatient;
use HL7\FHIR\STU3\FHIRDomainResource\FHIRPractitioner;
use HL7\FHIR\STU3\FHIRElement\FHIRAddress;
use HL7\FHIR\STU3\FHIRElement\FHIRAdministrativeGender;
use HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept;
use HL7\FHIR\STU3\FHIRElement\FHIRHumanName;
use HL7\FHIR\STU3\FHIRElement\FHIRId;
use HL7\FHIR\STU3\FHIRElement\FHIRReference;
use HL7\FHIR\STU3\FHIRResource\FHIREncounter\FHIREncounterParticipant;
use HL7\FHIR\STU3\PHPFHIRResponseParser;

//use HL7\FHIR\STU3\FHIRResource\FHIREncounter\FHIREncounterLocation;
//use HL7\FHIR\STU3\FHIRResource\FHIREncounter\FHIREncounterDiagnosis;
//use HL7\FHIR\STU3\FHIRElement\FHIRPeriod;
//use HL7\FHIR\STU3\FHIRElement\FHIRParticipantRequired;

class oeFHIRResource
{
    public function createBundle($pid = '', $encode = true, $resource_array = '')
    {
        $fs = new FetchLiveData();
    }

    public function createPatientResource($pid = '', $rid = '', $encode = true)
    {
        $fs = new FetchLiveData();
        $patientResource = new FHIRPatient();
        $id = new FhirId();
        $name = new FHIRHumanName();
        $address = new FHIRAddress();
        $oept = $fs->getDemographicsCurrent($pid);
        // fhir spec allows either create or update on update put endpoint, but only if id contains alphanumerics.
        // uri id and resource patient id must match.
        $id->setValue($rid);
        $name->setUse('official');
        $name->setFamily($oept['lname']);
        $name->given = [$oept['fname'], $oept['mname']];
        $address->addLine($oept['street']);
        $address->setCity($oept['city']);
        $address->setState($oept['state']);
        $address->setPostalCode($oept['postal_code']);
        $patientResource->setId($id);
        $patientResource->setActive(true);
        $gender = new FHIRAdministrativeGender();
        $gender->setValue(strtolower($oept['sex']));
        $patientResource->setGender($gender);
        $patientResource->addName($name);
        $patientResource->addAddress($address);

        return json_encode($patientResource);
    }

    public function createPractitionerResource($id = '', $rid = '', $encode = true)
    {
        $fs = new FetchLiveData();
        $oept = $fs->getUser($id);

        $resource = new FHIRPractitioner();
        $id = new FhirId();
        $name = new FHIRHumanName();
        $address = new FHIRAddress();
        $id->setValue($rid);
        $name->setUse('official');
        $name->setFamily($oept['lname']);
        $name->given = [$oept['fname'], $oept['mname']];
        $address->addLine($oept['street']);
        $address->setCity($oept['city']);
        $address->setState($oept['state']);
        $address->setPostalCode($oept['zip']);
        $resource->setId($id);
        $resource->setActive(true);
        $gender = new FHIRAdministrativeGender();
        $gender->setValue('unknown');
        $resource->setGender($gender);
        $resource->addName($name);
        $resource->addAddress($address);

        return json_encode($resource);
    }

    public function createEncounterResource($pid = '', $rid = '', $eid = '', $encode = true)
    {
        // If you set an endpoint to an element and resource doesn't exist on server, then create will fail.
        // Practitioner here is an example so I auto create or update the resource so encounter will go...
        // Same with conditions once it it built. Encounters can be tied to episodes and care plans e.t.c.
        $fs = new FetchLiveData();
        $oept = $fs->getEncounterData($pid, $eid);
        $temp = 'provider-' . $oept['provider_id'];
        $rs = new oeFHIRResource(); // update
        $r = $rs->createPractitionerResource($oept['provider_id'], $temp);
        $client = new oeFHIRHttpClient();
        $pt = $client->sendResource('Practitioner', $temp, $r); // update or add practitioner
        $resource = new FHIREncounter();
        $id = new FhirId();
        $id->setValue($rid);
        $resource->setId($id);
        $participant = new FHIREncounterParticipant();
        $prtref = new FHIRReference;
        $temp = 'Practitioner/provider-' . $oept['provider_id'];
        $prtref->setReference($temp);
        $participant->setIndividual($prtref);
        $date = date('Y-m-d', strtotime($oept['date']));
        $participant->setPeriod(['start' => $date]);

        $resource->addParticipant($participant);
        $reason = new FHIRCodeableConcept();
        $reason->setText($oept['reason']);
        $resource->addReason($reason);
        $resource->status = 'finished';

        $resource->setSubject(['reference' => 'Patient/patient-' . $pid]);

        return json_encode($resource);
    }

    public function parseResource($rjson = '', $scheme = 'json')
    {
        $parser = new PHPFHIRResponseParser(false);
        if ($scheme == 'json') {
            $class_object = $parser->parse($rjson);
        } else {
            // @todo xml- not sure yet.
        }
        return $class_object; // feed to resource class or use as is object
    }
}
