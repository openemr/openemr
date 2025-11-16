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
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod;
use OpenEMR\FHIR\R4\FHIRElement\FHIRString;
use OpenEMR\FHIR\R4\FHIRElement\FHIRUrl;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDocumentReference\FHIRDocumentReferenceContent;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDocumentReference\FHIRDocumentReferenceContext;
use OpenEMR\Services\DocumentService;
use OpenEMR\Services\FHIR\DocumentReference\Enum\DocumentReferenceAdvancedDirectiveCodeEnum;
use OpenEMR\Services\FHIR\DocumentReference\Enum\DocumentReferenceCategoryEnum;
use OpenEMR\Services\FHIR\FhirCodeSystemConstants;
use OpenEMR\Services\FHIR\FhirOrganizationService;
use OpenEMR\Services\FHIR\FhirProvenanceService;
use OpenEMR\Services\FHIR\FhirServiceBase;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\FHIR\Traits\PatientSearchTrait;
use OpenEMR\Services\FHIR\UtilsService;
use OpenEMR\Services\Search\CompositeSearchField;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\SearchModifier;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Services\Search\StringSearchField;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Services\Search\TokenSearchValue;
use OpenEMR\Validators\ProcessingResult;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class FhirPatientDocumentReferenceService extends FhirServiceBase
{
    use FhirServiceBaseEmptyTrait;
    use PatientSearchTrait;

    /**
     * @var DocumentService
     */
    private DocumentService $service;

    public function __construct($fhirApiURL = null)
    {
        parent::__construct($fhirApiURL);
        $this->service = new DocumentService();
    }

    public function setSession(SessionInterface $session): void
    {
        parent::setSession($session);
        $this->service->setSession($session);
    }


    public function supportsCategory($category)
    {
        return !in_array(DocumentReferenceCategoryEnum::tryFrom($category), DocumentReferenceCategoryEnum::cases());
    }


    public function supportsCode($code)
    {
        // exclude advanced directive codes as those are handled by another service
        return !in_array(DocumentReferenceAdvancedDirectiveCodeEnum::tryFrom($code), DocumentReferenceAdvancedDirectiveCodeEnum::cases());
    }

    protected function loadSearchParameters()
    {
        return  [
            'patient' => $this->getPatientContextSearchField(),
            'date' => new FhirSearchParameterDefinition('date', SearchFieldType::DATETIME, ['date']),
            'category' => new FhirSearchParameterDefinition('category', SearchFieldType::DATETIME, ['category_codes']),
            'type' => new FhirSearchParameterDefinition('type', SearchFieldType::DATETIME, ['category_codes']),
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, [new ServiceField('uuid', ServiceField::TYPE_UUID)]),
            '_lastUpdated' => $this->getLastModifiedSearchField(),
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
        if (isset($openEMRSearchParameters['patient'])) {
            // make sure that no other modifier such as NOT_EQUALS, OR missing=true is sent which would let system file names be
            // leaked out in the API
            $openEMRSearchParameters['patient']->setModifier(SearchModifier::EXACT);
        } else {
            // make sure we only return documents that are tied to patients
            $openEMRSearchParameters['patient'] = new TokenSearchField('puuid', [new TokenSearchValue(false, null)]);
            $openEMRSearchParameters['patient']->setModifier(SearchModifier::MISSING);
        }
        // we need to exclude the adi codes here as those are handled by another service
        // going to use a NOT_EQUALS_EXACT modifier on a string field for this
        $tokenField = new StringSearchField('category_codes', DocumentReferenceAdvancedDirectiveCodeEnum::getFullOpenEMRCodeList(), SearchModifier::NOT_EQUALS_EXACT, true);
        if (isset($openEMRSearchParameters['category_codes'])) {
            $compositeField = new CompositeSearchField('codes-filter', [], false);
            $compositeField->addChild($openEMRSearchParameters['category_codes']);
            $compositeField->addChild($tokenField);
        } else {
            $openEMRSearchParameters['category_codes'] = $tokenField;
        }
        return $this->service->search($openEMRSearchParameters);
    }

    public function createProvenanceResource($dataRecord, $encode = false)
    {
        if (!($dataRecord instanceof FHIRDocumentReference)) {
            throw new \BadMethodCallException("Data record should be correct instance class");
        }

        $fhirProvenanceService = new FhirProvenanceService();
        $authors = $dataRecord->getAuthor();
        $author = null;
        if (!empty($authors)) {
            $author = reset($authors); // grab the first one, as we only populate one anyways.
        }
        $fhirProvenance = $fhirProvenanceService->createProvenanceForDomainResource($dataRecord, $author);
        if ($encode) {
            return json_encode($fhirProvenance);
        } else {
            return $fhirProvenance;
        }
    }
}
