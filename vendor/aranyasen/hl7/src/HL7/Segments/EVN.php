<?php

namespace Aranyasen\HL7\Segments;

use Aranyasen\HL7\Segment;

/**
 * EVN segment class
 * Ref: http://hl7-definition.caristix.com:9010/HL7%20v2.3.1/segment/EVN
 *      https://corepointhealth.com/resource-center/hl7-resources/hl7-evn-event-type-segment
 */
class EVN extends Segment
{
    public function __construct(array $fields = null)
    {
        parent::__construct('EVN', $fields);
    }

    public function setEventTypeCode($value, int $position = 1): bool
    {
        return $this->setField($position, $value);
    }

    public function setRecordedDateTime($value, int $position = 2): bool
    {
        return $this->setField($position, $value);
    }

    public function setDateTimePlannedEvent($value, int $position = 3): bool
    {
        return $this->setField($position, $value);
    }

    public function setEventReasonCode($value, int $position = 4): bool
    {
        return $this->setField($position, $value);
    }

    public function setOperatorID($value, int $position = 5): bool
    {
        return $this->setField($position, $value);
    }

    public function setEventOccurred($value, int $position = 6): bool
    {
        return $this->setField($position, $value);
    }

    public function getEventTypeCode(int $position = 1)
    {
        return $this->getField($position);
    }

    public function getRecordedDateTime(int $position = 2)
    {
        return $this->getField($position);
    }

    public function getDateTimePlannedEvent(int $position = 3)
    {
        return $this->getField($position);
    }

    public function getEventReasonCode(int $position = 4)
    {
        return $this->getField($position);
    }

    public function getOperatorID(int $position = 5)
    {
        return $this->getField($position);
    }

    public function getEventOccurred(int $position = 6)
    {
        return $this->getField($position);
    }
}
