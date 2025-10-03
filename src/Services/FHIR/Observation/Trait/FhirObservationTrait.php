<?php

/*
 * FhirObservationTrait.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR\Observation\Trait;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRObservation;
use OpenEMR\FHIR\R4\FHIRElement\FHIRAnnotation;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCode;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRObservationStatus;
use OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod;
use OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\FHIR\R4\FHIRElement\FHIRString;
use OpenEMR\FHIR\R4\FHIRElement\FHIRUri;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;
use OpenEMR\Services\CodeTypesService;
use OpenEMR\Services\FHIR\FhirCodeSystemConstants;
use OpenEMR\Services\FHIR\FhirProvenanceService;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\FHIR\Traits\VersionedProfileTrait;
use OpenEMR\Services\FHIR\UtilsService;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Services\Utils\DateFormatterUtils;

trait FhirObservationTrait
{
    use FhirServiceBaseEmptyTrait;
    use VersionedProfileTrait;

    const US_CORE_CODESYSTEM_OBSERVATION_CATEGORY_URI = 'http://terminology.hl7.org/CodeSystem/observation-category';
    const US_CORE_CODESYSTEM_OBSERVATION_CATEGORY = ['social-history', 'vital-signs', 'imaging', 'laboratory', 'procedure', 'survey', 'exam', 'therapy', 'activity'];
    const US_CORE_CODESYSTEM_CATEGORY_URI = 'http://hl7.org/fhir/us/core/CodeSystem/us-core-category';
    const US_CORE_CODESYSTEM_CATEGORY = ['sdoh', 'functional-status', 'disability-status', 'cognitive-status', 'treatment-intervention-preference', 'care-experience-preference', 'observation-adi-documentation'];

    const USCGI_PROFILE_URI = 'http://hl7.org/fhir/us/core/StructureDefinition/us-core-simple-observation';
    const USCGI_SCREENING_ASSESSMENT_URI = 'http://hl7.org/fhir/us/core/StructureDefinition/us-core-observation-screening-assessment';
    const OBSERVATION_VALID_STATII = ['registered', 'preliminary', 'final', 'amended', 'corrected', 'cancelled', 'entered-in-error', 'unknown'];

    protected FhirProvenanceService $fhirProvenanceService;

    protected CodeTypesService $codeTypesService;

    public function setCodeTypesService(CodeTypesService $service)
    {
        $this->codeTypesService = $service;
    }

    public function getCodeTypesService()
    {
        if (!isset($this->codeTypesService)) {
            $this->codeTypesService = new CodeTypesService();
        }
        return $this->codeTypesService;
    }

    public function parseOpenEMRRecord($dataRecord = array(), $encode = false): FHIRDomainResource|string
    {
        return $this->parseObservationOpenEMRRecord($dataRecord, $encode);
    }

    /**
     * Parses an OpenEMR data record, returning the equivalent FHIR Resource
     *
     * @param array $dataRecord The source OpenEMR data record
     * @param bool $encode Indicates if the returned resource is encoded into a string. Defaults to True.
     * @return FHIRObservation|string the FHIR Resource. Returned format is defined using $encode parameter.
     */
    public function parseObservationOpenEMRRecord($dataRecord = array(), $encode = false): FHIRDomainResource|string
    {
        // AI-generated implementation start
        if (empty($dataRecord)) {
            throw new \InvalidArgumentException("Data record cannot be empty");
        }

        // Create new FHIR Observation
        $observation = new FHIRObservation();

        // Set ID (required)
        if (!empty($dataRecord['uuid'])) {
            $id = new FHIRId();
            $id->setValue($dataRecord['uuid']);
            $observation->setId($id);
        }

        // Set Status (required, mustSupport)
        $status = new FHIRObservationStatus();
        $statusValue = $this->getValidStatus($dataRecord['ob_status'] ?? 'unknown');
        $status->setValue($statusValue);
        $observation->setStatus($status);

        // Set Code (required, mustSupport)
        $this->setObservationCode($observation, $dataRecord);

        // Set Subject (required, mustSupport, min 1..1)
        $this->setObservationSubject($observation, $dataRecord);

        // Set Effective[x] (mustSupport, dateTime must be supported)
        $this->setObservationEffective($observation, $dataRecord);

        // Set Performer (mustSupport)
        $this->setObservationPerformer($observation, $dataRecord);

        // Set Value[x] or DataAbsentReason (constraint us-core-2)
        $this->setObservationValue($observation, $dataRecord);

        // Set HasMember (mustSupport) for panel observations
        $this->setObservationHasMember($observation, $dataRecord);

        // Set DerivedFrom (mustSupport)
        $this->setObservationDerivedFrom($observation, $dataRecord);

        // Set optional fields
        $this->setOptionalFields($observation, $dataRecord);

        // Validate us-core-2 constraint
        $this->validateUSCore2Constraint($observation, $dataRecord);

        // Set Category (required, mustSupport, min 1), including optional screening-assessment slice
        $this->setObservationCategory($observation, $dataRecord);

        // we do meta last as it needs to know about other fields
        $this->setObservationMeta($observation, $dataRecord);

        if ($encode) {
            return json_encode($observation);
        }

        return $observation;
        // AI-generated implementation ends
    }

    protected function setObservationMeta(FHIRObservation $observation, array $dataRecord): void
    {
        $meta = new FHIRMeta();

        // Set profile (required, mustSupport)
        $this->setObservationProfile($meta, $observation, $dataRecord);

        // Set versionId
        $versionId = new FHIRId();
        $versionId->setValue($dataRecord['version_id'] ?? '1');
        $meta->setVersionId($versionId);

        // Set lastUpdated
        if (!empty($dataRecord['last_updated_time'])) {
            $meta->setLastUpdated(UtilsService::getLocalDateAsUTC($dataRecord['last_updated_time']));
        } else {
            $meta->setLastUpdated(UtilsService::getDateFormattedAsUTC());
        }

        $observation->setMeta($meta);
    }


    /**
     * Set observation performer (mustSupport)
     */
    protected function setObservationPerformer(FHIRObservation $observation, array $dataRecord): void
    {
        $performerUuid = $dataRecord['performer_uuid'] ?? $dataRecord['user_uuid'] ?? null;
        $performerType = $dataRecord['performer_type'] ?? 'Practitioner';
        $performerDisplay = $dataRecord['performer_display'] ?? null;

        // we should ALWAYS have a performer, if not we add a data missing extension
        if (!empty($performerUuid)) {
            $performer = UtilsService::createRelativeReference($performerType, $performerUuid, $performerDisplay);
        } else {
            $performer = new FHIRReference();
            $performer->addExtension(UtilsService::createDataMissingExtension());
        }
        $observation->addPerformer($performer);
    }

    protected function setObservationProfile(FHIRMeta $meta, FHIRObservation $observation, array $dataRecord): void
    {
        $profiles = $dataRecord['profiles'] ?? [self::USCGI_PROFILE_URI];
        foreach ($profiles as $profileUrl) {
            $profile = new FHIRCanonical();
            $profile->setValue($profileUrl);
            $meta->addProfile($profile);
        }
    }

    protected function setObservationValueWithDetails(FhirObservation $observation, ?string $value, ?string $valueUnit, ?string $codeDescription, array $children = array())
    {
        $valueType = "string";
        if (is_string($value) && !empty($codeDescription) && str_contains($value, ':')) {
            $valueType = 'CodeableConcept';
        } else if (is_numeric($value)) {
            $valueType = 'Quantity';
        }

        // Set value based on type
        if (!empty($value)) {
            switch ($valueType) {
                case 'string':
                    $valueString = new FHIRString();
                    $valueString->setValue($value);
                    $observation->setValueString($valueString);
                    break;

                case 'CodeableConcept':
                    $parsedCode = $this->getCodeTypesService()->parseCode($value);
                    $code = $parsedCode['code'];
                    $system = $this->getCodeTypesService()->getSystemForCodeType($parsedCode['code_type']);
                    $valueCC = new FHIRCodeableConcept();
                    $valueCoding = new FHIRCoding();
                    $valueCoding->setSystem(new FHIRUri($system));
                    $valueCoding->setCode(new FHIRCode($code));
                    $valueCoding->setDisplay($codeDescription ?? $this->getCodeTypesService()->lookup_code_description($value));
                    $valueCC->addCoding($valueCoding);
                    $observation->setValueCodeableConcept($valueCC);
                    break;

                case 'Quantity':
                default:
                    $valueQuantity = new FHIRQuantity();
                    // must be an integer or decimal
                    $valueQuantity->setValue(floatval($value));

                    if (!empty($valueUnit)) {
                        $valueQuantity->setUnit($valueUnit);
                        // Apply UCUM constraint (us-core-3) for standard units
                        if ($this->shouldUseUCUM($valueUnit)) {
                            $valueQuantity->setSystem(new FHIRUri(FhirCodeSystemConstants::UNITS_OF_MEASURE));
                            $valueQuantity->setCode($valueUnit);
                        }
                    }
                    $observation->setValueQuantity($valueQuantity);
                    break;
            }
        } else if (empty($children)) {
            // Set dataAbsentReason (mustSupport)
            // If no value and no children (not a panel), dataAbsentReason is required (us-core-2)
//            $dataAbsentReason = new FHIRCodeableConcept();
//            $darCoding = new FHIRCoding();
//            $darCoding->setSystem($dataRecord['data_absent_reason_system'] ?? 'http://terminology.hl7.org/CodeSystem/data-absent-reason');
//            $darCoding->setCode($dataRecord['data_absent_reason']);
//            $darCoding->setDisplay($dataRecord['data_absent_reason_display'] ?? '');
//            $dataAbsentReason->addCoding($darCoding);
            $observation->setDataAbsentReason(UtilsService::createDataAbsentUnknownCodeableConcept());
//            $observation->setDataAbsentReason($dataAbsentReason);
        }
    }



    /**
     * Set observation category with required survey slice and optional screening-assessment slice
     */
    protected function setObservationCategory(FHIRObservation $observation, array $dataRecord): void
    {
        // Required survey category slice (mustSupport, min 1..1)
        $initialCategory = new FHIRCodeableConcept();
        $catCoding = new FHIRCoding();
        $catCoding->setSystem(new FHIRUri(FhirCodeSystemConstants::HL7_CATEGORY_OBSERVATION));
        $catCoding->setCode(new FhirCode($dataRecord['ob_type'] ?? 'survey'));
        $catCoding->setDisplay($dataRecord['ob_type'] ?? 'Survey');
        $initialCategory->addCoding($catCoding);
        $observation->addCategory($initialCategory);

        // Optional screening-assessment category slice if we have a questionnaire category (mustSupport, 0..1)
        if (!empty($dataRecord['screening_category_code'])) {
            if (in_array($dataRecord['screening_category_code'], self::US_CORE_CODESYSTEM_OBSERVATION_CATEGORY)) {
                $systemUri = self::US_CORE_CODESYSTEM_OBSERVATION_CATEGORY_URI;
            }
            if (in_array($dataRecord['screening_category_code'], self::US_CORE_CODESYSTEM_CATEGORY)) {
                $systemUri = self::US_CORE_CODESYSTEM_CATEGORY_URI;
            }
            if (empty($systemUri)) {
                $this->getSystemLogger()->warning("Observation screening category code {$dataRecord['screening_category_code']} not in supported code systems");
                return;
            }
            $screeningCategory = new FHIRCodeableConcept();
            $screeningCoding = new FHIRCoding();
            $screeningCoding->setSystem(new FHIRUri($systemUri));
            $screeningCoding->setCode(new FHIRCode($dataRecord['screening_category_code']));
            $screeningCoding->setDisplay($dataRecord['screening_category_display'] ?? '');
            $screeningCategory->addCoding($screeningCoding);
            $observation->addCategory($screeningCategory);
        }
    }

    /**
     * Set observation code (required, mustSupport)
     */
    protected function setObservationCode(FHIRObservation $observation, array $dataRecord): void
    {
        if (empty($dataRecord['code'])) {
            throw new \InvalidArgumentException("Code is required for observation");
        }

        $code = new FHIRCodeableConcept();
        $coding = new FHIRCoding();
        $codeDescription = $this->getCodeTypesService()->lookup_code_description($dataRecord['code']);
        $codeDescription = !empty($codeDescription) ? $codeDescription : ($dataRecord['description'] ?? '');
        // Parse system and code from format like "LOINC:72133-2"
        $codeParts = $this->getCodeTypesService()->parseCode($dataRecord['code']);
        $codeType = $codeParts['code_type'] ?? FhirCodeSystemConstants::LOINC;
        $system = $this->getCodeSystem($codeType);
        $coding->setSystem($system);
        $coding->setCode(new FHIRCode($codeParts['code']));

        $coding->setDisplay(trim($codeDescription));
        $code->addCoding($coding);
        $observation->setCode($code);
    }

    /**
     * Set observation subject (required, mustSupport, min 1..1)
     */
    protected function setObservationSubject(FHIRObservation $observation, array $dataRecord): void
    {
        if (empty($dataRecord['puuid'])) {
            throw new \InvalidArgumentException("Patient UUID (puuid) is required for observation subject");
        }

        $subject = new FHIRReference();
        $subject->setReference(new FHIRString('Patient/' . $dataRecord['puuid']));
        $observation->setSubject($subject);
    }

    /**
     * Set observation effective datetime/period (mustSupport, dateTime must be supported)
     */
    protected function setObservationEffective(FHIRObservation $observation, array $dataRecord): void
    {
        if (!empty($dataRecord['date'])) {
            $effectiveDateTime = new FHIRDateTime();
            $dateStart = UtilsService::getLocalDateAsUTC($dataRecord['date']);
            $effectiveDateTime->setValue($dateStart);
            $observation->setEffectiveDateTime($effectiveDateTime);

            // Set period if end date is provided
            if (DateFormatterUtils::isNotEmptyDateTimeString($dataRecord['date_end'] ?? null)) {
                $period = new FHIRPeriod();
                $period->setStart($effectiveDateTime);

                $endDateTime = new FHIRDateTime();
                $dateEnd = UtilsService::getLocalDateAsUTC($dataRecord['date_end']);
                $endDateTime->setValue($dateEnd);
                $period->setEnd($endDateTime);

                $observation->setEffectivePeriod($period);
            }
        }
    }

    /**
     * Set observation value or dataAbsentReason (mustSupport, constraint us-core-2)
     */
    protected function setObservationValue(FHIRObservation $observation, array $dataRecord): void
    {
        $value = $dataRecord['value'] ?? null;
        $valueUnit = $dataRecord['value_unit'] ?? null;
        $codeDescription = $dataRecord['value_code_description'] ?? null;
        $children = $dataRecord['sub_observations'] ?? array();
        $this->setObservationValueWithDetails($observation, $value, $valueUnit, $codeDescription, $children);
    }

    /**
     * Set observation hasMember for panel observations (mustSupport)
     */
    protected function setObservationHasMember(FHIRObservation $observation, array $dataRecord): void
    {
        if (!empty($dataRecord['sub_observations']) && is_array($dataRecord['sub_observations'])) {
            foreach ($dataRecord['sub_observations'] as $child) {
                if (!empty($child['uuid'])) {
                    $memberRef = new FHIRReference();
                    $memberRef->setReference(new FHIRString('Observation/' . $child['uuid']));
                    $observation->addHasMember($memberRef);
                }
            }
        }
    }

    /**
     * Set observation derivedFrom (mustSupport)
     */
    protected function setObservationDerivedFrom(FHIRObservation $observation, array $dataRecord): void
    {
        // Order: QuestionnaireResponse first, then Observation
        // TODO: @adunsulag our questionnaires appear to be failing on validation as the validator throws a 500 exception
        // local validation appears to show some minor errors but perhaps its failing to fetch the Questionnaire since
        // the Questionnaire is access controlled... not sure if we need to open that up or not.
//        if (!empty($dataRecord['questionnaire_response_uuid'])) {
//            $qrRef = new FHIRReference();
//            $qrRef->setReference(new FHIRString('QuestionnaireResponse/' . $dataRecord['questionnaire_response_uuid']));
//            $observation->addDerivedFrom($qrRef);
//        }

        if (!empty($dataRecord['parent_observation_uuid'])) {
            $parentRef = new FHIRReference();
            $parentRef->setReference(new FHIRString('Observation/' . $dataRecord['parent_observation_uuid']));
            $observation->addDerivedFrom($parentRef);
        }
    }

    /**
     * Set optional fields
     */
    protected function setOptionalFields(FHIRObservation $observation, array $dataRecord): void
    {
        // Set encounter
        if (!empty($dataRecord['encounter_uuid'])) {
            $encounter = new FHIRReference();
            $encounter->setReference(new FHIRString('Encounter/' . $dataRecord['encounter_uuid']));
            $observation->setEncounter($encounter);
        }

        // Set note
        if (!empty($dataRecord['note'])) {
            $note = new FHIRAnnotation();
            $note->setText($dataRecord['note']);
            $observation->addNote($note);
        }

        // Set interpretation
        // TODO: @adunsulag if we support interpretation codes, we can add them here
//        if (!empty($dataRecord['interpretation'])) {
//            $interpretation = new FHIRCodeableConcept();
//            $interpCoding = new FHIRCoding();
//            $interpCoding->setSystem($dataRecord['interpretation_system'] ?? 'http://terminology.hl7.org/CodeSystem/v3-ObservationInterpretation');
//            $interpCoding->setCode($dataRecord['interpretation']);
//            $interpCoding->setDisplay($dataRecord['interpretation_display'] ?? '');
//            $interpretation->addCoding($interpCoding);
//            $observation->addInterpretation($interpretation);
//        }
    }

    /**
     * Validate US Core constraint us-core-2
     */
    protected function validateUSCore2Constraint(FHIRObservation $observation, array $dataRecord): void
    {
        $hasComponent = !empty($observation->getComponent());
        $hasMember = !empty($observation->getHasMember());
        $hasValue = !empty($observation->getValueQuantity()) ||
            !empty($observation->getValueString()) ||
            !empty($observation->getValueCodeableConcept());
        $hasDataAbsentReason = !empty($observation->getDataAbsentReason());

        // If no component and no hasMember, must have value or dataAbsentReason
        if (!$hasComponent && !$hasMember && !$hasValue && !$hasDataAbsentReason) {
            throw new \InvalidArgumentException('Either value[x] or dataAbsentReason must be present when no hasMember exists (us-core-2 constraint)');
        }
    }

    /**
     * Get valid observation status
     */
    protected function getValidStatus($status)
    {
        $statii = self::OBSERVATION_VALID_STATII;
        if (array_search($status, $statii) !== false) {
            return $status;
        }
        return "unknown";
    }




    /**
     * Get code system URL from prefix
     */
    protected function getCodeSystem($prefix): FHIRUri
    {
        $system = $this->getCodeTypesService()->getSystemForCodeType($prefix);
        return new FHIRUri($system ?? FhirCodeSystemConstants::LOINC);
    }

    /**
     * Check if unit should use UCUM system (us-core-3 constraint)
     */
    protected function shouldUseUCUM($unit): bool
    {
        // Common non-UCUM units that shouldn't trigger UCUM system
        $nonUCUMUnits = ['{score}', '{count}', '{ratio}', '{index}'];
        return !in_array($unit, $nonUCUMUnits);
    }

    public function getProvenanceService(): FhirProvenanceService
    {
        if (!isset($this->fhirProvenanceService)) {
            $this->fhirProvenanceService = new FhirProvenanceService();
        }
        return $this->fhirProvenanceService;
    }

    public function setProvenanceService(FhirProvenanceService $service)
    {
        $this->fhirProvenanceService = $service;
    }

    /**
     * Creates the Provenance resource  for the equivalent FHIR Resource
     *
     * @param FHIRDomainResource $dataRecord The source OpenEMR data record
     * @param bool $encode Indicates if the returned resource is encoded into a string. Defaults to True.
     * @return FhirProvenanceService|string the FHIR Resource. Returned format is defined using $encode parameter.
     */
    public function createProvenanceResource($dataRecord, $encode = false)
    {
        if (!($dataRecord instanceof FHIRObservation)) {
            throw new \BadMethodCallException("Data record should be correct instance class");
        }
        $fhirProvenanceService = $this->getProvenanceService();
        $performer = null;
        if (!empty($dataRecord->getPerformer())) {
            // grab the first one
            $performer = current($dataRecord->getPerformer());
        }
        $fhirProvenance = $fhirProvenanceService->createProvenanceForDomainResource($dataRecord, $performer);
        if ($encode) {
            return json_encode($fhirProvenance);
        } else {
            return $fhirProvenance;
        }
    }

    public function getPatientContextSearchField(): FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('patient', SearchFieldType::REFERENCE, [new ServiceField('puuid', ServiceField::TYPE_UUID)]);
    }
}
