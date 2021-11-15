<?php

namespace Aranyasen\HL7\Segments;

use Aranyasen\HL7\Segment;

/**
 * AIP segment class
 *
 * AIP: Appointment Information - Personnel Resource
 * The AIP segment contains information about the personnel types that can be scheduled. Personnel included in a
 * transaction using this segment are assumed to be controlled by a schedule on a schedule filler application.
 * Personnel not controlled by a schedule are not identified on a schedule request using this segment. The kinds of
 * personnel described on this segment include any healthcare provider in the institution controlled by a schedule (for
 * example: technicians, physicians, nurses, surgeons, anesthesiologists, or CRNAs).


 * Ref: http://hl7-definition.caristix.com:9010/Default.aspx?version=HL7+v2.5.1&segment=AIP
 */
class AIP extends Segment
{
    /**
     * Index of this segment. Incremented for every new segment of this class created
     * @var int
     */
    protected static $setId = 1;

    public function __construct(array $fields = null)
    {
        parent::__construct('AIP', $fields);
        $this->setID($this::$setId++);
    }

    public function __destruct()
    {
        $this->setID($this::$setId--);
    }

    /**
     * Reset index of this segment
     * @param int $index
     */
    public static function resetIndex(int $index = 1): void
    {
        self::$setId = $index;
    }

    public function setID(int $value, int $position = 1): bool
    {
        return $this->setField($position, $value);
    }

    public function setSegmentActionCode($value, int $position = 2): bool
    {
        return $this->setField($value, $position);
    }

    public function setPersonnelResourceID($value, int $position = 3): bool
    {
        return $this->setField($value, $position);
    }

    public function setResourceType($value, int $position = 4): bool
    {
        return $this->setField($value, $position);
    }

    public function setResourceGroup($value, int $position = 5): bool
    {
        return $this->setField($value, $position);
    }

    public function setStartDateTime($value, int $position = 6): bool
    {
        return $this->setField($value, $position);
    }

    public function setStartDateTimeOffset($value, int $position = 7): bool
    {
        return $this->setField($value, $position);
    }

    public function setStartDateTimeOffsetUnits($value, int $position = 8): bool
    {
        return $this->setField($value, $position);
    }

    public function setDuration($value, int $position = 9): bool
    {
        return $this->setField($value, $position);
    }

    public function setDurationUnits($value, int $position = 10): bool
    {
        return $this->setField($value, $position);
    }

    public function setAllowSubstitutionCode($value, int $position = 11): bool
    {
        return $this->setField($value, $position);
    }

    public function setFillerStatusCode($value, int $position = 12): bool
    {
        return $this->setField($value, $position);
    }

    public function getID(int $position = 1)
    {
        return $this->getField($position);
    }

    public function getSegmentActionCode(int $position = 2)
    {
        return $this->getField($position);
    }

    public function getPersonnelResourceID(int $position = 3)
    {
        return $this->getField($position);
    }

    public function getResourceType(int $position = 4)
    {
        return $this->getField($position);
    }

    public function getResourceGroup(int $position = 5)
    {
        return $this->getField($position);
    }

    public function getStartDateTime(int $position = 6)
    {
        return $this->getField($position);
    }

    public function getStartDateTimeOffset(int $position = 7)
    {
        return $this->getField($position);
    }

    public function getStartDateTimeOffsetUnits(int $position = 8)
    {
        return $this->getField($position);
    }

    public function getDuration(int $position = 9)
    {
        return $this->getField($position);
    }

    public function getDurationUnits(int $position = 10)
    {
        return $this->getField($position);
    }

    public function getAllowSubstitutionCode(int $position = 11)
    {
        return $this->getField($position);
    }

    public function getFillerStatusCode(int $position = 12)
    {
        return $this->getField($position);
    }
}
