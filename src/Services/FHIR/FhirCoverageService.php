<?php

namespace OpenEMR\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCode;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCoverage;
use OpenEMR\Services\FHIR\FhirServiceBase;
use OpenEMR\Services\InsuranceService;
use OpenEMR\Validators\ProcessingResult;

/**
 * FHIR Coverage Service
 *
 * @coversDefaultClass OpenEMR\Services\FHIR\FhirCoverageService
 * @package            OpenEMR
 * @link               http://www.open-emr.org
 * @author             Vishnu Yarmaneni <vardhanvishnu@gmail.com>
 * @copyright          Copyright (c) 2021 Vishnu Yarmaneni <vardhanvishnu@gmail.com>
 * @license            https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

class FhirCoverageService extends FhirServiceBase
{
    /**
     * @var CoverageService
     */
    private $coverageService;

    public function __construct()
    {
        parent::__construct();
        $this->coverageService = new InsuranceService();
    }

    /**
     * Returns an array mapping FHIR Coverage Resource search parameters to OpenEMR Condition search parameters
     *
     * @return array The search parameters
     */
    protected function loadSearchParameters()
    {
        return  [
            'patient' => ['pid'],
            '_id' => ['id'],
            'payor' => ['provider']
        ];
    }

    /**
     * Parses an OpenEMR Insurance record, returning the equivalent FHIR Coverage Resource
     *
     * @param  array   $dataRecord The source OpenEMR data record
     * @param  boolean $encode     Indicates if the returned resource is encoded into a string. Defaults to false.
     * @return FHIRCoverage
     */
    public function parseOpenEMRRecord($dataRecord = array(), $encode = false)
    {
        $coverageResource = new FHIRCoverage();
        $meta = array('versionId' => '1', 'lastUpdated' => gmdate('c'));
        $coverageResource->setMeta($meta);

        $id = new FHIRId();
        $id->setValue($dataRecord['uuid']);
        $coverageResource->setId($id);


        if (isset($dataRecord['puuid'])) {
            $patient = new FHIRReference();
            $patient->setReference('Patient/' . $dataRecord['puuid']);
            $coverageResource->setBeneficiary($patient);
        }
        if (isset($dataRecord['insureruuid'])) {
            $payor = new FHIRReference();
            $payor->setReference('Organization/' . $dataRecord['insureruuid']);
            $coverageResource->addPayor($payor);
        }
        if (isset($dataRecord['subscriber_relationship'])) {
            $relationship = new FHIRCoding();
            $relationship->setSystem("http://terminology.hl7.org/CodeSystem/subscriber-relationship");
            $relationshipCode = new FHIRCodeableConcept();
            $relationship->setCode($dataRecord['subscriber_relationship']);
            $relationshipCode->addCoding($relationship);
            $coverageResource->setRelationship($relationshipCode);
        }
        //Currently Setting status to active - Change after status logic is confirmed
        $status = new FHIRCode();
        $status->setValue("active");
        $coverageResource->setStatus($status);



        if ($encode) {
            return json_encode($coverageResource);
        } else {
            return $coverageResource;
        }
    }


    /**
     * Performs a FHIR Coverage Resource lookup by FHIR Resource ID
     *
     * @param $fhirResourceId //The OpenEMR record's FHIR Condition Resource ID.
     */
    public function getOne($fhirResourceId)
    {
        $processingResult = $this->coverageService->getOne($fhirResourceId);
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
        return $this->coverageService->getAll($openEMRSearchParameters, false);
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
