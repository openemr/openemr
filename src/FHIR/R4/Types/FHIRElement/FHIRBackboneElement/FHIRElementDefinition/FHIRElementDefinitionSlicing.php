<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRElementDefinition;

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
use OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRSlicingRulesList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRSlicingRules;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * Captures constraints on each element within the resource, profile, or extension.
 * If the element is present, it must have a value for at least one of the defined
 * elements, an \@id referenced from the Narrative, or extensions
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRElementDefinitionSlicing extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_ELEMENT_DEFINITION_DOT_SLICING;

    /* class_default.php:56 */
    public const FIELD_DISCRIMINATOR = 'discriminator';
    public const FIELD_DESCRIPTION = 'description';
    public const FIELD_DESCRIPTION_EXT = '_description';
    public const FIELD_ORDERED = 'ordered';
    public const FIELD_ORDERED_EXT = '_ordered';
    public const FIELD_RULES = 'rules';
    public const FIELD_RULES_EXT = '_rules';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_RULES => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_DESCRIPTION => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_ORDERED => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_RULES => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * Captures constraints on each element within the resource, profile, or extension.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Designates which child elements are used to discriminate between the slices when
     * processing an instance. If one or more discriminators are provided, the value of
     * the child elements in the instance data SHALL completely distinguish which slice
     * the element in the resource matches based on the allowed values for those
     * elements in each of the slices.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRElementDefinition\FHIRElementDefinitionDiscriminator>
     */
    #[FHIRElementDefinitionDiscriminator]
    protected array $discriminator;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A human-readable text description of how the slicing works. If there is no
     * discriminator, this is required to be present to provide whatever information is
     * possible about how the slices can be differentiated.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $description;
    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If the matching elements have to occur in the same order as defined in the
     * profile.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    #[FHIRBoolean]
    protected FHIRBoolean $ordered;
    /**
     * How slices are interpreted when evaluating an instance.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Whether additional slices are allowed or not. When the slices are ordered,
     * profile authors can also say that additional slices are only allowed at the end.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRSlicingRules
     */
    #[FHIRSlicingRules]
    protected FHIRSlicingRules $rules;

    /* constructor.php:61 */
    /**
     * FHIRElementDefinitionSlicing Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRElementDefinition\FHIRElementDefinitionDiscriminator> $discriminator
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $description
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $ordered
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRSlicingRulesList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRSlicingRules $rules
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|iterable $discriminator = null,
                                null|string|FHIRStringPrimitive|FHIRString $description = null,
                                null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $ordered = null,
                                null|string|FHIRSlicingRulesList|FHIRSlicingRules $rules = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $discriminator) {
            $this->setDiscriminator(...$discriminator);
        }
        if (null !== $description) {
            $this->setDescription($description);
        }
        if (null !== $ordered) {
            $this->setOrdered($ordered);
        }
        if (null !== $rules) {
            $this->setRules($rules);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * Captures constraints on each element within the resource, profile, or extension.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Designates which child elements are used to discriminate between the slices when
     * processing an instance. If one or more discriminators are provided, the value of
     * the child elements in the instance data SHALL completely distinguish which slice
     * the element in the resource matches based on the allowed values for those
     * elements in each of the slices.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRElementDefinition\FHIRElementDefinitionDiscriminator>
     */
    public function getDiscriminator(): array
    {
        return $this->discriminator ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRElementDefinition\FHIRElementDefinitionDiscriminator>
     */
    public function getDiscriminatorIterator(): iterable
    {
        if (!isset($this->discriminator)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->discriminator);
    }

    /**
     * Captures constraints on each element within the resource, profile, or extension.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Designates which child elements are used to discriminate between the slices when
     * processing an instance. If one or more discriminators are provided, the value of
     * the child elements in the instance data SHALL completely distinguish which slice
     * the element in the resource matches based on the allowed values for those
     * elements in each of the slices.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRElementDefinition\FHIRElementDefinitionDiscriminator $discriminator
     * @return static
     */
    public function addDiscriminator(FHIRElementDefinitionDiscriminator $discriminator): self
    {
        if (!isset($this->discriminator)) {
            $this->discriminator = [];
        }
        $this->discriminator[] = $discriminator;
        return $this;
    }

    /**
     * Captures constraints on each element within the resource, profile, or extension.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Designates which child elements are used to discriminate between the slices when
     * processing an instance. If one or more discriminators are provided, the value of
     * the child elements in the instance data SHALL completely distinguish which slice
     * the element in the resource matches based on the allowed values for those
     * elements in each of the slices.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRElementDefinition\FHIRElementDefinitionDiscriminator ...$discriminator
     * @return static
     */
    public function setDiscriminator(FHIRElementDefinitionDiscriminator ...$discriminator): self
    {
        if ([] === $discriminator) {
            unset($this->discriminator);
            return $this;
        }
        $this->discriminator = $discriminator;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A human-readable text description of how the slicing works. If there is no
     * discriminator, this is required to be present to provide whatever information is
     * possible about how the slices can be differentiated.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getDescription(): null|FHIRString
    {
        return $this->description ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A human-readable text description of how the slicing works. If there is no
     * discriminator, this is required to be present to provide whatever information is
     * possible about how the slices can be differentiated.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $description
     * @return static
     */
    public function setDescription(null|string|FHIRStringPrimitive|FHIRString $description): self
    {
        if (null === $description) {
            unset($this->description);
            return $this;
        }
        if (!($description instanceof FHIRString)) {
            $description = new FHIRString(value: $description);
        }
        $this->description = $description;
        return $this;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If the matching elements have to occur in the same order as defined in the
     * profile.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    public function getOrdered(): null|FHIRBoolean
    {
        return $this->ordered ?? null;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If the matching elements have to occur in the same order as defined in the
     * profile.
     *
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $ordered
     * @return static
     */
    public function setOrdered(null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $ordered): self
    {
        if (null === $ordered) {
            unset($this->ordered);
            return $this;
        }
        if (!($ordered instanceof FHIRBoolean)) {
            $ordered = new FHIRBoolean(value: $ordered);
        }
        $this->ordered = $ordered;
        return $this;
    }

    /**
     * How slices are interpreted when evaluating an instance.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Whether additional slices are allowed or not. When the slices are ordered,
     * profile authors can also say that additional slices are only allowed at the end.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRSlicingRules
     */
    public function getRules(): null|FHIRSlicingRules
    {
        return $this->rules ?? null;
    }

    /**
     * How slices are interpreted when evaluating an instance.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Whether additional slices are allowed or not. When the slices are ordered,
     * profile authors can also say that additional slices are only allowed at the end.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRSlicingRulesList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRSlicingRules $rules
     * @return static
     */
    public function setRules(null|string|FHIRSlicingRulesList|FHIRSlicingRules $rules): self
    {
        if (null === $rules) {
            unset($this->rules);
            return $this;
        }
        if (!($rules instanceof FHIRSlicingRules)) {
            $rules = new FHIRSlicingRules(value: $rules);
        }
        $this->rules = $rules;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRElementDefinition\FHIRElementDefinitionSlicing $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRElementDefinition\FHIRElementDefinitionSlicing
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRElementDefinitionSlicing)) {
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
            } else if (self::FIELD_DISCRIMINATOR === $cen) {
                $type->addDiscriminator(FHIRElementDefinitionDiscriminator::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DESCRIPTION === $cen) {
                $type->setDescription(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ORDERED === $cen) {
                $type->setOrdered(FHIRBoolean::xmlUnserialize($ce, $config));
            } else if (self::FIELD_RULES === $cen) {
                $type->setRules(FHIRSlicingRules::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_DESCRIPTION])) {
            if (isset($type->description)) {
                $type->description->setValue((string)$attributes[self::FIELD_DESCRIPTION]);
            } else {
                $type->setDescription((string)$attributes[self::FIELD_DESCRIPTION]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_DESCRIPTION, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_ORDERED])) {
            if (isset($type->ordered)) {
                $type->ordered->setValue((string)$attributes[self::FIELD_ORDERED]);
            } else {
                $type->setOrdered((string)$attributes[self::FIELD_ORDERED]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_ORDERED, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_RULES])) {
            if (isset($type->rules)) {
                $type->rules->setValue((string)$attributes[self::FIELD_RULES]);
            } else {
                $type->setRules((string)$attributes[self::FIELD_RULES]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_RULES, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->description) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_DESCRIPTION]) {
            $xw->writeAttribute(self::FIELD_DESCRIPTION, $this->description->_getValueAsString());
        }
        if (isset($this->ordered) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_ORDERED]) {
            $xw->writeAttribute(self::FIELD_ORDERED, $this->ordered->_getValueAsString());
        }
        if (isset($this->rules) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_RULES]) {
            $xw->writeAttribute(self::FIELD_RULES, $this->rules->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->discriminator)) {
            foreach ($this->discriminator as $v) {
                $xw->startElement(self::FIELD_DISCRIMINATOR);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->description)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_DESCRIPTION]
                || $this->description->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_DESCRIPTION);
            $this->description->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_DESCRIPTION]);
            $xw->endElement();
        }
        if (isset($this->ordered)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_ORDERED]
                || $this->ordered->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_ORDERED);
            $this->ordered->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_ORDERED]);
            $xw->endElement();
        }
        if (isset($this->rules)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_RULES]
                || $this->rules->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_RULES);
            $this->rules->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_RULES]);
            $xw->endElement();
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRElementDefinition\FHIRElementDefinitionSlicing $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRElementDefinition\FHIRElementDefinitionSlicing
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
        } else if (!($type instanceof FHIRElementDefinitionSlicing)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->discriminator) || property_exists($decoded, self::FIELD_DISCRIMINATOR)) {
            if (is_object($decoded->discriminator)) {
                $vals = [$decoded->discriminator];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_DISCRIMINATOR, true);
            } else {
                $vals = $decoded->discriminator;
            }
            foreach($vals as $v) {
                $type->addDiscriminator(FHIRElementDefinitionDiscriminator::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->description)
            || isset($decoded->_description)
            || property_exists($decoded, self::FIELD_DESCRIPTION)
            || property_exists($decoded, self::FIELD_DESCRIPTION_EXT)) {
            $v = $decoded->_description ?? new \stdClass();
            $v->value = $decoded->description ?? null;
            $type->setDescription(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->ordered)
            || isset($decoded->_ordered)
            || property_exists($decoded, self::FIELD_ORDERED)
            || property_exists($decoded, self::FIELD_ORDERED_EXT)) {
            $v = $decoded->_ordered ?? new \stdClass();
            $v->value = $decoded->ordered ?? null;
            $type->setOrdered(FHIRBoolean::jsonUnserialize($v, $config));
        }
        if (isset($decoded->rules)
            || isset($decoded->_rules)
            || property_exists($decoded, self::FIELD_RULES)
            || property_exists($decoded, self::FIELD_RULES_EXT)) {
            $v = $decoded->_rules ?? new \stdClass();
            $v->value = $decoded->rules ?? null;
            $type->setRules(FHIRSlicingRules::jsonUnserialize($v, $config));
        }
        return $type;
    }

    /**
     * @return \stdClass
     */
    public function jsonSerialize(): mixed
    {
        $out = parent::jsonSerialize();
        if (isset($this->discriminator) && [] !== $this->discriminator) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_DISCRIMINATOR) && 1 === count($this->discriminator)) {
                $out->discriminator = $this->discriminator[0];
            } else {
                $out->discriminator = $this->discriminator;
            }
        }
        if (isset($this->description)) {
            if (null !== ($val = $this->description->getValue())) {
                $out->description = $val;
            }
            if ($this->description->_nonValueFieldDefined()) {
                $ext = $this->description->jsonSerialize();
                unset($ext->value);
                $out->_description = $ext;
            }
        }
        if (isset($this->ordered)) {
            if (null !== ($val = $this->ordered->getValue())) {
                $out->ordered = $val;
            }
            if ($this->ordered->_nonValueFieldDefined()) {
                $ext = $this->ordered->jsonSerialize();
                unset($ext->value);
                $out->_ordered = $ext;
            }
        }
        if (isset($this->rules)) {
            if (null !== ($val = $this->rules->getValue())) {
                $out->rules = $val;
            }
            if ($this->rules->_nonValueFieldDefined()) {
                $ext = $this->rules->jsonSerialize();
                unset($ext->value);
                $out->_rules = $ext;
            }
        }
        return $out;
    }
}
