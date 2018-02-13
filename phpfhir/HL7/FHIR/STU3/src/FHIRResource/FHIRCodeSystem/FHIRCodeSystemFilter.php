<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRCodeSystem;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * A code system resource specifies a set of codes drawn from one or more code systems.
 */
class FHIRCodeSystemFilter extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The code that identifies this filter when it is used in the instance.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCode
     */
    public $code = null;

    /**
     * A description of how or why the filter is used.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $description = null;

    /**
     * A list of operators that can be used with the filter.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRFilterOperator[]
     */
    public $operator = [];

    /**
     * A description of what the value for the filter should be.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $value = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'CodeSystem.Filter';

    /**
     * The code that identifies this filter when it is used in the instance.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCode
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * The code that identifies this filter when it is used in the instance.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCode $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * A description of how or why the filter is used.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * A description of how or why the filter is used.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * A list of operators that can be used with the filter.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRFilterOperator[]
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * A list of operators that can be used with the filter.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRFilterOperator $operator
     * @return $this
     */
    public function addOperator($operator)
    {
        $this->operator[] = $operator;
        return $this;
    }

    /**
     * A description of what the value for the filter should be.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * A description of what the value for the filter should be.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $value
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
            if (isset($data['code'])) {
                $this->setCode($data['code']);
            }
            if (isset($data['description'])) {
                $this->setDescription($data['description']);
            }
            if (isset($data['operator'])) {
                if (is_array($data['operator'])) {
                    foreach ($data['operator'] as $d) {
                        $this->addOperator($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"operator" must be array of objects or null, '.gettype($data['operator']).' seen.');
                }
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
        if (isset($this->code)) {
            $json['code'] = $this->code;
        }
        if (isset($this->description)) {
            $json['description'] = $this->description;
        }
        if (0 < count($this->operator)) {
            $json['operator'] = [];
            foreach ($this->operator as $operator) {
                $json['operator'][] = $operator;
            }
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
            $sxe = new \SimpleXMLElement('<CodeSystemFilter xmlns="http://hl7.org/fhir"></CodeSystemFilter>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->code)) {
            $this->code->xmlSerialize(true, $sxe->addChild('code'));
        }
        if (isset($this->description)) {
            $this->description->xmlSerialize(true, $sxe->addChild('description'));
        }
        if (0 < count($this->operator)) {
            foreach ($this->operator as $operator) {
                $operator->xmlSerialize(true, $sxe->addChild('operator'));
            }
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
