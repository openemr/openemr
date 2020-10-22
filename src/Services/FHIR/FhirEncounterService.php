<?php

namespace OpenEMR\Services\FHIR;

use OpenEMR\Services\EncounterService;
use OpenEMR\Services\FHIR\FhirServiceBase;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIREncounter;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCode;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\FHIR\R4\FHIRResource\FHIREncounter\FHIREncounterParticipant;
use OpenEMR\Validators\ProcessingResult;

class FhirEncounterService extends FhirServiceBase
{
    /**
     * @var EncounterService
     */
    private $encounterService;

    public function __construct()
    {
        parent::__construct();
        $this->encounterService = new EncounterService();
    }

    /**
     * Returns an array mapping FHIR Encounter Resource search parameters to OpenEMR Encounter search parameters
     * @return array The search parameters
     */
    protected function loadSearchParameters()
    {
        return  [
            '_id' => ['uuid'],
            'patient' => ['pid'],
            'date' => ['date']
        ];
    }

    /**
     * Parses an OpenEMR patient record, returning the equivalent FHIR Patient Resource
     * https://build.fhir.org/ig/HL7/US-Core-R4/StructureDefinition-us-core-encounter-definitions.html
     *
     * @param array $dataRecord The source OpenEMR data record
     * @param boolean $encode Indicates if the returned resource is encoded into a string. Defaults to false.
     * @return FHIREncounter
     */
    public function parseOpenEMRRecord($dataRecord = array(), $encode = false)
    {
        $encounterResource = new FHIREncounter();

        $meta = array('versionId' => '1', 'lastUpdated' => gmdate('c'));
        $encounterResource->setMeta($meta);

        $id = new FhirId();
        $id->setValue($dataRecord['uuid']);
        $encounterResource->setId($id);

        $status = new FHIRCode('finished');
        $encounterResource->setStatus($status);

        if (!empty($dataRecord['provider_id'])) {
            $parctitioner = new FHIRReference(['reference' => 'Practitioner/' . $dataRecord['provider_id']]);
            $participant = new FHIREncounterParticipant(array(
                'individual' => $parctitioner,
                'period' => ['start' => gmdate('c', strtotime($dataRecord['date']))]
            ));
            $participantType = new FHIRCodeableConcept();
            $participantType->addCoding(array(
                "system" => "http://terminology.hl7.org/CodeSystem/v3-ParticipationType",
                "code" => "PPRF"
            ));
            $participantType->setText("Primary Performer");
            $participant->addType($participantType);
            $encounterResource->addParticipant($participant);
        }

        if (!empty($dataRecord['facility_id'])) {
            $serviceOrg = new FHIRReference(['reference' => 'Organization/' . $dataRecord['facility_id']]);
            $encounterResource->setServiceProvider($serviceOrg);
        }

        if (!empty($dataRecord['reason'])) {
            $reason = new FHIRCodeableConcept();
            $reason->setText($dataRecord['reason']);
            $encounterResource->addReasonCode($reason);
        }

        if (!empty($dataRecord['puuid'])) {
            $patient = new FHIRReference(['reference' => 'Patient/' . $dataRecord['puuid']]);
            $encounterResource->setSubject($patient);
        }

        if (!empty($dataRecord['date'])) {
            $period = new FHIRPeriod();
            $period->setStart(gmdate('c', strtotime($dataRecord['date'])));
            $encounterResource->setPeriod($period);
        }

        if (!empty($dataRecord['class_code'])) {
            $class = new FHIRCoding();
            $class->setSystem("http://terminology.hl7.org/CodeSystem/v3-ActCode");
            $class->setCode($dataRecord['class_code']);
            $class->setDisplay($dataRecord['class_title']);
            $encounterResource->setClass($class);
        }

        // Encounter.type
        $type = new FHIRCodeableConcept();
        $type->addCoding(array(
            "system" => "http://snomed.info/sct",
            "code" => "185349003"
        ));
        $type->setText("Encounter for check up (procedure)");
        $encounterResource->addType($type);

        if ($encode) {
            return json_encode($encounterResource);
        } else {
            return $encounterResource;
        }
    }

    /**
     * Performs a FHIR Encounter Resource lookup by FHIR Resource ID
     * @param $fhirResourceId //The OpenEMR record's FHIR Encounter Resource ID.
     */
    public function getOne($fhirResourceId)
    {
        $processingResult = $this->encounterService->getEncounter($fhirResourceId);
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
    public function searchForOpenEMRRecords($searchParam)
    {
        return $this->encounterService->getEncountersBySearch($searchParam);
    }

    public function parseFhirResource($fhirResource = array())
    {
        // TODO: If Required in Future
    }

    public function insertOpenEMRRecord($openEmrRecord)
    {
        // TODO: If Required in Future
    }

    public function updateOpenEMRRecord($fhirResourceId, $updatedOpenEMRRecord)
    {
        // TODO: If Required in Future
    }

    public function createProvenanceResource($dataRecord = array(), $encode = false)
    {
        // TODO: If Required in Future
    }
}
