<?php

namespace Aranyasen\HL7\Segments;

use Aranyasen\HL7\Segment;

/**
 * AIG segment class
 *
 * AIG: Appointment information - general resource segment
 * The AIG segment contains information about various kinds of resources (other than those with specifically defined
 * segments in this chapter) that can be scheduled. Resources included in a transaction using this segment are assumed
 * to be controlled by a schedule on a schedule filler application. Resources not controlled by a schedule are not
 * identified on a schedule request using this segment. Resources described by this segment are general kinds of
 * resources, such as equipment, that are identified with a simple identification code.

 * Ref: http://hl7-definition.caristix.com:9010/Default.aspx?version=HL7+v2.3.1&segment=AIG
 */
class AIG extends Segment
{
    /**
     * Index of this segment. Incremented for every new segment of this class created
     * @var int
     */
    protected static $setId = 1;

    public function __construct(array $fields = null)
    {
        parent::__construct('AIG', $fields);
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

    public function setResourceID($value, int $position = 3): bool
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

    public function setResourceQuantity($value, int $position = 6): bool
    {
        return $this->setField($value, $position);
    }

    public function setResourceQuantityUnits($value, int $position = 7): bool
    {
        return $this->setField($value, $position);
    }

    public function setStartDateTime($value, int $position = 8): bool
    {
        return $this->setField($value, $position);
    }

    public function setStartDateTimeOffset($value, int $position = 9): bool
    {
        return $this->setField($value, $position);
    }

    public function setStartDateTimeOffsetUnits($value, int $position = 10): bool
    {
        return $this->setField($value, $position);
    }

    public function setDuration($value, int $position = 11): bool
    {
        return $this->setField($value, $position);
    }

    public function setDurationUnits($value, int $position = 12): bool
    {
        return $this->setField($value, $position);
    }

    public function setAllowSubstitutionCode($value, int $position = 13): bool
    {
        return $this->setField($value, $position);
    }

    public function setFillerStatusCode($value, int $position = 14): bool
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

    public function getResourceID(int $position = 3)
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

    public function getResourceQuantity(int $position = 6)
    {
        return $this->getField($position);
    }

    public function getResourceQuantityUnits(int $position = 7)
    {
        return $this->getField($position);
    }

    public function getStartDateTime(int $position = 8)
    {
        return $this->getField($position);
    }

    public function getStartDateTimeOffset(int $position = 9)
    {
        return $this->getField($position);
    }

    public function getStartDateTimeOffsetUnits(int $position = 10)
    {
        return $this->getField($position);
    }

    public function getDuration(int $position = 11)
    {
        return $this->getField($position);
    }

    public function getDurationUnits(int $position = 12)
    {
        return $this->getField($position);
    }

    public function getAllowSubstitutionCode(int $position = 13)
    {
        return $this->getField($position);
    }

    public function getFillerStatusCode(int $position = 14)
    {
        return $this->getField($position);
    }
}
