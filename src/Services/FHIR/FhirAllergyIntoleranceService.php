<?php

namespace OpenEMR\Services\FHIR;

use OpenEMR\Services\FHIR\FhirServiceBase;
use OpenEMR\Services\AllergyIntoleranceService;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRAllergyIntolerance;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRProvenance;
use OpenEMR\FHIR\R4\FHIRResource\FHIRProvenance\FHIRProvenanceAgent;
use OpenEMR\FHIR\R4\FHIRElement\FHIRAddress;
use OpenEMR\FHIR\R4\FHIRElement\FHIRHumanName;
use OpenEMR\FHIR\R4\FHIRElement\FHIRAdministrativeGender;
use OpenEMR\FHIR\R4\FHIRElement\FHIRAllergyIntoleranceCategory;
use OpenEMR\FHIR\R4\FHIRElement\FHIRAllergyIntoleranceCriticality;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\Validators\ProcessingResult;
use OpenEMR\Common\Uuid;
use OpenEMR\Common\Uuid\UuidRegistry;

/**
 * FHIR AllergyIntolerance Service
 *
 * @coversDefaultClass OpenEMR\Services\FHIR\FhirAllergyIntoleranceService
 * @package   OpenEMR
 * @author    Yash Bothra <yashrajbothra786gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786gmail.com>
 * @link      http://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */
class FhirAllergyIntoleranceService extends FhirServiceBase
{
    /**
     * @var AllergyIntoleranceService
     */
    private $allergyIntoleranceService;

    public function __construct()
    {
        parent::__construct();
        $this->allergyIntoleranceService = new AllergyIntoleranceService();
    }

    /**
     * Returns an array mapping FHIR AllergyIntolerance Resource search parameters to OpenEMR AllergyIntolerance search parameters
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
     * Parses an OpenEMR allergyIntolerance record, returning the equivalent FHIR AllergyIntolerance Resource
     *
     * @param array $dataRecord The source OpenEMR data record
     * @param boolean $encode Indicates if the returned resource is encoded into a string. Defaults to false.
     * @return FHIRAllergyIntolerance
     */
    public function createProvenanceResource($dataRecord = array(), $encode = false)
    {

        $allergyProvenance = new FHIRProvenance();
        $meta = array('versionId' => '1', 'lastUpdated' => gmdate('c'));
        $allergyProvenance->setMeta($meta);

        $id = new FHIRId();
        $uuidString = UuidRegistry::uuidToString((new UuidRegistry(['disable_tracker' => true]))->createUuid());
        $id->setValue($uuidString);
        $allergyProvenance->setId($id);

        if (isset($dataRecord['uuid'])) {
            $allergyReference = new FHIRReference();
            $allergyReference->setReference('AllergyIntolerance/' . $dataRecord['uuid']);
            $allergyProvenance->addTarget($allergyReference);
        }
        if (isset($dataRecord['date'])) {
            $allergyProvenance->setRecorded($dataRecord['date']);
        }
        if ((isset($dataRecord['practitioner'])) && isset($dataRecord['organization'])) {
            $agent = new FHIRProvenanceAgent();
            $agentType = new FHIRCodeableConcept();
            $agentTypeCoding = array(
                'system' => "http://terminology.hl7.org/CodeSystem/provenance-participant-type",
                'code' => 'author',
                'display' => 'Author',
            );
            $agentType->addCoding($agentTypeCoding);
            $agent->setType($agentType);
            $allergyProvenance->addAgent($agent);
            $agentWho = new FHIRReference();
            $agentWho->setReference('Practitioner/' . $dataRecord['practitioner']);
            $agent->setWho($agentWho);
            $agentBehalf = new FHIRReference();
            $agentBehalf->setReference('Organization/' . $dataRecord['organization']);
            $agent->setOnBehalfOf($agentBehalf);
        }
        if ($encode) {
            return json_encode($allergyProvenance);
        } else {
            return $allergyProvenance;
        }
    }

    /**
     * Parses an OpenEMR allergyIntolerance record, returning the equivalent FHIR AllergyIntolerance Resource
     *
     * @param array $dataRecord The source OpenEMR data record
     * @param boolean $encode Indicates if the returned resource is encoded into a string. Defaults to false.
     * @return FHIRAllergyIntolerance
     */
    public function parseOpenEMRRecord($dataRecord = array(), $encode = false)
    {
        $allergyIntoleranceResource = new FHIRAllergyIntolerance();

        $meta = array('versionId' => '1', 'lastUpdated' => gmdate('c'));
        $allergyIntoleranceResource->setMeta($meta);

        $id = new FHIRId();
        $id->setValue($dataRecord['uuid']);
        $allergyIntoleranceResource->setId($id);

        $clinicalStatus = "inactive";
        if ($dataRecord['outcome'] == '1' && isset($dataRecord['enddate'])) {
            $clinicalStatus = "resolved";
        } elseif (!isset($dataRecord['enddate'])) {
            $clinicalStatus = "active";
        }
        $clinical_Status = new FHIRCodeableConcept();
        $clinical_Status->addCoding(
            array(
            'system' => "http://terminology.hl7.org/CodeSystem/allergyintolerance-clinical",
            'code' => $clinicalStatus,
            'display' => ucwords($clinicalStatus),
            )
        );
        $allergyIntoleranceResource->setClinicalStatus($clinical_Status);

        $allergyIntoleranceCategory = new FHIRAllergyIntoleranceCategory();
        $allergyIntoleranceCategory->setValue("medication");
        $allergyIntoleranceResource->addCategory($allergyIntoleranceCategory);

        if (isset($dataRecord['severity_al'])) {
            $criticalityCode = array(
                "mild" => ["code" => "low", "display" => "Low Risk"],
                "mild_to_moderate" => ["code" => "low", "display" => "Low Risk"],
                "moderate" => ["code" => "low", "display" => "Low Risk"],
                "moderate_to_severe" => ["code" => "high", "display" => "High Risk"],
                "severe" => ["code" => "high", "display" => "High Risk"],
                "life_threatening_severity" => ["code" => "high", "display" => "High Risk"],
                "fatal" => ["code" => "high", "display" => "High Risk"],
                "unassigned" => ["code" => "unable-to-assess", "display" => "Unable to Assess Risk"],
            );
            $criticality = new FHIRAllergyIntoleranceCriticality();
            $criticality->setValue($criticalityCode[$dataRecord['severity_al']]['code']);
            $allergyIntoleranceResource->setCriticality($criticality);
        }

        if (isset($dataRecord['puuid'])) {
            $patient = new FHIRReference();
            $patient->setReference('Patient/' . $dataRecord['puuid']);
            $allergyIntoleranceResource->setPatient($patient);
        }

        if (isset($dataRecord['practitioner'])) {
            $recorder = new FHIRReference();
            $recorder->setReference('Practitioner/' . $dataRecord['practitioner']);
            $allergyIntoleranceResource->setRecorder($recorder);
        }

        if (!empty($dataRecord['diagnosis'])) {
            $diagnosisCoding = new FHIRCoding();
            $diagnosisCode = new FHIRCodeableConcept();
            foreach ($dataRecord['diagnosis'] as $code => $display) {
                $diagnosisCoding->setCode($code);
                $diagnosisCoding->setDisplay($display);
                $diagnosisCode->addCoding($diagnosisCoding);
            }
            $allergyIntoleranceResource->setCode($diagnosisCode);
        }

        $verificationStatus = new FHIRCodeableConcept();
        $verificationCoding = array(
            'system' => "http://terminology.hl7.org/CodeSystem/allergyintolerance-verification",
            'code' => 'unconfirmed',
            'display' => 'Unconfirmed',
        );
        if (!empty($dataRecord['verification'])) {
            $verificationCoding = array(
                'system' => "http://terminology.hl7.org/CodeSystem/allergyintolerance-verification",
                'code' => $dataRecord['verification'],
                'display' => $dataRecord['verification_title']
            );
        }
        $verificationStatus->addCoding($verificationCoding);
        $allergyIntoleranceResource->setVerificationStatus($verificationStatus);

        if ($encode) {
            return json_encode($allergyIntoleranceResource);
        } else {
            return $allergyIntoleranceResource;
        }
    }


    /**
     * Performs a FHIR AllergyIntolerance Resource lookup by FHIR Resource ID
     * @param $fhirResourceId //The OpenEMR record's FHIR AllergyIntolerance Resource ID.
     */
    public function getOne($fhirResourceId)
    {
        $processingResult = $this->allergyIntoleranceService->getOne($fhirResourceId);
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
        return $this->allergyIntoleranceService->getAll($openEMRSearchParameters, false);
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
}
