<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRConsent;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * A record of a healthcare consumer’s policy choices, which permits or denies identified recipient(s) or recipient role(s) to perform one or more actions within a given policy context, for specific purposes and periods of time.
 */
class FHIRConsentData1 extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * How the resource reference is interpreted when testing consent restrictions.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRConsentDataMeaning
     */
    public $meaning = null;

    /**
     * A reference to a specific resource that defines which resources are covered by this consent.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $reference = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Consent.Data1';

    /**
     * How the resource reference is interpreted when testing consent restrictions.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRConsentDataMeaning
     */
    public function getMeaning()
    {
        return $this->meaning;
    }

    /**
     * How the resource reference is interpreted when testing consent restrictions.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRConsentDataMeaning $meaning
     * @return $this
     */
    public function setMeaning($meaning)
    {
        $this->meaning = $meaning;
        return $this;
    }

    /**
     * A reference to a specific resource that defines which resources are covered by this consent.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * A reference to a specific resource that defines which resources are covered by this consent.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $reference
     * @return $this
     */
    public function setReference($reference)
    {
        $this->reference = $reference;
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
            if (isset($data['meaning'])) {
                $this->setMeaning($data['meaning']);
            }
            if (isset($data['reference'])) {
                $this->setReference($data['reference']);
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
        if (isset($this->meaning)) {
            $json['meaning'] = $this->meaning;
        }
        if (isset($this->reference)) {
            $json['reference'] = $this->reference;
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
            $sxe = new \SimpleXMLElement('<ConsentData1 xmlns="http://hl7.org/fhir"></ConsentData1>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->meaning)) {
            $this->meaning->xmlSerialize(true, $sxe->addChild('meaning'));
        }
        if (isset($this->reference)) {
            $this->reference->xmlSerialize(true, $sxe->addChild('reference'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
