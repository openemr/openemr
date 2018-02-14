<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRObservation;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * Measurements and simple assertions made about a patient, device or other subject.
 */
class FHIRObservationRelated extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * A code specifying the kind of relationship that exists with the target resource.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRObservationRelationshipType
     */
    public $type = null;

    /**
     * A reference to the observation or [[[QuestionnaireResponse]]] resource that is related to this observation.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $target = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Observation.Related';

    /**
     * A code specifying the kind of relationship that exists with the target resource.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRObservationRelationshipType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * A code specifying the kind of relationship that exists with the target resource.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRObservationRelationshipType $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * A reference to the observation or [[[QuestionnaireResponse]]] resource that is related to this observation.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * A reference to the observation or [[[QuestionnaireResponse]]] resource that is related to this observation.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $target
     * @return $this
     */
    public function setTarget($target)
    {
        $this->target = $target;
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
            if (isset($data['type'])) {
                $this->setType($data['type']);
            }
            if (isset($data['target'])) {
                $this->setTarget($data['target']);
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
        if (isset($this->type)) {
            $json['type'] = $this->type;
        }
        if (isset($this->target)) {
            $json['target'] = $this->target;
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
            $sxe = new \SimpleXMLElement('<ObservationRelated xmlns="http://hl7.org/fhir"></ObservationRelated>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (isset($this->target)) {
            $this->target->xmlSerialize(true, $sxe->addChild('target'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
