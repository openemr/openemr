<?php namespace HL7\FHIR\STU3\FHIRElement;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement;

/**
 * A description of a triggering event.
 * If the element is present, it must have a value for at least one of the defined elements, an @id referenced from the Narrative, or extensions
 */
class FHIRTriggerDefinition extends FHIRElement implements \JsonSerializable
{
    /**
     * The type of triggering event.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRTriggerType
     */
    public $type = null;

    /**
     * The name of the event (if this is a named-event trigger).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $eventName = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRTiming
     */
    public $eventTimingTiming = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $eventTimingReference = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDate
     */
    public $eventTimingDate = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDateTime
     */
    public $eventTimingDateTime = null;

    /**
     * The triggering data of the event (if this is a data trigger).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDataRequirement
     */
    public $eventData = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'TriggerDefinition';

    /**
     * The type of triggering event.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRTriggerType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * The type of triggering event.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRTriggerType $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * The name of the event (if this is a named-event trigger).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getEventName()
    {
        return $this->eventName;
    }

    /**
     * The name of the event (if this is a named-event trigger).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $eventName
     * @return $this
     */
    public function setEventName($eventName)
    {
        $this->eventName = $eventName;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRTiming
     */
    public function getEventTimingTiming()
    {
        return $this->eventTimingTiming;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRTiming $eventTimingTiming
     * @return $this
     */
    public function setEventTimingTiming($eventTimingTiming)
    {
        $this->eventTimingTiming = $eventTimingTiming;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getEventTimingReference()
    {
        return $this->eventTimingReference;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $eventTimingReference
     * @return $this
     */
    public function setEventTimingReference($eventTimingReference)
    {
        $this->eventTimingReference = $eventTimingReference;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDate
     */
    public function getEventTimingDate()
    {
        return $this->eventTimingDate;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDate $eventTimingDate
     * @return $this
     */
    public function setEventTimingDate($eventTimingDate)
    {
        $this->eventTimingDate = $eventTimingDate;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDateTime
     */
    public function getEventTimingDateTime()
    {
        return $this->eventTimingDateTime;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDateTime $eventTimingDateTime
     * @return $this
     */
    public function setEventTimingDateTime($eventTimingDateTime)
    {
        $this->eventTimingDateTime = $eventTimingDateTime;
        return $this;
    }

    /**
     * The triggering data of the event (if this is a data trigger).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDataRequirement
     */
    public function getEventData()
    {
        return $this->eventData;
    }

    /**
     * The triggering data of the event (if this is a data trigger).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDataRequirement $eventData
     * @return $this
     */
    public function setEventData($eventData)
    {
        $this->eventData = $eventData;
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
            if (isset($data['eventName'])) {
                $this->setEventName($data['eventName']);
            }
            if (isset($data['eventTimingTiming'])) {
                $this->setEventTimingTiming($data['eventTimingTiming']);
            }
            if (isset($data['eventTimingReference'])) {
                $this->setEventTimingReference($data['eventTimingReference']);
            }
            if (isset($data['eventTimingDate'])) {
                $this->setEventTimingDate($data['eventTimingDate']);
            }
            if (isset($data['eventTimingDateTime'])) {
                $this->setEventTimingDateTime($data['eventTimingDateTime']);
            }
            if (isset($data['eventData'])) {
                $this->setEventData($data['eventData']);
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
        if (isset($this->eventName)) {
            $json['eventName'] = $this->eventName;
        }
        if (isset($this->eventTimingTiming)) {
            $json['eventTimingTiming'] = $this->eventTimingTiming;
        }
        if (isset($this->eventTimingReference)) {
            $json['eventTimingReference'] = $this->eventTimingReference;
        }
        if (isset($this->eventTimingDate)) {
            $json['eventTimingDate'] = $this->eventTimingDate;
        }
        if (isset($this->eventTimingDateTime)) {
            $json['eventTimingDateTime'] = $this->eventTimingDateTime;
        }
        if (isset($this->eventData)) {
            $json['eventData'] = $this->eventData;
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
            $sxe = new \SimpleXMLElement('<TriggerDefinition xmlns="http://hl7.org/fhir"></TriggerDefinition>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (isset($this->eventName)) {
            $this->eventName->xmlSerialize(true, $sxe->addChild('eventName'));
        }
        if (isset($this->eventTimingTiming)) {
            $this->eventTimingTiming->xmlSerialize(true, $sxe->addChild('eventTimingTiming'));
        }
        if (isset($this->eventTimingReference)) {
            $this->eventTimingReference->xmlSerialize(true, $sxe->addChild('eventTimingReference'));
        }
        if (isset($this->eventTimingDate)) {
            $this->eventTimingDate->xmlSerialize(true, $sxe->addChild('eventTimingDate'));
        }
        if (isset($this->eventTimingDateTime)) {
            $this->eventTimingDateTime->xmlSerialize(true, $sxe->addChild('eventTimingDateTime'));
        }
        if (isset($this->eventData)) {
            $this->eventData->xmlSerialize(true, $sxe->addChild('eventData'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
