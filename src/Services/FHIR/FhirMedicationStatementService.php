<?php

namespace OpenEMR\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedicationStatement;
use OpenEMR\FHIR\R4\FHIRElement\FHIRAnnotation;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMedicationStatusCodes;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\FHIR\R4\FHIRElement\FHIRString;
use OpenEMR\FHIR\R4\FHIRElement\FHIRUnitsOfTime;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleLink;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDosage;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDosage\FHIRDosageDoseAndRate;
use OpenEMR\FHIR\R4\FHIRResource\FHIRTiming;
use OpenEMR\FHIR\R4\FHIRResource\FHIRTiming\FHIRTimingRepeat;
use OpenEMR\FHIR\R4\PHPFHIRResponseParser;

class FhirMedicationStatementService
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
        return "SELECT id,
                    active,
                    end_date,
                    drug,
                    rxnorm_drugcode,
                    patient_id,
                    encounter,
                    date_added,
                    provider_id,
                    note,
                    route,
                    unit
                    FROM prescriptions";
    }

    public function getAll($search)
    {
        $SQL = $this->get();
        if (isset($search['patient'])) {
            $SQL .= " WHERE patient_id = ?;";
        }

        $medicationStatementResults = sqlStatement($SQL, $search['patient']);
        $results = array();
        while ($row = sqlFetchArray($medicationStatementResults)) {
            array_push($results, $row);
        }

        return $results;
    }

    public function getOne($id)
    {
        $SQL = $this->get();
        $SQL .= " WHERE id = ? ";

        $sqlResult = sqlStatement($SQL, $id);
        $result = sqlFetchArray($sqlResult);
        return $result;
    }

    public function createMedicationStatementResource($id = '', $data = '', $encode = true)
    {
        $status = new FHIRMedicationStatusCodes();
        $statusString = new FHIRString();
        if (isset($data['end_date'])) {
            $statusString->setValue("completed");
        } elseif ($data['active'] == '1') {
            $statusString->setValue("active");
        } else {
            $statusString->setValue("stopped");
        }
        $status->setValue($statusString);

        $medication = new FHIRCodeableConcept();
        $medicationCoding = new FHIRCoding();
        $medicationCoding->setSystem("http://www.nlm.nih.gov/research/umls/rxnorm");
        $medicationCoding->setCode($data['rxnorm_drugcode']);
        $medicationCoding->setDisplay($data['drug']);
        $medication->addCoding($medicationCoding);

        $subject = new FHIRReference();
        $subject->setReference('Patient/' . $data['patient_id']);

        if (isset($data['encounter'])) {
            $context = new FHIRReference();
            $context->setReference('Encounter/' . $data['encounter']);
        }

        $dateAsserted = new FHIRDateTime();
        $dateAsserted->setValue($data['date_added']);

        $informationSource = new FHIRReference();
        $informationSource->setReference('Practitioner/' . $data['provider_id']);

        $note = new FHIRAnnotation();
        $note->setText($data['note']);  

        list($unitValue) = [
            '0' => [''],
            '1' => ['mg'],
            '2' => ['mg/1cc'],
            '3' => ['mg/2cc'],
            '4' => ['mg/3cc'],
            '5' => ['mg/4cc'],
            '6' => ['mg/5cc'],
            '7' => ['mcg'],
            '8' => ['grams'],
            '9' => ['mL']
        ][$data['unit']] ?? [''];
		
        $unit = new FHIRUnitsOfTime();
        $unit->setValue($unitValue);

        $decimal = new FHIRDecimal();
        $decimal->setValue($data['interval']);

        $repeat = new FHIRTimingRepeat();
        $repeat->setPeriodUnit($unit);
        $repeat->setPeriod($decimal);

        $timing = new FHIRTiming();
        $timing->setRepeat($repeat);

        list($routeValue, $routeCode) = [
            '0' => ['', ''],
            '1' => ['ORAL', 'C38288'],
            '2' => ['RECTAL', 'C38295'],
            '3' => ['CUTANEOUS', 'C38675'],
            '4' => ['To Affected Area', ''],
            '5' => ['SUBLINGUAL', 'C38300'],
            '6' => ['ORAL', 'C38288'],
            '7' => ['OD', ''],
            '8' => ['OPHTHALMIC', 'C38287'],
            '9' => ['SUBCUTANEOUS', 'C38299'],
            '10' => ['INTRAMUSCULAR', 'C28161'],
            '11' => ['INTRAVENOUS', 'C38276'],
            '12' => ['NASAL', 'C38284'],
            '13' => ['AURICULAR (OTIC)', 'C38192'],
            '14' => ['AURICULAR (OTIC)', 'C38192'],
            '15' => ['AURICULAR (OTIC)', 'C38192'],
            '16' => ['INTRADERMAL', 'C38238'],
            '18' => ['OTHER', 'C38290'],
            '19' => ['TRANSDERMAL', 'C38305'],
            '20' => ['INTRAMUSCULAR', 'C28161'],
        ][$data['route']] ?? ['', ''];

        $route = new FHIRCodeableConcept();
        $routeCoding = new FHIRCoding();
        $routeCoding->setSystem("http://ncimeta.nci.nih.gov");
        $routeCoding->setCode($routeCode);
        $routeCoding->setDisplay($routeValue);
        $route->addCoding($routeCoding);

        $dosage = new FHIRDosage();
        $dosage->setRoute($route);
        $dosage->setTiming($timing);

        $resource = new FHIRMedicationStatement();
        $resource->setStatus($status);
        $resource->setMedicationCodeableConcept($medication);
        $resource->setSubject($subject);
        $resource->setContext($context);
        $resource->setDateAsserted($dateAsserted);
        $resource->setInformationSource($informationSource);
        $resource->addNote($note);
        $resource->addDosage($dosage);

        if ($encode) {
            return json_encode($resource);
        } else {
            return $resource;
        }
    }
}
