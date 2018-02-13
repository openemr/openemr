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
class FHIRTestScriptCapability extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Whether or not the test execution will require the given capabilities of the server in order for this test script to execute.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public $required = null;

    /**
     * Whether or not the test execution will validate the given capabilities of the server in order for this test script to execute.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public $validated = null;

    /**
     * Description of the capabilities that this test script is requiring the server to support.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $description = null;

    /**
     * Which origin server these requirements apply to.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRInteger[]
     */
    public $origin = [];

    /**
     * Which server these requirements apply to.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRInteger
     */
    public $destination = null;

    /**
     * Links to the FHIR specification that describes this interaction and the resources involved in more detail.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRUri[]
     */
    public $link = [];

    /**
     * Minimum capabilities required of server for test script to execute successfully.   If server does not meet at a minimum the referenced capability statement, then all tests in this script are skipped.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $capabilities = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'TestScript.Capability';

    /**
     * Whether or not the test execution will require the given capabilities of the server in order for this test script to execute.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public function getRequired()
    {
        return $this->required;
    }

    /**
     * Whether or not the test execution will require the given capabilities of the server in order for this test script to execute.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRBoolean $required
     * @return $this
     */
    public function setRequired($required)
    {
        $this->required = $required;
        return $this;
    }

    /**
     * Whether or not the test execution will validate the given capabilities of the server in order for this test script to execute.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public function getValidated()
    {
        return $this->validated;
    }

    /**
     * Whether or not the test execution will validate the given capabilities of the server in order for this test script to execute.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRBoolean $validated
     * @return $this
     */
    public function setValidated($validated)
    {
        $this->validated = $validated;
        return $this;
    }

    /**
     * Description of the capabilities that this test script is requiring the server to support.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Description of the capabilities that this test script is requiring the server to support.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Which origin server these requirements apply to.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRInteger[]
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * Which origin server these requirements apply to.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRInteger $origin
     * @return $this
     */
    public function addOrigin($origin)
    {
        $this->origin[] = $origin;
        return $this;
    }

    /**
     * Which server these requirements apply to.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRInteger
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * Which server these requirements apply to.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRInteger $destination
     * @return $this
     */
    public function setDestination($destination)
    {
        $this->destination = $destination;
        return $this;
    }

    /**
     * Links to the FHIR specification that describes this interaction and the resources involved in more detail.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRUri[]
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Links to the FHIR specification that describes this interaction and the resources involved in more detail.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRUri $link
     * @return $this
     */
    public function addLink($link)
    {
        $this->link[] = $link;
        return $this;
    }

    /**
     * Minimum capabilities required of server for test script to execute successfully.   If server does not meet at a minimum the referenced capability statement, then all tests in this script are skipped.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getCapabilities()
    {
        return $this->capabilities;
    }

    /**
     * Minimum capabilities required of server for test script to execute successfully.   If server does not meet at a minimum the referenced capability statement, then all tests in this script are skipped.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $capabilities
     * @return $this
     */
    public function setCapabilities($capabilities)
    {
        $this->capabilities = $capabilities;
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
            if (isset($data['required'])) {
                $this->setRequired($data['required']);
            }
            if (isset($data['validated'])) {
                $this->setValidated($data['validated']);
            }
            if (isset($data['description'])) {
                $this->setDescription($data['description']);
            }
            if (isset($data['origin'])) {
                if (is_array($data['origin'])) {
                    foreach ($data['origin'] as $d) {
                        $this->addOrigin($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"origin" must be array of objects or null, '.gettype($data['origin']).' seen.');
                }
            }
            if (isset($data['destination'])) {
                $this->setDestination($data['destination']);
            }
            if (isset($data['link'])) {
                if (is_array($data['link'])) {
                    foreach ($data['link'] as $d) {
                        $this->addLink($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"link" must be array of objects or null, '.gettype($data['link']).' seen.');
                }
            }
            if (isset($data['capabilities'])) {
                $this->setCapabilities($data['capabilities']);
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
        if (isset($this->required)) {
            $json['required'] = $this->required;
        }
        if (isset($this->validated)) {
            $json['validated'] = $this->validated;
        }
        if (isset($this->description)) {
            $json['description'] = $this->description;
        }
        if (0 < count($this->origin)) {
            $json['origin'] = [];
            foreach ($this->origin as $origin) {
                $json['origin'][] = $origin;
            }
        }
        if (isset($this->destination)) {
            $json['destination'] = $this->destination;
        }
        if (0 < count($this->link)) {
            $json['link'] = [];
            foreach ($this->link as $link) {
                $json['link'][] = $link;
            }
        }
        if (isset($this->capabilities)) {
            $json['capabilities'] = $this->capabilities;
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
            $sxe = new \SimpleXMLElement('<TestScriptCapability xmlns="http://hl7.org/fhir"></TestScriptCapability>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->required)) {
            $this->required->xmlSerialize(true, $sxe->addChild('required'));
        }
        if (isset($this->validated)) {
            $this->validated->xmlSerialize(true, $sxe->addChild('validated'));
        }
        if (isset($this->description)) {
            $this->description->xmlSerialize(true, $sxe->addChild('description'));
        }
        if (0 < count($this->origin)) {
            foreach ($this->origin as $origin) {
                $origin->xmlSerialize(true, $sxe->addChild('origin'));
            }
        }
        if (isset($this->destination)) {
            $this->destination->xmlSerialize(true, $sxe->addChild('destination'));
        }
        if (0 < count($this->link)) {
            foreach ($this->link as $link) {
                $link->xmlSerialize(true, $sxe->addChild('link'));
            }
        }
        if (isset($this->capabilities)) {
            $this->capabilities->xmlSerialize(true, $sxe->addChild('capabilities'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
