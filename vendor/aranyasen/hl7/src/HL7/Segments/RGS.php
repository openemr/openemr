<?php

namespace Aranyasen\HL7\Segments;

use Aranyasen\HL7\Segment;

/**
 * RGS segment class
 * Ref: http://hl7-definition.caristix.com:9010/Default.aspx?version=HL7+v2.5.1&segment=RGS
 */
class RGS extends Segment
{
    /**
     * Index of this segment. Incremented for every new segment of this class created
     * @var int
     */
    protected static $setId = 1;

    public function __construct(array $fields = null, bool $autoIncrementIndices = true)
    {
        parent::__construct('RGS', $fields);
        if ($autoIncrementIndices) {
            $this->setID($this::$setId++);
        }
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
        return $this->setField($position, $value);
    }

    public function setResourceGroupID($value, int $position = 3): bool
    {
        return $this->setField($position, $value);
    }

    public function getID(int $position = 1)
    {
        return $this->getField($position);
    }

    public function getSegmentActionCode(int $position = 2)
    {
        return $this->getField($position);
    }

    public function getResourceGroupID(int $position = 3)
    {
        return $this->getField($position);
    }
}
