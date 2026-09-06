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
use OpenEMR\Services\CodeTypesService;
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
                'Expected FHIRAllergyIntolerance resource, got ' . $fhirResource::class
            );
        }

        // Use jsonSerialize() to get a normalized array representation since
        // the FHIR R4 library does not deeply hydrate nested objects
        $json = $fhirResource->jsonSerialize();
        $data = [];

        $resourceId = $json['id'] ?? null;
        if (is_string($resourceId) && $resourceId !== '') {
            $data['uuid'] = $resourceId;
        }

        // Patient reference -> puuid (required in US Core)
        $patientRef = $json['patient']['reference'] ?? null;
        if (is_string($patientRef) && $patientRef !== '') {
            $parsedUuid = UtilsService::parseReferenceString($patientRef, 'Patient')['uuid'] ?? null;
            if (
                is_string($parsedUuid) && $parsedUuid !== ''
                && \OpenEMR\Common\Uuid\UuidRegistry::isValidStringUUID($parsedUuid)
            ) {
                $data['puuid'] = $parsedUuid;
            }
        }

        // Code -> title and diagnosis
        $code = $json['code'] ?? null;
        $codeCodings = $this->codingEntries($code);
        if ($codeCodings !== []) {
            $codeTypesService = new CodeTypesService();
            $diagnosisParts = [];
            foreach ($codeCodings as $coding) {
                $systemValue = $coding['system'] ?? null;
                $system = is_string($systemValue) ? $systemValue : '';
                $codeValue = $coding['code'] ?? null;
                if (is_scalar($codeValue) && $codeValue !== '' && $codeValue !== false) {
                    $diagnosisParts[] = $codeTypesService->getOpenEMRCodeForSystemAndCode($system, $codeValue);
                }
                $display = $coding['display'] ?? null;
                if (is_string($display) && $display !== '' && !isset($data['title'])) {
                    $data['title'] = $display;
                }
            }
            if ($diagnosisParts !== []) {
                $data['diagnosis'] = implode(';', $diagnosisParts);
            }
        }
        $codeText = is_array($code) ? ($code['text'] ?? null) : null;
        if (!isset($data['title']) && is_string($codeText) && $codeText !== '') {
            $data['title'] = $codeText;
        }

        // ClinicalStatus -> outcome
        $clinicalStatus = $this->firstCodingCode($json['clinicalStatus'] ?? null);
        if ($clinicalStatus !== '') {
            $data['outcome'] = ($clinicalStatus === 'resolved') ? '1' : '0';
        }

        // Criticality -> severity_al
        $criticality = $json['criticality'] ?? null;
        if (is_string($criticality) && $criticality !== '') {
            $data['severity_al'] = match ($criticality) {
                'low' => 'mild',
                'high' => 'severe',
                'unable-to-assess' => 'unassigned',
                default => 'unassigned',
            };
        }

        // VerificationStatus -> verification
        $verification = $this->firstCodingCode($json['verificationStatus'] ?? null);
        if ($verification !== '') {
            $data['verification'] = $verification;
        }

        // Recorder -> practitioner reference
        $recorderRef = $json['recorder']['reference'] ?? null;
        if (is_string($recorderRef) && $recorderRef !== '') {
            $recorderUuid = UtilsService::parseReferenceString($recorderRef, 'Practitioner')['uuid'] ?? null;
            if (
                is_string($recorderUuid) && $recorderUuid !== ''
                && \OpenEMR\Common\Uuid\UuidRegistry::isValidStringUUID($recorderUuid)
            ) {
                $data['practitioner_uuid'] = $recorderUuid;
            }
        }

        // OnsetDateTime -> begdate (validator expects Y-m-d H:i:s)
        $onsetDateTime = $json['onsetDateTime'] ?? null;
        if (is_string($onsetDateTime) && $onsetDateTime !== '') {
            $onsetDt = date_create_immutable($onsetDateTime);
            if ($onsetDt !== false) {
                $data['begdate'] = $onsetDt->format('Y-m-d H:i:s');
            }
        }

        // Note -> comments
        $note = $json['note'] ?? null;
        $firstNote = is_array($note) ? ($note[0] ?? null) : null;
        $noteText = is_array($firstNote) ? ($firstNote['text'] ?? null) : null;
        if (is_string($noteText) && $noteText !== '') {
            $data['comments'] = $noteText;
        }

        // Reaction -> reaction code
        $reaction = $json['reaction'] ?? null;
        $firstReaction = is_array($reaction) ? ($reaction[0] ?? null) : null;
        $manifestation = is_array($firstReaction) ? ($firstReaction['manifestation'] ?? null) : null;
        $firstManifestation = is_array($manifestation) ? ($manifestation[0] ?? null) : null;
        $reactionCodings = $this->codingEntries($firstManifestation);
        if ($reactionCodings !== []) {
            $codeTypesService = new CodeTypesService();
            $reactionParts = [];
            foreach ($reactionCodings as $coding) {
                $codeValue = $coding['code'] ?? null;
                if (is_scalar($codeValue) && $codeValue !== '' && $codeValue !== false) {
                    $systemValue = $coding['system'] ?? null;
                    $system = is_string($systemValue) ? $systemValue : '';
                    $reactionParts[] = $codeTypesService->getOpenEMRCodeForSystemAndCode($system, $codeValue);
                }
            }
            if ($reactionParts !== []) {
                $data['reaction'] = implode(';', $reactionParts);
            }
        }

        // Final title fallback from narrative text
        $text = $json['text'] ?? null;
        $narrative = is_array($text) ? ($text['div'] ?? null) : null;
        if (!isset($data['title']) && is_string($narrative) && $narrative !== '') {
            $data['title'] = strip_tags($narrative);
        }

        return $data;
    }

    /**
     * Extracts the `coding` entries of a FHIR CodeableConcept.
     *
     * The FHIR R4 library does not hydrate nested elements, so what reaches
     * here is whatever the request payload carried. Entries that are not
     * arrays are dropped rather than passed on to the callers' offset reads.
     *
     * @param mixed $codeableConcept
     * @return list<array<array-key, mixed>>
     */
    private function codingEntries($codeableConcept): array
    {
        if (!is_array($codeableConcept)) {
            return [];
        }
        $coding = $codeableConcept['coding'] ?? null;
        if (!is_array($coding)) {
            return [];
        }
        $entries = [];
        foreach ($coding as $entry) {
            if (is_array($entry)) {
                $entries[] = $entry;
            }
        }

        return $entries;
    }

    /**
     * Returns the `code` of a CodeableConcept's first coding entry.
     *
     * @param mixed $codeableConcept
     * @return string The code, or '' when absent or not a string
     */
    private function firstCodingCode($codeableConcept): string
    {
        $code = $this->codingEntries($codeableConcept)[0]['code'] ?? null;

        return is_string($code) ? $code : '';
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
