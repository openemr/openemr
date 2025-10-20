<?php

/**
 * FhirCarePlanService.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCarePlan;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRNarrative;
use OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod;
use OpenEMR\FHIR\R4\FHIRResource\FHIRCarePlan\FHIRCarePlanActivity;
use OpenEMR\FHIR\R4\FHIRResource\FHIRCarePlan\FHIRCarePlanDetail;
use OpenEMR\Services\CarePlanService;
use OpenEMR\Services\FHIR\Traits\BulkExportSupportAllOperationsTrait;
use OpenEMR\Services\FHIR\Traits\FhirBulkExportDomainResourceTrait;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\FHIR\Traits\VersionedProfileTrait;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Validators\ProcessingResult;

class FhirCarePlanService extends FhirServiceBase implements IResourceUSCIGProfileService, IPatientCompartmentResourceService, IFhirExportableResourceService
{
    use FhirServiceBaseEmptyTrait;
    use BulkExportSupportAllOperationsTrait;
    use FhirBulkExportDomainResourceTrait;
    use VersionedProfileTrait;

    /**
     * @var CarePlanService
     */
    private $service;

    const USCGI_PROFILE_URI = 'http://hl7.org/fhir/us/core/StructureDefinition/us-core-careplan';

    public function __construct()
    {
        parent::__construct();
        $this->service = new CarePlanService();
    }

    /**
     * Returns an array mapping FHIR CarePlan Resource search parameters to OpenEMR CarePlan search parameters
     * @return array The search parameters
     */
    // In FhirCarePlanService.php - Update loadSearchParameters()
    protected function loadSearchParameters()
    {
        return  [
            'patient' => $this->getPatientContextSearchField(),
            // Map to the REAL column, not the derived alias
            'category' => new FhirSearchParameterDefinition('category', SearchFieldType::TOKEN, ['care_plan_type']),
            'status' => new FhirSearchParameterDefinition('status', SearchFieldType::TOKEN, ['plan_status']),
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, ['uuid']),
            '_lastUpdated' => $this->getLastModifiedSearchField(),
        ];
    }

    /**
     * Get the last modified search field definition
     * @return FhirSearchParameterDefinition|null
     */
    public function getLastModifiedSearchField(): ?FhirSearchParameterDefinition
    {
        // TODO: @adunsulag introduce a last_modified date field to the care plan table as we don't track this anywhere
        return new FhirSearchParameterDefinition('_lastUpdated', SearchFieldType::DATETIME, ['creation_date']);
    }

    /**
     * Get the patient context search field definition
     * @return FhirSearchParameterDefinition
     */
    public function getPatientContextSearchField(): FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('patient', SearchFieldType::REFERENCE, [new ServiceField('puuid', ServiceField::TYPE_UUID)]);
    }

    /**
     * Parses an OpenEMR record, returning the equivalent FHIR Resource
     *
     * @param array $dataRecord The source OpenEMR data record
     * @param boolean $encode Indicates if the returned resource is encoded into a string. Defaults to false.
     * @return FHIRCarePlan
     */
    public function parseOpenEMRRecord($dataRecord = [], $encode = false)
    {
        $carePlanResource = new FHIRCarePlan();

        // Set metadata
        $this->setResourceMetadata($carePlanResource, $dataRecord);

        // Set resource ID
        $fhirId = new FHIRId();
        $fhirId->setValue($dataRecord['uuid']);
        $carePlanResource->setId($fhirId);

        // Set subject (patient reference)
        $this->setSubject($carePlanResource, $dataRecord);

        // Set categories
        $this->setCategories($carePlanResource, $dataRecord);

        // Set intent (always "plan" for care plans)
        $carePlanResource->setIntent("plan");

        // Set status
        $status = $this->mapCarePlanStatus($dataRecord['plan_status'] ?? 'active');
        $carePlanResource->setStatus($status);

        // Set period if dates are available
        $this->setPeriod($carePlanResource, $dataRecord);

        // Set author (practitioner reference)
        $this->setAuthor($carePlanResource, $dataRecord);

        // Process activities from details
        $this->setActivities($carePlanResource, $dataRecord);

        // Set narrative text and description
        $this->setNarrativeAndDescription($carePlanResource, $dataRecord);

        if ($encode) {
            return json_encode($carePlanResource);
        } else {
            return $carePlanResource;
        }
    }

    /**
     * Set resource metadata including version and last updated timestamp
     */
    private function setResourceMetadata(FHIRCarePlan $carePlanResource, array $dataRecord): void
    {
        $fhirMeta = new FHIRMeta();
        $fhirMeta->setVersionId('1');

        if (!empty($dataRecord['creation_date'])) {
            $fhirMeta->setLastUpdated(UtilsService::getLocalDateAsUTC($dataRecord['creation_date']));
        } else {
            $fhirMeta->setLastUpdated(UtilsService::getDateFormattedAsUTC());
        }

        $carePlanResource->setMeta($fhirMeta);
    }

    /**
     * Set the subject (patient) reference
     */
    private function setSubject(FHIRCarePlan $carePlanResource, array $dataRecord): void
    {
        if (isset($dataRecord['puuid'])) {
            $carePlanResource->setSubject(UtilsService::createRelativeReference("Patient", $dataRecord['puuid']));
        } else {
            $carePlanResource->setSubject(UtilsService::createDataMissingExtension());
        }
    }

    /**
     * Set care plan categories based on type
     */
    private function setCategories(FHIRCarePlan $carePlanResource, array $dataRecord): void
    {
        // Determine primary category
        $primaryCategory = 'assess-plan';
        if (isset($dataRecord['care_plan_type']) && $dataRecord['care_plan_type'] === CarePlanService::TYPE_GOAL) {
            $primaryCategory = 'goal';
        }

        // Add primary category
        $codeableConcept = new FHIRCodeableConcept();
        $coding = new FHIRCoding();
        $coding->setCode($primaryCategory);
        $coding->setSystem(FhirCodeSystemConstants::HL7_SYSTEM_CAREPLAN_CATEGORY);
        $codeableConcept->addCoding($coding);
        $carePlanResource->addCategory($codeableConcept);
    }

    /**
     * Set the period (start and end dates) for the care plan
     */
    private function setPeriod(FHIRCarePlan $carePlanResource, array $dataRecord): void
    {
        $hasStartDate = false;
        $hasEndDate = false;
        $startDate = null;
        $endDate = null;

        // Check for dates in the aggregated details
        if (!empty($dataRecord['details'])) {
            foreach ($dataRecord['details'] as $detail) {
                if (!empty($detail['date']) && (empty($startDate) || $detail['date'] < $startDate)) {
                    $startDate = $detail['date'];
                    $hasStartDate = true;
                }
                if (!empty($detail['date_end']) && (empty($endDate) || $detail['date_end'] > $endDate)) {
                    $endDate = $detail['date_end'];
                    $hasEndDate = true;
                }
            }
        }

        // Fall back to record-level dates if available
        if (!$hasStartDate && !empty($dataRecord['date'])) {
            $startDate = $dataRecord['date'];
            $hasStartDate = true;
        }
        if (!$hasEndDate && !empty($dataRecord['date_end'])) {
            $endDate = $dataRecord['date_end'];
            $hasEndDate = true;
        }

        // Set period if we have any dates
        if ($hasStartDate || $hasEndDate) {
            $period = new FHIRPeriod();
            if ($hasStartDate) {
                $period->setStart(UtilsService::getLocalDateAsUTC($startDate));
            }
            if ($hasEndDate) {
                $period->setEnd(UtilsService::getLocalDateAsUTC($endDate));
            }
            $carePlanResource->setPeriod($period);
        }
    }

    /**
     * Set the author (practitioner) reference
     */
    private function setAuthor(FHIRCarePlan $carePlanResource, array $dataRecord): void
    {
        if (!empty($dataRecord['provider_uuid'])) {
            $carePlanResource->setAuthor(UtilsService::createRelativeReference("Practitioner", $dataRecord['provider_uuid']));
        }
    }

    /**
     * Set activities from care plan details
     */
    private function setActivities(FHIRCarePlan $carePlanResource, array $dataRecord): void
    {
        if (empty($dataRecord['details'])) {
            return;
        }

        foreach ($dataRecord['details'] as $detail) {
            $activity = new FHIRCarePlanActivity();
            $activityDetail = new FHIRCarePlanDetail();

            // Set activity code if present
            if (!empty($detail['code'])) {
                $activityCode = $this->createActivityCode($detail);
                if ($activityCode !== null) {
                    $activityDetail->setCode($activityCode);
                }
            }

            // Set activity description
            if (!empty($detail['description'])) {
                $activityDetail->setDescription($detail['description']);
            }

            // Set activity status
            $activityStatus = $this->mapActivityStatus($detail['moodCode'] ?? null);
            $activityDetail->setStatus($activityStatus);

            // Set scheduled period if dates are present
            if (!empty($detail['date']) || !empty($detail['date_end'])) {
                $scheduledPeriod = new FHIRPeriod();
                if (!empty($detail['date'])) {
                    $scheduledPeriod->setStart(UtilsService::getLocalDateAsUTC($detail['date']));
                }
                if (!empty($detail['date_end'])) {
                    $scheduledPeriod->setEnd(UtilsService::getLocalDateAsUTC($detail['date_end']));
                }
                $activityDetail->setScheduledPeriod($scheduledPeriod);
            }

            $activity->setDetail($activityDetail);
            $carePlanResource->addActivity($activity);
        }
    }

    /**
     * Create activity code from detail record
     */
    private function createActivityCode(array $detail): ?FHIRCodeableConcept
    {
        if (empty($detail['code'])) {
            return null;
        }

        $codeSystem = $this->getCodeSystem($detail['code']);
        $codeValue = $this->extractCodeValue($detail['code']);

        if (empty($codeValue)) {
            return null;
        }

        $codeableConcept = new FHIRCodeableConcept();
        $coding = new FHIRCoding();
        $coding->setCode($codeValue);

        if (!empty($codeSystem)) {
            $coding->setSystem($codeSystem);
        }

        if (!empty($detail['codetext'])) {
            $coding->setDisplay($detail['codetext']);
            $codeableConcept->setText($detail['codetext']);
        }

        $codeableConcept->addCoding($coding);
        return $codeableConcept;
    }

    /**
     * Set narrative text and description
     */
    private function setNarrativeAndDescription(FHIRCarePlan $carePlanResource, array $dataRecord): void
    {
        if (!empty($dataRecord['details'])) {
            $carePlanText = $this->getCarePlanTextFromDetails($dataRecord['details']);

            // Set description (plain text summary)
            $carePlanResource->setDescription($carePlanText['text']);

            // Set narrative (XHTML formatted text)
            $narrative = new FHIRNarrative();
            $narrative->setStatus("generated");
            $narrative->setDiv('<div xmlns="http://www.w3.org/1999/xhtml">' . $carePlanText['xhtml'] . '</div>');
            $carePlanResource->setText($narrative);
        } else {
            $carePlanResource->setText(UtilsService::createDataMissingExtension());
        }
    }

    /**
     * Generate care plan text from details
     */
    private function getCarePlanTextFromDetails(array $details): array
    {
        $descriptions = [];
        foreach ($details as $detail) {
            // Use description or fallback on codetext if needed
            $text = $detail['description'] ?? $detail['codetext'] ?? "";
            if (!empty(trim($text))) {
                $descriptions[] = trim($text);
            }
        }

        $carePlanText = [
            'text' => implode("\n", $descriptions),
            'xhtml' => ""
        ];

        if (!empty($descriptions)) {
            $escapedDescriptions = array_map('text', $descriptions);
            $carePlanText['xhtml'] = "<p>" . implode("</p><p>", $escapedDescriptions) . "</p>";
        }

        return $carePlanText;
    }

    /**
     * Map OpenEMR care plan status to FHIR status
     */
    private function mapCarePlanStatus(?string $formStatus): string
    {
        if (empty($formStatus)) {
            return 'unknown';
        }

        $statusMap = [
            'active' => 'active',
            'completed' => 'completed',
            'on-hold' => 'on-hold',
            'cancelled' => 'revoked',
            'entered-in-error' => 'entered-in-error',
            'draft' => 'draft',
            'unknown' => 'unknown'
        ];

        return $statusMap[strtolower($formStatus)] ?? 'unknown';
    }

    /**
     * Map OpenEMR activity status to FHIR activity status
     */
    private function mapActivityStatus(?string $moodCode): string
    {
        if (empty($moodCode)) {
            return 'not-started'; // Better default than 'unknown'
        }

        // OpenEMR moodCode values from list_options where list_id='Plan_of_Care_Type'
        $statusMap = [
            'int' => 'in-progress',      // Intent
            'rqo' => 'scheduled',         // Request
            'prp' => 'scheduled',         // Proposal
            'apt' => 'scheduled',         // Appointment
            'arp' => 'scheduled',         // Appointment Request
            'pln' => 'scheduled',         // Plan
            'evt' => 'completed',         // Event (occurred)
            'active' => 'in-progress',
            'planned' => 'scheduled',
            'not-started' => 'not-started',
            'in-progress' => 'in-progress',
            'on-hold' => 'on-hold',
            'completed' => 'completed',
            'cancelled' => 'cancelled',
            'stopped' => 'stopped',
            'entered-in-error' => 'entered-in-error'
        ];

        return $statusMap[strtolower($moodCode)] ?? 'not-started';
    }

    /**
     * Determine the code system based on the code format
     */
    private function getCodeSystem(?string $code): ?string
    {
        if (empty($code)) {
            return null;
        }

        // OpenEMR codes often come in format "CODETYPE:CODE"
        $parts = explode(':', $code, 2);
        if (count($parts) < 2) {
            // If no prefix, assume SNOMED-CT as default for care plans
            return FhirCodeSystemConstants::SNOMED_CT;
        }

        $codeType = strtoupper($parts[0]);

        // Map code types to their system URIs
        $systemMap = [
            'SNOMED' => FhirCodeSystemConstants::SNOMED_CT,
            'SNOMED-CT' => FhirCodeSystemConstants::SNOMED_CT,
            'SNOMED-PR' => FhirCodeSystemConstants::SNOMED_CT,
            'ICD10' => FhirCodeSystemConstants::HL7_ICD10,
            'ICD10-CM' => FhirCodeSystemConstants::HL7_ICD10,
            'CPT4' => FhirCodeSystemConstants::AMA_CPT,
            'LOINC' => FhirCodeSystemConstants::LOINC,
            'RXNORM' => FhirCodeSystemConstants::RXNORM,
            'RXCUI' => FhirCodeSystemConstants::RXNORM,
            'HCPCS' => 'https://www.cms.gov/Medicare/Coding/HCPCSReleaseCodeSets'
        ];

        return $systemMap[$codeType] ?? null;
    }

    /**
     * Extract the actual code value from an OpenEMR code string
     */
    private function extractCodeValue(?string $code): string
    {
        if (empty($code)) {
            return '';
        }

        // Split on colon to get the actual code
        $parts = explode(':', $code, 2);
        return count($parts) === 2 ? trim($parts[1]) : trim($code);
    }

    /**
     * Searches for OpenEMR records using OpenEMR search parameters
     *
     * @param array<string, ISearchField> $openEMRSearchParameters OpenEMR search fields
     * @return ProcessingResult
     */
    protected function searchForOpenEMRRecords($openEMRSearchParameters): ProcessingResult
    {
        // Translate FHIR category codes to OpenEMR care_plan_type values
        if (isset($openEMRSearchParameters['care_plan_type'])) {
            $categoryField = $openEMRSearchParameters['care_plan_type'];
            if ($categoryField instanceof \OpenEMR\Services\Search\TokenSearchField) {
                $translatedValues = [];
                foreach ($categoryField->getValues() as $value) {
                    if ($value instanceof \OpenEMR\Services\Search\TokenSearchValue) {
                        $fhirCode = $value->getCode();
                        // Map FHIR category to OpenEMR care_plan_type
                        $openEmrType = match ($fhirCode) {
                            'assess-plan' => 'plan_of_care',
                            'goal' => 'goal',
                            default => $fhirCode // Pass through if unknown
                        };
                        // Create new TokenSearchValue with translated code
                        // Constructor: TokenSearchValue($code, $system, $exact)
                        $translatedValues[] = new \OpenEMR\Services\Search\TokenSearchValue(
                            $openEmrType,
                            $value->getSystem(),
                            false  // exact match flag
                        );
                    }
                }
                if (!empty($translatedValues)) {
                    $categoryField->setValues($translatedValues);
                }
            }
        }

        return $this->service->search($openEMRSearchParameters, true);
    }

    /**
     * Create provenance resource for care plan
     */
    public function createProvenanceResource($dataRecord, $encode = false)
    {
        if (!($dataRecord instanceof FHIRCarePlan)) {
            throw new \BadMethodCallException("Data record should be correct instance class");
        }
        $provenanceService = new FhirProvenanceService();
        $provenance = $provenanceService->createProvenanceForDomainResource($dataRecord, $dataRecord->getAuthor());

        if ($encode) {
            return json_encode($provenance);
        }
        return $provenance;
    }

    /**
     * Get profile URIs for this resource
     */
    public function getProfileURIs(): array
    {
        return $this->getProfileForVersions(self::USCGI_PROFILE_URI, $this->getSupportedVersions());
    }
}
