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
class FHIRStructureMapDependent extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Name of a rule or group to apply.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRId
     */
    public $name = null;

    /**
     * Variable to pass to the rule or group.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString[]
     */
    public $variable = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'StructureMap.Dependent';

    /**
     * Name of a rule or group to apply.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRId
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Name of a rule or group to apply.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRId $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Variable to pass to the rule or group.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString[]
     */
    public function getVariable()
    {
        return $this->variable;
    }

    /**
     * Variable to pass to the rule or group.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $variable
     * @return $this
     */
    public function addVariable($variable)
    {
        $this->variable[] = $variable;
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
            if (isset($data['variable'])) {
                if (is_array($data['variable'])) {
                    foreach ($data['variable'] as $d) {
                        $this->addVariable($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"variable" must be array of objects or null, '.gettype($data['variable']).' seen.');
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
        if (0 < count($this->variable)) {
            $json['variable'] = [];
            foreach ($this->variable as $variable) {
                $json['variable'][] = $variable;
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
            $sxe = new \SimpleXMLElement('<StructureMapDependent xmlns="http://hl7.org/fhir"></StructureMapDependent>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->name)) {
            $this->name->xmlSerialize(true, $sxe->addChild('name'));
        }
        if (0 < count($this->variable)) {
            foreach ($this->variable as $variable) {
                $variable->xmlSerialize(true, $sxe->addChild('variable'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
