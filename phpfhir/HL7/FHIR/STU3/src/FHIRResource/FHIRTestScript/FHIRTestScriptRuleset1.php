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
class FHIRTestScriptRuleset1 extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The TestScript.ruleset id value this assert will evaluate.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRId
     */
    public $rulesetId = null;

    /**
     * The referenced rule within the external ruleset template.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRTestScript\FHIRTestScriptRule3[]
     */
    public $rule = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'TestScript.Ruleset1';

    /**
     * The TestScript.ruleset id value this assert will evaluate.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRId
     */
    public function getRulesetId()
    {
        return $this->rulesetId;
    }

    /**
     * The TestScript.ruleset id value this assert will evaluate.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRId $rulesetId
     * @return $this
     */
    public function setRulesetId($rulesetId)
    {
        $this->rulesetId = $rulesetId;
        return $this;
    }

    /**
     * The referenced rule within the external ruleset template.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRTestScript\FHIRTestScriptRule3[]
     */
    public function getRule()
    {
        return $this->rule;
    }

    /**
     * The referenced rule within the external ruleset template.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRTestScript\FHIRTestScriptRule3 $rule
     * @return $this
     */
    public function addRule($rule)
    {
        $this->rule[] = $rule;
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
            if (isset($data['rulesetId'])) {
                $this->setRulesetId($data['rulesetId']);
            }
            if (isset($data['rule'])) {
                if (is_array($data['rule'])) {
                    foreach ($data['rule'] as $d) {
                        $this->addRule($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"rule" must be array of objects or null, '.gettype($data['rule']).' seen.');
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
        if (isset($this->rulesetId)) {
            $json['rulesetId'] = $this->rulesetId;
        }
        if (0 < count($this->rule)) {
            $json['rule'] = [];
            foreach ($this->rule as $rule) {
                $json['rule'][] = $rule;
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
            $sxe = new \SimpleXMLElement('<TestScriptRuleset1 xmlns="http://hl7.org/fhir"></TestScriptRuleset1>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->rulesetId)) {
            $this->rulesetId->xmlSerialize(true, $sxe->addChild('rulesetId'));
        }
        if (0 < count($this->rule)) {
            foreach ($this->rule as $rule) {
                $rule->xmlSerialize(true, $sxe->addChild('rule'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
