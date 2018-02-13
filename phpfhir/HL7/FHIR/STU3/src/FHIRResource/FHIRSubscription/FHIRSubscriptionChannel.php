<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRSubscription;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * The subscription resource is used to define a push based subscription from a server to another system. Once a subscription is registered with the server, the server checks every resource that is created or updated, and if the resource matches the given criteria, it sends a message on the defined "channel" so that another system is able to take an appropriate action.
 */
class FHIRSubscriptionChannel extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The type of channel to send notifications on.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRSubscriptionChannelType
     */
    public $type = null;

    /**
     * The uri that describes the actual end-point to send messages to.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public $endpoint = null;

    /**
     * The mime type to send the payload in - either application/fhir+xml, or application/fhir+json. If the payload is not present, then there is no payload in the notification, just a notification.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $payload = null;

    /**
     * Additional headers / information to send as part of the notification.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString[]
     */
    public $header = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'Subscription.Channel';

    /**
     * The type of channel to send notifications on.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRSubscriptionChannelType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * The type of channel to send notifications on.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRSubscriptionChannelType $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * The uri that describes the actual end-point to send messages to.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * The uri that describes the actual end-point to send messages to.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRUri $endpoint
     * @return $this
     */
    public function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;
        return $this;
    }

    /**
     * The mime type to send the payload in - either application/fhir+xml, or application/fhir+json. If the payload is not present, then there is no payload in the notification, just a notification.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * The mime type to send the payload in - either application/fhir+xml, or application/fhir+json. If the payload is not present, then there is no payload in the notification, just a notification.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $payload
     * @return $this
     */
    public function setPayload($payload)
    {
        $this->payload = $payload;
        return $this;
    }

    /**
     * Additional headers / information to send as part of the notification.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString[]
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * Additional headers / information to send as part of the notification.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $header
     * @return $this
     */
    public function addHeader($header)
    {
        $this->header[] = $header;
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
            if (isset($data['type'])) {
                $this->setType($data['type']);
            }
            if (isset($data['endpoint'])) {
                $this->setEndpoint($data['endpoint']);
            }
            if (isset($data['payload'])) {
                $this->setPayload($data['payload']);
            }
            if (isset($data['header'])) {
                if (is_array($data['header'])) {
                    foreach ($data['header'] as $d) {
                        $this->addHeader($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"header" must be array of objects or null, '.gettype($data['header']).' seen.');
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
        if (isset($this->type)) {
            $json['type'] = $this->type;
        }
        if (isset($this->endpoint)) {
            $json['endpoint'] = $this->endpoint;
        }
        if (isset($this->payload)) {
            $json['payload'] = $this->payload;
        }
        if (0 < count($this->header)) {
            $json['header'] = [];
            foreach ($this->header as $header) {
                $json['header'][] = $header;
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
            $sxe = new \SimpleXMLElement('<SubscriptionChannel xmlns="http://hl7.org/fhir"></SubscriptionChannel>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (isset($this->endpoint)) {
            $this->endpoint->xmlSerialize(true, $sxe->addChild('endpoint'));
        }
        if (isset($this->payload)) {
            $this->payload->xmlSerialize(true, $sxe->addChild('payload'));
        }
        if (0 < count($this->header)) {
            foreach ($this->header as $header) {
                $header->xmlSerialize(true, $sxe->addChild('header'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
