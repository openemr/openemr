<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRPatient;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * Demographics and other administrative information about an individual or animal receiving care or other health-related services.
 */
class FHIRPatientCommunication extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The ISO-639-1 alpha 2 code in lower case for the language, optionally followed by a hyphen and the ISO-3166-1 alpha 2 code for the region in upper case; e.g. "en" for English, or "en-US" for American English versus "en-EN" for England English.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $language = null;

    /**
     * Indicates whether or not the patient prefers this language (over other languages he masters up a certain level).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public $preferred = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Patient.Communication';

    /**
     * The ISO-639-1 alpha 2 code in lower case for the language, optionally followed by a hyphen and the ISO-3166-1 alpha 2 code for the region in upper case; e.g. "en" for English, or "en-US" for American English versus "en-EN" for England English.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * The ISO-639-1 alpha 2 code in lower case for the language, optionally followed by a hyphen and the ISO-3166-1 alpha 2 code for the region in upper case; e.g. "en" for English, or "en-US" for American English versus "en-EN" for England English.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $language
     * @return $this
     */
    public function setLanguage($language)
    {
        $this->language = $language;
        return $this;
    }

    /**
     * Indicates whether or not the patient prefers this language (over other languages he masters up a certain level).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public function getPreferred()
    {
        return $this->preferred;
    }

    /**
     * Indicates whether or not the patient prefers this language (over other languages he masters up a certain level).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRBoolean $preferred
     * @return $this
     */
    public function setPreferred($preferred)
    {
        $this->preferred = $preferred;
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
            if (isset($data['preferred'])) {
                $this->setPreferred($data['preferred']);
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
        if (isset($this->preferred)) {
            $json['preferred'] = $this->preferred;
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
            $sxe = new \SimpleXMLElement('<PatientCommunication xmlns="http://hl7.org/fhir"></PatientCommunication>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->language)) {
            $this->language->xmlSerialize(true, $sxe->addChild('language'));
        }
        if (isset($this->preferred)) {
            $this->preferred->xmlSerialize(true, $sxe->addChild('preferred'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
