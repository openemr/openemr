<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRCompartmentDefinition;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * A compartment definition that defines how resources are accessed on a server.
 */
class FHIRCompartmentDefinitionResource extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The name of a resource supported by the server.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRResourceType
     */
    public $code = null;

    /**
     * The name of a search parameter that represents the link to the compartment. More than one may be listed because a resource may be linked to a compartment in more than one way,.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString[]
     */
    public $param = [];

    /**
     * Additional documentation about the resource and compartment.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $documentation = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'CompartmentDefinition.Resource';

    /**
     * The name of a resource supported by the server.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRResourceType
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * The name of a resource supported by the server.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRResourceType $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * The name of a search parameter that represents the link to the compartment. More than one may be listed because a resource may be linked to a compartment in more than one way,.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString[]
     */
    public function getParam()
    {
        return $this->param;
    }

    /**
     * The name of a search parameter that represents the link to the compartment. More than one may be listed because a resource may be linked to a compartment in more than one way,.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $param
     * @return $this
     */
    public function addParam($param)
    {
        $this->param[] = $param;
        return $this;
    }

    /**
     * Additional documentation about the resource and compartment.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getDocumentation()
    {
        return $this->documentation;
    }

    /**
     * Additional documentation about the resource and compartment.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $documentation
     * @return $this
     */
    public function setDocumentation($documentation)
    {
        $this->documentation = $documentation;
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
            if (isset($data['code'])) {
                $this->setCode($data['code']);
            }
            if (isset($data['param'])) {
                if (is_array($data['param'])) {
                    foreach ($data['param'] as $d) {
                        $this->addParam($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"param" must be array of objects or null, '.gettype($data['param']).' seen.');
                }
            }
            if (isset($data['documentation'])) {
                $this->setDocumentation($data['documentation']);
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
        if (isset($this->code)) {
            $json['code'] = $this->code;
        }
        if (0 < count($this->param)) {
            $json['param'] = [];
            foreach ($this->param as $param) {
                $json['param'][] = $param;
            }
        }
        if (isset($this->documentation)) {
            $json['documentation'] = $this->documentation;
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
            $sxe = new \SimpleXMLElement('<CompartmentDefinitionResource xmlns="http://hl7.org/fhir"></CompartmentDefinitionResource>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->code)) {
            $this->code->xmlSerialize(true, $sxe->addChild('code'));
        }
        if (0 < count($this->param)) {
            foreach ($this->param as $param) {
                $param->xmlSerialize(true, $sxe->addChild('param'));
            }
        }
        if (isset($this->documentation)) {
            $this->documentation->xmlSerialize(true, $sxe->addChild('documentation'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
