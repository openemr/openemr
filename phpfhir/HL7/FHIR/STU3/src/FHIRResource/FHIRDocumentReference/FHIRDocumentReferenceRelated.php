<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRDocumentReference;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 *
* Class creation date: February 10th, 2018 *
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * A reference to a document.
 */
class FHIRDocumentReferenceRelated extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Related identifier to this DocumentReference. If both id and ref are present they shall refer to the same thing.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier
     */
    public $identifier = null;

    /**
     * Related Resource to this DocumentReference. If both id and ref are present they shall refer to the same thing.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $ref = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'DocumentReference.Related';

    /**
     * Related identifier to this DocumentReference. If both id and ref are present they shall refer to the same thing.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Related identifier to this DocumentReference. If both id and ref are present they shall refer to the same thing.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * Related Resource to this DocumentReference. If both id and ref are present they shall refer to the same thing.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * Related Resource to this DocumentReference. If both id and ref are present they shall refer to the same thing.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $ref
     * @return $this
     */
    public function setRef($ref)
    {
        $this->ref = $ref;
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
            if (isset($data['identifier'])) {
                $this->setIdentifier($data['identifier']);
            }
            if (isset($data['ref'])) {
                $this->setRef($data['ref']);
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
        if (isset($this->identifier)) {
            $json['identifier'] = $this->identifier;
        }
        if (isset($this->ref)) {
            $json['ref'] = $this->ref;
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
            $sxe = new \SimpleXMLElement('<DocumentReferenceRelated xmlns="http://hl7.org/fhir"></DocumentReferenceRelated>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->identifier)) {
            $this->identifier->xmlSerialize(true, $sxe->addChild('identifier'));
        }
        if (isset($this->ref)) {
            $this->ref->xmlSerialize(true, $sxe->addChild('ref'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
