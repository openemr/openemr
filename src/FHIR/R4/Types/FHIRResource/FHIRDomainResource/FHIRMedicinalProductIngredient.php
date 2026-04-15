<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource;

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
use OpenEMR\FHIR\Types\ResourceTypeInterface;
use OpenEMR\FHIR\Validation\Rules\MinOccursRule;
use OpenEMR\FHIR\Validation\TypeValidationsTrait;
use OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductIngredient\FHIRMedicinalProductIngredientSpecifiedSubstance;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductIngredient\FHIRMedicinalProductIngredientSubstance;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri;
use OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource;
use OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive;
use OpenEMR\FHIR\Versions\R4\Version;
use OpenEMR\FHIR\Versions\R4\VersionConstants;
use OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface;
use OpenEMR\FHIR\Versions\R4\VersionTypeMap;

/**
 * An ingredient of a manufactured item or pharmaceutical product.
 * If the element is present, it must have either a \@value, an \@id, or extensions
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRMedicinalProductIngredient extends FHIRDomainResource implements VersionContainedTypeInterface
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_MEDICINAL_PRODUCT_INGREDIENT;

    /* class_default.php:56 */
    public const FIELD_IDENTIFIER = 'identifier';
    public const FIELD_ROLE = 'role';
    public const FIELD_ALLERGENIC_INDICATOR = 'allergenicIndicator';
    public const FIELD_ALLERGENIC_INDICATOR_EXT = '_allergenicIndicator';
    public const FIELD_MANUFACTURER = 'manufacturer';
    public const FIELD_SPECIFIED_SUBSTANCE = 'specifiedSubstance';
    public const FIELD_SUBSTANCE = 'substance';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_ROLE => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_ALLERGENIC_INDICATOR => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The identifier(s) of this Ingredient that are assigned by business processes
     * and/or used to refer to it when a direct URL reference to the resource itself is
     * not appropriate.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier
     */
    #[FHIRIdentifier]
    protected FHIRIdentifier $identifier;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Ingredient role e.g. Active ingredient, excipient.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $role;
    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If the ingredient is a known or suspected allergen.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    #[FHIRBoolean]
    protected FHIRBoolean $allergenicIndicator;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Manufacturer of this Ingredient.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $manufacturer;
    /**
     * An ingredient of a manufactured item or pharmaceutical product.
     *
     * A specified substance that comprises this ingredient.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductIngredient\FHIRMedicinalProductIngredientSpecifiedSubstance>
     */
    #[FHIRMedicinalProductIngredientSpecifiedSubstance]
    protected array $specifiedSubstance;
    /**
     * An ingredient of a manufactured item or pharmaceutical product.
     *
     * The ingredient substance.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductIngredient\FHIRMedicinalProductIngredientSubstance
     */
    #[FHIRMedicinalProductIngredientSubstance]
    protected FHIRMedicinalProductIngredientSubstance $substance;

    /* constructor.php:61 */
    /**
     * FHIRMedicinalProductIngredient Constructor
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $id
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta $meta
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $implicitRules
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $language
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative $text
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRResourceContainer>|iterable<\OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface> $contained
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier $identifier
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $role
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $allergenicIndicator
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $manufacturer
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductIngredient\FHIRMedicinalProductIngredientSpecifiedSubstance> $specifiedSubstance
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductIngredient\FHIRMedicinalProductIngredientSubstance $substance
     * @param null|string[] $fhirComments
     */
    public function __construct(null|string|FHIRIdPrimitive|FHIRId $id = null,
                                null|FHIRMeta $meta = null,
                                null|string|FHIRUriPrimitive|FHIRUri $implicitRules = null,
                                null|string|FHIRCodePrimitive|FHIRCode $language = null,
                                null|FHIRNarrative $text = null,
                                null|iterable $contained = null,
                                null|iterable $extension = null,
                                null|iterable $modifierExtension = null,
                                null|FHIRIdentifier $identifier = null,
                                null|FHIRCodeableConcept $role = null,
                                null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $allergenicIndicator = null,
                                null|iterable $manufacturer = null,
                                null|iterable $specifiedSubstance = null,
                                null|FHIRMedicinalProductIngredientSubstance $substance = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(id: $id,
                            meta: $meta,
                            implicitRules: $implicitRules,
                            language: $language,
                            text: $text,
                            contained: $contained,
                            extension: $extension,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $identifier) {
            $this->setIdentifier($identifier);
        }
        if (null !== $role) {
            $this->setRole($role);
        }
        if (null !== $allergenicIndicator) {
            $this->setAllergenicIndicator($allergenicIndicator);
        }
        if (null !== $manufacturer) {
            $this->setManufacturer(...$manufacturer);
        }
        if (null !== $specifiedSubstance) {
            $this->setSpecifiedSubstance(...$specifiedSubstance);
        }
        if (null !== $substance) {
            $this->setSubstance($substance);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:163 */
    public function _getResourceType(): string
    {
        return static::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The identifier(s) of this Ingredient that are assigned by business processes
     * and/or used to refer to it when a direct URL reference to the resource itself is
     * not appropriate.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier
     */
    public function getIdentifier(): null|FHIRIdentifier
    {
        return $this->identifier ?? null;
    }

    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The identifier(s) of this Ingredient that are assigned by business processes
     * and/or used to refer to it when a direct URL reference to the resource itself is
     * not appropriate.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier $identifier
     * @return static
     */
    public function setIdentifier(null|FHIRIdentifier $identifier): self
    {
        if (null === $identifier) {
            unset($this->identifier);
            return $this;
        }
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Ingredient role e.g. Active ingredient, excipient.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getRole(): null|FHIRCodeableConcept
    {
        return $this->role ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Ingredient role e.g. Active ingredient, excipient.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $role
     * @return static
     */
    public function setRole(null|FHIRCodeableConcept $role): self
    {
        if (null === $role) {
            unset($this->role);
            return $this;
        }
        $this->role = $role;
        return $this;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If the ingredient is a known or suspected allergen.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    public function getAllergenicIndicator(): null|FHIRBoolean
    {
        return $this->allergenicIndicator ?? null;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If the ingredient is a known or suspected allergen.
     *
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $allergenicIndicator
     * @return static
     */
    public function setAllergenicIndicator(null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $allergenicIndicator): self
    {
        if (null === $allergenicIndicator) {
            unset($this->allergenicIndicator);
            return $this;
        }
        if (!($allergenicIndicator instanceof FHIRBoolean)) {
            $allergenicIndicator = new FHIRBoolean(value: $allergenicIndicator);
        }
        $this->allergenicIndicator = $allergenicIndicator;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Manufacturer of this Ingredient.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getManufacturer(): array
    {
        return $this->manufacturer ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getManufacturerIterator(): iterable
    {
        if (!isset($this->manufacturer)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->manufacturer);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Manufacturer of this Ingredient.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $manufacturer
     * @return static
     */
    public function addManufacturer(FHIRReference $manufacturer): self
    {
        if (!isset($this->manufacturer)) {
            $this->manufacturer = [];
        }
        $this->manufacturer[] = $manufacturer;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Manufacturer of this Ingredient.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$manufacturer
     * @return static
     */
    public function setManufacturer(FHIRReference ...$manufacturer): self
    {
        if ([] === $manufacturer) {
            unset($this->manufacturer);
            return $this;
        }
        $this->manufacturer = $manufacturer;
        return $this;
    }

    /**
     * An ingredient of a manufactured item or pharmaceutical product.
     *
     * A specified substance that comprises this ingredient.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductIngredient\FHIRMedicinalProductIngredientSpecifiedSubstance>
     */
    public function getSpecifiedSubstance(): array
    {
        return $this->specifiedSubstance ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductIngredient\FHIRMedicinalProductIngredientSpecifiedSubstance>
     */
    public function getSpecifiedSubstanceIterator(): iterable
    {
        if (!isset($this->specifiedSubstance)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->specifiedSubstance);
    }

    /**
     * An ingredient of a manufactured item or pharmaceutical product.
     *
     * A specified substance that comprises this ingredient.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductIngredient\FHIRMedicinalProductIngredientSpecifiedSubstance $specifiedSubstance
     * @return static
     */
    public function addSpecifiedSubstance(FHIRMedicinalProductIngredientSpecifiedSubstance $specifiedSubstance): self
    {
        if (!isset($this->specifiedSubstance)) {
            $this->specifiedSubstance = [];
        }
        $this->specifiedSubstance[] = $specifiedSubstance;
        return $this;
    }

    /**
     * An ingredient of a manufactured item or pharmaceutical product.
     *
     * A specified substance that comprises this ingredient.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductIngredient\FHIRMedicinalProductIngredientSpecifiedSubstance ...$specifiedSubstance
     * @return static
     */
    public function setSpecifiedSubstance(FHIRMedicinalProductIngredientSpecifiedSubstance ...$specifiedSubstance): self
    {
        if ([] === $specifiedSubstance) {
            unset($this->specifiedSubstance);
            return $this;
        }
        $this->specifiedSubstance = $specifiedSubstance;
        return $this;
    }

    /**
     * An ingredient of a manufactured item or pharmaceutical product.
     *
     * The ingredient substance.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductIngredient\FHIRMedicinalProductIngredientSubstance
     */
    public function getSubstance(): null|FHIRMedicinalProductIngredientSubstance
    {
        return $this->substance ?? null;
    }

    /**
     * An ingredient of a manufactured item or pharmaceutical product.
     *
     * The ingredient substance.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductIngredient\FHIRMedicinalProductIngredientSubstance $substance
     * @return static
     */
    public function setSubstance(null|FHIRMedicinalProductIngredientSubstance $substance): self
    {
        if (null === $substance) {
            unset($this->substance);
            return $this;
        }
        $this->substance = $substance;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param string|\SimpleXMLElement $element
     * @param null|\OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMedicinalProductIngredient $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMedicinalProductIngredient
     * @throws \Exception
     */
    public static function xmlUnserialize(string|\SimpleXMLElement $element,
                                          null|UnserializeConfig $config = null,
                                          null|ResourceTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRMedicinalProductIngredient)) {
            throw new \RuntimeException(sprintf(
                '%s::xmlUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        if (null === $config) {
            $config = (new Version())->getConfig()->getUnserializeConfig();
        }
        if (is_string($element)) {
            $element = new \SimpleXMLElement($element, $config->getLibxmlOpts());
        }
        if (null !== ($ns = $element->getNamespaces()[''] ?? null)) {
            $type->_setSourceXMLNS((string)$ns);
        }
        foreach ($element->children() as $ce) {
            $cen = $ce->getName();
            if (self::FIELD_ID === $cen) {
                $type->setId(FHIRId::xmlUnserialize($ce, $config));
            } else if (self::FIELD_META === $cen) {
                $type->setMeta(FHIRMeta::xmlUnserialize($ce, $config));
            } else if (self::FIELD_IMPLICIT_RULES === $cen) {
                $type->setImplicitRules(FHIRUri::xmlUnserialize($ce, $config));
            } else if (self::FIELD_LANGUAGE === $cen) {
                $type->setLanguage(FHIRCode::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TEXT === $cen) {
                $type->setText(FHIRNarrative::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CONTAINED === $cen) {
                foreach ($ce->children() as $cen) {
                    /** @var \OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface $cn */
                    $cn = VersionTypeMap::mustGetContainedTypeClassnameFromXML($cen);
                    $type->addContained($cn::xmlUnserialize($cen, $config));
                }
            } else if (self::FIELD_EXTENSION === $cen) {
                $type->addExtension(FHIRExtension::xmlUnserialize($ce, $config));
            } else if (self::FIELD_MODIFIER_EXTENSION === $cen) {
                $type->addModifierExtension(FHIRExtension::xmlUnserialize($ce, $config));
            } else if (self::FIELD_IDENTIFIER === $cen) {
                $type->setIdentifier(FHIRIdentifier::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ROLE === $cen) {
                $type->setRole(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ALLERGENIC_INDICATOR === $cen) {
                $type->setAllergenicIndicator(FHIRBoolean::xmlUnserialize($ce, $config));
            } else if (self::FIELD_MANUFACTURER === $cen) {
                $type->addManufacturer(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SPECIFIED_SUBSTANCE === $cen) {
                $type->addSpecifiedSubstance(FHIRMedicinalProductIngredientSpecifiedSubstance::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SUBSTANCE === $cen) {
                $type->setSubstance(FHIRMedicinalProductIngredientSubstance::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            if (isset($type->id)) {
                $type->id->setValue((string)$attributes[self::FIELD_ID]);
            } else {
                $type->setId((string)$attributes[self::FIELD_ID]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_IMPLICIT_RULES])) {
            if (isset($type->implicitRules)) {
                $type->implicitRules->setValue((string)$attributes[self::FIELD_IMPLICIT_RULES]);
            } else {
                $type->setImplicitRules((string)$attributes[self::FIELD_IMPLICIT_RULES]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_IMPLICIT_RULES, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_LANGUAGE])) {
            if (isset($type->language)) {
                $type->language->setValue((string)$attributes[self::FIELD_LANGUAGE]);
            } else {
                $type->setLanguage((string)$attributes[self::FIELD_LANGUAGE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_LANGUAGE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_ALLERGENIC_INDICATOR])) {
            if (isset($type->allergenicIndicator)) {
                $type->allergenicIndicator->setValue((string)$attributes[self::FIELD_ALLERGENIC_INDICATOR]);
            } else {
                $type->setAllergenicIndicator((string)$attributes[self::FIELD_ALLERGENIC_INDICATOR]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_ALLERGENIC_INDICATOR, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        return $type;
    }

    /**
     * @param null|\OpenEMR\FHIR\Encoding\XMLWriter $xw
     * @param null|\OpenEMR\FHIR\Encoding\SerializeConfig $config
     * @return \OpenEMR\FHIR\Encoding\XMLWriter
     */
    public function xmlSerialize(null|XMLWriter $xw = null,
                                 null|SerializeConfig $config = null): XMLWriter
    {
        if (null === $config) {
            $config = (new Version())->getConfig()->getSerializeConfig();
        }
        if (null === $xw) {
            $xw = new XMLWriter($config);
        }
        if (!$xw->isOpen()) {
            $xw->openMemory();
        }
        if (!$xw->isDocStarted()) {
            $docStarted = true;
            $xw->startDocument();
        }
        if (!$xw->isRootOpen()) {
            $rootOpened = true;
            $xw->openRootNode('MedicinalProductIngredient', $this->_getSourceXMLNS());
        }
        if (isset($this->allergenicIndicator) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_ALLERGENIC_INDICATOR]) {
            $xw->writeAttribute(self::FIELD_ALLERGENIC_INDICATOR, $this->allergenicIndicator->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->identifier)) {
            $xw->startElement(self::FIELD_IDENTIFIER);
            $this->identifier->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->role)) {
            $xw->startElement(self::FIELD_ROLE);
            $this->role->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->allergenicIndicator)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_ALLERGENIC_INDICATOR]
                || $this->allergenicIndicator->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_ALLERGENIC_INDICATOR);
            $this->allergenicIndicator->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_ALLERGENIC_INDICATOR]);
            $xw->endElement();
        }
        if (isset($this->manufacturer)) {
            foreach ($this->manufacturer as $v) {
                $xw->startElement(self::FIELD_MANUFACTURER);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->specifiedSubstance)) {
            foreach ($this->specifiedSubstance as $v) {
                $xw->startElement(self::FIELD_SPECIFIED_SUBSTANCE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->substance)) {
            $xw->startElement(self::FIELD_SUBSTANCE);
            $this->substance->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if ($rootOpened ?? false) {
            $xw->endElement();
        }
        if ($docStarted ?? false) {
            $xw->endDocument();
        }
        return $xw;
    }

    /**
     * @param string|\stdClass $decoded
     * @param null|\OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMedicinalProductIngredient $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMedicinalProductIngredient
     * @throws \Exception
     */
    public static function jsonUnserialize(string|\stdClass $decoded,
                                           null|UnserializeConfig $config = null,
                                           null|ResourceTypeInterface $type = null): self
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
        } else if (!($type instanceof FHIRMedicinalProductIngredient)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        if (null === $config) {
            $config = (new Version())->getConfig()->getUnserializeConfig();
        }
        if (is_string($decoded)) {
            $decoded = json_decode(json: $decoded,
                                associative: false,
                                depth: $config->getJSONDecodeMaxDepth(),
                                flags: $config->getJSONDecodeOpts());
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->identifier) || property_exists($decoded, self::FIELD_IDENTIFIER)) {
            if (is_array($decoded->identifier)) {
                $type->setIdentifier(FHIRIdentifier::jsonUnserialize(reset($decoded->identifier), $config));
            } else {
                $type->setIdentifier(FHIRIdentifier::jsonUnserialize($decoded->identifier, $config));
            }
        }
        if (isset($decoded->role) || property_exists($decoded, self::FIELD_ROLE)) {
            if (is_array($decoded->role)) {
                $type->setRole(FHIRCodeableConcept::jsonUnserialize(reset($decoded->role), $config));
            } else {
                $type->setRole(FHIRCodeableConcept::jsonUnserialize($decoded->role, $config));
            }
        }
        if (isset($decoded->allergenicIndicator)
            || isset($decoded->_allergenicIndicator)
            || property_exists($decoded, self::FIELD_ALLERGENIC_INDICATOR)
            || property_exists($decoded, self::FIELD_ALLERGENIC_INDICATOR_EXT)) {
            $v = $decoded->_allergenicIndicator ?? new \stdClass();
            $v->value = $decoded->allergenicIndicator ?? null;
            $type->setAllergenicIndicator(FHIRBoolean::jsonUnserialize($v, $config));
        }
        if (isset($decoded->manufacturer) || property_exists($decoded, self::FIELD_MANUFACTURER)) {
            if (is_object($decoded->manufacturer)) {
                $vals = [$decoded->manufacturer];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_MANUFACTURER, true);
            } else {
                $vals = $decoded->manufacturer;
            }
            foreach($vals as $v) {
                $type->addManufacturer(FHIRReference::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->specifiedSubstance) || property_exists($decoded, self::FIELD_SPECIFIED_SUBSTANCE)) {
            if (is_object($decoded->specifiedSubstance)) {
                $vals = [$decoded->specifiedSubstance];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_SPECIFIED_SUBSTANCE, true);
            } else {
                $vals = $decoded->specifiedSubstance;
            }
            foreach($vals as $v) {
                $type->addSpecifiedSubstance(FHIRMedicinalProductIngredientSpecifiedSubstance::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->substance) || property_exists($decoded, self::FIELD_SUBSTANCE)) {
            if (is_array($decoded->substance)) {
                $type->setSubstance(FHIRMedicinalProductIngredientSubstance::jsonUnserialize(reset($decoded->substance), $config));
            } else {
                $type->setSubstance(FHIRMedicinalProductIngredientSubstance::jsonUnserialize($decoded->substance, $config));
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
        if (isset($this->identifier)) {
            $out->identifier = $this->identifier;
        }
        if (isset($this->role)) {
            $out->role = $this->role;
        }
        if (isset($this->allergenicIndicator)) {
            if (null !== ($val = $this->allergenicIndicator->getValue())) {
                $out->allergenicIndicator = $val;
            }
            if ($this->allergenicIndicator->_nonValueFieldDefined()) {
                $ext = $this->allergenicIndicator->jsonSerialize();
                unset($ext->value);
                $out->_allergenicIndicator = $ext;
            }
        }
        if (isset($this->manufacturer) && [] !== $this->manufacturer) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_MANUFACTURER) && 1 === count($this->manufacturer)) {
                $out->manufacturer = $this->manufacturer[0];
            } else {
                $out->manufacturer = $this->manufacturer;
            }
        }
        if (isset($this->specifiedSubstance) && [] !== $this->specifiedSubstance) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_SPECIFIED_SUBSTANCE) && 1 === count($this->specifiedSubstance)) {
                $out->specifiedSubstance = $this->specifiedSubstance[0];
            } else {
                $out->specifiedSubstance = $this->specifiedSubstance;
            }
        }
        if (isset($this->substance)) {
            $out->substance = $this->substance;
        }
        $out->resourceType = $this->_getResourceType();
        return $out;
    }
}
