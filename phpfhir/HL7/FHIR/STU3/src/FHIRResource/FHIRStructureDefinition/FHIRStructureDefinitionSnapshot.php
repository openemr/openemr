<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRStructureDefinition;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * A definition of a FHIR structure. This resource is used to describe the underlying resources, data types defined in FHIR, and also for describing extensions and constraints on resources and data types.
 */
class FHIRStructureDefinitionSnapshot extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Captures constraints on each element within the resource.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRElementDefinition[]
     */
    public $element = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'StructureDefinition.Snapshot';

    /**
     * Captures constraints on each element within the resource.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRElementDefinition[]
     */
    public function getElement()
    {
        return $this->element;
    }

    /**
     * Captures constraints on each element within the resource.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRElementDefinition $element
     * @return $this
     */
    public function addElement($element)
    {
        $this->element[] = $element;
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
            if (isset($data['element'])) {
                if (is_array($data['element'])) {
                    foreach ($data['element'] as $d) {
                        $this->addElement($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"element" must be array of objects or null, '.gettype($data['element']).' seen.');
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
        if (0 < count($this->element)) {
            $json['element'] = [];
            foreach ($this->element as $element) {
                $json['element'][] = $element;
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
            $sxe = new \SimpleXMLElement('<StructureDefinitionSnapshot xmlns="http://hl7.org/fhir"></StructureDefinitionSnapshot>');
        }
        parent::xmlSerialize(true, $sxe);
        if (0 < count($this->element)) {
            foreach ($this->element as $element) {
                $element->xmlSerialize(true, $sxe->addChild('element'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
