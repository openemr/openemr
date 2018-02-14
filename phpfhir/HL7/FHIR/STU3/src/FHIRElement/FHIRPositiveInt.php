<?php namespace HL7\FHIR\STU3\FHIRElement;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement;

/**
 * An integer with a value that is positive (e.g. >0)
 * If the element is present, it must have either a @value, an @id referenced from the Narrative, or extensions
 */
class FHIRPositiveInt extends FHIRElement implements \JsonSerializable
{
    /**
     * @var string
     */
    public $value = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'positiveInt';

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;
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
        if (is_scalar($data)) {
            $this->setValue($data);
        } else {
            parent::__construct($data);
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getValue();
    }

    /**
     * @return mixed
     */
    public function jsonSerialize()
    {
        return $this->value;
    }

    /**
     * @param boolean $returnSXE
     * @param \SimpleXMLElement $sxe
     * @return string|\SimpleXMLElement
     */
    public function xmlSerialize($returnSXE = false, $sxe = null)
    {
        if (null === $sxe) {
            $sxe = new \SimpleXMLElement('<positiveInt xmlns="http://hl7.org/fhir"></positiveInt>');
        }
        $sxe->addAttribute('value', $this->value);
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
