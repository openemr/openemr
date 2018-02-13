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
class FHIRPatientLink extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The other patient resource that the link refers to.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $other = null;

    /**
     * The type of link between this patient resource and another patient resource.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRLinkType
     */
    public $type = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Patient.Link';

    /**
     * The other patient resource that the link refers to.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getOther()
    {
        return $this->other;
    }

    /**
     * The other patient resource that the link refers to.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $other
     * @return $this
     */
    public function setOther($other)
    {
        $this->other = $other;
        return $this;
    }

    /**
     * The type of link between this patient resource and another patient resource.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRLinkType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * The type of link between this patient resource and another patient resource.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRLinkType $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
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
            if (isset($data['other'])) {
                $this->setOther($data['other']);
            }
            if (isset($data['type'])) {
                $this->setType($data['type']);
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
        if (isset($this->other)) {
            $json['other'] = $this->other;
        }
        if (isset($this->type)) {
            $json['type'] = $this->type;
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
            $sxe = new \SimpleXMLElement('<PatientLink xmlns="http://hl7.org/fhir"></PatientLink>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->other)) {
            $this->other->xmlSerialize(true, $sxe->addChild('other'));
        }
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
