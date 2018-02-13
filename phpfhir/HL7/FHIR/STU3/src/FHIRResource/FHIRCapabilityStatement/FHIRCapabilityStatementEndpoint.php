<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRCapabilityStatement;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * A Capability Statement documents a set of capabilities (behaviors) of a FHIR Server that may be used as a statement of actual server functionality or a statement of required or desired server implementation.
 */
class FHIRCapabilityStatementEndpoint extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * A list of the messaging transport protocol(s) identifiers, supported by this endpoint.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCoding
     */
    public $protocol = null;

    /**
     * The network address of the end-point. For solutions that do not use network addresses for routing, it can be just an identifier.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public $address = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'CapabilityStatement.Endpoint';

    /**
     * A list of the messaging transport protocol(s) identifiers, supported by this endpoint.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCoding
     */
    public function getProtocol()
    {
        return $this->protocol;
    }

    /**
     * A list of the messaging transport protocol(s) identifiers, supported by this endpoint.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCoding $protocol
     * @return $this
     */
    public function setProtocol($protocol)
    {
        $this->protocol = $protocol;
        return $this;
    }

    /**
     * The network address of the end-point. For solutions that do not use network addresses for routing, it can be just an identifier.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * The network address of the end-point. For solutions that do not use network addresses for routing, it can be just an identifier.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRUri $address
     * @return $this
     */
    public function setAddress($address)
    {
        $this->address = $address;
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
            if (isset($data['protocol'])) {
                $this->setProtocol($data['protocol']);
            }
            if (isset($data['address'])) {
                $this->setAddress($data['address']);
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
        if (isset($this->protocol)) {
            $json['protocol'] = $this->protocol;
        }
        if (isset($this->address)) {
            $json['address'] = $this->address;
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
            $sxe = new \SimpleXMLElement('<CapabilityStatementEndpoint xmlns="http://hl7.org/fhir"></CapabilityStatementEndpoint>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->protocol)) {
            $this->protocol->xmlSerialize(true, $sxe->addChild('protocol'));
        }
        if (isset($this->address)) {
            $this->address->xmlSerialize(true, $sxe->addChild('address'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
