<?php

namespace OpenEMR\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRObservation;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\Services\FHIR\FhirServiceBase;
use OpenEMR\Services\ObservationLabService;
use OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity;

/**
 * FHIR Observation Service
 *
 * @coversDefaultClass OpenEMR\Services\FHIR\FhirObservationService
 * @package            OpenEMR
 * @link               http://www.open-emr.org
 * @author             Yash Bothra <yashrajbothra786gmail.com>
 * @copyright          Copyright (c) 2020 Yash Bothra <yashrajbothra786gmail.com>
 * @license            https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class FhirObservationService extends FhirServiceBase
{
    /**
     * @var ObservationLabService
     */
    private $observationService;

    public function __construct()
    {
        parent::__construct();
        $this->observationService = new ObservationLabService();
    }

    /**
     * Returns an array mapping FHIR Observation Resource search parameters to OpenEMR Observation search parameters
     *
     * @return array The search parameters
     */
    protected function loadSearchParameters()
    {
        return  [];
    }

    /**
     * Parses an OpenEMR observation record, returning the equivalent FHIR Observation Resource
     *
     * @param  array   $dataRecord The source OpenEMR data record
     * @param  boolean $encode     Indicates if the returned resource is encoded into a string. Defaults to false.
     * @return FHIRObservation
     */
    public function parseOpenEMRRecord($dataRecord = array(), $encode = false)
    {
        $observationResource = new FHIRObservation();

        $meta = array('versionId' => '1', 'lastUpdated' => gmdate('c'));
        $observationResource->setMeta($meta);

        $id = new FHIRId();
        $id->setValue($dataRecord['uuid']);
        $observationResource->setId($id);

        $categoryCoding = new FHIRCoding();
        $categoryCode = new FHIRCodeableConcept();
        if (!empty($dataRecord['procedure_code'])) {
            $categoryCoding->setCode($dataRecord['procedure_code']);
            $categoryCoding->setSystem('http://loinc.org');
            $categoryCoding->setDisplay($dataRecord['procedure_name']);
            $categoryCode->addCoding($categoryCoding);
            $observationResource->setCode($categoryCode);
        }

        $subject = new FHIRReference();
        $subject->setReference('Patient/' . $dataRecord['puuid']);
        $observationResource->setSubject($subject);

        if (!empty($dataRecord['date_report'])) {
            $observationResource->setEffectiveDateTime(gmdate('c', strtotime($dataRecord['date_report'])));
        }

        if (!empty($dataRecord['result_status'])) {
            $observationResource->setStatus(($dataRecord['result_status']));
        } else {
            $observationResource->setStatus("unknown");
        }

        if (!empty($dataRecord['units'])) {
            $observationResource->setValueQuantity($dataRecord['units']);
        }

        if (!empty($dataRecord['range'])) {
            $observationResource->setValueRange($dataRecord['range']);
        }

        if (!empty($dataRecord['result'])) {
            $quantity = new FHIRQuantity();
            $quantity->setValue($dataRecord['result']);
            if (!empty($dataRecord['units'])) {
                $quantity->setUnit($dataRecord['units']);
            }
            $observationResource->setValueQuantity($quantity);
        }


        if (!empty($dataRecord['comments'])) {
            $observationResource->addNote(['text' => $dataRecord['comments']]);
        }

        if ($encode) {
            return json_encode($observationResource);
        } else {
            return $observationResource;
        }
    }

    /**
     * Performs a FHIR Observation Resource lookup by FHIR Resource ID
     *
     * @param $fhirResourceId //The OpenEMR record's FHIR Observation Resource ID.
     */
    public function getOne($fhirResourceId)
    {
        $processingResult = $this->observationService->getOne($fhirResourceId);
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
        return $this->observationService->getAll($openEMRSearchParameters, false);
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
