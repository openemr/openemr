<?php
/**
 * FHIRResources service class
 *
 * Copyright (C) 2018 Jerry Padgett <sjpadgett@gmail.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Jerry Padgett <sjpadgett@gmail.com>
 * @link    http://www.open-emr.org
 */

namespace OpenEMR\Services;

// @TODO move to OpenEMR composer auto
require_once dirname(dirname(__FILE__)) . "/phpfhir/vendor/autoload.php";

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
use HL7\FHIR\STU3\FHIRResource\FHIRBundle;
use HL7\FHIR\STU3\FHIRResource\FHIRBundle\FHIRBundleLink;
use HL7\FHIR\STU3\PHPFHIRResponseParser;

//use HL7\FHIR\STU3\FHIRResource\FHIREncounter\FHIREncounterLocation;
//use HL7\FHIR\STU3\FHIRResource\FHIREncounter\FHIREncounterDiagnosis;
//use HL7\FHIR\STU3\FHIRElement\FHIRPeriod;
//use HL7\FHIR\STU3\FHIRElement\FHIRParticipantRequired;

class FhirResourcesService
{
    public function createBundle($resource = '', $resource_array = [], $encode = true)
    {
        $bundleUrl = \RestConfig::$REST_FULL_URL;
        $nowDate = date("Y-m-d\TH:i:s");
        $meta = array('lastUpdated' => $nowDate);
        $bundleLink = new FHIRBundleLink(array('relation' => 'self', 'url' => $bundleUrl));
        // set bundle type default to collection so may include different
        // resource types. at least I hope thats how it works....
        $bundleInit = array(
            'identifier' => $resource . "bundle",
            'type' => 'collection',
            'total' => count($resource_array),
            'meta' => $meta);
        $bundle = new FHIRBundle($bundleInit);
        $bundle->addLink($bundleLink);
        foreach ($resource_array as $addResource) {
            $bundle->addEntry($addResource);
        }

        if ($encode)
            return json_encode($bundle);

        return $bundle;
    }

    public function createPatientResource($pid = '', $data = '', $encode = true)
    {
        // @todo add disply text after meta
        $nowDate = date("Y-m-d\TH:i:s");
        $id = new FhirId();
        $id->setValue($pid);
        $name = new FHIRHumanName();
        $address = new FHIRAddress();
        $gender = new FHIRAdministrativeGender();
        $meta = array('versionId' => '1', 'lastUpdated' => $nowDate);
        $initResource = array('id' => $id, 'meta' => $meta);
        $name->setUse('official');
        $name->setFamily($data['lname']);
        $name->given = [$data['fname'], $data['mname']];
        $address->addLine($data['street']);
        $address->setCity($data['city']);
        $address->setState($data['state']);
        $address->setPostalCode($data['postal_code']);
        $gender->setValue(strtolower($data['sex']));

        $patientResource = new FHIRPatient($initResource);
        //$patientResource->setId($id);
        $patientResource->setActive(true);
        $patientResource->setGender($gender);
        $patientResource->addName($name);
        $patientResource->addAddress($address);

        if ($encode)
            return json_encode($patientResource);
        else
            return $patientResource;
    }

    public function createPractitionerResource($id = '', $data = '', $encode = true)
    {
        $resource = new FHIRPractitioner();
        $id = new FhirId();
        $name = new FHIRHumanName();
        $address = new FHIRAddress();
        $id->setValue($id);
        $name->setUse('official');
        $name->setFamily($data['lname']);
        $name->given = [$data['fname'], $data['mname']];
        $address->addLine($data['street']);
        $address->setCity($data['city']);
        $address->setState($data['state']);
        $address->setPostalCode($data['zip']);
        $resource->setId($id);
        $resource->setActive(true);
        $gender = new FHIRAdministrativeGender();
        $gender->setValue('unknown');
        $resource->setGender($gender);
        $resource->addName($name);
        $resource->addAddress($address);

        if ($encode)
            return json_encode($resource);
        else
            return $resource;
    }

    public function createEncounterResource($eid = '', $data = '',  $encode = true)
    {
        $pid = $data['pid'];
        $temp = $data['provider_id'];
        //$r = $this->createPractitionerResource($data['provider_id'], $temp);
        $resource = new FHIREncounter();
        $id = new FhirId();
        $id->setValue($eid);
        $resource->setId($id);
        $participant = new FHIREncounterParticipant();
        $prtref = new FHIRReference;
        $temp = 'Practitioner/' . $data['provider_id'];
        $prtref->setReference($temp);
        $participant->setIndividual($prtref);
        $date = date('Y-m-d', strtotime($data['date']));
        $participant->setPeriod(['start' => $date]);

        $resource->addParticipant($participant);
        $reason = new FHIRCodeableConcept();
        $reason->setText($data['reason']);
        $resource->addReason($reason);
        $resource->status = 'finished';
        $resource->setSubject(['reference' => "Patient/$pid"]);

        if ($encode)
            return json_encode($resource);
        else
            return $resource;
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
