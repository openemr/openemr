<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRProcessRequest;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 *
* Class creation date: February 10th, 2018 *
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * This resource provides the target, request and response, and action details for an action to be performed by the target on or about existing resources.
 */
class FHIRProcessRequestItem extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * A service line number.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRInteger
     */
    public $sequenceLinkId = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'ProcessRequest.Item';

    /**
     * A service line number.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRInteger
     */
    public function getSequenceLinkId()
    {
        return $this->sequenceLinkId;
    }

    /**
     * A service line number.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRInteger $sequenceLinkId
     * @return $this
     */
    public function setSequenceLinkId($sequenceLinkId)
    {
        $this->sequenceLinkId = $sequenceLinkId;
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
            if (isset($data['sequenceLinkId'])) {
                $this->setSequenceLinkId($data['sequenceLinkId']);
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
        if (isset($this->sequenceLinkId)) {
            $json['sequenceLinkId'] = $this->sequenceLinkId;
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
            $sxe = new \SimpleXMLElement('<ProcessRequestItem xmlns="http://hl7.org/fhir"></ProcessRequestItem>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->sequenceLinkId)) {
            $this->sequenceLinkId->xmlSerialize(true, $sxe->addChild('sequenceLinkId'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
