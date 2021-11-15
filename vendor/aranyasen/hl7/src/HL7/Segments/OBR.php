<?php

namespace Aranyasen\HL7\Segments;

use Aranyasen\HL7\Segment;

/**
 * OBR segment class
 * Ref: https://corepointhealth.com/resource-center/hl7-resources/hl7-obr-segment
 */
class OBR extends Segment
{
    /**
     * Index of this segment. Incremented for every new segment of this class created
     * @var int
     */
    protected static $setId = 1;

    public function __construct(array $fields = null, bool $autoIncrementIndices = true)
    {
        parent::__construct('OBR', $fields);
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

    public function setPlacerOrderNumber($value, int $position = 2)
    {
        return $this->setField($position, $value);
    }

    public function setFillerOrderNumber($value, int $position = 3)
    {
        return $this->setField($position, $value);
    }

    public function setUniversalServiceID($value, int $position = 4)
    {
        return $this->setField($position, $value);
    }

    public function setPriority($value, int $position = 5)
    {
        return $this->setField($position, $value);
    }

    public function setRequestedDatetime($value, int $position = 6)
    {
        return $this->setField($position, $value);
    }

    public function setObservationDateTime($value, int $position = 7)
    {
        return $this->setField($position, $value);
    }

    public function setObservationEndDateTime($value, int $position = 8)
    {
        return $this->setField($position, $value);
    }

    public function setCollectionVolume($value, int $position = 9)
    {
        return $this->setField($position, $value);
    }

    public function setCollectorIdentifier($value, int $position = 10)
    {
        return $this->setField($position, $value);
    }

    public function setSpecimenActionCode($value, int $position = 11)
    {
        return $this->setField($position, $value);
    }

    public function setDangerCode($value, int $position = 12)
    {
        return $this->setField($position, $value);
    }

    public function setRelevantClinicalInfo($value, int $position = 13)
    {
        return $this->setField($position, $value);
    }

    public function setSpecimenReceivedDateTime($value, int $position = 14)
    {
        return $this->setField($position, $value);
    }

    public function setSpecimenSource($value, int $position = 15)
    {
        return $this->setField($position, $value);
    }

    public function setOrderingProvider($value, int $position = 16)
    {
        return $this->setField($position, $value);
    }

    public function setOrderCallbackPhoneNumber($value, int $position = 17)
    {
        return $this->setField($position, $value);
    }

    public function setPlacerfield1($value, int $position = 18)
    {
        return $this->setField($position, $value);
    }

    public function setPlacerfield2($value, int $position = 19)
    {
        return $this->setField($position, $value);
    }

    public function setFillerField1($value, int $position = 20)
    {
        return $this->setField($position, $value);
    }

    public function setFillerField2($value, int $position = 21)
    {
        return $this->setField($position, $value);
    }

    /**
     * This field specifies the date/time when the results were reported or status changed. This field is used to
     * indicate the date and time that the results are composed into a report and released, or that a status, as
     * defined in ORC-5 order status, is entered or changed. (This is a results field only.) When other applications
     * (such as office or clinical database applications) query the laboratory application for untransmitted results,
     * the information in this field may be used to control processing on the communications link. Usually, the
     * ordering service would want only those results for which the reporting date/time is greater than the date/time
     * the inquiring application last received results.
     *
     * @param $value
     * @param int $position
     * @return bool
     */
    public function setResultsRptStatusChngDateTime($value, int $position = 22)
    {
        return $this->setField($position, $value);
    }

    public function setChargetoPractice($value, int $position = 23)
    {
        return $this->setField($position, $value);
    }

    public function setDiagnosticServSectID($value, int $position = 24)
    {
        return $this->setField($position, $value);
    }

    public function setResultStatus($value, int $position = 25)
    {
        return $this->setField($position, $value);
    }

    public function setParentResult($value, int $position = 26)
    {
        return $this->setField($position, $value);
    }

    public function setQuantityTiming($value, int $position = 27)
    {
        return $this->setField($position, $value);
    }

    public function setResultCopiesTo($value, int $position = 28)
    {
        return $this->setField($position, $value);
    }

    public function setParent($value, int $position = 29)
    {
        return $this->setField($position, $value);
    }

    public function setTransportationMode($value, int $position = 30)
    {
        return $this->setField($position, $value);
    }

    public function setReasonforStudy($value, int $position = 31)
    {
        return $this->setField($position, $value);
    }

    public function setPrincipalResultInterpreter($value, int $position = 32)
    {
        return $this->setField($position, $value);
    }

    public function setAssistantResultInterpreter($value, int $position = 33)
    {
        return $this->setField($position, $value);
    }

    public function setTechnician($value, int $position = 34)
    {
        return $this->setField($position, $value);
    }

    public function setTranscriptionist($value, int $position = 35)
    {
        return $this->setField($position, $value);
    }

    public function setScheduledDateTime($value, int $position = 36)
    {
        return $this->setField($position, $value);
    }

    public function setNumberofSampleContainers($value, int $position = 37)
    {
        return $this->setField($position, $value);
    }

    public function setTransportLogisticsofCollectedSample($value, int $position = 38)
    {
        return $this->setField($position, $value);
    }

    public function setCollectorsComment($value, int $position = 39)
    {
        return $this->setField($position, $value);
    }

    public function setTransportArrangementResponsibility($value, int $position = 40)
    {
        return $this->setField($position, $value);
    }

    public function setTransportArranged($value, int $position = 41)
    {
        return $this->setField($position, $value);
    }

    public function setEscortRequired($value, int $position = 42)
    {
        return $this->setField($position, $value);
    }

    public function setPlannedPatientTransportComment($value, int $position = 43)
    {
        return $this->setField($position, $value);
    }

    public function getID(int $position = 1)
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

    public function getUniversalServiceID(int $position = 4)
    {
        return $this->getField($position);
    }

    public function getPriority(int $position = 5)
    {
        return $this->getField($position);
    }

    public function getRequestedDatetime(int $position = 6)
    {
        return $this->getField($position);
    }

    public function getObservationDateTime(int $position = 7)
    {
        return $this->getField($position);
    }

    public function getObservationEndDateTime(int $position = 8)
    {
        return $this->getField($position);
    }

    public function getCollectionVolume(int $position = 9)
    {
        return $this->getField($position);
    }

    public function getCollectorIdentifier(int $position = 10)
    {
        return $this->getField($position);
    }

    public function getSpecimenActionCode(int $position = 11)
    {
        return $this->getField($position);
    }

    public function getDangerCode(int $position = 12)
    {
        return $this->getField($position);
    }

    public function getRelevantClinicalInfo(int $position = 13)
    {
        return $this->getField($position);
    }

    public function getSpecimenReceivedDateTime(int $position = 14)
    {
        return $this->getField($position);
    }

    public function getSpecimenSource(int $position = 15)
    {
        return $this->getField($position);
    }

    public function getOrderingProvider(int $position = 16)
    {
        return $this->getField($position);
    }

    public function getOrderCallbackPhoneNumber(int $position = 17)
    {
        return $this->getField($position);
    }

    public function getPlacerfield1(int $position = 18)
    {
        return $this->getField($position);
    }

    public function getPlacerfield2(int $position = 19)
    {
        return $this->getField($position);
    }

    public function getFillerField1(int $position = 20)
    {
        return $this->getField($position);
    }

    public function getFillerField2(int $position = 21)
    {
        return $this->getField($position);
    }

    public function getResultsRptStatusChngDateTime(int $position = 22)
    {
        return $this->getField($position);
    }

    public function getChargetoPractice(int $position = 23)
    {
        return $this->getField($position);
    }

    public function getDiagnosticServSectID(int $position = 24)
    {
        return $this->getField($position);
    }

    public function getResultStatus(int $position = 25)
    {
        return $this->getField($position);
    }

    public function getParentResult(int $position = 26)
    {
        return $this->getField($position);
    }

    public function getQuantityTiming(int $position = 27)
    {
        return $this->getField($position);
    }

    public function getResultCopiesTo(int $position = 28)
    {
        return $this->getField($position);
    }

    public function getParent(int $position = 29)
    {
        return $this->getField($position);
    }

    public function getTransportationMode(int $position = 30)
    {
        return $this->getField($position);
    }

    public function getReasonforStudy(int $position = 31)
    {
        return $this->getField($position);
    }

    public function getPrincipalResultInterpreter(int $position = 32)
    {
        return $this->getField($position);
    }

    public function getAssistantResultInterpreter(int $position = 33)
    {
        return $this->getField($position);
    }

    public function getTechnician(int $position = 34)
    {
        return $this->getField($position);
    }

    public function getTranscriptionist(int $position = 35)
    {
        return $this->getField($position);
    }

    public function getScheduledDateTime(int $position = 36)
    {
        return $this->getField($position);
    }

    public function getNumberofSampleContainers(int $position = 37)
    {
        return $this->getField($position);
    }

    public function getTransportLogisticsofCollectedSample(int $position = 38)
    {
        return $this->getField($position);
    }

    public function getCollectorsComment(int $position = 39)
    {
        return $this->getField($position);
    }

    public function getTransportArrangementResponsibility(int $position = 40)
    {
        return $this->getField($position);
    }

    public function getTransportArranged(int $position = 41)
    {
        return $this->getField($position);
    }

    public function getEscortRequired(int $position = 42)
    {
        return $this->getField($position);
    }

    public function getPlannedPatientTransportComment(int $position = 43)
    {
        return $this->getField($position);
    }
}
