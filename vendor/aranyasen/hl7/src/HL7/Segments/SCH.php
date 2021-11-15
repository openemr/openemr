<?php

namespace Aranyasen\HL7\Segments;

use Aranyasen\HL7\Segment;

/**
 * SCH segment class
 * Ref: https://corepointhealth.com/resource-center/hl7-resources/hl7-siu-message
 * http://hl7-definition.caristix.com:9010/HL7%20v2.3.1/segment/SCH
 */
class SCH extends Segment
{
    public function __construct(array $fields = null)
    {
        parent::__construct('SCH', $fields);
    }

    public function setPlacerAppointmentID($value, int $position = 1): bool
    {
         return $this->setField($position, $value);
    }

    public function setFillerAppointmentID($value, int $position = 2): bool
    {
         return $this->setField($position, $value);
    }

    public function setOccurrenceNumber($value, int $position = 3): bool
    {
         return $this->setField($position, $value);
    }

    public function setPlacerGroupNumber($value, int $position = 4): bool
    {
         return $this->setField($position, $value);
    }

    public function setScheduleID($value, int $position = 5): bool
    {
         return $this->setField($position, $value);
    }

    public function setEventReason($value, int $position = 6): bool
    {
         return $this->setField($position, $value);
    }

    public function setAppointmentReason($value, int $position = 7): bool
    {
         return $this->setField($position, $value);
    }

    public function setAppointmentType($value, int $position = 8): bool
    {
         return $this->setField($position, $value);
    }

    public function setAppointmentDuration($value, int $position = 9): bool
    {
         return $this->setField($position, $value);
    }

    public function setAppointmentDurationUnits($value, int $position = 10): bool
    {
         return $this->setField($position, $value);
    }

    public function setAppointmentTimingQuantity($value, int $position = 11): bool
    {
         return $this->setField($position, $value);
    }

    public function setPlacerContactPerson($value, int $position = 12): bool
    {
         return $this->setField($position, $value);
    }

    public function setPlacerContactPhoneNumber($value, int $position = 13): bool
    {
         return $this->setField($position, $value);
    }

    public function setPlacerContactAddress($value, int $position = 14): bool
    {
         return $this->setField($position, $value);
    }

    public function setPlacerContactLocation($value, int $position = 15): bool
    {
         return $this->setField($position, $value);
    }

    public function setFillerContactPerson($value, int $position = 16): bool
    {
         return $this->setField($position, $value);
    }

    public function setFillerContactPhoneNumber($value, int $position = 17): bool
    {
         return $this->setField($position, $value);
    }

    public function setFillerContactAddress($value, int $position = 18): bool
    {
         return $this->setField($position, $value);
    }

    public function setFillerContactLocation($value, int $position = 19): bool
    {
         return $this->setField($position, $value);
    }

    public function setEnteredbyPerson($value, int $position = 20): bool
    {
         return $this->setField($position, $value);
    }

    public function setEnteredbyPhoneNumber($value, int $position = 21): bool
    {
         return $this->setField($position, $value);
    }

    public function setEnteredbyLocation($value, int $position = 22): bool
    {
         return $this->setField($position, $value);
    }

    public function setParentPlacerAppointmentID($value, int $position = 23): bool
    {
         return $this->setField($position, $value);
    }

    public function setParentFillerAppointmentID($value, int $position = 24): bool
    {
         return $this->setField($position, $value);
    }

    public function setFillerStatusCode($value, int $position = 25): bool
    {
         return $this->setField($position, $value);
    }

    public function getPlacerAppointmentID(int $position = 1)
    {
        return $this->getField($position);
    }

    public function getFillerAppointmentID(int $position = 2)
    {
        return $this->getField($position);
    }

    public function getOccurrenceNumber(int $position = 3)
    {
        return $this->getField($position);
    }

    public function getPlacerGroupNumber(int $position = 4)
    {
        return $this->getField($position);
    }

    public function getScheduleID(int $position = 5)
    {
        return $this->getField($position);
    }

    public function getEventReason(int $position = 6)
    {
        return $this->getField($position);
    }

    public function getAppointmentReason(int $position = 7)
    {
        return $this->getField($position);
    }

    public function getAppointmentType(int $position = 8)
    {
        return $this->getField($position);
    }

    public function getAppointmentDuration(int $position = 9)
    {
        return $this->getField($position);
    }

    public function getAppointmentDurationUnits(int $position = 10)
    {
        return $this->getField($position);
    }

    public function getAppointmentTimingQuantity(int $position = 11)
    {
        return $this->getField($position);
    }

    public function getPlacerContactPerson(int $position = 12)
    {
        return $this->getField($position);
    }

    public function getPlacerContactPhoneNumber(int $position = 13)
    {
        return $this->getField($position);
    }

    public function getPlacerContactAddress(int $position = 14)
    {
        return $this->getField($position);
    }

    public function getPlacerContactLocation(int $position = 15)
    {
        return $this->getField($position);
    }

    public function getFillerContactPerson(int $position = 16)
    {
        return $this->getField($position);
    }

    public function getFillerContactPhoneNumber(int $position = 17)
    {
        return $this->getField($position);
    }

    public function getFillerContactAddress(int $position = 18)
    {
        return $this->getField($position);
    }

    public function getFillerContactLocation(int $position = 19)
    {
        return $this->getField($position);
    }

    public function getEnteredbyPerson(int $position = 20)
    {
        return $this->getField($position);
    }

    public function getEnteredbyPhoneNumber(int $position = 21)
    {
        return $this->getField($position);
    }

    public function getEnteredbyLocation(int $position = 22)
    {
        return $this->getField($position);
    }

    public function getParentPlacerAppointmentID(int $position = 23)
    {
        return $this->getField($position);
    }

    public function getParentFillerAppointmentID(int $position = 24)
    {
        return $this->getField($position);
    }

    public function getFillerStatusCode(int $position = 25)
    {
        return $this->getField($position);
    }
}
