<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRGraphDefinition;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * A formal computable definition of a graph of resources - that is, a coherent set of resources that form a graph by following references. The Graph Definition resource defines a set and makes rules about the set.
 */
class FHIRGraphDefinitionTarget extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Type of resource this link refers to.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRResourceType
     */
    public $type = null;

    /**
     * Profile for the target resource.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public $profile = null;

    /**
     * Compartment Consistency Rules.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRGraphDefinition\FHIRGraphDefinitionCompartment[]
     */
    public $compartment = [];

    /**
     * Additional links from target resource.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRGraphDefinition\FHIRGraphDefinitionLink[]
     */
    public $link = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'GraphDefinition.Target';

    /**
     * Type of resource this link refers to.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRResourceType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Type of resource this link refers to.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRResourceType $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Profile for the target resource.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * Profile for the target resource.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRUri $profile
     * @return $this
     */
    public function setProfile($profile)
    {
        $this->profile = $profile;
        return $this;
    }

    /**
     * Compartment Consistency Rules.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRGraphDefinition\FHIRGraphDefinitionCompartment[]
     */
    public function getCompartment()
    {
        return $this->compartment;
    }

    /**
     * Compartment Consistency Rules.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRGraphDefinition\FHIRGraphDefinitionCompartment $compartment
     * @return $this
     */
    public function addCompartment($compartment)
    {
        $this->compartment[] = $compartment;
        return $this;
    }

    /**
     * Additional links from target resource.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRGraphDefinition\FHIRGraphDefinitionLink[]
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Additional links from target resource.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRGraphDefinition\FHIRGraphDefinitionLink $link
     * @return $this
     */
    public function addLink($link)
    {
        $this->link[] = $link;
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
            if (isset($data['profile'])) {
                $this->setProfile($data['profile']);
            }
            if (isset($data['compartment'])) {
                if (is_array($data['compartment'])) {
                    foreach ($data['compartment'] as $d) {
                        $this->addCompartment($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"compartment" must be array of objects or null, '.gettype($data['compartment']).' seen.');
                }
            }
            if (isset($data['link'])) {
                if (is_array($data['link'])) {
                    foreach ($data['link'] as $d) {
                        $this->addLink($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"link" must be array of objects or null, '.gettype($data['link']).' seen.');
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
        if (isset($this->type)) {
            $json['type'] = $this->type;
        }
        if (isset($this->profile)) {
            $json['profile'] = $this->profile;
        }
        if (0 < count($this->compartment)) {
            $json['compartment'] = [];
            foreach ($this->compartment as $compartment) {
                $json['compartment'][] = $compartment;
            }
        }
        if (0 < count($this->link)) {
            $json['link'] = [];
            foreach ($this->link as $link) {
                $json['link'][] = $link;
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
            $sxe = new \SimpleXMLElement('<GraphDefinitionTarget xmlns="http://hl7.org/fhir"></GraphDefinitionTarget>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (isset($this->profile)) {
            $this->profile->xmlSerialize(true, $sxe->addChild('profile'));
        }
        if (0 < count($this->compartment)) {
            foreach ($this->compartment as $compartment) {
                $compartment->xmlSerialize(true, $sxe->addChild('compartment'));
            }
        }
        if (0 < count($this->link)) {
            foreach ($this->link as $link) {
                $link->xmlSerialize(true, $sxe->addChild('link'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
