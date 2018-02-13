<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRClaimResponse;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * This resource provides the adjudication details from the processing of a Claim resource.
 */
class FHIRClaimResponseError extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The sequence number of the line item submitted which contains the error. This value is omitted when the error is elsewhere.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRPositiveInt
     */
    public $sequenceLinkId = null;

    /**
     * The sequence number of the addition within the line item submitted which contains the error. This value is omitted when the error is not related to an Addition.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRPositiveInt
     */
    public $detailSequenceLinkId = null;

    /**
     * The sequence number of the addition within the line item submitted which contains the error. This value is omitted when the error is not related to an Addition.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRPositiveInt
     */
    public $subdetailSequenceLinkId = null;

    /**
     * An error code,from a specified code system, which details why the claim could not be adjudicated.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $code = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'ClaimResponse.Error';

    /**
     * The sequence number of the line item submitted which contains the error. This value is omitted when the error is elsewhere.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRPositiveInt
     */
    public function getSequenceLinkId()
    {
        return $this->sequenceLinkId;
    }

    /**
     * The sequence number of the line item submitted which contains the error. This value is omitted when the error is elsewhere.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRPositiveInt $sequenceLinkId
     * @return $this
     */
    public function setSequenceLinkId($sequenceLinkId)
    {
        $this->sequenceLinkId = $sequenceLinkId;
        return $this;
    }

    /**
     * The sequence number of the addition within the line item submitted which contains the error. This value is omitted when the error is not related to an Addition.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRPositiveInt
     */
    public function getDetailSequenceLinkId()
    {
        return $this->detailSequenceLinkId;
    }

    /**
     * The sequence number of the addition within the line item submitted which contains the error. This value is omitted when the error is not related to an Addition.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRPositiveInt $detailSequenceLinkId
     * @return $this
     */
    public function setDetailSequenceLinkId($detailSequenceLinkId)
    {
        $this->detailSequenceLinkId = $detailSequenceLinkId;
        return $this;
    }

    /**
     * The sequence number of the addition within the line item submitted which contains the error. This value is omitted when the error is not related to an Addition.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRPositiveInt
     */
    public function getSubdetailSequenceLinkId()
    {
        return $this->subdetailSequenceLinkId;
    }

    /**
     * The sequence number of the addition within the line item submitted which contains the error. This value is omitted when the error is not related to an Addition.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRPositiveInt $subdetailSequenceLinkId
     * @return $this
     */
    public function setSubdetailSequenceLinkId($subdetailSequenceLinkId)
    {
        $this->subdetailSequenceLinkId = $subdetailSequenceLinkId;
        return $this;
    }

    /**
     * An error code,from a specified code system, which details why the claim could not be adjudicated.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * An error code,from a specified code system, which details why the claim could not be adjudicated.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;
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
            if (isset($data['detailSequenceLinkId'])) {
                $this->setDetailSequenceLinkId($data['detailSequenceLinkId']);
            }
            if (isset($data['subdetailSequenceLinkId'])) {
                $this->setSubdetailSequenceLinkId($data['subdetailSequenceLinkId']);
            }
            if (isset($data['code'])) {
                $this->setCode($data['code']);
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
        if (isset($this->detailSequenceLinkId)) {
            $json['detailSequenceLinkId'] = $this->detailSequenceLinkId;
        }
        if (isset($this->subdetailSequenceLinkId)) {
            $json['subdetailSequenceLinkId'] = $this->subdetailSequenceLinkId;
        }
        if (isset($this->code)) {
            $json['code'] = $this->code;
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
            $sxe = new \SimpleXMLElement('<ClaimResponseError xmlns="http://hl7.org/fhir"></ClaimResponseError>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->sequenceLinkId)) {
            $this->sequenceLinkId->xmlSerialize(true, $sxe->addChild('sequenceLinkId'));
        }
        if (isset($this->detailSequenceLinkId)) {
            $this->detailSequenceLinkId->xmlSerialize(true, $sxe->addChild('detailSequenceLinkId'));
        }
        if (isset($this->subdetailSequenceLinkId)) {
            $this->subdetailSequenceLinkId->xmlSerialize(true, $sxe->addChild('subdetailSequenceLinkId'));
        }
        if (isset($this->code)) {
            $this->code->xmlSerialize(true, $sxe->addChild('code'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
