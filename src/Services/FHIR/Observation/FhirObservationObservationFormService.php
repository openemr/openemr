<?php

/*
 * FhirObservationObservationFormService.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR\Observation;

use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRObservation;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCode;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRUri;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;
use OpenEMR\Services\FHIR\FhirCodeSystemConstants;
use OpenEMR\Services\FHIR\FhirServiceBase;
use OpenEMR\Services\FHIR\IPatientCompartmentResourceService;
use OpenEMR\Services\FHIR\IResourceUSCIGProfileService;
use OpenEMR\Services\FHIR\Observation\Trait\FhirObservationTrait;
use OpenEMR\Services\FHIR\Traits\VersionedProfileTrait;
use OpenEMR\Services\ObservationService;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Validators\ProcessingResult;

class FhirObservationObservationFormService extends FhirServiceBase implements IPatientCompartmentResourceService, IResourceUSCIGProfileService
{
    use FhirObservationTrait;

    private ObservationService $observationService;
    const SUPPORTED_CATEGORIES = ['survey', 'exam', 'social-history', 'vital-signs', 'imaging', 'laboratory', 'procedure', 'survey', 'therapy'];

    public function __construct($fhirApiURL = null)
    {
        parent::__construct($fhirApiURL);
        $this->observationService = new ObservationService();
        $this->observationService->setSystemLogger($this->getSystemLogger());
    }

    public function supportsCategory($category): bool
    {
        return in_array($category, self::SUPPORTED_CATEGORIES);
    }

    public function supportsCode(string $code): bool
    {
        // we support pretty much any LOINC code, we could hit procedure_order_code and procedure_results to be
        // specific but we'll just let the query execute.
        return true;
    }


    /**
     * Returns an array mapping FHIR Resource search parameters to OpenEMR search parameters
     * @return array<string, FhirSearchParameterDefinition>
     */
    protected function loadSearchParameters(): array
    {
        return [
            'patient' => $this->getPatientContextSearchField(),
            'code' => new FhirSearchParameterDefinition('code', SearchFieldType::TOKEN, ['ob_code']),
            'category' => new FhirSearchParameterDefinition('category', SearchFieldType::TOKEN, ['ob_type']),
            'date' => new FhirSearchParameterDefinition('date', SearchFieldType::DATETIME, ['date']),
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, [
                new ServiceField('uuid', ServiceField::TYPE_UUID)
                ]),
            '_lastUpdated' => $this->getLastModifiedSearchField()
        ];
    }

    public function getLastModifiedSearchField(): ?FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('_lastUpdated', SearchFieldType::DATETIME, ['report_date']);
    }

    private function createProfile(string $profileUri): FHIRCanonical
    {
        $profile = new FHIRCanonical();
        $profile->setValue($profileUri);
        return $profile;
    }
    protected function setObservationProfile(FHIRMeta $meta, FHIRObservation $observation, array $dataRecord): void
    {
        foreach ($this->getProfileForVersions(self::USCGI_PROFILE_URI, $this->getSupportedVersions()) as $profile) {
            $meta->addProfile($this->createProfile($profile));
        }

        if ($observation->getCategory() !== null) {
            foreach ($observation->getCategory() as $category) {
                if ($category->getCoding() !== null) {
                    $coding = $category->getCoding()[0]; // there's only one coding per category in our implementation
                    if ($coding->getCode() !== null && in_array($coding->getCode()->getValue(), self::US_CORE_CODESYSTEM_OBSERVATION_CATEGORY)) {
                        // this observation has a category in the US Core observation category code system, so we add the screening/assessment profile
                        foreach ($this->getProfileForVersions(self::USCGI_SCREENING_ASSESSMENT_URI, $this->getSupportedVersions()) as $profile) {
                            $meta->addProfile($this->createProfile($profile));
                        }
                        break;
                    }
                    if ($coding->getCode() !== null && in_array($coding->getCode()->getValue(), self::US_CORE_CODESYSTEM_CATEGORY)) {
                        // this observation has a category in the US Core category code system, so we add the screening/assessment profile
                        foreach ($this->getProfileForVersions(self::USCGI_SCREENING_ASSESSMENT_URI, $this->getSupportedVersions()) as $profile) {
                            $meta->addProfile($this->createProfile($profile));
                        }
                        break;
                    }
                }
            }
        }
    }

    /**
     * Searches for OpenEMR records using OpenEMR search parameters
     * @param array<string, ISearchField> $openEMRSearchParameters OpenEMR search fields
     * @return ProcessingResult OpenEMR records
     */
    protected function searchForOpenEMRRecords($openEMRSearchParameters): ProcessingResult
    {
        // we grab the records and grab any children records and populate them if we have them.
        return $this->observationService->searchAndPopulateChildObservations($openEMRSearchParameters);
    }

    /**
     * Set observation value or dataAbsentReason (mustSupport, constraint us-core-2)
     */
    protected function setObservationValue(FHIRObservation $observation, array $dataRecord): void
    {
        $this->setObservationValueWithDetails(
            $observation,
            $dataRecord['ob_value'] ?? null,
            $dataRecord['ob_unit'] ?? null,
            $dataRecord['ob_value_code_description'] ?? null,
            $dataRecord['sub_observations'] ?? []
        );
    }

    public function getPatientContextSearchField(): FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('patient', SearchFieldType::REFERENCE, [new ServiceField('puuid', ServiceField::TYPE_UUID)]);
    }

    public function getSupportedVersions()
    {
        return self::PROFILE_VERSIONS_V2;
    }

    public function getProfileURIs(): array
    {
        $profileSets = [
            $this->getProfileForVersions(self::USCGI_PROFILE_URI, $this->getSupportedVersions())
            , $this->getProfileForVersions(self::USCGI_SCREENING_ASSESSMENT_URI, $this->getSupportedVersions())
        ];
        return array_merge(...$profileSets);
    }
}
