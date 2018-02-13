<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRElementDefinition;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement;

/**
 * Captures constraints on each element within the resource, profile, or extension.
 * If the element is present, it must have a value for at least one of the defined elements, an @id referenced from the Narrative, or extensions
 */
class FHIRElementDefinitionSlicing extends FHIRElement implements \JsonSerializable
{
    /**
     * Designates which child elements are used to discriminate between the slices when processing an instance. If one or more discriminators are provided, the value of the child elements in the instance data SHALL completely distinguish which slice the element in the resource matches based on the allowed values for those elements in each of the slices.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRElementDefinition\FHIRElementDefinitionDiscriminator[]
     */
    public $discriminator = [];

    /**
     * A human-readable text description of how the slicing works. If there is no discriminator, this is required to be present to provide whatever information is possible about how the slices can be differentiated.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $description = null;

    /**
     * If the matching elements have to occur in the same order as defined in the profile.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public $ordered = null;

    /**
     * Whether additional slices are allowed or not. When the slices are ordered, profile authors can also say that additional slices are only allowed at the end.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRSlicingRules
     */
    public $rules = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'ElementDefinition.Slicing';

    /**
     * Designates which child elements are used to discriminate between the slices when processing an instance. If one or more discriminators are provided, the value of the child elements in the instance data SHALL completely distinguish which slice the element in the resource matches based on the allowed values for those elements in each of the slices.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRElementDefinition\FHIRElementDefinitionDiscriminator[]
     */
    public function getDiscriminator()
    {
        return $this->discriminator;
    }

    /**
     * Designates which child elements are used to discriminate between the slices when processing an instance. If one or more discriminators are provided, the value of the child elements in the instance data SHALL completely distinguish which slice the element in the resource matches based on the allowed values for those elements in each of the slices.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRElementDefinition\FHIRElementDefinitionDiscriminator $discriminator
     * @return $this
     */
    public function addDiscriminator($discriminator)
    {
        $this->discriminator[] = $discriminator;
        return $this;
    }

    /**
     * A human-readable text description of how the slicing works. If there is no discriminator, this is required to be present to provide whatever information is possible about how the slices can be differentiated.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * A human-readable text description of how the slicing works. If there is no discriminator, this is required to be present to provide whatever information is possible about how the slices can be differentiated.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * If the matching elements have to occur in the same order as defined in the profile.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public function getOrdered()
    {
        return $this->ordered;
    }

    /**
     * If the matching elements have to occur in the same order as defined in the profile.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRBoolean $ordered
     * @return $this
     */
    public function setOrdered($ordered)
    {
        $this->ordered = $ordered;
        return $this;
    }

    /**
     * Whether additional slices are allowed or not. When the slices are ordered, profile authors can also say that additional slices are only allowed at the end.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRSlicingRules
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * Whether additional slices are allowed or not. When the slices are ordered, profile authors can also say that additional slices are only allowed at the end.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRSlicingRules $rules
     * @return $this
     */
    public function setRules($rules)
    {
        $this->rules = $rules;
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
            if (isset($data['discriminator'])) {
                if (is_array($data['discriminator'])) {
                    foreach ($data['discriminator'] as $d) {
                        $this->addDiscriminator($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"discriminator" must be array of objects or null, '.gettype($data['discriminator']).' seen.');
                }
            }
            if (isset($data['description'])) {
                $this->setDescription($data['description']);
            }
            if (isset($data['ordered'])) {
                $this->setOrdered($data['ordered']);
            }
            if (isset($data['rules'])) {
                $this->setRules($data['rules']);
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
        if (0 < count($this->discriminator)) {
            $json['discriminator'] = [];
            foreach ($this->discriminator as $discriminator) {
                $json['discriminator'][] = $discriminator;
            }
        }
        if (isset($this->description)) {
            $json['description'] = $this->description;
        }
        if (isset($this->ordered)) {
            $json['ordered'] = $this->ordered;
        }
        if (isset($this->rules)) {
            $json['rules'] = $this->rules;
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
            $sxe = new \SimpleXMLElement('<ElementDefinitionSlicing xmlns="http://hl7.org/fhir"></ElementDefinitionSlicing>');
        }
        parent::xmlSerialize(true, $sxe);
        if (0 < count($this->discriminator)) {
            foreach ($this->discriminator as $discriminator) {
                $discriminator->xmlSerialize(true, $sxe->addChild('discriminator'));
            }
        }
        if (isset($this->description)) {
            $this->description->xmlSerialize(true, $sxe->addChild('description'));
        }
        if (isset($this->ordered)) {
            $this->ordered->xmlSerialize(true, $sxe->addChild('ordered'));
        }
        if (isset($this->rules)) {
            $this->rules->xmlSerialize(true, $sxe->addChild('rules'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
