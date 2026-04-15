<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRLocation;

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
 * Details and position information for a physical place where services are
 * provided and resources and participants may be stored, found, contained, or
 * accommodated.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRLocationHoursOfOperation extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_LOCATION_DOT_HOURS_OF_OPERATION;

    /* class_default.php:56 */
    public const FIELD_DAYS_OF_WEEK = 'daysOfWeek';
    public const FIELD_DAYS_OF_WEEK_EXT = '_daysOfWeek';
    public const FIELD_ALL_DAY = 'allDay';
    public const FIELD_ALL_DAY_EXT = '_allDay';
    public const FIELD_OPENING_TIME = 'openingTime';
    public const FIELD_OPENING_TIME_EXT = '_openingTime';
    public const FIELD_CLOSING_TIME = 'closingTime';
    public const FIELD_CLOSING_TIME_EXT = '_closingTime';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_ALL_DAY => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_OPENING_TIME => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_CLOSING_TIME => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
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
     * The Location is open all day.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    #[FHIRBoolean]
    protected FHIRBoolean $allDay;
    /**
     * A time during the day, with no date specified
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Time that the Location opens.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTime
     */
    #[FHIRTime]
    protected FHIRTime $openingTime;
    /**
     * A time during the day, with no date specified
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Time that the Location closes.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTime
     */
    #[FHIRTime]
    protected FHIRTime $closingTime;

    /* constructor.php:61 */
    /**
     * FHIRLocationHoursOfOperation Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|iterable<string>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRDaysOfWeekList>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDaysOfWeek> $daysOfWeek
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $allDay
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTime $openingTime
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTime $closingTime
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|iterable $daysOfWeek = null,
                                null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $allDay = null,
                                null|string|\DateTimeInterface|FHIRTimePrimitive|FHIRTime $openingTime = null,
                                null|string|\DateTimeInterface|FHIRTimePrimitive|FHIRTime $closingTime = null,
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
        if (null !== $openingTime) {
            $this->setOpeningTime($openingTime);
        }
        if (null !== $closingTime) {
            $this->setClosingTime($closingTime);
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
     * The Location is open all day.
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
     * The Location is open all day.
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
     * Time that the Location opens.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTime
     */
    public function getOpeningTime(): null|FHIRTime
    {
        return $this->openingTime ?? null;
    }

    /**
     * A time during the day, with no date specified
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Time that the Location opens.
     *
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTime $openingTime
     * @return static
     */
    public function setOpeningTime(null|string|\DateTimeInterface|FHIRTimePrimitive|FHIRTime $openingTime): self
    {
        if (null === $openingTime) {
            unset($this->openingTime);
            return $this;
        }
        if (!($openingTime instanceof FHIRTime)) {
            $openingTime = new FHIRTime(value: $openingTime);
        }
        $this->openingTime = $openingTime;
        return $this;
    }

    /**
     * A time during the day, with no date specified
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Time that the Location closes.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTime
     */
    public function getClosingTime(): null|FHIRTime
    {
        return $this->closingTime ?? null;
    }

    /**
     * A time during the day, with no date specified
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Time that the Location closes.
     *
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTime $closingTime
     * @return static
     */
    public function setClosingTime(null|string|\DateTimeInterface|FHIRTimePrimitive|FHIRTime $closingTime): self
    {
        if (null === $closingTime) {
            unset($this->closingTime);
            return $this;
        }
        if (!($closingTime instanceof FHIRTime)) {
            $closingTime = new FHIRTime(value: $closingTime);
        }
        $this->closingTime = $closingTime;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRLocation\FHIRLocationHoursOfOperation $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRLocation\FHIRLocationHoursOfOperation
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRLocationHoursOfOperation)) {
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
            } else if (self::FIELD_OPENING_TIME === $cen) {
                $type->setOpeningTime(FHIRTime::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CLOSING_TIME === $cen) {
                $type->setClosingTime(FHIRTime::xmlUnserialize($ce, $config));
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
        if (isset($attributes[self::FIELD_OPENING_TIME])) {
            if (isset($type->openingTime)) {
                $type->openingTime->setValue((string)$attributes[self::FIELD_OPENING_TIME]);
            } else {
                $type->setOpeningTime((string)$attributes[self::FIELD_OPENING_TIME]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_OPENING_TIME, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_CLOSING_TIME])) {
            if (isset($type->closingTime)) {
                $type->closingTime->setValue((string)$attributes[self::FIELD_CLOSING_TIME]);
            } else {
                $type->setClosingTime((string)$attributes[self::FIELD_CLOSING_TIME]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_CLOSING_TIME, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->openingTime) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_OPENING_TIME]) {
            $xw->writeAttribute(self::FIELD_OPENING_TIME, $this->openingTime->_getValueAsString());
        }
        if (isset($this->closingTime) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_CLOSING_TIME]) {
            $xw->writeAttribute(self::FIELD_CLOSING_TIME, $this->closingTime->_getValueAsString());
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
        if (isset($this->openingTime)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_OPENING_TIME]
                || $this->openingTime->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_OPENING_TIME);
            $this->openingTime->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_OPENING_TIME]);
            $xw->endElement();
        }
        if (isset($this->closingTime)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_CLOSING_TIME]
                || $this->closingTime->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_CLOSING_TIME);
            $this->closingTime->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_CLOSING_TIME]);
            $xw->endElement();
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRLocation\FHIRLocationHoursOfOperation $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRLocation\FHIRLocationHoursOfOperation
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
        } else if (!($type instanceof FHIRLocationHoursOfOperation)) {
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
        if (isset($decoded->openingTime)
            || isset($decoded->_openingTime)
            || property_exists($decoded, self::FIELD_OPENING_TIME)
            || property_exists($decoded, self::FIELD_OPENING_TIME_EXT)) {
            $v = $decoded->_openingTime ?? new \stdClass();
            $v->value = $decoded->openingTime ?? null;
            $type->setOpeningTime(FHIRTime::jsonUnserialize($v, $config));
        }
        if (isset($decoded->closingTime)
            || isset($decoded->_closingTime)
            || property_exists($decoded, self::FIELD_CLOSING_TIME)
            || property_exists($decoded, self::FIELD_CLOSING_TIME_EXT)) {
            $v = $decoded->_closingTime ?? new \stdClass();
            $v->value = $decoded->closingTime ?? null;
            $type->setClosingTime(FHIRTime::jsonUnserialize($v, $config));
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
        if (isset($this->openingTime)) {
            if (null !== ($val = $this->openingTime->getValue())) {
                $out->openingTime = $val;
            }
            if ($this->openingTime->_nonValueFieldDefined()) {
                $ext = $this->openingTime->jsonSerialize();
                unset($ext->value);
                $out->_openingTime = $ext;
            }
        }
        if (isset($this->closingTime)) {
            if (null !== ($val = $this->closingTime->getValue())) {
                $out->closingTime = $val;
            }
            if ($this->closingTime->_nonValueFieldDefined()) {
                $ext = $this->closingTime->jsonSerialize();
                unset($ext->value);
                $out->_closingTime = $ext;
            }
        }
        return $out;
    }
}
