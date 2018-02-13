<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRMessageHeader;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 *
* Class creation date: February 10th, 2018 *
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * The header for a message exchange that is either requesting or responding to an action.  The reference(s) that are the subject of the action as well as other information related to the action are typically transmitted in a bundle in which the MessageHeader resource instance is the first resource in the bundle.
 */
class FHIRMessageHeaderSource extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Human-readable name for the source system.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $name = null;

    /**
     * May include configuration or other information useful in debugging.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $software = null;

    /**
     * Can convey versions of multiple systems in situations where a message passes through multiple hands.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $version = null;

    /**
     * An e-mail, phone, website or other contact point to use to resolve issues with message communications.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRContactPoint
     */
    public $contact = null;

    /**
     * Identifies the routing target to send acknowledgements to.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public $endpoint = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'MessageHeader.Source';

    /**
     * Human-readable name for the source system.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Human-readable name for the source system.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * May include configuration or other information useful in debugging.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getSoftware()
    {
        return $this->software;
    }

    /**
     * May include configuration or other information useful in debugging.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $software
     * @return $this
     */
    public function setSoftware($software)
    {
        $this->software = $software;
        return $this;
    }

    /**
     * Can convey versions of multiple systems in situations where a message passes through multiple hands.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Can convey versions of multiple systems in situations where a message passes through multiple hands.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $version
     * @return $this
     */
    public function setVersion($version)
    {
        $this->version = $version;
        return $this;
    }

    /**
     * An e-mail, phone, website or other contact point to use to resolve issues with message communications.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRContactPoint
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * An e-mail, phone, website or other contact point to use to resolve issues with message communications.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRContactPoint $contact
     * @return $this
     */
    public function setContact($contact)
    {
        $this->contact = $contact;
        return $this;
    }

    /**
     * Identifies the routing target to send acknowledgements to.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * Identifies the routing target to send acknowledgements to.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRUri $endpoint
     * @return $this
     */
    public function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;
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
            if (isset($data['name'])) {
                $this->setName($data['name']);
            }
            if (isset($data['software'])) {
                $this->setSoftware($data['software']);
            }
            if (isset($data['version'])) {
                $this->setVersion($data['version']);
            }
            if (isset($data['contact'])) {
                $this->setContact($data['contact']);
            }
            if (isset($data['endpoint'])) {
                $this->setEndpoint($data['endpoint']);
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
        if (isset($this->name)) {
            $json['name'] = $this->name;
        }
        if (isset($this->software)) {
            $json['software'] = $this->software;
        }
        if (isset($this->version)) {
            $json['version'] = $this->version;
        }
        if (isset($this->contact)) {
            $json['contact'] = $this->contact;
        }
        if (isset($this->endpoint)) {
            $json['endpoint'] = $this->endpoint;
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
            $sxe = new \SimpleXMLElement('<MessageHeaderSource xmlns="http://hl7.org/fhir"></MessageHeaderSource>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->name)) {
            $this->name->xmlSerialize(true, $sxe->addChild('name'));
        }
        if (isset($this->software)) {
            $this->software->xmlSerialize(true, $sxe->addChild('software'));
        }
        if (isset($this->version)) {
            $this->version->xmlSerialize(true, $sxe->addChild('version'));
        }
        if (isset($this->contact)) {
            $this->contact->xmlSerialize(true, $sxe->addChild('contact'));
        }
        if (isset($this->endpoint)) {
            $this->endpoint->xmlSerialize(true, $sxe->addChild('endpoint'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
