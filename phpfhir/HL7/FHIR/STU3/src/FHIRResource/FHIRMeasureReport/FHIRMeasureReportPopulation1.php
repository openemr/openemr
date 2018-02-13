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
class FHIRMeasureReportPopulation1 extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The identifier of the population being reported, as defined by the population element of the measure.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier
     */
    public $identifier = null;

    /**
     * The type of the population.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $code = null;

    /**
     * The number of members of the population in this stratum.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRInteger
     */
    public $count = null;

    /**
     * This element refers to a List of patient level MeasureReport resources, one for each patient in this population in this stratum.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $patients = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'MeasureReport.Population1';

    /**
     * The identifier of the population being reported, as defined by the population element of the measure.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * The identifier of the population being reported, as defined by the population element of the measure.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * The type of the population.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * The type of the population.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * The number of members of the population in this stratum.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRInteger
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * The number of members of the population in this stratum.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRInteger $count
     * @return $this
     */
    public function setCount($count)
    {
        $this->count = $count;
        return $this;
    }

    /**
     * This element refers to a List of patient level MeasureReport resources, one for each patient in this population in this stratum.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getPatients()
    {
        return $this->patients;
    }

    /**
     * This element refers to a List of patient level MeasureReport resources, one for each patient in this population in this stratum.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $patients
     * @return $this
     */
    public function setPatients($patients)
    {
        $this->patients = $patients;
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
            if (isset($data['code'])) {
                $this->setCode($data['code']);
            }
            if (isset($data['count'])) {
                $this->setCount($data['count']);
            }
            if (isset($data['patients'])) {
                $this->setPatients($data['patients']);
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
        if (isset($this->code)) {
            $json['code'] = $this->code;
        }
        if (isset($this->count)) {
            $json['count'] = $this->count;
        }
        if (isset($this->patients)) {
            $json['patients'] = $this->patients;
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
            $sxe = new \SimpleXMLElement('<MeasureReportPopulation1 xmlns="http://hl7.org/fhir"></MeasureReportPopulation1>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->identifier)) {
            $this->identifier->xmlSerialize(true, $sxe->addChild('identifier'));
        }
        if (isset($this->code)) {
            $this->code->xmlSerialize(true, $sxe->addChild('code'));
        }
        if (isset($this->count)) {
            $this->count->xmlSerialize(true, $sxe->addChild('count'));
        }
        if (isset($this->patients)) {
            $this->patients->xmlSerialize(true, $sxe->addChild('patients'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
