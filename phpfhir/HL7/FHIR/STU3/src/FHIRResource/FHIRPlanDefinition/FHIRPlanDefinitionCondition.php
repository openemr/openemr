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
class FHIRPlanDefinitionCondition extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The kind of condition.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRActionConditionKind
     */
    public $kind = null;

    /**
     * A brief, natural language description of the condition that effectively communicates the intended semantics.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $description = null;

    /**
     * The media type of the language for the expression.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $language = null;

    /**
     * An expression that returns true or false, indicating whether or not the condition is satisfied.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $expression = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'PlanDefinition.Condition';

    /**
     * The kind of condition.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRActionConditionKind
     */
    public function getKind()
    {
        return $this->kind;
    }

    /**
     * The kind of condition.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRActionConditionKind $kind
     * @return $this
     */
    public function setKind($kind)
    {
        $this->kind = $kind;
        return $this;
    }

    /**
     * A brief, natural language description of the condition that effectively communicates the intended semantics.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * A brief, natural language description of the condition that effectively communicates the intended semantics.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * The media type of the language for the expression.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * The media type of the language for the expression.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $language
     * @return $this
     */
    public function setLanguage($language)
    {
        $this->language = $language;
        return $this;
    }

    /**
     * An expression that returns true or false, indicating whether or not the condition is satisfied.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
     * An expression that returns true or false, indicating whether or not the condition is satisfied.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $expression
     * @return $this
     */
    public function setExpression($expression)
    {
        $this->expression = $expression;
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
            if (isset($data['kind'])) {
                $this->setKind($data['kind']);
            }
            if (isset($data['description'])) {
                $this->setDescription($data['description']);
            }
            if (isset($data['language'])) {
                $this->setLanguage($data['language']);
            }
            if (isset($data['expression'])) {
                $this->setExpression($data['expression']);
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
        if (isset($this->kind)) {
            $json['kind'] = $this->kind;
        }
        if (isset($this->description)) {
            $json['description'] = $this->description;
        }
        if (isset($this->language)) {
            $json['language'] = $this->language;
        }
        if (isset($this->expression)) {
            $json['expression'] = $this->expression;
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
            $sxe = new \SimpleXMLElement('<PlanDefinitionCondition xmlns="http://hl7.org/fhir"></PlanDefinitionCondition>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->kind)) {
            $this->kind->xmlSerialize(true, $sxe->addChild('kind'));
        }
        if (isset($this->description)) {
            $this->description->xmlSerialize(true, $sxe->addChild('description'));
        }
        if (isset($this->language)) {
            $this->language->xmlSerialize(true, $sxe->addChild('language'));
        }
        if (isset($this->expression)) {
            $this->expression->xmlSerialize(true, $sxe->addChild('expression'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
