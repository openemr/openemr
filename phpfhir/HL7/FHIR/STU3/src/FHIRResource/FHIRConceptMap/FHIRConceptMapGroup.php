<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRConceptMap;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * A statement of relationships from one set of concepts to one or more other concepts - either code systems or data elements, or classes in class models.
 */
class FHIRConceptMapGroup extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * An absolute URI that identifies the Code System (if the source is a value set that crosses more than one code system).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public $source = null;

    /**
     * The specific version of the code system, as determined by the code system authority.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $sourceVersion = null;

    /**
     * An absolute URI that identifies the code system of the target code (if the target is a value set that cross code systems).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public $target = null;

    /**
     * The specific version of the code system, as determined by the code system authority.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $targetVersion = null;

    /**
     * Mappings for an individual concept in the source to one or more concepts in the target.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRConceptMap\FHIRConceptMapElement[]
     */
    public $element = [];

    /**
     * What to do when there is no match in the mappings in the group.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRConceptMap\FHIRConceptMapUnmapped
     */
    public $unmapped = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'ConceptMap.Group';

    /**
     * An absolute URI that identifies the Code System (if the source is a value set that crosses more than one code system).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * An absolute URI that identifies the Code System (if the source is a value set that crosses more than one code system).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRUri $source
     * @return $this
     */
    public function setSource($source)
    {
        $this->source = $source;
        return $this;
    }

    /**
     * The specific version of the code system, as determined by the code system authority.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getSourceVersion()
    {
        return $this->sourceVersion;
    }

    /**
     * The specific version of the code system, as determined by the code system authority.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $sourceVersion
     * @return $this
     */
    public function setSourceVersion($sourceVersion)
    {
        $this->sourceVersion = $sourceVersion;
        return $this;
    }

    /**
     * An absolute URI that identifies the code system of the target code (if the target is a value set that cross code systems).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * An absolute URI that identifies the code system of the target code (if the target is a value set that cross code systems).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRUri $target
     * @return $this
     */
    public function setTarget($target)
    {
        $this->target = $target;
        return $this;
    }

    /**
     * The specific version of the code system, as determined by the code system authority.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getTargetVersion()
    {
        return $this->targetVersion;
    }

    /**
     * The specific version of the code system, as determined by the code system authority.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $targetVersion
     * @return $this
     */
    public function setTargetVersion($targetVersion)
    {
        $this->targetVersion = $targetVersion;
        return $this;
    }

    /**
     * Mappings for an individual concept in the source to one or more concepts in the target.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRConceptMap\FHIRConceptMapElement[]
     */
    public function getElement()
    {
        return $this->element;
    }

    /**
     * Mappings for an individual concept in the source to one or more concepts in the target.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRConceptMap\FHIRConceptMapElement $element
     * @return $this
     */
    public function addElement($element)
    {
        $this->element[] = $element;
        return $this;
    }

    /**
     * What to do when there is no match in the mappings in the group.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRConceptMap\FHIRConceptMapUnmapped
     */
    public function getUnmapped()
    {
        return $this->unmapped;
    }

    /**
     * What to do when there is no match in the mappings in the group.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRConceptMap\FHIRConceptMapUnmapped $unmapped
     * @return $this
     */
    public function setUnmapped($unmapped)
    {
        $this->unmapped = $unmapped;
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
            if (isset($data['source'])) {
                $this->setSource($data['source']);
            }
            if (isset($data['sourceVersion'])) {
                $this->setSourceVersion($data['sourceVersion']);
            }
            if (isset($data['target'])) {
                $this->setTarget($data['target']);
            }
            if (isset($data['targetVersion'])) {
                $this->setTargetVersion($data['targetVersion']);
            }
            if (isset($data['element'])) {
                if (is_array($data['element'])) {
                    foreach ($data['element'] as $d) {
                        $this->addElement($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"element" must be array of objects or null, '.gettype($data['element']).' seen.');
                }
            }
            if (isset($data['unmapped'])) {
                $this->setUnmapped($data['unmapped']);
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
        if (isset($this->source)) {
            $json['source'] = $this->source;
        }
        if (isset($this->sourceVersion)) {
            $json['sourceVersion'] = $this->sourceVersion;
        }
        if (isset($this->target)) {
            $json['target'] = $this->target;
        }
        if (isset($this->targetVersion)) {
            $json['targetVersion'] = $this->targetVersion;
        }
        if (0 < count($this->element)) {
            $json['element'] = [];
            foreach ($this->element as $element) {
                $json['element'][] = $element;
            }
        }
        if (isset($this->unmapped)) {
            $json['unmapped'] = $this->unmapped;
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
            $sxe = new \SimpleXMLElement('<ConceptMapGroup xmlns="http://hl7.org/fhir"></ConceptMapGroup>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->source)) {
            $this->source->xmlSerialize(true, $sxe->addChild('source'));
        }
        if (isset($this->sourceVersion)) {
            $this->sourceVersion->xmlSerialize(true, $sxe->addChild('sourceVersion'));
        }
        if (isset($this->target)) {
            $this->target->xmlSerialize(true, $sxe->addChild('target'));
        }
        if (isset($this->targetVersion)) {
            $this->targetVersion->xmlSerialize(true, $sxe->addChild('targetVersion'));
        }
        if (0 < count($this->element)) {
            foreach ($this->element as $element) {
                $element->xmlSerialize(true, $sxe->addChild('element'));
            }
        }
        if (isset($this->unmapped)) {
            $this->unmapped->xmlSerialize(true, $sxe->addChild('unmapped'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
