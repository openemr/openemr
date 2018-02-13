<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRTestReport;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 *
 * Class creation date: February 10th, 2018
 *
 *
 *
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * A summary of information based on the results of executing a TestScript.
 */
class FHIRTestReportAction extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The operation performed.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRTestReport\FHIRTestReportOperation
     */
    public $operation = null;

    /**
     * The results of the assertion performed on the previous operations.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRTestReport\FHIRTestReportAssert
     */
    public $assert = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'TestReport.Action';

    /**
     * The operation performed.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRTestReport\FHIRTestReportOperation
     */
    public function getOperation()
    {
        return $this->operation;
    }

    /**
     * The operation performed.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRTestReport\FHIRTestReportOperation $operation
     * @return $this
     */
    public function setOperation($operation)
    {
        $this->operation = $operation;
        return $this;
    }

    /**
     * The results of the assertion performed on the previous operations.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRTestReport\FHIRTestReportAssert
     */
    public function getAssert()
    {
        return $this->assert;
    }

    /**
     * The results of the assertion performed on the previous operations.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRTestReport\FHIRTestReportAssert $assert
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
            $sxe = new \SimpleXMLElement('<TestReportAction xmlns="http://hl7.org/fhir"></TestReportAction>');
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
