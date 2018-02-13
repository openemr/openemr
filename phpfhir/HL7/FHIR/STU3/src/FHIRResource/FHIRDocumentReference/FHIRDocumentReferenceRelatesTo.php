<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRDocumentReference;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * A reference to a document.
 */
class FHIRDocumentReferenceRelatesTo extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The type of relationship that this document has with anther document.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDocumentRelationshipType
     */
    public $code = null;

    /**
     * The target document of this relationship.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $target = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'DocumentReference.RelatesTo';

    /**
     * The type of relationship that this document has with anther document.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDocumentRelationshipType
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * The type of relationship that this document has with anther document.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDocumentRelationshipType $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * The target document of this relationship.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * The target document of this relationship.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $target
     * @return $this
     */
    public function setTarget($target)
    {
        $this->target = $target;
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
            if (isset($data['target'])) {
                $this->setTarget($data['target']);
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
        if (isset($this->target)) {
            $json['target'] = $this->target;
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
            $sxe = new \SimpleXMLElement('<DocumentReferenceRelatesTo xmlns="http://hl7.org/fhir"></DocumentReferenceRelatesTo>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->code)) {
            $this->code->xmlSerialize(true, $sxe->addChild('code'));
        }
        if (isset($this->target)) {
            $this->target->xmlSerialize(true, $sxe->addChild('target'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
