<?php namespace HL7\FHIR\STU3\FHIRElement;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement;

/**
 * A measured amount (or an amount that can potentially be measured). Note that measured amounts include amounts that are not precisely quantified, including amounts involving arbitrary units and floating currencies.
 * If the element is present, it must have a value for at least one of the defined elements, an @id referenced from the Narrative, or extensions
 */
class FHIRQuantity extends FHIRElement implements \JsonSerializable
{
    /**
     * The value of the measured amount. The value includes an implicit precision in the presentation of the value.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public $value = null;

    /**
     * How the value should be understood and represented - whether the actual value is greater or less than the stated value due to measurement issues; e.g. if the comparator is "<" , then the real value is < stated value.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRQuantityComparator
     */
    public $comparator = null;

    /**
     * A human-readable form of the unit.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $unit = null;

    /**
     * The identification of the system that provides the coded form of the unit.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public $system = null;

    /**
     * A computer processable form of the unit in some unit representation system.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCode
     */
    public $code = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Quantity';

    /**
     * The value of the measured amount. The value includes an implicit precision in the presentation of the value.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * The value of the measured amount. The value includes an implicit precision in the presentation of the value.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDecimal $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * How the value should be understood and represented - whether the actual value is greater or less than the stated value due to measurement issues; e.g. if the comparator is "<" , then the real value is < stated value.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRQuantityComparator
     */
    public function getComparator()
    {
        return $this->comparator;
    }

    /**
     * How the value should be understood and represented - whether the actual value is greater or less than the stated value due to measurement issues; e.g. if the comparator is "<" , then the real value is < stated value.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRQuantityComparator $comparator
     * @return $this
     */
    public function setComparator($comparator)
    {
        $this->comparator = $comparator;
        return $this;
    }

    /**
     * A human-readable form of the unit.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * A human-readable form of the unit.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $unit
     * @return $this
     */
    public function setUnit($unit)
    {
        $this->unit = $unit;
        return $this;
    }

    /**
     * The identification of the system that provides the coded form of the unit.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public function getSystem()
    {
        return $this->system;
    }

    /**
     * The identification of the system that provides the coded form of the unit.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRUri $system
     * @return $this
     */
    public function setSystem($system)
    {
        $this->system = $system;
        return $this;
    }

    /**
     * A computer processable form of the unit in some unit representation system.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCode
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * A computer processable form of the unit in some unit representation system.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCode $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;
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
            if (isset($data['value'])) {
                $this->setValue($data['value']);
            }
            if (isset($data['comparator'])) {
                $this->setComparator($data['comparator']);
            }
            if (isset($data['unit'])) {
                $this->setUnit($data['unit']);
            }
            if (isset($data['system'])) {
                $this->setSystem($data['system']);
            }
            if (isset($data['code'])) {
                $this->setCode($data['code']);
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
        return (string)$this->getValue();
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $json = parent::jsonSerialize();
        if (isset($this->value)) {
            $json['value'] = $this->value;
        }
        if (isset($this->comparator)) {
            $json['comparator'] = $this->comparator;
        }
        if (isset($this->unit)) {
            $json['unit'] = $this->unit;
        }
        if (isset($this->system)) {
            $json['system'] = $this->system;
        }
        if (isset($this->code)) {
            $json['code'] = $this->code;
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
            $sxe = new \SimpleXMLElement('<Quantity xmlns="http://hl7.org/fhir"></Quantity>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->value)) {
            $this->value->xmlSerialize(true, $sxe->addChild('value'));
        }
        if (isset($this->comparator)) {
            $this->comparator->xmlSerialize(true, $sxe->addChild('comparator'));
        }
        if (isset($this->unit)) {
            $this->unit->xmlSerialize(true, $sxe->addChild('unit'));
        }
        if (isset($this->system)) {
            $this->system->xmlSerialize(true, $sxe->addChild('system'));
        }
        if (isset($this->code)) {
            $this->code->xmlSerialize(true, $sxe->addChild('code'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
