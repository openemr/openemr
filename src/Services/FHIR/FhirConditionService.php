<?php

namespace OpenEMR\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCondition;
use OpenEMR\FHIR\R4\FHIRElement\FHIRAnnotation;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleLink;
use OpenEMR\FHIR\R4\PHPFHIRResponseParser;
use OpenEMR\Services\ListService;

class FhirConditionService
{

    private $id;

    public function __construct()
    {
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getAll($search)
    {
        $SQL = "SELECT id,
                        date as recorded_date,
                        type,
                        subtype,
                        title,
                        begdate,
                        enddate,
                        returndate,
                        occurrence,
                        referredby,
                        extrainfo,
                        diagnosis,
                        pid,
                        outcome,
                        severity_al
                        FROM lists";

        if (isset($search['patient'])) {
            $SQL .= " WHERE pid = ?;";
        }

        $conditionResults = sqlStatement($SQL, $search['patient']);
        $results = array();
        while ($row = sqlFetchArray($conditionResults)) {
            array_push($results, $row);
        }
        return $results;
    }

    public function getOne($id)
    {
        $SQL = "SELECT id,
                        date as recorded_date,
                        type,
                        subtype,
                        title,
                        begdate,
                        enddate,
                        returndate,
                        occurrence,
                        referredby,
                        extrainfo,
                        diagnosis,
                        pid,
                        outcome,
                        severity_al
                        FROM lists
                        WHERE id = ?;";

        $sqlResult = sqlStatement($SQL, $id);
        $result = sqlFetchArray($sqlResult);

        return $result;
    }

    public function createConditionResource($id = '', $data = '', $encode = true)
    {
        $clinicalStatus = new FHIRCodeableConcept();
        $clinicalStatusCoding = new FHIRCoding();
        $clinicalStatusCoding->setSystem("http://terminology.hl7.org/CodeSystem/condition-clinical");
        if ($data['occurrence'] == 1) {
            if (isset($data['enddate']) && $data['outcome'] == "1") {
                $clinicalStatusCoding->setCode("resolved");
                $clinicalStatusCoding->setDisplay("Resolved");
            } elseif (!isset($data['enddate'])) {
                $clinicalStatusCoding->setCode("active");
                $clinicalStatusCoding->setDisplay("Active");
            }
        } elseif (isset($data['enddate']) && $data['outcome'] == "0") {
            $clinicalStatusCoding->setCode("inactive");
            $clinicalStatusCoding->setDisplay("Inactive");
        } elseif ($data['occurrence'] == 2 && !isset($data['enddate']) && $data['outcome'] == "0") {
            $clinicalStatusCoding->setCode("recurrence");
            $clinicalStatusCoding->setDisplay("Recurrence");
        } elseif ($data['occurrence'] > 2 && isset($data['enddate']) && $data['outcome'] == "1") {
            $clinicalStatusCoding->setCode("remission");
            $clinicalStatusCoding->setDisplay("Remission");
        } elseif ($data['occurrence'] > 2 && !isset($data['enddate']) && $data['outcome'] == "0") {
            $clinicalStatusCoding->setCode("relapse");
            $clinicalStatusCoding->setDisplay("Relapse");
        } else {
            $clinicalStatusCoding->setSystem("http://terminology.hl7.org/CodeSystem/data-absent-reason");
            $clinicalStatusCoding->setCode("unknown");
            $clinicalStatusCoding->setDisplay("Unknown");
        }
        $clinicalStatus->addCoding($clinicalStatusCoding);
        
        $severity = new FHIRCodeableConcept();
        $severityToCodeMapping = array(
                "Mild" => "255604002",
                "Moderate" => "6736007",
                "Severe" => "24484000"
            );
        $severityCoding = new FHIRCoding();
        $severityCoding->setSystem("http://snomed.info/sct");
        $severityCoding->setCode($severityToCodeMapping[ucwords(strtolower($data['severity_al']))]);
        $severityCoding->setDisplay(ucwords(strtolower($data['severity_al'])));
        $severity->addCoding($severityCoding);
        
        $code = new FHIRCodeableConcept();
        $codeCoding = new FHIRCoding();
        $codeCoding->setSystem("http://snomed.info/sct");
        $codeCoding->setCode($data['diagnosis']);
        $codeCoding->setDisplay($data['diagnosis']);
        $code->addCoding($codeCoding);
        
        $bodySite = new FHIRCodeableConcept();
        $bodySiteCoding = new FHIRCoding();
        $bodySiteCoding->setSystem("http://snomed.info/sct");
        $bodySiteCoding->setCode($data['injury_part']);
        $bodySiteCoding->setDisplay($data['injury_part']);
        $bodySite->addCoding($bodySiteCoding);

        $subject = new FHIRReference();
        $subject->setReference('Patient/' . $data['pid']);
        
        $onSetDateTime = new FHIRDateTime();
        $onSetDateTime->setValue($data['begdate']);
        
        $abatementDateTime = new FHIRDateTime();
        $abatementDateTime->setValue($data['enddate']);

        $recordedDateTime = new FHIRDateTime();
        $recordedDateTime->setValue($data['recorded_date']);
        
        $recorder = new FHIRReference();
        $recorder->setReference('Practitioner/' . $data['referredby']);
        
        $asserter = new FHIRReference();
        $asserter->setReference('Patient/' . $data['pid']);
        
        $note = new FHIRAnnotation();
        $note->setText($data['extrainfo']);
        
        $resource = new FHIRCondition();
        $resource->setClinicalStatus($clinicalStatus);
        $resource->setSeverity($severity);
        $resource->setCode($code);
        $resource->addBodySite($bodySite);
        $resource->setSubject($subject);
        $resource->setOnsetDateTime($onSetDateTime);
        $resource->setAbatementDateTime($abatementDateTime);
        $resource->setRecordedDate($recordedDateTime);
        $resource->setRecorder($recorder);
        $resource->setAsserter($asserter);
        $resource->addNote($note);
        
        if ($encode) {
            return json_encode($resource);
        } else {
            return $resource;
        }
    }
}
