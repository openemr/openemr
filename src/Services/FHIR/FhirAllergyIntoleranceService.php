<?php

namespace OpenEMR\Services\FHIR;

use Google\Service;
use OpenEMR\FHIR\Export\ExportCannotEncodeException;
use OpenEMR\FHIR\Export\ExportException;
use OpenEMR\FHIR\Export\ExportJob;
use OpenEMR\FHIR\Export\ExportStreamWriter;
use OpenEMR\FHIR\Export\ExportWillShutdownException;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRUri;
use OpenEMR\FHIR\R4\FHIRResource\FHIRAllergyIntolerance\FHIRAllergyIntoleranceReaction;
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
use OpenEMR\Services\FHIR\Traits\BulkExportSupportAllOperationsTrait;
use OpenEMR\Services\FHIR\Traits\FhirBulkExportDomainResourceTrait;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\PractitionerService;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\ReferenceSearchValue;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;
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
class FhirAllergyIntoleranceService extends FhirServiceBase implements IResourceUSCIGProfileService, IPatientCompartmentResourceService, IFhirExportableResourceService
{
    use FhirServiceBaseEmptyTrait;
    use BulkExportSupportAllOperationsTrait;
    use FhirBulkExportDomainResourceTrait;

    /**
     * @var AllergyIntoleranceService
     */
    private $allergyIntoleranceService;

    const USCGI_PROFILE_URI = 'http://hl7.org/fhir/us/core/StructureDefinition/us-core-allergyintolerance';

    public function __construct($fhirAPIURL = null)
    {
        parent::__construct($fhirAPIURL);
        $this->allergyIntoleranceService = new AllergyIntoleranceService();
    }

    /**
     * Returns an array mapping FHIR AllergyIntolerance Resource search parameters to OpenEMR AllergyIntolerance search parameters
     * @return array The search parameters
     */
    protected function loadSearchParameters()
    {
        return  [
            'patient' => $this->getPatientContextSearchField(),
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, [new ServiceField('allergy_uuid', ServiceField::TYPE_UUID)]),
        ];
    }


    /**
     * Parses an OpenEMR allergyIntolerance record, returning the equivalent FHIR AllergyIntolerance Resource
     *
     * @param array $dataRecord The source OpenEMR data record
     * @param boolean $encode Indicates if the returned resource is encoded into a string. Defaults to false.
     * @return FHIRAllergyIntolerance
     */
    public function createProvenanceResource($dataRecord, $encode = false)
    {
        if (!($dataRecord instanceof FHIRAllergyIntolerance)) {
            throw new \BadMethodCallException("Data record should be correct instance class");
        }
        $fhirProvenanceService = new FhirProvenanceService();
        $fhirProvenance = $fhirProvenanceService->createProvenanceForDomainResource($dataRecord, $dataRecord->getRecorder());
        if ($encode) {
            return json_encode($fhirProvenance);
        } else {
            return $fhirProvenance;
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
        $fhirMeta = new FHIRMeta();
        $fhirMeta->setVersionId("1");
        $fhirMeta->setLastUpdated(UtilsService::getDateFormattedAsUTC());
        $allergyIntoleranceResource->setMeta($fhirMeta);

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
        // @see https://www.hl7.org/fhir/us/core/StructureDefinition-us-core-allergyintolerance-definitions.html#AllergyIntolerance.category
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

        if (isset($dataRecord['practitioner']) && !empty($dataRecord['practitioner_npi'])) {
            $recorder = new FHIRReference();
            $recorder->setReference('Practitioner/' . $dataRecord['practitioner']);
            $allergyIntoleranceResource->setRecorder($recorder);
        }

        // cardinality is 0..*
        // however in OpenEMR we currently only track a single reaction, we will populate it if we have it.
        // if a reaction is unassigned, it has no codes and so we will skip over this as it has no meaning in FHIR.
        if (!empty($dataRecord['reaction']) && $dataRecord['reaction'] !== 'unassigned') {
            $reaction = new FHIRAllergyIntoleranceReaction();
            $reactionConcept = new FHIRCodeableConcept();
            $conceptText = $dataRecord['reaction_title'] ?? "";
            $reactionConcept->setText($conceptText);

            foreach ($dataRecord['reaction'] as $code => $codeValues) {
                $reactionCoding = new FHIRCoding();
                // some of our codes are parsed as numbers on the underlying service.. and we need to force them as
                // strings
                if (is_numeric($code)) {
                    $code = "$code";
                }

                $reactionCoding->setCode($code);
                $display = !empty($display) ? $codeValues['description'] : $dataRecord['reaction_title'];
                // we trim as some of the database values have white space which violates ONC spec
                $reactionCoding->setDisplay(trim($display));
                // @see http://hl7.org/fhir/R4/valueset-clinical-findings.html
                $reactionCoding->setSystem($codeValues['system']);
                $reactionConcept->addCoding($reactionCoding);
            }
            $reaction->addManifestation($reactionConcept);
            $allergyIntoleranceResource->addReaction($reaction);
        } else {
            $reaction = new FHIRAllergyIntoleranceReaction();
            $reaction->addManifestation(UtilsService::createDataAbsentUnknownCodeableConcept());
        }

        if (!empty($dataRecord['diagnosis'])) {
            $diagnosisCoding = new FHIRCoding();
            $diagnosisCode = new FHIRCodeableConcept();
            foreach ($dataRecord['diagnosis'] as $code => $codeValues) {
                // some of our codes are parsed as numbers on the underlying service.. and we need to force them as
                // strings
                if (is_numeric($code)) {
                    $code = "$code";
                }
                $diagnosisCoding->setCode($code);
                // if we have no display value we will just show the code value here
                $display = !empty($codeValues['description']) ? $codeValues['description'] : $dataRecord['title'];
                // we trim as some of the database values have white space which violates ONC spec
                $diagnosisCoding->setDisplay(trim($display));
                $diagnosisCoding->setSystem($codeValues['system']);
                $diagnosisCode->addCoding($diagnosisCoding);
            }
            $allergyIntoleranceResource->setCode($diagnosisCode);
        } else {
            $allergyIntoleranceResource->setCode(UtilsService::createDataAbsentUnknownCodeableConcept());
        }
        // we don't have title anywhere else so we mark it as an additional narrative.  If we don't have an actual code
        // this becomes very helpful.
        $allergyIntoleranceResource->setText(UtilsService::createNarrative($dataRecord['title'], "additional"));

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
     * Searches for OpenEMR records using OpenEMR search parameters
     *
     * @param array openEMRSearchParameters OpenEMR search fields
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @return ProcessingResult
     */
    protected function searchForOpenEMRRecords($openEMRSearchParameters, $puuidBind = null): ProcessingResult
    {
        return $this->allergyIntoleranceService->search($openEMRSearchParameters, true, $puuidBind);
    }

    public function getProfileURIs(): array
    {
        return [self::USCGI_PROFILE_URI];
    }

    public function getPatientContextSearchField(): FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('patient', SearchFieldType::REFERENCE, [new ServiceField('puuid', ServiceField::TYPE_UUID)]);
    }
}
