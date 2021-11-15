<?php

namespace Aranyasen\HL7\Segments;

use Aranyasen\HL7\Segment;

/**
 * IN1 segment class
 * Ref: https://corepointhealth.com/resource-center/hl7-resources/hl7-in1-insurance-segment
 */
class IN1 extends Segment
{
    /**
     * Index of this segment. Incremented for every new segment of this class created
     * @var int
     */
    protected static $setId = 1;

    public function __construct(array $fields = null, bool $autoIncrementIndices = true)
    {
        parent::__construct('IN1', $fields);
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

    public function setInsurancePlanID($value, int $position = 2)
    {
        return $this->setField($position, $value);
    }

    public function setInsuranceCompanyID($value, int $position = 3)
    {
        return $this->setField($position, $value);
    }

    public function setInsuranceCompanyName($value, int $position = 4)
    {
        return $this->setField($position, $value);
    }

    public function setInsuranceCompanyAddress($value, int $position = 5)
    {
        return $this->setField($position, $value);
    }

    public function setInsuranceCoContactPerson($value, int $position = 6)
    {
        return $this->setField($position, $value);
    }

    public function setInsuranceCoPhoneNumber($value, int $position = 7)
    {
        return $this->setField($position, $value);
    }

    public function setGroupNumber($value, int $position = 8)
    {
        return $this->setField($position, $value);
    }

    public function setGroupName($value, int $position = 9)
    {
        return $this->setField($position, $value);
    }

    public function setInsuredsGroupEmpID($value, int $position = 10)
    {
        return $this->setField($position, $value);
    }

    public function setInsuredsGroupEmpName($value, int $position = 11)
    {
        return $this->setField($position, $value);
    }

    public function setPlanEffectiveDate($value, int $position = 12)
    {
        return $this->setField($position, $value);
    }

    public function setPlanExpirationDate($value, int $position = 13)
    {
        return $this->setField($position, $value);
    }

    public function setAuthorizationInformation($value, int $position = 14)
    {
        return $this->setField($position, $value);
    }

    public function setPlanType($value, int $position = 15)
    {
        return $this->setField($position, $value);
    }

    public function setNameOfInsured($value, int $position = 16)
    {
        return $this->setField($position, $value);
    }

    public function setInsuredsRelationshipToPatient($value, int $position = 17)
    {
        return $this->setField($position, $value);
    }

    public function setInsuredsDateOfBirth($value, int $position = 18)
    {
        return $this->setField($position, $value);
    }

    public function setInsuredsAddress($value, int $position = 19)
    {
        return $this->setField($position, $value);
    }

    public function setAssignmentOfBenefits($value, int $position = 20)
    {
        return $this->setField($position, $value);
    }

    public function setCoordinationOfBenefits($value, int $position = 21)
    {
        return $this->setField($position, $value);
    }

    public function setCoordOfBenPriority($value, int $position = 22)
    {
        return $this->setField($position, $value);
    }

    public function setNoticeOfAdmissionFlag($value, int $position = 23)
    {
        return $this->setField($position, $value);
    }

    public function setNoticeOfAdmissionDate($value, int $position = 24)
    {
        return $this->setField($position, $value);
    }

    public function setReportOfEligibilityFlag($value, int $position = 25)
    {
        return $this->setField($position, $value);
    }

    public function setReportOfEligibilityDate($value, int $position = 26)
    {
        return $this->setField($position, $value);
    }

    public function setReleaseInformationCode($value, int $position = 27)
    {
        return $this->setField($position, $value);
    }

    public function setPreAdmitCertPAC($value, int $position = 28)
    {
        return $this->setField($position, $value);
    }

    public function setVerificationDateTime($value, int $position = 29)
    {
        return $this->setField($position, $value);
    }

    public function setVerificationBy($value, int $position = 30)
    {
        return $this->setField($position, $value);
    }

    public function setTypeOfAgreementCode($value, int $position = 31)
    {
        return $this->setField($position, $value);
    }

    public function setBillingStatus($value, int $position = 32)
    {
        return $this->setField($position, $value);
    }

    public function setLifetimeReserveDays($value, int $position = 33)
    {
        return $this->setField($position, $value);
    }

    public function setDelayBeforeLRDay($value, int $position = 34)
    {
        return $this->setField($position, $value);
    }

    public function setCompanyPlanCode($value, int $position = 35)
    {
        return $this->setField($position, $value);
    }

    public function setPolicyNumber($value, int $position = 36)
    {
        return $this->setField($position, $value);
    }

    public function setPolicyDeductible($value, int $position = 37)
    {
        return $this->setField($position, $value);
    }

    public function setPolicyLimitAmount($value, int $position = 38)
    {
        return $this->setField($position, $value);
    }

    public function setPolicyLimitDays($value, int $position = 39)
    {
        return $this->setField($position, $value);
    }

    public function setRoomRateSemiPrivate($value, int $position = 40)
    {
        return $this->setField($position, $value);
    }

    public function setRoomRatePrivate($value, int $position = 41)
    {
        return $this->setField($position, $value);
    }

    public function setInsuredsEmploymentStatus($value, int $position = 42)
    {
        return $this->setField($position, $value);
    }

    public function setInsuredsSex($value, int $position = 43)
    {
        return $this->setField($position, $value);
    }

    public function setInsuredsEmployersAddress($value, int $position = 44)
    {
        return $this->setField($position, $value);
    }

    public function setVerificationStatus($value, int $position = 45)
    {
        return $this->setField($position, $value);
    }

    public function setPriorInsurancePlanID($value, int $position = 46)
    {
        return $this->setField($position, $value);
    }

    public function setCoverageType($value, int $position = 47)
    {
        return $this->setField($position, $value);
    }

    public function setHandicap($value, int $position = 48)
    {
        return $this->setField($position, $value);
    }

    public function setInsuredsIDNumber($value, int $position = 49)
    {
        return $this->setField($position, $value);
    }

    public function getID(int $position = 1)
    {
        return $this->getField($position);
    }

    public function getInsurancePlanID(int $position = 2)
    {
        return $this->getField($position);
    }

    public function getInsuranceCompanyID(int $position = 3)
    {
        return $this->getField($position);
    }

    public function getInsuranceCompanyName(int $position = 4)
    {
        return $this->getField($position);
    }

    public function getInsuranceCompanyAddress(int $position = 5)
    {
        return $this->getField($position);
    }

    public function getInsuranceCoContactPerson(int $position = 6)
    {
        return $this->getField($position);
    }

    public function getInsuranceCoPhoneNumber(int $position = 7)
    {
        return $this->getField($position);
    }

    public function getGroupNumber(int $position = 8)
    {
        return $this->getField($position);
    }

    public function getGroupName(int $position = 9)
    {
        return $this->getField($position);
    }

    public function getInsuredsGroupEmpID(int $position = 10)
    {
        return $this->getField($position);
    }

    public function getInsuredsGroupEmpName(int $position = 11)
    {
        return $this->getField($position);
    }

    public function getPlanEffectiveDate(int $position = 12)
    {
        return $this->getField($position);
    }

    public function getPlanExpirationDate(int $position = 13)
    {
        return $this->getField($position);
    }

    public function getAuthorizationInformation(int $position = 14)
    {
        return $this->getField($position);
    }

    public function getPlanType(int $position = 15)
    {
        return $this->getField($position);
    }

    public function getNameOfInsured(int $position = 16)
    {
        return $this->getField($position);
    }

    public function getInsuredsRelationshipToPatient(int $position = 17)
    {
        return $this->getField($position);
    }

    public function getInsuredsDateOfBirth(int $position = 18)
    {
        return $this->getField($position);
    }

    public function getInsuredsAddress(int $position = 19)
    {
        return $this->getField($position);
    }

    public function getAssignmentOfBenefits(int $position = 20)
    {
        return $this->getField($position);
    }

    public function getCoordinationOfBenefits(int $position = 21)
    {
        return $this->getField($position);
    }

    public function getCoordOfBenPriority(int $position = 22)
    {
        return $this->getField($position);
    }

    public function getNoticeOfAdmissionFlag(int $position = 23)
    {
        return $this->getField($position);
    }

    public function getNoticeOfAdmissionDate(int $position = 24)
    {
        return $this->getField($position);
    }

    public function getReportOfEligibilityFlag(int $position = 25)
    {
        return $this->getField($position);
    }

    public function getReportOfEligibilityDate(int $position = 26)
    {
        return $this->getField($position);
    }

    public function getReleaseInformationCode(int $position = 27)
    {
        return $this->getField($position);
    }

    public function getPreAdmitCertPAC(int $position = 28)
    {
        return $this->getField($position);
    }

    public function getVerificationDateTime(int $position = 29)
    {
        return $this->getField($position);
    }

    public function getVerificationBy(int $position = 30)
    {
        return $this->getField($position);
    }

    public function getTypeOfAgreementCode(int $position = 31)
    {
        return $this->getField($position);
    }

    public function getBillingStatus(int $position = 32)
    {
        return $this->getField($position);
    }

    public function getLifetimeReserveDays(int $position = 33)
    {
        return $this->getField($position);
    }

    public function getDelayBeforeLRDay(int $position = 34)
    {
        return $this->getField($position);
    }

    public function getCompanyPlanCode(int $position = 35)
    {
        return $this->getField($position);
    }

    public function getPolicyNumber(int $position = 36)
    {
        return $this->getField($position);
    }

    public function getPolicyDeductible(int $position = 37)
    {
        return $this->getField($position);
    }

    public function getPolicyLimitAmount(int $position = 38)
    {
        return $this->getField($position);
    }

    public function getPolicyLimitDays(int $position = 39)
    {
        return $this->getField($position);
    }

    public function getRoomRateSemiPrivate(int $position = 40)
    {
        return $this->getField($position);
    }

    public function getRoomRatePrivate(int $position = 41)
    {
        return $this->getField($position);
    }

    public function getInsuredsEmploymentStatus(int $position = 42)
    {
        return $this->getField($position);
    }

    public function getInsuredsSex(int $position = 43)
    {
        return $this->getField($position);
    }

    public function getInsuredsEmployersAddress(int $position = 44)
    {
        return $this->getField($position);
    }

    public function getVerificationStatus(int $position = 45)
    {
        return $this->getField($position);
    }

    public function getPriorInsurancePlanID(int $position = 46)
    {
        return $this->getField($position);
    }

    public function getCoverageType(int $position = 47)
    {
        return $this->getField($position);
    }

    public function getHandicap(int $position = 48)
    {
        return $this->getField($position);
    }

    public function getInsuredsIDNumber(int $position = 49)
    {
        return $this->getField($position);
    }
}
