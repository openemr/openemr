<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement;

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
use OpenEMR\FHIR\Validation\Rules\MinOccursRule;
use OpenEMR\FHIR\Validation\TypeValidationsTrait;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRTriggerTypeList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRDatePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTiming;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * A description of a triggering event. Triggering events can be named events, data
 * events, or periodic, as determined by the type element.
 * If the element is present, it must have a value for at least one of the defined
 * elements, an \@id referenced from the Narrative, or extensions
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRTriggerDefinition extends FHIRElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_TRIGGER_DEFINITION;

    /* class_default.php:56 */
    public const FIELD_TYPE = 'type';
    public const FIELD_TYPE_EXT = '_type';
    public const FIELD_NAME = 'name';
    public const FIELD_NAME_EXT = '_name';
    public const FIELD_TIMING_TIMING = 'timingTiming';
    public const FIELD_TIMING_REFERENCE = 'timingReference';
    public const FIELD_TIMING_DATE = 'timingDate';
    public const FIELD_TIMING_DATE_EXT = '_timingDate';
    public const FIELD_TIMING_DATE_TIME = 'timingDateTime';
    public const FIELD_TIMING_DATE_TIME_EXT = '_timingDateTime';
    public const FIELD_DATA = 'data';
    public const FIELD_CONDITION = 'condition';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_TYPE => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_TYPE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_NAME => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_TIMING_DATE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_TIMING_DATE_TIME => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * The type of trigger.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The type of triggering event.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTriggerType
     */
    #[FHIRTriggerType]
    protected FHIRTriggerType $type;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A formal name for the event. This may be an absolute URI that identifies the
     * event formally (e.g. from a trigger registry), or a simple relative URI that
     * identifies the event in a local context.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $name;
    /**
     * Specifies an event that may occur multiple times. Timing schedules are used to
     * record when things are planned, expected or requested to occur. The most common
     * usage is in dosage instructions for medications. They are also used when
     * planning care of various kinds, and may be used for reporting the schedule to
     * which past regular activities were carried out.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The timing of the event (if this is a periodic trigger). (choose any one of
     * timing*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTiming
     */
    #[FHIRTiming]
    protected FHIRTiming $timingTiming;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The timing of the event (if this is a periodic trigger). (choose any one of
     * timing*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $timingReference;
    /**
     * A date or partial date (e.g. just year or year + month). There is no time zone.
     * The format is a union of the schema types gYear, gYearMonth and date. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The timing of the event (if this is a periodic trigger). (choose any one of
     * timing*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDate
     */
    #[FHIRDate]
    protected FHIRDate $timingDate;
    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The timing of the event (if this is a periodic trigger). (choose any one of
     * timing*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    #[FHIRDateTime]
    protected FHIRDateTime $timingDateTime;
    /**
     * Describes a required data item for evaluation in terms of the type of data, and
     * optional code or date-based filters of the data.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The triggering data of the event (if this is a data trigger). If more than one
     * data is requirement is specified, then all the data requirements must be true.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDataRequirement>
     */
    #[FHIRDataRequirement]
    protected array $data;
    /**
     * A expression that is evaluated in a specified context and returns a value. The
     * context of use of the expression must specify the context in which the
     * expression is evaluated, and how the result of the expression is used.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A boolean-valued expression that is evaluated in the context of the container of
     * the trigger definition and returns whether or not the trigger fires.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExpression
     */
    #[FHIRExpression]
    protected FHIRExpression $condition;

    /* constructor.php:61 */
    /**
     * FHIRTriggerDefinition Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRTriggerTypeList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTriggerType $type
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $name
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTiming $timingTiming
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $timingReference
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDatePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDate $timingDate
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $timingDateTime
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDataRequirement> $data
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExpression $condition
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|string|FHIRTriggerTypeList|FHIRTriggerType $type = null,
                                null|string|FHIRStringPrimitive|FHIRString $name = null,
                                null|FHIRTiming $timingTiming = null,
                                null|FHIRReference $timingReference = null,
                                null|string|\DateTimeInterface|FHIRDatePrimitive|FHIRDate $timingDate = null,
                                null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $timingDateTime = null,
                                null|iterable $data = null,
                                null|FHIRExpression $condition = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            fhirComments: $fhirComments);
        if (null !== $type) {
            $this->setType($type);
        }
        if (null !== $name) {
            $this->setName($name);
        }
        if (null !== $timingTiming) {
            $this->setTimingTiming($timingTiming);
        }
        if (null !== $timingReference) {
            $this->setTimingReference($timingReference);
        }
        if (null !== $timingDate) {
            $this->setTimingDate($timingDate);
        }
        if (null !== $timingDateTime) {
            $this->setTimingDateTime($timingDateTime);
        }
        if (null !== $data) {
            $this->setData(...$data);
        }
        if (null !== $condition) {
            $this->setCondition($condition);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * The type of trigger.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The type of triggering event.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTriggerType
     */
    public function getType(): null|FHIRTriggerType
    {
        return $this->type ?? null;
    }

    /**
     * The type of trigger.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The type of triggering event.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRTriggerTypeList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTriggerType $type
     * @return static
     */
    public function setType(null|string|FHIRTriggerTypeList|FHIRTriggerType $type): self
    {
        if (null === $type) {
            unset($this->type);
            return $this;
        }
        if (!($type instanceof FHIRTriggerType)) {
            $type = new FHIRTriggerType(value: $type);
        }
        $this->type = $type;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A formal name for the event. This may be an absolute URI that identifies the
     * event formally (e.g. from a trigger registry), or a simple relative URI that
     * identifies the event in a local context.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getName(): null|FHIRString
    {
        return $this->name ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A formal name for the event. This may be an absolute URI that identifies the
     * event formally (e.g. from a trigger registry), or a simple relative URI that
     * identifies the event in a local context.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $name
     * @return static
     */
    public function setName(null|string|FHIRStringPrimitive|FHIRString $name): self
    {
        if (null === $name) {
            unset($this->name);
            return $this;
        }
        if (!($name instanceof FHIRString)) {
            $name = new FHIRString(value: $name);
        }
        $this->name = $name;
        return $this;
    }

    /**
     * Specifies an event that may occur multiple times. Timing schedules are used to
     * record when things are planned, expected or requested to occur. The most common
     * usage is in dosage instructions for medications. They are also used when
     * planning care of various kinds, and may be used for reporting the schedule to
     * which past regular activities were carried out.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The timing of the event (if this is a periodic trigger). (choose any one of
     * timing*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTiming
     */
    public function getTimingTiming(): null|FHIRTiming
    {
        return $this->timingTiming ?? null;
    }

    /**
     * Specifies an event that may occur multiple times. Timing schedules are used to
     * record when things are planned, expected or requested to occur. The most common
     * usage is in dosage instructions for medications. They are also used when
     * planning care of various kinds, and may be used for reporting the schedule to
     * which past regular activities were carried out.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The timing of the event (if this is a periodic trigger). (choose any one of
     * timing*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTiming $timingTiming
     * @return static
     */
    public function setTimingTiming(null|FHIRTiming $timingTiming): self
    {
        if (null === $timingTiming) {
            unset($this->timingTiming);
            return $this;
        }
        $this->timingTiming = $timingTiming;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The timing of the event (if this is a periodic trigger). (choose any one of
     * timing*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getTimingReference(): null|FHIRReference
    {
        return $this->timingReference ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The timing of the event (if this is a periodic trigger). (choose any one of
     * timing*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $timingReference
     * @return static
     */
    public function setTimingReference(null|FHIRReference $timingReference): self
    {
        if (null === $timingReference) {
            unset($this->timingReference);
            return $this;
        }
        $this->timingReference = $timingReference;
        return $this;
    }

    /**
     * A date or partial date (e.g. just year or year + month). There is no time zone.
     * The format is a union of the schema types gYear, gYearMonth and date. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The timing of the event (if this is a periodic trigger). (choose any one of
     * timing*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDate
     */
    public function getTimingDate(): null|FHIRDate
    {
        return $this->timingDate ?? null;
    }

    /**
     * A date or partial date (e.g. just year or year + month). There is no time zone.
     * The format is a union of the schema types gYear, gYearMonth and date. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The timing of the event (if this is a periodic trigger). (choose any one of
     * timing*, but only one)
     *
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDatePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDate $timingDate
     * @return static
     */
    public function setTimingDate(null|string|\DateTimeInterface|FHIRDatePrimitive|FHIRDate $timingDate): self
    {
        if (null === $timingDate) {
            unset($this->timingDate);
            return $this;
        }
        if (!($timingDate instanceof FHIRDate)) {
            $timingDate = new FHIRDate(value: $timingDate);
        }
        $this->timingDate = $timingDate;
        return $this;
    }

    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The timing of the event (if this is a periodic trigger). (choose any one of
     * timing*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    public function getTimingDateTime(): null|FHIRDateTime
    {
        return $this->timingDateTime ?? null;
    }

    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The timing of the event (if this is a periodic trigger). (choose any one of
     * timing*, but only one)
     *
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $timingDateTime
     * @return static
     */
    public function setTimingDateTime(null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $timingDateTime): self
    {
        if (null === $timingDateTime) {
            unset($this->timingDateTime);
            return $this;
        }
        if (!($timingDateTime instanceof FHIRDateTime)) {
            $timingDateTime = new FHIRDateTime(value: $timingDateTime);
        }
        $this->timingDateTime = $timingDateTime;
        return $this;
    }

    /**
     * Describes a required data item for evaluation in terms of the type of data, and
     * optional code or date-based filters of the data.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The triggering data of the event (if this is a data trigger). If more than one
     * data is requirement is specified, then all the data requirements must be true.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDataRequirement>
     */
    public function getData(): array
    {
        return $this->data ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDataRequirement>
     */
    public function getDataIterator(): iterable
    {
        if (!isset($this->data)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->data);
    }

    /**
     * Describes a required data item for evaluation in terms of the type of data, and
     * optional code or date-based filters of the data.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The triggering data of the event (if this is a data trigger). If more than one
     * data is requirement is specified, then all the data requirements must be true.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDataRequirement $data
     * @return static
     */
    public function addData(FHIRDataRequirement $data): self
    {
        if (!isset($this->data)) {
            $this->data = [];
        }
        $this->data[] = $data;
        return $this;
    }

    /**
     * Describes a required data item for evaluation in terms of the type of data, and
     * optional code or date-based filters of the data.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The triggering data of the event (if this is a data trigger). If more than one
     * data is requirement is specified, then all the data requirements must be true.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDataRequirement ...$data
     * @return static
     */
    public function setData(FHIRDataRequirement ...$data): self
    {
        if ([] === $data) {
            unset($this->data);
            return $this;
        }
        $this->data = $data;
        return $this;
    }

    /**
     * A expression that is evaluated in a specified context and returns a value. The
     * context of use of the expression must specify the context in which the
     * expression is evaluated, and how the result of the expression is used.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A boolean-valued expression that is evaluated in the context of the container of
     * the trigger definition and returns whether or not the trigger fires.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExpression
     */
    public function getCondition(): null|FHIRExpression
    {
        return $this->condition ?? null;
    }

    /**
     * A expression that is evaluated in a specified context and returns a value. The
     * context of use of the expression must specify the context in which the
     * expression is evaluated, and how the result of the expression is used.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A boolean-valued expression that is evaluated in the context of the container of
     * the trigger definition and returns whether or not the trigger fires.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExpression $condition
     * @return static
     */
    public function setCondition(null|FHIRExpression $condition): self
    {
        if (null === $condition) {
            unset($this->condition);
            return $this;
        }
        $this->condition = $condition;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTriggerDefinition $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTriggerDefinition
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRTriggerDefinition)) {
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
            } else if (self::FIELD_TYPE === $cen) {
                $type->setType(FHIRTriggerType::xmlUnserialize($ce, $config));
            } else if (self::FIELD_NAME === $cen) {
                $type->setName(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TIMING_TIMING === $cen) {
                $type->setTimingTiming(FHIRTiming::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TIMING_REFERENCE === $cen) {
                $type->setTimingReference(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TIMING_DATE === $cen) {
                $type->setTimingDate(FHIRDate::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TIMING_DATE_TIME === $cen) {
                $type->setTimingDateTime(FHIRDateTime::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DATA === $cen) {
                $type->addData(FHIRDataRequirement::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CONDITION === $cen) {
                $type->setCondition(FHIRExpression::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_TYPE])) {
            if (isset($type->type)) {
                $type->type->setValue((string)$attributes[self::FIELD_TYPE]);
            } else {
                $type->setType((string)$attributes[self::FIELD_TYPE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_TYPE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_NAME])) {
            if (isset($type->name)) {
                $type->name->setValue((string)$attributes[self::FIELD_NAME]);
            } else {
                $type->setName((string)$attributes[self::FIELD_NAME]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_NAME, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_TIMING_DATE])) {
            if (isset($type->timingDate)) {
                $type->timingDate->setValue((string)$attributes[self::FIELD_TIMING_DATE]);
            } else {
                $type->setTimingDate((string)$attributes[self::FIELD_TIMING_DATE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_TIMING_DATE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_TIMING_DATE_TIME])) {
            if (isset($type->timingDateTime)) {
                $type->timingDateTime->setValue((string)$attributes[self::FIELD_TIMING_DATE_TIME]);
            } else {
                $type->setTimingDateTime((string)$attributes[self::FIELD_TIMING_DATE_TIME]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_TIMING_DATE_TIME, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->type) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_TYPE]) {
            $xw->writeAttribute(self::FIELD_TYPE, $this->type->_getValueAsString());
        }
        if (isset($this->name) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_NAME]) {
            $xw->writeAttribute(self::FIELD_NAME, $this->name->_getValueAsString());
        }
        if (isset($this->timingDate) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_TIMING_DATE]) {
            $xw->writeAttribute(self::FIELD_TIMING_DATE, $this->timingDate->_getValueAsString());
        }
        if (isset($this->timingDateTime) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_TIMING_DATE_TIME]) {
            $xw->writeAttribute(self::FIELD_TIMING_DATE_TIME, $this->timingDateTime->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->type)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_TYPE]
                || $this->type->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_TYPE);
            $this->type->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_TYPE]);
            $xw->endElement();
        }
        if (isset($this->name)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_NAME]
                || $this->name->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_NAME);
            $this->name->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_NAME]);
            $xw->endElement();
        }
        if (isset($this->timingTiming)) {
            $xw->startElement(self::FIELD_TIMING_TIMING);
            $this->timingTiming->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->timingReference)) {
            $xw->startElement(self::FIELD_TIMING_REFERENCE);
            $this->timingReference->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->timingDate)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_TIMING_DATE]
                || $this->timingDate->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_TIMING_DATE);
            $this->timingDate->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_TIMING_DATE]);
            $xw->endElement();
        }
        if (isset($this->timingDateTime)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_TIMING_DATE_TIME]
                || $this->timingDateTime->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_TIMING_DATE_TIME);
            $this->timingDateTime->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_TIMING_DATE_TIME]);
            $xw->endElement();
        }
        if (isset($this->data)) {
            foreach ($this->data as $v) {
                $xw->startElement(self::FIELD_DATA);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->condition)) {
            $xw->startElement(self::FIELD_CONDITION);
            $this->condition->xmlSerialize($xw, $config);
            $xw->endElement();
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTriggerDefinition $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTriggerDefinition
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
        } else if (!($type instanceof FHIRTriggerDefinition)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->type)
            || isset($decoded->_type)
            || property_exists($decoded, self::FIELD_TYPE)
            || property_exists($decoded, self::FIELD_TYPE_EXT)) {
            $v = $decoded->_type ?? new \stdClass();
            $v->value = $decoded->type ?? null;
            $type->setType(FHIRTriggerType::jsonUnserialize($v, $config));
        }
        if (isset($decoded->name)
            || isset($decoded->_name)
            || property_exists($decoded, self::FIELD_NAME)
            || property_exists($decoded, self::FIELD_NAME_EXT)) {
            $v = $decoded->_name ?? new \stdClass();
            $v->value = $decoded->name ?? null;
            $type->setName(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->timingTiming) || property_exists($decoded, self::FIELD_TIMING_TIMING)) {
            if (is_array($decoded->timingTiming)) {
                $type->setTimingTiming(FHIRTiming::jsonUnserialize(reset($decoded->timingTiming), $config));
            } else {
                $type->setTimingTiming(FHIRTiming::jsonUnserialize($decoded->timingTiming, $config));
            }
        }
        if (isset($decoded->timingReference) || property_exists($decoded, self::FIELD_TIMING_REFERENCE)) {
            if (is_array($decoded->timingReference)) {
                $type->setTimingReference(FHIRReference::jsonUnserialize(reset($decoded->timingReference), $config));
            } else {
                $type->setTimingReference(FHIRReference::jsonUnserialize($decoded->timingReference, $config));
            }
        }
        if (isset($decoded->timingDate)
            || isset($decoded->_timingDate)
            || property_exists($decoded, self::FIELD_TIMING_DATE)
            || property_exists($decoded, self::FIELD_TIMING_DATE_EXT)) {
            $v = $decoded->_timingDate ?? new \stdClass();
            $v->value = $decoded->timingDate ?? null;
            $type->setTimingDate(FHIRDate::jsonUnserialize($v, $config));
        }
        if (isset($decoded->timingDateTime)
            || isset($decoded->_timingDateTime)
            || property_exists($decoded, self::FIELD_TIMING_DATE_TIME)
            || property_exists($decoded, self::FIELD_TIMING_DATE_TIME_EXT)) {
            $v = $decoded->_timingDateTime ?? new \stdClass();
            $v->value = $decoded->timingDateTime ?? null;
            $type->setTimingDateTime(FHIRDateTime::jsonUnserialize($v, $config));
        }
        if (isset($decoded->data) || property_exists($decoded, self::FIELD_DATA)) {
            if (is_object($decoded->data)) {
                $vals = [$decoded->data];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_DATA, true);
            } else {
                $vals = $decoded->data;
            }
            foreach($vals as $v) {
                $type->addData(FHIRDataRequirement::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->condition) || property_exists($decoded, self::FIELD_CONDITION)) {
            if (is_array($decoded->condition)) {
                $type->setCondition(FHIRExpression::jsonUnserialize(reset($decoded->condition), $config));
            } else {
                $type->setCondition(FHIRExpression::jsonUnserialize($decoded->condition, $config));
            }
        }
        return $type;
    }

    /**
     * @return \stdClass
     */
    public function jsonSerialize(): mixed
    {
        $out = parent::jsonSerialize();
        if (isset($this->type)) {
            if (null !== ($val = $this->type->getValue())) {
                $out->type = $val;
            }
            if ($this->type->_nonValueFieldDefined()) {
                $ext = $this->type->jsonSerialize();
                unset($ext->value);
                $out->_type = $ext;
            }
        }
        if (isset($this->name)) {
            if (null !== ($val = $this->name->getValue())) {
                $out->name = $val;
            }
            if ($this->name->_nonValueFieldDefined()) {
                $ext = $this->name->jsonSerialize();
                unset($ext->value);
                $out->_name = $ext;
            }
        }
        if (isset($this->timingTiming)) {
            $out->timingTiming = $this->timingTiming;
        }
        if (isset($this->timingReference)) {
            $out->timingReference = $this->timingReference;
        }
        if (isset($this->timingDate)) {
            if (null !== ($val = $this->timingDate->getValue())) {
                $out->timingDate = $val;
            }
            if ($this->timingDate->_nonValueFieldDefined()) {
                $ext = $this->timingDate->jsonSerialize();
                unset($ext->value);
                $out->_timingDate = $ext;
            }
        }
        if (isset($this->timingDateTime)) {
            if (null !== ($val = $this->timingDateTime->getValue())) {
                $out->timingDateTime = $val;
            }
            if ($this->timingDateTime->_nonValueFieldDefined()) {
                $ext = $this->timingDateTime->jsonSerialize();
                unset($ext->value);
                $out->_timingDateTime = $ext;
            }
        }
        if (isset($this->data) && [] !== $this->data) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_DATA) && 1 === count($this->data)) {
                $out->data = $this->data[0];
            } else {
                $out->data = $this->data;
            }
        }
        if (isset($this->condition)) {
            $out->condition = $this->condition;
        }
        return $out;
    }
}
