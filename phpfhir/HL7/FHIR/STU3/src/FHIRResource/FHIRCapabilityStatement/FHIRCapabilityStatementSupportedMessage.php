<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRCapabilityStatement;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * A Capability Statement documents a set of capabilities (behaviors) of a FHIR Server that may be used as a statement of actual server functionality or a statement of required or desired server implementation.
 */
class FHIRCapabilityStatementSupportedMessage extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The mode of this event declaration - whether application is sender or receiver.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIREventCapabilityMode
     */
    public $mode = null;

    /**
     * Points to a message definition that identifies the messaging event, message structure, allowed responses, etc.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $definition = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'CapabilityStatement.SupportedMessage';

    /**
     * The mode of this event declaration - whether application is sender or receiver.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIREventCapabilityMode
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * The mode of this event declaration - whether application is sender or receiver.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIREventCapabilityMode $mode
     * @return $this
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
        return $this;
    }

    /**
     * Points to a message definition that identifies the messaging event, message structure, allowed responses, etc.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * Points to a message definition that identifies the messaging event, message structure, allowed responses, etc.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $definition
     * @return $this
     */
    public function setDefinition($definition)
    {
        $this->definition = $definition;
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
            if (isset($data['mode'])) {
                $this->setMode($data['mode']);
            }
            if (isset($data['definition'])) {
                $this->setDefinition($data['definition']);
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
        if (isset($this->mode)) {
            $json['mode'] = $this->mode;
        }
        if (isset($this->definition)) {
            $json['definition'] = $this->definition;
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
            $sxe = new \SimpleXMLElement('<CapabilityStatementSupportedMessage xmlns="http://hl7.org/fhir"></CapabilityStatementSupportedMessage>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->mode)) {
            $this->mode->xmlSerialize(true, $sxe->addChild('mode'));
        }
        if (isset($this->definition)) {
            $this->definition->xmlSerialize(true, $sxe->addChild('definition'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
