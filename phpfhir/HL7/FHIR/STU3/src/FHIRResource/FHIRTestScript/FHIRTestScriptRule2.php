<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRTestScript;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * A structured set of tests against a FHIR server implementation to determine compliance against the FHIR specification.
 */
class FHIRTestScriptRule2 extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The TestScript.rule id value this assert will evaluate.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRId
     */
    public $ruleId = null;

    /**
     * Each rule template can take one or more parameters for rule evaluation.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRTestScript\FHIRTestScriptParam2[]
     */
    public $param = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'TestScript.Rule2';

    /**
     * The TestScript.rule id value this assert will evaluate.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRId
     */
    public function getRuleId()
    {
        return $this->ruleId;
    }

    /**
     * The TestScript.rule id value this assert will evaluate.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRId $ruleId
     * @return $this
     */
    public function setRuleId($ruleId)
    {
        $this->ruleId = $ruleId;
        return $this;
    }

    /**
     * Each rule template can take one or more parameters for rule evaluation.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRTestScript\FHIRTestScriptParam2[]
     */
    public function getParam()
    {
        return $this->param;
    }

    /**
     * Each rule template can take one or more parameters for rule evaluation.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRTestScript\FHIRTestScriptParam2 $param
     * @return $this
     */
    public function addParam($param)
    {
        $this->param[] = $param;
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
            if (isset($data['ruleId'])) {
                $this->setRuleId($data['ruleId']);
            }
            if (isset($data['param'])) {
                if (is_array($data['param'])) {
                    foreach ($data['param'] as $d) {
                        $this->addParam($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"param" must be array of objects or null, '.gettype($data['param']).' seen.');
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
        if (isset($this->ruleId)) {
            $json['ruleId'] = $this->ruleId;
        }
        if (0 < count($this->param)) {
            $json['param'] = [];
            foreach ($this->param as $param) {
                $json['param'][] = $param;
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
            $sxe = new \SimpleXMLElement('<TestScriptRule2 xmlns="http://hl7.org/fhir"></TestScriptRule2>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->ruleId)) {
            $this->ruleId->xmlSerialize(true, $sxe->addChild('ruleId'));
        }
        if (0 < count($this->param)) {
            foreach ($this->param as $param) {
                $param->xmlSerialize(true, $sxe->addChild('param'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
