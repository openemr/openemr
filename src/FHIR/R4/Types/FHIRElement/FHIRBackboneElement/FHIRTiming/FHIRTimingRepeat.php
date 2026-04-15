<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTiming;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 *
 * Class creation date: April 15th, 2026 16:02+0000
 *
 * PHPFHIR Copyright:
 *
 * Copyright 2016-2026 Daniel Carbone (daniel.p.carbone@gmail.com)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *        http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 *
 * FHIR Copyright Notice:
 *
 *   Copyright (c) 2011+, HL7, Inc.
 *   All rights reserved.
 *
 *   Redistribution and use in source and binary forms, with or without modification,
 *   are permitted provided that the following conditions are met:
 *
 *    * Redistributions of source code must retain the above copyright notice, this
 *      list of conditions and the following disclaimer.
 *    * Redistributions in binary form must reproduce the above copyright notice,
 *      this list of conditions and the following disclaimer in the documentation
 *      and/or other materials provided with the distribution.
 *    * Neither the name of HL7 nor the names of its contributors may be used to
 *      endorse or promote products derived from this software without specific
 *      prior written permission.
 *
 *   THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 *   ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 *   WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 *   IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
 *   INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT
 *   NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 *   PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
 *   WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 *   ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 *   POSSIBILITY OF SUCH DAMAGE.
 *
 *
 *   Generated on Fri, Nov 1, 2019 09:29+1100 for FHIR v4.0.1
 *
 *   Note: the schemas & schematrons do not contain all of the rules about what makes resources
 *   valid. Implementers will still need to be familiar with the content of the specification and with
 *   any profiles that apply to the resources in order to make a conformant implementation.
 *
 */
use OpenEMR\FHIR\Encoding\JSONSerializationOptionsTrait;
use OpenEMR\FHIR\Encoding\SerializeConfig;
use OpenEMR\FHIR\Encoding\UnserializeConfig;
use OpenEMR\FHIR\Encoding\ValueXMLLocationEnum;
use OpenEMR\FHIR\Encoding\XMLSerializationOptionsTrait;
use OpenEMR\FHIR\Encoding\XMLWriter;
use OpenEMR\FHIR\Types\ElementTypeInterface;
use OpenEMR\FHIR\Validation\TypeValidationsTrait;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIREventTimingList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRUnitsOfTimeList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIREventTiming;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPositiveInt;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRange;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTime;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUnitsOfTime;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUnsignedInt;
use OpenEMR\FHIR\Versions\R4\Types\FHIRPositiveIntPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRTimePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRUnsignedIntPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * Specifies an event that may occur multiple times. Timing schedules are used to
 * record when things are planned, expected or requested to occur. The most common
 * usage is in dosage instructions for medications. They are also used when
 * planning care of various kinds, and may be used for reporting the schedule to
 * which past regular activities were carried out.
 * If the element is present, it must have a value for at least one of the defined
 * elements, an \@id referenced from the Narrative, or extensions
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRTimingRepeat extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_TIMING_DOT_REPEAT;

    /* class_default.php:56 */
    public const FIELD_BOUNDS_DURATION = 'boundsDuration';
    public const FIELD_BOUNDS_RANGE = 'boundsRange';
    public const FIELD_BOUNDS_PERIOD = 'boundsPeriod';
    public const FIELD_COUNT = 'count';
    public const FIELD_COUNT_EXT = '_count';
    public const FIELD_COUNT_MAX = 'countMax';
    public const FIELD_COUNT_MAX_EXT = '_countMax';
    public const FIELD_DURATION = 'duration';
    public const FIELD_DURATION_EXT = '_duration';
    public const FIELD_DURATION_MAX = 'durationMax';
    public const FIELD_DURATION_MAX_EXT = '_durationMax';
    public const FIELD_DURATION_UNIT = 'durationUnit';
    public const FIELD_DURATION_UNIT_EXT = '_durationUnit';
    public const FIELD_FREQUENCY = 'frequency';
    public const FIELD_FREQUENCY_EXT = '_frequency';
    public const FIELD_FREQUENCY_MAX = 'frequencyMax';
    public const FIELD_FREQUENCY_MAX_EXT = '_frequencyMax';
    public const FIELD_PERIOD = 'period';
    public const FIELD_PERIOD_EXT = '_period';
    public const FIELD_PERIOD_MAX = 'periodMax';
    public const FIELD_PERIOD_MAX_EXT = '_periodMax';
    public const FIELD_PERIOD_UNIT = 'periodUnit';
    public const FIELD_PERIOD_UNIT_EXT = '_periodUnit';
    public const FIELD_DAY_OF_WEEK = 'dayOfWeek';
    public const FIELD_DAY_OF_WEEK_EXT = '_dayOfWeek';
    public const FIELD_TIME_OF_DAY = 'timeOfDay';
    public const FIELD_TIME_OF_DAY_EXT = '_timeOfDay';
    public const FIELD_WHEN = 'when';
    public const FIELD_WHEN_EXT = '_when';
    public const FIELD_OFFSET = 'offset';
    public const FIELD_OFFSET_EXT = '_offset';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_COUNT => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_COUNT_MAX => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_DURATION => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_DURATION_MAX => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_DURATION_UNIT => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_FREQUENCY => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_FREQUENCY_MAX => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_PERIOD => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_PERIOD_MAX => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_PERIOD_UNIT => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_OFFSET => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * A length of time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Either a duration for the length of the timing schedule, a range of possible
     * length, or outer bounds for start and/or end limits of the timing schedule.
     * (choose any one of bounds*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration
     */
    #[FHIRDuration]
    protected FHIRDuration $boundsDuration;
    /**
     * A set of ordered Quantities defined by a low and high limit.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Either a duration for the length of the timing schedule, a range of possible
     * length, or outer bounds for start and/or end limits of the timing schedule.
     * (choose any one of bounds*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRange
     */
    #[FHIRRange]
    protected FHIRRange $boundsRange;
    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Either a duration for the length of the timing schedule, a range of possible
     * length, or outer bounds for start and/or end limits of the timing schedule.
     * (choose any one of bounds*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod
     */
    #[FHIRPeriod]
    protected FHIRPeriod $boundsPeriod;
    /**
     * An integer with a value that is positive (e.g. >0)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * A total count of the desired number of repetitions across the duration of the
     * entire timing specification. If countMax is present, this element indicates the
     * lower bound of the allowed range of count values.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPositiveInt
     */
    #[FHIRPositiveInt]
    protected FHIRPositiveInt $count;
    /**
     * An integer with a value that is positive (e.g. >0)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * If present, indicates that the count is a range - so to perform the action
     * between [count] and [countMax] times.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPositiveInt
     */
    #[FHIRPositiveInt]
    protected FHIRPositiveInt $countMax;
    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * How long this thing happens for when it happens. If durationMax is present, this
     * element indicates the lower bound of the allowed range of the duration.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal
     */
    #[FHIRDecimal]
    protected FHIRDecimal $duration;
    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If present, indicates that the duration is a range - so to perform the action
     * between [duration] and [durationMax] time length.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal
     */
    #[FHIRDecimal]
    protected FHIRDecimal $durationMax;
    /**
     * A unit of time (units from UCUM).
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The units of time for the duration, in UCUM units.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUnitsOfTime
     */
    #[FHIRUnitsOfTime]
    protected FHIRUnitsOfTime $durationUnit;
    /**
     * An integer with a value that is positive (e.g. >0)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The number of times to repeat the action within the specified period. If
     * frequencyMax is present, this element indicates the lower bound of the allowed
     * range of the frequency.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPositiveInt
     */
    #[FHIRPositiveInt]
    protected FHIRPositiveInt $frequency;
    /**
     * An integer with a value that is positive (e.g. >0)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * If present, indicates that the frequency is a range - so to repeat between
     * [frequency] and [frequencyMax] times within the period or period range.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPositiveInt
     */
    #[FHIRPositiveInt]
    protected FHIRPositiveInt $frequencyMax;
    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates the duration of time over which repetitions are to occur; e.g. to
     * express "3 times per day", 3 would be the frequency and "1 day" would be the
     * period. If periodMax is present, this element indicates the lower bound of the
     * allowed range of the period length.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal
     */
    #[FHIRDecimal]
    protected FHIRDecimal $period;
    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If present, indicates that the period is a range from [period] to [periodMax],
     * allowing expressing concepts such as "do this once every 3-5 days.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal
     */
    #[FHIRDecimal]
    protected FHIRDecimal $periodMax;
    /**
     * A unit of time (units from UCUM).
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The units of time for the period in UCUM units.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUnitsOfTime
     */
    #[FHIRUnitsOfTime]
    protected FHIRUnitsOfTime $periodUnit;
    /**
     * A string which has at least one character and no leading or trailing whitespace
     * and where there is no whitespace other than single spaces in the contents
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * If one or more days of week is provided, then the action happens only on the
     * specified day(s).
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode>
     */
    #[FHIRCode]
    protected array $dayOfWeek;
    /**
     * A time during the day, with no date specified
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Specified time of day for action to take place.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTime>
     */
    #[FHIRTime]
    protected array $timeOfDay;
    /**
     * Real world event relating to the schedule.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * An approximate time period during the day, potentially linked to an event of
     * daily living that indicates when the action should occur.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIREventTiming>
     */
    #[FHIREventTiming]
    protected array $when;
    /**
     * An integer with a value that is not negative (e.g. >= 0)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The number of minutes from the event. If the event code does not indicate
     * whether the minutes is before or after the event, then the offset is assumed to
     * be after the event.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUnsignedInt
     */
    #[FHIRUnsignedInt]
    protected FHIRUnsignedInt $offset;

    /* constructor.php:61 */
    /**
     * FHIRTimingRepeat Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration $boundsDuration
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRange $boundsRange
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $boundsPeriod
     * @param null|string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRPositiveIntPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPositiveInt $count
     * @param null|string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRPositiveIntPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPositiveInt $countMax
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $duration
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $durationMax
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRUnitsOfTimeList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUnitsOfTime $durationUnit
     * @param null|string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRPositiveIntPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPositiveInt $frequency
     * @param null|string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRPositiveIntPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPositiveInt $frequencyMax
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $period
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $periodMax
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRUnitsOfTimeList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUnitsOfTime $periodUnit
     * @param null|iterable<string>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode> $dayOfWeek
     * @param null|iterable<string>|iterable<\DateTimeInterface>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRTimePrimitive>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTime> $timeOfDay
     * @param null|iterable<string>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIREventTimingList>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIREventTiming> $when
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRUnsignedIntPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUnsignedInt $offset
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|FHIRDuration $boundsDuration = null,
                                null|FHIRRange $boundsRange = null,
                                null|FHIRPeriod $boundsPeriod = null,
                                null|string|float|FHIRPositiveIntPrimitive|FHIRPositiveInt $count = null,
                                null|string|float|FHIRPositiveIntPrimitive|FHIRPositiveInt $countMax = null,
                                null|string|int|float|FHIRDecimalPrimitive|FHIRDecimal $duration = null,
                                null|string|int|float|FHIRDecimalPrimitive|FHIRDecimal $durationMax = null,
                                null|string|FHIRUnitsOfTimeList|FHIRUnitsOfTime $durationUnit = null,
                                null|string|float|FHIRPositiveIntPrimitive|FHIRPositiveInt $frequency = null,
                                null|string|float|FHIRPositiveIntPrimitive|FHIRPositiveInt $frequencyMax = null,
                                null|string|int|float|FHIRDecimalPrimitive|FHIRDecimal $period = null,
                                null|string|int|float|FHIRDecimalPrimitive|FHIRDecimal $periodMax = null,
                                null|string|FHIRUnitsOfTimeList|FHIRUnitsOfTime $periodUnit = null,
                                null|iterable $dayOfWeek = null,
                                null|iterable $timeOfDay = null,
                                null|iterable $when = null,
                                null|string|int|float|FHIRUnsignedIntPrimitive|FHIRUnsignedInt $offset = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $boundsDuration) {
            $this->setBoundsDuration($boundsDuration);
        }
        if (null !== $boundsRange) {
            $this->setBoundsRange($boundsRange);
        }
        if (null !== $boundsPeriod) {
            $this->setBoundsPeriod($boundsPeriod);
        }
        if (null !== $count) {
            $this->setCount($count);
        }
        if (null !== $countMax) {
            $this->setCountMax($countMax);
        }
        if (null !== $duration) {
            $this->setDuration($duration);
        }
        if (null !== $durationMax) {
            $this->setDurationMax($durationMax);
        }
        if (null !== $durationUnit) {
            $this->setDurationUnit($durationUnit);
        }
        if (null !== $frequency) {
            $this->setFrequency($frequency);
        }
        if (null !== $frequencyMax) {
            $this->setFrequencyMax($frequencyMax);
        }
        if (null !== $period) {
            $this->setPeriod($period);
        }
        if (null !== $periodMax) {
            $this->setPeriodMax($periodMax);
        }
        if (null !== $periodUnit) {
            $this->setPeriodUnit($periodUnit);
        }
        if (null !== $dayOfWeek) {
            $this->setDayOfWeek(...$dayOfWeek);
        }
        if (null !== $timeOfDay) {
            $this->setTimeOfDay(...$timeOfDay);
        }
        if (null !== $when) {
            $this->setWhen(...$when);
        }
        if (null !== $offset) {
            $this->setOffset($offset);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * A length of time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Either a duration for the length of the timing schedule, a range of possible
     * length, or outer bounds for start and/or end limits of the timing schedule.
     * (choose any one of bounds*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration
     */
    public function getBoundsDuration(): null|FHIRDuration
    {
        return $this->boundsDuration ?? null;
    }

    /**
     * A length of time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Either a duration for the length of the timing schedule, a range of possible
     * length, or outer bounds for start and/or end limits of the timing schedule.
     * (choose any one of bounds*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration $boundsDuration
     * @return static
     */
    public function setBoundsDuration(null|FHIRDuration $boundsDuration): self
    {
        if (null === $boundsDuration) {
            unset($this->boundsDuration);
            return $this;
        }
        $this->boundsDuration = $boundsDuration;
        return $this;
    }

    /**
     * A set of ordered Quantities defined by a low and high limit.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Either a duration for the length of the timing schedule, a range of possible
     * length, or outer bounds for start and/or end limits of the timing schedule.
     * (choose any one of bounds*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRange
     */
    public function getBoundsRange(): null|FHIRRange
    {
        return $this->boundsRange ?? null;
    }

    /**
     * A set of ordered Quantities defined by a low and high limit.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Either a duration for the length of the timing schedule, a range of possible
     * length, or outer bounds for start and/or end limits of the timing schedule.
     * (choose any one of bounds*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRange $boundsRange
     * @return static
     */
    public function setBoundsRange(null|FHIRRange $boundsRange): self
    {
        if (null === $boundsRange) {
            unset($this->boundsRange);
            return $this;
        }
        $this->boundsRange = $boundsRange;
        return $this;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Either a duration for the length of the timing schedule, a range of possible
     * length, or outer bounds for start and/or end limits of the timing schedule.
     * (choose any one of bounds*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod
     */
    public function getBoundsPeriod(): null|FHIRPeriod
    {
        return $this->boundsPeriod ?? null;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Either a duration for the length of the timing schedule, a range of possible
     * length, or outer bounds for start and/or end limits of the timing schedule.
     * (choose any one of bounds*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $boundsPeriod
     * @return static
     */
    public function setBoundsPeriod(null|FHIRPeriod $boundsPeriod): self
    {
        if (null === $boundsPeriod) {
            unset($this->boundsPeriod);
            return $this;
        }
        $this->boundsPeriod = $boundsPeriod;
        return $this;
    }

    /**
     * An integer with a value that is positive (e.g. >0)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * A total count of the desired number of repetitions across the duration of the
     * entire timing specification. If countMax is present, this element indicates the
     * lower bound of the allowed range of count values.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPositiveInt
     */
    public function getCount(): null|FHIRPositiveInt
    {
        return $this->count ?? null;
    }

    /**
     * An integer with a value that is positive (e.g. >0)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * A total count of the desired number of repetitions across the duration of the
     * entire timing specification. If countMax is present, this element indicates the
     * lower bound of the allowed range of count values.
     *
     * @param null|string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRPositiveIntPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPositiveInt $count
     * @return static
     */
    public function setCount(null|string|float|FHIRPositiveIntPrimitive|FHIRPositiveInt $count): self
    {
        if (null === $count) {
            unset($this->count);
            return $this;
        }
        if (!($count instanceof FHIRPositiveInt)) {
            $count = new FHIRPositiveInt(value: $count);
        }
        $this->count = $count;
        return $this;
    }

    /**
     * An integer with a value that is positive (e.g. >0)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * If present, indicates that the count is a range - so to perform the action
     * between [count] and [countMax] times.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPositiveInt
     */
    public function getCountMax(): null|FHIRPositiveInt
    {
        return $this->countMax ?? null;
    }

    /**
     * An integer with a value that is positive (e.g. >0)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * If present, indicates that the count is a range - so to perform the action
     * between [count] and [countMax] times.
     *
     * @param null|string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRPositiveIntPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPositiveInt $countMax
     * @return static
     */
    public function setCountMax(null|string|float|FHIRPositiveIntPrimitive|FHIRPositiveInt $countMax): self
    {
        if (null === $countMax) {
            unset($this->countMax);
            return $this;
        }
        if (!($countMax instanceof FHIRPositiveInt)) {
            $countMax = new FHIRPositiveInt(value: $countMax);
        }
        $this->countMax = $countMax;
        return $this;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * How long this thing happens for when it happens. If durationMax is present, this
     * element indicates the lower bound of the allowed range of the duration.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal
     */
    public function getDuration(): null|FHIRDecimal
    {
        return $this->duration ?? null;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * How long this thing happens for when it happens. If durationMax is present, this
     * element indicates the lower bound of the allowed range of the duration.
     *
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $duration
     * @return static
     */
    public function setDuration(null|string|int|float|FHIRDecimalPrimitive|FHIRDecimal $duration): self
    {
        if (null === $duration) {
            unset($this->duration);
            return $this;
        }
        if (!($duration instanceof FHIRDecimal)) {
            $duration = new FHIRDecimal(value: $duration);
        }
        $this->duration = $duration;
        return $this;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If present, indicates that the duration is a range - so to perform the action
     * between [duration] and [durationMax] time length.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal
     */
    public function getDurationMax(): null|FHIRDecimal
    {
        return $this->durationMax ?? null;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If present, indicates that the duration is a range - so to perform the action
     * between [duration] and [durationMax] time length.
     *
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $durationMax
     * @return static
     */
    public function setDurationMax(null|string|int|float|FHIRDecimalPrimitive|FHIRDecimal $durationMax): self
    {
        if (null === $durationMax) {
            unset($this->durationMax);
            return $this;
        }
        if (!($durationMax instanceof FHIRDecimal)) {
            $durationMax = new FHIRDecimal(value: $durationMax);
        }
        $this->durationMax = $durationMax;
        return $this;
    }

    /**
     * A unit of time (units from UCUM).
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The units of time for the duration, in UCUM units.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUnitsOfTime
     */
    public function getDurationUnit(): null|FHIRUnitsOfTime
    {
        return $this->durationUnit ?? null;
    }

    /**
     * A unit of time (units from UCUM).
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The units of time for the duration, in UCUM units.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRUnitsOfTimeList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUnitsOfTime $durationUnit
     * @return static
     */
    public function setDurationUnit(null|string|FHIRUnitsOfTimeList|FHIRUnitsOfTime $durationUnit): self
    {
        if (null === $durationUnit) {
            unset($this->durationUnit);
            return $this;
        }
        if (!($durationUnit instanceof FHIRUnitsOfTime)) {
            $durationUnit = new FHIRUnitsOfTime(value: $durationUnit);
        }
        $this->durationUnit = $durationUnit;
        return $this;
    }

    /**
     * An integer with a value that is positive (e.g. >0)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The number of times to repeat the action within the specified period. If
     * frequencyMax is present, this element indicates the lower bound of the allowed
     * range of the frequency.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPositiveInt
     */
    public function getFrequency(): null|FHIRPositiveInt
    {
        return $this->frequency ?? null;
    }

    /**
     * An integer with a value that is positive (e.g. >0)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The number of times to repeat the action within the specified period. If
     * frequencyMax is present, this element indicates the lower bound of the allowed
     * range of the frequency.
     *
     * @param null|string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRPositiveIntPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPositiveInt $frequency
     * @return static
     */
    public function setFrequency(null|string|float|FHIRPositiveIntPrimitive|FHIRPositiveInt $frequency): self
    {
        if (null === $frequency) {
            unset($this->frequency);
            return $this;
        }
        if (!($frequency instanceof FHIRPositiveInt)) {
            $frequency = new FHIRPositiveInt(value: $frequency);
        }
        $this->frequency = $frequency;
        return $this;
    }

    /**
     * An integer with a value that is positive (e.g. >0)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * If present, indicates that the frequency is a range - so to repeat between
     * [frequency] and [frequencyMax] times within the period or period range.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPositiveInt
     */
    public function getFrequencyMax(): null|FHIRPositiveInt
    {
        return $this->frequencyMax ?? null;
    }

    /**
     * An integer with a value that is positive (e.g. >0)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * If present, indicates that the frequency is a range - so to repeat between
     * [frequency] and [frequencyMax] times within the period or period range.
     *
     * @param null|string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRPositiveIntPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPositiveInt $frequencyMax
     * @return static
     */
    public function setFrequencyMax(null|string|float|FHIRPositiveIntPrimitive|FHIRPositiveInt $frequencyMax): self
    {
        if (null === $frequencyMax) {
            unset($this->frequencyMax);
            return $this;
        }
        if (!($frequencyMax instanceof FHIRPositiveInt)) {
            $frequencyMax = new FHIRPositiveInt(value: $frequencyMax);
        }
        $this->frequencyMax = $frequencyMax;
        return $this;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates the duration of time over which repetitions are to occur; e.g. to
     * express "3 times per day", 3 would be the frequency and "1 day" would be the
     * period. If periodMax is present, this element indicates the lower bound of the
     * allowed range of the period length.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal
     */
    public function getPeriod(): null|FHIRDecimal
    {
        return $this->period ?? null;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates the duration of time over which repetitions are to occur; e.g. to
     * express "3 times per day", 3 would be the frequency and "1 day" would be the
     * period. If periodMax is present, this element indicates the lower bound of the
     * allowed range of the period length.
     *
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $period
     * @return static
     */
    public function setPeriod(null|string|int|float|FHIRDecimalPrimitive|FHIRDecimal $period): self
    {
        if (null === $period) {
            unset($this->period);
            return $this;
        }
        if (!($period instanceof FHIRDecimal)) {
            $period = new FHIRDecimal(value: $period);
        }
        $this->period = $period;
        return $this;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If present, indicates that the period is a range from [period] to [periodMax],
     * allowing expressing concepts such as "do this once every 3-5 days.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal
     */
    public function getPeriodMax(): null|FHIRDecimal
    {
        return $this->periodMax ?? null;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If present, indicates that the period is a range from [period] to [periodMax],
     * allowing expressing concepts such as "do this once every 3-5 days.
     *
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $periodMax
     * @return static
     */
    public function setPeriodMax(null|string|int|float|FHIRDecimalPrimitive|FHIRDecimal $periodMax): self
    {
        if (null === $periodMax) {
            unset($this->periodMax);
            return $this;
        }
        if (!($periodMax instanceof FHIRDecimal)) {
            $periodMax = new FHIRDecimal(value: $periodMax);
        }
        $this->periodMax = $periodMax;
        return $this;
    }

    /**
     * A unit of time (units from UCUM).
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The units of time for the period in UCUM units.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUnitsOfTime
     */
    public function getPeriodUnit(): null|FHIRUnitsOfTime
    {
        return $this->periodUnit ?? null;
    }

    /**
     * A unit of time (units from UCUM).
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The units of time for the period in UCUM units.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRUnitsOfTimeList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUnitsOfTime $periodUnit
     * @return static
     */
    public function setPeriodUnit(null|string|FHIRUnitsOfTimeList|FHIRUnitsOfTime $periodUnit): self
    {
        if (null === $periodUnit) {
            unset($this->periodUnit);
            return $this;
        }
        if (!($periodUnit instanceof FHIRUnitsOfTime)) {
            $periodUnit = new FHIRUnitsOfTime(value: $periodUnit);
        }
        $this->periodUnit = $periodUnit;
        return $this;
    }

    /**
     * A string which has at least one character and no leading or trailing whitespace
     * and where there is no whitespace other than single spaces in the contents
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * If one or more days of week is provided, then the action happens only on the
     * specified day(s).
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode>
     */
    public function getDayOfWeek(): array
    {
        return $this->dayOfWeek ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode>
     */
    public function getDayOfWeekIterator(): iterable
    {
        if (!isset($this->dayOfWeek)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->dayOfWeek);
    }

    /**
     * A string which has at least one character and no leading or trailing whitespace
     * and where there is no whitespace other than single spaces in the contents
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * If one or more days of week is provided, then the action happens only on the
     * specified day(s).
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $dayOfWeek
     * @return static
     */
    public function addDayOfWeek(string|FHIRCodePrimitive|FHIRCode $dayOfWeek): self
    {
        if (!($dayOfWeek instanceof FHIRCode)) {
            $dayOfWeek = new FHIRCode(value: $dayOfWeek);
        }
        if (!isset($this->dayOfWeek)) {
            $this->dayOfWeek = [];
        }
        $this->dayOfWeek[] = $dayOfWeek;
        return $this;
    }

    /**
     * A string which has at least one character and no leading or trailing whitespace
     * and where there is no whitespace other than single spaces in the contents
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * If one or more days of week is provided, then the action happens only on the
     * specified day(s).
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode ...$dayOfWeek
     * @return static
     */
    public function setDayOfWeek(string|FHIRCodePrimitive|FHIRCode ...$dayOfWeek): self
    {
        if ([] === $dayOfWeek) {
            unset($this->dayOfWeek);
            return $this;
        }
        $this->dayOfWeek = [];
        foreach($dayOfWeek as $v) {
            if ($v instanceof FHIRCode) {
                $this->dayOfWeek[] = $v;
            } else {
                $this->dayOfWeek[] = new FHIRCode(value: $v);
            }
        }
        return $this;
    }

    /**
     * A time during the day, with no date specified
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Specified time of day for action to take place.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTime>
     */
    public function getTimeOfDay(): array
    {
        return $this->timeOfDay ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTime>
     */
    public function getTimeOfDayIterator(): iterable
    {
        if (!isset($this->timeOfDay)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->timeOfDay);
    }

    /**
     * A time during the day, with no date specified
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Specified time of day for action to take place.
     *
     * @param string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTime $timeOfDay
     * @return static
     */
    public function addTimeOfDay(string|\DateTimeInterface|FHIRTimePrimitive|FHIRTime $timeOfDay): self
    {
        if (!($timeOfDay instanceof FHIRTime)) {
            $timeOfDay = new FHIRTime(value: $timeOfDay);
        }
        if (!isset($this->timeOfDay)) {
            $this->timeOfDay = [];
        }
        $this->timeOfDay[] = $timeOfDay;
        return $this;
    }

    /**
     * A time during the day, with no date specified
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Specified time of day for action to take place.
     *
     * @param string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTime ...$timeOfDay
     * @return static
     */
    public function setTimeOfDay(string|\DateTimeInterface|FHIRTimePrimitive|FHIRTime ...$timeOfDay): self
    {
        if ([] === $timeOfDay) {
            unset($this->timeOfDay);
            return $this;
        }
        $this->timeOfDay = [];
        foreach($timeOfDay as $v) {
            if ($v instanceof FHIRTime) {
                $this->timeOfDay[] = $v;
            } else {
                $this->timeOfDay[] = new FHIRTime(value: $v);
            }
        }
        return $this;
    }

    /**
     * Real world event relating to the schedule.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * An approximate time period during the day, potentially linked to an event of
     * daily living that indicates when the action should occur.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIREventTiming>
     */
    public function getWhen(): array
    {
        return $this->when ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIREventTiming>
     */
    public function getWhenIterator(): iterable
    {
        if (!isset($this->when)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->when);
    }

    /**
     * Real world event relating to the schedule.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * An approximate time period during the day, potentially linked to an event of
     * daily living that indicates when the action should occur.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIREventTimingList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIREventTiming $when
     * @return static
     */
    public function addWhen(string|FHIREventTimingList|FHIREventTiming $when): self
    {
        if (!($when instanceof FHIREventTiming)) {
            $when = new FHIREventTiming(value: $when);
        }
        if (!isset($this->when)) {
            $this->when = [];
        }
        $this->when[] = $when;
        return $this;
    }

    /**
     * Real world event relating to the schedule.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * An approximate time period during the day, potentially linked to an event of
     * daily living that indicates when the action should occur.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIREventTimingList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIREventTiming ...$when
     * @return static
     */
    public function setWhen(string|FHIREventTimingList|FHIREventTiming ...$when): self
    {
        if ([] === $when) {
            unset($this->when);
            return $this;
        }
        $this->when = [];
        foreach($when as $v) {
            if ($v instanceof FHIREventTiming) {
                $this->when[] = $v;
            } else {
                $this->when[] = new FHIREventTiming(value: $v);
            }
        }
        return $this;
    }

    /**
     * An integer with a value that is not negative (e.g. >= 0)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The number of minutes from the event. If the event code does not indicate
     * whether the minutes is before or after the event, then the offset is assumed to
     * be after the event.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUnsignedInt
     */
    public function getOffset(): null|FHIRUnsignedInt
    {
        return $this->offset ?? null;
    }

    /**
     * An integer with a value that is not negative (e.g. >= 0)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The number of minutes from the event. If the event code does not indicate
     * whether the minutes is before or after the event, then the offset is assumed to
     * be after the event.
     *
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRUnsignedIntPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUnsignedInt $offset
     * @return static
     */
    public function setOffset(null|string|int|float|FHIRUnsignedIntPrimitive|FHIRUnsignedInt $offset): self
    {
        if (null === $offset) {
            unset($this->offset);
            return $this;
        }
        if (!($offset instanceof FHIRUnsignedInt)) {
            $offset = new FHIRUnsignedInt(value: $offset);
        }
        $this->offset = $offset;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTiming\FHIRTimingRepeat $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTiming\FHIRTimingRepeat
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRTimingRepeat)) {
            throw new \RuntimeException(sprintf(
                '%s::xmlUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        foreach ($element->children() as $ce) {
            $cen = $ce->getName();
            if (self::FIELD_EXTENSION === $cen) {
                $type->addExtension(FHIRExtension::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ID === $cen) {
                $va = $ce->attributes()[FHIRStringPrimitive::FIELD_VALUE] ?? null;
                if (null !== $va) {
                    $type->setId((string)$va);
                    $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::ELEMENT_ATTRIBUTE);
                } else {
                    $type->setId((string)$ce);
                    $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::ELEMENT_VALUE);
                }
            } else if (self::FIELD_MODIFIER_EXTENSION === $cen) {
                $type->addModifierExtension(FHIRExtension::xmlUnserialize($ce, $config));
            } else if (self::FIELD_BOUNDS_DURATION === $cen) {
                $type->setBoundsDuration(FHIRDuration::xmlUnserialize($ce, $config));
            } else if (self::FIELD_BOUNDS_RANGE === $cen) {
                $type->setBoundsRange(FHIRRange::xmlUnserialize($ce, $config));
            } else if (self::FIELD_BOUNDS_PERIOD === $cen) {
                $type->setBoundsPeriod(FHIRPeriod::xmlUnserialize($ce, $config));
            } else if (self::FIELD_COUNT === $cen) {
                $type->setCount(FHIRPositiveInt::xmlUnserialize($ce, $config));
            } else if (self::FIELD_COUNT_MAX === $cen) {
                $type->setCountMax(FHIRPositiveInt::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DURATION === $cen) {
                $type->setDuration(FHIRDecimal::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DURATION_MAX === $cen) {
                $type->setDurationMax(FHIRDecimal::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DURATION_UNIT === $cen) {
                $type->setDurationUnit(FHIRUnitsOfTime::xmlUnserialize($ce, $config));
            } else if (self::FIELD_FREQUENCY === $cen) {
                $type->setFrequency(FHIRPositiveInt::xmlUnserialize($ce, $config));
            } else if (self::FIELD_FREQUENCY_MAX === $cen) {
                $type->setFrequencyMax(FHIRPositiveInt::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PERIOD === $cen) {
                $type->setPeriod(FHIRDecimal::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PERIOD_MAX === $cen) {
                $type->setPeriodMax(FHIRDecimal::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PERIOD_UNIT === $cen) {
                $type->setPeriodUnit(FHIRUnitsOfTime::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DAY_OF_WEEK === $cen) {
                $type->addDayOfWeek(FHIRCode::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TIME_OF_DAY === $cen) {
                $type->addTimeOfDay(FHIRTime::xmlUnserialize($ce, $config));
            } else if (self::FIELD_WHEN === $cen) {
                $type->addWhen(FHIREventTiming::xmlUnserialize($ce, $config));
            } else if (self::FIELD_OFFSET === $cen) {
                $type->setOffset(FHIRUnsignedInt::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_COUNT])) {
            if (isset($type->count)) {
                $type->count->setValue((string)$attributes[self::FIELD_COUNT]);
            } else {
                $type->setCount((string)$attributes[self::FIELD_COUNT]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_COUNT, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_COUNT_MAX])) {
            if (isset($type->countMax)) {
                $type->countMax->setValue((string)$attributes[self::FIELD_COUNT_MAX]);
            } else {
                $type->setCountMax((string)$attributes[self::FIELD_COUNT_MAX]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_COUNT_MAX, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_DURATION])) {
            if (isset($type->duration)) {
                $type->duration->setValue((string)$attributes[self::FIELD_DURATION]);
            } else {
                $type->setDuration((string)$attributes[self::FIELD_DURATION]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_DURATION, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_DURATION_MAX])) {
            if (isset($type->durationMax)) {
                $type->durationMax->setValue((string)$attributes[self::FIELD_DURATION_MAX]);
            } else {
                $type->setDurationMax((string)$attributes[self::FIELD_DURATION_MAX]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_DURATION_MAX, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_DURATION_UNIT])) {
            if (isset($type->durationUnit)) {
                $type->durationUnit->setValue((string)$attributes[self::FIELD_DURATION_UNIT]);
            } else {
                $type->setDurationUnit((string)$attributes[self::FIELD_DURATION_UNIT]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_DURATION_UNIT, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_FREQUENCY])) {
            if (isset($type->frequency)) {
                $type->frequency->setValue((string)$attributes[self::FIELD_FREQUENCY]);
            } else {
                $type->setFrequency((string)$attributes[self::FIELD_FREQUENCY]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_FREQUENCY, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_FREQUENCY_MAX])) {
            if (isset($type->frequencyMax)) {
                $type->frequencyMax->setValue((string)$attributes[self::FIELD_FREQUENCY_MAX]);
            } else {
                $type->setFrequencyMax((string)$attributes[self::FIELD_FREQUENCY_MAX]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_FREQUENCY_MAX, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_PERIOD])) {
            if (isset($type->period)) {
                $type->period->setValue((string)$attributes[self::FIELD_PERIOD]);
            } else {
                $type->setPeriod((string)$attributes[self::FIELD_PERIOD]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_PERIOD, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_PERIOD_MAX])) {
            if (isset($type->periodMax)) {
                $type->periodMax->setValue((string)$attributes[self::FIELD_PERIOD_MAX]);
            } else {
                $type->setPeriodMax((string)$attributes[self::FIELD_PERIOD_MAX]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_PERIOD_MAX, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_PERIOD_UNIT])) {
            if (isset($type->periodUnit)) {
                $type->periodUnit->setValue((string)$attributes[self::FIELD_PERIOD_UNIT]);
            } else {
                $type->setPeriodUnit((string)$attributes[self::FIELD_PERIOD_UNIT]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_PERIOD_UNIT, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_OFFSET])) {
            if (isset($type->offset)) {
                $type->offset->setValue((string)$attributes[self::FIELD_OFFSET]);
            } else {
                $type->setOffset((string)$attributes[self::FIELD_OFFSET]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_OFFSET, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        return $type;
    }

    /**
     * @param \OpenEMR\FHIR\Encoding\XMLWriter $xw
     * @param \OpenEMR\FHIR\Encoding\SerializeConfig $config
     */
    public function xmlSerialize(XMLWriter $xw,
                                 SerializeConfig $config): void
    {
        if (isset($this->count) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_COUNT]) {
            $xw->writeAttribute(self::FIELD_COUNT, $this->count->_getValueAsString());
        }
        if (isset($this->countMax) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_COUNT_MAX]) {
            $xw->writeAttribute(self::FIELD_COUNT_MAX, $this->countMax->_getValueAsString());
        }
        if (isset($this->duration) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_DURATION]) {
            $xw->writeAttribute(self::FIELD_DURATION, $this->duration->_getValueAsString());
        }
        if (isset($this->durationMax) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_DURATION_MAX]) {
            $xw->writeAttribute(self::FIELD_DURATION_MAX, $this->durationMax->_getValueAsString());
        }
        if (isset($this->durationUnit) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_DURATION_UNIT]) {
            $xw->writeAttribute(self::FIELD_DURATION_UNIT, $this->durationUnit->_getValueAsString());
        }
        if (isset($this->frequency) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_FREQUENCY]) {
            $xw->writeAttribute(self::FIELD_FREQUENCY, $this->frequency->_getValueAsString());
        }
        if (isset($this->frequencyMax) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_FREQUENCY_MAX]) {
            $xw->writeAttribute(self::FIELD_FREQUENCY_MAX, $this->frequencyMax->_getValueAsString());
        }
        if (isset($this->period) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_PERIOD]) {
            $xw->writeAttribute(self::FIELD_PERIOD, $this->period->_getValueAsString());
        }
        if (isset($this->periodMax) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_PERIOD_MAX]) {
            $xw->writeAttribute(self::FIELD_PERIOD_MAX, $this->periodMax->_getValueAsString());
        }
        if (isset($this->periodUnit) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_PERIOD_UNIT]) {
            $xw->writeAttribute(self::FIELD_PERIOD_UNIT, $this->periodUnit->_getValueAsString());
        }
        if (isset($this->offset) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_OFFSET]) {
            $xw->writeAttribute(self::FIELD_OFFSET, $this->offset->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->boundsDuration)) {
            $xw->startElement(self::FIELD_BOUNDS_DURATION);
            $this->boundsDuration->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->boundsRange)) {
            $xw->startElement(self::FIELD_BOUNDS_RANGE);
            $this->boundsRange->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->boundsPeriod)) {
            $xw->startElement(self::FIELD_BOUNDS_PERIOD);
            $this->boundsPeriod->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->count)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_COUNT]
                || $this->count->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_COUNT);
            $this->count->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_COUNT]);
            $xw->endElement();
        }
        if (isset($this->countMax)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_COUNT_MAX]
                || $this->countMax->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_COUNT_MAX);
            $this->countMax->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_COUNT_MAX]);
            $xw->endElement();
        }
        if (isset($this->duration)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_DURATION]
                || $this->duration->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_DURATION);
            $this->duration->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_DURATION]);
            $xw->endElement();
        }
        if (isset($this->durationMax)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_DURATION_MAX]
                || $this->durationMax->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_DURATION_MAX);
            $this->durationMax->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_DURATION_MAX]);
            $xw->endElement();
        }
        if (isset($this->durationUnit)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_DURATION_UNIT]
                || $this->durationUnit->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_DURATION_UNIT);
            $this->durationUnit->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_DURATION_UNIT]);
            $xw->endElement();
        }
        if (isset($this->frequency)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_FREQUENCY]
                || $this->frequency->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_FREQUENCY);
            $this->frequency->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_FREQUENCY]);
            $xw->endElement();
        }
        if (isset($this->frequencyMax)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_FREQUENCY_MAX]
                || $this->frequencyMax->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_FREQUENCY_MAX);
            $this->frequencyMax->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_FREQUENCY_MAX]);
            $xw->endElement();
        }
        if (isset($this->period)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_PERIOD]
                || $this->period->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_PERIOD);
            $this->period->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_PERIOD]);
            $xw->endElement();
        }
        if (isset($this->periodMax)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_PERIOD_MAX]
                || $this->periodMax->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_PERIOD_MAX);
            $this->periodMax->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_PERIOD_MAX]);
            $xw->endElement();
        }
        if (isset($this->periodUnit)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_PERIOD_UNIT]
                || $this->periodUnit->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_PERIOD_UNIT);
            $this->periodUnit->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_PERIOD_UNIT]);
            $xw->endElement();
        }
        if (isset($this->dayOfWeek) && [] !== $this->dayOfWeek) {
            foreach($this->dayOfWeek as $v) {
                $xw->startElement(self::FIELD_DAY_OF_WEEK);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->timeOfDay) && [] !== $this->timeOfDay) {
            foreach($this->timeOfDay as $v) {
                $xw->startElement(self::FIELD_TIME_OF_DAY);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->when) && [] !== $this->when) {
            foreach($this->when as $v) {
                $xw->startElement(self::FIELD_WHEN);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->offset)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_OFFSET]
                || $this->offset->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_OFFSET);
            $this->offset->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_OFFSET]);
            $xw->endElement();
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTiming\FHIRTimingRepeat $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTiming\FHIRTimingRepeat
     * @throws \Exception
     */
    public static function jsonUnserialize(\stdClass $decoded,
                                           UnserializeConfig $config,
                                           null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            if (isset($decoded->resourceType) && $decoded->resourceType !== static::FHIR_TYPE_NAME) {
                throw new \DomainException(sprintf(
                    '%s::jsonUnserialize - Cannot unmarshal data for resource type "%s" into this type.',
                    ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                    $decoded->resourceType,
                ));
            }
            $type = new static();
        } else if (!($type instanceof FHIRTimingRepeat)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->boundsDuration) || property_exists($decoded, self::FIELD_BOUNDS_DURATION)) {
            if (is_array($decoded->boundsDuration)) {
                $type->setBoundsDuration(FHIRDuration::jsonUnserialize(reset($decoded->boundsDuration), $config));
            } else {
                $type->setBoundsDuration(FHIRDuration::jsonUnserialize($decoded->boundsDuration, $config));
            }
        }
        if (isset($decoded->boundsRange) || property_exists($decoded, self::FIELD_BOUNDS_RANGE)) {
            if (is_array($decoded->boundsRange)) {
                $type->setBoundsRange(FHIRRange::jsonUnserialize(reset($decoded->boundsRange), $config));
            } else {
                $type->setBoundsRange(FHIRRange::jsonUnserialize($decoded->boundsRange, $config));
            }
        }
        if (isset($decoded->boundsPeriod) || property_exists($decoded, self::FIELD_BOUNDS_PERIOD)) {
            if (is_array($decoded->boundsPeriod)) {
                $type->setBoundsPeriod(FHIRPeriod::jsonUnserialize(reset($decoded->boundsPeriod), $config));
            } else {
                $type->setBoundsPeriod(FHIRPeriod::jsonUnserialize($decoded->boundsPeriod, $config));
            }
        }
        if (isset($decoded->count)
            || isset($decoded->_count)
            || property_exists($decoded, self::FIELD_COUNT)
            || property_exists($decoded, self::FIELD_COUNT_EXT)) {
            $v = $decoded->_count ?? new \stdClass();
            $v->value = $decoded->count ?? null;
            $type->setCount(FHIRPositiveInt::jsonUnserialize($v, $config));
        }
        if (isset($decoded->countMax)
            || isset($decoded->_countMax)
            || property_exists($decoded, self::FIELD_COUNT_MAX)
            || property_exists($decoded, self::FIELD_COUNT_MAX_EXT)) {
            $v = $decoded->_countMax ?? new \stdClass();
            $v->value = $decoded->countMax ?? null;
            $type->setCountMax(FHIRPositiveInt::jsonUnserialize($v, $config));
        }
        if (isset($decoded->duration)
            || isset($decoded->_duration)
            || property_exists($decoded, self::FIELD_DURATION)
            || property_exists($decoded, self::FIELD_DURATION_EXT)) {
            $v = $decoded->_duration ?? new \stdClass();
            $v->value = $decoded->duration ?? null;
            $type->setDuration(FHIRDecimal::jsonUnserialize($v, $config));
        }
        if (isset($decoded->durationMax)
            || isset($decoded->_durationMax)
            || property_exists($decoded, self::FIELD_DURATION_MAX)
            || property_exists($decoded, self::FIELD_DURATION_MAX_EXT)) {
            $v = $decoded->_durationMax ?? new \stdClass();
            $v->value = $decoded->durationMax ?? null;
            $type->setDurationMax(FHIRDecimal::jsonUnserialize($v, $config));
        }
        if (isset($decoded->durationUnit)
            || isset($decoded->_durationUnit)
            || property_exists($decoded, self::FIELD_DURATION_UNIT)
            || property_exists($decoded, self::FIELD_DURATION_UNIT_EXT)) {
            $v = $decoded->_durationUnit ?? new \stdClass();
            $v->value = $decoded->durationUnit ?? null;
            $type->setDurationUnit(FHIRUnitsOfTime::jsonUnserialize($v, $config));
        }
        if (isset($decoded->frequency)
            || isset($decoded->_frequency)
            || property_exists($decoded, self::FIELD_FREQUENCY)
            || property_exists($decoded, self::FIELD_FREQUENCY_EXT)) {
            $v = $decoded->_frequency ?? new \stdClass();
            $v->value = $decoded->frequency ?? null;
            $type->setFrequency(FHIRPositiveInt::jsonUnserialize($v, $config));
        }
        if (isset($decoded->frequencyMax)
            || isset($decoded->_frequencyMax)
            || property_exists($decoded, self::FIELD_FREQUENCY_MAX)
            || property_exists($decoded, self::FIELD_FREQUENCY_MAX_EXT)) {
            $v = $decoded->_frequencyMax ?? new \stdClass();
            $v->value = $decoded->frequencyMax ?? null;
            $type->setFrequencyMax(FHIRPositiveInt::jsonUnserialize($v, $config));
        }
        if (isset($decoded->period)
            || isset($decoded->_period)
            || property_exists($decoded, self::FIELD_PERIOD)
            || property_exists($decoded, self::FIELD_PERIOD_EXT)) {
            $v = $decoded->_period ?? new \stdClass();
            $v->value = $decoded->period ?? null;
            $type->setPeriod(FHIRDecimal::jsonUnserialize($v, $config));
        }
        if (isset($decoded->periodMax)
            || isset($decoded->_periodMax)
            || property_exists($decoded, self::FIELD_PERIOD_MAX)
            || property_exists($decoded, self::FIELD_PERIOD_MAX_EXT)) {
            $v = $decoded->_periodMax ?? new \stdClass();
            $v->value = $decoded->periodMax ?? null;
            $type->setPeriodMax(FHIRDecimal::jsonUnserialize($v, $config));
        }
        if (isset($decoded->periodUnit)
            || isset($decoded->_periodUnit)
            || property_exists($decoded, self::FIELD_PERIOD_UNIT)
            || property_exists($decoded, self::FIELD_PERIOD_UNIT_EXT)) {
            $v = $decoded->_periodUnit ?? new \stdClass();
            $v->value = $decoded->periodUnit ?? null;
            $type->setPeriodUnit(FHIRUnitsOfTime::jsonUnserialize($v, $config));
        }
        if (isset($decoded->dayOfWeek)
            || isset($decoded->_dayOfWeek)
            || property_exists($decoded, self::FIELD_DAY_OF_WEEK)
            || property_exists($decoded, self::FIELD_DAY_OF_WEEK_EXT)) {
            $vals = (array)($decoded->dayOfWeek ?? []);
            $exts = (array)($decoded->_dayOfWeek ?? []);
            $valCnt = count($vals);
            $extCnt = count($exts);
            if ($extCnt > $valCnt) {
                $valCnt = $extCnt;
            }
            for ($i = 0; $i < $valCnt; $i++) {
                $v = $exts[$i] ?? new \stdClass();
                $v->value = $vals[$i] ?? null;
                $type->addDayOfWeek(FHIRCode::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->timeOfDay)
            || isset($decoded->_timeOfDay)
            || property_exists($decoded, self::FIELD_TIME_OF_DAY)
            || property_exists($decoded, self::FIELD_TIME_OF_DAY_EXT)) {
            $vals = (array)($decoded->timeOfDay ?? []);
            $exts = (array)($decoded->_timeOfDay ?? []);
            $valCnt = count($vals);
            $extCnt = count($exts);
            if ($extCnt > $valCnt) {
                $valCnt = $extCnt;
            }
            for ($i = 0; $i < $valCnt; $i++) {
                $v = $exts[$i] ?? new \stdClass();
                $v->value = $vals[$i] ?? null;
                $type->addTimeOfDay(FHIRTime::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->when)
            || isset($decoded->_when)
            || property_exists($decoded, self::FIELD_WHEN)
            || property_exists($decoded, self::FIELD_WHEN_EXT)) {
            $vals = (array)($decoded->when ?? []);
            $exts = (array)($decoded->_when ?? []);
            $valCnt = count($vals);
            $extCnt = count($exts);
            if ($extCnt > $valCnt) {
                $valCnt = $extCnt;
            }
            for ($i = 0; $i < $valCnt; $i++) {
                $v = $exts[$i] ?? new \stdClass();
                $v->value = $vals[$i] ?? null;
                $type->addWhen(FHIREventTiming::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->offset)
            || isset($decoded->_offset)
            || property_exists($decoded, self::FIELD_OFFSET)
            || property_exists($decoded, self::FIELD_OFFSET_EXT)) {
            $v = $decoded->_offset ?? new \stdClass();
            $v->value = $decoded->offset ?? null;
            $type->setOffset(FHIRUnsignedInt::jsonUnserialize($v, $config));
        }
        return $type;
    }

    /**
     * @return \stdClass
     */
    public function jsonSerialize(): mixed
    {
        $out = parent::jsonSerialize();
        if (isset($this->boundsDuration)) {
            $out->boundsDuration = $this->boundsDuration;
        }
        if (isset($this->boundsRange)) {
            $out->boundsRange = $this->boundsRange;
        }
        if (isset($this->boundsPeriod)) {
            $out->boundsPeriod = $this->boundsPeriod;
        }
        if (isset($this->count)) {
            if (null !== ($val = $this->count->getValue())) {
                $out->count = $val;
            }
            if ($this->count->_nonValueFieldDefined()) {
                $ext = $this->count->jsonSerialize();
                unset($ext->value);
                $out->_count = $ext;
            }
        }
        if (isset($this->countMax)) {
            if (null !== ($val = $this->countMax->getValue())) {
                $out->countMax = $val;
            }
            if ($this->countMax->_nonValueFieldDefined()) {
                $ext = $this->countMax->jsonSerialize();
                unset($ext->value);
                $out->_countMax = $ext;
            }
        }
        if (isset($this->duration)) {
            if (null !== ($val = $this->duration->getValue())) {
                $out->duration = $val;
            }
            if ($this->duration->_nonValueFieldDefined()) {
                $ext = $this->duration->jsonSerialize();
                unset($ext->value);
                $out->_duration = $ext;
            }
        }
        if (isset($this->durationMax)) {
            if (null !== ($val = $this->durationMax->getValue())) {
                $out->durationMax = $val;
            }
            if ($this->durationMax->_nonValueFieldDefined()) {
                $ext = $this->durationMax->jsonSerialize();
                unset($ext->value);
                $out->_durationMax = $ext;
            }
        }
        if (isset($this->durationUnit)) {
            if (null !== ($val = $this->durationUnit->getValue())) {
                $out->durationUnit = $val;
            }
            if ($this->durationUnit->_nonValueFieldDefined()) {
                $ext = $this->durationUnit->jsonSerialize();
                unset($ext->value);
                $out->_durationUnit = $ext;
            }
        }
        if (isset($this->frequency)) {
            if (null !== ($val = $this->frequency->getValue())) {
                $out->frequency = $val;
            }
            if ($this->frequency->_nonValueFieldDefined()) {
                $ext = $this->frequency->jsonSerialize();
                unset($ext->value);
                $out->_frequency = $ext;
            }
        }
        if (isset($this->frequencyMax)) {
            if (null !== ($val = $this->frequencyMax->getValue())) {
                $out->frequencyMax = $val;
            }
            if ($this->frequencyMax->_nonValueFieldDefined()) {
                $ext = $this->frequencyMax->jsonSerialize();
                unset($ext->value);
                $out->_frequencyMax = $ext;
            }
        }
        if (isset($this->period)) {
            if (null !== ($val = $this->period->getValue())) {
                $out->period = $val;
            }
            if ($this->period->_nonValueFieldDefined()) {
                $ext = $this->period->jsonSerialize();
                unset($ext->value);
                $out->_period = $ext;
            }
        }
        if (isset($this->periodMax)) {
            if (null !== ($val = $this->periodMax->getValue())) {
                $out->periodMax = $val;
            }
            if ($this->periodMax->_nonValueFieldDefined()) {
                $ext = $this->periodMax->jsonSerialize();
                unset($ext->value);
                $out->_periodMax = $ext;
            }
        }
        if (isset($this->periodUnit)) {
            if (null !== ($val = $this->periodUnit->getValue())) {
                $out->periodUnit = $val;
            }
            if ($this->periodUnit->_nonValueFieldDefined()) {
                $ext = $this->periodUnit->jsonSerialize();
                unset($ext->value);
                $out->_periodUnit = $ext;
            }
        }
        if (isset($this->dayOfWeek) && [] !== $this->dayOfWeek) {
            $vals = [];
            $exts = [];
            $hasVals = false;
            $hasExts = false;
            foreach ($this->dayOfWeek as $v) {
                $val = $v->getValue();
                if (null !== $val) {
                    $hasVals = true;
                    $vals[] = $val;
                } else {
                    $vals[] = null;
                }
                if ($v->_nonValueFieldDefined()) {
                    $hasExts = true;
                    $ext = $v->jsonSerialize();
                    unset($ext->value);
                    $exts[] = $ext;
                } else {
                    $exts[] = null;
                }
            }
            if ($hasVals) {
                $out->dayOfWeek = $vals;
            }
            if ($hasExts) {
                $out->_dayOfWeek = $exts;
            }
        }
        if (isset($this->timeOfDay) && [] !== $this->timeOfDay) {
            $vals = [];
            $exts = [];
            $hasVals = false;
            $hasExts = false;
            foreach ($this->timeOfDay as $v) {
                $val = $v->getValue();
                if (null !== $val) {
                    $hasVals = true;
                    $vals[] = $val;
                } else {
                    $vals[] = null;
                }
                if ($v->_nonValueFieldDefined()) {
                    $hasExts = true;
                    $ext = $v->jsonSerialize();
                    unset($ext->value);
                    $exts[] = $ext;
                } else {
                    $exts[] = null;
                }
            }
            if ($hasVals) {
                $out->timeOfDay = $vals;
            }
            if ($hasExts) {
                $out->_timeOfDay = $exts;
            }
        }
        if (isset($this->when) && [] !== $this->when) {
            $vals = [];
            $exts = [];
            $hasVals = false;
            $hasExts = false;
            foreach ($this->when as $v) {
                $val = $v->getValue();
                if (null !== $val) {
                    $hasVals = true;
                    $vals[] = $val;
                } else {
                    $vals[] = null;
                }
                if ($v->_nonValueFieldDefined()) {
                    $hasExts = true;
                    $ext = $v->jsonSerialize();
                    unset($ext->value);
                    $exts[] = $ext;
                } else {
                    $exts[] = null;
                }
            }
            if ($hasVals) {
                $out->when = $vals;
            }
            if ($hasExts) {
                $out->_when = $exts;
            }
        }
        if (isset($this->offset)) {
            if (null !== ($val = $this->offset->getValue())) {
                $out->offset = $val;
            }
            if ($this->offset->_nonValueFieldDefined()) {
                $ext = $this->offset->jsonSerialize();
                unset($ext->value);
                $out->_offset = $ext;
            }
        }
        return $out;
    }
}
