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
class FHIRExpansionProfileFixedVersion extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The specific system for which to fix the version.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public $system = null;

    /**
     * The version of the code system from which codes in the expansion should be included.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $version = null;

    /**
     * How to manage the intersection between a fixed version in a value set, and this fixed version of the system in the expansion profile.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRSystemVersionProcessingMode
     */
    public $mode = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'ExpansionProfile.FixedVersion';

    /**
     * The specific system for which to fix the version.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public function getSystem()
    {
        return $this->system;
    }

    /**
     * The specific system for which to fix the version.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRUri $system
     * @return $this
     */
    public function setSystem($system)
    {
        $this->system = $system;
        return $this;
    }

    /**
     * The version of the code system from which codes in the expansion should be included.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * The version of the code system from which codes in the expansion should be included.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $version
     * @return $this
     */
    public function setVersion($version)
    {
        $this->version = $version;
        return $this;
    }

    /**
     * How to manage the intersection between a fixed version in a value set, and this fixed version of the system in the expansion profile.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRSystemVersionProcessingMode
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * How to manage the intersection between a fixed version in a value set, and this fixed version of the system in the expansion profile.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRSystemVersionProcessingMode $mode
     * @return $this
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
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
            if (isset($data['system'])) {
                $this->setSystem($data['system']);
            }
            if (isset($data['version'])) {
                $this->setVersion($data['version']);
            }
            if (isset($data['mode'])) {
                $this->setMode($data['mode']);
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
        if (isset($this->system)) {
            $json['system'] = $this->system;
        }
        if (isset($this->version)) {
            $json['version'] = $this->version;
        }
        if (isset($this->mode)) {
            $json['mode'] = $this->mode;
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
            $sxe = new \SimpleXMLElement('<ExpansionProfileFixedVersion xmlns="http://hl7.org/fhir"></ExpansionProfileFixedVersion>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->system)) {
            $this->system->xmlSerialize(true, $sxe->addChild('system'));
        }
        if (isset($this->version)) {
            $this->version->xmlSerialize(true, $sxe->addChild('version'));
        }
        if (isset($this->mode)) {
            $this->mode->xmlSerialize(true, $sxe->addChild('mode'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
