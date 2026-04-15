<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSpecimenDefinition;

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
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRange;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * A kind of specimen with associated set of requirements.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRSpecimenDefinitionHandling extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_SPECIMEN_DEFINITION_DOT_HANDLING;

    /* class_default.php:56 */
    public const FIELD_TEMPERATURE_QUALIFIER = 'temperatureQualifier';
    public const FIELD_TEMPERATURE_RANGE = 'temperatureRange';
    public const FIELD_MAX_DURATION = 'maxDuration';
    public const FIELD_INSTRUCTION = 'instruction';
    public const FIELD_INSTRUCTION_EXT = '_instruction';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_INSTRUCTION => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * It qualifies the interval of temperature, which characterizes an occurrence of
     * handling. Conditions that are not related to temperature may be handled in the
     * instruction element.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $temperatureQualifier;
    /**
     * A set of ordered Quantities defined by a low and high limit.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The temperature interval for this set of handling instructions.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRange
     */
    #[FHIRRange]
    protected FHIRRange $temperatureRange;
    /**
     * A length of time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The maximum time interval of preservation of the specimen with these conditions.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration
     */
    #[FHIRDuration]
    protected FHIRDuration $maxDuration;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Additional textual instructions for the preservation or transport of the
     * specimen. For instance, 'Protect from light exposure'.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $instruction;

    /* constructor.php:61 */
    /**
     * FHIRSpecimenDefinitionHandling Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $temperatureQualifier
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRange $temperatureRange
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration $maxDuration
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $instruction
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|FHIRCodeableConcept $temperatureQualifier = null,
                                null|FHIRRange $temperatureRange = null,
                                null|FHIRDuration $maxDuration = null,
                                null|string|FHIRStringPrimitive|FHIRString $instruction = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $temperatureQualifier) {
            $this->setTemperatureQualifier($temperatureQualifier);
        }
        if (null !== $temperatureRange) {
            $this->setTemperatureRange($temperatureRange);
        }
        if (null !== $maxDuration) {
            $this->setMaxDuration($maxDuration);
        }
        if (null !== $instruction) {
            $this->setInstruction($instruction);
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
     * It qualifies the interval of temperature, which characterizes an occurrence of
     * handling. Conditions that are not related to temperature may be handled in the
     * instruction element.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getTemperatureQualifier(): null|FHIRCodeableConcept
    {
        return $this->temperatureQualifier ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * It qualifies the interval of temperature, which characterizes an occurrence of
     * handling. Conditions that are not related to temperature may be handled in the
     * instruction element.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $temperatureQualifier
     * @return static
     */
    public function setTemperatureQualifier(null|FHIRCodeableConcept $temperatureQualifier): self
    {
        if (null === $temperatureQualifier) {
            unset($this->temperatureQualifier);
            return $this;
        }
        $this->temperatureQualifier = $temperatureQualifier;
        return $this;
    }

    /**
     * A set of ordered Quantities defined by a low and high limit.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The temperature interval for this set of handling instructions.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRange
     */
    public function getTemperatureRange(): null|FHIRRange
    {
        return $this->temperatureRange ?? null;
    }

    /**
     * A set of ordered Quantities defined by a low and high limit.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The temperature interval for this set of handling instructions.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRange $temperatureRange
     * @return static
     */
    public function setTemperatureRange(null|FHIRRange $temperatureRange): self
    {
        if (null === $temperatureRange) {
            unset($this->temperatureRange);
            return $this;
        }
        $this->temperatureRange = $temperatureRange;
        return $this;
    }

    /**
     * A length of time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The maximum time interval of preservation of the specimen with these conditions.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration
     */
    public function getMaxDuration(): null|FHIRDuration
    {
        return $this->maxDuration ?? null;
    }

    /**
     * A length of time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The maximum time interval of preservation of the specimen with these conditions.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration $maxDuration
     * @return static
     */
    public function setMaxDuration(null|FHIRDuration $maxDuration): self
    {
        if (null === $maxDuration) {
            unset($this->maxDuration);
            return $this;
        }
        $this->maxDuration = $maxDuration;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Additional textual instructions for the preservation or transport of the
     * specimen. For instance, 'Protect from light exposure'.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getInstruction(): null|FHIRString
    {
        return $this->instruction ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Additional textual instructions for the preservation or transport of the
     * specimen. For instance, 'Protect from light exposure'.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $instruction
     * @return static
     */
    public function setInstruction(null|string|FHIRStringPrimitive|FHIRString $instruction): self
    {
        if (null === $instruction) {
            unset($this->instruction);
            return $this;
        }
        if (!($instruction instanceof FHIRString)) {
            $instruction = new FHIRString(value: $instruction);
        }
        $this->instruction = $instruction;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSpecimenDefinition\FHIRSpecimenDefinitionHandling $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSpecimenDefinition\FHIRSpecimenDefinitionHandling
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRSpecimenDefinitionHandling)) {
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
            } else if (self::FIELD_TEMPERATURE_QUALIFIER === $cen) {
                $type->setTemperatureQualifier(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TEMPERATURE_RANGE === $cen) {
                $type->setTemperatureRange(FHIRRange::xmlUnserialize($ce, $config));
            } else if (self::FIELD_MAX_DURATION === $cen) {
                $type->setMaxDuration(FHIRDuration::xmlUnserialize($ce, $config));
            } else if (self::FIELD_INSTRUCTION === $cen) {
                $type->setInstruction(FHIRString::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_INSTRUCTION])) {
            if (isset($type->instruction)) {
                $type->instruction->setValue((string)$attributes[self::FIELD_INSTRUCTION]);
            } else {
                $type->setInstruction((string)$attributes[self::FIELD_INSTRUCTION]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_INSTRUCTION, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->instruction) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_INSTRUCTION]) {
            $xw->writeAttribute(self::FIELD_INSTRUCTION, $this->instruction->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->temperatureQualifier)) {
            $xw->startElement(self::FIELD_TEMPERATURE_QUALIFIER);
            $this->temperatureQualifier->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->temperatureRange)) {
            $xw->startElement(self::FIELD_TEMPERATURE_RANGE);
            $this->temperatureRange->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->maxDuration)) {
            $xw->startElement(self::FIELD_MAX_DURATION);
            $this->maxDuration->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->instruction)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_INSTRUCTION]
                || $this->instruction->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_INSTRUCTION);
            $this->instruction->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_INSTRUCTION]);
            $xw->endElement();
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSpecimenDefinition\FHIRSpecimenDefinitionHandling $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSpecimenDefinition\FHIRSpecimenDefinitionHandling
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
        } else if (!($type instanceof FHIRSpecimenDefinitionHandling)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->temperatureQualifier) || property_exists($decoded, self::FIELD_TEMPERATURE_QUALIFIER)) {
            if (is_array($decoded->temperatureQualifier)) {
                $type->setTemperatureQualifier(FHIRCodeableConcept::jsonUnserialize(reset($decoded->temperatureQualifier), $config));
            } else {
                $type->setTemperatureQualifier(FHIRCodeableConcept::jsonUnserialize($decoded->temperatureQualifier, $config));
            }
        }
        if (isset($decoded->temperatureRange) || property_exists($decoded, self::FIELD_TEMPERATURE_RANGE)) {
            if (is_array($decoded->temperatureRange)) {
                $type->setTemperatureRange(FHIRRange::jsonUnserialize(reset($decoded->temperatureRange), $config));
            } else {
                $type->setTemperatureRange(FHIRRange::jsonUnserialize($decoded->temperatureRange, $config));
            }
        }
        if (isset($decoded->maxDuration) || property_exists($decoded, self::FIELD_MAX_DURATION)) {
            if (is_array($decoded->maxDuration)) {
                $type->setMaxDuration(FHIRDuration::jsonUnserialize(reset($decoded->maxDuration), $config));
            } else {
                $type->setMaxDuration(FHIRDuration::jsonUnserialize($decoded->maxDuration, $config));
            }
        }
        if (isset($decoded->instruction)
            || isset($decoded->_instruction)
            || property_exists($decoded, self::FIELD_INSTRUCTION)
            || property_exists($decoded, self::FIELD_INSTRUCTION_EXT)) {
            $v = $decoded->_instruction ?? new \stdClass();
            $v->value = $decoded->instruction ?? null;
            $type->setInstruction(FHIRString::jsonUnserialize($v, $config));
        }
        return $type;
    }

    /**
     * @return \stdClass
     */
    public function jsonSerialize(): mixed
    {
        $out = parent::jsonSerialize();
        if (isset($this->temperatureQualifier)) {
            $out->temperatureQualifier = $this->temperatureQualifier;
        }
        if (isset($this->temperatureRange)) {
            $out->temperatureRange = $this->temperatureRange;
        }
        if (isset($this->maxDuration)) {
            $out->maxDuration = $this->maxDuration;
        }
        if (isset($this->instruction)) {
            if (null !== ($val = $this->instruction->getValue())) {
                $out->instruction = $val;
            }
            if ($this->instruction->_nonValueFieldDefined()) {
                $ext = $this->instruction->jsonSerialize();
                unset($ext->value);
                $out->_instruction = $ext;
            }
        }
        return $out;
    }
}
