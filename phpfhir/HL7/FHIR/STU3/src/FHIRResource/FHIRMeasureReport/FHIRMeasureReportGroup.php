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
class FHIRMeasureReportGroup extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The identifier of the population group as defined in the measure definition.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier
     */
    public $identifier = null;

    /**
     * The populations that make up the population group, one for each type of population appropriate for the measure.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRMeasureReport\FHIRMeasureReportPopulation[]
     */
    public $population = [];

    /**
     * The measure score for this population group, calculated as appropriate for the measure type and scoring method, and based on the contents of the populations defined in the group.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public $measureScore = null;

    /**
     * When a measure includes multiple stratifiers, there will be a stratifier group for each stratifier defined by the measure.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRMeasureReport\FHIRMeasureReportStratifier[]
     */
    public $stratifier = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'MeasureReport.Group';

    /**
     * The identifier of the population group as defined in the measure definition.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * The identifier of the population group as defined in the measure definition.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * The populations that make up the population group, one for each type of population appropriate for the measure.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRMeasureReport\FHIRMeasureReportPopulation[]
     */
    public function getPopulation()
    {
        return $this->population;
    }

    /**
     * The populations that make up the population group, one for each type of population appropriate for the measure.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRMeasureReport\FHIRMeasureReportPopulation $population
     * @return $this
     */
    public function addPopulation($population)
    {
        $this->population[] = $population;
        return $this;
    }

    /**
     * The measure score for this population group, calculated as appropriate for the measure type and scoring method, and based on the contents of the populations defined in the group.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public function getMeasureScore()
    {
        return $this->measureScore;
    }

    /**
     * The measure score for this population group, calculated as appropriate for the measure type and scoring method, and based on the contents of the populations defined in the group.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDecimal $measureScore
     * @return $this
     */
    public function setMeasureScore($measureScore)
    {
        $this->measureScore = $measureScore;
        return $this;
    }

    /**
     * When a measure includes multiple stratifiers, there will be a stratifier group for each stratifier defined by the measure.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRMeasureReport\FHIRMeasureReportStratifier[]
     */
    public function getStratifier()
    {
        return $this->stratifier;
    }

    /**
     * When a measure includes multiple stratifiers, there will be a stratifier group for each stratifier defined by the measure.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRMeasureReport\FHIRMeasureReportStratifier $stratifier
     * @return $this
     */
    public function addStratifier($stratifier)
    {
        $this->stratifier[] = $stratifier;
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
            if (isset($data['identifier'])) {
                $this->setIdentifier($data['identifier']);
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
            if (isset($data['stratifier'])) {
                if (is_array($data['stratifier'])) {
                    foreach ($data['stratifier'] as $d) {
                        $this->addStratifier($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"stratifier" must be array of objects or null, '.gettype($data['stratifier']).' seen.');
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
        if (isset($this->identifier)) {
            $json['identifier'] = $this->identifier;
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
        if (0 < count($this->stratifier)) {
            $json['stratifier'] = [];
            foreach ($this->stratifier as $stratifier) {
                $json['stratifier'][] = $stratifier;
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
            $sxe = new \SimpleXMLElement('<MeasureReportGroup xmlns="http://hl7.org/fhir"></MeasureReportGroup>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->identifier)) {
            $this->identifier->xmlSerialize(true, $sxe->addChild('identifier'));
        }
        if (0 < count($this->population)) {
            foreach ($this->population as $population) {
                $population->xmlSerialize(true, $sxe->addChild('population'));
            }
        }
        if (isset($this->measureScore)) {
            $this->measureScore->xmlSerialize(true, $sxe->addChild('measureScore'));
        }
        if (0 < count($this->stratifier)) {
            foreach ($this->stratifier as $stratifier) {
                $stratifier->xmlSerialize(true, $sxe->addChild('stratifier'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
