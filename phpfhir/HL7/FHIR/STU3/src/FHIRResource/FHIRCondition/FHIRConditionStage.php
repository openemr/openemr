<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRCondition;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 *
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * A clinical condition, problem, diagnosis, or other event, situation, issue, or clinical concept that has risen to a level of concern.
 */
class FHIRConditionStage extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * A simple summary of the stage such as "Stage 3". The determination of the stage is disease-specific.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $summary = null;

    /**
     * Reference to a formal record of the evidence on which the staging assessment is based.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference[]
     */
    public $assessment = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'Condition.Stage';

    /**
     * A simple summary of the stage such as "Stage 3". The determination of the stage is disease-specific.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * A simple summary of the stage such as "Stage 3". The determination of the stage is disease-specific.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $summary
     * @return $this
     */
    public function setSummary($summary)
    {
        $this->summary = $summary;
        return $this;
    }

    /**
     * Reference to a formal record of the evidence on which the staging assessment is based.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference[]
     */
    public function getAssessment()
    {
        return $this->assessment;
    }

    /**
     * Reference to a formal record of the evidence on which the staging assessment is based.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $assessment
     * @return $this
     */
    public function addAssessment($assessment)
    {
        $this->assessment[] = $assessment;
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
            if (isset($data['summary'])) {
                $this->setSummary($data['summary']);
            }
            if (isset($data['assessment'])) {
                if (is_array($data['assessment'])) {
                    foreach ($data['assessment'] as $d) {
                        $this->addAssessment($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"assessment" must be array of objects or null, '.gettype($data['assessment']).' seen.');
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
        if (isset($this->summary)) {
            $json['summary'] = $this->summary;
        }
        if (0 < count($this->assessment)) {
            $json['assessment'] = [];
            foreach ($this->assessment as $assessment) {
                $json['assessment'][] = $assessment;
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
            $sxe = new \SimpleXMLElement('<ConditionStage xmlns="http://hl7.org/fhir"></ConditionStage>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->summary)) {
            $this->summary->xmlSerialize(true, $sxe->addChild('summary'));
        }
        if (0 < count($this->assessment)) {
            foreach ($this->assessment as $assessment) {
                $assessment->xmlSerialize(true, $sxe->addChild('assessment'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
