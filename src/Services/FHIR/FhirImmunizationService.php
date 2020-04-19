<?php

namespace OpenEMR\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRElement\FHIRAnnotation;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRDate;
use OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRImmunization;
use OpenEMR\FHIR\R4\FHIRElement\FHIRImmunizationStatusCodes;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\FHIR\R4\FHIRElement\FHIRString;
use OpenEMR\FHIR\R4\FHIRElement\FHIRUrl;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleLink;
use OpenEMR\FHIR\R4\FHIRResource\FHIRImmunization\FHIRImmunizationEducation;
use OpenEMR\FHIR\R4\FHIRResource\FHIRImmunization\FHIRImmunizationPerformer;
use OpenEMR\FHIR\R4\PHPFHIRResponseParser;

class FhirImmunizationService
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
                    patient_id,
                    administered_date,
                    cvx_code,
                    manufacturer,
                    lot_number,
                    administered_by_id,
                    administered_by,
                    education_date,
                    note,
                    create_date,
                    amount_administered,
                    amount_administered_unit,
                    expiration_date,
                    route,
                    administration_site,
                    completion_status,
                    refusal_reason 
                    FROM Immunizations";

        if (isset($search['patient'])) {
            $SQL .= " WHERE patient_id = ?;";
        }

        $immunizationResults = sqlStatement($SQL, $search['patient']);
        $results = array();
        while ($row = sqlFetchArray($immunizationResults)) {
            array_push($results, $row);
        }
        return $results;
    }

    public function getOne($id)
    {
        $SQL = "SELECT id,
                    patient_id,
                    administered_date,
                    cvx_code,
                    manufacturer,
                    lot_number,
                    administered_by_id,
                    administered_by,
                    education_date,
                    note,
                    create_date,
                    amount_administered,
                    amount_administered_unit,
                    expiration_date,
                    route,
                    administration_site,
                    completion_status,
                    refusal_reason 
                    FROM Immunizations
                    WHERE id = ?";

        $sqlResult = sqlStatement($SQL, $id);
        $result = sqlFetchArray($sqlResult);

        return $result;
    }

    public function createImmunizationResource($id = '', $data = '', $encode = true)
    {
        $status = new FHIRImmunizationStatusCodes();
        $statusCoding = new FHIRCoding();
        $statusCoding->setSystem('http://hl7.org/fhir/event-status');

        if ($data['added_erroneously'] != "0") {
            $statusCoding->setCode("entered-in-error");
            $statusCoding->setDisplay("Entered in Error");
        } else if (isset($data['administered_date'])) {
            $statusCoding->setCode("completed");
            $statusCoding->setDisplay("Completed");
        } else {
            $statusCoding->setCode("not-done");
            $statusCoding->setDisplay("Not Done");
        }
        $status->setValue($statusCoding);

        $statusReason = new FHIRCodeableConcept();
        $statusReasonCoding = new FHIRCoding();
        $statusReasonCoding->setSystem("http://terminology.hl7.org/CodeSystem/v3-ActReason");
        $statusToCodeMapping = array(
                                    "immunity" => "IMMUNE",
                                    "medical precaution" => "MEDPREC",
                                    "product out of stock" => "OSTOCK",
                                    "patient objection" => "PATOBJ"
                                );
        $statusReasonCoding->setCode($statusToDisplayMapping[strtolower($data['refusal_reason'])]);
        $statusReasonCoding->setDisplay($data['refusal_reason']);
        $statusReason->addCoding($statusReasonCoding);

        $vaccineCode = new FHIRCodeableConcept();
        $vaccineCodeCoding = new FHIRCoding();
        $vaccineCodeCoding->setSystem("http://hl7.org/fhir/sid/cvx");
        $vaccineCodeCoding->setCode($data['cvx_code']);
        $vaccineCodeCoding->setDisplay($data['cvx_code']);
        $vaccineCode->addCoding($vaccineCodeCoding);

        $patient = new FHIRReference();
        $patient->setReference('Patient/' . $data['patient_id']);

        $occurenceDateTime = new FHIRDateTime();
        $occurenceDateTime->setValue($data['administered_date']);

        $recorded = new FHIRDateTime();
        $recorded->setValue($data['create_date']);

        $manufacturer = new FHIRReference();
        $manufacturer->setReference('Organization/' . $data['manufacturer']);

        $lotNumber = new FHIRString();
        $lotNumber->setValue($data['lot_number']);

        $expirationDate = new FHIRDate();
        $expirationDate->setValue($data['expiration_date']);

        $site = new FHIRCodeableConcept();
        $siteCoding = new FHIRCoding();
        $siteCoding->setSystem("http://terminology.hl7.org/CodeSystem/v3-ActSite");
        if (strtolower($data['administration_site']) == "left arm") {
            $siteCoding->setCode("LA");
            $siteCoding->setDisplay("Left arm");
        } else if (strtolower($data['administration_site']) == "right arm") {
            $siteCoding->setCode("RA");
            $siteCoding->setDisplay("Right Arm");
        }
        $site->addCoding($siteCoding);

        $route = new FHIRCodeableConcept();
        $routeCoding = new FHIRCoding();
        $routeCoding->setSystem("http://terminology.hl7.org/CodeSystem/v3-RouteOfAdministration");
        $routeCoding->setCode($data['route']);
        $routeCoding->setDisplay($data['route']);
        $route->addCoding($routeCoding);

        $doseQuantity = new FHIRQuantity();
        $doseQuantity->setValue($data['amount_administered']);
        $doseQuantity->setSystem($data['http://unitsofmeasure.org']);
        $doseQuantity->setCode($data['amount_administered_unit']);

        $actor = new FHIRReference();
        $actor->setReference($data['administered_by'] . '/' . $data['administered_by_id']);
        $function = new FHIRCodeableConcept();
        $functionCoding = new FHIRCoding();
        $functionCoding->setSystem("http://terminology.hl7.org/CodeSystem/v2-0443");
        $functionCoding->setCode("OP");
        $functionCoding->setDisplay("Ordering Provider");
        $performer = new FHIRImmunizationPerformer();
        $performer->setActor($actor);
        $performer->setFunction($functionCoding);

        $note = new FHIRAnnotation();
        $note->setText($data['note']);

        $education = new FHIRImmunizationEducation();
        $educationDateTime = new FHIRDateTime();
        $educationDateTime->setValue($data['education_date']);
        $education->setPresentationDate($educationDateTime);

        $resource = new FHIRImmunization();
        $resource->setStatus($status);
        $resource->setStatusReason($statusReason);
        $resource->setVaccineCode($vaccineCode);
        $resource->setPatient($patient);
        $resource->setOccurrenceDateTime($occurenceDateTime);
        $resource->setRecorded($recorded);
        $resource->setManufacturer($manufacturer);
        $resource->setLotNumber($lotNumber);
        $resource->setExpirationDate($expirationDate);
        $resource->setSite($site);
        $resource->setRoute($route);
        $resource->setDoseQuantity($doseQuantity);
        $resource->addPerformer($performer);
        $resource->addNote($note);
        $resource->addEducation($education);

        if ($encode) {
            return json_encode($resource);
        } else {
            return $resource;
        }
    }
}
