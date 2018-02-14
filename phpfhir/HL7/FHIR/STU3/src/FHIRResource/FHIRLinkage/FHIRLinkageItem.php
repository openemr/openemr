<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRLinkage;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * Identifies two or more records (resource instances) that are referring to the same real-world "occurrence".
 */
class FHIRLinkageItem extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Distinguishes which item is "source of truth" (if any) and which items are no longer considered to be current representations.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRLinkageType
     */
    public $type = null;

    /**
     * The resource instance being linked as part of the group.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $resource = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Linkage.Item';

    /**
     * Distinguishes which item is "source of truth" (if any) and which items are no longer considered to be current representations.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRLinkageType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Distinguishes which item is "source of truth" (if any) and which items are no longer considered to be current representations.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRLinkageType $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * The resource instance being linked as part of the group.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * The resource instance being linked as part of the group.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $resource
     * @return $this
     */
    public function setResource($resource)
    {
        $this->resource = $resource;
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
            if (isset($data['resource'])) {
                $this->setResource($data['resource']);
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
        if (isset($this->resource)) {
            $json['resource'] = $this->resource;
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
            $sxe = new \SimpleXMLElement('<LinkageItem xmlns="http://hl7.org/fhir"></LinkageItem>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (isset($this->resource)) {
            $this->resource->xmlSerialize(true, $sxe->addChild('resource'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
