<?php namespace HL7\FHIR\STU3\FHIRElement;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement;

/**
 * A set of ordered Quantities defined by a low and high limit.
 * If the element is present, it must have a value for at least one of the defined elements, an @id referenced from the Narrative, or extensions
 */
class FHIRRange extends FHIRElement implements \JsonSerializable
{
    /**
     * The low limit. The boundary is inclusive.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRQuantity
     */
    public $low = null;

    /**
     * The high limit. The boundary is inclusive.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRQuantity
     */
    public $high = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Range';

    /**
     * The low limit. The boundary is inclusive.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRQuantity
     */
    public function getLow()
    {
        return $this->low;
    }

    /**
     * The low limit. The boundary is inclusive.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRQuantity $low
     * @return $this
     */
    public function setLow($low)
    {
        $this->low = $low;
        return $this;
    }

    /**
     * The high limit. The boundary is inclusive.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRQuantity
     */
    public function getHigh()
    {
        return $this->high;
    }

    /**
     * The high limit. The boundary is inclusive.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRQuantity $high
     * @return $this
     */
    public function setHigh($high)
    {
        $this->high = $high;
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
            if (isset($data['low'])) {
                $this->setLow($data['low']);
            }
            if (isset($data['high'])) {
                $this->setHigh($data['high']);
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
        if (isset($this->low)) {
            $json['low'] = $this->low;
        }
        if (isset($this->high)) {
            $json['high'] = $this->high;
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
            $sxe = new \SimpleXMLElement('<Range xmlns="http://hl7.org/fhir"></Range>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->low)) {
            $this->low->xmlSerialize(true, $sxe->addChild('low'));
        }
        if (isset($this->high)) {
            $this->high->xmlSerialize(true, $sxe->addChild('high'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
