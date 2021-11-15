<?php

namespace Aranyasen\HL7\Segments;

use Aranyasen\HL7\Segment;

/**
 * PV1 segment class
 * Ref: https://corepointhealth.com/resource-center/hl7-resources/hl7-pv1-patient-visit-information-segment
 */
class PV1 extends Segment
{
    /**
     * Index of this segment. Incremented for every new segment of this class created
     * @var int
     */
    protected static $setId = 1;

    public function __construct(array $fields = null, bool $autoIncrementIndices = true)
    {
        parent::__construct('PV1', $fields);
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

    public function setPatientClass($value, int $position = 2)
    {
        return $this->setField($position, $value);
    }

    public function setAssignedPatientLocation($value, int $position = 3)
    {
        return $this->setField($position, $value);
    }

    /**
     * @param $value
     * @param int $position
     * @return bool
     */
    public function setAdmissionType($value, int $position = 4)
    {
        return $this->setField($position, $value);
    }

    /**
     * @param $value
     * @param int $position
     * @return bool
     */
    public function setPreAdmitNumber($value, int $position = 5)
    {
        return $this->setField($position, $value);
    }

    /**
     * @param $value
     * @param int $position
     * @return bool
     */
    public function setPriorPatientLocation($value, int $position = 6)
    {
        return $this->setField($position, $value);
    }

    /**
     * @param $value
     * @param int $position
     * @return bool
     */
    public function setAttendingDoctor($value, int $position = 7)
    {
        return $this->setField($position, $value);
    }

    /**
     * @param $value
     * @param int $position
     * @return bool
     */
    public function setReferringDoctor($value, int $position = 8)
    {
        return $this->setField($position, $value);
    }

    /**
     * @param $value
     * @param int $position
     * @return bool
     */
    public function setConsultingDoctor($value, int $position = 9)
    {
        return $this->setField($position, $value);
    }

    /**
     * @param $value
     * @param int $position
     * @return bool
     */
    public function setHospitalService($value, int $position = 10)
    {
        return $this->setField($position, $value);
    }

    /**
     * @param $value
     * @param int $position
     * @return bool
     */
    public function setTemporaryLocation($value, int $position = 11)
    {
        return $this->setField($position, $value);
    }

    /**
     * @param $value
     * @param int $position
     * @return bool
     */
    public function setPreAdmitTestIndicator($value, int $position = 12)
    {
        return $this->setField($position, $value);
    }

    /**
     * @param $value
     * @param int $position
     * @return bool
     */
    public function setReAdmissionIndicator($value, int $position = 13)
        {
        return $this->setField($position, $value);
    }

    /**
     * @param $value
     * @param int $position
     * @return bool
     */
    public function setAdmitSource($value, int $position = 14)
    {
        return $this->setField($position, $value);
    }

    /**
     * @param $value
     * @param int $position
     * @return bool
     */
    public function setAmbulatoryStatus($value, int $position = 15)
    {
        return $this->setField($position, $value);
    }

    /**
     * @param $value
     * @param int $position
     * @return bool
     */
    public function setVipIndicator($value, int $position = 16)
    {
        return $this->setField($position, $value);
    }

    /**
     * @param $value
     * @param int $position
     * @return bool
     */
    public function setAdmittingDoctor($value, int $position = 17)
    {
        return $this->setField($position, $value);
    }

    /**
     * @param $value
     * @param int $position
     * @return bool
     */
    public function setPatientType($value, int $position = 18)
    {
        return $this->setField($position, $value);
    }

    /**
     * @param $value
     * @param int $position
     * @return bool
     */
    public function setVisitNumber($value, int $position = 19)
    {
        return $this->setField($position, $value);
    }

    /**
     * @param $value
     * @param int $position
     * @return bool
     */
    public function setFinancialClass($value, int $position = 20)
    {
        return $this->setField($position, $value);
    }

    /**
     * @param $value
     * @param int $position
     * @return bool
     */
    public function setChargePriceIndicator($value, int $position = 21)
    {
        return $this->setField($position, $value);
    }

    /**
     * @param $value
     * @param int $position
     * @return bool
     */
    public function setCourtesyCode($value, int $position = 22)
    {
        return $this->setField($position, $value);
    }

    /**
     * @param $value
     * @param int $position
     * @return bool
     */
    public function setCreditRating($value, int $position = 23)
    {
        return $this->setField($position, $value);
    }

    /**
     * @param $value
     * @param int $position
     * @return bool
     */
    public function setContractCode($value, int $position = 24)
    {
        return $this->setField($position, $value);
    }

    /**
     * @param $value
     * @param int $position
     * @return bool
     */
    public function setContractEffectiveDate($value, int $position = 25)
    {
        return $this->setField($position, $value);
    }

    /**
     * @param $value
     * @param int $position
     * @return bool
     */
    public function setContractAmount($value, int $position = 26)
    {
        return $this->setField($position, $value);
    }

    /**
     * @param $value
     * @param int $position
     * @return bool
     */
    public function setContractPeriod($value, int $position = 27)
    {
        return $this->setField($position, $value);
    }

    /**
     * @param $value
     * @param int $position
     * @return bool
     */
    public function setInterestCode($value, int $position = 28)
    {
        return $this->setField($position, $value);
    }

    /**
     * @param $value
     * @param int $position
     * @return bool
     */
    public function setTransferToBadDebtCode($value, int $position = 29)
    {
        return $this->setField($position, $value);
    }

    /**
     * @param $value
     * @param int $position
     * @return bool
     */
    public function setTransferToBadDebtDate($value, int $position = 30)
    {
        return $this->setField($position, $value);
    }

    /**
     * @param $value
     * @param int $position
     * @return bool
     */
    public function setBadDebtAgencyCode($value, int $position = 31)
    {
        return $this->setField($position, $value);
    }

    /**
     * @param $value
     * @param int $position
     * @return bool
     */
    public function setBadDebtTransferAmount($value, int $position = 32)
    {
        return $this->setField($position, $value);
    }

    /**
     * @param $value
     * @param int $position
     * @return bool
     */
    public function setBadDebtRecoveryAmount($value, int $position = 33)
    {
        return $this->setField($position, $value);
    }

    /**
     * @param $value
     * @param int $position
     * @return bool
     */
    public function setDeleteAccountIndicator($value, int $position = 34)
    {
        return $this->setField($position, $value);
    }

    /**
     * @param $value
     * @param int $position
     * @return bool
     */
    public function setDeleteAccountDate($value, int $position = 35)
    {
        return $this->setField($position, $value);
    }

    /**
     * @param $value
     * @param int $position
     * @return bool
     */
    public function setDischargeDisposition($value, int $position = 36)
    {
        return $this->setField($position, $value);
    }

    /**
     * @param $value
     * @param int $position
     * @return bool
     */
    public function setDischargedToLocation($value, int $position = 37)
    {
        return $this->setField($position, $value);
    }

    /**
     * @param $value
     * @param int $position
     * @return bool
     */
    public function setDietType($value, int $position = 38)
    {
        return $this->setField($position, $value);
    }

    /**
     * @param $value
     * @param int $position
     * @return bool
     */
    public function setServicingFacility($value, int $position = 39)
    {
        return $this->setField($position, $value);
    }

    /**
     * @param $value
     * @param int $position
     * @return bool
     */
    public function setBedStatus($value, int $position = 40)
    {
        return $this->setField($position, $value);
    }

    /**
     * @param $value
     * @param int $position
     * @return bool
     */
    public function setAccountStatus($value, int $position = 41)
    {
        return $this->setField($position, $value);
    }

    /**
     * @param $value
     * @param int $position
     * @return bool
     */
    public function setPendingLocation($value, int $position = 42)
    {
        return $this->setField($position, $value);
    }

    /**
     * @param $value
     * @param int $position
     * @return bool
     */
    public function setPriorTemporaryLocation($value, int $position = 43)
    {
        return $this->setField($position, $value);
    }

    /**
     * @param $value
     * @param int $position
     * @return bool
     */
    public function setAdmitDateTime($value, int $position = 44)
    {
        return $this->setField($position, $value);
    }

    /**
     * @param $value
     * @param int $position
     * @return bool
     */
    public function setDischargeDateTime($value, int $position = 45)
    {
        return $this->setField($position, $value);
    }

    /**
     * @param $value
     * @param int $position
     * @return bool
     */
    public function setCurrentPatientBalance($value, int $position = 46)
    {
        return $this->setField($position, $value);
    }

    /**
     * @param $value
     * @param int $position
     * @return bool
     */
    public function setTotalCharges($value, int $position = 47)
    {
        return $this->setField($position, $value);
    }

    /**
     * @param $value
     * @param int $position
     * @return bool
     */
    public function setTotalAdjustments($value, int $position = 48)
    {
        return $this->setField($position, $value);
    }

    /**
     * @param $value
     * @param int $position
     * @return bool
     */
    public function setTotalPayments($value, int $position = 49)
    {
        return $this->setField($position, $value);
    }

    /**
     * @param $value
     * @param int $position
     * @return bool
     */
    public function setAlternateVisitID($value, int $position = 50)
    {
        return $this->setField($position, $value);
    }

    /**
     * @param $value
     * @param int $position
     * @return bool
     */
    public function setVisitIndicator($value, int $position = 51)
    {
        return $this->setField($position, $value);
    }

    /**
     * @param $value
     * @param int $position
     * @return bool
     */
    public function setOtherHealthcareProvider($value, int $position = 52)
    {
        return $this->setField($position, $value);
    }

    public function getID(int $position = 1)
    {
        return $this->getField($position);
    }

    public function getPatientClass(int $position = 2)
    {
        return $this->getField($position);
    }

    public function getAssignedPatientLocation(int $position = 3)
    {
        return $this->getField($position);
    }

    public function getAdmissionType(int $position = 4)
    {
        return $this->getField($position);
    }

    public function getPreAdmitNumber(int $position = 5)
    {
        return $this->getField($position);
    }

    public function getPriorPatientLocation(int $position = 6)
    {
        return $this->getField($position);
    }

    public function getAttendingDoctor(int $position = 7)
    {
        return $this->getField($position);
    }

    public function getReferringDoctor(int $position = 8)
    {
        return $this->getField($position);
    }

    public function getConsultingDoctor(int $position = 9)
    {
        return $this->getField($position);
    }

    public function getHospitalService(int $position = 10)
    {
        return $this->getField($position);
    }

    public function getTemporaryLocation(int $position = 11)
    {
        return $this->getField($position);
    }

    public function getPreAdmitTestIndicator(int $position = 12)
    {
        return $this->getField($position);
    }

    public function getReAdmissionIndicator(int $position = 13)
    {
        return $this->getField($position);
    }

    public function getAdmitSource(int $position = 14)
    {
        return $this->getField($position);
    }

    public function getAmbulatoryStatus(int $position = 15)
    {
        return $this->getField($position);
    }

    public function getVipIndicator(int $position = 16)
    {
        return $this->getField($position);
    }

    public function getAdmittingDoctor(int $position = 17)
    {
        return $this->getField($position);
    }

    public function getPatientType(int $position = 18)
    {
        return $this->getField($position);
    }

    public function getVisitNumber(int $position = 19)
    {
        return $this->getField($position);
    }

    public function getFinancialClass(int $position = 20)
    {
        return $this->getField($position);
    }

    public function getChargePriceIndicator(int $position = 21)
    {
        return $this->getField($position);
    }

    public function getCourtesyCode(int $position = 22)
    {
        return $this->getField($position);
    }

    public function getCreditRating(int $position = 23)
    {
        return $this->getField($position);
    }

    public function getContractCode(int $position = 24)
    {
        return $this->getField($position);
    }

    public function getContractEffectiveDate(int $position = 25)
    {
        return $this->getField($position);
    }

    public function getContractAmount(int $position = 26)
    {
        return $this->getField($position);
    }

    public function getContractPeriod(int $position = 27)
    {
        return $this->getField($position);
    }

    public function getInterestCode(int $position = 28)
    {
        return $this->getField($position);
    }

    public function getTransferToBadDebtCode(int $position = 29)
    {
        return $this->getField($position);
    }

    public function getTransferToBadDebtDate(int $position = 30)
    {
        return $this->getField($position);
    }

    public function getBadDebtAgencyCode(int $position = 31)
    {
        return $this->getField($position);
    }

    public function getBadDebtTransferAmount(int $position = 32)
    {
        return $this->getField($position);
    }

    public function getBadDebtRecoveryAmount(int $position = 33)
    {
        return $this->getField($position);
    }

    public function getDeleteAccountIndicator(int $position = 34)
    {
        return $this->getField($position);
    }

    public function getDeleteAccountDate(int $position = 35)
    {
        return $this->getField($position);
    }

    public function getDischargeDisposition(int $position = 36)
    {
        return $this->getField($position);
    }

    public function getDischargedToLocation(int $position = 37)
    {
        return $this->getField($position);
    }

    public function getDietType(int $position = 38)
    {
        return $this->getField($position);
    }

    public function getServicingFacility(int $position = 39)
    {
        return $this->getField($position);
    }

    public function getBedStatus(int $position = 40)
    {
        return $this->getField($position);
    }

    public function getAccountStatus(int $position = 41)
    {
        return $this->getField($position);
    }

    public function getPendingLocation(int $position = 42)
    {
        return $this->getField($position);
    }

    public function getPriorTemporaryLocation(int $position = 43)
    {
        return $this->getField($position);
    }

    public function getAdmitDateTime(int $position = 44)
    {
        return $this->getField($position);
    }

    public function getDischargeDateTime(int $position = 45)
    {
        return $this->getField($position);
    }

    public function getCurrentPatientBalance(int $position = 46)
    {
        return $this->getField($position);
    }

    public function getTotalCharges(int $position = 47)
    {
        return $this->getField($position);
    }

    public function getTotalAdjustments(int $position = 48)
    {
        return $this->getField($position);
    }

    public function getTotalPayments(int $position = 49)
    {
        return $this->getField($position);
    }

    public function getAlternateVisitID(int $position = 50)
    {
        return $this->getField($position);
    }

    public function getVisitIndicator(int $position = 51)
    {
        return $this->getField($position);
    }

    public function getOtherHealthcareProvider(int $position = 52)
    {
        return $this->getField($position);
    }
}
