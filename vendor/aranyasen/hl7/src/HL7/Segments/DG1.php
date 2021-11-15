<?php

namespace Aranyasen\HL7\Segments;

use Aranyasen\HL7\Segment;

/**
 * DG1 segment class
 * Ref: http://hl7-definition.caristix.com:9010/HL7%20v2.3.1/segment/DG1
 */
class DG1 extends Segment
{
    /**
     * Index of this segment. Incremented for every new segment of this class created
     * @var int
     */
    protected static $setId = 1;

    public function __construct(array $fields = null, bool $autoIncrementIndices = true)
    {
        parent::__construct('DG1', $fields);
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

    public function setDiagnosisCodingMethod($value, int $position = 2)
    {
        return $this->setField($position, $value);
    }

    public function setDiagnosisCodeDG1($value, int $position = 3)
    {
        return $this->setField($position, $value);
    }

    public function setDiagnosisDescription($value, int $position = 4)
    {
        return $this->setField($position, $value);
    }

    public function setDiagnosisDateTime($value, int $position = 5)
    {
        return $this->setField($position, $value);
    }

    public function setDiagnosisType($value, int $position = 6)
    {
        return $this->setField($position, $value);
    }

    public function setMajorDiagnosticCategory($value, int $position = 7)
    {
        return $this->setField($position, $value);
    }

    public function setDiagnosticRelatedGroup($value, int $position = 8)
    {
        return $this->setField($position, $value);
    }

    public function setDRGApprovalIndicator($value, int $position = 9)
    {
        return $this->setField($position, $value);
    }

    public function setDRGGrouperReviewCode($value, int $position = 10)
    {
        return $this->setField($position, $value);
    }

    public function setOutlierType($value, int $position = 11)
    {
        return $this->setField($position, $value);
    }

    public function setOutlierDays($value, int $position = 12)
    {
        return $this->setField($position, $value);
    }

    public function setOutlierCost($value, int $position = 13)
    {
        return $this->setField($position, $value);
    }

    public function setGrouperVersionAndType($value, int $position = 14)
    {
        return $this->setField($position, $value);
    }

    public function setDiagnosisPriority($value, int $position = 15)
    {
        return $this->setField($position, $value);
    }

    public function setDiagnosingClinician($value, int $position = 16)
    {
        return $this->setField($position, $value);
    }

    public function setDiagnosisClassification($value, int $position = 17)
    {
        return $this->setField($position, $value);
    }

    public function setConfidentialIndicator($value, int $position = 18)
    {
        return $this->setField($position, $value);
    }

    public function setAttestationDateTime($value, int $position = 19)
    {
        return $this->setField($position, $value);
    }

    public function getID(int $position = 1)
    {
        return $this->getField($position);
    }

    public function getDiagnosisCodingMethod(int $position = 2)
    {
        return $this->getField($position);
    }

    public function getDiagnosisCodeDG1(int $position = 3)
    {
        return $this->getField($position);
    }

    public function getDiagnosisDescription(int $position = 4)
    {
        return $this->getField($position);
    }

    public function getDiagnosisDateTime(int $position = 5)
    {
        return $this->getField($position);
    }

    public function getDiagnosisType(int $position = 6)
    {
        return $this->getField($position);
    }

    public function getMajorDiagnosticCategory(int $position = 7)
    {
        return $this->getField($position);
    }

    public function getDiagnosticRelatedGroup(int $position = 8)
    {
        return $this->getField($position);
    }

    public function getDRGApprovalIndicator(int $position = 9)
    {
        return $this->getField($position);
    }

    public function getDRGGrouperReviewCode(int $position = 10)
    {
        return $this->getField($position);
    }

    public function getOutlierType(int $position = 11)
    {
        return $this->getField($position);
    }

    public function getOutlierDays(int $position = 12)
    {
        return $this->getField($position);
    }

    public function getOutlierCost(int $position = 13)
    {
        return $this->getField($position);
    }

    public function getGrouperVersionAndType(int $position = 14)
    {
        return $this->getField($position);
    }

    public function getDiagnosisPriority(int $position = 15)
    {
        return $this->getField($position);
    }

    public function getDiagnosingClinician(int $position = 16)
    {
        return $this->getField($position);
    }

    public function getDiagnosisClassification(int $position = 17)
    {
        return $this->getField($position);
    }

    public function getConfidentialIndicator(int $position = 18)
    {
        return $this->getField($position);
    }

    public function getAttestationDateTime(int $position = 19)
    {
        return $this->getField($position);
    }
}
