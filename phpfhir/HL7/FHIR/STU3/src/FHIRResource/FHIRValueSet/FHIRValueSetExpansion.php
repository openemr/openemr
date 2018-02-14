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
class FHIRValueSetExpansion extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * An identifier that uniquely identifies this expansion of the valueset. Systems may re-use the same identifier as long as the expansion and the definition remain the same, but are not required to do so.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public $identifier = null;

    /**
     * The time at which the expansion was produced by the expanding system.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDateTime
     */
    public $timestamp = null;

    /**
     * The total number of concepts in the expansion. If the number of concept nodes in this resource is less than the stated number, then the server can return more using the offset parameter.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRInteger
     */
    public $total = null;

    /**
     * If paging is being used, the offset at which this resource starts.  I.e. this resource is a partial view into the expansion. If paging is not being used, this element SHALL not be present.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRInteger
     */
    public $offset = null;

    /**
     * A parameter that controlled the expansion process. These parameters may be used by users of expanded value sets to check whether the expansion is suitable for a particular purpose, or to pick the correct expansion.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRValueSet\FHIRValueSetParameter[]
     */
    public $parameter = [];

    /**
     * The codes that are contained in the value set expansion.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRValueSet\FHIRValueSetContains[]
     */
    public $contains = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'ValueSet.Expansion';

    /**
     * An identifier that uniquely identifies this expansion of the valueset. Systems may re-use the same identifier as long as the expansion and the definition remain the same, but are not required to do so.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * An identifier that uniquely identifies this expansion of the valueset. Systems may re-use the same identifier as long as the expansion and the definition remain the same, but are not required to do so.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRUri $identifier
     * @return $this
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * The time at which the expansion was produced by the expanding system.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDateTime
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * The time at which the expansion was produced by the expanding system.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDateTime $timestamp
     * @return $this
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
        return $this;
    }

    /**
     * The total number of concepts in the expansion. If the number of concept nodes in this resource is less than the stated number, then the server can return more using the offset parameter.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRInteger
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * The total number of concepts in the expansion. If the number of concept nodes in this resource is less than the stated number, then the server can return more using the offset parameter.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRInteger $total
     * @return $this
     */
    public function setTotal($total)
    {
        $this->total = $total;
        return $this;
    }

    /**
     * If paging is being used, the offset at which this resource starts.  I.e. this resource is a partial view into the expansion. If paging is not being used, this element SHALL not be present.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRInteger
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * If paging is being used, the offset at which this resource starts.  I.e. this resource is a partial view into the expansion. If paging is not being used, this element SHALL not be present.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRInteger $offset
     * @return $this
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * A parameter that controlled the expansion process. These parameters may be used by users of expanded value sets to check whether the expansion is suitable for a particular purpose, or to pick the correct expansion.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRValueSet\FHIRValueSetParameter[]
     */
    public function getParameter()
    {
        return $this->parameter;
    }

    /**
     * A parameter that controlled the expansion process. These parameters may be used by users of expanded value sets to check whether the expansion is suitable for a particular purpose, or to pick the correct expansion.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRValueSet\FHIRValueSetParameter $parameter
     * @return $this
     */
    public function addParameter($parameter)
    {
        $this->parameter[] = $parameter;
        return $this;
    }

    /**
     * The codes that are contained in the value set expansion.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRValueSet\FHIRValueSetContains[]
     */
    public function getContains()
    {
        return $this->contains;
    }

    /**
     * The codes that are contained in the value set expansion.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRValueSet\FHIRValueSetContains $contains
     * @return $this
     */
    public function addContains($contains)
    {
        $this->contains[] = $contains;
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
            if (isset($data['identifier'])) {
                $this->setIdentifier($data['identifier']);
            }
            if (isset($data['timestamp'])) {
                $this->setTimestamp($data['timestamp']);
            }
            if (isset($data['total'])) {
                $this->setTotal($data['total']);
            }
            if (isset($data['offset'])) {
                $this->setOffset($data['offset']);
            }
            if (isset($data['parameter'])) {
                if (is_array($data['parameter'])) {
                    foreach ($data['parameter'] as $d) {
                        $this->addParameter($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"parameter" must be array of objects or null, '.gettype($data['parameter']).' seen.');
                }
            }
            if (isset($data['contains'])) {
                if (is_array($data['contains'])) {
                    foreach ($data['contains'] as $d) {
                        $this->addContains($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"contains" must be array of objects or null, '.gettype($data['contains']).' seen.');
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
        if (isset($this->identifier)) {
            $json['identifier'] = $this->identifier;
        }
        if (isset($this->timestamp)) {
            $json['timestamp'] = $this->timestamp;
        }
        if (isset($this->total)) {
            $json['total'] = $this->total;
        }
        if (isset($this->offset)) {
            $json['offset'] = $this->offset;
        }
        if (0 < count($this->parameter)) {
            $json['parameter'] = [];
            foreach ($this->parameter as $parameter) {
                $json['parameter'][] = $parameter;
            }
        }
        if (0 < count($this->contains)) {
            $json['contains'] = [];
            foreach ($this->contains as $contains) {
                $json['contains'][] = $contains;
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
            $sxe = new \SimpleXMLElement('<ValueSetExpansion xmlns="http://hl7.org/fhir"></ValueSetExpansion>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->identifier)) {
            $this->identifier->xmlSerialize(true, $sxe->addChild('identifier'));
        }
        if (isset($this->timestamp)) {
            $this->timestamp->xmlSerialize(true, $sxe->addChild('timestamp'));
        }
        if (isset($this->total)) {
            $this->total->xmlSerialize(true, $sxe->addChild('total'));
        }
        if (isset($this->offset)) {
            $this->offset->xmlSerialize(true, $sxe->addChild('offset'));
        }
        if (0 < count($this->parameter)) {
            foreach ($this->parameter as $parameter) {
                $parameter->xmlSerialize(true, $sxe->addChild('parameter'));
            }
        }
        if (0 < count($this->contains)) {
            foreach ($this->contains as $contains) {
                $contains->xmlSerialize(true, $sxe->addChild('contains'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
