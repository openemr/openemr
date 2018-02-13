<?php namespace HL7\FHIR\STU3\FHIRResource\FHIREpisodeOfCare;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * An association between a patient and an organization / healthcare provider(s) during which time encounters may occur. The managing organization assumes a level of responsibility for the patient during this time.
 */
class FHIREpisodeOfCareDiagnosis extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * A list of conditions/problems/diagnoses that this episode of care is intended to be providing care for.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $condition = null;

    /**
     * Role that this diagnosis has within the episode of care (e.g. admission, billing, discharge …).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $role = null;

    /**
     * Ranking of the diagnosis (for each role type).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRPositiveInt
     */
    public $rank = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'EpisodeOfCare.Diagnosis';

    /**
     * A list of conditions/problems/diagnoses that this episode of care is intended to be providing care for.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * A list of conditions/problems/diagnoses that this episode of care is intended to be providing care for.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $condition
     * @return $this
     */
    public function setCondition($condition)
    {
        $this->condition = $condition;
        return $this;
    }

    /**
     * Role that this diagnosis has within the episode of care (e.g. admission, billing, discharge …).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Role that this diagnosis has within the episode of care (e.g. admission, billing, discharge …).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $role
     * @return $this
     */
    public function setRole($role)
    {
        $this->role = $role;
        return $this;
    }

    /**
     * Ranking of the diagnosis (for each role type).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRPositiveInt
     */
    public function getRank()
    {
        return $this->rank;
    }

    /**
     * Ranking of the diagnosis (for each role type).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRPositiveInt $rank
     * @return $this
     */
    public function setRank($rank)
    {
        $this->rank = $rank;
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
            if (isset($data['condition'])) {
                $this->setCondition($data['condition']);
            }
            if (isset($data['role'])) {
                $this->setRole($data['role']);
            }
            if (isset($data['rank'])) {
                $this->setRank($data['rank']);
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
        if (isset($this->condition)) {
            $json['condition'] = $this->condition;
        }
        if (isset($this->role)) {
            $json['role'] = $this->role;
        }
        if (isset($this->rank)) {
            $json['rank'] = $this->rank;
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
            $sxe = new \SimpleXMLElement('<EpisodeOfCareDiagnosis xmlns="http://hl7.org/fhir"></EpisodeOfCareDiagnosis>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->condition)) {
            $this->condition->xmlSerialize(true, $sxe->addChild('condition'));
        }
        if (isset($this->role)) {
            $this->role->xmlSerialize(true, $sxe->addChild('role'));
        }
        if (isset($this->rank)) {
            $this->rank->xmlSerialize(true, $sxe->addChild('rank'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
