<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRSubstance;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * A homogeneous material with a definite composition.
 */
class FHIRSubstanceInstance extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Identifier associated with the package/container (usually a label affixed directly).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier
     */
    public $identifier = null;

    /**
     * When the substance is no longer valid to use. For some substances, a single arbitrary date is used for expiry.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDateTime
     */
    public $expiry = null;

    /**
     * The amount of the substance.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRQuantity
     */
    public $quantity = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Substance.Instance';

    /**
     * Identifier associated with the package/container (usually a label affixed directly).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Identifier associated with the package/container (usually a label affixed directly).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * When the substance is no longer valid to use. For some substances, a single arbitrary date is used for expiry.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDateTime
     */
    public function getExpiry()
    {
        return $this->expiry;
    }

    /**
     * When the substance is no longer valid to use. For some substances, a single arbitrary date is used for expiry.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDateTime $expiry
     * @return $this
     */
    public function setExpiry($expiry)
    {
        $this->expiry = $expiry;
        return $this;
    }

    /**
     * The amount of the substance.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRQuantity
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * The amount of the substance.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRQuantity $quantity
     * @return $this
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
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
            if (isset($data['expiry'])) {
                $this->setExpiry($data['expiry']);
            }
            if (isset($data['quantity'])) {
                $this->setQuantity($data['quantity']);
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
        if (isset($this->expiry)) {
            $json['expiry'] = $this->expiry;
        }
        if (isset($this->quantity)) {
            $json['quantity'] = $this->quantity;
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
            $sxe = new \SimpleXMLElement('<SubstanceInstance xmlns="http://hl7.org/fhir"></SubstanceInstance>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->identifier)) {
            $this->identifier->xmlSerialize(true, $sxe->addChild('identifier'));
        }
        if (isset($this->expiry)) {
            $this->expiry->xmlSerialize(true, $sxe->addChild('expiry'));
        }
        if (isset($this->quantity)) {
            $this->quantity->xmlSerialize(true, $sxe->addChild('quantity'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
