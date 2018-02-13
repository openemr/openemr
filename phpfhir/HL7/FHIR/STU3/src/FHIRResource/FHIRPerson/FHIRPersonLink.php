<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRPerson;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * Demographics and administrative information about a person independent of a specific health-related context.
 */
class FHIRPersonLink extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The resource to which this actual person is associated.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $target = null;

    /**
     * Level of assurance that this link is actually associated with the target resource.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRIdentityAssuranceLevel
     */
    public $assurance = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Person.Link';

    /**
     * The resource to which this actual person is associated.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * The resource to which this actual person is associated.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $target
     * @return $this
     */
    public function setTarget($target)
    {
        $this->target = $target;
        return $this;
    }

    /**
     * Level of assurance that this link is actually associated with the target resource.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRIdentityAssuranceLevel
     */
    public function getAssurance()
    {
        return $this->assurance;
    }

    /**
     * Level of assurance that this link is actually associated with the target resource.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRIdentityAssuranceLevel $assurance
     * @return $this
     */
    public function setAssurance($assurance)
    {
        $this->assurance = $assurance;
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
            if (isset($data['target'])) {
                $this->setTarget($data['target']);
            }
            if (isset($data['assurance'])) {
                $this->setAssurance($data['assurance']);
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
        if (isset($this->target)) {
            $json['target'] = $this->target;
        }
        if (isset($this->assurance)) {
            $json['assurance'] = $this->assurance;
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
            $sxe = new \SimpleXMLElement('<PersonLink xmlns="http://hl7.org/fhir"></PersonLink>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->target)) {
            $this->target->xmlSerialize(true, $sxe->addChild('target'));
        }
        if (isset($this->assurance)) {
            $this->assurance->xmlSerialize(true, $sxe->addChild('assurance'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
