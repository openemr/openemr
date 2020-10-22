<?php

namespace OpenEMR\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedicationRequest;
use OpenEMR\FHIR\R4\FHIRElement\FHIRAnnotation;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\FHIR\R4\FHIRElement\FHIRUnitsOfTime;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDosage;
use OpenEMR\FHIR\R4\FHIRResource\FHIRTiming;
use OpenEMR\FHIR\R4\FHIRResource\FHIRTiming\FHIRTimingRepeat;
use OpenEMR\Services\PrescriptionService;

class FhirMedicationRequestService extends FhirServiceBase
{
    /**
     * @var PrescriptionService
     */
    private $prescriptionService;

    public function __construct()
    {
        parent::__construct();
        $this->prescriptionService = new PrescriptionService();
    }

    /**
     * Returns an array mapping FHIR Patient Resource search parameters to OpenEMR Patient search parameters
     * @return array The search parameters
     */
    protected function loadSearchParameters()
    {
        return  [
            'patient' => ['patient_id'],
        ];
    }

    /**
     * Parses an OpenEMR prescription record, returning the equivalent FHIR Patient Resource
     *
     * @param array $dataRecord The source OpenEMR data record
     * @param boolean $encode Indicates if the returned resource is encoded into a string. Defaults to false.
     * @return FHIRPatient
     */
    public function parseOpenEMRRecord($dataRecord = array(), $encode = false)
    {
        $medRequestResource = new FHIRMedicationRequest();

        $meta = array('versionId' => '1', 'lastUpdated' => gmdate('c'));
        $medRequestResource->setMeta($meta);

        $id = new FHIRId();
        $id->setValue($dataRecord['uuid']);
        $medRequestResource->setId($id);

        $medRequestResource->setIntent('order');

        if (isset($dataRecord['end_date']) && $dataRecord['active'] == '1') {
            $medRequestResource->setStatus("completed");
        } elseif ($dataRecord['active'] == '1') {
            $medRequestResource->setStatus("active");
        } else {
            $medRequestResource->setStatus("stopped");
        }

        if (!empty($dataRecord['rxnorm_drugcode'])) {
            $rxnormCoding = new FHIRCoding();
            $rxnormCode = new FHIRCodeableConcept();
            $rxnormCoding->setSystem("http://www.nlm.nih.gov/research/umls/rxnorm");
            foreach ($dataRecord['rxnorm_drugcode'] as $code => $display) {
                $rxnormCoding->setCode($code);
                $rxnormCoding->setDisplay($display);
                $rxnormCode->addCoding($rxnormCoding);
            }
            $medRequestResource->setMedicationCodeableConcept($rxnormCode);
        }

        if (!empty($dataRecord['drug_uuid'])) {
            $medication = new FHIRReference();
            $medication->setReference('Medication/' . $dataRecord['drug_uuid']);
            $medRequestResource->setMedicationReference($medication);
        }

        if (!empty($dataRecord['puuid'])) {
            $subject = new FHIRReference();
            $subject->setReference('Patient/' . $dataRecord['puuid']);
        }

        if (!empty($dataRecord['euuid'])) {
            $encounter = new FHIRReference();
            $encounter->setReference('Encounter/' . $dataRecord['euuid']);
            $medRequestResource->setEncounter($encounter);
        }

        if (!empty($dataRecord['date_added'])) {
            $authored_on = new FHIRDateTime();
            $authored_on->setValue($dataRecord['date_added']);
            $medRequestResource->setAuthoredOn($authored_on);
        }

        if (!empty($dataRecord['pruuid'])) {
            $requester = new FHIRReference();
            $requester->setReference('Practitioner/' . $dataRecord['pruuid']);
            $medRequestResource->setRequester($requester);
        }

        if (!empty($dataRecord['note'])) {
            $note = new FHIRAnnotation();
            $note->setText($dataRecord['note']);
        }

        if (!empty($dataRecord['unit'] || $dataRecord['interval'])) {
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
            ][$dataRecord['unit']] ?? [''];
            $unit = new FHIRUnitsOfTime();
            $unit->setValue($unitValue);
            $decimal = new FHIRDecimal();
            $decimal->setValue($dataRecord['interval']);
            $repeat = new FHIRTimingRepeat();
            $repeat->setPeriodUnit($unit);
            $repeat->setPeriod($decimal);
            $timing = new FHIRTiming();
            $timing->setRepeat($repeat);
        }

        if (!empty($dataRecord['route'])) {
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
            ][$dataRecord['route']] ?? ['', ''];
            $route = new FHIRCodeableConcept();
            $routeCoding = new FHIRCoding();
            $routeCoding->setSystem("http://ncimeta.nci.nih.gov");
            $routeCoding->setCode($routeCode);
            $routeCoding->setDisplay($routeValue);
            $route->addCoding($routeCoding);
            $dosage = new FHIRDosage();
            $dosage->setRoute($route);
            $dosage->setTiming($timing);
            $medRequestResource->addDosageInstruction($dosage);
        }
        if ($encode) {
            return json_encode($medRequestResource);
        } else {
            return $medRequestResource;
        }
    }

    /**
     * Performs a FHIR MedicationRequest Resource lookup by FHIR Resource ID
     *
     * @param $fhirResourceId //The OpenEMR record's FHIR MedicationRequest Resource ID.
     */
    public function getOne($fhirResourceId)
    {
        $processingResult = $this->procedureService->getOne($fhirResourceId);
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
     * @param  array openEMRSearchParameters OpenEMR search fields
     * @return ProcessingResult
     */
    public function searchForOpenEMRRecords($openEMRSearchParameters)
    {
        return $this->procedureService->getAll($openEMRSearchParameters, false);
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
