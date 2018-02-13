<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRComposition;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * A set of healthcare-related information that is assembled together into a single logical document that provides a single coherent statement of meaning, establishes its own context and that has clinical attestation with regard to who is making the statement. While a Composition defines the structure, it does not actually contain the content: rather the full content of a document is contained in a Bundle, of which the Composition is the first resource contained.
 */
class FHIRCompositionRelatesTo extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The type of relationship that this composition has with anther composition or document.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDocumentRelationshipType
     */
    public $code = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier
     */
    public $targetIdentifier = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $targetReference = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Composition.RelatesTo';

    /**
     * The type of relationship that this composition has with anther composition or document.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDocumentRelationshipType
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * The type of relationship that this composition has with anther composition or document.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDocumentRelationshipType $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier
     */
    public function getTargetIdentifier()
    {
        return $this->targetIdentifier;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier $targetIdentifier
     * @return $this
     */
    public function setTargetIdentifier($targetIdentifier)
    {
        $this->targetIdentifier = $targetIdentifier;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getTargetReference()
    {
        return $this->targetReference;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $targetReference
     * @return $this
     */
    public function setTargetReference($targetReference)
    {
        $this->targetReference = $targetReference;
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
            if (isset($data['targetIdentifier'])) {
                $this->setTargetIdentifier($data['targetIdentifier']);
            }
            if (isset($data['targetReference'])) {
                $this->setTargetReference($data['targetReference']);
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
        if (isset($this->targetIdentifier)) {
            $json['targetIdentifier'] = $this->targetIdentifier;
        }
        if (isset($this->targetReference)) {
            $json['targetReference'] = $this->targetReference;
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
            $sxe = new \SimpleXMLElement('<CompositionRelatesTo xmlns="http://hl7.org/fhir"></CompositionRelatesTo>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->code)) {
            $this->code->xmlSerialize(true, $sxe->addChild('code'));
        }
        if (isset($this->targetIdentifier)) {
            $this->targetIdentifier->xmlSerialize(true, $sxe->addChild('targetIdentifier'));
        }
        if (isset($this->targetReference)) {
            $this->targetReference->xmlSerialize(true, $sxe->addChild('targetReference'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
