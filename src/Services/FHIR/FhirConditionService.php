<?php

namespace OpenEMR\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCondition;
use OpenEMR\Services\FHIR\FhirServiceBase;
use OpenEMR\Services\ConditionService;
use OpenEMR\Validators\ProcessingResult;

/**
 * FHIR Condition Service
 *
 * @coversDefaultClass OpenEMR\Services\FHIR\FhirConditionService
 * @package            OpenEMR
 * @link               http://www.open-emr.org
 * @author             Yash Bothra <yashrajbothra786gmail.com>
 * @copyright          Copyright (c) 2020 Yash Bothra <yashrajbothra786gmail.com>
 * @license            https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class FhirConditionService extends FhirServiceBase
{
    /**
     * @var ConditionService
     */
    private $conditionService;

    public function __construct()
    {
        parent::__construct();
        $this->conditionService = new ConditionService();
    }

    /**
     * Returns an array mapping FHIR Condition Resource search parameters to OpenEMR Condition search parameters
     *
     * @return array The search parameters
     */
    protected function loadSearchParameters()
    {
        return  [
            'patient' => ['lists.pid'],
            '_id' => ['lists.id']
        ];
    }

    /**
     * Parses an OpenEMR condition record, returning the equivalent FHIR Condition Resource
     *
     * @param  array   $dataRecord The source OpenEMR data record
     * @param  boolean $encode     Indicates if the returned resource is encoded into a string. Defaults to false.
     * @return FHIRCondition
     */
    public function parseOpenEMRRecord($dataRecord = array(), $encode = false)
    {
        $conditionResource = new FHIRCondition();

        $meta = array('versionId' => '1', 'lastUpdated' => gmdate('c'));
        $conditionResource->setMeta($meta);

        $id = new FHIRId();
        $id->setValue($dataRecord['uuid']);
        $conditionResource->setId($id);

        $clinicalStatus = "inactive";
        $clinicalSysytem = "http://terminology.hl7.org/CodeSystem/condition-clinical";
        if (
            (!isset($dataRecord['enddate']) && isset($dataRecord['begdate']))
            || isset($dataRecord['enddate']) && strtotime($dataRecord['enddate']) >= strtotime("now")
        ) {
            // Active if Only Begin Date isset OR End Date isnot expired
            $clinicalStatus = "active";
            if ($dataRecord['occurrence'] == 1 || $dataRecord['outcome'] == 1) {
                $clinicalStatus = "resolved";
            } elseif ($dataRecord['occurrence'] > 1) {
                $clinicalStatus = "recurrence";
            }
        } elseif (isset($dataRecord['enddate']) && strtotime($dataRecord['enddate']) < strtotime("now")) {
            //Inactive if End Date is expired
            $clinicalStatus = "inactive";
        } else {
            $clinicalSysytem = "http://terminology.hl7.org/CodeSystem/data-absent-reason";
            $clinicalStatus = "unknown";
        }
        $clinical_Status = new FHIRCodeableConcept();
        $clinical_Status->addCoding(
            array(
            'system' => $clinicalSysytem,
            'code' => $clinicalStatus,
            'display' => ucwords($clinicalStatus),
            )
        );
        $conditionResource->setClinicalStatus($clinical_Status);

        $conditionCategory = new FHIRCodeableConcept();
        $conditionCategory->addCoding(
            array(
                'system' => "http://terminology.hl7.org/CodeSystem/condition-category",
                'code' => 'problem-list-item',
                'display' => 'Problem List Item'
            )
        );
        $conditionResource->addCategory($conditionCategory);

        if (isset($dataRecord['puuid'])) {
            $patient = new FHIRReference();
            $patient->setReference('Patient/' . $dataRecord['puuid']);
            $conditionResource->setSubject($patient);
        }

        if (isset($dataRecord['encounter_uuid'])) {
            $encounter = new FHIRReference();
            $encounter->setReference('Encounter/' . $dataRecord['encounter_uuid']);
            $conditionResource->setEncounter($encounter);
        }

        if (!empty($dataRecord['diagnosis'])) {
            $diagnosisCoding = new FHIRCoding();
            $diagnosisCode = new FHIRCodeableConcept();
            foreach ($dataRecord['diagnosis'] as $code => $display) {
                $diagnosisCoding->setCode($code);
                $diagnosisCoding->setDisplay($display);
                $diagnosisCode->addCoding($diagnosisCoding);
            }
            $conditionResource->setCode($diagnosisCode);
        }

        $verificationStatus = new FHIRCodeableConcept();
        $verificationCoding = array(
            'system' => "http://terminology.hl7.org/CodeSystem/condition-ver-status",
            'code' => 'unconfirmed',
            'display' => 'Unconfirmed',
        );
        if (!empty($dataRecord['verification'])) {
            $verificationCoding = array(
                'system' => "http://terminology.hl7.org/CodeSystem/condition-ver-status",
                'code' => $dataRecord['verification'],
                'display' => $dataRecord['verification_title']
            );
        }
        $verificationStatus->addCoding($verificationCoding);
        $conditionResource->setVerificationStatus($verificationStatus);

        if ($encode) {
            return json_encode($conditionResource);
        } else {
            return $conditionResource;
        }
    }


    /**
     * Performs a FHIR Condition Resource lookup by FHIR Resource ID
     *
     * @param $fhirResourceId //The OpenEMR record's FHIR Condition Resource ID.
     */
    public function getOne($fhirResourceId)
    {
        $processingResult = $this->conditionService->getOne($fhirResourceId);
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
        return $this->conditionService->getAll($openEMRSearchParameters, false);
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
