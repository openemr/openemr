<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRHealthcareService;

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
use OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRDaysOfWeekList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDaysOfWeek;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTime;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRTimePrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * The details of a healthcare service available at a location.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRHealthcareServiceAvailableTime extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_HEALTHCARE_SERVICE_DOT_AVAILABLE_TIME;

    /* class_default.php:56 */
    public const FIELD_DAYS_OF_WEEK = 'daysOfWeek';
    public const FIELD_DAYS_OF_WEEK_EXT = '_daysOfWeek';
    public const FIELD_ALL_DAY = 'allDay';
    public const FIELD_ALL_DAY_EXT = '_allDay';
    public const FIELD_AVAILABLE_START_TIME = 'availableStartTime';
    public const FIELD_AVAILABLE_START_TIME_EXT = '_availableStartTime';
    public const FIELD_AVAILABLE_END_TIME = 'availableEndTime';
    public const FIELD_AVAILABLE_END_TIME_EXT = '_availableEndTime';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_ALL_DAY => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_AVAILABLE_START_TIME => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_AVAILABLE_END_TIME => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * The days of the week.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates which days of the week are available between the start and end Times.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDaysOfWeek>
     */
    #[FHIRDaysOfWeek]
    protected array $daysOfWeek;
    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Is this always available? (hence times are irrelevant) e.g. 24 hour service.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    #[FHIRBoolean]
    protected FHIRBoolean $allDay;
    /**
     * A time during the day, with no date specified
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The opening time of day. Note: If the AllDay flag is set, then this time is
     * ignored.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTime
     */
    #[FHIRTime]
    protected FHIRTime $availableStartTime;
    /**
     * A time during the day, with no date specified
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The closing time of day. Note: If the AllDay flag is set, then this time is
     * ignored.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTime
     */
    #[FHIRTime]
    protected FHIRTime $availableEndTime;

    /* constructor.php:61 */
    /**
     * FHIRHealthcareServiceAvailableTime Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|iterable<string>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRDaysOfWeekList>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDaysOfWeek> $daysOfWeek
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $allDay
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTime $availableStartTime
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTime $availableEndTime
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|iterable $daysOfWeek = null,
                                null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $allDay = null,
                                null|string|\DateTimeInterface|FHIRTimePrimitive|FHIRTime $availableStartTime = null,
                                null|string|\DateTimeInterface|FHIRTimePrimitive|FHIRTime $availableEndTime = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $daysOfWeek) {
            $this->setDaysOfWeek(...$daysOfWeek);
        }
        if (null !== $allDay) {
            $this->setAllDay($allDay);
        }
        if (null !== $availableStartTime) {
            $this->setAvailableStartTime($availableStartTime);
        }
        if (null !== $availableEndTime) {
            $this->setAvailableEndTime($availableEndTime);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * The days of the week.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates which days of the week are available between the start and end Times.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDaysOfWeek>
     */
    public function getDaysOfWeek(): array
    {
        return $this->daysOfWeek ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDaysOfWeek>
     */
    public function getDaysOfWeekIterator(): iterable
    {
        if (!isset($this->daysOfWeek)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->daysOfWeek);
    }

    /**
     * The days of the week.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates which days of the week are available between the start and end Times.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRDaysOfWeekList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDaysOfWeek $daysOfWeek
     * @return static
     */
    public function addDaysOfWeek(string|FHIRDaysOfWeekList|FHIRDaysOfWeek $daysOfWeek): self
    {
        if (!($daysOfWeek instanceof FHIRDaysOfWeek)) {
            $daysOfWeek = new FHIRDaysOfWeek(value: $daysOfWeek);
        }
        if (!isset($this->daysOfWeek)) {
            $this->daysOfWeek = [];
        }
        $this->daysOfWeek[] = $daysOfWeek;
        return $this;
    }

    /**
     * The days of the week.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates which days of the week are available between the start and end Times.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRDaysOfWeekList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDaysOfWeek ...$daysOfWeek
     * @return static
     */
    public function setDaysOfWeek(string|FHIRDaysOfWeekList|FHIRDaysOfWeek ...$daysOfWeek): self
    {
        if ([] === $daysOfWeek) {
            unset($this->daysOfWeek);
            return $this;
        }
        $this->daysOfWeek = [];
        foreach($daysOfWeek as $v) {
            if ($v instanceof FHIRDaysOfWeek) {
                $this->daysOfWeek[] = $v;
            } else {
                $this->daysOfWeek[] = new FHIRDaysOfWeek(value: $v);
            }
        }
        return $this;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Is this always available? (hence times are irrelevant) e.g. 24 hour service.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    public function getAllDay(): null|FHIRBoolean
    {
        return $this->allDay ?? null;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Is this always available? (hence times are irrelevant) e.g. 24 hour service.
     *
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $allDay
     * @return static
     */
    public function setAllDay(null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $allDay): self
    {
        if (null === $allDay) {
            unset($this->allDay);
            return $this;
        }
        if (!($allDay instanceof FHIRBoolean)) {
            $allDay = new FHIRBoolean(value: $allDay);
        }
        $this->allDay = $allDay;
        return $this;
    }

    /**
     * A time during the day, with no date specified
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The opening time of day. Note: If the AllDay flag is set, then this time is
     * ignored.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTime
     */
    public function getAvailableStartTime(): null|FHIRTime
    {
        return $this->availableStartTime ?? null;
    }

    /**
     * A time during the day, with no date specified
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The opening time of day. Note: If the AllDay flag is set, then this time is
     * ignored.
     *
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTime $availableStartTime
     * @return static
     */
    public function setAvailableStartTime(null|string|\DateTimeInterface|FHIRTimePrimitive|FHIRTime $availableStartTime): self
    {
        if (null === $availableStartTime) {
            unset($this->availableStartTime);
            return $this;
        }
        if (!($availableStartTime instanceof FHIRTime)) {
            $availableStartTime = new FHIRTime(value: $availableStartTime);
        }
        $this->availableStartTime = $availableStartTime;
        return $this;
    }

    /**
     * A time during the day, with no date specified
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The closing time of day. Note: If the AllDay flag is set, then this time is
     * ignored.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTime
     */
    public function getAvailableEndTime(): null|FHIRTime
    {
        return $this->availableEndTime ?? null;
    }

    /**
     * A time during the day, with no date specified
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The closing time of day. Note: If the AllDay flag is set, then this time is
     * ignored.
     *
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTime $availableEndTime
     * @return static
     */
    public function setAvailableEndTime(null|string|\DateTimeInterface|FHIRTimePrimitive|FHIRTime $availableEndTime): self
    {
        if (null === $availableEndTime) {
            unset($this->availableEndTime);
            return $this;
        }
        if (!($availableEndTime instanceof FHIRTime)) {
            $availableEndTime = new FHIRTime(value: $availableEndTime);
        }
        $this->availableEndTime = $availableEndTime;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRHealthcareService\FHIRHealthcareServiceAvailableTime $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRHealthcareService\FHIRHealthcareServiceAvailableTime
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRHealthcareServiceAvailableTime)) {
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
            } else if (self::FIELD_DAYS_OF_WEEK === $cen) {
                $type->addDaysOfWeek(FHIRDaysOfWeek::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ALL_DAY === $cen) {
                $type->setAllDay(FHIRBoolean::xmlUnserialize($ce, $config));
            } else if (self::FIELD_AVAILABLE_START_TIME === $cen) {
                $type->setAvailableStartTime(FHIRTime::xmlUnserialize($ce, $config));
            } else if (self::FIELD_AVAILABLE_END_TIME === $cen) {
                $type->setAvailableEndTime(FHIRTime::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_ALL_DAY])) {
            if (isset($type->allDay)) {
                $type->allDay->setValue((string)$attributes[self::FIELD_ALL_DAY]);
            } else {
                $type->setAllDay((string)$attributes[self::FIELD_ALL_DAY]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_ALL_DAY, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_AVAILABLE_START_TIME])) {
            if (isset($type->availableStartTime)) {
                $type->availableStartTime->setValue((string)$attributes[self::FIELD_AVAILABLE_START_TIME]);
            } else {
                $type->setAvailableStartTime((string)$attributes[self::FIELD_AVAILABLE_START_TIME]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_AVAILABLE_START_TIME, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_AVAILABLE_END_TIME])) {
            if (isset($type->availableEndTime)) {
                $type->availableEndTime->setValue((string)$attributes[self::FIELD_AVAILABLE_END_TIME]);
            } else {
                $type->setAvailableEndTime((string)$attributes[self::FIELD_AVAILABLE_END_TIME]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_AVAILABLE_END_TIME, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->allDay) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_ALL_DAY]) {
            $xw->writeAttribute(self::FIELD_ALL_DAY, $this->allDay->_getValueAsString());
        }
        if (isset($this->availableStartTime) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_AVAILABLE_START_TIME]) {
            $xw->writeAttribute(self::FIELD_AVAILABLE_START_TIME, $this->availableStartTime->_getValueAsString());
        }
        if (isset($this->availableEndTime) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_AVAILABLE_END_TIME]) {
            $xw->writeAttribute(self::FIELD_AVAILABLE_END_TIME, $this->availableEndTime->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->daysOfWeek) && [] !== $this->daysOfWeek) {
            foreach($this->daysOfWeek as $v) {
                $xw->startElement(self::FIELD_DAYS_OF_WEEK);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->allDay)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_ALL_DAY]
                || $this->allDay->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_ALL_DAY);
            $this->allDay->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_ALL_DAY]);
            $xw->endElement();
        }
        if (isset($this->availableStartTime)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_AVAILABLE_START_TIME]
                || $this->availableStartTime->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_AVAILABLE_START_TIME);
            $this->availableStartTime->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_AVAILABLE_START_TIME]);
            $xw->endElement();
        }
        if (isset($this->availableEndTime)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_AVAILABLE_END_TIME]
                || $this->availableEndTime->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_AVAILABLE_END_TIME);
            $this->availableEndTime->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_AVAILABLE_END_TIME]);
            $xw->endElement();
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRHealthcareService\FHIRHealthcareServiceAvailableTime $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRHealthcareService\FHIRHealthcareServiceAvailableTime
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
        } else if (!($type instanceof FHIRHealthcareServiceAvailableTime)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->daysOfWeek)
            || isset($decoded->_daysOfWeek)
            || property_exists($decoded, self::FIELD_DAYS_OF_WEEK)
            || property_exists($decoded, self::FIELD_DAYS_OF_WEEK_EXT)) {
            $vals = (array)($decoded->daysOfWeek ?? []);
            $exts = (array)($decoded->_daysOfWeek ?? []);
            $valCnt = count($vals);
            $extCnt = count($exts);
            if ($extCnt > $valCnt) {
                $valCnt = $extCnt;
            }
            for ($i = 0; $i < $valCnt; $i++) {
                $v = $exts[$i] ?? new \stdClass();
                $v->value = $vals[$i] ?? null;
                $type->addDaysOfWeek(FHIRDaysOfWeek::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->allDay)
            || isset($decoded->_allDay)
            || property_exists($decoded, self::FIELD_ALL_DAY)
            || property_exists($decoded, self::FIELD_ALL_DAY_EXT)) {
            $v = $decoded->_allDay ?? new \stdClass();
            $v->value = $decoded->allDay ?? null;
            $type->setAllDay(FHIRBoolean::jsonUnserialize($v, $config));
        }
        if (isset($decoded->availableStartTime)
            || isset($decoded->_availableStartTime)
            || property_exists($decoded, self::FIELD_AVAILABLE_START_TIME)
            || property_exists($decoded, self::FIELD_AVAILABLE_START_TIME_EXT)) {
            $v = $decoded->_availableStartTime ?? new \stdClass();
            $v->value = $decoded->availableStartTime ?? null;
            $type->setAvailableStartTime(FHIRTime::jsonUnserialize($v, $config));
        }
        if (isset($decoded->availableEndTime)
            || isset($decoded->_availableEndTime)
            || property_exists($decoded, self::FIELD_AVAILABLE_END_TIME)
            || property_exists($decoded, self::FIELD_AVAILABLE_END_TIME_EXT)) {
            $v = $decoded->_availableEndTime ?? new \stdClass();
            $v->value = $decoded->availableEndTime ?? null;
            $type->setAvailableEndTime(FHIRTime::jsonUnserialize($v, $config));
        }
        return $type;
    }

    /**
     * @return \stdClass
     */
    public function jsonSerialize(): mixed
    {
        $out = parent::jsonSerialize();
        if (isset($this->daysOfWeek) && [] !== $this->daysOfWeek) {
            $vals = [];
            $exts = [];
            $hasVals = false;
            $hasExts = false;
            foreach ($this->daysOfWeek as $v) {
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
                $out->daysOfWeek = $vals;
            }
            if ($hasExts) {
                $out->_daysOfWeek = $exts;
            }
        }
        if (isset($this->allDay)) {
            if (null !== ($val = $this->allDay->getValue())) {
                $out->allDay = $val;
            }
            if ($this->allDay->_nonValueFieldDefined()) {
                $ext = $this->allDay->jsonSerialize();
                unset($ext->value);
                $out->_allDay = $ext;
            }
        }
        if (isset($this->availableStartTime)) {
            if (null !== ($val = $this->availableStartTime->getValue())) {
                $out->availableStartTime = $val;
            }
            if ($this->availableStartTime->_nonValueFieldDefined()) {
                $ext = $this->availableStartTime->jsonSerialize();
                unset($ext->value);
                $out->_availableStartTime = $ext;
            }
        }
        if (isset($this->availableEndTime)) {
            if (null !== ($val = $this->availableEndTime->getValue())) {
                $out->availableEndTime = $val;
            }
            if ($this->availableEndTime->_nonValueFieldDefined()) {
                $ext = $this->availableEndTime->jsonSerialize();
                unset($ext->value);
                $out->_availableEndTime = $ext;
            }
        }
        return $out;
    }
}
