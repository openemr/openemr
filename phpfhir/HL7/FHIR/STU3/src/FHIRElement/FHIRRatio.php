<?php namespace HL7\FHIR\STU3\FHIRElement;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement;

/**
 * A relationship of two Quantity values - expressed as a numerator and a denominator.
 * If the element is present, it must have a value for at least one of the defined elements, an @id referenced from the Narrative, or extensions
 */
class FHIRRatio extends FHIRElement implements \JsonSerializable
{
    /**
     * The value of the numerator.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRQuantity
     */
    public $numerator = null;

    /**
     * The value of the denominator.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRQuantity
     */
    public $denominator = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Ratio';

    /**
     * The value of the numerator.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRQuantity
     */
    public function getNumerator()
    {
        return $this->numerator;
    }

    /**
     * The value of the numerator.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRQuantity $numerator
     * @return $this
     */
    public function setNumerator($numerator)
    {
        $this->numerator = $numerator;
        return $this;
    }

    /**
     * The value of the denominator.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRQuantity
     */
    public function getDenominator()
    {
        return $this->denominator;
    }

    /**
     * The value of the denominator.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRQuantity $denominator
     * @return $this
     */
    public function setDenominator($denominator)
    {
        $this->denominator = $denominator;
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
            if (isset($data['numerator'])) {
                $this->setNumerator($data['numerator']);
            }
            if (isset($data['denominator'])) {
                $this->setDenominator($data['denominator']);
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
        if (isset($this->numerator)) {
            $json['numerator'] = $this->numerator;
        }
        if (isset($this->denominator)) {
            $json['denominator'] = $this->denominator;
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
            $sxe = new \SimpleXMLElement('<Ratio xmlns="http://hl7.org/fhir"></Ratio>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->numerator)) {
            $this->numerator->xmlSerialize(true, $sxe->addChild('numerator'));
        }
        if (isset($this->denominator)) {
            $this->denominator->xmlSerialize(true, $sxe->addChild('denominator'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
