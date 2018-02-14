<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRConceptMap;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * A statement of relationships from one set of concepts to one or more other concepts - either code systems or data elements, or classes in class models.
 */
class FHIRConceptMapTarget extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Identity (code or path) or the element/item that the map refers to.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCode
     */
    public $code = null;

    /**
     * The display for the code. The display is only provided to help editors when editing the concept map.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $display = null;

    /**
     * The equivalence between the source and target concepts (counting for the dependencies and products). The equivalence is read from target to source (e.g. the target is 'wider' than the source).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRConceptMapEquivalence
     */
    public $equivalence = null;

    /**
     * A description of status/issues in mapping that conveys additional information not represented in  the structured data.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $comment = null;

    /**
     * A set of additional dependencies for this mapping to hold. This mapping is only applicable if the specified element can be resolved, and it has the specified value.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRConceptMap\FHIRConceptMapDependsOn[]
     */
    public $dependsOn = [];

    /**
     * A set of additional outcomes from this mapping to other elements. To properly execute this mapping, the specified element must be mapped to some data element or source that is in context. The mapping may still be useful without a place for the additional data elements, but the equivalence cannot be relied on.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRConceptMap\FHIRConceptMapDependsOn[]
     */
    public $product = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'ConceptMap.Target';

    /**
     * Identity (code or path) or the element/item that the map refers to.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCode
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Identity (code or path) or the element/item that the map refers to.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCode $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * The display for the code. The display is only provided to help editors when editing the concept map.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getDisplay()
    {
        return $this->display;
    }

    /**
     * The display for the code. The display is only provided to help editors when editing the concept map.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $display
     * @return $this
     */
    public function setDisplay($display)
    {
        $this->display = $display;
        return $this;
    }

    /**
     * The equivalence between the source and target concepts (counting for the dependencies and products). The equivalence is read from target to source (e.g. the target is 'wider' than the source).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRConceptMapEquivalence
     */
    public function getEquivalence()
    {
        return $this->equivalence;
    }

    /**
     * The equivalence between the source and target concepts (counting for the dependencies and products). The equivalence is read from target to source (e.g. the target is 'wider' than the source).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRConceptMapEquivalence $equivalence
     * @return $this
     */
    public function setEquivalence($equivalence)
    {
        $this->equivalence = $equivalence;
        return $this;
    }

    /**
     * A description of status/issues in mapping that conveys additional information not represented in  the structured data.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * A description of status/issues in mapping that conveys additional information not represented in  the structured data.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $comment
     * @return $this
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * A set of additional dependencies for this mapping to hold. This mapping is only applicable if the specified element can be resolved, and it has the specified value.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRConceptMap\FHIRConceptMapDependsOn[]
     */
    public function getDependsOn()
    {
        return $this->dependsOn;
    }

    /**
     * A set of additional dependencies for this mapping to hold. This mapping is only applicable if the specified element can be resolved, and it has the specified value.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRConceptMap\FHIRConceptMapDependsOn $dependsOn
     * @return $this
     */
    public function addDependsOn($dependsOn)
    {
        $this->dependsOn[] = $dependsOn;
        return $this;
    }

    /**
     * A set of additional outcomes from this mapping to other elements. To properly execute this mapping, the specified element must be mapped to some data element or source that is in context. The mapping may still be useful without a place for the additional data elements, but the equivalence cannot be relied on.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRConceptMap\FHIRConceptMapDependsOn[]
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * A set of additional outcomes from this mapping to other elements. To properly execute this mapping, the specified element must be mapped to some data element or source that is in context. The mapping may still be useful without a place for the additional data elements, but the equivalence cannot be relied on.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRConceptMap\FHIRConceptMapDependsOn $product
     * @return $this
     */
    public function addProduct($product)
    {
        $this->product[] = $product;
        return $this;
    }

    /**
     * @return string
     */
    public function get_fhirElementName()
    {
        return $this->_fhirElementName;
    }

    /**
     * @param mixed $data
     */
    public function __construct($data = [])
    {
        if (is_array($data)) {
            if (isset($data['code'])) {
                $this->setCode($data['code']);
            }
            if (isset($data['display'])) {
                $this->setDisplay($data['display']);
            }
            if (isset($data['equivalence'])) {
                $this->setEquivalence($data['equivalence']);
            }
            if (isset($data['comment'])) {
                $this->setComment($data['comment']);
            }
            if (isset($data['dependsOn'])) {
                if (is_array($data['dependsOn'])) {
                    foreach ($data['dependsOn'] as $d) {
                        $this->addDependsOn($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"dependsOn" must be array of objects or null, '.gettype($data['dependsOn']).' seen.');
                }
            }
            if (isset($data['product'])) {
                if (is_array($data['product'])) {
                    foreach ($data['product'] as $d) {
                        $this->addProduct($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"product" must be array of objects or null, '.gettype($data['product']).' seen.');
                }
            }
        } else if (null !== $data) {
            throw new \InvalidArgumentException('$data expected to be array of values, saw "'.gettype($data).'"');
        }
        parent::__construct($data);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->get_fhirElementName();
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $json = parent::jsonSerialize();
        if (isset($this->code)) {
            $json['code'] = $this->code;
        }
        if (isset($this->display)) {
            $json['display'] = $this->display;
        }
        if (isset($this->equivalence)) {
            $json['equivalence'] = $this->equivalence;
        }
        if (isset($this->comment)) {
            $json['comment'] = $this->comment;
        }
        if (0 < count($this->dependsOn)) {
            $json['dependsOn'] = [];
            foreach ($this->dependsOn as $dependsOn) {
                $json['dependsOn'][] = $dependsOn;
            }
        }
        if (0 < count($this->product)) {
            $json['product'] = [];
            foreach ($this->product as $product) {
                $json['product'][] = $product;
            }
        }
        return $json;
    }

    /**
     * @param boolean $returnSXE
     * @param \SimpleXMLElement $sxe
     * @return string|\SimpleXMLElement
     */
    public function xmlSerialize($returnSXE = false, $sxe = null)
    {
        if (null === $sxe) {
            $sxe = new \SimpleXMLElement('<ConceptMapTarget xmlns="http://hl7.org/fhir"></ConceptMapTarget>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->code)) {
            $this->code->xmlSerialize(true, $sxe->addChild('code'));
        }
        if (isset($this->display)) {
            $this->display->xmlSerialize(true, $sxe->addChild('display'));
        }
        if (isset($this->equivalence)) {
            $this->equivalence->xmlSerialize(true, $sxe->addChild('equivalence'));
        }
        if (isset($this->comment)) {
            $this->comment->xmlSerialize(true, $sxe->addChild('comment'));
        }
        if (0 < count($this->dependsOn)) {
            foreach ($this->dependsOn as $dependsOn) {
                $dependsOn->xmlSerialize(true, $sxe->addChild('dependsOn'));
            }
        }
        if (0 < count($this->product)) {
            foreach ($this->product as $product) {
                $product->xmlSerialize(true, $sxe->addChild('product'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
