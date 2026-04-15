<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductIngredient;

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
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRatio;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * An ingredient of a manufactured item or pharmaceutical product.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRMedicinalProductIngredientStrength extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_MEDICINAL_PRODUCT_INGREDIENT_DOT_STRENGTH;

    /* class_default.php:56 */
    public const FIELD_PRESENTATION = 'presentation';
    public const FIELD_PRESENTATION_LOW_LIMIT = 'presentationLowLimit';
    public const FIELD_CONCENTRATION = 'concentration';
    public const FIELD_CONCENTRATION_LOW_LIMIT = 'concentrationLowLimit';
    public const FIELD_MEASUREMENT_POINT = 'measurementPoint';
    public const FIELD_MEASUREMENT_POINT_EXT = '_measurementPoint';
    public const FIELD_COUNTRY = 'country';
    public const FIELD_REFERENCE_STRENGTH = 'referenceStrength';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_PRESENTATION => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_MEASUREMENT_POINT => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * A relationship of two Quantity values - expressed as a numerator and a
     * denominator.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The quantity of substance in the unit of presentation, or in the volume (or
     * mass) of the single pharmaceutical product or manufactured item.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRatio
     */
    #[FHIRRatio]
    protected FHIRRatio $presentation;
    /**
     * A relationship of two Quantity values - expressed as a numerator and a
     * denominator.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A lower limit for the quantity of substance in the unit of presentation. For use
     * when there is a range of strengths, this is the lower limit, with the
     * presentation attribute becoming the upper limit.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRatio
     */
    #[FHIRRatio]
    protected FHIRRatio $presentationLowLimit;
    /**
     * A relationship of two Quantity values - expressed as a numerator and a
     * denominator.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The strength per unitary volume (or mass).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRatio
     */
    #[FHIRRatio]
    protected FHIRRatio $concentration;
    /**
     * A relationship of two Quantity values - expressed as a numerator and a
     * denominator.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A lower limit for the strength per unitary volume (or mass), for when there is a
     * range. The concentration attribute then becomes the upper limit.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRatio
     */
    #[FHIRRatio]
    protected FHIRRatio $concentrationLowLimit;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * For when strength is measured at a particular point or distance.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $measurementPoint;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The country or countries for which the strength range applies.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    #[FHIRCodeableConcept]
    protected array $country;
    /**
     * An ingredient of a manufactured item or pharmaceutical product.
     *
     * Strength expressed in terms of a reference substance.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductIngredient\FHIRMedicinalProductIngredientReferenceStrength>
     */
    #[FHIRMedicinalProductIngredientReferenceStrength]
    protected array $referenceStrength;

    /* constructor.php:61 */
    /**
     * FHIRMedicinalProductIngredientStrength Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRatio $presentation
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRatio $presentationLowLimit
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRatio $concentration
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRatio $concentrationLowLimit
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $measurementPoint
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $country
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductIngredient\FHIRMedicinalProductIngredientReferenceStrength> $referenceStrength
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|FHIRRatio $presentation = null,
                                null|FHIRRatio $presentationLowLimit = null,
                                null|FHIRRatio $concentration = null,
                                null|FHIRRatio $concentrationLowLimit = null,
                                null|string|FHIRStringPrimitive|FHIRString $measurementPoint = null,
                                null|iterable $country = null,
                                null|iterable $referenceStrength = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $presentation) {
            $this->setPresentation($presentation);
        }
        if (null !== $presentationLowLimit) {
            $this->setPresentationLowLimit($presentationLowLimit);
        }
        if (null !== $concentration) {
            $this->setConcentration($concentration);
        }
        if (null !== $concentrationLowLimit) {
            $this->setConcentrationLowLimit($concentrationLowLimit);
        }
        if (null !== $measurementPoint) {
            $this->setMeasurementPoint($measurementPoint);
        }
        if (null !== $country) {
            $this->setCountry(...$country);
        }
        if (null !== $referenceStrength) {
            $this->setReferenceStrength(...$referenceStrength);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * A relationship of two Quantity values - expressed as a numerator and a
     * denominator.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The quantity of substance in the unit of presentation, or in the volume (or
     * mass) of the single pharmaceutical product or manufactured item.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRatio
     */
    public function getPresentation(): null|FHIRRatio
    {
        return $this->presentation ?? null;
    }

    /**
     * A relationship of two Quantity values - expressed as a numerator and a
     * denominator.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The quantity of substance in the unit of presentation, or in the volume (or
     * mass) of the single pharmaceutical product or manufactured item.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRatio $presentation
     * @return static
     */
    public function setPresentation(null|FHIRRatio $presentation): self
    {
        if (null === $presentation) {
            unset($this->presentation);
            return $this;
        }
        $this->presentation = $presentation;
        return $this;
    }

    /**
     * A relationship of two Quantity values - expressed as a numerator and a
     * denominator.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A lower limit for the quantity of substance in the unit of presentation. For use
     * when there is a range of strengths, this is the lower limit, with the
     * presentation attribute becoming the upper limit.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRatio
     */
    public function getPresentationLowLimit(): null|FHIRRatio
    {
        return $this->presentationLowLimit ?? null;
    }

    /**
     * A relationship of two Quantity values - expressed as a numerator and a
     * denominator.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A lower limit for the quantity of substance in the unit of presentation. For use
     * when there is a range of strengths, this is the lower limit, with the
     * presentation attribute becoming the upper limit.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRatio $presentationLowLimit
     * @return static
     */
    public function setPresentationLowLimit(null|FHIRRatio $presentationLowLimit): self
    {
        if (null === $presentationLowLimit) {
            unset($this->presentationLowLimit);
            return $this;
        }
        $this->presentationLowLimit = $presentationLowLimit;
        return $this;
    }

    /**
     * A relationship of two Quantity values - expressed as a numerator and a
     * denominator.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The strength per unitary volume (or mass).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRatio
     */
    public function getConcentration(): null|FHIRRatio
    {
        return $this->concentration ?? null;
    }

    /**
     * A relationship of two Quantity values - expressed as a numerator and a
     * denominator.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The strength per unitary volume (or mass).
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRatio $concentration
     * @return static
     */
    public function setConcentration(null|FHIRRatio $concentration): self
    {
        if (null === $concentration) {
            unset($this->concentration);
            return $this;
        }
        $this->concentration = $concentration;
        return $this;
    }

    /**
     * A relationship of two Quantity values - expressed as a numerator and a
     * denominator.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A lower limit for the strength per unitary volume (or mass), for when there is a
     * range. The concentration attribute then becomes the upper limit.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRatio
     */
    public function getConcentrationLowLimit(): null|FHIRRatio
    {
        return $this->concentrationLowLimit ?? null;
    }

    /**
     * A relationship of two Quantity values - expressed as a numerator and a
     * denominator.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A lower limit for the strength per unitary volume (or mass), for when there is a
     * range. The concentration attribute then becomes the upper limit.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRatio $concentrationLowLimit
     * @return static
     */
    public function setConcentrationLowLimit(null|FHIRRatio $concentrationLowLimit): self
    {
        if (null === $concentrationLowLimit) {
            unset($this->concentrationLowLimit);
            return $this;
        }
        $this->concentrationLowLimit = $concentrationLowLimit;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * For when strength is measured at a particular point or distance.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getMeasurementPoint(): null|FHIRString
    {
        return $this->measurementPoint ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * For when strength is measured at a particular point or distance.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $measurementPoint
     * @return static
     */
    public function setMeasurementPoint(null|string|FHIRStringPrimitive|FHIRString $measurementPoint): self
    {
        if (null === $measurementPoint) {
            unset($this->measurementPoint);
            return $this;
        }
        if (!($measurementPoint instanceof FHIRString)) {
            $measurementPoint = new FHIRString(value: $measurementPoint);
        }
        $this->measurementPoint = $measurementPoint;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The country or countries for which the strength range applies.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getCountry(): array
    {
        return $this->country ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getCountryIterator(): iterable
    {
        if (!isset($this->country)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->country);
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The country or countries for which the strength range applies.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $country
     * @return static
     */
    public function addCountry(FHIRCodeableConcept $country): self
    {
        if (!isset($this->country)) {
            $this->country = [];
        }
        $this->country[] = $country;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The country or countries for which the strength range applies.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept ...$country
     * @return static
     */
    public function setCountry(FHIRCodeableConcept ...$country): self
    {
        if ([] === $country) {
            unset($this->country);
            return $this;
        }
        $this->country = $country;
        return $this;
    }

    /**
     * An ingredient of a manufactured item or pharmaceutical product.
     *
     * Strength expressed in terms of a reference substance.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductIngredient\FHIRMedicinalProductIngredientReferenceStrength>
     */
    public function getReferenceStrength(): array
    {
        return $this->referenceStrength ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductIngredient\FHIRMedicinalProductIngredientReferenceStrength>
     */
    public function getReferenceStrengthIterator(): iterable
    {
        if (!isset($this->referenceStrength)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->referenceStrength);
    }

    /**
     * An ingredient of a manufactured item or pharmaceutical product.
     *
     * Strength expressed in terms of a reference substance.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductIngredient\FHIRMedicinalProductIngredientReferenceStrength $referenceStrength
     * @return static
     */
    public function addReferenceStrength(FHIRMedicinalProductIngredientReferenceStrength $referenceStrength): self
    {
        if (!isset($this->referenceStrength)) {
            $this->referenceStrength = [];
        }
        $this->referenceStrength[] = $referenceStrength;
        return $this;
    }

    /**
     * An ingredient of a manufactured item or pharmaceutical product.
     *
     * Strength expressed in terms of a reference substance.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductIngredient\FHIRMedicinalProductIngredientReferenceStrength ...$referenceStrength
     * @return static
     */
    public function setReferenceStrength(FHIRMedicinalProductIngredientReferenceStrength ...$referenceStrength): self
    {
        if ([] === $referenceStrength) {
            unset($this->referenceStrength);
            return $this;
        }
        $this->referenceStrength = $referenceStrength;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductIngredient\FHIRMedicinalProductIngredientStrength $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductIngredient\FHIRMedicinalProductIngredientStrength
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRMedicinalProductIngredientStrength)) {
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
            } else if (self::FIELD_PRESENTATION === $cen) {
                $type->setPresentation(FHIRRatio::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PRESENTATION_LOW_LIMIT === $cen) {
                $type->setPresentationLowLimit(FHIRRatio::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CONCENTRATION === $cen) {
                $type->setConcentration(FHIRRatio::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CONCENTRATION_LOW_LIMIT === $cen) {
                $type->setConcentrationLowLimit(FHIRRatio::xmlUnserialize($ce, $config));
            } else if (self::FIELD_MEASUREMENT_POINT === $cen) {
                $type->setMeasurementPoint(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_COUNTRY === $cen) {
                $type->addCountry(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_REFERENCE_STRENGTH === $cen) {
                $type->addReferenceStrength(FHIRMedicinalProductIngredientReferenceStrength::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_MEASUREMENT_POINT])) {
            if (isset($type->measurementPoint)) {
                $type->measurementPoint->setValue((string)$attributes[self::FIELD_MEASUREMENT_POINT]);
            } else {
                $type->setMeasurementPoint((string)$attributes[self::FIELD_MEASUREMENT_POINT]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_MEASUREMENT_POINT, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->measurementPoint) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_MEASUREMENT_POINT]) {
            $xw->writeAttribute(self::FIELD_MEASUREMENT_POINT, $this->measurementPoint->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->presentation)) {
            $xw->startElement(self::FIELD_PRESENTATION);
            $this->presentation->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->presentationLowLimit)) {
            $xw->startElement(self::FIELD_PRESENTATION_LOW_LIMIT);
            $this->presentationLowLimit->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->concentration)) {
            $xw->startElement(self::FIELD_CONCENTRATION);
            $this->concentration->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->concentrationLowLimit)) {
            $xw->startElement(self::FIELD_CONCENTRATION_LOW_LIMIT);
            $this->concentrationLowLimit->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->measurementPoint)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_MEASUREMENT_POINT]
                || $this->measurementPoint->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_MEASUREMENT_POINT);
            $this->measurementPoint->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_MEASUREMENT_POINT]);
            $xw->endElement();
        }
        if (isset($this->country)) {
            foreach ($this->country as $v) {
                $xw->startElement(self::FIELD_COUNTRY);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->referenceStrength)) {
            foreach ($this->referenceStrength as $v) {
                $xw->startElement(self::FIELD_REFERENCE_STRENGTH);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductIngredient\FHIRMedicinalProductIngredientStrength $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductIngredient\FHIRMedicinalProductIngredientStrength
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
        } else if (!($type instanceof FHIRMedicinalProductIngredientStrength)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->presentation) || property_exists($decoded, self::FIELD_PRESENTATION)) {
            if (is_array($decoded->presentation)) {
                $type->setPresentation(FHIRRatio::jsonUnserialize(reset($decoded->presentation), $config));
            } else {
                $type->setPresentation(FHIRRatio::jsonUnserialize($decoded->presentation, $config));
            }
        }
        if (isset($decoded->presentationLowLimit) || property_exists($decoded, self::FIELD_PRESENTATION_LOW_LIMIT)) {
            if (is_array($decoded->presentationLowLimit)) {
                $type->setPresentationLowLimit(FHIRRatio::jsonUnserialize(reset($decoded->presentationLowLimit), $config));
            } else {
                $type->setPresentationLowLimit(FHIRRatio::jsonUnserialize($decoded->presentationLowLimit, $config));
            }
        }
        if (isset($decoded->concentration) || property_exists($decoded, self::FIELD_CONCENTRATION)) {
            if (is_array($decoded->concentration)) {
                $type->setConcentration(FHIRRatio::jsonUnserialize(reset($decoded->concentration), $config));
            } else {
                $type->setConcentration(FHIRRatio::jsonUnserialize($decoded->concentration, $config));
            }
        }
        if (isset($decoded->concentrationLowLimit) || property_exists($decoded, self::FIELD_CONCENTRATION_LOW_LIMIT)) {
            if (is_array($decoded->concentrationLowLimit)) {
                $type->setConcentrationLowLimit(FHIRRatio::jsonUnserialize(reset($decoded->concentrationLowLimit), $config));
            } else {
                $type->setConcentrationLowLimit(FHIRRatio::jsonUnserialize($decoded->concentrationLowLimit, $config));
            }
        }
        if (isset($decoded->measurementPoint)
            || isset($decoded->_measurementPoint)
            || property_exists($decoded, self::FIELD_MEASUREMENT_POINT)
            || property_exists($decoded, self::FIELD_MEASUREMENT_POINT_EXT)) {
            $v = $decoded->_measurementPoint ?? new \stdClass();
            $v->value = $decoded->measurementPoint ?? null;
            $type->setMeasurementPoint(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->country) || property_exists($decoded, self::FIELD_COUNTRY)) {
            if (is_object($decoded->country)) {
                $vals = [$decoded->country];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_COUNTRY, true);
            } else {
                $vals = $decoded->country;
            }
            foreach($vals as $v) {
                $type->addCountry(FHIRCodeableConcept::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->referenceStrength) || property_exists($decoded, self::FIELD_REFERENCE_STRENGTH)) {
            if (is_object($decoded->referenceStrength)) {
                $vals = [$decoded->referenceStrength];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_REFERENCE_STRENGTH, true);
            } else {
                $vals = $decoded->referenceStrength;
            }
            foreach($vals as $v) {
                $type->addReferenceStrength(FHIRMedicinalProductIngredientReferenceStrength::jsonUnserialize($v, $config));
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
        if (isset($this->presentation)) {
            $out->presentation = $this->presentation;
        }
        if (isset($this->presentationLowLimit)) {
            $out->presentationLowLimit = $this->presentationLowLimit;
        }
        if (isset($this->concentration)) {
            $out->concentration = $this->concentration;
        }
        if (isset($this->concentrationLowLimit)) {
            $out->concentrationLowLimit = $this->concentrationLowLimit;
        }
        if (isset($this->measurementPoint)) {
            if (null !== ($val = $this->measurementPoint->getValue())) {
                $out->measurementPoint = $val;
            }
            if ($this->measurementPoint->_nonValueFieldDefined()) {
                $ext = $this->measurementPoint->jsonSerialize();
                unset($ext->value);
                $out->_measurementPoint = $ext;
            }
        }
        if (isset($this->country) && [] !== $this->country) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_COUNTRY) && 1 === count($this->country)) {
                $out->country = $this->country[0];
            } else {
                $out->country = $this->country;
            }
        }
        if (isset($this->referenceStrength) && [] !== $this->referenceStrength) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_REFERENCE_STRENGTH) && 1 === count($this->referenceStrength)) {
                $out->referenceStrength = $this->referenceStrength[0];
            } else {
                $out->referenceStrength = $this->referenceStrength;
            }
        }
        return $out;
    }
}
