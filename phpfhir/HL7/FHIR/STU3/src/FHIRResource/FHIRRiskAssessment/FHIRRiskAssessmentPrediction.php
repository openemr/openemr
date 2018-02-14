<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRRiskAssessment;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * An assessment of the likely outcome(s) for a patient or other subject as well as the likelihood of each outcome.
 */
class FHIRRiskAssessmentPrediction extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * One of the potential outcomes for the patient (e.g. remission, death,  a particular condition).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $outcome = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public $probabilityDecimal = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRRange
     */
    public $probabilityRange = null;

    /**
     * How likely is the outcome (in the specified timeframe), expressed as a qualitative value (e.g. low, medium, high).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $qualitativeRisk = null;

    /**
     * Indicates the risk for this particular subject (with their specific characteristics) divided by the risk of the population in general.  (Numbers greater than 1 = higher risk than the population, numbers less than 1 = lower risk.).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public $relativeRisk = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRPeriod
     */
    public $whenPeriod = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRRange
     */
    public $whenRange = null;

    /**
     * Additional information explaining the basis for the prediction.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $rationale = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'RiskAssessment.Prediction';

    /**
     * One of the potential outcomes for the patient (e.g. remission, death,  a particular condition).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getOutcome()
    {
        return $this->outcome;
    }

    /**
     * One of the potential outcomes for the patient (e.g. remission, death,  a particular condition).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $outcome
     * @return $this
     */
    public function setOutcome($outcome)
    {
        $this->outcome = $outcome;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public function getProbabilityDecimal()
    {
        return $this->probabilityDecimal;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDecimal $probabilityDecimal
     * @return $this
     */
    public function setProbabilityDecimal($probabilityDecimal)
    {
        $this->probabilityDecimal = $probabilityDecimal;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRRange
     */
    public function getProbabilityRange()
    {
        return $this->probabilityRange;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRRange $probabilityRange
     * @return $this
     */
    public function setProbabilityRange($probabilityRange)
    {
        $this->probabilityRange = $probabilityRange;
        return $this;
    }

    /**
     * How likely is the outcome (in the specified timeframe), expressed as a qualitative value (e.g. low, medium, high).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getQualitativeRisk()
    {
        return $this->qualitativeRisk;
    }

    /**
     * How likely is the outcome (in the specified timeframe), expressed as a qualitative value (e.g. low, medium, high).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $qualitativeRisk
     * @return $this
     */
    public function setQualitativeRisk($qualitativeRisk)
    {
        $this->qualitativeRisk = $qualitativeRisk;
        return $this;
    }

    /**
     * Indicates the risk for this particular subject (with their specific characteristics) divided by the risk of the population in general.  (Numbers greater than 1 = higher risk than the population, numbers less than 1 = lower risk.).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public function getRelativeRisk()
    {
        return $this->relativeRisk;
    }

    /**
     * Indicates the risk for this particular subject (with their specific characteristics) divided by the risk of the population in general.  (Numbers greater than 1 = higher risk than the population, numbers less than 1 = lower risk.).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDecimal $relativeRisk
     * @return $this
     */
    public function setRelativeRisk($relativeRisk)
    {
        $this->relativeRisk = $relativeRisk;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRPeriod
     */
    public function getWhenPeriod()
    {
        return $this->whenPeriod;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRPeriod $whenPeriod
     * @return $this
     */
    public function setWhenPeriod($whenPeriod)
    {
        $this->whenPeriod = $whenPeriod;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRRange
     */
    public function getWhenRange()
    {
        return $this->whenRange;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRRange $whenRange
     * @return $this
     */
    public function setWhenRange($whenRange)
    {
        $this->whenRange = $whenRange;
        return $this;
    }

    /**
     * Additional information explaining the basis for the prediction.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getRationale()
    {
        return $this->rationale;
    }

    /**
     * Additional information explaining the basis for the prediction.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $rationale
     * @return $this
     */
    public function setRationale($rationale)
    {
        $this->rationale = $rationale;
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
            if (isset($data['outcome'])) {
                $this->setOutcome($data['outcome']);
            }
            if (isset($data['probabilityDecimal'])) {
                $this->setProbabilityDecimal($data['probabilityDecimal']);
            }
            if (isset($data['probabilityRange'])) {
                $this->setProbabilityRange($data['probabilityRange']);
            }
            if (isset($data['qualitativeRisk'])) {
                $this->setQualitativeRisk($data['qualitativeRisk']);
            }
            if (isset($data['relativeRisk'])) {
                $this->setRelativeRisk($data['relativeRisk']);
            }
            if (isset($data['whenPeriod'])) {
                $this->setWhenPeriod($data['whenPeriod']);
            }
            if (isset($data['whenRange'])) {
                $this->setWhenRange($data['whenRange']);
            }
            if (isset($data['rationale'])) {
                $this->setRationale($data['rationale']);
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
        if (isset($this->outcome)) {
            $json['outcome'] = $this->outcome;
        }
        if (isset($this->probabilityDecimal)) {
            $json['probabilityDecimal'] = $this->probabilityDecimal;
        }
        if (isset($this->probabilityRange)) {
            $json['probabilityRange'] = $this->probabilityRange;
        }
        if (isset($this->qualitativeRisk)) {
            $json['qualitativeRisk'] = $this->qualitativeRisk;
        }
        if (isset($this->relativeRisk)) {
            $json['relativeRisk'] = $this->relativeRisk;
        }
        if (isset($this->whenPeriod)) {
            $json['whenPeriod'] = $this->whenPeriod;
        }
        if (isset($this->whenRange)) {
            $json['whenRange'] = $this->whenRange;
        }
        if (isset($this->rationale)) {
            $json['rationale'] = $this->rationale;
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
            $sxe = new \SimpleXMLElement('<RiskAssessmentPrediction xmlns="http://hl7.org/fhir"></RiskAssessmentPrediction>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->outcome)) {
            $this->outcome->xmlSerialize(true, $sxe->addChild('outcome'));
        }
        if (isset($this->probabilityDecimal)) {
            $this->probabilityDecimal->xmlSerialize(true, $sxe->addChild('probabilityDecimal'));
        }
        if (isset($this->probabilityRange)) {
            $this->probabilityRange->xmlSerialize(true, $sxe->addChild('probabilityRange'));
        }
        if (isset($this->qualitativeRisk)) {
            $this->qualitativeRisk->xmlSerialize(true, $sxe->addChild('qualitativeRisk'));
        }
        if (isset($this->relativeRisk)) {
            $this->relativeRisk->xmlSerialize(true, $sxe->addChild('relativeRisk'));
        }
        if (isset($this->whenPeriod)) {
            $this->whenPeriod->xmlSerialize(true, $sxe->addChild('whenPeriod'));
        }
        if (isset($this->whenRange)) {
            $this->whenRange->xmlSerialize(true, $sxe->addChild('whenRange'));
        }
        if (isset($this->rationale)) {
            $this->rationale->xmlSerialize(true, $sxe->addChild('rationale'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
