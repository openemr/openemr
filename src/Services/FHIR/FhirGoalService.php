<?php

/**
 * FhirGoalService.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRGoal;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRDate;
use OpenEMR\FHIR\R4\FHIRElement\FHIRGoalLifecycleStatus;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRResource\FHIRGoal\FHIRGoalTarget;
use OpenEMR\Services\CarePlanService;
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

class FhirGoalService extends FhirServiceBase implements IResourceUSCIGProfileService, IFhirExportableResourceService, IPatientCompartmentResourceService
{
    use FhirServiceBaseEmptyTrait;
    use BulkExportSupportAllOperationsTrait;
    use FhirBulkExportDomainResourceTrait;
    use VersionedProfileTrait;

    /**
     * @var CarePlanService
     */
    private $service;

    const USCGI_PROFILE_URI = 'http://hl7.org/fhir/us/core/StructureDefinition/us-core-goal';

    public function __construct()
    {
        parent::__construct();
        // goals are stored inside the care plan forms
        $this->service = new CarePlanService(CarePlanService::TYPE_GOAL);
    }

    /**
     * Returns an array mapping FHIR Resource search parameters to OpenEMR search parameters
     * @return array The search parameters
     */
    protected function loadSearchParameters()
    {
        return  [
            'patient' => $this->getPatientContextSearchField(),
            // note even though we label this as a uuid, it is a SURROGATE UID because of the nature of how goals are stored
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, ['uuid']),
            '_lastUpdated' => $this->getLastModifiedSearchField(),
            'lifecycle-status' => new FhirSearchParameterDefinition('lifecycle-status', SearchFieldType::TOKEN, ['plan_status']),
            'target-date' => new FhirSearchParameterDefinition('target-date', SearchFieldType::DATE, ['date_end']),
            'description' => new FhirSearchParameterDefinition('description', SearchFieldType::TOKEN, ['code']),
        ];
    }

    public function getLastModifiedSearchField(): ?FhirSearchParameterDefinition
    {
        // TODO: @adunsulag introduce a last_modified date field to the care plan table as we don't track this anywhere
        return new FhirSearchParameterDefinition('_lastUpdated', SearchFieldType::DATETIME, ['creation_date']);
    }

    /**
     * Parses an OpenEMR careTeam record, returning the equivalent FHIR CareTeam Resource
     *
     * @param array $dataRecord The source OpenEMR data record
     * @param boolean $encode Indicates if the returned resource is encoded into a string. Defaults to false.
     * @return FHIRGoal
     */
    public function parseOpenEMRRecord($dataRecord = [], $encode = false)
    {
        $goal = new FHIRGoal();

        // FIXED: Add US Core profile to meta
        $fhirMeta = new FHIRMeta();
        $fhirMeta->setVersionId('1');
        if (!empty($dataRecord['creation_date'])) {
            $fhirMeta->setLastUpdated(UtilsService::getLocalDateAsUTC($dataRecord['creation_date']));
        } else {
            $fhirMeta->setLastUpdated(UtilsService::getDateFormattedAsUTC());
        }
        $fhirMeta->addProfile(self::USCGI_PROFILE_URI);
        $goal->setMeta($fhirMeta);

        $fhirId = new FHIRId();
        $fhirId->setValue($dataRecord['uuid']);
        $goal->setId($fhirId);

        if (isset($dataRecord['puuid'])) {
            $goal->setSubject(UtilsService::createRelativeReference("Patient", $dataRecord['puuid']));
        } else {
            $goal->setSubject(UtilsService::createDataMissingExtension());
        }

        // US Core 8.0: lifecycleStatus is mandatory and Must Support
        $lifecycleStatus = $this->mapPlanStatusToLifecycleStatus($dataRecord['plan_status'] ?? null);
        $fhirLifecycleStatus = new FHIRGoalLifecycleStatus();
        $fhirLifecycleStatus->setValue($lifecycleStatus);
        $goal->setLifecycleStatus($fhirLifecycleStatus);

        // US Core 8.0: achievementStatus is Must Support (new requirement)
        if (!empty($dataRecord['plan_status'])) {
            $achievementStatus = $this->mapPlanStatusToAchievementStatus($dataRecord['plan_status']);
            if ($achievementStatus) {
                $goal->setAchievementStatus($achievementStatus);
            }
        }

        // US Core 8.0: category is Must Support
        $category = new FHIRCodeableConcept();
        $categoryCoding = new FHIRCoding();
        $categoryCoding->setSystem('http://hl7.org/fhir/goal-category');

        // Check if this is an SDOH goal based on code or description
        if ($this->isSDOHGoal($dataRecord)) {
            $categoryCoding->setCode('sdoh');
            $categoryCoding->setDisplay('Social Determinants of Health');
        } else {
            $categoryCoding->setCode('physiological');
            $categoryCoding->setDisplay('Physiological');
        }
        $category->addCoding($categoryCoding);
        $goal->addCategory($category);

        // US Core 8.0: expressedBy is Must Support for provenance
        if (!empty($dataRecord['provider_uuid']) && !empty($dataRecord['provider_npi'])) {
            $goal->setExpressedBy(UtilsService::createRelativeReference("Practitioner", $dataRecord['provider_uuid']));
        }

        // US Core 8.0: startDate is Must Support (at least one of startDate or target.dueDate required)
        if (!empty($dataRecord['date'])) {
            $parsedDateTime = \DateTime::createFromFormat("Y-m-d H:i:s", $dataRecord['date'], new \DateTimeZone(date('P')));
            if ($parsedDateTime) {
                $fhirDate = new FHIRDate();
                $fhirDate->setValue($parsedDateTime->format("Y-m-d"));
                $goal->setStartDate($fhirDate);
            }
        }

        // US Core 8.0: description is mandatory and Must Support with preferred binding
        if (!empty($dataRecord['details'])) {
            $text = $this->getGoalTextFromDetails($dataRecord['details']);
            $codeableConcept = new FHIRCodeableConcept();

            // FIXED: Fix apostrophes in text
            $cleanText = str_replace('`', "'", $text['text']);
            $codeableConcept->setText($cleanText);

            // FIXED: Add coding from US Core Goal Description value set (no duplicates, clean codes)
            $codeTypeService = new CodeTypesService();
            $addedCodes = []; // Track codes to prevent duplicates

            foreach ($dataRecord['details'] as $detail) {
                if (!empty($detail['code'])) {
                    // FIXED: Strip prefix from code
                    $cleanCode = self::stripCodePrefix($detail['code']);

                    // FIXED: Prevent duplicate codes
                    if (in_array($cleanCode, $addedCodes)) {
                        continue;
                    }

                    $codeText = $codeTypeService->lookup_code_description($detail['code']);
                    $codeSystem = $codeTypeService->getSystemForCode($detail['code']);

                    $coding = new FHIRCoding();
                    $coding->setCode($cleanCode);
                    if (!empty($codeText)) {
                        // FIXED: Fix apostrophes in display text
                        $cleanDisplay = str_replace('`', "'", xlt($codeText));
                        $coding->setDisplay($cleanDisplay);
                    }
                    $coding->setSystem($codeSystem);
                    $codeableConcept->addCoding($coding);

                    $addedCodes[] = $cleanCode;
                }
            }
            $goal->setDescription($codeableConcept);

            // US Core 8.0: target is Must Support with measure and dueDate
            foreach ($dataRecord['details'] as $detail) {
                $fhirGoalTarget = new FHIRGoalTarget();

                // target.dueDate from date_end (US Core 8.0 requirement)
                if (!empty($dataRecord['date_end'])) {
                    $parsedEndDate = \DateTime::createFromFormat("Y-m-d H:i:s", $dataRecord['date_end'], new \DateTimeZone(date('P')));
                    if ($parsedEndDate) {
                        $fhirDueDate = new FHIRDate();
                        $fhirDueDate->setValue($parsedEndDate->format("Y-m-d"));
                        $fhirGoalTarget->setDueDate($fhirDueDate);
                    }
                } elseif (!empty($detail['date'])) {
                    // Fallback to detail date if no date_end
                    $parsedDateTime = \DateTime::createFromFormat("Y-m-d H:i:s", $detail['date'], new \DateTimeZone(date('P')));
                    if ($parsedDateTime) {
                        $fhirDueDate = new FHIRDate();
                        $fhirDueDate->setValue($parsedDateTime->format("Y-m-d"));
                        $fhirGoalTarget->setDueDate($fhirDueDate);
                    }
                }

                $detailDescription = trim($detail['description'] ?? "");
                if (!empty($detailDescription)) {
                    // FIXED: Fix apostrophes in detail string
                    $cleanDetailDescription = str_replace('`', "'", $detailDescription);
                    $fhirGoalTarget->setDetailString($cleanDetailDescription);

                    // US Core 8.0: target.measure required if target.detail is populated
                    if (!empty($detail['code'])) {
                        // FIXED: Strip prefix from target measure code
                        $cleanCode = self::stripCodePrefix($detail['code']);

                        $codeText = $codeTypeService->lookup_code_description($detail['code']);
                        $codeSystem = $codeTypeService->getSystemForCode($detail['code']);

                        $targetCodeableConcept = new FHIRCodeableConcept();
                        $coding = new FHIRCoding();
                        $coding->setCode($cleanCode);
                        if (empty($codeText)) {
                            $coding->setDisplay(UtilsService::createDataMissingExtension());
                        } else {
                            // FIXED: Fix apostrophes in target display text
                            $cleanCodeText = str_replace('`', "'", xlt($codeText));
                            $coding->setDisplay($cleanCodeText);
                        }
                        $coding->setSystem($codeSystem);
                        $targetCodeableConcept->addCoding($coding);
                        $fhirGoalTarget->setMeasure($targetCodeableConcept);
                    } else {
                        $fhirGoalTarget->setMeasure(UtilsService::createDataMissingExtension());
                    }
                }
                $goal->addTarget($fhirGoalTarget);
            }
        }

        if ($encode) {
            return json_encode($goal);
        } else {
            return $goal;
        }
    }

    /**
     * Map OpenEMR plan_status to FHIR Goal lifecycleStatus
     *
     * @param string|null $planStatus
     * @return string
     */
    private function mapPlanStatusToLifecycleStatus(?string $planStatus): string
    {
        if (empty($planStatus)) {
            return 'active'; // Default per US Core
        }

        // Map common status values
        $statusMap = [
            'active' => 'active',
            'completed' => 'completed',
            'cancelled' => 'cancelled',
            'canceled' => 'cancelled',
            'on-hold' => 'on-hold',
            'entered-in-error' => 'entered-in-error',
        ];

        $lowerStatus = strtolower(trim($planStatus));
        return $statusMap[$lowerStatus] ?? 'active';
    }

    /**
     * Map OpenEMR plan_status to FHIR Goal achievementStatus
     * US Core 8.0 new requirement
     *
     * @param string $planStatus
     * @return FHIRCodeableConcept|null
     */
    private function mapPlanStatusToAchievementStatus(string $planStatus): ?FHIRCodeableConcept
    {
        $achievementMap = [
            'completed' => ['code' => 'achieved', 'display' => 'Achieved'],
            'active' => ['code' => 'in-progress', 'display' => 'In Progress'],
            'on-hold' => ['code' => 'sustaining', 'display' => 'Sustaining'],
            'cancelled' => ['code' => 'not-achieved', 'display' => 'Not Achieved'],
            'canceled' => ['code' => 'not-achieved', 'display' => 'Not Achieved'],
        ];

        $lowerStatus = strtolower(trim($planStatus));
        if (!isset($achievementMap[$lowerStatus])) {
            return null;
        }

        $achievement = $achievementMap[$lowerStatus];
        $codeableConcept = new FHIRCodeableConcept();
        $coding = new FHIRCoding();
        $coding->setSystem('http://terminology.hl7.org/CodeSystem/goal-achievement');
        $coding->setCode($achievement['code']);
        $coding->setDisplay($achievement['display']);
        $codeableConcept->addCoding($coding);

        return $codeableConcept;
    }

    /**
     * Check if goal is SDOH-related based on code or description
     * USCDI v5 requirement for SDOH Goals
     *
     * @param array $dataRecord
     * @return bool
     */
    private function isSDOHGoal(array $dataRecord): bool
    {
        // Check for SDOH-related codes or keywords
        $sdohKeywords = ['social', 'housing', 'food', 'transportation', 'education',
            'employment', 'financial', 'social support', 'stress'];

        foreach ($dataRecord['details'] ?? [] as $detail) {
            $description = strtolower($detail['description'] ?? '');
            $codetext = strtolower($detail['codetext'] ?? '');

            foreach ($sdohKeywords as $keyword) {
                if (str_contains($description, $keyword) || str_contains($codetext, $keyword)) {
                    return true;
                }
            }

            // Check if code is from SDOH code system
            $code = $detail['code'] ?? '';
            if (str_contains($code, 'SDOH') || str_contains($code, '96777')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Searches for OpenEMR records using OpenEMR search parameters
     *
     * @param array<string, ISearchField> $openEMRSearchParameters OpenEMR search fields
     * @return ProcessingResult
     */
    protected function searchForOpenEMRRecords($openEMRSearchParameters): ProcessingResult
    {
        return $this->service->search($openEMRSearchParameters, true);
    }

    public function createProvenanceResource($dataRecord, $encode = false)
    {
        if (!($dataRecord instanceof FHIRGoal)) {
            throw new \BadMethodCallException("Data record should be correct instance class");
        }
        $provenanceService = new FhirProvenanceService();
        $provenance = $provenanceService->createProvenanceForDomainResource($dataRecord, $dataRecord->getExpressedBy());
        return $provenance;
    }

    public function getProfileURIs(): array
    {
        return $this->getProfileForVersions(self::USCGI_PROFILE_URI, $this->getSupportedVersions());
    }

    public function getPatientContextSearchField(): FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('patient', SearchFieldType::REFERENCE, [new ServiceField('puuid', ServiceField::TYPE_UUID)]);
    }

    private function getGoalTextFromDetails($details)
    {
        $descriptions = [];
        foreach ($details as $detail) {
            // use description or fallback on codetext if needed
            $descriptions[] = $detail['description'] ?? $detail['codetext'] ?? "";
        }
        $carePlanText = ['text' => trim(implode("\n", $descriptions)), "xhtml" => ""];
        if (!empty($descriptions)) {
            $carePlanText['xhtml'] = "<p>" . implode("</p><p>", $descriptions) . "</p>";
        }
        return $carePlanText;
    }

    public static function stripCodePrefix(?string $code): ?string
    {
        if (empty($code)) {
            return $code;
        }
        $prefixes = [
            'LOINC:',
            'SNOMED:',
            'SNOMEDCT:',
            'SNOMED-CT:',
            'ICD10:',
            'ICD10CM:',
            'ICD-10:',
            'ICD-10-CM:',
            'ICD9:',
            'ICD-9:',
            'CPT:',
            'CPT4:',
            'RXNORM:',
            'CVX:',
            'UCUM:',
        ];
        foreach ($prefixes as $prefix) {
            if (stripos($code, $prefix) === 0) {
                return substr($code, strlen($prefix));
            }
        }

        return $code;
    }
}
