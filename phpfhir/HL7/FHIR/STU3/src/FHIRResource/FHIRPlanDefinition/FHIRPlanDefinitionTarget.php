<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRPlanDefinition;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * This resource allows for the definition of various types of plans as a sharable, consumable, and executable artifact. The resource is general enough to support the description of a broad range of clinical artifacts such as clinical decision support rules, order sets and protocols.
 */
class FHIRPlanDefinitionTarget extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The parameter whose value is to be tracked, e.g. body weigth, blood pressure, or hemoglobin A1c level.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $measure = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRQuantity
     */
    public $detailQuantity = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRRange
     */
    public $detailRange = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $detailCodeableConcept = null;

    /**
     * Indicates the timeframe after the start of the goal in which the goal should be met.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRQuantity\FHIRDuration
     */
    public $due = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'PlanDefinition.Target';

    /**
     * The parameter whose value is to be tracked, e.g. body weigth, blood pressure, or hemoglobin A1c level.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getMeasure()
    {
        return $this->measure;
    }

    /**
     * The parameter whose value is to be tracked, e.g. body weigth, blood pressure, or hemoglobin A1c level.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $measure
     * @return $this
     */
    public function setMeasure($measure)
    {
        $this->measure = $measure;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRQuantity
     */
    public function getDetailQuantity()
    {
        return $this->detailQuantity;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRQuantity $detailQuantity
     * @return $this
     */
    public function setDetailQuantity($detailQuantity)
    {
        $this->detailQuantity = $detailQuantity;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRRange
     */
    public function getDetailRange()
    {
        return $this->detailRange;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRRange $detailRange
     * @return $this
     */
    public function setDetailRange($detailRange)
    {
        $this->detailRange = $detailRange;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getDetailCodeableConcept()
    {
        return $this->detailCodeableConcept;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $detailCodeableConcept
     * @return $this
     */
    public function setDetailCodeableConcept($detailCodeableConcept)
    {
        $this->detailCodeableConcept = $detailCodeableConcept;
        return $this;
    }

    /**
     * Indicates the timeframe after the start of the goal in which the goal should be met.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRQuantity\FHIRDuration
     */
    public function getDue()
    {
        return $this->due;
    }

    /**
     * Indicates the timeframe after the start of the goal in which the goal should be met.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRQuantity\FHIRDuration $due
     * @return $this
     */
    public function setDue($due)
    {
        $this->due = $due;
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
            if (isset($data['measure'])) {
                $this->setMeasure($data['measure']);
            }
            if (isset($data['detailQuantity'])) {
                $this->setDetailQuantity($data['detailQuantity']);
            }
            if (isset($data['detailRange'])) {
                $this->setDetailRange($data['detailRange']);
            }
            if (isset($data['detailCodeableConcept'])) {
                $this->setDetailCodeableConcept($data['detailCodeableConcept']);
            }
            if (isset($data['due'])) {
                $this->setDue($data['due']);
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
        if (isset($this->measure)) {
            $json['measure'] = $this->measure;
        }
        if (isset($this->detailQuantity)) {
            $json['detailQuantity'] = $this->detailQuantity;
        }
        if (isset($this->detailRange)) {
            $json['detailRange'] = $this->detailRange;
        }
        if (isset($this->detailCodeableConcept)) {
            $json['detailCodeableConcept'] = $this->detailCodeableConcept;
        }
        if (isset($this->due)) {
            $json['due'] = $this->due;
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
            $sxe = new \SimpleXMLElement('<PlanDefinitionTarget xmlns="http://hl7.org/fhir"></PlanDefinitionTarget>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->measure)) {
            $this->measure->xmlSerialize(true, $sxe->addChild('measure'));
        }
        if (isset($this->detailQuantity)) {
            $this->detailQuantity->xmlSerialize(true, $sxe->addChild('detailQuantity'));
        }
        if (isset($this->detailRange)) {
            $this->detailRange->xmlSerialize(true, $sxe->addChild('detailRange'));
        }
        if (isset($this->detailCodeableConcept)) {
            $this->detailCodeableConcept->xmlSerialize(true, $sxe->addChild('detailCodeableConcept'));
        }
        if (isset($this->due)) {
            $this->due->xmlSerialize(true, $sxe->addChild('due'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
