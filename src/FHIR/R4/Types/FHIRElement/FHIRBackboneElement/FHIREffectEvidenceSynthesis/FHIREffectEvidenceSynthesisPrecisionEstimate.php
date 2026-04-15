<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIREffectEvidenceSynthesis;

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
use OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * The EffectEvidenceSynthesis resource describes the difference in an outcome
 * between exposures states in a population where the effect estimate is derived
 * from a combination of research studies.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIREffectEvidenceSynthesisPrecisionEstimate extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_EFFECT_EVIDENCE_SYNTHESIS_DOT_PRECISION_ESTIMATE;

    /* class_default.php:56 */
    public const FIELD_TYPE = 'type';
    public const FIELD_LEVEL = 'level';
    public const FIELD_LEVEL_EXT = '_level';
    public const FIELD_FROM = 'from';
    public const FIELD_FROM_EXT = '_from';
    public const FIELD_TO = 'to';
    public const FIELD_TO_EXT = '_to';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_LEVEL => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_FROM => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_TO => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Examples include confidence interval and interquartile range.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $type;
    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Use 95 for a 95% confidence interval.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal
     */
    #[FHIRDecimal]
    protected FHIRDecimal $level;
    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Lower bound of confidence interval.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal
     */
    #[FHIRDecimal]
    protected FHIRDecimal $from;
    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Upper bound of confidence interval.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal
     */
    #[FHIRDecimal]
    protected FHIRDecimal $to;

    /* constructor.php:61 */
    /**
     * FHIREffectEvidenceSynthesisPrecisionEstimate Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $type
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $level
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $from
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $to
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|FHIRCodeableConcept $type = null,
                                null|string|int|float|FHIRDecimalPrimitive|FHIRDecimal $level = null,
                                null|string|int|float|FHIRDecimalPrimitive|FHIRDecimal $from = null,
                                null|string|int|float|FHIRDecimalPrimitive|FHIRDecimal $to = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $type) {
            $this->setType($type);
        }
        if (null !== $level) {
            $this->setLevel($level);
        }
        if (null !== $from) {
            $this->setFrom($from);
        }
        if (null !== $to) {
            $this->setTo($to);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Examples include confidence interval and interquartile range.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getType(): null|FHIRCodeableConcept
    {
        return $this->type ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Examples include confidence interval and interquartile range.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $type
     * @return static
     */
    public function setType(null|FHIRCodeableConcept $type): self
    {
        if (null === $type) {
            unset($this->type);
            return $this;
        }
        $this->type = $type;
        return $this;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Use 95 for a 95% confidence interval.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal
     */
    public function getLevel(): null|FHIRDecimal
    {
        return $this->level ?? null;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Use 95 for a 95% confidence interval.
     *
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $level
     * @return static
     */
    public function setLevel(null|string|int|float|FHIRDecimalPrimitive|FHIRDecimal $level): self
    {
        if (null === $level) {
            unset($this->level);
            return $this;
        }
        if (!($level instanceof FHIRDecimal)) {
            $level = new FHIRDecimal(value: $level);
        }
        $this->level = $level;
        return $this;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Lower bound of confidence interval.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal
     */
    public function getFrom(): null|FHIRDecimal
    {
        return $this->from ?? null;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Lower bound of confidence interval.
     *
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $from
     * @return static
     */
    public function setFrom(null|string|int|float|FHIRDecimalPrimitive|FHIRDecimal $from): self
    {
        if (null === $from) {
            unset($this->from);
            return $this;
        }
        if (!($from instanceof FHIRDecimal)) {
            $from = new FHIRDecimal(value: $from);
        }
        $this->from = $from;
        return $this;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Upper bound of confidence interval.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal
     */
    public function getTo(): null|FHIRDecimal
    {
        return $this->to ?? null;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Upper bound of confidence interval.
     *
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $to
     * @return static
     */
    public function setTo(null|string|int|float|FHIRDecimalPrimitive|FHIRDecimal $to): self
    {
        if (null === $to) {
            unset($this->to);
            return $this;
        }
        if (!($to instanceof FHIRDecimal)) {
            $to = new FHIRDecimal(value: $to);
        }
        $this->to = $to;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIREffectEvidenceSynthesis\FHIREffectEvidenceSynthesisPrecisionEstimate $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIREffectEvidenceSynthesis\FHIREffectEvidenceSynthesisPrecisionEstimate
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIREffectEvidenceSynthesisPrecisionEstimate)) {
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
            } else if (self::FIELD_TYPE === $cen) {
                $type->setType(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_LEVEL === $cen) {
                $type->setLevel(FHIRDecimal::xmlUnserialize($ce, $config));
            } else if (self::FIELD_FROM === $cen) {
                $type->setFrom(FHIRDecimal::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TO === $cen) {
                $type->setTo(FHIRDecimal::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_LEVEL])) {
            if (isset($type->level)) {
                $type->level->setValue((string)$attributes[self::FIELD_LEVEL]);
            } else {
                $type->setLevel((string)$attributes[self::FIELD_LEVEL]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_LEVEL, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_FROM])) {
            if (isset($type->from)) {
                $type->from->setValue((string)$attributes[self::FIELD_FROM]);
            } else {
                $type->setFrom((string)$attributes[self::FIELD_FROM]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_FROM, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_TO])) {
            if (isset($type->to)) {
                $type->to->setValue((string)$attributes[self::FIELD_TO]);
            } else {
                $type->setTo((string)$attributes[self::FIELD_TO]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_TO, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->level) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_LEVEL]) {
            $xw->writeAttribute(self::FIELD_LEVEL, $this->level->_getValueAsString());
        }
        if (isset($this->from) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_FROM]) {
            $xw->writeAttribute(self::FIELD_FROM, $this->from->_getValueAsString());
        }
        if (isset($this->to) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_TO]) {
            $xw->writeAttribute(self::FIELD_TO, $this->to->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->type)) {
            $xw->startElement(self::FIELD_TYPE);
            $this->type->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->level)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_LEVEL]
                || $this->level->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_LEVEL);
            $this->level->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_LEVEL]);
            $xw->endElement();
        }
        if (isset($this->from)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_FROM]
                || $this->from->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_FROM);
            $this->from->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_FROM]);
            $xw->endElement();
        }
        if (isset($this->to)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_TO]
                || $this->to->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_TO);
            $this->to->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_TO]);
            $xw->endElement();
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIREffectEvidenceSynthesis\FHIREffectEvidenceSynthesisPrecisionEstimate $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIREffectEvidenceSynthesis\FHIREffectEvidenceSynthesisPrecisionEstimate
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
        } else if (!($type instanceof FHIREffectEvidenceSynthesisPrecisionEstimate)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->type) || property_exists($decoded, self::FIELD_TYPE)) {
            if (is_array($decoded->type)) {
                $type->setType(FHIRCodeableConcept::jsonUnserialize(reset($decoded->type), $config));
            } else {
                $type->setType(FHIRCodeableConcept::jsonUnserialize($decoded->type, $config));
            }
        }
        if (isset($decoded->level)
            || isset($decoded->_level)
            || property_exists($decoded, self::FIELD_LEVEL)
            || property_exists($decoded, self::FIELD_LEVEL_EXT)) {
            $v = $decoded->_level ?? new \stdClass();
            $v->value = $decoded->level ?? null;
            $type->setLevel(FHIRDecimal::jsonUnserialize($v, $config));
        }
        if (isset($decoded->from)
            || isset($decoded->_from)
            || property_exists($decoded, self::FIELD_FROM)
            || property_exists($decoded, self::FIELD_FROM_EXT)) {
            $v = $decoded->_from ?? new \stdClass();
            $v->value = $decoded->from ?? null;
            $type->setFrom(FHIRDecimal::jsonUnserialize($v, $config));
        }
        if (isset($decoded->to)
            || isset($decoded->_to)
            || property_exists($decoded, self::FIELD_TO)
            || property_exists($decoded, self::FIELD_TO_EXT)) {
            $v = $decoded->_to ?? new \stdClass();
            $v->value = $decoded->to ?? null;
            $type->setTo(FHIRDecimal::jsonUnserialize($v, $config));
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
            $out->type = $this->type;
        }
        if (isset($this->level)) {
            if (null !== ($val = $this->level->getValue())) {
                $out->level = $val;
            }
            if ($this->level->_nonValueFieldDefined()) {
                $ext = $this->level->jsonSerialize();
                unset($ext->value);
                $out->_level = $ext;
            }
        }
        if (isset($this->from)) {
            if (null !== ($val = $this->from->getValue())) {
                $out->from = $val;
            }
            if ($this->from->_nonValueFieldDefined()) {
                $ext = $this->from->jsonSerialize();
                unset($ext->value);
                $out->_from = $ext;
            }
        }
        if (isset($this->to)) {
            if (null !== ($val = $this->to->getValue())) {
                $out->to = $val;
            }
            if ($this->to->_nonValueFieldDefined()) {
                $ext = $this->to->jsonSerialize();
                unset($ext->value);
                $out->_to = $ext;
            }
        }
        return $out;
    }
}
