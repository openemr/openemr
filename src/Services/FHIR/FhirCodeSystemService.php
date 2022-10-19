<?php

namespace OpenEMR\Services\FHIR;

use CategoryTree;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCodeSystem;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCode;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRUri;
use OpenEMR\FHIR\R4\FHIRResource\FHIRCodeSystem\FHIRCodeSystemConcept;
use OpenEMR\FHIR\R4\FHIRResource\FHIRCodeSystem\FHIRCodeSystemProperty1;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Validators\ProcessingResult;

class FhirCodeSystemService extends FhirServiceBase
{
    use FhirServiceBaseEmptyTrait;

    // TODO: when we've received more feedback on this implementation we can bump this to active.
    const DEFAULT_CODE_SYSTEM_STATUS = "draft";
    const DOCUMENT_CATEGORY_TYPE = "document-category";


    const DEFAULT_CODE_SYSTEM_NAME = "openemr-concepts";
    // TODO: we need a mechanism for versioning these things if anything changes in our terminology definitions
    const DEFAULT_CODE_SYSTEM_VERSION = "0.0.1";

    public function __construct($defaultFhirApiUri = "")
    {
        parent::__construct($defaultFhirApiUri);
    }

    public function getAll($fhirSearchParameters, $puuidBind = null): ProcessingResult
    {
        // we will return the same thing here.
        return $this->getOne(self::DEFAULT_CODE_SYSTEM_NAME, $puuidBind);
    }

    public function getOne($fhirResourceId, $puuidBind = null): ProcessingResult
    {
        $processingResult = new ProcessingResult();
        if ($fhirResourceId == self::DEFAULT_CODE_SYSTEM_NAME) {
            $processingResult->addData($this->getDefaultCodeSystemDefinition());
        }

        return $processingResult;
    }

    public function getCodeSystemUri()
    {
        return $this->getFhirApiURL() . '/fhir/CodeSystem/' . self::DEFAULT_CODE_SYSTEM_NAME;
    }

    public function getDefaultCodeSystemVersion()
    {
        return self::DEFAULT_CODE_SYSTEM_VERSION;
    }

    public function getDefaultCodeSystemDefinition()
    {
        $cs = new FHIRCodeSystem();
        $id = new FHIRId();
        $id->setValue(self::DEFAULT_CODE_SYSTEM_NAME);
        $cs->setId($id);

        // we need to grab our document types and add them
        $csUri = new FHIRUri();
        $csUri->setValue($this->getCodeSystemUri()); // needs to be the FHIR base URI + our /fhir/CodeSystem/openemr-terms definition
        $cs->setUrl($csUri);
        $cs->setVersion(self::DEFAULT_CODE_SYSTEM_VERSION);
        $cs->setStatus(self::DEFAULT_CODE_SYSTEM_STATUS);
        $cs->setDescription("These are the concepts, terms, and definnitions that are local to this installation and "
            . "custom configuration of OpenEMR.  This Code System is a draft and is expected to change in the future, such as becoming a subset of a codesystem maintained by the OpenEMR project.");
        $csCode = new FHIRCode();
        $csCode->setValue("complete");
        $cs->setContent($csCode);

        $this->addDocumentConceptsToCodeSystem($cs);
        return $cs;
    }

    public function getOpenEMRDocumentCategoryCodeFromCategoryId($id)
    {
        $uniquePrefix = "OEDC"; // O=Open, E=EMR, D=Document, C=Categories
        return $uniquePrefix . $id;
    }

    public function getDocumentCategoryConcepts(): ?FHIRCodeSystemConcept
    {
        $t = new CategoryTree(1);
        $t->load_tree();
        $concept = $this->getDocumentConceptForCategoryTreeNode($t, 1);
        $this->convertNodeArrayToDocumentConcept($t, $concept, reset($t->tree));
        return $concept;
    }

    private function addDocumentConceptsToCodeSystem(FHIRCodeSystem $cs)
    {
        // grab our list of document categories and give them a unique identifier so we don't conflict with other openemr code systems
        $fhirNode = $this->getDocumentCategoryConcepts();
        if (!empty($fhirNode)) {
            $cs->addConcept($fhirNode);
        }
    }

    private function convertNodeArrayToDocumentConcept(CategoryTree $tree, FHIRCodeSystemConcept $parentConcept, $currentNode, $depth = 0)
    {
        // we want to avoid more than 20 heirarchies of categories as that suggests a bug
        // and if the concept for some reason is null, also a bug.
        if ($depth > 20 || empty($currentNode)) {
            return;
        }

        foreach ($currentNode as $key => $childNode) {
            if ($key == 0) {
                continue; // not sure why the array has 0 as a key, but we are going to skip it here.
            }
            $csConcept = $this->getDocumentConceptForCategoryTreeNode($tree, $key);
            if (is_array($childNode)) {
                $this->convertNodeArrayToDocumentConcept($tree, $csConcept, $childNode, $depth + 1);
            }
            $parentConcept->addConcept($csConcept);
        }
    }

    private function getDocumentConceptForCategoryTreeNode(CategoryTree $node, $nodeId)
    {

        $csConcept = new FHIRCodeSystemConcept();
        $csConcept->setCode($this->getOpenEMRDocumentCategoryCodeFromCategoryId($nodeId));
        $csConcept->setDisplay($node->get_node_name($nodeId));
        $propertyCode = new FhirCode();
        $propertyCode->setValue("type");
        $propertyType = new FHIRCodeSystemProperty1();
        $propertyType->setCode($propertyCode);
        $propertyType->setValueString(self::DOCUMENT_CATEGORY_TYPE);
        $csConcept->addProperty($propertyType);
        return $csConcept;
    }
}
