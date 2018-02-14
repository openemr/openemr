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
class FHIRExpansionProfileExclude extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * A data group for each designation to be excluded.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRExpansionProfile\FHIRExpansionProfileDesignation2[]
     */
    public $designation = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'ExpansionProfile.Exclude';

    /**
     * A data group for each designation to be excluded.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRExpansionProfile\FHIRExpansionProfileDesignation2[]
     */
    public function getDesignation()
    {
        return $this->designation;
    }

    /**
     * A data group for each designation to be excluded.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRExpansionProfile\FHIRExpansionProfileDesignation2 $designation
     * @return $this
     */
    public function addDesignation($designation)
    {
        $this->designation[] = $designation;
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
            if (isset($data['designation'])) {
                if (is_array($data['designation'])) {
                    foreach ($data['designation'] as $d) {
                        $this->addDesignation($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"designation" must be array of objects or null, '.gettype($data['designation']).' seen.');
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
        if (0 < count($this->designation)) {
            $json['designation'] = [];
            foreach ($this->designation as $designation) {
                $json['designation'][] = $designation;
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
            $sxe = new \SimpleXMLElement('<ExpansionProfileExclude xmlns="http://hl7.org/fhir"></ExpansionProfileExclude>');
        }
        parent::xmlSerialize(true, $sxe);
        if (0 < count($this->designation)) {
            foreach ($this->designation as $designation) {
                $designation->xmlSerialize(true, $sxe->addChild('designation'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
