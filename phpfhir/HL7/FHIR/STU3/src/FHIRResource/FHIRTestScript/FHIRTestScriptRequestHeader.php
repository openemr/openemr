<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRTestScript;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * A structured set of tests against a FHIR server implementation to determine compliance against the FHIR specification.
 */
class FHIRTestScriptRequestHeader extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The HTTP header field e.g. "Accept".
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $field = null;

    /**
     * The value of the header e.g. "application/fhir+xml".
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $value = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'TestScript.RequestHeader';

    /**
     * The HTTP header field e.g. "Accept".
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * The HTTP header field e.g. "Accept".
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $field
     * @return $this
     */
    public function setField($field)
    {
        $this->field = $field;
        return $this;
    }

    /**
     * The value of the header e.g. "application/fhir+xml".
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * The value of the header e.g. "application/fhir+xml".
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
            if (isset($data['field'])) {
                $this->setField($data['field']);
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
        if (isset($this->field)) {
            $json['field'] = $this->field;
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
            $sxe = new \SimpleXMLElement('<TestScriptRequestHeader xmlns="http://hl7.org/fhir"></TestScriptRequestHeader>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->field)) {
            $this->field->xmlSerialize(true, $sxe->addChild('field'));
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
