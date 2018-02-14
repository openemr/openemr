<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRAuditEvent;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * A record of an event made for purposes of maintaining a security log. Typical uses include detection of intrusion attempts and monitoring for inappropriate usage.
 */
class FHIRAuditEventDetail extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The type of extra detail provided in the value.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $type = null;

    /**
     * The details, base64 encoded. Used to carry bulk information.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRBase64Binary
     */
    public $value = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'AuditEvent.Detail';

    /**
     * The type of extra detail provided in the value.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * The type of extra detail provided in the value.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * The details, base64 encoded. Used to carry bulk information.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRBase64Binary
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * The details, base64 encoded. Used to carry bulk information.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRBase64Binary $value
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
            if (isset($data['type'])) {
                $this->setType($data['type']);
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
        if (isset($this->type)) {
            $json['type'] = $this->type;
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
            $sxe = new \SimpleXMLElement('<AuditEventDetail xmlns="http://hl7.org/fhir"></AuditEventDetail>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
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
