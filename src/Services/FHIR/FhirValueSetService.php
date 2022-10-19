<?php

namespace OpenEMR\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRValueSet;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRResource\FHIRCodeSystem\FHIRCodeSystemConcept;
use OpenEMR\FHIR\R4\FHIRResource\FHIRValueSet\FHIRValueSetCompose;
use OpenEMR\FHIR\R4\FHIRResource\FHIRValueSet\FHIRValueSetConcept;
use OpenEMR\FHIR\R4\FHIRResource\FHIRValueSet\FHIRValueSetInclude;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Validators\ProcessingResult;

class FhirValueSetService extends FhirServiceBase
{
    use FhirServiceBaseEmptyTrait;

    const VALUE_SET_DOCUMENT_CATEGORIES_ID =  'openemr-document-categories';

    public function __construct($fhirApiURL = null)
    {
        parent::__construct($fhirApiURL);
    }

    public function getAll($fhirSearchParameters, $puuidBind = null): ProcessingResult
    {
        // we will return the same thing here.
        return $this->getOne('openemr-document-categories', $puuidBind);
    }

    public function getOne($fhirResourceId, $puuidBind = null): ProcessingResult
    {
        $processingResult = new ProcessingResult();
        if ($fhirResourceId == self::VALUE_SET_DOCUMENT_CATEGORIES_ID) {
            $processingResult->addData($this->getDefaultDocumentCategoriesValueSet());
        }

        return $processingResult;
    }


    private function getDefaultDocumentCategoriesValueSet()
    {
        $vs = new FHIRValueSet();
        $id = new FHIRId();
        $id->setValue(self::VALUE_SET_DOCUMENT_CATEGORIES_ID);
        $vs->setId($id);

        $vs->setStatus("draft"); // TODO: when we've received more feedback on this implementation we can bump this to active.
        $vs->setImmutable(false);
        $vs->setDescription("These are the document categories that are supported by this OpenEMR installation to be used in the DocumentReference resource."
            . " This ValueSet is a draft and is expected to change in the future, such as becoming an extension of a document categories value set maintained by the OpenEMR project.");

        $vsCompose = new FHIRValueSetCompose();
        $vsInclude = new FHIRValueSetInclude();
        $codeSystemService = new FhirCodeSystemService($this->getFhirApiURL());
        $oeSystemUri = $codeSystemService->getCodeSystemUri();
        $vsInclude->setSystem($oeSystemUri);
        $vsInclude->setVersion($codeSystemService->getDefaultCodeSystemVersion());


        $documentCategyConceptTree = $codeSystemService->getDocumentCategoryConcepts();


        $flattenedConcepts = $this->getFlattenedConceptsFromCodeSystemHierarchy($documentCategyConceptTree);
        foreach ($flattenedConcepts as $concept) {
            $vsInclude->addConcept($concept);
        }

        // if we wanted to programatically include, or let clients programmatically include the concepts, we can do that here...
//        $vsIncludeFilter = new FHIRValueSetFilter();
//        $vsIncludeFilter->setOp(new FhirCode("is-a")); // we want the document-category concept and all of its descendant concepts
//        $vsIncludeFilter->setProperty(new FhirCode("type"));
//        $vsIncludeFilter->setValue(new FhirCode(FhirCodeSystemRestController::DOCUMENT_CATEGORY_TYPE));
//        $vsInclude->addFilter($vsIncludeFilter);

        // for easy consumption of this value set we are just going to include all the codes as the list shouldn't be that much
        $vsCompose->addInclude($vsInclude);
        $vs->setCompose($vsCompose);

        return $vs;
    }

    private function getFlattenedConceptsFromCodeSystemHierarchy(FHIRCodeSystemConcept $concept, $depth = 0)
    {
        $convertedConcepts = [];

        $vsConcept = new FHIRValueSetConcept();
        $vsConcept->setCode($concept->getCode());
        $vsConcept->setDisplay($concept->getDisplay());
        $convertedConcepts[] = $vsConcept;
        if ($depth > 20) {
            return $convertedConcepts; // kill the recursion as we don't want to go beyond 20 nested concepts as that indicates a bug
        }
        foreach ($concept->getConcept() as $childConcept) {
            $convertedConcepts = array_merge($convertedConcepts, $this->getFlattenedConceptsFromCodeSystemHierarchy($childConcept, $depth + 1));
        }
        return $convertedConcepts;
    }
}
