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
class FHIRCapabilityStatementSecurity extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Server adds CORS headers when responding to requests - this enables javascript applications to use the server.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public $cors = null;

    /**
     * Types of security services that are supported/required by the system.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept[]
     */
    public $service = [];

    /**
     * General description of how security works.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $description = null;

    /**
     * Certificates associated with security profiles.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementCertificate[]
     */
    public $certificate = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'CapabilityStatement.Security';

    /**
     * Server adds CORS headers when responding to requests - this enables javascript applications to use the server.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public function getCors()
    {
        return $this->cors;
    }

    /**
     * Server adds CORS headers when responding to requests - this enables javascript applications to use the server.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRBoolean $cors
     * @return $this
     */
    public function setCors($cors)
    {
        $this->cors = $cors;
        return $this;
    }

    /**
     * Types of security services that are supported/required by the system.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept[]
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * Types of security services that are supported/required by the system.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $service
     * @return $this
     */
    public function addService($service)
    {
        $this->service[] = $service;
        return $this;
    }

    /**
     * General description of how security works.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * General description of how security works.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Certificates associated with security profiles.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementCertificate[]
     */
    public function getCertificate()
    {
        return $this->certificate;
    }

    /**
     * Certificates associated with security profiles.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementCertificate $certificate
     * @return $this
     */
    public function addCertificate($certificate)
    {
        $this->certificate[] = $certificate;
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
            if (isset($data['cors'])) {
                $this->setCors($data['cors']);
            }
            if (isset($data['service'])) {
                if (is_array($data['service'])) {
                    foreach ($data['service'] as $d) {
                        $this->addService($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"service" must be array of objects or null, '.gettype($data['service']).' seen.');
                }
            }
            if (isset($data['description'])) {
                $this->setDescription($data['description']);
            }
            if (isset($data['certificate'])) {
                if (is_array($data['certificate'])) {
                    foreach ($data['certificate'] as $d) {
                        $this->addCertificate($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"certificate" must be array of objects or null, '.gettype($data['certificate']).' seen.');
                }
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
        if (isset($this->cors)) {
            $json['cors'] = $this->cors;
        }
        if (0 < count($this->service)) {
            $json['service'] = [];
            foreach ($this->service as $service) {
                $json['service'][] = $service;
            }
        }
        if (isset($this->description)) {
            $json['description'] = $this->description;
        }
        if (0 < count($this->certificate)) {
            $json['certificate'] = [];
            foreach ($this->certificate as $certificate) {
                $json['certificate'][] = $certificate;
            }
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
            $sxe = new \SimpleXMLElement('<CapabilityStatementSecurity xmlns="http://hl7.org/fhir"></CapabilityStatementSecurity>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->cors)) {
            $this->cors->xmlSerialize(true, $sxe->addChild('cors'));
        }
        if (0 < count($this->service)) {
            foreach ($this->service as $service) {
                $service->xmlSerialize(true, $sxe->addChild('service'));
            }
        }
        if (isset($this->description)) {
            $this->description->xmlSerialize(true, $sxe->addChild('description'));
        }
        if (0 < count($this->certificate)) {
            foreach ($this->certificate as $certificate) {
                $certificate->xmlSerialize(true, $sxe->addChild('certificate'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
