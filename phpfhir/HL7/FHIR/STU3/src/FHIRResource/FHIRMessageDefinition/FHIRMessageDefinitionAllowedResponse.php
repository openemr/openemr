<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRMessageDefinition;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 *
* Class creation date: February 10th, 2018 *
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * Defines the characteristics of a message that can be shared between systems, including the type of event that initiates the message, the content to be transmitted and what response(s), if any, are permitted.
 */
class FHIRMessageDefinitionAllowedResponse extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * A reference to the message definition that must be adhered to by this supported response.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $message = null;

    /**
     * Provides a description of the circumstances in which this response should be used (as opposed to one of the alternative responses).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRMarkdown
     */
    public $situation = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'MessageDefinition.AllowedResponse';

    /**
     * A reference to the message definition that must be adhered to by this supported response.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * A reference to the message definition that must be adhered to by this supported response.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $message
     * @return $this
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * Provides a description of the circumstances in which this response should be used (as opposed to one of the alternative responses).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRMarkdown
     */
    public function getSituation()
    {
        return $this->situation;
    }

    /**
     * Provides a description of the circumstances in which this response should be used (as opposed to one of the alternative responses).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRMarkdown $situation
     * @return $this
     */
    public function setSituation($situation)
    {
        $this->situation = $situation;
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
            if (isset($data['message'])) {
                $this->setMessage($data['message']);
            }
            if (isset($data['situation'])) {
                $this->setSituation($data['situation']);
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
        if (isset($this->message)) {
            $json['message'] = $this->message;
        }
        if (isset($this->situation)) {
            $json['situation'] = $this->situation;
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
            $sxe = new \SimpleXMLElement('<MessageDefinitionAllowedResponse xmlns="http://hl7.org/fhir"></MessageDefinitionAllowedResponse>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->message)) {
            $this->message->xmlSerialize(true, $sxe->addChild('message'));
        }
        if (isset($this->situation)) {
            $this->situation->xmlSerialize(true, $sxe->addChild('situation'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
