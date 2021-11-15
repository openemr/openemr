<?php

namespace Aranyasen\HL7\Segments;

use Aranyasen\HL7\Segment;

/**
 * SAC segment class
 * Ref: https://www.interfaceware.com/hl7-standard/hl7-segment-SAC.html
 */
class SAC extends Segment
{
    /**
     * Index of this segment. Incremented for every new segment of this class created
     * @var int
     */
    protected static $setId = 1;

    public function __construct(array $fields = null)
    {
        parent::__construct('SAC', $fields);
    }

    /**
     * Reset index of this segment
     * @param int $index
     */
    public static function resetIndex(int $index = 1): void
    {
        self::$setId = $index;
    }

    public function setExternalAccessionIdentifier(int $value, int $position = 1)
    {
        return $this->setField($position, $value);
    }

    public function setAccessionIdentifier($value, int $position = 2)
    {
        return $this->setField($position, $value);
    }

    public function setContainerIdentifier($value, int $position = 3)
    {
        return $this->setField($position, $value);
    }

    public function setPrimaryContainerIdentifier($value, int $position = 4)
    {
        return $this->setField($position, $value);
    }

    public function setEquipmentContainerIdentifier($value, int $position = 5)
    {
        return $this->setField($position, $value);
    }

    public function setSpecimenSource($value, int $position = 6)
    {
        return $this->setField($position, $value);
    }

    public function setRegistrationDateTime($value, int $position = 7)
    {
        return $this->setField($position, $value);
    }

    /**
     * @param $value
     * @param int $position
     * @return bool
     *
     * value:
     * I :	Identified
     * P :	In Position
     * O :	In Process
     * R :	Process Completed
     * L :	Left Equipment
     * M :	Missing
     * X :	Container Unavailable
     * U :	Unknown
     */
    public function setContainerStatus($value, int $position = 8)
    {
        return $this->setField($position, $value);
    }

    public function setCarrierType($value, int $position = 9)
    {
        return $this->setField($position, $value);
    }

    public function setCarrierIdentifier($value, int $position = 10)
    {
        return $this->setField($position, $value);
    }

    public function setPositionInCarrier($value, int $position = 11)
    {
        return $this->setField($position, $value);
    }

    public function setTrayTypeSAC($value, int $position = 12)
    {
        return $this->setField($position, $value);
    }

    public function setTrayIdentifier($value, int $position = 13)
    {
        return $this->setField($position, $value);
    }

    public function setPositionInTray($value, int $position = 14)
    {
        return $this->setField($position, $value);
    }

    public function setLocation($value, int $position = 15)
    {
        return $this->setField($position, $value);
    }

    public function setContainerHeight($value, int $position = 16)
    {
        return $this->setField($position, $value);
    }

    public function setContainerDiameter($value, int $position = 17)
    {
        return $this->setField($position, $value);
    }

    public function setBarrierDelta($value, int $position = 18)
    {
        return $this->setField($position, $value);
    }

    public function setBottomDelta($value, int $position = 19)
    {
        return $this->setField($position, $value);
    }

    public function setContainerSizeUnits($value, int $position = 20)
    {
        return $this->setField($position, $value);
    }

    public function setContainerVolume($value, int $position = 21)
    {
        return $this->setField($position, $value);
    }

    public function setAvailableSpecimenVolume($value, int $position = 22)
    {
        return $this->setField($position, $value);
    }

    public function setInitialSpecimenVolume($value, int $position = 23)
    {
        return $this->setField($position, $value);
    }

    public function setVolumeUnits($value, int $position = 24)
    {
        return $this->setField($position, $value);
    }

    public function setSeparatorType($value, int $position = 25)
    {
        return $this->setField($position, $value);
    }

    public function setCapType($value, int $position = 26)
    {
        return $this->setField($position, $value);
    }

    public function setAdditive($value, int $position = 27)
    {
        return $this->setField($position, $value);
    }

    public function setSpecimenComponent($value, int $position = 28)
    {
        return $this->setField($position, $value);
    }

    public function setDilutionFactor($value, int $position = 29)
    {
        return $this->setField($position, $value);
    }

    public function setTreatment($value, int $position = 30)
    {
        return $this->setField($position, $value);
    }

    public function setTemperature($value, int $position = 31)
    {
        return $this->setField($position, $value);
    }

    public function setHemolysisIndex($value, int $position = 32)
    {
        return $this->setField($position, $value);
    }

    public function setHemolysisIndexUnits($value, int $position = 33)
    {
        return $this->setField($position, $value);
    }

    public function setLepemiaIndex($value, int $position = 34)
    {
        return $this->setField($position, $value);
    }

    public function setLepemiaIndexUnits($value, int $position = 35)
    {
        return $this->setField($position, $value);
    }

    public function setIcterusIndex($value, int $position = 36)
    {
        return $this->setField($position, $value);
    }

    public function setIcterusIndexUnits($value, int $position = 37)
    {
        return $this->setField($position, $value);
    }

    public function setFibrinIndex($value, int $position = 38)
    {
        return $this->setField($position, $value);
    }

    public function setFibrinIndexUnits($value, int $position = 39)
    {
        return $this->setField($position, $value);
    }

    public function setSystemInducedContaminants($value, int $position = 40)
    {
        return $this->setField($position, $value);
    }

    public function setDrugInterference($value, int $position = 41)
    {
        return $this->setField($position, $value);
    }

    public function setArtificialBlood($value, int $position = 42)
    {
        return $this->setField($position, $value);
    }

    public function setSpecialHandlingCode($value, int $position = 43)
    {
        return $this->setField($position, $value);
    }

    public function setOtherEnvironmentalFactors($value, int $position = 44)
    {
        return $this->setField($position, $value);
    }

    public function getExternalAccessionIdentifier(int $position = 1)
    {
        return $this->getField($position);
    }

    public function getAccessionIdentifier(int $position = 2)
    {
        return $this->getField($position);
    }

    public function getContainerIdentifier(int $position = 3)
    {
        return $this->getField($position);
    }

    public function getPrimaryContainerIdentifier(int $position = 4)
    {
        return $this->getField($position);
    }

    public function getEquipmentContainerIdentifier(int $position = 5)
    {
        return $this->getField($position);
    }

    public function getSpecimenSource(int $position = 6)
    {
        return $this->getField($position);
    }

    public function getRegistrationDateTime(int $position = 7)
    {
        return $this->getField($position);
    }

    public function getContainerStatus(int $position = 8)
    {
        return $this->getField($position);
    }

    public function getCarrierType(int $position = 9)
    {
        return $this->getField($position);
    }

    public function getCarrierIdentifier(int $position = 10)
    {
        return $this->getField($position);
    }

    public function getPositionInCarrier(int $position = 11)
    {
        return $this->getField($position);
    }

    public function getTrayTypeSAC(int $position = 12)
    {
        return $this->getField($position);
    }

    public function getTrayIdentifier(int $position = 13)
    {
        return $this->getField($position);
    }

    public function getPositionInTray(int $position = 14)
    {
        return $this->getField($position);
    }

    public function getLocation(int $position = 15)
    {
        return $this->getField($position);
    }

    public function getContainerHeight(int $position = 16)
    {
        return $this->getField($position);
    }

    public function getContainerDiameter(int $position = 17)
    {
        return $this->getField($position);
    }

    public function getBarrierDelta(int $position = 18)
    {
        return $this->getField($position);
    }

    public function getBottomDelta(int $position = 19)
    {
        return $this->getField($position);
    }

    public function getContainerSizeUnits(int $position = 20)
    {
        return $this->getField($position);
    }

    public function getContainerVolume(int $position = 21)
    {
        return $this->getField($position);
    }

    public function getAvailableSpecimenVolume(int $position = 22)
    {
        return $this->getField($position);
    }

    public function getInitialSpecimenVolume(int $position = 23)
    {
        return $this->getField($position);
    }

    public function getVolumeUnits(int $position = 24)
    {
        return $this->getField($position);
    }

    public function getSeparatorType(int $position = 25)
    {
        return $this->getField($position);
    }

    public function getCapType(int $position = 26)
    {
        return $this->getField($position);
    }

    public function getAdditive(int $position = 27)
    {
        return $this->getField($position);
    }

    public function getSpecimenComponent(int $position = 28)
    {
        return $this->getField($position);
    }

    public function getDilutionFactor(int $position = 29)
    {
        return $this->getField($position);
    }

    public function getTreatment(int $position = 30)
    {
        return $this->getField($position);
    }

    public function getTemperature(int $position = 31)
    {
        return $this->getField($position);
    }

    public function getHemolysisIndex(int $position = 32)
    {
        return $this->getField($position);
    }

    public function getHemolysisIndexUnits(int $position = 33)
    {
        return $this->getField($position);
    }

    public function getLepemiaIndex(int $position = 34)
    {
        return $this->getField($position);
    }

    public function getLepemiaIndexUnits(int $position = 35)
    {
        return $this->getField($position);
    }

    public function getIcterusIndex(int $position = 36)
    {
        return $this->getField($position);
    }

    public function getIcterusIndexUnits(int $position = 37)
    {
        return $this->getField($position);
    }

    public function getFibrinIndex(int $position = 38)
    {
        return $this->getField($position);
    }

    public function getFibrinIndexUnits(int $position = 39)
    {
        return $this->getField($position);
    }

    public function getSystemInducedContaminants(int $position = 40)
    {
        return $this->getField($position);
    }

    public function getDrugInterference(int $position = 41)
    {
        return $this->getField($position);
    }

    public function getArtificialBlood(int $position = 42)
    {
        return $this->getField($position);
    }

    public function getSpecialHandlingCode(int $position = 43)
    {
        return $this->getField($position);
    }

    public function getOtherEnvironmentalFactors(int $position = 44)
    {
        return $this->getField($position);
    }
}
