<?php namespace HL7\FHIR\STU3\FHIRDomainResource;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRResource\FHIRDomainResource;

/**
 * The subscription resource is used to define a push based subscription from a server to another system. Once a subscription is registered with the server, the server checks every resource that is created or updated, and if the resource matches the given criteria, it sends a message on the defined "channel" so that another system is able to take an appropriate action.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRSubscription extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * The status of the subscription, which marks the server state for managing the subscription.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRSubscriptionStatus
     */
    public $status = null;

    /**
     * Contact details for a human to contact about the subscription. The primary use of this for system administrator troubleshooting.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRContactPoint[]
     */
    public $contact = [];

    /**
     * The time for the server to turn the subscription off.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRInstant
     */
    public $end = null;

    /**
     * A description of why this subscription is defined.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $reason = null;

    /**
     * The rules that the server should use to determine when to generate notifications for this subscription.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $criteria = null;

    /**
     * A record of the last error that occurred when the server processed a notification.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $error = null;

    /**
     * Details where to send notifications when resources are received that meet the criteria.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRSubscription\FHIRSubscriptionChannel
     */
    public $channel = null;

    /**
     * A tag to add to any resource that matches the criteria, after the subscription is processed.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCoding[]
     */
    public $tag = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'Subscription';

    /**
     * The status of the subscription, which marks the server state for managing the subscription.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRSubscriptionStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * The status of the subscription, which marks the server state for managing the subscription.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRSubscriptionStatus $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Contact details for a human to contact about the subscription. The primary use of this for system administrator troubleshooting.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRContactPoint[]
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * Contact details for a human to contact about the subscription. The primary use of this for system administrator troubleshooting.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRContactPoint $contact
     * @return $this
     */
    public function addContact($contact)
    {
        $this->contact[] = $contact;
        return $this;
    }

    /**
     * The time for the server to turn the subscription off.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRInstant
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * The time for the server to turn the subscription off.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRInstant $end
     * @return $this
     */
    public function setEnd($end)
    {
        $this->end = $end;
        return $this;
    }

    /**
     * A description of why this subscription is defined.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * A description of why this subscription is defined.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $reason
     * @return $this
     */
    public function setReason($reason)
    {
        $this->reason = $reason;
        return $this;
    }

    /**
     * The rules that the server should use to determine when to generate notifications for this subscription.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getCriteria()
    {
        return $this->criteria;
    }

    /**
     * The rules that the server should use to determine when to generate notifications for this subscription.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $criteria
     * @return $this
     */
    public function setCriteria($criteria)
    {
        $this->criteria = $criteria;
        return $this;
    }

    /**
     * A record of the last error that occurred when the server processed a notification.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * A record of the last error that occurred when the server processed a notification.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $error
     * @return $this
     */
    public function setError($error)
    {
        $this->error = $error;
        return $this;
    }

    /**
     * Details where to send notifications when resources are received that meet the criteria.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRSubscription\FHIRSubscriptionChannel
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * Details where to send notifications when resources are received that meet the criteria.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRSubscription\FHIRSubscriptionChannel $channel
     * @return $this
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;
        return $this;
    }

    /**
     * A tag to add to any resource that matches the criteria, after the subscription is processed.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCoding[]
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * A tag to add to any resource that matches the criteria, after the subscription is processed.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCoding $tag
     * @return $this
     */
    public function addTag($tag)
    {
        $this->tag[] = $tag;
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
            if (isset($data['status'])) {
                $this->setStatus($data['status']);
            }
            if (isset($data['contact'])) {
                if (is_array($data['contact'])) {
                    foreach ($data['contact'] as $d) {
                        $this->addContact($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"contact" must be array of objects or null, '.gettype($data['contact']).' seen.');
                }
            }
            if (isset($data['end'])) {
                $this->setEnd($data['end']);
            }
            if (isset($data['reason'])) {
                $this->setReason($data['reason']);
            }
            if (isset($data['criteria'])) {
                $this->setCriteria($data['criteria']);
            }
            if (isset($data['error'])) {
                $this->setError($data['error']);
            }
            if (isset($data['channel'])) {
                $this->setChannel($data['channel']);
            }
            if (isset($data['tag'])) {
                if (is_array($data['tag'])) {
                    foreach ($data['tag'] as $d) {
                        $this->addTag($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"tag" must be array of objects or null, '.gettype($data['tag']).' seen.');
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
        if (isset($this->status)) {
            $json['status'] = $this->status;
        }
        if (0 < count($this->contact)) {
            $json['contact'] = [];
            foreach ($this->contact as $contact) {
                $json['contact'][] = $contact;
            }
        }
        if (isset($this->end)) {
            $json['end'] = $this->end;
        }
        if (isset($this->reason)) {
            $json['reason'] = $this->reason;
        }
        if (isset($this->criteria)) {
            $json['criteria'] = $this->criteria;
        }
        if (isset($this->error)) {
            $json['error'] = $this->error;
        }
        if (isset($this->channel)) {
            $json['channel'] = $this->channel;
        }
        if (0 < count($this->tag)) {
            $json['tag'] = [];
            foreach ($this->tag as $tag) {
                $json['tag'][] = $tag;
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
            $sxe = new \SimpleXMLElement('<Subscription xmlns="http://hl7.org/fhir"></Subscription>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->status)) {
            $this->status->xmlSerialize(true, $sxe->addChild('status'));
        }
        if (0 < count($this->contact)) {
            foreach ($this->contact as $contact) {
                $contact->xmlSerialize(true, $sxe->addChild('contact'));
            }
        }
        if (isset($this->end)) {
            $this->end->xmlSerialize(true, $sxe->addChild('end'));
        }
        if (isset($this->reason)) {
            $this->reason->xmlSerialize(true, $sxe->addChild('reason'));
        }
        if (isset($this->criteria)) {
            $this->criteria->xmlSerialize(true, $sxe->addChild('criteria'));
        }
        if (isset($this->error)) {
            $this->error->xmlSerialize(true, $sxe->addChild('error'));
        }
        if (isset($this->channel)) {
            $this->channel->xmlSerialize(true, $sxe->addChild('channel'));
        }
        if (0 < count($this->tag)) {
            foreach ($this->tag as $tag) {
                $tag->xmlSerialize(true, $sxe->addChild('tag'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
