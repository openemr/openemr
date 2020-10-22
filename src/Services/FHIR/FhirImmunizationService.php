<?php

namespace OpenEMR\Services\FHIR;

use OpenEMR\Services\FHIR\FhirServiceBase;
use OpenEMR\Services\ImmunizationService;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRImmunization;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRDate;
use OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRImmunizationStatusCodes;
use OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\FHIR\R4\FHIRResource\FHIRImmunization\FHIRImmunizationEducation;

/**
 * FHIR Immunization Service
 *
 * @coversDefaultClass OpenEMR\Services\FHIR\FhirImmunizationService
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class FhirImmunizationService extends FhirServiceBase
{

    /**
     * @var ImmunizationService
     */
    private $immunizationService;

    public function __construct()
    {
        parent::__construct();
        $this->immunizationService = new ImmunizationService();
    }

    /**
     * Returns an array mapping FHIR Immunization Resource search parameters to OpenEMR Immunization search parameters
     * @return array The search parameters
     */
    protected function loadSearchParameters()
    {
        return  [
            "patient" => ["patient.uuid"]
        ];
    }

    /**
     * Parses an OpenEMR immunization record, returning the equivalent FHIR Immunization Resource
     *
     * @param array $dataRecord The source OpenEMR data record
     * @param boolean $encode Indicates if the returned resource is encoded into a string. Defaults to false.
     * @return FHIRImmunization
     */
    public function parseOpenEMRRecord($dataRecord = array(), $encode = false)
    {
        $immunizationResource = new FHIRImmunization();

        $meta = array('versionId' => '1', 'lastUpdated' => gmdate('c'));
        $immunizationResource->setMeta($meta);

        $id = new FHIRId();
        $id->setValue($dataRecord['uuid']);
        $immunizationResource->setId($id);

        $status = new FHIRImmunizationStatusCodes();
        $statusCoding = new FHIRCoding();
        $statusCoding->setSystem('http://hl7.org/fhir/event-status');
        if ($dataRecord['added_erroneously'] != "0") {
            $statusCoding->setCode("entered-in-error");
            $statusCoding->setDisplay("Entered in Error");
        } elseif ($dataRecord['completion_status'] == "Completed") {
            $statusCoding->setCode("completed");
            $statusCoding->setDisplay("Completed");
        } else {
            $statusCoding->setCode("not-done");
            $statusCoding->setDisplay("Not Done");
        }
        $status->setValue($statusCoding);
        $immunizationResource->setStatus($status);

        $statusReason = new FHIRCodeableConcept();
        $statusReason->addCoding(array(
            'system' => "http://terminology.hl7.org/CodeSystem/v3-ActReason",
            'code' => 'PATOBJ',
            'display' => 'patient objection'
        ));
        $immunizationResource->setStatusReason($statusReason);
        $immunizationResource->setPrimarySource($dataRecord['primarySource']);

        if (!empty($dataRecord['cvx_code'])) {
            $vaccineCode = new FHIRCodeableConcept();
            $vaccineCode->addCoding(array(
                'system' => "http://hl7.org/fhir/sid/cvx",
                'code' =>  $dataRecord['cvx_code'],
                'display' => $dataRecord['cvx_code_text']
            ));
            $immunizationResource->setVaccineCode($vaccineCode);
        }

        if (!empty($dataRecord['puuid'])) {
            $patient = new FHIRReference(['reference' => 'Patient/' . $dataRecord['puuid']]);
            $immunizationResource->setPatient($patient);
        }

        if (!empty($dataRecord['administered_date'])) {
            $occurenceDateTime = new FHIRDateTime();
            $occurenceDateTime->setValue($dataRecord['administered_date']);
            $immunizationResource->setOccurrenceDateTime($occurenceDateTime);
        }

        if (!empty($dataRecord['create_date'])) {
            $recorded = new FHIRDateTime();
            $recorded->setValue($dataRecord['create_date']);
            $immunizationResource->setRecorded($recorded);
        }

        if (!empty($dataRecord['expiration_date'])) {
            $expirationDate = new FHIRDate();
            $expirationDate->setValue($dataRecord['expiration_date']);
            $immunizationResource->setExpirationDate($expirationDate);
        }

        if (!empty($dataRecord['note'])) {
            $immunizationResource->addNote(array(
                'text' => $dataRecord['note']
            ));
        }

        if (!empty($dataRecord['administration_site'])) {
            $siteCode = new FHIRCodeableConcept();
            $siteCode->addCoding(array(
                'system' => "http://terminology.hl7.org/CodeSystem/v3-ActSite",
                'code' =>  $dataRecord['site_code'],
                'display' => $dataRecord['site_display']
            ));
            $immunizationResource->setSite($siteCode);
        }

        if (!empty($dataRecord['lot_number'])) {
            $immunizationResource->setLotNumber($dataRecord['lot_number']);
        }

        if (!empty($dataRecord['administration_site'])) {
            $doseQuantity = new FHIRQuantity();
            $doseQuantity->setValue($dataRecord['amount_administered']);
            $doseQuantity->setSystem($dataRecord['http://unitsofmeasure.org']);
            $doseQuantity->setCode($dataRecord['amount_administered_unit']);
            $immunizationResource->setDoseQuantity($doseQuantity);
        }

        if (!empty($dataRecord['education_date'])) {
            $education = new FHIRImmunizationEducation();
            $educationDateTime = new FHIRDateTime();
            $educationDateTime->setValue($dataRecord['education_date']);
            $education->setPresentationDate($educationDateTime);
            $immunizationResource->addEducation($education);
        }

        if ($encode) {
            return json_encode($immunizationResource);
        } else {
            return $immunizationResource;
        }
    }

    /**
     * Parses a FHIR Immunization Resource, returning the equivalent OpenEMR immunization record.
     *
     * @param array $fhirResource The source FHIR resource
     * @return array a mapped OpenEMR data record (array)
     */
    public function parseFhirResource($fhirResource = array())
    {
    }

    /**
     * Inserts an OpenEMR record into the system.
     *
     * @param array $openEmrRecord OpenEMR immunization record
     * @return ProcessingResult
     */
    public function insertOpenEMRRecord($openEmrRecord)
    {
        // return $this->immunizationService->insert($openEmrRecord);
    }


    /**
     * Updates an existing OpenEMR record.
     *
     * @param $fhirResourceId //The OpenEMR record's FHIR Resource ID.
     * @param $updatedOpenEMRRecord //The "updated" OpenEMR record.
     * @return ProcessingResult
     */
    public function updateOpenEMRRecord($fhirResourceId, $updatedOpenEMRRecord)
    {
        // $processingResult = $this->immunizationService->update($fhirResourceId, $updatedOpenEMRRecord);
        // return $processingResult;
    }

    /**
     * Performs a FHIR Immunization Resource lookup by FHIR Resource ID
     * @param $fhirResourceId //The OpenEMR record's FHIR Immunization Resource ID.
     */
    public function getOne($fhirResourceId)
    {
        $processingResult = $this->immunizationService->getOne($fhirResourceId);
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
    public function searchForOpenEMRRecords($openEMRSearchParameters)
    {
        return $this->immunizationService->getAll($openEMRSearchParameters, false);
    }
    public function createProvenanceResource($dataRecord = array(), $encode = false)
    {
        // TODO: If Required in Future
    }
}
