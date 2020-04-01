<?php

namespace OpenEMR\Services\FHIR;

use OpenEMR\Services\EncounterService;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIREncounter;
use OpenEMR\FHIR\R4\FHIRElement\FhirId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\FHIR\R4\FHIRResource\FHIREncounter\FHIREncounterParticipant;

class FhirEncounterService
{
    private $encounterService;

    public function __construct()
    {
        $this->encounterService = new EncounterService();
    }

    public function createEncounterResource($eid = '', $data = '', $encode = true)
    {
        $pid = $data['pid'];
        //$temp = $data['provider_id'];
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
        $resource->addReasonCode($reason);
        $resource->status = 'finished';
        $resource->setSubject(['reference' => "Patient/$pid"]);

        if ($encode) {
            return json_encode($resource);
        } else {
            return $resource;
        }
    }

    public function getEncounter($id)
    {
        return $this->encounterService->getEncounter($id);
    }

    public function getEncountersBySearch($searchParam)
    {
        return $this->encounterService->getEncountersBySearch($searchParam);
    }
}
