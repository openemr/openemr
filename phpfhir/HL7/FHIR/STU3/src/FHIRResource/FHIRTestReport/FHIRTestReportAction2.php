<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRTestReport;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * A summary of information based on the results of executing a TestScript.
 */
class FHIRTestReportAction2 extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * An operation would involve a REST request to a server.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRTestReport\FHIRTestReportOperation
     */
    public $operation = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'TestReport.Action2';

    /**
     * An operation would involve a REST request to a server.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRTestReport\FHIRTestReportOperation
     */
    public function getOperation()
    {
        return $this->operation;
    }

    /**
     * An operation would involve a REST request to a server.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRTestReport\FHIRTestReportOperation $operation
     * @return $this
     */
    public function setOperation($operation)
    {
        $this->operation = $operation;
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
            $sxe = new \SimpleXMLElement('<TestReportAction2 xmlns="http://hl7.org/fhir"></TestReportAction2>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->operation)) {
            $this->operation->xmlSerialize(true, $sxe->addChild('operation'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
