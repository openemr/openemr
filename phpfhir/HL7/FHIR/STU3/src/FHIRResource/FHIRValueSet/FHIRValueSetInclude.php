<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRValueSet;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * A value set specifies a set of codes drawn from one or more code systems.
 */
class FHIRValueSetInclude extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * An absolute URI which is the code system from which the selected codes come from.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public $system = null;

    /**
     * The version of the code system that the codes are selected from.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $version = null;

    /**
     * Specifies a concept to be included or excluded.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRValueSet\FHIRValueSetConcept[]
     */
    public $concept = [];

    /**
     * Select concepts by specify a matching criteria based on the properties (including relationships) defined by the system. If multiple filters are specified, they SHALL all be true.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRValueSet\FHIRValueSetFilter[]
     */
    public $filter = [];

    /**
     * Selects concepts found in this value set. This is an absolute URI that is a reference to ValueSet.url.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRUri[]
     */
    public $valueSet = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'ValueSet.Include';

    /**
     * An absolute URI which is the code system from which the selected codes come from.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public function getSystem()
    {
        return $this->system;
    }

    /**
     * An absolute URI which is the code system from which the selected codes come from.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRUri $system
     * @return $this
     */
    public function setSystem($system)
    {
        $this->system = $system;
        return $this;
    }

    /**
     * The version of the code system that the codes are selected from.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * The version of the code system that the codes are selected from.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $version
     * @return $this
     */
    public function setVersion($version)
    {
        $this->version = $version;
        return $this;
    }

    /**
     * Specifies a concept to be included or excluded.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRValueSet\FHIRValueSetConcept[]
     */
    public function getConcept()
    {
        return $this->concept;
    }

    /**
     * Specifies a concept to be included or excluded.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRValueSet\FHIRValueSetConcept $concept
     * @return $this
     */
    public function addConcept($concept)
    {
        $this->concept[] = $concept;
        return $this;
    }

    /**
     * Select concepts by specify a matching criteria based on the properties (including relationships) defined by the system. If multiple filters are specified, they SHALL all be true.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRValueSet\FHIRValueSetFilter[]
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * Select concepts by specify a matching criteria based on the properties (including relationships) defined by the system. If multiple filters are specified, they SHALL all be true.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRValueSet\FHIRValueSetFilter $filter
     * @return $this
     */
    public function addFilter($filter)
    {
        $this->filter[] = $filter;
        return $this;
    }

    /**
     * Selects concepts found in this value set. This is an absolute URI that is a reference to ValueSet.url.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRUri[]
     */
    public function getValueSet()
    {
        return $this->valueSet;
    }

    /**
     * Selects concepts found in this value set. This is an absolute URI that is a reference to ValueSet.url.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRUri $valueSet
     * @return $this
     */
    public function addValueSet($valueSet)
    {
        $this->valueSet[] = $valueSet;
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
            if (isset($data['system'])) {
                $this->setSystem($data['system']);
            }
            if (isset($data['version'])) {
                $this->setVersion($data['version']);
            }
            if (isset($data['concept'])) {
                if (is_array($data['concept'])) {
                    foreach ($data['concept'] as $d) {
                        $this->addConcept($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"concept" must be array of objects or null, '.gettype($data['concept']).' seen.');
                }
            }
            if (isset($data['filter'])) {
                if (is_array($data['filter'])) {
                    foreach ($data['filter'] as $d) {
                        $this->addFilter($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"filter" must be array of objects or null, '.gettype($data['filter']).' seen.');
                }
            }
            if (isset($data['valueSet'])) {
                if (is_array($data['valueSet'])) {
                    foreach ($data['valueSet'] as $d) {
                        $this->addValueSet($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"valueSet" must be array of objects or null, '.gettype($data['valueSet']).' seen.');
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
        if (isset($this->system)) {
            $json['system'] = $this->system;
        }
        if (isset($this->version)) {
            $json['version'] = $this->version;
        }
        if (0 < count($this->concept)) {
            $json['concept'] = [];
            foreach ($this->concept as $concept) {
                $json['concept'][] = $concept;
            }
        }
        if (0 < count($this->filter)) {
            $json['filter'] = [];
            foreach ($this->filter as $filter) {
                $json['filter'][] = $filter;
            }
        }
        if (0 < count($this->valueSet)) {
            $json['valueSet'] = [];
            foreach ($this->valueSet as $valueSet) {
                $json['valueSet'][] = $valueSet;
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
            $sxe = new \SimpleXMLElement('<ValueSetInclude xmlns="http://hl7.org/fhir"></ValueSetInclude>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->system)) {
            $this->system->xmlSerialize(true, $sxe->addChild('system'));
        }
        if (isset($this->version)) {
            $this->version->xmlSerialize(true, $sxe->addChild('version'));
        }
        if (0 < count($this->concept)) {
            foreach ($this->concept as $concept) {
                $concept->xmlSerialize(true, $sxe->addChild('concept'));
            }
        }
        if (0 < count($this->filter)) {
            foreach ($this->filter as $filter) {
                $filter->xmlSerialize(true, $sxe->addChild('filter'));
            }
        }
        if (0 < count($this->valueSet)) {
            foreach ($this->valueSet as $valueSet) {
                $valueSet->xmlSerialize(true, $sxe->addChild('valueSet'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
