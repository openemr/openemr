<?php

namespace OpenEMR\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRAllergyIntolerance;
use OpenEMR\FHIR\R4\FHIRElement\FHIRAllergyIntoleranceCategory;
use OpenEMR\FHIR\R4\FHIRElement\FHIRAllergyIntoleranceCriticality;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\FHIR\R4\FHIRResource\FHIRAllergyIntolerance\FHIRAllergyIntoleranceReaction;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;
use OpenEMR\Services\AllergyIntoleranceService;
use OpenEMR\Services\FHIR\Traits\BulkExportSupportAllOperationsTrait;
use OpenEMR\Services\FHIR\Traits\FhirBulkExportDomainResourceTrait;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\FHIR\Traits\VersionedProfileTrait;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Validators\ProcessingResult;

/**
 * FHIR AllergyIntolerance Service
 *
 * @package   OpenEMR
 * @author    Yash Bothra <yashrajbothra786gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786gmail.com>
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class FhirAllergyIntoleranceService extends FhirServiceBase implements IResourceUSCIGProfileService, IPatientCompartmentResourceService, IFhirExportableResourceService
{
    use FhirServiceBaseEmptyTrait;
    use BulkExportSupportAllOperationsTrait;
    use FhirBulkExportDomainResourceTrait;
    use VersionedProfileTrait;

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
            '_lastUpdated' => $this->getLastModifiedSearchField(),
        ];
    }

    public function getLastModifiedSearchField(): ?FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('_lastUpdated', SearchFieldType::DATETIME, ['modifydate']);
    }

    /**
     * Parses an OpenEMR allergyIntolerance record, returning the equivalent FHIR AllergyIntolerance Resource
     *
     * @param array $dataRecord The source OpenEMR data record
     * @param bool $encode Indicates if the returned resource is encoded into a string. Defaults to false.
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
     * @param bool $encode Indicates if the returned resource is encoded into a string. Defaults to false.
     * @return FHIRAllergyIntolerance
     */
    public function parseOpenEMRRecord($dataRecord = [], $encode = false)
    {
        $allergyIntoleranceResource = new FHIRAllergyIntolerance();
        $fhirMeta = new FHIRMeta();
        $fhirMeta->setVersionId("1");
        if (!empty($dataRecord['date'])) {
            $fhirMeta->setLastUpdated(UtilsService::getLocalDateAsUTC($dataRecord['modifydate']));
        } else {
            $fhirMeta->setLastUpdated(UtilsService::getDateFormattedAsUTC());
        }
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
            [
            'system' => "http://terminology.hl7.org/CodeSystem/allergyintolerance-clinical",
            'code' => $clinicalStatus,
            'display' => ucwords($clinicalStatus),
            ]
        );
        $allergyIntoleranceResource->setClinicalStatus($clinical_Status);

        $allergyIntoleranceCategory = new FHIRAllergyIntoleranceCategory();
        // @see https://www.hl7.org/fhir/us/core/StructureDefinition-us-core-allergyintolerance-definitions.html#AllergyIntolerance.category
        $allergyIntoleranceCategory->setValue("medication");
        $allergyIntoleranceResource->addCategory($allergyIntoleranceCategory);

        if (isset($dataRecord['severity_al'])) {
            $criticalityCode = [
                "mild" => ["code" => "low", "display" => "Low Risk"],
                "mild_to_moderate" => ["code" => "low", "display" => "Low Risk"],
                "moderate" => ["code" => "low", "display" => "Low Risk"],
                "moderate_to_severe" => ["code" => "high", "display" => "High Risk"],
                "severe" => ["code" => "high", "display" => "High Risk"],
                "life_threatening_severity" => ["code" => "high", "display" => "High Risk"],
                "fatal" => ["code" => "high", "display" => "High Risk"],
                "unassigned" => ["code" => "unable-to-assess", "display" => "Unable to Assess Risk"],
            ];
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
                $reactionCoding->setDisplay(trim((string) $display));
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
                $diagnosisCoding->setDisplay(trim((string) $display));
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
        $verificationCoding = [
            'system' => "http://terminology.hl7.org/CodeSystem/allergyintolerance-verification",
            'code' => 'unconfirmed',
            'display' => 'Unconfirmed',
        ];
        if (!empty($dataRecord['verification'])) {
            $verificationCoding = [
                'system' => "http://terminology.hl7.org/CodeSystem/allergyintolerance-verification",
                'code' => $dataRecord['verification'],
                'display' => $dataRecord['verification_title']
            ];
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
     * Parses a FHIR AllergyIntolerance resource, returning the equivalent OpenEMR record.
     *
     * @param FHIRDomainResource $fhirResource The source FHIR resource
     * @return array a mapped OpenEMR data record
     */
    public function parseFhirResource(FHIRDomainResource $fhirResource)
    {
        if (!($fhirResource instanceof FHIRAllergyIntolerance)) {
            throw new \InvalidArgumentException(
                'Expected FHIRAllergyIntolerance resource, got ' . get_class($fhirResource)
            );
        }

        $data = [];

        if ($fhirResource->getId()) {
            $data['uuid'] = (string) $fhirResource->getId();
        }

        // Category is always 'medication' in OpenEMR (US Core requires it)
        // We accept it from the FHIR resource but don't need to store it since
        // OpenEMR allergy type is always 'allergy' in the lists table

        // Patient reference -> puuid (required in US Core)
        $patient = $fhirResource->getPatient();
        if ($patient && $patient->getReference()) {
            $reference = (string) $patient->getReference();
            $data['puuid'] = str_replace('Patient/', '', $reference);
        }

        // Code -> title and diagnosis
        $code = $fhirResource->getCode();
        if ($code) {
            $codings = $code->getCoding();
            if (!empty($codings)) {
                $diagnosisParts = [];
                foreach ($codings as $coding) {
                    $system = (string) $coding->getSystem();
                    $codeValue = (string) $coding->getCode();
                    $display = (string) $coding->getDisplay();
                    if (!empty($codeValue)) {
                        // Map FHIR system URIs to OpenEMR code type prefixes
                        $prefix = match ($system) {
                            'http://snomed.info/sct' => 'SNOMED-CT',
                            'http://www.nlm.nih.gov/research/umls/rxnorm' => 'RXNORM',
                            'http://hl7.org/fhir/ndc' => 'NDC',
                            default => $system,
                        };
                        $diagnosisParts[] = $prefix . ':' . $codeValue;
                    }
                    if (!empty($display) && empty($data['title'])) {
                        $data['title'] = $display;
                    }
                }
                if (!empty($diagnosisParts)) {
                    $data['diagnosis'] = implode(';', $diagnosisParts);
                }
            }
            // Fall back to text if no display
            if (empty($data['title']) && $code->getText()) {
                $data['title'] = (string) $code->getText();
            }
        }

        // ClinicalStatus -> outcome and enddate
        $clinicalStatus = $fhirResource->getClinicalStatus();
        if ($clinicalStatus) {
            $codings = $clinicalStatus->getCoding();
            if (!empty($codings)) {
                $statusCode = (string) $codings[0]->getCode();
                if ($statusCode === 'resolved') {
                    $data['outcome'] = '1';
                } elseif ($statusCode === 'active') {
                    // no enddate for active allergies
                    $data['outcome'] = '0';
                } elseif ($statusCode === 'inactive') {
                    $data['outcome'] = '0';
                }
            }
        }

        // Criticality -> severity_al
        $criticality = $fhirResource->getCriticality();
        if ($criticality) {
            $criticalityValue = (string) $criticality->getValue();
            $data['severity_al'] = match ($criticalityValue) {
                'low' => 'mild',
                'high' => 'severe',
                'unable-to-assess' => 'unassigned',
                default => 'unassigned',
            };
        }

        // VerificationStatus -> verification
        $verificationStatus = $fhirResource->getVerificationStatus();
        if ($verificationStatus) {
            $codings = $verificationStatus->getCoding();
            if (!empty($codings)) {
                $data['verification'] = (string) $codings[0]->getCode();
            }
        }

        // Recorder -> user (practitioner reference)
        $recorder = $fhirResource->getRecorder();
        if ($recorder && $recorder->getReference()) {
            $reference = (string) $recorder->getReference();
            // Store the practitioner UUID; the underlying service handles mapping
            $data['practitioner_uuid'] = str_replace('Practitioner/', '', $reference);
        }

        // OnsetDateTime -> begdate (validator expects Y-m-d H:i:s)
        $onset = $fhirResource->getOnsetDateTime();
        if ($onset) {
            $onsetValue = trim((string) $onset);
            if ($onsetValue !== '') {
                try {
                    $onsetDt = new \DateTimeImmutable($onsetValue);
                    $data['begdate'] = $onsetDt->format('Y-m-d H:i:s');
                } catch (\Exception) {
                    // Skip invalid date values
                }
            }
        }

        // Note -> comments
        $notes = $fhirResource->getNote();
        if (!empty($notes)) {
            $data['comments'] = (string) $notes[0]->getText();
        }

        // Reaction -> reaction code
        $reactions = $fhirResource->getReaction();
        if (!empty($reactions)) {
            $manifestations = $reactions[0]->getManifestation();
            if (!empty($manifestations)) {
                $codings = $manifestations[0]->getCoding();
                if (!empty($codings)) {
                    $reactionParts = [];
                    foreach ($codings as $coding) {
                        $codeValue = (string) $coding->getCode();
                        if (!empty($codeValue)) {
                            $system = (string) $coding->getSystem();
                            $prefix = match ($system) {
                                'http://snomed.info/sct' => 'SNOMED-CT',
                                default => $system,
                            };
                            $reactionParts[] = $prefix . ':' . $codeValue;
                        }
                    }
                    if (!empty($reactionParts)) {
                        $data['reaction'] = implode(';', $reactionParts);
                    }
                }
            }
        }

        // Final title fallback from narrative text element
        if (empty($data['title'])) {
            $text = $fhirResource->getText();
            if ($text && $text->getDiv()) {
                // Strip HTML tags from the narrative div
                $data['title'] = strip_tags((string) $text->getDiv());
            }
        }

        return $data;
    }

    /**
     * Inserts an OpenEMR record into the system.
     *
     * @param array $openEmrRecord The OpenEMR record to insert
     * @return ProcessingResult
     */
    protected function insertOpenEMRRecord($openEmrRecord)
    {
        return $this->allergyIntoleranceService->insert($openEmrRecord);
    }

    /**
     * Updates an existing OpenEMR record.
     *
     * @param string $fhirResourceId The OpenEMR record's FHIR Resource ID (uuid)
     * @param array $updatedOpenEMRRecord The updated OpenEMR record
     * @return ProcessingResult
     */
    protected function updateOpenEMRRecord($fhirResourceId, $updatedOpenEMRRecord)
    {
        return $this->allergyIntoleranceService->update($fhirResourceId, $updatedOpenEMRRecord);
    }

    /**
     * Searches for OpenEMR records using OpenEMR search parameters
     *
     * @param array<string, ISearchField> $openEMRSearchParameters OpenEMR search fields
     * @return ProcessingResult
     */
    protected function searchForOpenEMRRecords($openEMRSearchParameters): ProcessingResult
    {
        return $this->allergyIntoleranceService->search($openEMRSearchParameters, true);
    }

    public function getProfileURIs(): array
    {
        return $this->getProfileForVersions(self::USCGI_PROFILE_URI, $this->getSupportedVersions());
    }

    public function getPatientContextSearchField(): FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('patient', SearchFieldType::REFERENCE, [new ServiceField('puuid', ServiceField::TYPE_UUID)]);
    }
}
