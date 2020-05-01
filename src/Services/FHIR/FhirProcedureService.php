<?php

namespace OpenEMR\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRProcedure;
use OpenEMR\FHIR\R4\FHIRElement\FHIRAnnotation;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIREventStatus;
use OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleLink;
use OpenEMR\FHIR\R4\FHIRResource\FHIRProcedure\FHIRProcedurePerformer;
use OpenEMR\FHIR\R4\PHPFHIRResponseParser;

class FhirProcedureService
{

    private $id;

    public function __construct()
    {
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function get()
    {
        return "SELECT ptype.procedure_code,
                        ptype.body_site,
                        ptype.notes,
                        pcode.procedure_name,
                        porder.procedure_order_id,
                        porder.patient_id,
                        porder.encounter_id,
                        porder.date_collected,
                        porder.provider_id,
                        porder.order_diagnosis,
                        porder.order_status,
                        presult.result_status
                        FROM procedure_order AS porder 
                        LEFT JOIN procedure_order_code AS pcode 
                        ON porder.procedure_order_id = pcode.procedure_order_id 
                        LEFT JOIN procedure_type AS ptype 
                        ON pcode.procedure_code = ptype.procedure_code 
                        LEFT JOIN procedure_report AS preport 
                        ON preport.procedure_order_id = porder.procedure_order_id 
                        LEFT JOIN procedure_result AS presult 
                        ON presult.procedure_report_id = preport.procedure_report_id";
    }

    public function getAll($search)
    {
        $SQL = $this->get();
        if (isset($search['patient'])) {
            $SQL .= " WHERE porder.patient_id = ?;";
        }

        $SQLFollowUpNotes = "SELECT p.body,
                                p.update_date
                                FROM pnotes AS p
                                LEFT JOIN gprelations AS r
                                ON p.id = r.id2
                                AND r.type1 = 2
                                AND r.id1 = ?
                                AND r.type2 = 6
                                AND p.pid != p.`user`;";

        $SQLProvider = "SELECT specialty, organization FROM users WHERE id = ?;";

        $procedureResults = sqlStatement($SQL, $search['patient']);
        $results = array();
        while ($row = sqlFetchArray($procedureResults)) {
            $provider = sqlQuery($SQLProvider, array($row['provider_id']));
            $row['function'] = $provider['specialty'];
            $row['organization'] = $provider['organization'];

            $followUpNotes = sqlQuery($SQLFollowUpNotes, array($row['procedure_order_id']));
            $row['followUp'] = $followUpNotes['body'] . " " . $followUpNotes['update_date'];

            array_push($results, $row);
        }

        return $results;
    }

    public function getOne($id)
    {
        $SQL = $this->get();
        $SQL .= " WHERE porder.procedure_order_id = ? ";

        $sqlResult = sqlStatement($SQL, $id);
        $result = sqlFetchArray($sqlResult);
        if ($result) {
            $SQLProvider = "SELECT specialty, organization FROM users WHERE id = ?";
            $provider = sqlQuery($SQLProvider, array($result['provider_id']));
            $result['function'] = $provider['specialty'];
            $result['organization'] = $provider['organization'];

            $SQLFollowUpNotes = "SELECT p.body,
                                    p.update_date
                                    FROM pnotes AS p
                                    LEFT JOIN gprelations AS r
                                    ON p.id = r.id2
                                    AND r.type1 = 2
                                    AND r.id1 = ?
                                    AND r.type2 = 6
                                    AND p.pid != p.`user`;";

            $followUpNotes = sqlQuery($SQLFollowUpNotes, array($result['procedure_order_id']));
            $result['followUp'] = $followUpNotes['body'] . " " . $followUpNotes['update_date'];
        }
        return $result;
    }

    public function createProcedureResource($id = '', $data = '', $encode = true)
    {
        $status = new FHIREventStatus();
        if ($data['order_status'] == "completed") {
            $status->setValue("completed");
        } elseif ($data['order_status'] == "pending") {
            $status->setValue("in-progress");
        } elseif ($data['order_status'] == "cancelled") {
            $status->setValue("stopped");
        } else {
            $status->setvalue("Unknown");
        }

        $procedureCode = new FHIRCodeableConcept();
        $procedureCodeCoding = new FHIRCoding();
        $procedureCodeCoding->setSystem("http://www.ama-assn.org/go/cpt");
        $procedureCodeCoding->setCode($data['procedure_code']);
        $procedureCodeCoding->setDisplay($data['procedure_name']);
        $procedureCode->addCoding($procedureCodeCoding);

        $subject = new FHIRReference();
        $subject->setReference('Patient/' . $data['patient_id']);

        $encounter = new FHIRReference();
        $encounter->setReference('Encounter/' . $data['encounter_id']);

        $performedDateTime = new FHIRDateTime();
        $performedDateTime->setValue($data['date_collected']);

        $recorder = new FHIRReference();
        $recorder->setReference('Practitioner/' . $data['provider_id']);

        $asserter = new FHIRReference();
        $asserter->setReference('Practitioner/' . $data['provider_id']);

        $function = new FHIRCodeableConcept();
        $functionCoding = new FHIRCoding();
        $functionCoding->setSystem("http://snomed.info/sct");
        $functionCoding->setCode($data['specialty']);
        $functionCoding->setDisplay($data['specialty']);
        $function->addCoding($functionCoding);

        $actor = new FHIRReference();
        $actor->setReference('Practitioner/' . $data['provider_id']);

        $onBehalfOf = new FHIRReference();
        $onBehalfOf->setReference('Organization/' . $data['organization']);

        $performer = new FHIRProcedurePerformer();
        $performer->setActor($actor);
        $performer->setFunction($function);
        $performer->setOnBehalfOf($onBehalfOf);

        $reasonCode = new FHIRCodeableConcept();
        $reasonCodeCoding = new FHIRCoding();
        $reasonCodeCoding->setSystem("http://hl7.org/fhir/sid/icd-10-cm");
        $reasonCodeCoding->setCode($data['order_diagnosis']);
        $reasonCodeCoding->setDisplay($data['order_diagnosis']);
        $reasonCode->addCoding($reasonCodeCoding);

        $outcome = new FHIRCodeableConcept();
        $outcomeCoding = new FHIRCoding();
        $outcomeCoding->setSystem("http://snomed.info/sct");
        if (strtolower($data['result_status']) == "corrected" || strtolower($data['result_status']) == "final") {
            $outcomeCoding->setCode("385669000");
            $outcomeCoding->setDisplay("Successful");
        } elseif (strtolower($data['result_status']) == "incomplete" || strtolower($data['result_status']) == "preliminary") {
            $outcomeCoding->setCode("385670004");
            $outcomeCoding->setDisplay("Partially successful");
        } else {
            $outcomeCoding->setCode("385671000");
            $outcomeCoding->setDisplay("Unsuccessful");
        }
        $outcomeCoding->setCode($data['body_site']);
        $outcomeCoding->setDisplay($data['body_site']);
        $outcome->addCoding($bodySiteCoding);

        $bodySite = new FHIRCodeableConcept();
        $bodySiteCoding = new FHIRCoding();
        $bodySiteCoding->setSystem("http://snomed.info/sct");
        $bodySiteCoding->setCode($data['body_site']);
        $bodySiteCoding->setDisplay($data['body_site']);
        $bodySite->addCoding($bodySiteCoding);

        $followUp = new FHIRCodeableConcept();
        $followUp->setText($data['followUp']);

        $note = new FHIRAnnotation();
        $note->setText($data['notes']);

        $resource = new FHIRProcedure();
        $resource->setStatus($status);
        $resource->setCode($procedureCode);
        $resource->setSubject($subject);
        $resource->setEncounter($encounter);
        $resource->setPerformedDateTime($performedDateTime);
        $resource->setRecorder($recorder);
        $resource->setAsserter($asserter);
        $resource->addPerformer($performer);
        $resource->addReasonCode($reasonCode);
        $resource->addBodySite($bodySite);
        $resource->setOutcome($outcome);
        $resource->addFollowUp($followUp);
        $resource->addNote($note);

        if ($encode) {
            return json_encode($resource);
        } else {
            return $resource;
        }
    }
}
