<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRValueSet;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * A value set specifies a set of codes drawn from one or more code systems.
 */
class FHIRValueSetFilter extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * A code that identifies a property defined in the code system.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCode
     */
    public $property = null;

    /**
     * The kind of operation to perform as a part of the filter criteria.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRFilterOperator
     */
    public $op = null;

    /**
     * The match value may be either a code defined by the system, or a string value, which is a regex match on the literal string of the property value when the operation is 'regex', or one of the values (true and false), when the operation is 'exists'.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCode
     */
    public $value = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'ValueSet.Filter';

    /**
     * A code that identifies a property defined in the code system.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCode
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * A code that identifies a property defined in the code system.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCode $property
     * @return $this
     */
    public function setProperty($property)
    {
        $this->property = $property;
        return $this;
    }

    /**
     * The kind of operation to perform as a part of the filter criteria.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRFilterOperator
     */
    public function getOp()
    {
        return $this->op;
    }

    /**
     * The kind of operation to perform as a part of the filter criteria.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRFilterOperator $op
     * @return $this
     */
    public function setOp($op)
    {
        $this->op = $op;
        return $this;
    }

    /**
     * The match value may be either a code defined by the system, or a string value, which is a regex match on the literal string of the property value when the operation is 'regex', or one of the values (true and false), when the operation is 'exists'.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCode
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * The match value may be either a code defined by the system, or a string value, which is a regex match on the literal string of the property value when the operation is 'regex', or one of the values (true and false), when the operation is 'exists'.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCode $value
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
        if (is_array($data)) {
            if (isset($data['property'])) {
                $this->setProperty($data['property']);
            }
            if (isset($data['op'])) {
                $this->setOp($data['op']);
            }
            if (isset($data['value'])) {
                $this->setValue($data['value']);
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
        if (isset($this->property)) {
            $json['property'] = $this->property;
        }
        if (isset($this->op)) {
            $json['op'] = $this->op;
        }
        if (isset($this->value)) {
            $json['value'] = $this->value;
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
            $sxe = new \SimpleXMLElement('<ValueSetFilter xmlns="http://hl7.org/fhir"></ValueSetFilter>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->property)) {
            $this->property->xmlSerialize(true, $sxe->addChild('property'));
        }
        if (isset($this->op)) {
            $this->op->xmlSerialize(true, $sxe->addChild('op'));
        }
        if (isset($this->value)) {
            $this->value->xmlSerialize(true, $sxe->addChild('value'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
