<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRHealthcareService;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * The details of a healthcare service available at a location.
 */
class FHIRHealthcareServiceNotAvailable extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The reason that can be presented to the user as to why this time is not available.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $description = null;

    /**
     * Service is not available (seasonally or for a public holiday) from this date.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRPeriod
     */
    public $during = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'HealthcareService.NotAvailable';

    /**
     * The reason that can be presented to the user as to why this time is not available.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * The reason that can be presented to the user as to why this time is not available.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Service is not available (seasonally or for a public holiday) from this date.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRPeriod
     */
    public function getDuring()
    {
        return $this->during;
    }

    /**
     * Service is not available (seasonally or for a public holiday) from this date.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRPeriod $during
     * @return $this
     */
    public function setDuring($during)
    {
        $this->during = $during;
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
            if (isset($data['description'])) {
                $this->setDescription($data['description']);
            }
            if (isset($data['during'])) {
                $this->setDuring($data['during']);
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
        if (isset($this->description)) {
            $json['description'] = $this->description;
        }
        if (isset($this->during)) {
            $json['during'] = $this->during;
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
            $sxe = new \SimpleXMLElement('<HealthcareServiceNotAvailable xmlns="http://hl7.org/fhir"></HealthcareServiceNotAvailable>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->description)) {
            $this->description->xmlSerialize(true, $sxe->addChild('description'));
        }
        if (isset($this->during)) {
            $this->during->xmlSerialize(true, $sxe->addChild('during'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
