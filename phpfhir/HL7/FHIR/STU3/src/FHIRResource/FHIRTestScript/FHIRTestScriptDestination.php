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
class FHIRTestScriptDestination extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Abstract name given to a destination server in this test script.  The name is provided as a number starting at 1.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRInteger
     */
    public $index = null;

    /**
     * The type of destination profile the test system supports.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCoding
     */
    public $profile = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'TestScript.Destination';

    /**
     * Abstract name given to a destination server in this test script.  The name is provided as a number starting at 1.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRInteger
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * Abstract name given to a destination server in this test script.  The name is provided as a number starting at 1.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRInteger $index
     * @return $this
     */
    public function setIndex($index)
    {
        $this->index = $index;
        return $this;
    }

    /**
     * The type of destination profile the test system supports.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCoding
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * The type of destination profile the test system supports.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCoding $profile
     * @return $this
     */
    public function setProfile($profile)
    {
        $this->profile = $profile;
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
            if (isset($data['index'])) {
                $this->setIndex($data['index']);
            }
            if (isset($data['profile'])) {
                $this->setProfile($data['profile']);
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
        if (isset($this->index)) {
            $json['index'] = $this->index;
        }
        if (isset($this->profile)) {
            $json['profile'] = $this->profile;
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
            $sxe = new \SimpleXMLElement('<TestScriptDestination xmlns="http://hl7.org/fhir"></TestScriptDestination>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->index)) {
            $this->index->xmlSerialize(true, $sxe->addChild('index'));
        }
        if (isset($this->profile)) {
            $this->profile->xmlSerialize(true, $sxe->addChild('profile'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
