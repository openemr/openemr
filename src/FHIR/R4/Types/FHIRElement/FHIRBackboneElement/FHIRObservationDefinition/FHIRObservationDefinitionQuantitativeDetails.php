<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRObservationDefinition;

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
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger;
use OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * Set of definitional characteristics for a kind of observation or measurement
 * produced or consumed by an orderable health care service.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRObservationDefinitionQuantitativeDetails extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_OBSERVATION_DEFINITION_DOT_QUANTITATIVE_DETAILS;

    /* class_default.php:56 */
    public const FIELD_CUSTOMARY_UNIT = 'customaryUnit';
    public const FIELD_UNIT = 'unit';
    public const FIELD_CONVERSION_FACTOR = 'conversionFactor';
    public const FIELD_CONVERSION_FACTOR_EXT = '_conversionFactor';
    public const FIELD_DECIMAL_PRECISION = 'decimalPrecision';
    public const FIELD_DECIMAL_PRECISION_EXT = '_decimalPrecision';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_CONVERSION_FACTOR => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_DECIMAL_PRECISION => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Customary unit used to report quantitative results of observations conforming to
     * this ObservationDefinition.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $customaryUnit;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * SI unit used to report quantitative results of observations conforming to this
     * ObservationDefinition.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $unit;
    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Factor for converting value expressed with SI unit to value expressed with
     * customary unit.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal
     */
    #[FHIRDecimal]
    protected FHIRDecimal $conversionFactor;
    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Number of digits after decimal separator when the results of such observations
     * are of type Quantity.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger
     */
    #[FHIRInteger]
    protected FHIRInteger $decimalPrecision;

    /* constructor.php:61 */
    /**
     * FHIRObservationDefinitionQuantitativeDetails Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $customaryUnit
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $unit
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $conversionFactor
     * @param null|string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger $decimalPrecision
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|FHIRCodeableConcept $customaryUnit = null,
                                null|FHIRCodeableConcept $unit = null,
                                null|string|int|float|FHIRDecimalPrimitive|FHIRDecimal $conversionFactor = null,
                                null|string|float|FHIRIntegerPrimitive|FHIRInteger $decimalPrecision = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $customaryUnit) {
            $this->setCustomaryUnit($customaryUnit);
        }
        if (null !== $unit) {
            $this->setUnit($unit);
        }
        if (null !== $conversionFactor) {
            $this->setConversionFactor($conversionFactor);
        }
        if (null !== $decimalPrecision) {
            $this->setDecimalPrecision($decimalPrecision);
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
     * Customary unit used to report quantitative results of observations conforming to
     * this ObservationDefinition.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getCustomaryUnit(): null|FHIRCodeableConcept
    {
        return $this->customaryUnit ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Customary unit used to report quantitative results of observations conforming to
     * this ObservationDefinition.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $customaryUnit
     * @return static
     */
    public function setCustomaryUnit(null|FHIRCodeableConcept $customaryUnit): self
    {
        if (null === $customaryUnit) {
            unset($this->customaryUnit);
            return $this;
        }
        $this->customaryUnit = $customaryUnit;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * SI unit used to report quantitative results of observations conforming to this
     * ObservationDefinition.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getUnit(): null|FHIRCodeableConcept
    {
        return $this->unit ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * SI unit used to report quantitative results of observations conforming to this
     * ObservationDefinition.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $unit
     * @return static
     */
    public function setUnit(null|FHIRCodeableConcept $unit): self
    {
        if (null === $unit) {
            unset($this->unit);
            return $this;
        }
        $this->unit = $unit;
        return $this;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Factor for converting value expressed with SI unit to value expressed with
     * customary unit.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal
     */
    public function getConversionFactor(): null|FHIRDecimal
    {
        return $this->conversionFactor ?? null;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Factor for converting value expressed with SI unit to value expressed with
     * customary unit.
     *
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $conversionFactor
     * @return static
     */
    public function setConversionFactor(null|string|int|float|FHIRDecimalPrimitive|FHIRDecimal $conversionFactor): self
    {
        if (null === $conversionFactor) {
            unset($this->conversionFactor);
            return $this;
        }
        if (!($conversionFactor instanceof FHIRDecimal)) {
            $conversionFactor = new FHIRDecimal(value: $conversionFactor);
        }
        $this->conversionFactor = $conversionFactor;
        return $this;
    }

    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Number of digits after decimal separator when the results of such observations
     * are of type Quantity.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger
     */
    public function getDecimalPrecision(): null|FHIRInteger
    {
        return $this->decimalPrecision ?? null;
    }

    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Number of digits after decimal separator when the results of such observations
     * are of type Quantity.
     *
     * @param null|string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger $decimalPrecision
     * @return static
     */
    public function setDecimalPrecision(null|string|float|FHIRIntegerPrimitive|FHIRInteger $decimalPrecision): self
    {
        if (null === $decimalPrecision) {
            unset($this->decimalPrecision);
            return $this;
        }
        if (!($decimalPrecision instanceof FHIRInteger)) {
            $decimalPrecision = new FHIRInteger(value: $decimalPrecision);
        }
        $this->decimalPrecision = $decimalPrecision;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRObservationDefinition\FHIRObservationDefinitionQuantitativeDetails $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRObservationDefinition\FHIRObservationDefinitionQuantitativeDetails
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRObservationDefinitionQuantitativeDetails)) {
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
            } else if (self::FIELD_CUSTOMARY_UNIT === $cen) {
                $type->setCustomaryUnit(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_UNIT === $cen) {
                $type->setUnit(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CONVERSION_FACTOR === $cen) {
                $type->setConversionFactor(FHIRDecimal::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DECIMAL_PRECISION === $cen) {
                $type->setDecimalPrecision(FHIRInteger::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_CONVERSION_FACTOR])) {
            if (isset($type->conversionFactor)) {
                $type->conversionFactor->setValue((string)$attributes[self::FIELD_CONVERSION_FACTOR]);
            } else {
                $type->setConversionFactor((string)$attributes[self::FIELD_CONVERSION_FACTOR]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_CONVERSION_FACTOR, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_DECIMAL_PRECISION])) {
            if (isset($type->decimalPrecision)) {
                $type->decimalPrecision->setValue((string)$attributes[self::FIELD_DECIMAL_PRECISION]);
            } else {
                $type->setDecimalPrecision((string)$attributes[self::FIELD_DECIMAL_PRECISION]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_DECIMAL_PRECISION, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->conversionFactor) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_CONVERSION_FACTOR]) {
            $xw->writeAttribute(self::FIELD_CONVERSION_FACTOR, $this->conversionFactor->_getValueAsString());
        }
        if (isset($this->decimalPrecision) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_DECIMAL_PRECISION]) {
            $xw->writeAttribute(self::FIELD_DECIMAL_PRECISION, $this->decimalPrecision->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->customaryUnit)) {
            $xw->startElement(self::FIELD_CUSTOMARY_UNIT);
            $this->customaryUnit->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->unit)) {
            $xw->startElement(self::FIELD_UNIT);
            $this->unit->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->conversionFactor)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_CONVERSION_FACTOR]
                || $this->conversionFactor->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_CONVERSION_FACTOR);
            $this->conversionFactor->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_CONVERSION_FACTOR]);
            $xw->endElement();
        }
        if (isset($this->decimalPrecision)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_DECIMAL_PRECISION]
                || $this->decimalPrecision->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_DECIMAL_PRECISION);
            $this->decimalPrecision->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_DECIMAL_PRECISION]);
            $xw->endElement();
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRObservationDefinition\FHIRObservationDefinitionQuantitativeDetails $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRObservationDefinition\FHIRObservationDefinitionQuantitativeDetails
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
        } else if (!($type instanceof FHIRObservationDefinitionQuantitativeDetails)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->customaryUnit) || property_exists($decoded, self::FIELD_CUSTOMARY_UNIT)) {
            if (is_array($decoded->customaryUnit)) {
                $type->setCustomaryUnit(FHIRCodeableConcept::jsonUnserialize(reset($decoded->customaryUnit), $config));
            } else {
                $type->setCustomaryUnit(FHIRCodeableConcept::jsonUnserialize($decoded->customaryUnit, $config));
            }
        }
        if (isset($decoded->unit) || property_exists($decoded, self::FIELD_UNIT)) {
            if (is_array($decoded->unit)) {
                $type->setUnit(FHIRCodeableConcept::jsonUnserialize(reset($decoded->unit), $config));
            } else {
                $type->setUnit(FHIRCodeableConcept::jsonUnserialize($decoded->unit, $config));
            }
        }
        if (isset($decoded->conversionFactor)
            || isset($decoded->_conversionFactor)
            || property_exists($decoded, self::FIELD_CONVERSION_FACTOR)
            || property_exists($decoded, self::FIELD_CONVERSION_FACTOR_EXT)) {
            $v = $decoded->_conversionFactor ?? new \stdClass();
            $v->value = $decoded->conversionFactor ?? null;
            $type->setConversionFactor(FHIRDecimal::jsonUnserialize($v, $config));
        }
        if (isset($decoded->decimalPrecision)
            || isset($decoded->_decimalPrecision)
            || property_exists($decoded, self::FIELD_DECIMAL_PRECISION)
            || property_exists($decoded, self::FIELD_DECIMAL_PRECISION_EXT)) {
            $v = $decoded->_decimalPrecision ?? new \stdClass();
            $v->value = $decoded->decimalPrecision ?? null;
            $type->setDecimalPrecision(FHIRInteger::jsonUnserialize($v, $config));
        }
        return $type;
    }

    /**
     * @return \stdClass
     */
    public function jsonSerialize(): mixed
    {
        $out = parent::jsonSerialize();
        if (isset($this->customaryUnit)) {
            $out->customaryUnit = $this->customaryUnit;
        }
        if (isset($this->unit)) {
            $out->unit = $this->unit;
        }
        if (isset($this->conversionFactor)) {
            if (null !== ($val = $this->conversionFactor->getValue())) {
                $out->conversionFactor = $val;
            }
            if ($this->conversionFactor->_nonValueFieldDefined()) {
                $ext = $this->conversionFactor->jsonSerialize();
                unset($ext->value);
                $out->_conversionFactor = $ext;
            }
        }
        if (isset($this->decimalPrecision)) {
            if (null !== ($val = $this->decimalPrecision->getValue())) {
                $out->decimalPrecision = $val;
            }
            if ($this->decimalPrecision->_nonValueFieldDefined()) {
                $ext = $this->decimalPrecision->jsonSerialize();
                unset($ext->value);
                $out->_decimalPrecision = $ext;
            }
        }
        return $out;
    }
}
