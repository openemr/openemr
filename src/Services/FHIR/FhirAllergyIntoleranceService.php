<?php

namespace OpenEMR\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRAllergyIntolerance;
use OpenEMR\FHIR\R4\FHIRElement\FHIRAllergyIntoleranceCategory;
use OpenEMR\FHIR\R4\FHIRElement\FHIRAllergyIntoleranceCriticality;
use OpenEMR\FHIR\R4\FHIRElement\FHIRAllergyIntoleranceType;
use OpenEMR\FHIR\R4\FHIRElement\FHIRAnnotation;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\FHIR\R4\FHIRElement\FHIRUrl;
use OpenEMR\FHIR\R4\FHIRResource\FHIRAllergyIntolerance\FHIRAllergyIntoleranceReaction;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleLink;
use OpenEMR\FHIR\R4\PHPFHIRResponseParser;
use OpenEMR\Services\ListService;

class FhirAllergyIntoleranceService
{
    
    private $id;
    
    public function __construct()
    {
    }
    
    public function setId($id)
    {
        $this->id = $id;
    }
    
    public function getAll()
    {      
        $SQL = "SELECT id,
                        date as recorded_date,
                        type,
                        subtype,
                        title,
                        begdate,
                        enddate,
                        returndate,
                        referredby,
                        extrainfo,
                        diagnosis,
                        pid,
                        outcome,
                        reaction,
                        severity_al
                        FROM lists
                        WHERE type = 'allergy'";

        $allergyIntolerenceresults = sqlStatement($SQL);
        $results = array();
        while ($row = sqlFetchArray($allergyIntolerenceresults)) {
            $codeSQL = "SELECT dx_code 
                            FROM icd10_dx_order_code
                            WHERE short_desc = ?;";
            $code = sqlQuery($codeSQL, array($row['reaction']));
            $row['code'] = $code['dx_code'];
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
                        referredby,
                        extrainfo,
                        diagnosis,
                        pid,
                        outcome,
                        reaction,
                        severity_al
                        FROM lists
                        WHERE type = 'allergy'
                        AND id = ?;";

        $sqlResult = sqlStatement($SQL, $id);
        $result = sqlFetchArray($sqlResult);
        $codeSQL = "SELECT dx_code
                        FROM icd10_dx_order_code
                        WHERE short_desc = ?;";

        $code = sqlQuery($codeSQL, array($result['reaction']));
        $result['code'] = $code['dx_code'];
        return $result;        
    }

    public function createAllergyIntoleranceResource($id = '', $data = '', $encode = true)
    {     
        $type = new FHIRAllergyIntoleranceType();
        $type->setValue($data['type']);

        $onSetDateTime = new FHIRDateTime();
        $onSetDateTime->setValue($data['begdate']);

        $recordedDate = new FHIRDateTime();
        $recordedDate->setValue($data['recorded_date']);
        
        $clinicalStatus = '';
        if ($data['outcome'] == '1' && isset($data['enddate']))
            $clinicalStatus = "resolved";
        else if (isset($data['enddate'])) 
            $clinicalStatus = "active";
        else
            $clinicalStatus = "inactive";
        $clinicalStatusCoding = new FHIRCoding();
        $clinicalStatusCoding->setSystem("http://terminology.hl7.org/CodeSystem/allergyintolerance-clinical");
        $clinicalStatusCoding->setCode($clinicalStatus);
        $clinicalStatusCoding->setDisplay($clinicalStatus);

        $category = new FHIRAllergyIntoleranceCategory();
        $category->setValue($data['subtype']);

        $criticality = new FHIRAllergyIntoleranceCriticality();
        $criticality->setValue($data['severity_al']);

        $patient = new FHIRReference();
        $patient->setReference('Patient/'.$data['pid']);

        $recorder = new FHIRReference();
        $recorder->setReference('Practitioner/'.$data['referredby']);

        $asserter = new FHIRReference();
        $asserter->setReference('Patient/'.$data['pid']);

        $note = new FHIRAnnotation();
        $note->setText($data['extrainfo']);

        $lastOccurrence = new FHIRDateTime();
        $lastOccurrence->setValue($data['returndate']);

        $manifestation = new FHIRCoding();
        $manifestation->setSystem("http://hl7.org/fhir/sid/icd-10-cm");
        $manifestation->setCode($data['code']);
        $manifestation->setDisplay($data['reaction']);

        $description = $data['comments'];

        $reaction = new FHIRAllergyIntoleranceReaction();
        $reaction->addManifestation($manifestation->jsonSerialize());
        $reaction->setDescription($description);
        $reaction->setOnset($data['begdate']);
        $reaction->setSeverity($data['severity_al']);
        
        $resource = new FHIRAllergyIntolerance();
        $resource->setClinicalStatus($clinicalStatusCoding);
        $resource->setType($type);
        $resource->addCategory($category);
        $resource->setOnsetDateTime($onSetDateTime);
        $resource->setRecordedDate($recordedDate);
        $resource->setCriticality($criticality);
        $resource->setPatient($patient);
        $resource->setRecorder($recorder);
        $resource->setAsserter($asserter);
        $resource->setLastOccurrence($lastOccurrence);
        $resource->addNote($note);
        $resource->addReaction($reaction);
        if ($encode) {
            return json_encode($resource);
        } else {
            return $resource;
        }
    }
}
