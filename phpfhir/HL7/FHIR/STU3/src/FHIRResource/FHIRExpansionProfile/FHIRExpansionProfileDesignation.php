<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRExpansionProfile;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * Resource to define constraints on the Expansion of a FHIR ValueSet.
 */
class FHIRExpansionProfileDesignation extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Designations to be included.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRExpansionProfile\FHIRExpansionProfileInclude
     */
    public $include = null;

    /**
     * Designations to be excluded.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRExpansionProfile\FHIRExpansionProfileExclude
     */
    public $exclude = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'ExpansionProfile.Designation';

    /**
     * Designations to be included.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRExpansionProfile\FHIRExpansionProfileInclude
     */
    public function getInclude()
    {
        return $this->include;
    }

    /**
     * Designations to be included.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRExpansionProfile\FHIRExpansionProfileInclude $include
     * @return $this
     */
    public function setInclude($include)
    {
        $this->include = $include;
        return $this;
    }

    /**
     * Designations to be excluded.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRExpansionProfile\FHIRExpansionProfileExclude
     */
    public function getExclude()
    {
        return $this->exclude;
    }

    /**
     * Designations to be excluded.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRExpansionProfile\FHIRExpansionProfileExclude $exclude
     * @return $this
     */
    public function setExclude($exclude)
    {
        $this->exclude = $exclude;
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
            if (isset($data['include'])) {
                $this->setInclude($data['include']);
            }
            if (isset($data['exclude'])) {
                $this->setExclude($data['exclude']);
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
        if (isset($this->include)) {
            $json['include'] = $this->include;
        }
        if (isset($this->exclude)) {
            $json['exclude'] = $this->exclude;
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
            $sxe = new \SimpleXMLElement('<ExpansionProfileDesignation xmlns="http://hl7.org/fhir"></ExpansionProfileDesignation>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->include)) {
            $this->include->xmlSerialize(true, $sxe->addChild('include'));
        }
        if (isset($this->exclude)) {
            $this->exclude->xmlSerialize(true, $sxe->addChild('exclude'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
