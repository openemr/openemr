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
class FHIRSubstanceIngredient extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The amount of the ingredient in the substance - a concentration ratio.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRRatio
     */
    public $quantity = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $substanceCodeableConcept = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $substanceReference = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Substance.Ingredient';

    /**
     * The amount of the ingredient in the substance - a concentration ratio.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRRatio
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * The amount of the ingredient in the substance - a concentration ratio.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRRatio $quantity
     * @return $this
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getSubstanceCodeableConcept()
    {
        return $this->substanceCodeableConcept;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $substanceCodeableConcept
     * @return $this
     */
    public function setSubstanceCodeableConcept($substanceCodeableConcept)
    {
        $this->substanceCodeableConcept = $substanceCodeableConcept;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getSubstanceReference()
    {
        return $this->substanceReference;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $substanceReference
     * @return $this
     */
    public function setSubstanceReference($substanceReference)
    {
        $this->substanceReference = $substanceReference;
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
            if (isset($data['quantity'])) {
                $this->setQuantity($data['quantity']);
            }
            if (isset($data['substanceCodeableConcept'])) {
                $this->setSubstanceCodeableConcept($data['substanceCodeableConcept']);
            }
            if (isset($data['substanceReference'])) {
                $this->setSubstanceReference($data['substanceReference']);
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
        if (isset($this->quantity)) {
            $json['quantity'] = $this->quantity;
        }
        if (isset($this->substanceCodeableConcept)) {
            $json['substanceCodeableConcept'] = $this->substanceCodeableConcept;
        }
        if (isset($this->substanceReference)) {
            $json['substanceReference'] = $this->substanceReference;
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
            $sxe = new \SimpleXMLElement('<SubstanceIngredient xmlns="http://hl7.org/fhir"></SubstanceIngredient>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->quantity)) {
            $this->quantity->xmlSerialize(true, $sxe->addChild('quantity'));
        }
        if (isset($this->substanceCodeableConcept)) {
            $this->substanceCodeableConcept->xmlSerialize(true, $sxe->addChild('substanceCodeableConcept'));
        }
        if (isset($this->substanceReference)) {
            $this->substanceReference->xmlSerialize(true, $sxe->addChild('substanceReference'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
