<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRCapabilityStatement;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 *
* Class creation date: February 10th, 2018 *
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * A Capability Statement documents a set of capabilities (behaviors) of a FHIR Server that may be used as a statement of actual server functionality or a statement of required or desired server implementation.
 */
class FHIRCapabilityStatementMessaging extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * An endpoint (network accessible address) to which messages and/or replies are to be sent.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementEndpoint[]
     */
    public $endpoint = [];

    /**
     * Length if the receiver's reliable messaging cache in minutes (if a receiver) or how long the cache length on the receiver should be (if a sender).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRUnsignedInt
     */
    public $reliableCache = null;

    /**
     * Documentation about the system's messaging capabilities for this endpoint not otherwise documented by the capability statement.  For example, the process for becoming an authorized messaging exchange partner.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $documentation = null;

    /**
     * References to message definitions for messages this system can send or receive.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementSupportedMessage[]
     */
    public $supportedMessage = [];

    /**
     * A description of the solution's support for an event at this end-point.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementEvent[]
     */
    public $event = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'CapabilityStatement.Messaging';

    /**
     * An endpoint (network accessible address) to which messages and/or replies are to be sent.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementEndpoint[]
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * An endpoint (network accessible address) to which messages and/or replies are to be sent.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementEndpoint $endpoint
     * @return $this
     */
    public function addEndpoint($endpoint)
    {
        $this->endpoint[] = $endpoint;
        return $this;
    }

    /**
     * Length if the receiver's reliable messaging cache in minutes (if a receiver) or how long the cache length on the receiver should be (if a sender).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRUnsignedInt
     */
    public function getReliableCache()
    {
        return $this->reliableCache;
    }

    /**
     * Length if the receiver's reliable messaging cache in minutes (if a receiver) or how long the cache length on the receiver should be (if a sender).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRUnsignedInt $reliableCache
     * @return $this
     */
    public function setReliableCache($reliableCache)
    {
        $this->reliableCache = $reliableCache;
        return $this;
    }

    /**
     * Documentation about the system's messaging capabilities for this endpoint not otherwise documented by the capability statement.  For example, the process for becoming an authorized messaging exchange partner.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getDocumentation()
    {
        return $this->documentation;
    }

    /**
     * Documentation about the system's messaging capabilities for this endpoint not otherwise documented by the capability statement.  For example, the process for becoming an authorized messaging exchange partner.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $documentation
     * @return $this
     */
    public function setDocumentation($documentation)
    {
        $this->documentation = $documentation;
        return $this;
    }

    /**
     * References to message definitions for messages this system can send or receive.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementSupportedMessage[]
     */
    public function getSupportedMessage()
    {
        return $this->supportedMessage;
    }

    /**
     * References to message definitions for messages this system can send or receive.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementSupportedMessage $supportedMessage
     * @return $this
     */
    public function addSupportedMessage($supportedMessage)
    {
        $this->supportedMessage[] = $supportedMessage;
        return $this;
    }

    /**
     * A description of the solution's support for an event at this end-point.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementEvent[]
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * A description of the solution's support for an event at this end-point.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementEvent $event
     * @return $this
     */
    public function addEvent($event)
    {
        $this->event[] = $event;
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
            if (isset($data['endpoint'])) {
                if (is_array($data['endpoint'])) {
                    foreach ($data['endpoint'] as $d) {
                        $this->addEndpoint($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"endpoint" must be array of objects or null, '.gettype($data['endpoint']).' seen.');
                }
            }
            if (isset($data['reliableCache'])) {
                $this->setReliableCache($data['reliableCache']);
            }
            if (isset($data['documentation'])) {
                $this->setDocumentation($data['documentation']);
            }
            if (isset($data['supportedMessage'])) {
                if (is_array($data['supportedMessage'])) {
                    foreach ($data['supportedMessage'] as $d) {
                        $this->addSupportedMessage($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"supportedMessage" must be array of objects or null, '.gettype($data['supportedMessage']).' seen.');
                }
            }
            if (isset($data['event'])) {
                if (is_array($data['event'])) {
                    foreach ($data['event'] as $d) {
                        $this->addEvent($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"event" must be array of objects or null, '.gettype($data['event']).' seen.');
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
        if (0 < count($this->endpoint)) {
            $json['endpoint'] = [];
            foreach ($this->endpoint as $endpoint) {
                $json['endpoint'][] = $endpoint;
            }
        }
        if (isset($this->reliableCache)) {
            $json['reliableCache'] = $this->reliableCache;
        }
        if (isset($this->documentation)) {
            $json['documentation'] = $this->documentation;
        }
        if (0 < count($this->supportedMessage)) {
            $json['supportedMessage'] = [];
            foreach ($this->supportedMessage as $supportedMessage) {
                $json['supportedMessage'][] = $supportedMessage;
            }
        }
        if (0 < count($this->event)) {
            $json['event'] = [];
            foreach ($this->event as $event) {
                $json['event'][] = $event;
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
            $sxe = new \SimpleXMLElement('<CapabilityStatementMessaging xmlns="http://hl7.org/fhir"></CapabilityStatementMessaging>');
        }
        parent::xmlSerialize(true, $sxe);
        if (0 < count($this->endpoint)) {
            foreach ($this->endpoint as $endpoint) {
                $endpoint->xmlSerialize(true, $sxe->addChild('endpoint'));
            }
        }
        if (isset($this->reliableCache)) {
            $this->reliableCache->xmlSerialize(true, $sxe->addChild('reliableCache'));
        }
        if (isset($this->documentation)) {
            $this->documentation->xmlSerialize(true, $sxe->addChild('documentation'));
        }
        if (0 < count($this->supportedMessage)) {
            foreach ($this->supportedMessage as $supportedMessage) {
                $supportedMessage->xmlSerialize(true, $sxe->addChild('supportedMessage'));
            }
        }
        if (0 < count($this->event)) {
            foreach ($this->event as $event) {
                $event->xmlSerialize(true, $sxe->addChild('event'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
