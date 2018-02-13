<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRImplementationGuide;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * A set of rules of how FHIR is used to solve a particular problem. This resource is used to gather all the parts of an implementation guide into a logical whole and to publish a computable definition of all the parts.
 */
class FHIRImplementationGuideDependency extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * How the dependency is represented when the guide is published.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRGuideDependencyType
     */
    public $type = null;

    /**
     * Where the dependency is located.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public $uri = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'ImplementationGuide.Dependency';

    /**
     * How the dependency is represented when the guide is published.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRGuideDependencyType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * How the dependency is represented when the guide is published.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRGuideDependencyType $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Where the dependency is located.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Where the dependency is located.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRUri $uri
     * @return $this
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
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
            if (isset($data['type'])) {
                $this->setType($data['type']);
            }
            if (isset($data['uri'])) {
                $this->setUri($data['uri']);
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
        if (isset($this->type)) {
            $json['type'] = $this->type;
        }
        if (isset($this->uri)) {
            $json['uri'] = $this->uri;
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
            $sxe = new \SimpleXMLElement('<ImplementationGuideDependency xmlns="http://hl7.org/fhir"></ImplementationGuideDependency>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (isset($this->uri)) {
            $this->uri->xmlSerialize(true, $sxe->addChild('uri'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
