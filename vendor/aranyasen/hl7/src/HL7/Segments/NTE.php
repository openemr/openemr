<?php

namespace Aranyasen\HL7\Segments;

use Aranyasen\HL7\Segment;

/**
 * NTE segment class
 * Ref: https://corepointhealth.com/resource-center/hl7-resources/hl7-nte-notes-comments
 */
class NTE extends Segment
{
    /**
     * Index of this segment. Incremented for every new segment of this class created
     * @var int
     */
    protected static $setId = 1;

    public function __construct(array $fields = null, bool $autoIncrementIndices = true)
    {
        parent::__construct('NTE', $fields);
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

    public function setID(int $value, int $position = 1)
    {
        return $this->setField($position, $value);
    }

    public function setSourceOfComment($value, int $position = 2)
    {
        return $this->setField($position, $value);
    }

    public function setComment($value, int $position = 3)
    {
        return $this->setField($position, $value);
    }

    public function setCommentType($value, int $position = 4)
    {
        return $this->setField($position, $value);
    }

    public function getID(int $position = 1)
    {
        return $this->getField($position);
    }

    public function getSourceOfComment(int $position = 2)
    {
        return $this->getField($position);
    }

    public function getComment(int $position = 3)
    {
        return $this->getField($position);
    }

    public function getCommentType(int $position = 4)
    {
        return $this->getField($position);
    }
}
