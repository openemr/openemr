<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;

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
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceAmount\FHIRSubstanceAmountReferenceRange;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRange;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * Chemical substances are a single substance type whose primary defining element
 * is the molecular structure. Chemical substances shall be defined on the basis of
 * their complete covalent molecular structure; the presence of a salt
 * (counter-ion) and/or solvates (water, alcohols) is also captured. Purity, grade,
 * physical form or particle size are not taken into account in the definition of a
 * chemical substance or in the assignment of a Substance ID.
 * If the element is present, it must have a value for at least one of the defined
 * elements, an \@id referenced from the Narrative, or extensions
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRSubstanceAmount extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_SUBSTANCE_AMOUNT;

    /* class_default.php:56 */
    public const FIELD_AMOUNT_QUANTITY = 'amountQuantity';
    public const FIELD_AMOUNT_RANGE = 'amountRange';
    public const FIELD_AMOUNT_STRING = 'amountString';
    public const FIELD_AMOUNT_STRING_EXT = '_amountString';
    public const FIELD_AMOUNT_TYPE = 'amountType';
    public const FIELD_AMOUNT_TEXT = 'amountText';
    public const FIELD_AMOUNT_TEXT_EXT = '_amountText';
    public const FIELD_REFERENCE_RANGE = 'referenceRange';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_AMOUNT_STRING => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_AMOUNT_TEXT => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Used to capture quantitative values for a variety of elements. If only limits
     * are given, the arithmetic mean would be the average. If only a single definite
     * value for a given element is given, it would be captured in this field. (choose
     * any one of amount*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity
     */
    #[FHIRQuantity]
    protected FHIRQuantity $amountQuantity;
    /**
     * A set of ordered Quantities defined by a low and high limit.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Used to capture quantitative values for a variety of elements. If only limits
     * are given, the arithmetic mean would be the average. If only a single definite
     * value for a given element is given, it would be captured in this field. (choose
     * any one of amount*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRange
     */
    #[FHIRRange]
    protected FHIRRange $amountRange;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Used to capture quantitative values for a variety of elements. If only limits
     * are given, the arithmetic mean would be the average. If only a single definite
     * value for a given element is given, it would be captured in this field. (choose
     * any one of amount*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $amountString;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Most elements that require a quantitative value will also have a field called
     * amount type. Amount type should always be specified because the actual value of
     * the amount is often dependent on it. EXAMPLE: In capturing the actual relative
     * amounts of substances or molecular fragments it is essential to indicate whether
     * the amount refers to a mole ratio or weight ratio. For any given element an
     * effort should be made to use same the amount type for all related definitional
     * elements.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $amountType;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A textual comment on a numeric value.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $amountText;
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
     * Reference range of possible or expected values.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceAmount\FHIRSubstanceAmountReferenceRange
     */
    #[FHIRSubstanceAmountReferenceRange]
    protected FHIRSubstanceAmountReferenceRange $referenceRange;

    /* constructor.php:61 */
    /**
     * FHIRSubstanceAmount Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity $amountQuantity
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRange $amountRange
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $amountString
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $amountType
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $amountText
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceAmount\FHIRSubstanceAmountReferenceRange $referenceRange
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|FHIRQuantity $amountQuantity = null,
                                null|FHIRRange $amountRange = null,
                                null|string|FHIRStringPrimitive|FHIRString $amountString = null,
                                null|FHIRCodeableConcept $amountType = null,
                                null|string|FHIRStringPrimitive|FHIRString $amountText = null,
                                null|FHIRSubstanceAmountReferenceRange $referenceRange = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $amountQuantity) {
            $this->setAmountQuantity($amountQuantity);
        }
        if (null !== $amountRange) {
            $this->setAmountRange($amountRange);
        }
        if (null !== $amountString) {
            $this->setAmountString($amountString);
        }
        if (null !== $amountType) {
            $this->setAmountType($amountType);
        }
        if (null !== $amountText) {
            $this->setAmountText($amountText);
        }
        if (null !== $referenceRange) {
            $this->setReferenceRange($referenceRange);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Used to capture quantitative values for a variety of elements. If only limits
     * are given, the arithmetic mean would be the average. If only a single definite
     * value for a given element is given, it would be captured in this field. (choose
     * any one of amount*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity
     */
    public function getAmountQuantity(): null|FHIRQuantity
    {
        return $this->amountQuantity ?? null;
    }

    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Used to capture quantitative values for a variety of elements. If only limits
     * are given, the arithmetic mean would be the average. If only a single definite
     * value for a given element is given, it would be captured in this field. (choose
     * any one of amount*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity $amountQuantity
     * @return static
     */
    public function setAmountQuantity(null|FHIRQuantity $amountQuantity): self
    {
        if (null === $amountQuantity) {
            unset($this->amountQuantity);
            return $this;
        }
        $this->amountQuantity = $amountQuantity;
        return $this;
    }

    /**
     * A set of ordered Quantities defined by a low and high limit.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Used to capture quantitative values for a variety of elements. If only limits
     * are given, the arithmetic mean would be the average. If only a single definite
     * value for a given element is given, it would be captured in this field. (choose
     * any one of amount*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRange
     */
    public function getAmountRange(): null|FHIRRange
    {
        return $this->amountRange ?? null;
    }

    /**
     * A set of ordered Quantities defined by a low and high limit.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Used to capture quantitative values for a variety of elements. If only limits
     * are given, the arithmetic mean would be the average. If only a single definite
     * value for a given element is given, it would be captured in this field. (choose
     * any one of amount*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRange $amountRange
     * @return static
     */
    public function setAmountRange(null|FHIRRange $amountRange): self
    {
        if (null === $amountRange) {
            unset($this->amountRange);
            return $this;
        }
        $this->amountRange = $amountRange;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Used to capture quantitative values for a variety of elements. If only limits
     * are given, the arithmetic mean would be the average. If only a single definite
     * value for a given element is given, it would be captured in this field. (choose
     * any one of amount*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getAmountString(): null|FHIRString
    {
        return $this->amountString ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Used to capture quantitative values for a variety of elements. If only limits
     * are given, the arithmetic mean would be the average. If only a single definite
     * value for a given element is given, it would be captured in this field. (choose
     * any one of amount*, but only one)
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $amountString
     * @return static
     */
    public function setAmountString(null|string|FHIRStringPrimitive|FHIRString $amountString): self
    {
        if (null === $amountString) {
            unset($this->amountString);
            return $this;
        }
        if (!($amountString instanceof FHIRString)) {
            $amountString = new FHIRString(value: $amountString);
        }
        $this->amountString = $amountString;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Most elements that require a quantitative value will also have a field called
     * amount type. Amount type should always be specified because the actual value of
     * the amount is often dependent on it. EXAMPLE: In capturing the actual relative
     * amounts of substances or molecular fragments it is essential to indicate whether
     * the amount refers to a mole ratio or weight ratio. For any given element an
     * effort should be made to use same the amount type for all related definitional
     * elements.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getAmountType(): null|FHIRCodeableConcept
    {
        return $this->amountType ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Most elements that require a quantitative value will also have a field called
     * amount type. Amount type should always be specified because the actual value of
     * the amount is often dependent on it. EXAMPLE: In capturing the actual relative
     * amounts of substances or molecular fragments it is essential to indicate whether
     * the amount refers to a mole ratio or weight ratio. For any given element an
     * effort should be made to use same the amount type for all related definitional
     * elements.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $amountType
     * @return static
     */
    public function setAmountType(null|FHIRCodeableConcept $amountType): self
    {
        if (null === $amountType) {
            unset($this->amountType);
            return $this;
        }
        $this->amountType = $amountType;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A textual comment on a numeric value.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getAmountText(): null|FHIRString
    {
        return $this->amountText ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A textual comment on a numeric value.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $amountText
     * @return static
     */
    public function setAmountText(null|string|FHIRStringPrimitive|FHIRString $amountText): self
    {
        if (null === $amountText) {
            unset($this->amountText);
            return $this;
        }
        if (!($amountText instanceof FHIRString)) {
            $amountText = new FHIRString(value: $amountText);
        }
        $this->amountText = $amountText;
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
     * Reference range of possible or expected values.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceAmount\FHIRSubstanceAmountReferenceRange
     */
    public function getReferenceRange(): null|FHIRSubstanceAmountReferenceRange
    {
        return $this->referenceRange ?? null;
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
     * Reference range of possible or expected values.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceAmount\FHIRSubstanceAmountReferenceRange $referenceRange
     * @return static
     */
    public function setReferenceRange(null|FHIRSubstanceAmountReferenceRange $referenceRange): self
    {
        if (null === $referenceRange) {
            unset($this->referenceRange);
            return $this;
        }
        $this->referenceRange = $referenceRange;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceAmount $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceAmount
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRSubstanceAmount)) {
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
            } else if (self::FIELD_AMOUNT_QUANTITY === $cen) {
                $type->setAmountQuantity(FHIRQuantity::xmlUnserialize($ce, $config));
            } else if (self::FIELD_AMOUNT_RANGE === $cen) {
                $type->setAmountRange(FHIRRange::xmlUnserialize($ce, $config));
            } else if (self::FIELD_AMOUNT_STRING === $cen) {
                $type->setAmountString(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_AMOUNT_TYPE === $cen) {
                $type->setAmountType(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_AMOUNT_TEXT === $cen) {
                $type->setAmountText(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_REFERENCE_RANGE === $cen) {
                $type->setReferenceRange(FHIRSubstanceAmountReferenceRange::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_AMOUNT_STRING])) {
            if (isset($type->amountString)) {
                $type->amountString->setValue((string)$attributes[self::FIELD_AMOUNT_STRING]);
            } else {
                $type->setAmountString((string)$attributes[self::FIELD_AMOUNT_STRING]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_AMOUNT_STRING, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_AMOUNT_TEXT])) {
            if (isset($type->amountText)) {
                $type->amountText->setValue((string)$attributes[self::FIELD_AMOUNT_TEXT]);
            } else {
                $type->setAmountText((string)$attributes[self::FIELD_AMOUNT_TEXT]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_AMOUNT_TEXT, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->amountString) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_AMOUNT_STRING]) {
            $xw->writeAttribute(self::FIELD_AMOUNT_STRING, $this->amountString->_getValueAsString());
        }
        if (isset($this->amountText) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_AMOUNT_TEXT]) {
            $xw->writeAttribute(self::FIELD_AMOUNT_TEXT, $this->amountText->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->amountQuantity)) {
            $xw->startElement(self::FIELD_AMOUNT_QUANTITY);
            $this->amountQuantity->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->amountRange)) {
            $xw->startElement(self::FIELD_AMOUNT_RANGE);
            $this->amountRange->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->amountString)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_AMOUNT_STRING]
                || $this->amountString->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_AMOUNT_STRING);
            $this->amountString->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_AMOUNT_STRING]);
            $xw->endElement();
        }
        if (isset($this->amountType)) {
            $xw->startElement(self::FIELD_AMOUNT_TYPE);
            $this->amountType->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->amountText)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_AMOUNT_TEXT]
                || $this->amountText->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_AMOUNT_TEXT);
            $this->amountText->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_AMOUNT_TEXT]);
            $xw->endElement();
        }
        if (isset($this->referenceRange)) {
            $xw->startElement(self::FIELD_REFERENCE_RANGE);
            $this->referenceRange->xmlSerialize($xw, $config);
            $xw->endElement();
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceAmount $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceAmount
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
        } else if (!($type instanceof FHIRSubstanceAmount)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->amountQuantity) || property_exists($decoded, self::FIELD_AMOUNT_QUANTITY)) {
            if (is_array($decoded->amountQuantity)) {
                $type->setAmountQuantity(FHIRQuantity::jsonUnserialize(reset($decoded->amountQuantity), $config));
            } else {
                $type->setAmountQuantity(FHIRQuantity::jsonUnserialize($decoded->amountQuantity, $config));
            }
        }
        if (isset($decoded->amountRange) || property_exists($decoded, self::FIELD_AMOUNT_RANGE)) {
            if (is_array($decoded->amountRange)) {
                $type->setAmountRange(FHIRRange::jsonUnserialize(reset($decoded->amountRange), $config));
            } else {
                $type->setAmountRange(FHIRRange::jsonUnserialize($decoded->amountRange, $config));
            }
        }
        if (isset($decoded->amountString)
            || isset($decoded->_amountString)
            || property_exists($decoded, self::FIELD_AMOUNT_STRING)
            || property_exists($decoded, self::FIELD_AMOUNT_STRING_EXT)) {
            $v = $decoded->_amountString ?? new \stdClass();
            $v->value = $decoded->amountString ?? null;
            $type->setAmountString(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->amountType) || property_exists($decoded, self::FIELD_AMOUNT_TYPE)) {
            if (is_array($decoded->amountType)) {
                $type->setAmountType(FHIRCodeableConcept::jsonUnserialize(reset($decoded->amountType), $config));
            } else {
                $type->setAmountType(FHIRCodeableConcept::jsonUnserialize($decoded->amountType, $config));
            }
        }
        if (isset($decoded->amountText)
            || isset($decoded->_amountText)
            || property_exists($decoded, self::FIELD_AMOUNT_TEXT)
            || property_exists($decoded, self::FIELD_AMOUNT_TEXT_EXT)) {
            $v = $decoded->_amountText ?? new \stdClass();
            $v->value = $decoded->amountText ?? null;
            $type->setAmountText(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->referenceRange) || property_exists($decoded, self::FIELD_REFERENCE_RANGE)) {
            if (is_array($decoded->referenceRange)) {
                $type->setReferenceRange(FHIRSubstanceAmountReferenceRange::jsonUnserialize(reset($decoded->referenceRange), $config));
            } else {
                $type->setReferenceRange(FHIRSubstanceAmountReferenceRange::jsonUnserialize($decoded->referenceRange, $config));
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
        if (isset($this->amountQuantity)) {
            $out->amountQuantity = $this->amountQuantity;
        }
        if (isset($this->amountRange)) {
            $out->amountRange = $this->amountRange;
        }
        if (isset($this->amountString)) {
            if (null !== ($val = $this->amountString->getValue())) {
                $out->amountString = $val;
            }
            if ($this->amountString->_nonValueFieldDefined()) {
                $ext = $this->amountString->jsonSerialize();
                unset($ext->value);
                $out->_amountString = $ext;
            }
        }
        if (isset($this->amountType)) {
            $out->amountType = $this->amountType;
        }
        if (isset($this->amountText)) {
            if (null !== ($val = $this->amountText->getValue())) {
                $out->amountText = $val;
            }
            if ($this->amountText->_nonValueFieldDefined()) {
                $ext = $this->amountText->jsonSerialize();
                unset($ext->value);
                $out->_amountText = $ext;
            }
        }
        if (isset($this->referenceRange)) {
            $out->referenceRange = $this->referenceRange;
        }
        return $out;
    }
}
