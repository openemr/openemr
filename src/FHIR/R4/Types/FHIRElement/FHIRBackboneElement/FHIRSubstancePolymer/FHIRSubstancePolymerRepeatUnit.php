<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstancePolymer;

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
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceAmount;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * Todo.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRSubstancePolymerRepeatUnit extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_SUBSTANCE_POLYMER_DOT_REPEAT_UNIT;

    /* class_default.php:56 */
    public const FIELD_ORIENTATION_OF_POLYMERISATION = 'orientationOfPolymerisation';
    public const FIELD_REPEAT_UNIT = 'repeatUnit';
    public const FIELD_REPEAT_UNIT_EXT = '_repeatUnit';
    public const FIELD_AMOUNT = 'amount';
    public const FIELD_DEGREE_OF_POLYMERISATION = 'degreeOfPolymerisation';
    public const FIELD_STRUCTURAL_REPRESENTATION = 'structuralRepresentation';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_REPEAT_UNIT => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Todo.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $orientationOfPolymerisation;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Todo.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $repeatUnit;
    /**
     * Chemical substances are a single substance type whose primary defining element
     * is the molecular structure. Chemical substances shall be defined on the basis of
     * their complete covalent molecular structure; the presence of a salt
     * (counter-ion) and/or solvates (water, alcohols) is also captured. Purity, grade,
     * physical form or particle size are not taken into account in the definition of a
     * chemical substance or in the assignment of a Substance ID.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Todo.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceAmount
     */
    #[FHIRSubstanceAmount]
    protected FHIRSubstanceAmount $amount;
    /**
     * Todo.
     *
     * Todo.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstancePolymer\FHIRSubstancePolymerDegreeOfPolymerisation>
     */
    #[FHIRSubstancePolymerDegreeOfPolymerisation]
    protected array $degreeOfPolymerisation;
    /**
     * Todo.
     *
     * Todo.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstancePolymer\FHIRSubstancePolymerStructuralRepresentation>
     */
    #[FHIRSubstancePolymerStructuralRepresentation]
    protected array $structuralRepresentation;

    /* constructor.php:61 */
    /**
     * FHIRSubstancePolymerRepeatUnit Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $orientationOfPolymerisation
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $repeatUnit
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceAmount $amount
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstancePolymer\FHIRSubstancePolymerDegreeOfPolymerisation> $degreeOfPolymerisation
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstancePolymer\FHIRSubstancePolymerStructuralRepresentation> $structuralRepresentation
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|FHIRCodeableConcept $orientationOfPolymerisation = null,
                                null|string|FHIRStringPrimitive|FHIRString $repeatUnit = null,
                                null|FHIRSubstanceAmount $amount = null,
                                null|iterable $degreeOfPolymerisation = null,
                                null|iterable $structuralRepresentation = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $orientationOfPolymerisation) {
            $this->setOrientationOfPolymerisation($orientationOfPolymerisation);
        }
        if (null !== $repeatUnit) {
            $this->setRepeatUnit($repeatUnit);
        }
        if (null !== $amount) {
            $this->setAmount($amount);
        }
        if (null !== $degreeOfPolymerisation) {
            $this->setDegreeOfPolymerisation(...$degreeOfPolymerisation);
        }
        if (null !== $structuralRepresentation) {
            $this->setStructuralRepresentation(...$structuralRepresentation);
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
     * Todo.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getOrientationOfPolymerisation(): null|FHIRCodeableConcept
    {
        return $this->orientationOfPolymerisation ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Todo.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $orientationOfPolymerisation
     * @return static
     */
    public function setOrientationOfPolymerisation(null|FHIRCodeableConcept $orientationOfPolymerisation): self
    {
        if (null === $orientationOfPolymerisation) {
            unset($this->orientationOfPolymerisation);
            return $this;
        }
        $this->orientationOfPolymerisation = $orientationOfPolymerisation;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Todo.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getRepeatUnit(): null|FHIRString
    {
        return $this->repeatUnit ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Todo.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $repeatUnit
     * @return static
     */
    public function setRepeatUnit(null|string|FHIRStringPrimitive|FHIRString $repeatUnit): self
    {
        if (null === $repeatUnit) {
            unset($this->repeatUnit);
            return $this;
        }
        if (!($repeatUnit instanceof FHIRString)) {
            $repeatUnit = new FHIRString(value: $repeatUnit);
        }
        $this->repeatUnit = $repeatUnit;
        return $this;
    }

    /**
     * Chemical substances are a single substance type whose primary defining element
     * is the molecular structure. Chemical substances shall be defined on the basis of
     * their complete covalent molecular structure; the presence of a salt
     * (counter-ion) and/or solvates (water, alcohols) is also captured. Purity, grade,
     * physical form or particle size are not taken into account in the definition of a
     * chemical substance or in the assignment of a Substance ID.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Todo.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceAmount
     */
    public function getAmount(): null|FHIRSubstanceAmount
    {
        return $this->amount ?? null;
    }

    /**
     * Chemical substances are a single substance type whose primary defining element
     * is the molecular structure. Chemical substances shall be defined on the basis of
     * their complete covalent molecular structure; the presence of a salt
     * (counter-ion) and/or solvates (water, alcohols) is also captured. Purity, grade,
     * physical form or particle size are not taken into account in the definition of a
     * chemical substance or in the assignment of a Substance ID.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Todo.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceAmount $amount
     * @return static
     */
    public function setAmount(null|FHIRSubstanceAmount $amount): self
    {
        if (null === $amount) {
            unset($this->amount);
            return $this;
        }
        $this->amount = $amount;
        return $this;
    }

    /**
     * Todo.
     *
     * Todo.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstancePolymer\FHIRSubstancePolymerDegreeOfPolymerisation>
     */
    public function getDegreeOfPolymerisation(): array
    {
        return $this->degreeOfPolymerisation ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstancePolymer\FHIRSubstancePolymerDegreeOfPolymerisation>
     */
    public function getDegreeOfPolymerisationIterator(): iterable
    {
        if (!isset($this->degreeOfPolymerisation)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->degreeOfPolymerisation);
    }

    /**
     * Todo.
     *
     * Todo.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstancePolymer\FHIRSubstancePolymerDegreeOfPolymerisation $degreeOfPolymerisation
     * @return static
     */
    public function addDegreeOfPolymerisation(FHIRSubstancePolymerDegreeOfPolymerisation $degreeOfPolymerisation): self
    {
        if (!isset($this->degreeOfPolymerisation)) {
            $this->degreeOfPolymerisation = [];
        }
        $this->degreeOfPolymerisation[] = $degreeOfPolymerisation;
        return $this;
    }

    /**
     * Todo.
     *
     * Todo.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstancePolymer\FHIRSubstancePolymerDegreeOfPolymerisation ...$degreeOfPolymerisation
     * @return static
     */
    public function setDegreeOfPolymerisation(FHIRSubstancePolymerDegreeOfPolymerisation ...$degreeOfPolymerisation): self
    {
        if ([] === $degreeOfPolymerisation) {
            unset($this->degreeOfPolymerisation);
            return $this;
        }
        $this->degreeOfPolymerisation = $degreeOfPolymerisation;
        return $this;
    }

    /**
     * Todo.
     *
     * Todo.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstancePolymer\FHIRSubstancePolymerStructuralRepresentation>
     */
    public function getStructuralRepresentation(): array
    {
        return $this->structuralRepresentation ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstancePolymer\FHIRSubstancePolymerStructuralRepresentation>
     */
    public function getStructuralRepresentationIterator(): iterable
    {
        if (!isset($this->structuralRepresentation)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->structuralRepresentation);
    }

    /**
     * Todo.
     *
     * Todo.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstancePolymer\FHIRSubstancePolymerStructuralRepresentation $structuralRepresentation
     * @return static
     */
    public function addStructuralRepresentation(FHIRSubstancePolymerStructuralRepresentation $structuralRepresentation): self
    {
        if (!isset($this->structuralRepresentation)) {
            $this->structuralRepresentation = [];
        }
        $this->structuralRepresentation[] = $structuralRepresentation;
        return $this;
    }

    /**
     * Todo.
     *
     * Todo.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstancePolymer\FHIRSubstancePolymerStructuralRepresentation ...$structuralRepresentation
     * @return static
     */
    public function setStructuralRepresentation(FHIRSubstancePolymerStructuralRepresentation ...$structuralRepresentation): self
    {
        if ([] === $structuralRepresentation) {
            unset($this->structuralRepresentation);
            return $this;
        }
        $this->structuralRepresentation = $structuralRepresentation;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstancePolymer\FHIRSubstancePolymerRepeatUnit $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstancePolymer\FHIRSubstancePolymerRepeatUnit
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRSubstancePolymerRepeatUnit)) {
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
            } else if (self::FIELD_ORIENTATION_OF_POLYMERISATION === $cen) {
                $type->setOrientationOfPolymerisation(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_REPEAT_UNIT === $cen) {
                $type->setRepeatUnit(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_AMOUNT === $cen) {
                $type->setAmount(FHIRSubstanceAmount::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DEGREE_OF_POLYMERISATION === $cen) {
                $type->addDegreeOfPolymerisation(FHIRSubstancePolymerDegreeOfPolymerisation::xmlUnserialize($ce, $config));
            } else if (self::FIELD_STRUCTURAL_REPRESENTATION === $cen) {
                $type->addStructuralRepresentation(FHIRSubstancePolymerStructuralRepresentation::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_REPEAT_UNIT])) {
            if (isset($type->repeatUnit)) {
                $type->repeatUnit->setValue((string)$attributes[self::FIELD_REPEAT_UNIT]);
            } else {
                $type->setRepeatUnit((string)$attributes[self::FIELD_REPEAT_UNIT]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_REPEAT_UNIT, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->repeatUnit) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_REPEAT_UNIT]) {
            $xw->writeAttribute(self::FIELD_REPEAT_UNIT, $this->repeatUnit->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->orientationOfPolymerisation)) {
            $xw->startElement(self::FIELD_ORIENTATION_OF_POLYMERISATION);
            $this->orientationOfPolymerisation->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->repeatUnit)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_REPEAT_UNIT]
                || $this->repeatUnit->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_REPEAT_UNIT);
            $this->repeatUnit->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_REPEAT_UNIT]);
            $xw->endElement();
        }
        if (isset($this->amount)) {
            $xw->startElement(self::FIELD_AMOUNT);
            $this->amount->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->degreeOfPolymerisation)) {
            foreach ($this->degreeOfPolymerisation as $v) {
                $xw->startElement(self::FIELD_DEGREE_OF_POLYMERISATION);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->structuralRepresentation)) {
            foreach ($this->structuralRepresentation as $v) {
                $xw->startElement(self::FIELD_STRUCTURAL_REPRESENTATION);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstancePolymer\FHIRSubstancePolymerRepeatUnit $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstancePolymer\FHIRSubstancePolymerRepeatUnit
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
        } else if (!($type instanceof FHIRSubstancePolymerRepeatUnit)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->orientationOfPolymerisation) || property_exists($decoded, self::FIELD_ORIENTATION_OF_POLYMERISATION)) {
            if (is_array($decoded->orientationOfPolymerisation)) {
                $type->setOrientationOfPolymerisation(FHIRCodeableConcept::jsonUnserialize(reset($decoded->orientationOfPolymerisation), $config));
            } else {
                $type->setOrientationOfPolymerisation(FHIRCodeableConcept::jsonUnserialize($decoded->orientationOfPolymerisation, $config));
            }
        }
        if (isset($decoded->repeatUnit)
            || isset($decoded->_repeatUnit)
            || property_exists($decoded, self::FIELD_REPEAT_UNIT)
            || property_exists($decoded, self::FIELD_REPEAT_UNIT_EXT)) {
            $v = $decoded->_repeatUnit ?? new \stdClass();
            $v->value = $decoded->repeatUnit ?? null;
            $type->setRepeatUnit(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->amount) || property_exists($decoded, self::FIELD_AMOUNT)) {
            if (is_array($decoded->amount)) {
                $type->setAmount(FHIRSubstanceAmount::jsonUnserialize(reset($decoded->amount), $config));
            } else {
                $type->setAmount(FHIRSubstanceAmount::jsonUnserialize($decoded->amount, $config));
            }
        }
        if (isset($decoded->degreeOfPolymerisation) || property_exists($decoded, self::FIELD_DEGREE_OF_POLYMERISATION)) {
            if (is_object($decoded->degreeOfPolymerisation)) {
                $vals = [$decoded->degreeOfPolymerisation];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_DEGREE_OF_POLYMERISATION, true);
            } else {
                $vals = $decoded->degreeOfPolymerisation;
            }
            foreach($vals as $v) {
                $type->addDegreeOfPolymerisation(FHIRSubstancePolymerDegreeOfPolymerisation::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->structuralRepresentation) || property_exists($decoded, self::FIELD_STRUCTURAL_REPRESENTATION)) {
            if (is_object($decoded->structuralRepresentation)) {
                $vals = [$decoded->structuralRepresentation];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_STRUCTURAL_REPRESENTATION, true);
            } else {
                $vals = $decoded->structuralRepresentation;
            }
            foreach($vals as $v) {
                $type->addStructuralRepresentation(FHIRSubstancePolymerStructuralRepresentation::jsonUnserialize($v, $config));
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
        if (isset($this->orientationOfPolymerisation)) {
            $out->orientationOfPolymerisation = $this->orientationOfPolymerisation;
        }
        if (isset($this->repeatUnit)) {
            if (null !== ($val = $this->repeatUnit->getValue())) {
                $out->repeatUnit = $val;
            }
            if ($this->repeatUnit->_nonValueFieldDefined()) {
                $ext = $this->repeatUnit->jsonSerialize();
                unset($ext->value);
                $out->_repeatUnit = $ext;
            }
        }
        if (isset($this->amount)) {
            $out->amount = $this->amount;
        }
        if (isset($this->degreeOfPolymerisation) && [] !== $this->degreeOfPolymerisation) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_DEGREE_OF_POLYMERISATION) && 1 === count($this->degreeOfPolymerisation)) {
                $out->degreeOfPolymerisation = $this->degreeOfPolymerisation[0];
            } else {
                $out->degreeOfPolymerisation = $this->degreeOfPolymerisation;
            }
        }
        if (isset($this->structuralRepresentation) && [] !== $this->structuralRepresentation) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_STRUCTURAL_REPRESENTATION) && 1 === count($this->structuralRepresentation)) {
                $out->structuralRepresentation = $this->structuralRepresentation[0];
            } else {
                $out->structuralRepresentation = $this->structuralRepresentation;
            }
        }
        return $out;
    }
}
