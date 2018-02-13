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
class FHIRStructureMapGroup extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * A unique name for the group for the convenience of human readers.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRId
     */
    public $name = null;

    /**
     * Another group that this group adds rules to.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRId
     */
    public $extends = null;

    /**
     * If this is the default rule set to apply for thie source type, or this combination of types.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRStructureMapGroupTypeMode
     */
    public $typeMode = null;

    /**
     * Additional supporting documentation that explains the purpose of the group and the types of mappings within it.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $documentation = null;

    /**
     * A name assigned to an instance of data. The instance must be provided when the mapping is invoked.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRStructureMap\FHIRStructureMapInput[]
     */
    public $input = [];

    /**
     * Transform Rule from source to target.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRStructureMap\FHIRStructureMapRule[]
     */
    public $rule = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'StructureMap.Group';

    /**
     * A unique name for the group for the convenience of human readers.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRId
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * A unique name for the group for the convenience of human readers.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRId $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Another group that this group adds rules to.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRId
     */
    public function getExtends()
    {
        return $this->extends;
    }

    /**
     * Another group that this group adds rules to.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRId $extends
     * @return $this
     */
    public function setExtends($extends)
    {
        $this->extends = $extends;
        return $this;
    }

    /**
     * If this is the default rule set to apply for thie source type, or this combination of types.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRStructureMapGroupTypeMode
     */
    public function getTypeMode()
    {
        return $this->typeMode;
    }

    /**
     * If this is the default rule set to apply for thie source type, or this combination of types.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRStructureMapGroupTypeMode $typeMode
     * @return $this
     */
    public function setTypeMode($typeMode)
    {
        $this->typeMode = $typeMode;
        return $this;
    }

    /**
     * Additional supporting documentation that explains the purpose of the group and the types of mappings within it.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getDocumentation()
    {
        return $this->documentation;
    }

    /**
     * Additional supporting documentation that explains the purpose of the group and the types of mappings within it.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $documentation
     * @return $this
     */
    public function setDocumentation($documentation)
    {
        $this->documentation = $documentation;
        return $this;
    }

    /**
     * A name assigned to an instance of data. The instance must be provided when the mapping is invoked.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRStructureMap\FHIRStructureMapInput[]
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * A name assigned to an instance of data. The instance must be provided when the mapping is invoked.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRStructureMap\FHIRStructureMapInput $input
     * @return $this
     */
    public function addInput($input)
    {
        $this->input[] = $input;
        return $this;
    }

    /**
     * Transform Rule from source to target.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRStructureMap\FHIRStructureMapRule[]
     */
    public function getRule()
    {
        return $this->rule;
    }

    /**
     * Transform Rule from source to target.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRStructureMap\FHIRStructureMapRule $rule
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
            if (isset($data['name'])) {
                $this->setName($data['name']);
            }
            if (isset($data['extends'])) {
                $this->setExtends($data['extends']);
            }
            if (isset($data['typeMode'])) {
                $this->setTypeMode($data['typeMode']);
            }
            if (isset($data['documentation'])) {
                $this->setDocumentation($data['documentation']);
            }
            if (isset($data['input'])) {
                if (is_array($data['input'])) {
                    foreach ($data['input'] as $d) {
                        $this->addInput($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"input" must be array of objects or null, '.gettype($data['input']).' seen.');
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
        if (isset($this->extends)) {
            $json['extends'] = $this->extends;
        }
        if (isset($this->typeMode)) {
            $json['typeMode'] = $this->typeMode;
        }
        if (isset($this->documentation)) {
            $json['documentation'] = $this->documentation;
        }
        if (0 < count($this->input)) {
            $json['input'] = [];
            foreach ($this->input as $input) {
                $json['input'][] = $input;
            }
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
            $sxe = new \SimpleXMLElement('<StructureMapGroup xmlns="http://hl7.org/fhir"></StructureMapGroup>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->name)) {
            $this->name->xmlSerialize(true, $sxe->addChild('name'));
        }
        if (isset($this->extends)) {
            $this->extends->xmlSerialize(true, $sxe->addChild('extends'));
        }
        if (isset($this->typeMode)) {
            $this->typeMode->xmlSerialize(true, $sxe->addChild('typeMode'));
        }
        if (isset($this->documentation)) {
            $this->documentation->xmlSerialize(true, $sxe->addChild('documentation'));
        }
        if (0 < count($this->input)) {
            foreach ($this->input as $input) {
                $input->xmlSerialize(true, $sxe->addChild('input'));
            }
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
