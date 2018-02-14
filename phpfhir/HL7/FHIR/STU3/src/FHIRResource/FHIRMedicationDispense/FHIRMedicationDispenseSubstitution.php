<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRMedicationDispense;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * Indicates that a medication product is to be or has been dispensed for a named person/patient.  This includes a description of the medication product (supply) provided and the instructions for administering the medication.  The medication dispense is the result of a pharmacy system responding to a medication order.
 */
class FHIRMedicationDispenseSubstitution extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * True if the dispenser dispensed a different drug or product from what was prescribed.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public $wasSubstituted = null;

    /**
     * A code signifying whether a different drug was dispensed from what was prescribed.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $type = null;

    /**
     * Indicates the reason for the substitution of (or lack of substitution) from what was prescribed.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept[]
     */
    public $reason = [];

    /**
     * The person or organization that has primary responsibility for the substitution.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference[]
     */
    public $responsibleParty = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'MedicationDispense.Substitution';

    /**
     * True if the dispenser dispensed a different drug or product from what was prescribed.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public function getWasSubstituted()
    {
        return $this->wasSubstituted;
    }

    /**
     * True if the dispenser dispensed a different drug or product from what was prescribed.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRBoolean $wasSubstituted
     * @return $this
     */
    public function setWasSubstituted($wasSubstituted)
    {
        $this->wasSubstituted = $wasSubstituted;
        return $this;
    }

    /**
     * A code signifying whether a different drug was dispensed from what was prescribed.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * A code signifying whether a different drug was dispensed from what was prescribed.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Indicates the reason for the substitution of (or lack of substitution) from what was prescribed.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept[]
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * Indicates the reason for the substitution of (or lack of substitution) from what was prescribed.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $reason
     * @return $this
     */
    public function addReason($reason)
    {
        $this->reason[] = $reason;
        return $this;
    }

    /**
     * The person or organization that has primary responsibility for the substitution.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference[]
     */
    public function getResponsibleParty()
    {
        return $this->responsibleParty;
    }

    /**
     * The person or organization that has primary responsibility for the substitution.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $responsibleParty
     * @return $this
     */
    public function addResponsibleParty($responsibleParty)
    {
        $this->responsibleParty[] = $responsibleParty;
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
            if (isset($data['wasSubstituted'])) {
                $this->setWasSubstituted($data['wasSubstituted']);
            }
            if (isset($data['type'])) {
                $this->setType($data['type']);
            }
            if (isset($data['reason'])) {
                if (is_array($data['reason'])) {
                    foreach ($data['reason'] as $d) {
                        $this->addReason($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"reason" must be array of objects or null, '.gettype($data['reason']).' seen.');
                }
            }
            if (isset($data['responsibleParty'])) {
                if (is_array($data['responsibleParty'])) {
                    foreach ($data['responsibleParty'] as $d) {
                        $this->addResponsibleParty($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"responsibleParty" must be array of objects or null, '.gettype($data['responsibleParty']).' seen.');
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
        if (isset($this->wasSubstituted)) {
            $json['wasSubstituted'] = $this->wasSubstituted;
        }
        if (isset($this->type)) {
            $json['type'] = $this->type;
        }
        if (0 < count($this->reason)) {
            $json['reason'] = [];
            foreach ($this->reason as $reason) {
                $json['reason'][] = $reason;
            }
        }
        if (0 < count($this->responsibleParty)) {
            $json['responsibleParty'] = [];
            foreach ($this->responsibleParty as $responsibleParty) {
                $json['responsibleParty'][] = $responsibleParty;
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
            $sxe = new \SimpleXMLElement('<MedicationDispenseSubstitution xmlns="http://hl7.org/fhir"></MedicationDispenseSubstitution>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->wasSubstituted)) {
            $this->wasSubstituted->xmlSerialize(true, $sxe->addChild('wasSubstituted'));
        }
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (0 < count($this->reason)) {
            foreach ($this->reason as $reason) {
                $reason->xmlSerialize(true, $sxe->addChild('reason'));
            }
        }
        if (0 < count($this->responsibleParty)) {
            foreach ($this->responsibleParty as $responsibleParty) {
                $responsibleParty->xmlSerialize(true, $sxe->addChild('responsibleParty'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
