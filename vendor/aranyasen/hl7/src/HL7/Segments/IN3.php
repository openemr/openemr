<?php

namespace Aranyasen\HL7\Segments;

use Aranyasen\HL7\Segment;

/**
 * IN3 segment class
 * Ref: http://hl7-definition.caristix.com:9010/Default.aspx?version=HL7+v2.5.1&segment=ORC
 */
class IN3 extends Segment
{
    /**
     * Index of this segment. Incremented for every new segment of this class created
     * @var int
     */
    protected static $setId = 1;

    public function __construct(array $fields = null, bool $autoIncrementIndices = true)
    {
        parent::__construct('IN3', $fields);
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

    public function setCertificationNumber($value, int $position = 2)
    {
        return $this->setField($position, $value);
    }

    public function setCertifiedBy($value, int $position = 3)
    {
        return $this->setField($position, $value);
    }

    public function setCertificationRequired($value, int $position = 4)
    {
        return $this->setField($position, $value);
    }

    public function setPenalty($value, int $position = 5)
    {
        return $this->setField($position, $value);
    }

    public function setCertificationDateTime($value, int $position = 6)
    {
        return $this->setField($position, $value);
    }

    public function setCertificationModifyDateTime($value, int $position = 7)
    {
        return $this->setField($position, $value);
    }

    public function setOperator($value, int $position = 8)
    {
        return $this->setField($position, $value);
    }

    public function setCertificationBeginDate($value, int $position = 9)
    {
        return $this->setField($position, $value);
    }

    public function setCertificationEndDate($value, int $position = 10)
    {
        return $this->setField($position, $value);
    }

    public function setDays($value, int $position = 11)
    {
        return $this->setField($position, $value);
    }

    public function setNonConcurCodeDescription($value, int $position = 12)
    {
        return $this->setField($position, $value);
    }

    public function setNonConcurEffectiveDateTime($value, int $position = 13)
    {
        return $this->setField($position, $value);
    }

    public function setPhysicianReviewer($value, int $position = 14)
    {
        return $this->setField($position, $value);
    }

    public function setCertificationContact($value, int $position = 15)
    {
        return $this->setField($position, $value);
    }

    public function setCertificationContactPhoneNumber($value, int $position = 16)
    {
        return $this->setField($position, $value);
    }

    public function setAppealReason($value, int $position = 17)
    {
        return $this->setField($position, $value);
    }

    public function setCertificationAgency($value, int $position = 18)
    {
        return $this->setField($position, $value);
    }

    public function setCertificationAgencyPhoneNumber($value, int $position = 19)
    {
        return $this->setField($position, $value);
    }

    public function setPreCertificationRequirement($value, int $position = 20)
    {
        return $this->setField($position, $value);
    }

    public function setCaseManager($value, int $position = 21)
    {
        return $this->setField($position, $value);
    }

    public function setSecondOpinionDate($value, int $position = 22)
    {
        return $this->setField($position, $value);
    }

    public function setSecondOpinionStatus($value, int $position = 23)
    {
        return $this->setField($position, $value);
    }

    public function setSecondOpinionDocumentationReceived($value, int $position = 24)
    {
        return $this->setField($position, $value);
    }

    public function setSecondOpinionPhysician($value, int $position = 25)
    {
        return $this->setField($position, $value);
    }

    public function getID(int $position = 1)
    {
        return $this->getField($position);
    }

    public function getCertificationNumber(int $position = 2)
    {
        return $this->getField($position);
    }

    public function getCertifiedBy(int $position = 3)
    {
        return $this->getField($position);
    }

    public function getCertificationRequired(int $position = 4)
    {
        return $this->getField($position);
    }

    public function getPenalty(int $position = 5)
    {
        return $this->getField($position);
    }

    public function getCertificationDateTime(int $position = 6)
    {
        return $this->getField($position);
    }

    public function getCertificationModifyDateTime(int $position = 7)
    {
        return $this->getField($position);
    }

    public function getOperator(int $position = 8)
    {
        return $this->getField($position);
    }

    public function getCertificationBeginDate(int $position = 9)
    {
        return $this->getField($position);
    }

    public function getCertificationEndDate(int $position = 10)
    {
        return $this->getField($position);
    }

    public function getDays(int $position = 11)
    {
        return $this->getField($position);
    }

    public function getNonConcurCodeDescription(int $position = 12)
    {
        return $this->getField($position);
    }

    public function getNonConcurEffectiveDateTime(int $position = 13)
    {
        return $this->getField($position);
    }

    public function getPhysicianReviewer(int $position = 14)
    {
        return $this->getField($position);
    }

    public function getCertificationContact(int $position = 15)
    {
        return $this->getField($position);
    }

    public function getCertificationContactPhoneNumber(int $position = 16)
    {
        return $this->getField($position);
    }

    public function getAppealReason(int $position = 17)
    {
        return $this->getField($position);
    }

    public function getCertificationAgency(int $position = 18)
    {
        return $this->getField($position);
    }

    public function getCertificationAgencyPhoneNumber(int $position = 19)
    {
        return $this->getField($position);
    }

    public function getPreCertificationRequirement(int $position = 20)
    {
        return $this->getField($position);
    }

    public function getCaseManager(int $position = 21)
    {
        return $this->getField($position);
    }

    public function getSecondOpinionDate(int $position = 22)
    {
        return $this->getField($position);
    }

    public function getSecondOpinionStatus(int $position = 23)
    {
        return $this->getField($position);
    }

    public function getSecondOpinionDocumentationReceived(int $position = 24)
    {
        return $this->getField($position);
    }

    public function getSecondOpinionPhysician(int $position = 25)
    {
        return $this->getField($position);
    }
}
