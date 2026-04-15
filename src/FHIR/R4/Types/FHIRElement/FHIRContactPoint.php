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
use OpenEMR\FHIR\Validation\TypeValidationsTrait;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRContactPointSystemList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRContactPointUseList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRPositiveIntPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * Details for all kinds of technology mediated contact points for a person or
 * organization, including telephone, email, etc.
 * If the element is present, it must have a value for at least one of the defined
 * elements, an \@id referenced from the Narrative, or extensions
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRContactPoint extends FHIRElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_CONTACT_POINT;

    /* class_default.php:56 */
    public const FIELD_SYSTEM = 'system';
    public const FIELD_SYSTEM_EXT = '_system';
    public const FIELD_VALUE = 'value';
    public const FIELD_VALUE_EXT = '_value';
    public const FIELD_USE = 'use';
    public const FIELD_USE_EXT = '_use';
    public const FIELD_RANK = 'rank';
    public const FIELD_RANK_EXT = '_rank';
    public const FIELD_PERIOD = 'period';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_SYSTEM => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_VALUE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_USE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_RANK => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * Telecommunications form for contact point.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Telecommunications form for contact point - what communications system is
     * required to make use of the contact.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContactPointSystem
     */
    #[FHIRContactPointSystem]
    protected FHIRContactPointSystem $system;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The actual contact point details, in a form that is meaningful to the designated
     * communication system (i.e. phone number or email address).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $value;
    /**
     * Use of contact point.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Identifies the purpose for the contact point.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContactPointUse
     */
    #[FHIRContactPointUse]
    protected FHIRContactPointUse $use;
    /**
     * An integer with a value that is positive (e.g. >0)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Specifies a preferred order in which to use a set of contacts. ContactPoints
     * with lower rank values are more preferred than those with higher rank values.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPositiveInt
     */
    #[FHIRPositiveInt]
    protected FHIRPositiveInt $rank;
    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Time period when the contact point was/is in use.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod
     */
    #[FHIRPeriod]
    protected FHIRPeriod $period;

    /* constructor.php:61 */
    /**
     * FHIRContactPoint Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRContactPointSystemList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContactPointSystem $system
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $value
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRContactPointUseList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContactPointUse $use
     * @param null|string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRPositiveIntPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPositiveInt $rank
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $period
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|string|FHIRContactPointSystemList|FHIRContactPointSystem $system = null,
                                null|string|FHIRStringPrimitive|FHIRString $value = null,
                                null|string|FHIRContactPointUseList|FHIRContactPointUse $use = null,
                                null|string|float|FHIRPositiveIntPrimitive|FHIRPositiveInt $rank = null,
                                null|FHIRPeriod $period = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            fhirComments: $fhirComments);
        if (null !== $system) {
            $this->setSystem($system);
        }
        if (null !== $value) {
            $this->setValue($value);
        }
        if (null !== $use) {
            $this->setUse($use);
        }
        if (null !== $rank) {
            $this->setRank($rank);
        }
        if (null !== $period) {
            $this->setPeriod($period);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * Telecommunications form for contact point.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Telecommunications form for contact point - what communications system is
     * required to make use of the contact.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContactPointSystem
     */
    public function getSystem(): null|FHIRContactPointSystem
    {
        return $this->system ?? null;
    }

    /**
     * Telecommunications form for contact point.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Telecommunications form for contact point - what communications system is
     * required to make use of the contact.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRContactPointSystemList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContactPointSystem $system
     * @return static
     */
    public function setSystem(null|string|FHIRContactPointSystemList|FHIRContactPointSystem $system): self
    {
        if (null === $system) {
            unset($this->system);
            return $this;
        }
        if (!($system instanceof FHIRContactPointSystem)) {
            $system = new FHIRContactPointSystem(value: $system);
        }
        $this->system = $system;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The actual contact point details, in a form that is meaningful to the designated
     * communication system (i.e. phone number or email address).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getValue(): null|FHIRString
    {
        return $this->value ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The actual contact point details, in a form that is meaningful to the designated
     * communication system (i.e. phone number or email address).
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $value
     * @return static
     */
    public function setValue(null|string|FHIRStringPrimitive|FHIRString $value): self
    {
        if (null === $value) {
            unset($this->value);
            return $this;
        }
        if (!($value instanceof FHIRString)) {
            $value = new FHIRString(value: $value);
        }
        $this->value = $value;
        return $this;
    }

    /**
     * Use of contact point.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Identifies the purpose for the contact point.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContactPointUse
     */
    public function getUse(): null|FHIRContactPointUse
    {
        return $this->use ?? null;
    }

    /**
     * Use of contact point.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Identifies the purpose for the contact point.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRContactPointUseList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContactPointUse $use
     * @return static
     */
    public function setUse(null|string|FHIRContactPointUseList|FHIRContactPointUse $use): self
    {
        if (null === $use) {
            unset($this->use);
            return $this;
        }
        if (!($use instanceof FHIRContactPointUse)) {
            $use = new FHIRContactPointUse(value: $use);
        }
        $this->use = $use;
        return $this;
    }

    /**
     * An integer with a value that is positive (e.g. >0)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Specifies a preferred order in which to use a set of contacts. ContactPoints
     * with lower rank values are more preferred than those with higher rank values.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPositiveInt
     */
    public function getRank(): null|FHIRPositiveInt
    {
        return $this->rank ?? null;
    }

    /**
     * An integer with a value that is positive (e.g. >0)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Specifies a preferred order in which to use a set of contacts. ContactPoints
     * with lower rank values are more preferred than those with higher rank values.
     *
     * @param null|string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRPositiveIntPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPositiveInt $rank
     * @return static
     */
    public function setRank(null|string|float|FHIRPositiveIntPrimitive|FHIRPositiveInt $rank): self
    {
        if (null === $rank) {
            unset($this->rank);
            return $this;
        }
        if (!($rank instanceof FHIRPositiveInt)) {
            $rank = new FHIRPositiveInt(value: $rank);
        }
        $this->rank = $rank;
        return $this;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Time period when the contact point was/is in use.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod
     */
    public function getPeriod(): null|FHIRPeriod
    {
        return $this->period ?? null;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Time period when the contact point was/is in use.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $period
     * @return static
     */
    public function setPeriod(null|FHIRPeriod $period): self
    {
        if (null === $period) {
            unset($this->period);
            return $this;
        }
        $this->period = $period;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContactPoint $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContactPoint
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRContactPoint)) {
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
            } else if (self::FIELD_SYSTEM === $cen) {
                $type->setSystem(FHIRContactPointSystem::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALUE === $cen) {
                $type->setValue(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_USE === $cen) {
                $type->setUse(FHIRContactPointUse::xmlUnserialize($ce, $config));
            } else if (self::FIELD_RANK === $cen) {
                $type->setRank(FHIRPositiveInt::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PERIOD === $cen) {
                $type->setPeriod(FHIRPeriod::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_SYSTEM])) {
            if (isset($type->system)) {
                $type->system->setValue((string)$attributes[self::FIELD_SYSTEM]);
            } else {
                $type->setSystem((string)$attributes[self::FIELD_SYSTEM]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_SYSTEM, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_VALUE])) {
            if (isset($type->value)) {
                $type->value->setValue((string)$attributes[self::FIELD_VALUE]);
            } else {
                $type->setValue((string)$attributes[self::FIELD_VALUE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_VALUE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_USE])) {
            if (isset($type->use)) {
                $type->use->setValue((string)$attributes[self::FIELD_USE]);
            } else {
                $type->setUse((string)$attributes[self::FIELD_USE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_USE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_RANK])) {
            if (isset($type->rank)) {
                $type->rank->setValue((string)$attributes[self::FIELD_RANK]);
            } else {
                $type->setRank((string)$attributes[self::FIELD_RANK]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_RANK, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->system) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_SYSTEM]) {
            $xw->writeAttribute(self::FIELD_SYSTEM, $this->system->_getValueAsString());
        }
        if (isset($this->value) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_VALUE]) {
            $xw->writeAttribute(self::FIELD_VALUE, $this->value->_getValueAsString());
        }
        if (isset($this->use) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_USE]) {
            $xw->writeAttribute(self::FIELD_USE, $this->use->_getValueAsString());
        }
        if (isset($this->rank) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_RANK]) {
            $xw->writeAttribute(self::FIELD_RANK, $this->rank->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->system)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_SYSTEM]
                || $this->system->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_SYSTEM);
            $this->system->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_SYSTEM]);
            $xw->endElement();
        }
        if (isset($this->value)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_VALUE]
                || $this->value->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_VALUE);
            $this->value->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_VALUE]);
            $xw->endElement();
        }
        if (isset($this->use)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_USE]
                || $this->use->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_USE);
            $this->use->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_USE]);
            $xw->endElement();
        }
        if (isset($this->rank)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_RANK]
                || $this->rank->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_RANK);
            $this->rank->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_RANK]);
            $xw->endElement();
        }
        if (isset($this->period)) {
            $xw->startElement(self::FIELD_PERIOD);
            $this->period->xmlSerialize($xw, $config);
            $xw->endElement();
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContactPoint $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContactPoint
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
        } else if (!($type instanceof FHIRContactPoint)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->system)
            || isset($decoded->_system)
            || property_exists($decoded, self::FIELD_SYSTEM)
            || property_exists($decoded, self::FIELD_SYSTEM_EXT)) {
            $v = $decoded->_system ?? new \stdClass();
            $v->value = $decoded->system ?? null;
            $type->setSystem(FHIRContactPointSystem::jsonUnserialize($v, $config));
        }
        if (isset($decoded->value)
            || isset($decoded->_value)
            || property_exists($decoded, self::FIELD_VALUE)
            || property_exists($decoded, self::FIELD_VALUE_EXT)) {
            $v = $decoded->_value ?? new \stdClass();
            $v->value = $decoded->value ?? null;
            $type->setValue(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->use)
            || isset($decoded->_use)
            || property_exists($decoded, self::FIELD_USE)
            || property_exists($decoded, self::FIELD_USE_EXT)) {
            $v = $decoded->_use ?? new \stdClass();
            $v->value = $decoded->use ?? null;
            $type->setUse(FHIRContactPointUse::jsonUnserialize($v, $config));
        }
        if (isset($decoded->rank)
            || isset($decoded->_rank)
            || property_exists($decoded, self::FIELD_RANK)
            || property_exists($decoded, self::FIELD_RANK_EXT)) {
            $v = $decoded->_rank ?? new \stdClass();
            $v->value = $decoded->rank ?? null;
            $type->setRank(FHIRPositiveInt::jsonUnserialize($v, $config));
        }
        if (isset($decoded->period) || property_exists($decoded, self::FIELD_PERIOD)) {
            if (is_array($decoded->period)) {
                $type->setPeriod(FHIRPeriod::jsonUnserialize(reset($decoded->period), $config));
            } else {
                $type->setPeriod(FHIRPeriod::jsonUnserialize($decoded->period, $config));
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
        if (isset($this->system)) {
            if (null !== ($val = $this->system->getValue())) {
                $out->system = $val;
            }
            if ($this->system->_nonValueFieldDefined()) {
                $ext = $this->system->jsonSerialize();
                unset($ext->value);
                $out->_system = $ext;
            }
        }
        if (isset($this->value)) {
            if (null !== ($val = $this->value->getValue())) {
                $out->value = $val;
            }
            if ($this->value->_nonValueFieldDefined()) {
                $ext = $this->value->jsonSerialize();
                unset($ext->value);
                $out->_value = $ext;
            }
        }
        if (isset($this->use)) {
            if (null !== ($val = $this->use->getValue())) {
                $out->use = $val;
            }
            if ($this->use->_nonValueFieldDefined()) {
                $ext = $this->use->jsonSerialize();
                unset($ext->value);
                $out->_use = $ext;
            }
        }
        if (isset($this->rank)) {
            if (null !== ($val = $this->rank->getValue())) {
                $out->rank = $val;
            }
            if ($this->rank->_nonValueFieldDefined()) {
                $ext = $this->rank->jsonSerialize();
                unset($ext->value);
                $out->_rank = $ext;
            }
        }
        if (isset($this->period)) {
            $out->period = $this->period;
        }
        return $out;
    }
}
