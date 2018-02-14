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
class FHIRTestReportOperation extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The result of this operation.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRTestReportActionResult
     */
    public $result = null;

    /**
     * An explanatory message associated with the result.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRMarkdown
     */
    public $message = null;

    /**
     * A link to further details on the result.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public $detail = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'TestReport.Operation';

    /**
     * The result of this operation.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRTestReportActionResult
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * The result of this operation.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRTestReportActionResult $result
     * @return $this
     */
    public function setResult($result)
    {
        $this->result = $result;
        return $this;
    }

    /**
     * An explanatory message associated with the result.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRMarkdown
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * An explanatory message associated with the result.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRMarkdown $message
     * @return $this
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * A link to further details on the result.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public function getDetail()
    {
        return $this->detail;
    }

    /**
     * A link to further details on the result.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRUri $detail
     * @return $this
     */
    public function setDetail($detail)
    {
        $this->detail = $detail;
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
            if (isset($data['result'])) {
                $this->setResult($data['result']);
            }
            if (isset($data['message'])) {
                $this->setMessage($data['message']);
            }
            if (isset($data['detail'])) {
                $this->setDetail($data['detail']);
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
        if (isset($this->result)) {
            $json['result'] = $this->result;
        }
        if (isset($this->message)) {
            $json['message'] = $this->message;
        }
        if (isset($this->detail)) {
            $json['detail'] = $this->detail;
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
            $sxe = new \SimpleXMLElement('<TestReportOperation xmlns="http://hl7.org/fhir"></TestReportOperation>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->result)) {
            $this->result->xmlSerialize(true, $sxe->addChild('result'));
        }
        if (isset($this->message)) {
            $this->message->xmlSerialize(true, $sxe->addChild('message'));
        }
        if (isset($this->detail)) {
            $this->detail->xmlSerialize(true, $sxe->addChild('detail'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
