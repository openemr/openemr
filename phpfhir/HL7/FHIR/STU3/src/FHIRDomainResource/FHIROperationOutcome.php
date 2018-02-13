<?php namespace HL7\FHIR\STU3\FHIRDomainResource;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 *
* Class creation date: February 10th, 2018 *
 */

use HL7\FHIR\STU3\FHIRResource\FHIRDomainResource;

/**
 * A collection of error, warning or information messages that result from a system action.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIROperationOutcome extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * An error, warning or information message that results from a system action.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIROperationOutcome\FHIROperationOutcomeIssue[]
     */
    public $issue = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'OperationOutcome';

    /**
     * An error, warning or information message that results from a system action.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIROperationOutcome\FHIROperationOutcomeIssue[]
     */
    public function getIssue()
    {
        return $this->issue;
    }

    /**
     * An error, warning or information message that results from a system action.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIROperationOutcome\FHIROperationOutcomeIssue $issue
     * @return $this
     */
    public function addIssue($issue)
    {
        $this->issue[] = $issue;
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
            if (isset($data['issue'])) {
                if (is_array($data['issue'])) {
                    foreach ($data['issue'] as $d) {
                        $this->addIssue($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"issue" must be array of objects or null, '.gettype($data['issue']).' seen.');
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
        if (0 < count($this->issue)) {
            $json['issue'] = [];
            foreach ($this->issue as $issue) {
                $json['issue'][] = $issue;
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
            $sxe = new \SimpleXMLElement('<OperationOutcome xmlns="http://hl7.org/fhir"></OperationOutcome>');
        }
        parent::xmlSerialize(true, $sxe);
        if (0 < count($this->issue)) {
            foreach ($this->issue as $issue) {
                $issue->xmlSerialize(true, $sxe->addChild('issue'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
