<?php

namespace OpenEMR\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedication;
use OpenEMR\FHIR\R4\FHIRElement\FHIRAnnotation;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMedicationStatusCodes;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRRatio;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\FHIR\R4\FHIRElement\FHIRString;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleLink;
use OpenEMR\FHIR\R4\FHIRResource\FHIRMedication\FHIRMedicationBatch;
use OpenEMR\FHIR\R4\PHPFHIRResponseParser;
use OpenEMR\Services\ListService;

class FhirMedicationService
{

    private $id;

    public function __construct()
    {
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function get() {
        return "SELECT d.name,
                    d.ndc_number,
                    d.active,
                    d.form,
                    di.manufacturer,
                    di.lot_number,
                    di.expiration
                    FROM drugs AS d
                    LEFT JOIN drug_inventory AS di 
                    ON d.drug_id = di.drug_id";
    }

    public function getAll()
    {
        $SQL = $this->get();

        $medicationResults = sqlStatement($SQL);
        $results = array();
        while ($row = sqlFetchArray($medicationResults)) {
            array_push($results, $row);
        }
        return $results;
    }

    public function getOne($id)
    {
        $SQL = $this->get();
        $SQL .= " WHERE d.drug_id = ? ";

        $sqlResult = sqlStatement($SQL, $id);
        $result = sqlFetchArray($sqlResult);

        return $result;
    }

    public function createMedicationResource($id = '', $data = '', $encode = true)
    {
        $code = new FHIRCodeableConcept();
        $codeCoding = new FHIRCoding();
        $codeCoding->setSystem("http://hl7.org/fhir/sid/ndc");
        $codeCoding->setCode($data['ndc_number']);
        $codeCoding->setDisplay($data['name']);
        $code->addCoding($codeCoding);

        $status = new FHIRString();

        if ($data['active'] == '1')
            $status->setValue("active");
        else
            $status->setValue("inactive");

        $manufacturer = new FHIRReference();
        $manufacturer->setReference('Organization/' . $data['manufacturer']);

        $form = new FHIRCodeableConcept();
        $formCoding = new FHIRCoding();
        $formCoding->setSystem("http://ncimeta.nci.nih.gov");

        //alternative for switch case
        list($formValue, $formCode) = [
            '1' => ['suspension', 'C60928'],
            '2' => ['tablet', 'C42998'],
            '3' => ['capsule', 'C25158'],
            '4' => ['solution', 'C42986'],
            '5' => ['tsp', 'C48544'],
            '6' => ['ml', 'C28254'],
            '7' => ['units', 'C44278'],
            '8' => ['inhalation', 'C42944'],
            '9' => ['gtts(drops)', 'C48491'],
            '10' => ['cream', 'C28944'],
            '11' => ['ointment', 'C42966'],
            '12' => ['puff', 'C42944']
        ][$data['form']] ?? ['', ''];

        $formCoding->setCode($formCode);
        $formCoding->setDisplay($formValue);
        $form->addCoding($formCoding);

        $lotNumber = new FHIRString();
        $lotNumber->setValue($data['lot_number']);
        $expirationDate = new FHIRDateTime();
        $expirationDate->setValue($data['expiration']);
        $batch = new FHIRMedicationBatch();
        $batch->setLotNumber($lotNumber);
        $batch->setExpirationDate($expiration);

        $resource = new FHIRMedication();
        $resource->setCode($code);
        $resource->setStatus($status);
        $resource->setManufacturer($manufacturer);
        $resource->setForm($form);
        $resource->setBatch($batch);

        if ($encode) {
            return json_encode($resource);
        } else {
            return $resource;
        }
    }
}
