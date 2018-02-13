<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRCommunicationRequest;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * A request to convey information; e.g. the CDS system proposes that an alert be sent to a responsible provider, the CDS system proposes that the public health agency be notified about a reportable condition.
 */
class FHIRCommunicationRequestPayload extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $contentString = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRAttachment
     */
    public $contentAttachment = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $contentReference = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'CommunicationRequest.Payload';

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getContentString()
    {
        return $this->contentString;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $contentString
     * @return $this
     */
    public function setContentString($contentString)
    {
        $this->contentString = $contentString;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRAttachment
     */
    public function getContentAttachment()
    {
        return $this->contentAttachment;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRAttachment $contentAttachment
     * @return $this
     */
    public function setContentAttachment($contentAttachment)
    {
        $this->contentAttachment = $contentAttachment;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getContentReference()
    {
        return $this->contentReference;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $contentReference
     * @return $this
     */
    public function setContentReference($contentReference)
    {
        $this->contentReference = $contentReference;
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
            if (isset($data['contentString'])) {
                $this->setContentString($data['contentString']);
            }
            if (isset($data['contentAttachment'])) {
                $this->setContentAttachment($data['contentAttachment']);
            }
            if (isset($data['contentReference'])) {
                $this->setContentReference($data['contentReference']);
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
        if (isset($this->contentString)) {
            $json['contentString'] = $this->contentString;
        }
        if (isset($this->contentAttachment)) {
            $json['contentAttachment'] = $this->contentAttachment;
        }
        if (isset($this->contentReference)) {
            $json['contentReference'] = $this->contentReference;
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
            $sxe = new \SimpleXMLElement('<CommunicationRequestPayload xmlns="http://hl7.org/fhir"></CommunicationRequestPayload>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->contentString)) {
            $this->contentString->xmlSerialize(true, $sxe->addChild('contentString'));
        }
        if (isset($this->contentAttachment)) {
            $this->contentAttachment->xmlSerialize(true, $sxe->addChild('contentAttachment'));
        }
        if (isset($this->contentReference)) {
            $this->contentReference->xmlSerialize(true, $sxe->addChild('contentReference'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
