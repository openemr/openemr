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
class FHIRExpansionProfileDesignation2 extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The language this designation is defined for.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCode
     */
    public $language = null;

    /**
     * Which kinds of designation to exclude from the expansion.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCoding
     */
    public $use = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'ExpansionProfile.Designation2';

    /**
     * The language this designation is defined for.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCode
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * The language this designation is defined for.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCode $language
     * @return $this
     */
    public function setLanguage($language)
    {
        $this->language = $language;
        return $this;
    }

    /**
     * Which kinds of designation to exclude from the expansion.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCoding
     */
    public function getUse()
    {
        return $this->use;
    }

    /**
     * Which kinds of designation to exclude from the expansion.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCoding $use
     * @return $this
     */
    public function setUse($use)
    {
        $this->use = $use;
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
            if (isset($data['language'])) {
                $this->setLanguage($data['language']);
            }
            if (isset($data['use'])) {
                $this->setUse($data['use']);
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
        if (isset($this->language)) {
            $json['language'] = $this->language;
        }
        if (isset($this->use)) {
            $json['use'] = $this->use;
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
            $sxe = new \SimpleXMLElement('<ExpansionProfileDesignation2 xmlns="http://hl7.org/fhir"></ExpansionProfileDesignation2>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->language)) {
            $this->language->xmlSerialize(true, $sxe->addChild('language'));
        }
        if (isset($this->use)) {
            $this->use->xmlSerialize(true, $sxe->addChild('use'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
