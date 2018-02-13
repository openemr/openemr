<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRCarePlan;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * Describes the intention of how one or more practitioners intend to deliver care for a particular patient, group or community for a period of time, possibly limited to care for a specific condition or set of conditions.
 */
class FHIRCarePlanActivity extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Identifies the outcome at the point when the status of the activity is assessed.  For example, the outcome of an education activity could be patient understands (or not).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept[]
     */
    public $outcomeCodeableConcept = [];

    /**
     * Details of the outcome or action resulting from the activity.  The reference to an "event" resource, such as Procedure or Encounter or Observation, is the result/outcome of the activity itself.  The activity can be conveyed using CarePlan.activity.detail OR using the CarePlan.activity.reference (a reference to a “request” resource).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference[]
     */
    public $outcomeReference = [];

    /**
     * Notes about the adherence/status/progress of the activity.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRAnnotation[]
     */
    public $progress = [];

    /**
     * The details of the proposed activity represented in a specific resource.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $reference = null;

    /**
     * A simple summary of a planned activity suitable for a general care plan system (e.g. form driven) that doesn't know about specific resources such as procedure etc.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRCarePlan\FHIRCarePlanDetail
     */
    public $detail = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'CarePlan.Activity';

    /**
     * Identifies the outcome at the point when the status of the activity is assessed.  For example, the outcome of an education activity could be patient understands (or not).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept[]
     */
    public function getOutcomeCodeableConcept()
    {
        return $this->outcomeCodeableConcept;
    }

    /**
     * Identifies the outcome at the point when the status of the activity is assessed.  For example, the outcome of an education activity could be patient understands (or not).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $outcomeCodeableConcept
     * @return $this
     */
    public function addOutcomeCodeableConcept($outcomeCodeableConcept)
    {
        $this->outcomeCodeableConcept[] = $outcomeCodeableConcept;
        return $this;
    }

    /**
     * Details of the outcome or action resulting from the activity.  The reference to an "event" resource, such as Procedure or Encounter or Observation, is the result/outcome of the activity itself.  The activity can be conveyed using CarePlan.activity.detail OR using the CarePlan.activity.reference (a reference to a “request” resource).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference[]
     */
    public function getOutcomeReference()
    {
        return $this->outcomeReference;
    }

    /**
     * Details of the outcome or action resulting from the activity.  The reference to an "event" resource, such as Procedure or Encounter or Observation, is the result/outcome of the activity itself.  The activity can be conveyed using CarePlan.activity.detail OR using the CarePlan.activity.reference (a reference to a “request” resource).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $outcomeReference
     * @return $this
     */
    public function addOutcomeReference($outcomeReference)
    {
        $this->outcomeReference[] = $outcomeReference;
        return $this;
    }

    /**
     * Notes about the adherence/status/progress of the activity.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRAnnotation[]
     */
    public function getProgress()
    {
        return $this->progress;
    }

    /**
     * Notes about the adherence/status/progress of the activity.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRAnnotation $progress
     * @return $this
     */
    public function addProgress($progress)
    {
        $this->progress[] = $progress;
        return $this;
    }

    /**
     * The details of the proposed activity represented in a specific resource.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * The details of the proposed activity represented in a specific resource.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $reference
     * @return $this
     */
    public function setReference($reference)
    {
        $this->reference = $reference;
        return $this;
    }

    /**
     * A simple summary of a planned activity suitable for a general care plan system (e.g. form driven) that doesn't know about specific resources such as procedure etc.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRCarePlan\FHIRCarePlanDetail
     */
    public function getDetail()
    {
        return $this->detail;
    }

    /**
     * A simple summary of a planned activity suitable for a general care plan system (e.g. form driven) that doesn't know about specific resources such as procedure etc.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRCarePlan\FHIRCarePlanDetail $detail
     * @return $this
     */
    public function setDetail($detail)
    {
        $this->detail = $detail;
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
            if (isset($data['outcomeCodeableConcept'])) {
                if (is_array($data['outcomeCodeableConcept'])) {
                    foreach ($data['outcomeCodeableConcept'] as $d) {
                        $this->addOutcomeCodeableConcept($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"outcomeCodeableConcept" must be array of objects or null, '.gettype($data['outcomeCodeableConcept']).' seen.');
                }
            }
            if (isset($data['outcomeReference'])) {
                if (is_array($data['outcomeReference'])) {
                    foreach ($data['outcomeReference'] as $d) {
                        $this->addOutcomeReference($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"outcomeReference" must be array of objects or null, '.gettype($data['outcomeReference']).' seen.');
                }
            }
            if (isset($data['progress'])) {
                if (is_array($data['progress'])) {
                    foreach ($data['progress'] as $d) {
                        $this->addProgress($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"progress" must be array of objects or null, '.gettype($data['progress']).' seen.');
                }
            }
            if (isset($data['reference'])) {
                $this->setReference($data['reference']);
            }
            if (isset($data['detail'])) {
                $this->setDetail($data['detail']);
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
        if (0 < count($this->outcomeCodeableConcept)) {
            $json['outcomeCodeableConcept'] = [];
            foreach ($this->outcomeCodeableConcept as $outcomeCodeableConcept) {
                $json['outcomeCodeableConcept'][] = $outcomeCodeableConcept;
            }
        }
        if (0 < count($this->outcomeReference)) {
            $json['outcomeReference'] = [];
            foreach ($this->outcomeReference as $outcomeReference) {
                $json['outcomeReference'][] = $outcomeReference;
            }
        }
        if (0 < count($this->progress)) {
            $json['progress'] = [];
            foreach ($this->progress as $progress) {
                $json['progress'][] = $progress;
            }
        }
        if (isset($this->reference)) {
            $json['reference'] = $this->reference;
        }
        if (isset($this->detail)) {
            $json['detail'] = $this->detail;
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
            $sxe = new \SimpleXMLElement('<CarePlanActivity xmlns="http://hl7.org/fhir"></CarePlanActivity>');
        }
        parent::xmlSerialize(true, $sxe);
        if (0 < count($this->outcomeCodeableConcept)) {
            foreach ($this->outcomeCodeableConcept as $outcomeCodeableConcept) {
                $outcomeCodeableConcept->xmlSerialize(true, $sxe->addChild('outcomeCodeableConcept'));
            }
        }
        if (0 < count($this->outcomeReference)) {
            foreach ($this->outcomeReference as $outcomeReference) {
                $outcomeReference->xmlSerialize(true, $sxe->addChild('outcomeReference'));
            }
        }
        if (0 < count($this->progress)) {
            foreach ($this->progress as $progress) {
                $progress->xmlSerialize(true, $sxe->addChild('progress'));
            }
        }
        if (isset($this->reference)) {
            $this->reference->xmlSerialize(true, $sxe->addChild('reference'));
        }
        if (isset($this->detail)) {
            $this->detail->xmlSerialize(true, $sxe->addChild('detail'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
