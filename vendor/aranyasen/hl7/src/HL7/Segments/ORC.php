<?php

namespace Aranyasen\HL7\Segments;

use Aranyasen\HL7\Segment;

/**
 * ORC segment class
 * Ref: http://hl7-definition.caristix.com:9010/Default.aspx?version=HL7%20v2.5.1&segment=ORC
 */
class ORC extends Segment
{
    public function __construct(array $fields = null)
    {
        parent::__construct('ORC', $fields);
    }

    public function setOrderControl($value, int $position = 1)
    {
        return $this->setField($position, $value);
    }

    public function setPlacerOrderNumber($value, int $position = 2)
    {
        return $this->setField($position, $value);
    }

    public function setFillerOrderNumber($value, int $position = 3)
    {
        return $this->setField($position, $value);
    }

    public function setPlacerGroupNumber($value, int $position = 4)
    {
        return $this->setField($position, $value);
    }

    public function setOrderStatus($value, int $position = 5)
    {
        return $this->setField($position, $value);
    }

    public function setResponseFlag($value, int $position = 6)
    {
        return $this->setField($position, $value);
    }

    public function setQuantityTiming($value, int $position = 7)
    {
        return $this->setField($position, $value);
    }

    public function setParentOrder($value, int $position = 8)
    {
        return $this->setField($position, $value);
    }

    public function setDateTimeofTransaction($value, int $position = 9)
    {
        return $this->setField($position, $value);
    }

    public function setEnteredBy($value, int $position = 10)
    {
        return $this->setField($position, $value);
    }

    public function setVerifiedBy($value, int $position = 11)
    {
        return $this->setField($position, $value);
    }

    public function setOrderingProvider($value, int $position = 12)
    {
        return $this->setField($position, $value);
    }

    public function setEnterersLocation($value, int $position = 13)
    {
        return $this->setField($position, $value);
    }

    public function setCallBackPhoneNumber($value, int $position = 14)
    {
        return $this->setField($position, $value);
    }

    public function setOrderEffectiveDateTime($value, int $position = 15)
    {
        return $this->setField($position, $value);
    }

    public function setOrderControlCodeReason($value, int $position = 16)
    {
        return $this->setField($position, $value);
    }

    public function setEnteringOrganization($value, int $position = 17)
    {
        return $this->setField($position, $value);
    }

    public function setEnteringDevice($value, int $position = 18)
    {
        return $this->setField($position, $value);
    }

    public function setActionBy($value, int $position = 19)
    {
        return $this->setField($position, $value);
    }

    public function setAdvancedBeneficiaryNoticeCode($value, int $position = 20)
    {
        return $this->setField($position, $value);
    }

    public function setOrderingFacilityName($value, int $position = 21)
    {
        return $this->setField($position, $value);
    }

    public function setOrderingFacilityAddress($value, int $position = 22)
    {
        return $this->setField($position, $value);
    }

    public function setOrderingFacilityPhoneNumber($value, int $position = 23)
    {
        return $this->setField($position, $value);
    }

    public function setOrderingProviderAddress($value, int $position = 24)
    {
        return $this->setField($position, $value);
    }

    public function setOrderStatusModifier($value, int $position = 25)
    {
        return $this->setField($position, $value);
    }

    public function setAdvancedBeneficiaryNoticeOverrideReason($value, int $position = 26)
    {
        return $this->setField($position, $value);
    }

    public function setFillersExpectedAvailabilityDateTime($value, int $position = 27)
    {
        return $this->setField($position, $value);
    }

    public function setConfidentialityCode($value, int $position = 28)
    {
        return $this->setField($position, $value);
    }

    public function setOrderType($value, int $position = 29)
    {
        return $this->setField($position, $value);
    }

    public function setEntererAuthorizationMode($value, int $position = 30)
    {
        return $this->setField($position, $value);
    }

    public function setParentUniversalServiceIdentifier($value, int $position = 31)
    {
        return $this->setField($position, $value);
    }

    public function getOrderControl(int $position = 1)
    {
        return $this->getField($position);
    }

    public function getPlacerOrderNumber(int $position = 2)
    {
        return $this->getField($position);
    }

    public function getFillerOrderNumber(int $position = 3)
    {
        return $this->getField($position);
    }

    public function getPlacerGroupNumber(int $position = 4)
    {
        return $this->getField($position);
    }

    public function getOrderStatus(int $position = 5)
    {
        return $this->getField($position);
    }

    public function getResponseFlag(int $position = 6)
    {
        return $this->getField($position);
    }

    public function getQuantityTiming(int $position = 7)
    {
        return $this->getField($position);
    }

    public function getParentOrder(int $position = 8)
    {
        return $this->getField($position);
    }

    public function getDateTimeofTransaction(int $position = 9)
    {
        return $this->getField($position);
    }

    public function getEnteredBy(int $position = 10)
    {
        return $this->getField($position);
    }

    public function getVerifiedBy(int $position = 11)
    {
        return $this->getField($position);
    }

    public function getOrderingProvider(int $position = 12)
    {
        return $this->getField($position);
    }

    public function getEnterersLocation(int $position = 13)
    {
        return $this->getField($position);
    }

    public function getCallBackPhoneNumber(int $position = 14)
    {
        return $this->getField($position);
    }

    public function getOrderEffectiveDateTime(int $position = 15)
    {
        return $this->getField($position);
    }

    public function getOrderControlCodeReason(int $position = 16)
    {
        return $this->getField($position);
    }

    public function getEnteringOrganization(int $position = 17)
    {
        return $this->getField($position);
    }

    public function getEnteringDevice(int $position = 18)
    {
        return $this->getField($position);
    }

    public function getActionBy(int $position = 19)
    {
        return $this->getField($position);
    }

    public function getAdvancedBeneficiaryNoticeCode(int $position = 20)
    {
        return $this->getField($position);
    }

    public function getOrderingFacilityName(int $position = 21)
    {
        return $this->getField($position);
    }

    public function getOrderingFacilityAddress(int $position = 22)
    {
        return $this->getField($position);
    }

    public function getOrderingFacilityPhoneNumber(int $position = 23)
    {
        return $this->getField($position);
    }

    public function getOrderingProviderAddress(int $position = 24)
    {
        return $this->getField($position);
    }

    public function getOrderStatusModifier(int $position = 25)
    {
        return $this->getField($position);
    }

    public function getAdvancedBeneficiaryNoticeOverrideReason(int $position = 26)
    {
        return $this->getField($position);
    }

    public function getFillersExpectedAvailabilityDateTime(int $position = 27)
    {
        return $this->getField($position);
    }

    public function getConfidentialityCode(int $position = 28)
    {
        return $this->getField($position);
    }

    public function getOrderType(int $position = 29)
    {
        return $this->getField($position);
    }

    public function getEntererAuthorizationMode(int $position = 30)
    {
        return $this->getField($position);
    }

    public function getParentUniversalServiceIdentifier(int $position = 31)
    {
        return $this->getField($position);
    }
}
