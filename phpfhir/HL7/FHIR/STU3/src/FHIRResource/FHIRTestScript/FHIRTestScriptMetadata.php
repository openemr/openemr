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
class FHIRTestScriptMetadata extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * A link to the FHIR specification that this test is covering.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRTestScript\FHIRTestScriptLink[]
     */
    public $link = [];

    /**
     * Capabilities that must exist and are assumed to function correctly on the FHIR server being tested.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRTestScript\FHIRTestScriptCapability[]
     */
    public $capability = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'TestScript.Metadata';

    /**
     * A link to the FHIR specification that this test is covering.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRTestScript\FHIRTestScriptLink[]
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * A link to the FHIR specification that this test is covering.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRTestScript\FHIRTestScriptLink $link
     * @return $this
     */
    public function addLink($link)
    {
        $this->link[] = $link;
        return $this;
    }

    /**
     * Capabilities that must exist and are assumed to function correctly on the FHIR server being tested.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRTestScript\FHIRTestScriptCapability[]
     */
    public function getCapability()
    {
        return $this->capability;
    }

    /**
     * Capabilities that must exist and are assumed to function correctly on the FHIR server being tested.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRTestScript\FHIRTestScriptCapability $capability
     * @return $this
     */
    public function addCapability($capability)
    {
        $this->capability[] = $capability;
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
            if (isset($data['link'])) {
                if (is_array($data['link'])) {
                    foreach ($data['link'] as $d) {
                        $this->addLink($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"link" must be array of objects or null, '.gettype($data['link']).' seen.');
                }
            }
            if (isset($data['capability'])) {
                if (is_array($data['capability'])) {
                    foreach ($data['capability'] as $d) {
                        $this->addCapability($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"capability" must be array of objects or null, '.gettype($data['capability']).' seen.');
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
        if (0 < count($this->link)) {
            $json['link'] = [];
            foreach ($this->link as $link) {
                $json['link'][] = $link;
            }
        }
        if (0 < count($this->capability)) {
            $json['capability'] = [];
            foreach ($this->capability as $capability) {
                $json['capability'][] = $capability;
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
            $sxe = new \SimpleXMLElement('<TestScriptMetadata xmlns="http://hl7.org/fhir"></TestScriptMetadata>');
        }
        parent::xmlSerialize(true, $sxe);
        if (0 < count($this->link)) {
            foreach ($this->link as $link) {
                $link->xmlSerialize(true, $sxe->addChild('link'));
            }
        }
        if (0 < count($this->capability)) {
            foreach ($this->capability as $capability) {
                $capability->xmlSerialize(true, $sxe->addChild('capability'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
