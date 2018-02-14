<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRMedicationRequest;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * An order or request for both supply of the medication and the instructions for administration of the medication to a patient. The resource is called "MedicationRequest" rather than "MedicationPrescription" or "MedicationOrder" to generalize the use across inpatient and outpatient settings, including care plans, etc., and to harmonize with workflow patterns.
 */
class FHIRMedicationRequestSubstitution extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * True if the prescriber allows a different drug to be dispensed from what was prescribed.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public $allowed = null;

    /**
     * Indicates the reason for the substitution, or why substitution must or must not be performed.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $reason = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'MedicationRequest.Substitution';

    /**
     * True if the prescriber allows a different drug to be dispensed from what was prescribed.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public function getAllowed()
    {
        return $this->allowed;
    }

    /**
     * True if the prescriber allows a different drug to be dispensed from what was prescribed.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRBoolean $allowed
     * @return $this
     */
    public function setAllowed($allowed)
    {
        $this->allowed = $allowed;
        return $this;
    }

    /**
     * Indicates the reason for the substitution, or why substitution must or must not be performed.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * Indicates the reason for the substitution, or why substitution must or must not be performed.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $reason
     * @return $this
     */
    public function setReason($reason)
    {
        $this->reason = $reason;
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
            if (isset($data['allowed'])) {
                $this->setAllowed($data['allowed']);
            }
            if (isset($data['reason'])) {
                $this->setReason($data['reason']);
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
        if (isset($this->allowed)) {
            $json['allowed'] = $this->allowed;
        }
        if (isset($this->reason)) {
            $json['reason'] = $this->reason;
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
            $sxe = new \SimpleXMLElement('<MedicationRequestSubstitution xmlns="http://hl7.org/fhir"></MedicationRequestSubstitution>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->allowed)) {
            $this->allowed->xmlSerialize(true, $sxe->addChild('allowed'));
        }
        if (isset($this->reason)) {
            $this->reason->xmlSerialize(true, $sxe->addChild('reason'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
