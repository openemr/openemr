<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRTestScript;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * A structured set of tests against a FHIR server implementation to determine compliance against the FHIR specification.
 */
class FHIRTestScriptAction extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The operation to perform.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRTestScript\FHIRTestScriptOperation
     */
    public $operation = null;

    /**
     * Evaluates the results of previous operations to determine if the server under test behaves appropriately.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRTestScript\FHIRTestScriptAssert
     */
    public $assert = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'TestScript.Action';

    /**
     * The operation to perform.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRTestScript\FHIRTestScriptOperation
     */
    public function getOperation()
    {
        return $this->operation;
    }

    /**
     * The operation to perform.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRTestScript\FHIRTestScriptOperation $operation
     * @return $this
     */
    public function setOperation($operation)
    {
        $this->operation = $operation;
        return $this;
    }

    /**
     * Evaluates the results of previous operations to determine if the server under test behaves appropriately.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRTestScript\FHIRTestScriptAssert
     */
    public function getAssert()
    {
        return $this->assert;
    }

    /**
     * Evaluates the results of previous operations to determine if the server under test behaves appropriately.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRTestScript\FHIRTestScriptAssert $assert
     * @return $this
     */
    public function setAssert($assert)
    {
        $this->assert = $assert;
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
            if (isset($data['operation'])) {
                $this->setOperation($data['operation']);
            }
            if (isset($data['assert'])) {
                $this->setAssert($data['assert']);
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
        if (isset($this->operation)) {
            $json['operation'] = $this->operation;
        }
        if (isset($this->assert)) {
            $json['assert'] = $this->assert;
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
            $sxe = new \SimpleXMLElement('<TestScriptAction xmlns="http://hl7.org/fhir"></TestScriptAction>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->operation)) {
            $this->operation->xmlSerialize(true, $sxe->addChild('operation'));
        }
        if (isset($this->assert)) {
            $this->assert->xmlSerialize(true, $sxe->addChild('assert'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
