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
class FHIRAuditEventNetwork extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * An identifier for the network access point of the user device for the audit event.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $address = null;

    /**
     * An identifier for the type of network access point that originated the audit event.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRAuditEventAgentNetworkType
     */
    public $type = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'AuditEvent.Network';

    /**
     * An identifier for the network access point of the user device for the audit event.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * An identifier for the network access point of the user device for the audit event.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $address
     * @return $this
     */
    public function setAddress($address)
    {
        $this->address = $address;
        return $this;
    }

    /**
     * An identifier for the type of network access point that originated the audit event.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRAuditEventAgentNetworkType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * An identifier for the type of network access point that originated the audit event.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRAuditEventAgentNetworkType $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
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
            if (isset($data['address'])) {
                $this->setAddress($data['address']);
            }
            if (isset($data['type'])) {
                $this->setType($data['type']);
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
        if (isset($this->address)) {
            $json['address'] = $this->address;
        }
        if (isset($this->type)) {
            $json['type'] = $this->type;
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
            $sxe = new \SimpleXMLElement('<AuditEventNetwork xmlns="http://hl7.org/fhir"></AuditEventNetwork>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->address)) {
            $this->address->xmlSerialize(true, $sxe->addChild('address'));
        }
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
