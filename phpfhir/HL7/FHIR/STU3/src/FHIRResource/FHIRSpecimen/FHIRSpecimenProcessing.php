<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRSpecimen;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 *
 * Class creation date: February 10th, 2018
 *
 *
 *
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * A sample to be used for analysis.
 */
class FHIRSpecimenProcessing extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Textual description of procedure.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $description = null;

    /**
     * A coded value specifying the procedure used to process the specimen.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $procedure = null;

    /**
     * Material used in the processing step.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference[]
     */
    public $additive = [];

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDateTime
     */
    public $timeDateTime = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRPeriod
     */
    public $timePeriod = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Specimen.Processing';

    /**
     * Textual description of procedure.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Textual description of procedure.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * A coded value specifying the procedure used to process the specimen.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getProcedure()
    {
        return $this->procedure;
    }

    /**
     * A coded value specifying the procedure used to process the specimen.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $procedure
     * @return $this
     */
    public function setProcedure($procedure)
    {
        $this->procedure = $procedure;
        return $this;
    }

    /**
     * Material used in the processing step.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference[]
     */
    public function getAdditive()
    {
        return $this->additive;
    }

    /**
     * Material used in the processing step.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $additive
     * @return $this
     */
    public function addAdditive($additive)
    {
        $this->additive[] = $additive;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDateTime
     */
    public function getTimeDateTime()
    {
        return $this->timeDateTime;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDateTime $timeDateTime
     * @return $this
     */
    public function setTimeDateTime($timeDateTime)
    {
        $this->timeDateTime = $timeDateTime;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRPeriod
     */
    public function getTimePeriod()
    {
        return $this->timePeriod;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRPeriod $timePeriod
     * @return $this
     */
    public function setTimePeriod($timePeriod)
    {
        $this->timePeriod = $timePeriod;
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
            if (isset($data['description'])) {
                $this->setDescription($data['description']);
            }
            if (isset($data['procedure'])) {
                $this->setProcedure($data['procedure']);
            }
            if (isset($data['additive'])) {
                if (is_array($data['additive'])) {
                    foreach ($data['additive'] as $d) {
                        $this->addAdditive($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"additive" must be array of objects or null, '.gettype($data['additive']).' seen.');
                }
            }
            if (isset($data['timeDateTime'])) {
                $this->setTimeDateTime($data['timeDateTime']);
            }
            if (isset($data['timePeriod'])) {
                $this->setTimePeriod($data['timePeriod']);
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
        if (isset($this->description)) {
            $json['description'] = $this->description;
        }
        if (isset($this->procedure)) {
            $json['procedure'] = $this->procedure;
        }
        if (0 < count($this->additive)) {
            $json['additive'] = [];
            foreach ($this->additive as $additive) {
                $json['additive'][] = $additive;
            }
        }
        if (isset($this->timeDateTime)) {
            $json['timeDateTime'] = $this->timeDateTime;
        }
        if (isset($this->timePeriod)) {
            $json['timePeriod'] = $this->timePeriod;
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
            $sxe = new \SimpleXMLElement('<SpecimenProcessing xmlns="http://hl7.org/fhir"></SpecimenProcessing>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->description)) {
            $this->description->xmlSerialize(true, $sxe->addChild('description'));
        }
        if (isset($this->procedure)) {
            $this->procedure->xmlSerialize(true, $sxe->addChild('procedure'));
        }
        if (0 < count($this->additive)) {
            foreach ($this->additive as $additive) {
                $additive->xmlSerialize(true, $sxe->addChild('additive'));
            }
        }
        if (isset($this->timeDateTime)) {
            $this->timeDateTime->xmlSerialize(true, $sxe->addChild('timeDateTime'));
        }
        if (isset($this->timePeriod)) {
            $this->timePeriod->xmlSerialize(true, $sxe->addChild('timePeriod'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
