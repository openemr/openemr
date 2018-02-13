<?php namespace HL7\FHIR\STU3\FHIRResource\FHIROperationDefinition;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * A formal computable definition of an operation (on the RESTful interface) or a named query (using the search interaction).
 */
class FHIROperationDefinitionOverload extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Name of parameter to include in overload.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString[]
     */
    public $parameterName = [];

    /**
     * Comments to go on overload.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $comment = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'OperationDefinition.Overload';

    /**
     * Name of parameter to include in overload.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString[]
     */
    public function getParameterName()
    {
        return $this->parameterName;
    }

    /**
     * Name of parameter to include in overload.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $parameterName
     * @return $this
     */
    public function addParameterName($parameterName)
    {
        $this->parameterName[] = $parameterName;
        return $this;
    }

    /**
     * Comments to go on overload.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Comments to go on overload.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $comment
     * @return $this
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
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
            if (isset($data['parameterName'])) {
                if (is_array($data['parameterName'])) {
                    foreach ($data['parameterName'] as $d) {
                        $this->addParameterName($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"parameterName" must be array of objects or null, '.gettype($data['parameterName']).' seen.');
                }
            }
            if (isset($data['comment'])) {
                $this->setComment($data['comment']);
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
        if (0 < count($this->parameterName)) {
            $json['parameterName'] = [];
            foreach ($this->parameterName as $parameterName) {
                $json['parameterName'][] = $parameterName;
            }
        }
        if (isset($this->comment)) {
            $json['comment'] = $this->comment;
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
            $sxe = new \SimpleXMLElement('<OperationDefinitionOverload xmlns="http://hl7.org/fhir"></OperationDefinitionOverload>');
        }
        parent::xmlSerialize(true, $sxe);
        if (0 < count($this->parameterName)) {
            foreach ($this->parameterName as $parameterName) {
                $parameterName->xmlSerialize(true, $sxe->addChild('parameterName'));
            }
        }
        if (isset($this->comment)) {
            $this->comment->xmlSerialize(true, $sxe->addChild('comment'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
