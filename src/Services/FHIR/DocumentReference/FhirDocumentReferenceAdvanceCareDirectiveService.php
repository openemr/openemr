<?php

/**
 * FhirPatientDocumentReferenceService.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR\DocumentReference;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRDocumentReference;
use OpenEMR\FHIR\R4\FHIRElement\FHIRAttachment;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod;
use OpenEMR\FHIR\R4\FHIRElement\FHIRString;
use OpenEMR\FHIR\R4\FHIRElement\FHIRUrl;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDocumentReference\FHIRDocumentReferenceContent;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDocumentReference\FHIRDocumentReferenceContext;
use OpenEMR\Services\CodeTypesService;
use OpenEMR\Services\DocumentService;
use OpenEMR\Services\FHIR\DocumentReference\Enum\DocumentReferenceCategoryEnum;
use OpenEMR\Services\FHIR\DocumentReference\Trait\FhirDocumentReferenceTrait;
use OpenEMR\Services\FHIR\FhirCodeSystemConstants;
use OpenEMR\Services\FHIR\FhirOrganizationService;
use OpenEMR\Services\FHIR\FhirProvenanceService;
use OpenEMR\Services\FHIR\FhirServiceBase;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\FHIR\Traits\PatientSearchTrait;
use OpenEMR\Services\FHIR\UtilsService;
use OpenEMR\Services\PatientAdvanceDirectiveService;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\SearchModifier;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Services\Search\TokenSearchValue;
use OpenEMR\Validators\ProcessingResult;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class FhirDocumentReferenceAdvanceCareDirectiveService extends FhirServiceBase
{
    use PatientSearchTrait;
    use FhirDocumentReferenceTrait;

    const US_CORE_ADI_PROFILE = "http://hl7.org/fhir/us/core/StructureDefinition/us-core-adi-documentreference";

    /**
     * @var PatientAdvanceDirectiveService
     */
    private PatientAdvanceDirectiveService $service;

    public function __construct($fhirApiURL = null)
    {
        parent::__construct($fhirApiURL);
    }

    public function getProfileUrl(): string
    {
        return self::US_CORE_ADI_PROFILE;
    }

    public function setSession(SessionInterface $session): void
    {
        parent::setSession($session);
        $this->getADIService()->setSession($session);
    }

    public function getADIService(): PatientAdvanceDirectiveService
    {
        if (!isset($this->service)) {
            $this->service = new PatientAdvanceDirectiveService();
        }
        return $this->service;
    }


    public function supportsCategory($category)
    {
        return DocumentReferenceCategoryEnum::tryFrom($category) == DocumentReferenceCategoryEnum::ADVANCE_CARE_DIRECTIVE;
    }


    public function supportsCode($code)
    {

        return false;
    }

    protected function loadSearchParameters()
    {
        return  [
            'patient' => $this->getPatientContextSearchField(),
            'date' => new FhirSearchParameterDefinition('date', SearchFieldType::DATETIME, ['date']),
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, [new ServiceField('uuid', ServiceField::TYPE_UUID)]),
            '_lastUpdated' => $this->getLastModifiedSearchField(),
            'category' => new FhirSearchParameterDefinition('category', SearchFieldType::TOKEN, ['category_codes']),
            // US Core 8.0 requires support for searching by category
        ];
    }

    public function getLastModifiedSearchField(): ?FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('_lastUpdated', SearchFieldType::DATETIME, ['date']);
    }

    /**
     * @param array<string, ISearchField> $openEMRSearchParameters OpenEMR search fields
     * @return ProcessingResult OpenEMR records
     */
    protected function searchForOpenEMRRecords($openEMRSearchParameters): ProcessingResult
    {
        if (isset($openEMRSearchParameters['category'])) {
            // we have nothing with this category so we are going to return nothing as we never should have gotten here

            unset($openEMRSearchParameters['category']);
        }
        if (isset($openEMRSearchParameters['puuid'])) {
            // make sure that no other modifier such as NOT_EQUALS, OR missing=true is sent which would let system file names be
            // leaked out in the API
            $openEMRSearchParameters['puuid']->setModifier(SearchModifier::EXACT);
        } else {
            // make sure we only return documents that are tied to patients
            $openEMRSearchParameters['puuid'] = new TokenSearchField('puuid', [new TokenSearchValue(false, null)]);
            $openEMRSearchParameters['puuid']->setModifier(SearchModifier::MISSING);
        }
        return $this->getADIService()->search($openEMRSearchParameters);
    }

    protected function parseOpenEMRRecordIntoFHIRDocumentReference($dataRecord = []): FHIRDocumentReference
    {
        $docReference = new FHIRDocumentReference();
        $this->populateMetaData($docReference, $dataRecord);
        $this->populateId($docReference, $dataRecord);
        $this->populateIdentifiers($docReference, $dataRecord);
        $this->populateDate($docReference, $dataRecord);
        $this->populateContext($docReference, $dataRecord);
        $this->populateContent($docReference, $dataRecord);
        $this->populateSubject($docReference, $dataRecord);
        $this->populateCategories($docReference, $dataRecord);
        $this->populateAuthor($docReference, $dataRecord);
        $this->populateStatus($docReference, $dataRecord);
        $this->populateType($docReference, $dataRecord);
        $this->populateAuthenticator($docReference, $dataRecord);
        $this->populateAuthenticationTime($docReference, $dataRecord);

        return $docReference;
    }

    protected function populateAuthenticator(FHIRDocumentReference $docReference, array $dataRecord): void
    {
        // Advance Care Directives do not have an authenticator
        if ($this->hasValidAuthenticator($dataRecord)) {
            $practitionerReference = UtilsService::createRelativeReference('Practitioner'
                , $dataRecord['authenticator']['uuid']);
            $docReference->setAuthenticator($practitionerReference);
        }
    }

    protected function hasValidAuthenticator(array $dataRecord): bool
    {
        return !empty($dataRecord['authenticator']['uuid']) && isset($dataRecord['authenticator']['date_reviewed']);
    }

    protected function populateAuthenticationTime(FHIRDocumentReference $docReference, array $dataRecord): void
    {
        if ($this->hasValidAuthenticator($dataRecord)) {
            $extension = new FHIRExtension();
            $extension->setUrl("http://hl7.org/fhir/us/core/StructureDefinition/us-core-authentication-time");
            $extension->setValueDateTime(UtilsService::getLocalDateAsUTC($dataRecord['authenticator']['date_reviewed']));
            $docReference->addExtension($extension);
        }
    }

    protected function populateCategories(FHIRDocumentReference $docReference, array $dataRecord): void
    {
        // mandatory category
        $docReference->addCategory(UtilsService::createCodeableConcept([
                '42348-3' => [
                    'system' => FhirCodeSystemConstants::LOINC,
                    'description' => 'Advance healthcare directives',
                    'code' => '42348-3'
                ]
        ]));
    }

    protected function populateType(FHIRDocumentReference $docReference, array $dataRecord): void
    {
        if (!empty($dataRecord['category_codes'])) {
            $codeTypeSystem = new CodeTypesService();
            $parsedCode = $codeTypeSystem->parseCode($dataRecord['category_codes']);
            $description = $codeTypeSystem->lookup_code_description($dataRecord['category_codes']);

            $docReference->setType(UtilsService::createCodeableConcept([
                $parsedCode['code'] => [
                    'system' => $codeTypeSystem->getSystemForCodeType($parsedCode['code_type']),
                    'description' => $description,
                    'code' => $parsedCode['code']
                ]
            ]));
        }
    }


    public function populateDate(FHIRDocumentReference $docReference, array $dataRecord)
    {
        if (!empty($dataRecord['created_date'])) {
            $docReference->setDate(UtilsService::getLocalDateAsUTC($dataRecord['created_date']));
        } else {
            $docReference->setDate(UtilsService::createDataMissingExtension());
        }
    }
}
