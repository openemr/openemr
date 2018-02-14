<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRTiming;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement;

/**
 * Specifies an event that may occur multiple times. Timing schedules are used to record when things are planned, expected or requested to occur. The most common usage is in dosage instructions for medications. They are also used when planning care of various kinds, and may be used for reporting the schedule to which past regular activities were carried out.
 * If the element is present, it must have a value for at least one of the defined elements, an @id referenced from the Narrative, or extensions
 */
class FHIRTimingRepeat extends FHIRElement implements \JsonSerializable
{
    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRQuantity\FHIRDuration
     */
    public $boundsDuration = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRRange
     */
    public $boundsRange = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRPeriod
     */
    public $boundsPeriod = null;

    /**
     * A total count of the desired number of repetitions.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRInteger
     */
    public $count = null;

    /**
     * A maximum value for the count of the desired repetitions (e.g. do something 6-8 times).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRInteger
     */
    public $countMax = null;

    /**
     * How long this thing happens for when it happens.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public $duration = null;

    /**
     * The upper limit of how long this thing happens for when it happens.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public $durationMax = null;

    /**
     * The units of time for the duration, in UCUM units.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRUnitsOfTime
     */
    public $durationUnit = null;

    /**
     * The number of times to repeat the action within the specified period / period range (i.e. both period and periodMax provided).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRInteger
     */
    public $frequency = null;

    /**
     * If present, indicates that the frequency is a range - so to repeat between [frequency] and [frequencyMax] times within the period or period range.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRInteger
     */
    public $frequencyMax = null;

    /**
     * Indicates the duration of time over which repetitions are to occur; e.g. to express "3 times per day", 3 would be the frequency and "1 day" would be the period.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public $period = null;

    /**
     * If present, indicates that the period is a range from [period] to [periodMax], allowing expressing concepts such as "do this once every 3-5 days.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public $periodMax = null;

    /**
     * The units of time for the period in UCUM units.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRUnitsOfTime
     */
    public $periodUnit = null;

    /**
     * If one or more days of week is provided, then the action happens only on the specified day(s).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCode[]
     */
    public $dayOfWeek = [];

    /**
     * Specified time of day for action to take place.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRTime[]
     */
    public $timeOfDay = [];

    /**
     * Real world events that the occurrence of the event should be tied to.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIREventTiming[]
     */
    public $when = [];

    /**
     * The number of minutes from the event. If the event code does not indicate whether the minutes is before or after the event, then the offset is assumed to be after the event.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRUnsignedInt
     */
    public $offset = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Timing.Repeat';

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRQuantity\FHIRDuration
     */
    public function getBoundsDuration()
    {
        return $this->boundsDuration;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRQuantity\FHIRDuration $boundsDuration
     * @return $this
     */
    public function setBoundsDuration($boundsDuration)
    {
        $this->boundsDuration = $boundsDuration;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRRange
     */
    public function getBoundsRange()
    {
        return $this->boundsRange;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRRange $boundsRange
     * @return $this
     */
    public function setBoundsRange($boundsRange)
    {
        $this->boundsRange = $boundsRange;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRPeriod
     */
    public function getBoundsPeriod()
    {
        return $this->boundsPeriod;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRPeriod $boundsPeriod
     * @return $this
     */
    public function setBoundsPeriod($boundsPeriod)
    {
        $this->boundsPeriod = $boundsPeriod;
        return $this;
    }

    /**
     * A total count of the desired number of repetitions.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRInteger
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * A total count of the desired number of repetitions.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRInteger $count
     * @return $this
     */
    public function setCount($count)
    {
        $this->count = $count;
        return $this;
    }

    /**
     * A maximum value for the count of the desired repetitions (e.g. do something 6-8 times).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRInteger
     */
    public function getCountMax()
    {
        return $this->countMax;
    }

    /**
     * A maximum value for the count of the desired repetitions (e.g. do something 6-8 times).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRInteger $countMax
     * @return $this
     */
    public function setCountMax($countMax)
    {
        $this->countMax = $countMax;
        return $this;
    }

    /**
     * How long this thing happens for when it happens.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * How long this thing happens for when it happens.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDecimal $duration
     * @return $this
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;
        return $this;
    }

    /**
     * The upper limit of how long this thing happens for when it happens.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public function getDurationMax()
    {
        return $this->durationMax;
    }

    /**
     * The upper limit of how long this thing happens for when it happens.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDecimal $durationMax
     * @return $this
     */
    public function setDurationMax($durationMax)
    {
        $this->durationMax = $durationMax;
        return $this;
    }

    /**
     * The units of time for the duration, in UCUM units.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRUnitsOfTime
     */
    public function getDurationUnit()
    {
        return $this->durationUnit;
    }

    /**
     * The units of time for the duration, in UCUM units.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRUnitsOfTime $durationUnit
     * @return $this
     */
    public function setDurationUnit($durationUnit)
    {
        $this->durationUnit = $durationUnit;
        return $this;
    }

    /**
     * The number of times to repeat the action within the specified period / period range (i.e. both period and periodMax provided).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRInteger
     */
    public function getFrequency()
    {
        return $this->frequency;
    }

    /**
     * The number of times to repeat the action within the specified period / period range (i.e. both period and periodMax provided).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRInteger $frequency
     * @return $this
     */
    public function setFrequency($frequency)
    {
        $this->frequency = $frequency;
        return $this;
    }

    /**
     * If present, indicates that the frequency is a range - so to repeat between [frequency] and [frequencyMax] times within the period or period range.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRInteger
     */
    public function getFrequencyMax()
    {
        return $this->frequencyMax;
    }

    /**
     * If present, indicates that the frequency is a range - so to repeat between [frequency] and [frequencyMax] times within the period or period range.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRInteger $frequencyMax
     * @return $this
     */
    public function setFrequencyMax($frequencyMax)
    {
        $this->frequencyMax = $frequencyMax;
        return $this;
    }

    /**
     * Indicates the duration of time over which repetitions are to occur; e.g. to express "3 times per day", 3 would be the frequency and "1 day" would be the period.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public function getPeriod()
    {
        return $this->period;
    }

    /**
     * Indicates the duration of time over which repetitions are to occur; e.g. to express "3 times per day", 3 would be the frequency and "1 day" would be the period.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDecimal $period
     * @return $this
     */
    public function setPeriod($period)
    {
        $this->period = $period;
        return $this;
    }

    /**
     * If present, indicates that the period is a range from [period] to [periodMax], allowing expressing concepts such as "do this once every 3-5 days.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public function getPeriodMax()
    {
        return $this->periodMax;
    }

    /**
     * If present, indicates that the period is a range from [period] to [periodMax], allowing expressing concepts such as "do this once every 3-5 days.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDecimal $periodMax
     * @return $this
     */
    public function setPeriodMax($periodMax)
    {
        $this->periodMax = $periodMax;
        return $this;
    }

    /**
     * The units of time for the period in UCUM units.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRUnitsOfTime
     */
    public function getPeriodUnit()
    {
        return $this->periodUnit;
    }

    /**
     * The units of time for the period in UCUM units.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRUnitsOfTime $periodUnit
     * @return $this
     */
    public function setPeriodUnit($periodUnit)
    {
        $this->periodUnit = $periodUnit;
        return $this;
    }

    /**
     * If one or more days of week is provided, then the action happens only on the specified day(s).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCode[]
     */
    public function getDayOfWeek()
    {
        return $this->dayOfWeek;
    }

    /**
     * If one or more days of week is provided, then the action happens only on the specified day(s).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCode $dayOfWeek
     * @return $this
     */
    public function addDayOfWeek($dayOfWeek)
    {
        $this->dayOfWeek[] = $dayOfWeek;
        return $this;
    }

    /**
     * Specified time of day for action to take place.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRTime[]
     */
    public function getTimeOfDay()
    {
        return $this->timeOfDay;
    }

    /**
     * Specified time of day for action to take place.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRTime $timeOfDay
     * @return $this
     */
    public function addTimeOfDay($timeOfDay)
    {
        $this->timeOfDay[] = $timeOfDay;
        return $this;
    }

    /**
     * Real world events that the occurrence of the event should be tied to.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIREventTiming[]
     */
    public function getWhen()
    {
        return $this->when;
    }

    /**
     * Real world events that the occurrence of the event should be tied to.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIREventTiming $when
     * @return $this
     */
    public function addWhen($when)
    {
        $this->when[] = $when;
        return $this;
    }

    /**
     * The number of minutes from the event. If the event code does not indicate whether the minutes is before or after the event, then the offset is assumed to be after the event.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRUnsignedInt
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * The number of minutes from the event. If the event code does not indicate whether the minutes is before or after the event, then the offset is assumed to be after the event.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRUnsignedInt $offset
     * @return $this
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * @return string
     */
    public function get_fhirElementName()
    {
        return $this->_fhirElementName;
    }

    /**
     * @param mixed $data
     */
    public function __construct($data = [])
    {
        if (is_array($data)) {
            if (isset($data['boundsDuration'])) {
                $this->setBoundsDuration($data['boundsDuration']);
            }
            if (isset($data['boundsRange'])) {
                $this->setBoundsRange($data['boundsRange']);
            }
            if (isset($data['boundsPeriod'])) {
                $this->setBoundsPeriod($data['boundsPeriod']);
            }
            if (isset($data['count'])) {
                $this->setCount($data['count']);
            }
            if (isset($data['countMax'])) {
                $this->setCountMax($data['countMax']);
            }
            if (isset($data['duration'])) {
                $this->setDuration($data['duration']);
            }
            if (isset($data['durationMax'])) {
                $this->setDurationMax($data['durationMax']);
            }
            if (isset($data['durationUnit'])) {
                $this->setDurationUnit($data['durationUnit']);
            }
            if (isset($data['frequency'])) {
                $this->setFrequency($data['frequency']);
            }
            if (isset($data['frequencyMax'])) {
                $this->setFrequencyMax($data['frequencyMax']);
            }
            if (isset($data['period'])) {
                $this->setPeriod($data['period']);
            }
            if (isset($data['periodMax'])) {
                $this->setPeriodMax($data['periodMax']);
            }
            if (isset($data['periodUnit'])) {
                $this->setPeriodUnit($data['periodUnit']);
            }
            if (isset($data['dayOfWeek'])) {
                if (is_array($data['dayOfWeek'])) {
                    foreach ($data['dayOfWeek'] as $d) {
                        $this->addDayOfWeek($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"dayOfWeek" must be array of objects or null, '.gettype($data['dayOfWeek']).' seen.');
                }
            }
            if (isset($data['timeOfDay'])) {
                if (is_array($data['timeOfDay'])) {
                    foreach ($data['timeOfDay'] as $d) {
                        $this->addTimeOfDay($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"timeOfDay" must be array of objects or null, '.gettype($data['timeOfDay']).' seen.');
                }
            }
            if (isset($data['when'])) {
                if (is_array($data['when'])) {
                    foreach ($data['when'] as $d) {
                        $this->addWhen($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"when" must be array of objects or null, '.gettype($data['when']).' seen.');
                }
            }
            if (isset($data['offset'])) {
                $this->setOffset($data['offset']);
            }
        } else if (null !== $data) {
            throw new \InvalidArgumentException('$data expected to be array of values, saw "'.gettype($data).'"');
        }
        parent::__construct($data);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->get_fhirElementName();
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $json = parent::jsonSerialize();
        if (isset($this->boundsDuration)) {
            $json['boundsDuration'] = $this->boundsDuration;
        }
        if (isset($this->boundsRange)) {
            $json['boundsRange'] = $this->boundsRange;
        }
        if (isset($this->boundsPeriod)) {
            $json['boundsPeriod'] = $this->boundsPeriod;
        }
        if (isset($this->count)) {
            $json['count'] = $this->count;
        }
        if (isset($this->countMax)) {
            $json['countMax'] = $this->countMax;
        }
        if (isset($this->duration)) {
            $json['duration'] = $this->duration;
        }
        if (isset($this->durationMax)) {
            $json['durationMax'] = $this->durationMax;
        }
        if (isset($this->durationUnit)) {
            $json['durationUnit'] = $this->durationUnit;
        }
        if (isset($this->frequency)) {
            $json['frequency'] = $this->frequency;
        }
        if (isset($this->frequencyMax)) {
            $json['frequencyMax'] = $this->frequencyMax;
        }
        if (isset($this->period)) {
            $json['period'] = $this->period;
        }
        if (isset($this->periodMax)) {
            $json['periodMax'] = $this->periodMax;
        }
        if (isset($this->periodUnit)) {
            $json['periodUnit'] = $this->periodUnit;
        }
        if (0 < count($this->dayOfWeek)) {
            $json['dayOfWeek'] = [];
            foreach ($this->dayOfWeek as $dayOfWeek) {
                $json['dayOfWeek'][] = $dayOfWeek;
            }
        }
        if (0 < count($this->timeOfDay)) {
            $json['timeOfDay'] = [];
            foreach ($this->timeOfDay as $timeOfDay) {
                $json['timeOfDay'][] = $timeOfDay;
            }
        }
        if (0 < count($this->when)) {
            $json['when'] = [];
            foreach ($this->when as $when) {
                $json['when'][] = $when;
            }
        }
        if (isset($this->offset)) {
            $json['offset'] = $this->offset;
        }
        return $json;
    }

    /**
     * @param boolean $returnSXE
     * @param \SimpleXMLElement $sxe
     * @return string|\SimpleXMLElement
     */
    public function xmlSerialize($returnSXE = false, $sxe = null)
    {
        if (null === $sxe) {
            $sxe = new \SimpleXMLElement('<TimingRepeat xmlns="http://hl7.org/fhir"></TimingRepeat>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->boundsDuration)) {
            $this->boundsDuration->xmlSerialize(true, $sxe->addChild('boundsDuration'));
        }
        if (isset($this->boundsRange)) {
            $this->boundsRange->xmlSerialize(true, $sxe->addChild('boundsRange'));
        }
        if (isset($this->boundsPeriod)) {
            $this->boundsPeriod->xmlSerialize(true, $sxe->addChild('boundsPeriod'));
        }
        if (isset($this->count)) {
            $this->count->xmlSerialize(true, $sxe->addChild('count'));
        }
        if (isset($this->countMax)) {
            $this->countMax->xmlSerialize(true, $sxe->addChild('countMax'));
        }
        if (isset($this->duration)) {
            $this->duration->xmlSerialize(true, $sxe->addChild('duration'));
        }
        if (isset($this->durationMax)) {
            $this->durationMax->xmlSerialize(true, $sxe->addChild('durationMax'));
        }
        if (isset($this->durationUnit)) {
            $this->durationUnit->xmlSerialize(true, $sxe->addChild('durationUnit'));
        }
        if (isset($this->frequency)) {
            $this->frequency->xmlSerialize(true, $sxe->addChild('frequency'));
        }
        if (isset($this->frequencyMax)) {
            $this->frequencyMax->xmlSerialize(true, $sxe->addChild('frequencyMax'));
        }
        if (isset($this->period)) {
            $this->period->xmlSerialize(true, $sxe->addChild('period'));
        }
        if (isset($this->periodMax)) {
            $this->periodMax->xmlSerialize(true, $sxe->addChild('periodMax'));
        }
        if (isset($this->periodUnit)) {
            $this->periodUnit->xmlSerialize(true, $sxe->addChild('periodUnit'));
        }
        if (0 < count($this->dayOfWeek)) {
            foreach ($this->dayOfWeek as $dayOfWeek) {
                $dayOfWeek->xmlSerialize(true, $sxe->addChild('dayOfWeek'));
            }
        }
        if (0 < count($this->timeOfDay)) {
            foreach ($this->timeOfDay as $timeOfDay) {
                $timeOfDay->xmlSerialize(true, $sxe->addChild('timeOfDay'));
            }
        }
        if (0 < count($this->when)) {
            foreach ($this->when as $when) {
                $when->xmlSerialize(true, $sxe->addChild('when'));
            }
        }
        if (isset($this->offset)) {
            $this->offset->xmlSerialize(true, $sxe->addChild('offset'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
