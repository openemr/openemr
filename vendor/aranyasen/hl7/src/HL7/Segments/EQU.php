<?php

namespace Aranyasen\HL7\Segments;

use Aranyasen\HL7\Segment;

/**
 * EQU segment class
 * Ref: https://www.interfaceware.com/hl7-standard/hl7-segment-EQU.html
 */
class EQU extends Segment
{
    /**
     * Index of this segment. Incremented for every new segment of this class created
     * @var int
     */
    protected static $setId = 1;

    public function __construct(array $fields = null)
    {
        parent::__construct('EQU', $fields);
    }

    /**
     * Reset index of this segment
     * @param int $index
     */
    public static function resetIndex(int $index = 1): void
    {
        self::$setId = $index;
    }

    public function setEquipmentInstanceIdentifier(int $value, int $position = 1)
    {
        return $this->setField($position, $value);
    }

    public function setEventDateTime($value, int $position = 2)
    {
        return $this->setField($position, $value);
    }

    public function setEquipmentState($value, int $position = 3)
    {
        return $this->setField($position, $value);
    }

    public function setLocalRemoteControlState($value, int $position = 4)
    {
        return $this->setField($position, $value);
    }

    public function setAlertLevel($value, int $position = 5)
    {
        return $this->setField($position, $value);
    }

    public function getEquipmentInstanceIdentifier(int $position = 1)
    {
        return $this->getField($position);
    }

    public function getEventDateTime(int $position = 2)
    {
        return $this->getField($position);
    }

    public function getEquipmentState(int $position = 3)
    {
        return $this->getField($position);
    }

    public function getLocalRemoteControlState(int $position = 4)
    {
        return $this->getField($position);
    }

    public function getAlertLevel(int $position = 4)
    {
        return $this->getField($position);
    }

}
