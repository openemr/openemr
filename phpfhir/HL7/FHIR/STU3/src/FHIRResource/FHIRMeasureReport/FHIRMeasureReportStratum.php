<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRMeasureReport;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * The MeasureReport resource contains the results of evaluating a measure.
 */
class FHIRMeasureReportStratum extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The value for this stratum, expressed as a string. When defining stratifiers on complex values, the value must be rendered such that the value for each stratum within the stratifier is unique.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $value = null;

    /**
     * The populations that make up the stratum, one for each type of population appropriate to the measure.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRMeasureReport\FHIRMeasureReportPopulation1[]
     */
    public $population = [];

    /**
     * The measure score for this stratum, calculated as appropriate for the measure type and scoring method, and based on only the members of this stratum.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public $measureScore = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'MeasureReport.Stratum';

    /**
     * The value for this stratum, expressed as a string. When defining stratifiers on complex values, the value must be rendered such that the value for each stratum within the stratifier is unique.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * The value for this stratum, expressed as a string. When defining stratifiers on complex values, the value must be rendered such that the value for each stratum within the stratifier is unique.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * The populations that make up the stratum, one for each type of population appropriate to the measure.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRMeasureReport\FHIRMeasureReportPopulation1[]
     */
    public function getPopulation()
    {
        return $this->population;
    }

    /**
     * The populations that make up the stratum, one for each type of population appropriate to the measure.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRMeasureReport\FHIRMeasureReportPopulation1 $population
     * @return $this
     */
    public function addPopulation($population)
    {
        $this->population[] = $population;
        return $this;
    }

    /**
     * The measure score for this stratum, calculated as appropriate for the measure type and scoring method, and based on only the members of this stratum.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public function getMeasureScore()
    {
        return $this->measureScore;
    }

    /**
     * The measure score for this stratum, calculated as appropriate for the measure type and scoring method, and based on only the members of this stratum.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDecimal $measureScore
     * @return $this
     */
    public function setMeasureScore($measureScore)
    {
        $this->measureScore = $measureScore;
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
            if (isset($data['value'])) {
                $this->setValue($data['value']);
            }
            if (isset($data['population'])) {
                if (is_array($data['population'])) {
                    foreach ($data['population'] as $d) {
                        $this->addPopulation($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"population" must be array of objects or null, '.gettype($data['population']).' seen.');
                }
            }
            if (isset($data['measureScore'])) {
                $this->setMeasureScore($data['measureScore']);
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
        return (string)$this->getValue();
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $json = parent::jsonSerialize();
        if (isset($this->value)) {
            $json['value'] = $this->value;
        }
        if (0 < count($this->population)) {
            $json['population'] = [];
            foreach ($this->population as $population) {
                $json['population'][] = $population;
            }
        }
        if (isset($this->measureScore)) {
            $json['measureScore'] = $this->measureScore;
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
            $sxe = new \SimpleXMLElement('<MeasureReportStratum xmlns="http://hl7.org/fhir"></MeasureReportStratum>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->value)) {
            $this->value->xmlSerialize(true, $sxe->addChild('value'));
        }
        if (0 < count($this->population)) {
            foreach ($this->population as $population) {
                $population->xmlSerialize(true, $sxe->addChild('population'));
            }
        }
        if (isset($this->measureScore)) {
            $this->measureScore->xmlSerialize(true, $sxe->addChild('measureScore'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
