<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRStructureMap;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * A Map of relationships between 2 structures that can be used to transform data.
 */
class FHIRStructureMapRule extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Name of the rule for internal references.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRId
     */
    public $name = null;

    /**
     * Source inputs to the mapping.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRStructureMap\FHIRStructureMapSource[]
     */
    public $source = [];

    /**
     * Content to create because of this mapping rule.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRStructureMap\FHIRStructureMapTarget[]
     */
    public $target = [];

    /**
     * Rules contained in this rule.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRStructureMap\FHIRStructureMapRule[]
     */
    public $rule = [];

    /**
     * Which other rules to apply in the context of this rule.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRStructureMap\FHIRStructureMapDependent[]
     */
    public $dependent = [];

    /**
     * Documentation for this instance of data.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $documentation = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'StructureMap.Rule';

    /**
     * Name of the rule for internal references.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRId
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Name of the rule for internal references.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRId $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Source inputs to the mapping.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRStructureMap\FHIRStructureMapSource[]
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Source inputs to the mapping.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRStructureMap\FHIRStructureMapSource $source
     * @return $this
     */
    public function addSource($source)
    {
        $this->source[] = $source;
        return $this;
    }

    /**
     * Content to create because of this mapping rule.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRStructureMap\FHIRStructureMapTarget[]
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Content to create because of this mapping rule.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRStructureMap\FHIRStructureMapTarget $target
     * @return $this
     */
    public function addTarget($target)
    {
        $this->target[] = $target;
        return $this;
    }

    /**
     * Rules contained in this rule.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRStructureMap\FHIRStructureMapRule[]
     */
    public function getRule()
    {
        return $this->rule;
    }

    /**
     * Rules contained in this rule.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRStructureMap\FHIRStructureMapRule $rule
     * @return $this
     */
    public function addRule($rule)
    {
        $this->rule[] = $rule;
        return $this;
    }

    /**
     * Which other rules to apply in the context of this rule.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRStructureMap\FHIRStructureMapDependent[]
     */
    public function getDependent()
    {
        return $this->dependent;
    }

    /**
     * Which other rules to apply in the context of this rule.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRStructureMap\FHIRStructureMapDependent $dependent
     * @return $this
     */
    public function addDependent($dependent)
    {
        $this->dependent[] = $dependent;
        return $this;
    }

    /**
     * Documentation for this instance of data.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getDocumentation()
    {
        return $this->documentation;
    }

    /**
     * Documentation for this instance of data.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $documentation
     * @return $this
     */
    public function setDocumentation($documentation)
    {
        $this->documentation = $documentation;
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
            if (isset($data['name'])) {
                $this->setName($data['name']);
            }
            if (isset($data['source'])) {
                if (is_array($data['source'])) {
                    foreach ($data['source'] as $d) {
                        $this->addSource($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"source" must be array of objects or null, '.gettype($data['source']).' seen.');
                }
            }
            if (isset($data['target'])) {
                if (is_array($data['target'])) {
                    foreach ($data['target'] as $d) {
                        $this->addTarget($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"target" must be array of objects or null, '.gettype($data['target']).' seen.');
                }
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
            if (isset($data['dependent'])) {
                if (is_array($data['dependent'])) {
                    foreach ($data['dependent'] as $d) {
                        $this->addDependent($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"dependent" must be array of objects or null, '.gettype($data['dependent']).' seen.');
                }
            }
            if (isset($data['documentation'])) {
                $this->setDocumentation($data['documentation']);
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
        if (isset($this->name)) {
            $json['name'] = $this->name;
        }
        if (0 < count($this->source)) {
            $json['source'] = [];
            foreach ($this->source as $source) {
                $json['source'][] = $source;
            }
        }
        if (0 < count($this->target)) {
            $json['target'] = [];
            foreach ($this->target as $target) {
                $json['target'][] = $target;
            }
        }
        if (0 < count($this->rule)) {
            $json['rule'] = [];
            foreach ($this->rule as $rule) {
                $json['rule'][] = $rule;
            }
        }
        if (0 < count($this->dependent)) {
            $json['dependent'] = [];
            foreach ($this->dependent as $dependent) {
                $json['dependent'][] = $dependent;
            }
        }
        if (isset($this->documentation)) {
            $json['documentation'] = $this->documentation;
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
            $sxe = new \SimpleXMLElement('<StructureMapRule xmlns="http://hl7.org/fhir"></StructureMapRule>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->name)) {
            $this->name->xmlSerialize(true, $sxe->addChild('name'));
        }
        if (0 < count($this->source)) {
            foreach ($this->source as $source) {
                $source->xmlSerialize(true, $sxe->addChild('source'));
            }
        }
        if (0 < count($this->target)) {
            foreach ($this->target as $target) {
                $target->xmlSerialize(true, $sxe->addChild('target'));
            }
        }
        if (0 < count($this->rule)) {
            foreach ($this->rule as $rule) {
                $rule->xmlSerialize(true, $sxe->addChild('rule'));
            }
        }
        if (0 < count($this->dependent)) {
            foreach ($this->dependent as $dependent) {
                $dependent->xmlSerialize(true, $sxe->addChild('dependent'));
            }
        }
        if (isset($this->documentation)) {
            $this->documentation->xmlSerialize(true, $sxe->addChild('documentation'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
