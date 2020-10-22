<?php

namespace OpenEMR\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedication;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRResource\FHIRMedication\FHIRMedicationBatch;
use OpenEMR\Services\DrugService;
use OpenEMR\Services\FHIR\FhirServiceBase;

/**
 * FHIR Medication Service
 *
 * @coversDefaultClass OpenEMR\Services\FHIR\FhirMedicationService
 * @package            OpenEMR
 * @link               http://www.open-emr.org
 * @author             Yash Bothra <yashrajbothra786gmail.com>
 * @copyright          Copyright (c) 2020 Yash Bothra <yashrajbothra786gmail.com>
 * @license            https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class FhirMedicationService extends FhirServiceBase
{
    /**
     * @var MedicationService
     */
    private $medicationService;

    public function __construct()
    {
        parent::__construct();
        $this->medicationService = new DrugService();
    }

    /**
     * Returns an array mapping FHIR Medication Resource search parameters to OpenEMR Medication search parameters
     *
     * @return array The search parameters
     */
    protected function loadSearchParameters()
    {
        return  [];
    }

    /**
     * Parses an OpenEMR medication record, returning the equivalent FHIR Medication Resource
     *
     * @param  array   $dataRecord The source OpenEMR data record
     * @param  boolean $encode     Indicates if the returned resource is encoded into a string. Defaults to false.
     * @return FHIRMedication
     */
    public function parseOpenEMRRecord($dataRecord = array(), $encode = false)
    {
        $medicationResource = new FHIRMedication();

        $meta = array('versionId' => '1', 'lastUpdated' => gmdate('c'));
        $medicationResource->setMeta($meta);

        $id = new FHIRId();
        $id->setValue($dataRecord['uuid']);
        $medicationResource->setId($id);

        if ($dataRecord['active'] == '1') {
            $medicationResource->setStatus("active");
        } else {
            $medicationResource->setStatus("inactive");
        }

        if (!empty($dataRecord['drug_code'])) {
            $medicationCoding = new FHIRCoding();
            $medicationCode = new FHIRCodeableConcept();
            foreach ($dataRecord['drug_code'] as $code => $display) {
                $medicationCoding->setSystem("http://www.nlm.nih.gov/research/umls/rxnorm");
                $medicationCoding->setCode($code);
                $medicationCoding->setDisplay($display);
                $medicationCode->addCoding($medicationCoding);
            }
            $medicationResource->setCode($medicationCode);
        }

        //alternative for switch case
        list($formDisplay, $formCode) = [
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
        ][$dataRecord['form']] ?? ['', ''];

        if (!empty($formCode)) {
            $form = new FHIRCodeableConcept();
            $formCoding = new FHIRCoding();
            $formCoding->setSystem("http://ncimeta.nci.nih.gov");
            $formCoding->setCode($formCode);
            $formCoding->setDisplay($formDisplay);
            $form->addCoding($formCoding);
        }

        if (isset($dataRecord['expiration']) || isset($dataRecord['expiration'])) {
            $batch = new FHIRMedicationBatch();
            if (isset($dataRecord['expiration'])) {
                $expirationDate = new FHIRDateTime();
                $expirationDate->setValue($dataRecord['expiration']);
                $batch->setExpirationDate($expirationDate);
            }
            if (isset($dataRecord['lot_number'])) {
                $batch->setLotNumber($dataRecord['lot_number']);
            }
            $medicationResource->setBatch($batch);
        }

        if ($encode) {
            return json_encode($medicationResource);
        } else {
            return $medicationResource;
        }
    }

    /**
     * Performs a FHIR Condition Resource lookup by FHIR Resource ID
     *
     * @param $fhirResourceId //The OpenEMR record's FHIR Condition Resource ID.
     */
    public function getOne($fhirResourceId)
    {
        $processingResult = $this->medicationService->getOne($fhirResourceId, true);
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
        return $this->medicationService->getAll($openEMRSearchParameters, false, true);
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
