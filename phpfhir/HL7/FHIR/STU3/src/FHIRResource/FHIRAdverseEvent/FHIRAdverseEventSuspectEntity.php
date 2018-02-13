<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRAdverseEvent;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 *
* Class creation date: February 10th, 2018 *
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * Actual or  potential/avoided event causing unintended physical injury resulting from or contributed to by medical care, a research study or other healthcare setting factors that requires additional monitoring, treatment, or hospitalization, or that results in death.
 */
class FHIRAdverseEventSuspectEntity extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Identifies the actual instance of what caused the adverse event.  May be a substance, medication, medication administration, medication statement or a device.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $instance = null;

    /**
     * causality1 | causality2.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRAdverseEventCausality
     */
    public $causality = null;

    /**
     * assess1 | assess2.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $causalityAssessment = null;

    /**
     * AdverseEvent.suspectEntity.causalityProductRelatedness.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $causalityProductRelatedness = null;

    /**
     * method1 | method2.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $causalityMethod = null;

    /**
     * AdverseEvent.suspectEntity.causalityAuthor.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $causalityAuthor = null;

    /**
     * result1 | result2.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $causalityResult = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'AdverseEvent.SuspectEntity';

    /**
     * Identifies the actual instance of what caused the adverse event.  May be a substance, medication, medication administration, medication statement or a device.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getInstance()
    {
        return $this->instance;
    }

    /**
     * Identifies the actual instance of what caused the adverse event.  May be a substance, medication, medication administration, medication statement or a device.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $instance
     * @return $this
     */
    public function setInstance($instance)
    {
        $this->instance = $instance;
        return $this;
    }

    /**
     * causality1 | causality2.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRAdverseEventCausality
     */
    public function getCausality()
    {
        return $this->causality;
    }

    /**
     * causality1 | causality2.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRAdverseEventCausality $causality
     * @return $this
     */
    public function setCausality($causality)
    {
        $this->causality = $causality;
        return $this;
    }

    /**
     * assess1 | assess2.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getCausalityAssessment()
    {
        return $this->causalityAssessment;
    }

    /**
     * assess1 | assess2.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $causalityAssessment
     * @return $this
     */
    public function setCausalityAssessment($causalityAssessment)
    {
        $this->causalityAssessment = $causalityAssessment;
        return $this;
    }

    /**
     * AdverseEvent.suspectEntity.causalityProductRelatedness.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getCausalityProductRelatedness()
    {
        return $this->causalityProductRelatedness;
    }

    /**
     * AdverseEvent.suspectEntity.causalityProductRelatedness.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $causalityProductRelatedness
     * @return $this
     */
    public function setCausalityProductRelatedness($causalityProductRelatedness)
    {
        $this->causalityProductRelatedness = $causalityProductRelatedness;
        return $this;
    }

    /**
     * method1 | method2.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getCausalityMethod()
    {
        return $this->causalityMethod;
    }

    /**
     * method1 | method2.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $causalityMethod
     * @return $this
     */
    public function setCausalityMethod($causalityMethod)
    {
        $this->causalityMethod = $causalityMethod;
        return $this;
    }

    /**
     * AdverseEvent.suspectEntity.causalityAuthor.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getCausalityAuthor()
    {
        return $this->causalityAuthor;
    }

    /**
     * AdverseEvent.suspectEntity.causalityAuthor.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $causalityAuthor
     * @return $this
     */
    public function setCausalityAuthor($causalityAuthor)
    {
        $this->causalityAuthor = $causalityAuthor;
        return $this;
    }

    /**
     * result1 | result2.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getCausalityResult()
    {
        return $this->causalityResult;
    }

    /**
     * result1 | result2.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $causalityResult
     * @return $this
     */
    public function setCausalityResult($causalityResult)
    {
        $this->causalityResult = $causalityResult;
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
            if (isset($data['instance'])) {
                $this->setInstance($data['instance']);
            }
            if (isset($data['causality'])) {
                $this->setCausality($data['causality']);
            }
            if (isset($data['causalityAssessment'])) {
                $this->setCausalityAssessment($data['causalityAssessment']);
            }
            if (isset($data['causalityProductRelatedness'])) {
                $this->setCausalityProductRelatedness($data['causalityProductRelatedness']);
            }
            if (isset($data['causalityMethod'])) {
                $this->setCausalityMethod($data['causalityMethod']);
            }
            if (isset($data['causalityAuthor'])) {
                $this->setCausalityAuthor($data['causalityAuthor']);
            }
            if (isset($data['causalityResult'])) {
                $this->setCausalityResult($data['causalityResult']);
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
        if (isset($this->instance)) {
            $json['instance'] = $this->instance;
        }
        if (isset($this->causality)) {
            $json['causality'] = $this->causality;
        }
        if (isset($this->causalityAssessment)) {
            $json['causalityAssessment'] = $this->causalityAssessment;
        }
        if (isset($this->causalityProductRelatedness)) {
            $json['causalityProductRelatedness'] = $this->causalityProductRelatedness;
        }
        if (isset($this->causalityMethod)) {
            $json['causalityMethod'] = $this->causalityMethod;
        }
        if (isset($this->causalityAuthor)) {
            $json['causalityAuthor'] = $this->causalityAuthor;
        }
        if (isset($this->causalityResult)) {
            $json['causalityResult'] = $this->causalityResult;
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
            $sxe = new \SimpleXMLElement('<AdverseEventSuspectEntity xmlns="http://hl7.org/fhir"></AdverseEventSuspectEntity>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->instance)) {
            $this->instance->xmlSerialize(true, $sxe->addChild('instance'));
        }
        if (isset($this->causality)) {
            $this->causality->xmlSerialize(true, $sxe->addChild('causality'));
        }
        if (isset($this->causalityAssessment)) {
            $this->causalityAssessment->xmlSerialize(true, $sxe->addChild('causalityAssessment'));
        }
        if (isset($this->causalityProductRelatedness)) {
            $this->causalityProductRelatedness->xmlSerialize(true, $sxe->addChild('causalityProductRelatedness'));
        }
        if (isset($this->causalityMethod)) {
            $this->causalityMethod->xmlSerialize(true, $sxe->addChild('causalityMethod'));
        }
        if (isset($this->causalityAuthor)) {
            $this->causalityAuthor->xmlSerialize(true, $sxe->addChild('causalityAuthor'));
        }
        if (isset($this->causalityResult)) {
            $this->causalityResult->xmlSerialize(true, $sxe->addChild('causalityResult'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
