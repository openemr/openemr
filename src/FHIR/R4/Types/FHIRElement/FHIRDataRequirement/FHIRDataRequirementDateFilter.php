<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDataRequirement;

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
use OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * Describes a required data item for evaluation in terms of the type of data, and
 * optional code or date-based filters of the data.
 * If the element is present, it must have a value for at least one of the defined
 * elements, an \@id referenced from the Narrative, or extensions
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRDataRequirementDateFilter extends FHIRElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_DATA_REQUIREMENT_DOT_DATE_FILTER;

    /* class_default.php:56 */
    public const FIELD_PATH = 'path';
    public const FIELD_PATH_EXT = '_path';
    public const FIELD_SEARCH_PARAM = 'searchParam';
    public const FIELD_SEARCH_PARAM_EXT = '_searchParam';
    public const FIELD_VALUE_DATE_TIME = 'valueDateTime';
    public const FIELD_VALUE_DATE_TIME_EXT = '_valueDateTime';
    public const FIELD_VALUE_PERIOD = 'valuePeriod';
    public const FIELD_VALUE_DURATION = 'valueDuration';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_PATH => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_SEARCH_PARAM => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_VALUE_DATE_TIME => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The date-valued attribute of the filter. The specified path SHALL be a FHIRPath
     * resolveable on the specified type of the DataRequirement, and SHALL consist only
     * of identifiers, constant indexers, and .resolve(). The path is allowed to
     * contain qualifiers (.) to traverse sub-elements, as well as indexers ([x]) to
     * traverse multiple-cardinality sub-elements (see the [Simple FHIRPath
     * Profile](fhirpath.html#simple) for full details). Note that the index must be an
     * integer constant. The path must resolve to an element of type date, dateTime,
     * Period, Schedule, or Timing.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $path;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A date parameter that refers to a search parameter defined on the specified type
     * of the DataRequirement, and which searches on elements of type date, dateTime,
     * Period, Schedule, or Timing.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $searchParam;
    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The value of the filter. If period is specified, the filter will return only
     * those data items that fall within the bounds determined by the Period, inclusive
     * of the period boundaries. If dateTime is specified, the filter will return only
     * those data items that are equal to the specified dateTime. If a Duration is
     * specified, the filter will return only those data items that fall within
     * Duration before now. (choose any one of value*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    #[FHIRDateTime]
    protected FHIRDateTime $valueDateTime;
    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The value of the filter. If period is specified, the filter will return only
     * those data items that fall within the bounds determined by the Period, inclusive
     * of the period boundaries. If dateTime is specified, the filter will return only
     * those data items that are equal to the specified dateTime. If a Duration is
     * specified, the filter will return only those data items that fall within
     * Duration before now. (choose any one of value*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod
     */
    #[FHIRPeriod]
    protected FHIRPeriod $valuePeriod;
    /**
     * A length of time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The value of the filter. If period is specified, the filter will return only
     * those data items that fall within the bounds determined by the Period, inclusive
     * of the period boundaries. If dateTime is specified, the filter will return only
     * those data items that are equal to the specified dateTime. If a Duration is
     * specified, the filter will return only those data items that fall within
     * Duration before now. (choose any one of value*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration
     */
    #[FHIRDuration]
    protected FHIRDuration $valueDuration;

    /* constructor.php:61 */
    /**
     * FHIRDataRequirementDateFilter Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $path
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $searchParam
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $valueDateTime
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $valuePeriod
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration $valueDuration
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|string|FHIRStringPrimitive|FHIRString $path = null,
                                null|string|FHIRStringPrimitive|FHIRString $searchParam = null,
                                null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $valueDateTime = null,
                                null|FHIRPeriod $valuePeriod = null,
                                null|FHIRDuration $valueDuration = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            fhirComments: $fhirComments);
        if (null !== $path) {
            $this->setPath($path);
        }
        if (null !== $searchParam) {
            $this->setSearchParam($searchParam);
        }
        if (null !== $valueDateTime) {
            $this->setValueDateTime($valueDateTime);
        }
        if (null !== $valuePeriod) {
            $this->setValuePeriod($valuePeriod);
        }
        if (null !== $valueDuration) {
            $this->setValueDuration($valueDuration);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The date-valued attribute of the filter. The specified path SHALL be a FHIRPath
     * resolveable on the specified type of the DataRequirement, and SHALL consist only
     * of identifiers, constant indexers, and .resolve(). The path is allowed to
     * contain qualifiers (.) to traverse sub-elements, as well as indexers ([x]) to
     * traverse multiple-cardinality sub-elements (see the [Simple FHIRPath
     * Profile](fhirpath.html#simple) for full details). Note that the index must be an
     * integer constant. The path must resolve to an element of type date, dateTime,
     * Period, Schedule, or Timing.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getPath(): null|FHIRString
    {
        return $this->path ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The date-valued attribute of the filter. The specified path SHALL be a FHIRPath
     * resolveable on the specified type of the DataRequirement, and SHALL consist only
     * of identifiers, constant indexers, and .resolve(). The path is allowed to
     * contain qualifiers (.) to traverse sub-elements, as well as indexers ([x]) to
     * traverse multiple-cardinality sub-elements (see the [Simple FHIRPath
     * Profile](fhirpath.html#simple) for full details). Note that the index must be an
     * integer constant. The path must resolve to an element of type date, dateTime,
     * Period, Schedule, or Timing.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $path
     * @return static
     */
    public function setPath(null|string|FHIRStringPrimitive|FHIRString $path): self
    {
        if (null === $path) {
            unset($this->path);
            return $this;
        }
        if (!($path instanceof FHIRString)) {
            $path = new FHIRString(value: $path);
        }
        $this->path = $path;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A date parameter that refers to a search parameter defined on the specified type
     * of the DataRequirement, and which searches on elements of type date, dateTime,
     * Period, Schedule, or Timing.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getSearchParam(): null|FHIRString
    {
        return $this->searchParam ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A date parameter that refers to a search parameter defined on the specified type
     * of the DataRequirement, and which searches on elements of type date, dateTime,
     * Period, Schedule, or Timing.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $searchParam
     * @return static
     */
    public function setSearchParam(null|string|FHIRStringPrimitive|FHIRString $searchParam): self
    {
        if (null === $searchParam) {
            unset($this->searchParam);
            return $this;
        }
        if (!($searchParam instanceof FHIRString)) {
            $searchParam = new FHIRString(value: $searchParam);
        }
        $this->searchParam = $searchParam;
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
     * The value of the filter. If period is specified, the filter will return only
     * those data items that fall within the bounds determined by the Period, inclusive
     * of the period boundaries. If dateTime is specified, the filter will return only
     * those data items that are equal to the specified dateTime. If a Duration is
     * specified, the filter will return only those data items that fall within
     * Duration before now. (choose any one of value*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    public function getValueDateTime(): null|FHIRDateTime
    {
        return $this->valueDateTime ?? null;
    }

    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The value of the filter. If period is specified, the filter will return only
     * those data items that fall within the bounds determined by the Period, inclusive
     * of the period boundaries. If dateTime is specified, the filter will return only
     * those data items that are equal to the specified dateTime. If a Duration is
     * specified, the filter will return only those data items that fall within
     * Duration before now. (choose any one of value*, but only one)
     *
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $valueDateTime
     * @return static
     */
    public function setValueDateTime(null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $valueDateTime): self
    {
        if (null === $valueDateTime) {
            unset($this->valueDateTime);
            return $this;
        }
        if (!($valueDateTime instanceof FHIRDateTime)) {
            $valueDateTime = new FHIRDateTime(value: $valueDateTime);
        }
        $this->valueDateTime = $valueDateTime;
        return $this;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The value of the filter. If period is specified, the filter will return only
     * those data items that fall within the bounds determined by the Period, inclusive
     * of the period boundaries. If dateTime is specified, the filter will return only
     * those data items that are equal to the specified dateTime. If a Duration is
     * specified, the filter will return only those data items that fall within
     * Duration before now. (choose any one of value*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod
     */
    public function getValuePeriod(): null|FHIRPeriod
    {
        return $this->valuePeriod ?? null;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The value of the filter. If period is specified, the filter will return only
     * those data items that fall within the bounds determined by the Period, inclusive
     * of the period boundaries. If dateTime is specified, the filter will return only
     * those data items that are equal to the specified dateTime. If a Duration is
     * specified, the filter will return only those data items that fall within
     * Duration before now. (choose any one of value*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $valuePeriod
     * @return static
     */
    public function setValuePeriod(null|FHIRPeriod $valuePeriod): self
    {
        if (null === $valuePeriod) {
            unset($this->valuePeriod);
            return $this;
        }
        $this->valuePeriod = $valuePeriod;
        return $this;
    }

    /**
     * A length of time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The value of the filter. If period is specified, the filter will return only
     * those data items that fall within the bounds determined by the Period, inclusive
     * of the period boundaries. If dateTime is specified, the filter will return only
     * those data items that are equal to the specified dateTime. If a Duration is
     * specified, the filter will return only those data items that fall within
     * Duration before now. (choose any one of value*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration
     */
    public function getValueDuration(): null|FHIRDuration
    {
        return $this->valueDuration ?? null;
    }

    /**
     * A length of time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The value of the filter. If period is specified, the filter will return only
     * those data items that fall within the bounds determined by the Period, inclusive
     * of the period boundaries. If dateTime is specified, the filter will return only
     * those data items that are equal to the specified dateTime. If a Duration is
     * specified, the filter will return only those data items that fall within
     * Duration before now. (choose any one of value*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration $valueDuration
     * @return static
     */
    public function setValueDuration(null|FHIRDuration $valueDuration): self
    {
        if (null === $valueDuration) {
            unset($this->valueDuration);
            return $this;
        }
        $this->valueDuration = $valueDuration;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDataRequirement\FHIRDataRequirementDateFilter $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDataRequirement\FHIRDataRequirementDateFilter
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRDataRequirementDateFilter)) {
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
            } else if (self::FIELD_PATH === $cen) {
                $type->setPath(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SEARCH_PARAM === $cen) {
                $type->setSearchParam(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALUE_DATE_TIME === $cen) {
                $type->setValueDateTime(FHIRDateTime::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALUE_PERIOD === $cen) {
                $type->setValuePeriod(FHIRPeriod::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALUE_DURATION === $cen) {
                $type->setValueDuration(FHIRDuration::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_PATH])) {
            if (isset($type->path)) {
                $type->path->setValue((string)$attributes[self::FIELD_PATH]);
            } else {
                $type->setPath((string)$attributes[self::FIELD_PATH]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_PATH, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_SEARCH_PARAM])) {
            if (isset($type->searchParam)) {
                $type->searchParam->setValue((string)$attributes[self::FIELD_SEARCH_PARAM]);
            } else {
                $type->setSearchParam((string)$attributes[self::FIELD_SEARCH_PARAM]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_SEARCH_PARAM, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_VALUE_DATE_TIME])) {
            if (isset($type->valueDateTime)) {
                $type->valueDateTime->setValue((string)$attributes[self::FIELD_VALUE_DATE_TIME]);
            } else {
                $type->setValueDateTime((string)$attributes[self::FIELD_VALUE_DATE_TIME]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_VALUE_DATE_TIME, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->path) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_PATH]) {
            $xw->writeAttribute(self::FIELD_PATH, $this->path->_getValueAsString());
        }
        if (isset($this->searchParam) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_SEARCH_PARAM]) {
            $xw->writeAttribute(self::FIELD_SEARCH_PARAM, $this->searchParam->_getValueAsString());
        }
        if (isset($this->valueDateTime) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_VALUE_DATE_TIME]) {
            $xw->writeAttribute(self::FIELD_VALUE_DATE_TIME, $this->valueDateTime->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->path)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_PATH]
                || $this->path->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_PATH);
            $this->path->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_PATH]);
            $xw->endElement();
        }
        if (isset($this->searchParam)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_SEARCH_PARAM]
                || $this->searchParam->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_SEARCH_PARAM);
            $this->searchParam->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_SEARCH_PARAM]);
            $xw->endElement();
        }
        if (isset($this->valueDateTime)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_VALUE_DATE_TIME]
                || $this->valueDateTime->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_VALUE_DATE_TIME);
            $this->valueDateTime->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_VALUE_DATE_TIME]);
            $xw->endElement();
        }
        if (isset($this->valuePeriod)) {
            $xw->startElement(self::FIELD_VALUE_PERIOD);
            $this->valuePeriod->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->valueDuration)) {
            $xw->startElement(self::FIELD_VALUE_DURATION);
            $this->valueDuration->xmlSerialize($xw, $config);
            $xw->endElement();
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDataRequirement\FHIRDataRequirementDateFilter $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDataRequirement\FHIRDataRequirementDateFilter
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
        } else if (!($type instanceof FHIRDataRequirementDateFilter)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->path)
            || isset($decoded->_path)
            || property_exists($decoded, self::FIELD_PATH)
            || property_exists($decoded, self::FIELD_PATH_EXT)) {
            $v = $decoded->_path ?? new \stdClass();
            $v->value = $decoded->path ?? null;
            $type->setPath(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->searchParam)
            || isset($decoded->_searchParam)
            || property_exists($decoded, self::FIELD_SEARCH_PARAM)
            || property_exists($decoded, self::FIELD_SEARCH_PARAM_EXT)) {
            $v = $decoded->_searchParam ?? new \stdClass();
            $v->value = $decoded->searchParam ?? null;
            $type->setSearchParam(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->valueDateTime)
            || isset($decoded->_valueDateTime)
            || property_exists($decoded, self::FIELD_VALUE_DATE_TIME)
            || property_exists($decoded, self::FIELD_VALUE_DATE_TIME_EXT)) {
            $v = $decoded->_valueDateTime ?? new \stdClass();
            $v->value = $decoded->valueDateTime ?? null;
            $type->setValueDateTime(FHIRDateTime::jsonUnserialize($v, $config));
        }
        if (isset($decoded->valuePeriod) || property_exists($decoded, self::FIELD_VALUE_PERIOD)) {
            if (is_array($decoded->valuePeriod)) {
                $type->setValuePeriod(FHIRPeriod::jsonUnserialize(reset($decoded->valuePeriod), $config));
            } else {
                $type->setValuePeriod(FHIRPeriod::jsonUnserialize($decoded->valuePeriod, $config));
            }
        }
        if (isset($decoded->valueDuration) || property_exists($decoded, self::FIELD_VALUE_DURATION)) {
            if (is_array($decoded->valueDuration)) {
                $type->setValueDuration(FHIRDuration::jsonUnserialize(reset($decoded->valueDuration), $config));
            } else {
                $type->setValueDuration(FHIRDuration::jsonUnserialize($decoded->valueDuration, $config));
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
        if (isset($this->path)) {
            if (null !== ($val = $this->path->getValue())) {
                $out->path = $val;
            }
            if ($this->path->_nonValueFieldDefined()) {
                $ext = $this->path->jsonSerialize();
                unset($ext->value);
                $out->_path = $ext;
            }
        }
        if (isset($this->searchParam)) {
            if (null !== ($val = $this->searchParam->getValue())) {
                $out->searchParam = $val;
            }
            if ($this->searchParam->_nonValueFieldDefined()) {
                $ext = $this->searchParam->jsonSerialize();
                unset($ext->value);
                $out->_searchParam = $ext;
            }
        }
        if (isset($this->valueDateTime)) {
            if (null !== ($val = $this->valueDateTime->getValue())) {
                $out->valueDateTime = $val;
            }
            if ($this->valueDateTime->_nonValueFieldDefined()) {
                $ext = $this->valueDateTime->jsonSerialize();
                unset($ext->value);
                $out->_valueDateTime = $ext;
            }
        }
        if (isset($this->valuePeriod)) {
            $out->valuePeriod = $this->valuePeriod;
        }
        if (isset($this->valueDuration)) {
            $out->valueDuration = $this->valueDuration;
        }
        return $out;
    }
}
