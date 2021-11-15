<?php

namespace Aranyasen\HL7\Segments;

use Aranyasen\HL7\Segment;

/**
 * OBX segment class
 * Ref: https://hl7-definition.caristix.com/v2/HL7v2.5.1/Segments/OBX 
 */
class OBX extends Segment
{
    /**
     * Index of this segment. Incremented for every new segment of this class created
     * @var int
     */
    protected static $setId = 1;

    public function __construct(array $fields = null, bool $autoIncrementIndices = true)
    {
        parent::__construct('OBX', $fields);
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

    public function setValueType($value, int $position = 2)
    {
        return $this->setField($position, $value);
    }

    public function setObservationIdentifier($value, int $position = 3)
    {
        return $this->setField($position, $value);
    }

    public function setObservationSubId($value, int $position = 4)
    {
        return $this->setField($position, $value);
    }

    public function setObservationValue($value, int $position = 5)
    {
        return $this->setField($position, $value);
    }

    public function setUnits($value, int $position = 6)
    {
        return $this->setField($position, $value);
    }

    public function setReferenceRange($value, int $position = 7)
    {
        return $this->setField($position, $value);
    }

    public function setAbnormalFlags($value, int $position = 8)
    {
        return $this->setField($position, $value);
    }

    public function setProbability($value, int $position = 9)
    {
        return $this->setField($position, $value);
    }

    public function setNatureOfAbnormalTest($value, int $position = 10)
    {
        return $this->setField($position, $value);
    }

    public function setObserveResultStatus($value, int $position = 11)
    {
        return $this->setField($position, $value);
    }

    public function setDataLastObsNormalValues($value, int $position = 12)
    {
        return $this->setField($position, $value);
    }

    public function setUserDefinedAccessChecks($value, int $position = 13)
    {
        return $this->setField($position, $value);
    }

    public function setDateTimeOfTheObservation($value, int $position = 14)
    {
           return $this->setField($position, $value);
    }

    public function setProducersId($value, int $position = 15)
    {
        return $this->setField($position, $value);
    }

    public function setResponsibleObserver($value, int $position = 16)
    {
        return $this->setField($position, $value);
    }

    public function setObservationMethod($value, int $position = 17)
    {
        return $this->setField($position, $value);
    }

    public function setEquipmentInstanceIdentifier($value, int $position = 18)
    {
        return $this->setField($position, $value);
    }

    public function setDateTimeOfAnalysis($value, int $position = 19)
    {
        return $this->setField($position, $value);
    }

    public function getID(int $position = 1)
    {
        return $this->getField($position);
    }

    public function getValueType(int $position = 2)
    {
        return $this->getField($position);
    }

    public function getObservationIdentifier(int $position = 3)
    {
        return $this->getField($position);
    }

    public function getObservationSubId(int $position = 4)
    {
        return $this->getField($position);
    }

    public function getObservationValue(int $position = 5)
    {
        return $this->getField($position);
    }

    public function getUnits(int $position = 6)
    {
        return $this->getField($position);
    }

    public function getReferenceRange(int $position = 7)
    {
        return $this->getField($position);
    }

    public function getAbnormalFlags(int $position = 8)
    {
        return $this->getField($position);
    }

    public function getProbability(int $position = 9)
    {
        return $this->getField($position);
    }

    public function getNatureOfAbnormalTest(int $position = 10)
    {
        return $this->getField($position);
    }

    public function getObserveResultStatus(int $position = 11)
    {
        return $this->getField($position);
    }

    public function getDataLastObsNormalValues(int $position = 12)
    {
        return $this->getField($position);
    }

    public function getUserDefinedAccessChecks(int $position = 13)
    {
        return $this->getField($position);
    }

    public function getDateTimeOfTheObservation(int $position = 14)
    {
        return $this->getField($position);
    }

    public function getProducersId(int $position = 15)
    {
        return $this->getField($position);
    }

    public function getResponsibleObserver(int $position = 16)
    {
        return $this->getField($position);
    }

    public function getObservationMethod(int $position = 17)
    {
        return $this->getField($position);
    }
    
    public function getEquipmentInstanceIdentifier(int $position = 18)
    {
        return $this->getField($position);
    }

    public function getDateTimeOfAnalysis(int $position = 19)
    {
        return $this->getField($position);
    }

}
