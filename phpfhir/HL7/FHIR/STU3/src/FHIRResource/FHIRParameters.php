<?php namespace HL7\FHIR\STU3\FHIRResource;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRResource;

/**
 * This special resource type is used to represent an operation request and response (operations.html). It has no other use, and there is no RESTful endpoint associated with it.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRParameters extends FHIRResource implements \JsonSerializable
{
    /**
     * A parameter passed to or received from the operation.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRParameters\FHIRParametersParameter[]
     */
    public $parameter = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'Parameters';

    /**
     * A parameter passed to or received from the operation.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRParameters\FHIRParametersParameter[]
     */
    public function getParameter()
    {
        return $this->parameter;
    }

    /**
     * A parameter passed to or received from the operation.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRParameters\FHIRParametersParameter $parameter
     * @return $this
     */
    public function addParameter($parameter)
    {
        $this->parameter[] = $parameter;
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
            if (isset($data['parameter'])) {
                if (is_array($data['parameter'])) {
                    foreach ($data['parameter'] as $d) {
                        $this->addParameter($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"parameter" must be array of objects or null, '.gettype($data['parameter']).' seen.');
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
        $json['resourceType'] = $this->_fhirElementName;
        if (0 < count($this->parameter)) {
            $json['parameter'] = [];
            foreach ($this->parameter as $parameter) {
                $json['parameter'][] = $parameter;
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
            $sxe = new \SimpleXMLElement('<Parameters xmlns="http://hl7.org/fhir"></Parameters>');
        }
        parent::xmlSerialize(true, $sxe);
        if (0 < count($this->parameter)) {
            foreach ($this->parameter as $parameter) {
                $parameter->xmlSerialize(true, $sxe->addChild('parameter'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
