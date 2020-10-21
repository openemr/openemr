<?php

namespace OpenEMR\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRProcedure;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\Services\FHIR\FhirServiceBase;
use OpenEMR\Services\ProcedureService;

/**
 * FHIR Procedure Service
 *
 * @coversDefaultClass OpenEMR\Services\FHIR\FhirProcedureService
 * @package            OpenEMR
 * @link               http://www.open-emr.org
 * @author             Yash Bothra <yashrajbothra786gmail.com>
 * @copyright          Copyright (c) 2020 Yash Bothra <yashrajbothra786gmail.com>
 * @license            https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class FhirProcedureService extends FhirServiceBase
{
    /**
     * @var ProcedureService
     */
    private $procedureService;

    public function __construct()
    {
        parent::__construct();
        $this->procedureService = new ProcedureService();
    }

    /**
     * Returns an array mapping FHIR Procedure Resource search parameters to OpenEMR Procedure search parameters
     *
     * @return array The search parameters
     */
    protected function loadSearchParameters()
    {
        return  [
            'patient' => ['patient.uuid']
        ];
    }

    /**
     * Parses an OpenEMR procedure record, returning the equivalent FHIR Procedure Resource
     *
     * @param  array   $dataRecord The source OpenEMR data record
     * @param  boolean $encode     Indicates if the returned resource is encoded into a string. Defaults to false.
     * @return FHIRProcedure
     */
    public function parseOpenEMRRecord($dataRecord = array(), $encode = false)
    {
        $procedureResource = new FHIRProcedure();

        $meta = array('versionId' => '1', 'lastUpdated' => gmdate('c'));
        $procedureResource->setMeta($meta);

        $id = new FHIRId();
        $id->setValue($dataRecord['uuid']);
        $procedureResource->setId($id);

        $subject = new FHIRReference();
        $subject->setReference('Patient/' . $dataRecord['puuid']);
        $procedureResource->setSubject($subject);

        $encounter = new FHIRReference();
        $encounter->setReference('Encounter/' . $dataRecord['euuid']);
        $procedureResource->setEncounter($encounter);

        $practitioner = new FHIRReference();
        $practitioner->setReference('Practitioner/' . $dataRecord['pruuid']);
        $procedureResource->addPerformer(['actor' => $practitioner]);

        if ($dataRecord['order_status'] == "completed") {
            $procedureResource->setStatus("completed");
        } elseif ($dataRecord['order_status'] == "pending") {
            $procedureResource->setStatus("in-progress");
        } elseif ($dataRecord['order_status'] == "cancelled") {
            $procedureResource->setStatus("stopped");
        } else {
            $procedureResource->setStatus("unknown");
        }

        if (!empty($dataRecord['diagnoses'])) {
            foreach ($dataRecord['diagnoses'] as $code) {
                $diagnosisCoding = new FHIRCoding();
                $diagnosisCode = new FHIRCodeableConcept();
                if ($code[0] == "ICD10") {
                    $diagnosisCoding->setSystem("http://hl7.org/fhir/sid/icd-10");
                    $diagnosisCoding->setCode($code[1]);
                    $diagnosisCode->addCoding($diagnosisCoding);
                    $procedureResource->addReasonCode($diagnosisCode);
                }
            }
        }
        if (!empty($dataRecord['procedure_code'])) {
            $procedureCoding = new FHIRCoding();
            $procedureCode = new FHIRCodeableConcept();
            $procedureCoding->setCode($dataRecord['procedure_code']);
            $procedureCode->addCoding($procedureCoding);
            $procedureResource->setCode($procedureCode);
        }

        if (!empty($dataRecord['date_collected'])) {
            $procedureResource->setPerformedDateTime(gmdate('c', strtotime($dataRecord['date_collected'])));
        }

        if (!empty($dataRecord['notes'])) {
            $procedureResource->addNote(['text' => $dataRecord['notes']]);
        }

        if ($encode) {
            return json_encode($procedureResource);
        } else {
            return $procedureResource;
        }
    }

    /**
     * Performs a FHIR Procedure Resource lookup by FHIR Resource ID
     *
     * @param $fhirResourceId //The OpenEMR record's FHIR Procedure Resource ID.
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
