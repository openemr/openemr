<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRNutritionOrder;

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
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * A request to supply a diet, formula feeding (enteral) or oral nutritional
 * supplement to a patient/resident.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRNutritionOrderEnteralFormula extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_NUTRITION_ORDER_DOT_ENTERAL_FORMULA;

    /* class_default.php:56 */
    public const FIELD_BASE_FORMULA_TYPE = 'baseFormulaType';
    public const FIELD_BASE_FORMULA_PRODUCT_NAME = 'baseFormulaProductName';
    public const FIELD_BASE_FORMULA_PRODUCT_NAME_EXT = '_baseFormulaProductName';
    public const FIELD_ADDITIVE_TYPE = 'additiveType';
    public const FIELD_ADDITIVE_PRODUCT_NAME = 'additiveProductName';
    public const FIELD_ADDITIVE_PRODUCT_NAME_EXT = '_additiveProductName';
    public const FIELD_CALORIC_DENSITY = 'caloricDensity';
    public const FIELD_ROUTEOF_ADMINISTRATION = 'routeofAdministration';
    public const FIELD_ADMINISTRATION = 'administration';
    public const FIELD_MAX_VOLUME_TO_DELIVER = 'maxVolumeToDeliver';
    public const FIELD_ADMINISTRATION_INSTRUCTION = 'administrationInstruction';
    public const FIELD_ADMINISTRATION_INSTRUCTION_EXT = '_administrationInstruction';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_BASE_FORMULA_PRODUCT_NAME => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_ADDITIVE_PRODUCT_NAME => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_ADMINISTRATION_INSTRUCTION => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The type of enteral or infant formula such as an adult standard formula with
     * fiber or a soy-based infant formula.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $baseFormulaType;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The product or brand name of the enteral or infant formula product such as "ACME
     * Adult Standard Formula".
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $baseFormulaProductName;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates the type of modular component such as protein, carbohydrate, fat or
     * fiber to be provided in addition to or mixed with the base formula.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $additiveType;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The product or brand name of the type of modular component to be added to the
     * formula.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $additiveProductName;
    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The amount of energy (calories) that the formula should provide per specified
     * volume, typically per mL or fluid oz. For example, an infant may require a
     * formula that provides 24 calories per fluid ounce or an adult may require an
     * enteral formula that provides 1.5 calorie/mL.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity
     */
    #[FHIRQuantity]
    protected FHIRQuantity $caloricDensity;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The route or physiological path of administration into the patient's
     * gastrointestinal tract for purposes of providing the formula feeding, e.g.
     * nasogastric tube.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $routeofAdministration;
    /**
     * A request to supply a diet, formula feeding (enteral) or oral nutritional
     * supplement to a patient/resident.
     *
     * Formula administration instructions as structured data. This repeating structure
     * allows for changing the administration rate or volume over time for both bolus
     * and continuous feeding. An example of this would be an instruction to increase
     * the rate of continuous feeding every 2 hours.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRNutritionOrder\FHIRNutritionOrderAdministration>
     */
    #[FHIRNutritionOrderAdministration]
    protected array $administration;
    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The maximum total quantity of formula that may be administered to a subject over
     * the period of time, e.g. 1440 mL over 24 hours.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity
     */
    #[FHIRQuantity]
    protected FHIRQuantity $maxVolumeToDeliver;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Free text formula administration, feeding instructions or additional
     * instructions or information.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $administrationInstruction;

    /* constructor.php:61 */
    /**
     * FHIRNutritionOrderEnteralFormula Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $baseFormulaType
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $baseFormulaProductName
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $additiveType
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $additiveProductName
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity $caloricDensity
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $routeofAdministration
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRNutritionOrder\FHIRNutritionOrderAdministration> $administration
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity $maxVolumeToDeliver
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $administrationInstruction
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|FHIRCodeableConcept $baseFormulaType = null,
                                null|string|FHIRStringPrimitive|FHIRString $baseFormulaProductName = null,
                                null|FHIRCodeableConcept $additiveType = null,
                                null|string|FHIRStringPrimitive|FHIRString $additiveProductName = null,
                                null|FHIRQuantity $caloricDensity = null,
                                null|FHIRCodeableConcept $routeofAdministration = null,
                                null|iterable $administration = null,
                                null|FHIRQuantity $maxVolumeToDeliver = null,
                                null|string|FHIRStringPrimitive|FHIRString $administrationInstruction = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $baseFormulaType) {
            $this->setBaseFormulaType($baseFormulaType);
        }
        if (null !== $baseFormulaProductName) {
            $this->setBaseFormulaProductName($baseFormulaProductName);
        }
        if (null !== $additiveType) {
            $this->setAdditiveType($additiveType);
        }
        if (null !== $additiveProductName) {
            $this->setAdditiveProductName($additiveProductName);
        }
        if (null !== $caloricDensity) {
            $this->setCaloricDensity($caloricDensity);
        }
        if (null !== $routeofAdministration) {
            $this->setRouteofAdministration($routeofAdministration);
        }
        if (null !== $administration) {
            $this->setAdministration(...$administration);
        }
        if (null !== $maxVolumeToDeliver) {
            $this->setMaxVolumeToDeliver($maxVolumeToDeliver);
        }
        if (null !== $administrationInstruction) {
            $this->setAdministrationInstruction($administrationInstruction);
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
     * The type of enteral or infant formula such as an adult standard formula with
     * fiber or a soy-based infant formula.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getBaseFormulaType(): null|FHIRCodeableConcept
    {
        return $this->baseFormulaType ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The type of enteral or infant formula such as an adult standard formula with
     * fiber or a soy-based infant formula.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $baseFormulaType
     * @return static
     */
    public function setBaseFormulaType(null|FHIRCodeableConcept $baseFormulaType): self
    {
        if (null === $baseFormulaType) {
            unset($this->baseFormulaType);
            return $this;
        }
        $this->baseFormulaType = $baseFormulaType;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The product or brand name of the enteral or infant formula product such as "ACME
     * Adult Standard Formula".
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getBaseFormulaProductName(): null|FHIRString
    {
        return $this->baseFormulaProductName ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The product or brand name of the enteral or infant formula product such as "ACME
     * Adult Standard Formula".
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $baseFormulaProductName
     * @return static
     */
    public function setBaseFormulaProductName(null|string|FHIRStringPrimitive|FHIRString $baseFormulaProductName): self
    {
        if (null === $baseFormulaProductName) {
            unset($this->baseFormulaProductName);
            return $this;
        }
        if (!($baseFormulaProductName instanceof FHIRString)) {
            $baseFormulaProductName = new FHIRString(value: $baseFormulaProductName);
        }
        $this->baseFormulaProductName = $baseFormulaProductName;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates the type of modular component such as protein, carbohydrate, fat or
     * fiber to be provided in addition to or mixed with the base formula.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getAdditiveType(): null|FHIRCodeableConcept
    {
        return $this->additiveType ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates the type of modular component such as protein, carbohydrate, fat or
     * fiber to be provided in addition to or mixed with the base formula.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $additiveType
     * @return static
     */
    public function setAdditiveType(null|FHIRCodeableConcept $additiveType): self
    {
        if (null === $additiveType) {
            unset($this->additiveType);
            return $this;
        }
        $this->additiveType = $additiveType;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The product or brand name of the type of modular component to be added to the
     * formula.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getAdditiveProductName(): null|FHIRString
    {
        return $this->additiveProductName ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The product or brand name of the type of modular component to be added to the
     * formula.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $additiveProductName
     * @return static
     */
    public function setAdditiveProductName(null|string|FHIRStringPrimitive|FHIRString $additiveProductName): self
    {
        if (null === $additiveProductName) {
            unset($this->additiveProductName);
            return $this;
        }
        if (!($additiveProductName instanceof FHIRString)) {
            $additiveProductName = new FHIRString(value: $additiveProductName);
        }
        $this->additiveProductName = $additiveProductName;
        return $this;
    }

    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The amount of energy (calories) that the formula should provide per specified
     * volume, typically per mL or fluid oz. For example, an infant may require a
     * formula that provides 24 calories per fluid ounce or an adult may require an
     * enteral formula that provides 1.5 calorie/mL.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity
     */
    public function getCaloricDensity(): null|FHIRQuantity
    {
        return $this->caloricDensity ?? null;
    }

    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The amount of energy (calories) that the formula should provide per specified
     * volume, typically per mL or fluid oz. For example, an infant may require a
     * formula that provides 24 calories per fluid ounce or an adult may require an
     * enteral formula that provides 1.5 calorie/mL.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity $caloricDensity
     * @return static
     */
    public function setCaloricDensity(null|FHIRQuantity $caloricDensity): self
    {
        if (null === $caloricDensity) {
            unset($this->caloricDensity);
            return $this;
        }
        $this->caloricDensity = $caloricDensity;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The route or physiological path of administration into the patient's
     * gastrointestinal tract for purposes of providing the formula feeding, e.g.
     * nasogastric tube.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getRouteofAdministration(): null|FHIRCodeableConcept
    {
        return $this->routeofAdministration ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The route or physiological path of administration into the patient's
     * gastrointestinal tract for purposes of providing the formula feeding, e.g.
     * nasogastric tube.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $routeofAdministration
     * @return static
     */
    public function setRouteofAdministration(null|FHIRCodeableConcept $routeofAdministration): self
    {
        if (null === $routeofAdministration) {
            unset($this->routeofAdministration);
            return $this;
        }
        $this->routeofAdministration = $routeofAdministration;
        return $this;
    }

    /**
     * A request to supply a diet, formula feeding (enteral) or oral nutritional
     * supplement to a patient/resident.
     *
     * Formula administration instructions as structured data. This repeating structure
     * allows for changing the administration rate or volume over time for both bolus
     * and continuous feeding. An example of this would be an instruction to increase
     * the rate of continuous feeding every 2 hours.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRNutritionOrder\FHIRNutritionOrderAdministration>
     */
    public function getAdministration(): array
    {
        return $this->administration ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRNutritionOrder\FHIRNutritionOrderAdministration>
     */
    public function getAdministrationIterator(): iterable
    {
        if (!isset($this->administration)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->administration);
    }

    /**
     * A request to supply a diet, formula feeding (enteral) or oral nutritional
     * supplement to a patient/resident.
     *
     * Formula administration instructions as structured data. This repeating structure
     * allows for changing the administration rate or volume over time for both bolus
     * and continuous feeding. An example of this would be an instruction to increase
     * the rate of continuous feeding every 2 hours.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRNutritionOrder\FHIRNutritionOrderAdministration $administration
     * @return static
     */
    public function addAdministration(FHIRNutritionOrderAdministration $administration): self
    {
        if (!isset($this->administration)) {
            $this->administration = [];
        }
        $this->administration[] = $administration;
        return $this;
    }

    /**
     * A request to supply a diet, formula feeding (enteral) or oral nutritional
     * supplement to a patient/resident.
     *
     * Formula administration instructions as structured data. This repeating structure
     * allows for changing the administration rate or volume over time for both bolus
     * and continuous feeding. An example of this would be an instruction to increase
     * the rate of continuous feeding every 2 hours.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRNutritionOrder\FHIRNutritionOrderAdministration ...$administration
     * @return static
     */
    public function setAdministration(FHIRNutritionOrderAdministration ...$administration): self
    {
        if ([] === $administration) {
            unset($this->administration);
            return $this;
        }
        $this->administration = $administration;
        return $this;
    }

    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The maximum total quantity of formula that may be administered to a subject over
     * the period of time, e.g. 1440 mL over 24 hours.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity
     */
    public function getMaxVolumeToDeliver(): null|FHIRQuantity
    {
        return $this->maxVolumeToDeliver ?? null;
    }

    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The maximum total quantity of formula that may be administered to a subject over
     * the period of time, e.g. 1440 mL over 24 hours.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity $maxVolumeToDeliver
     * @return static
     */
    public function setMaxVolumeToDeliver(null|FHIRQuantity $maxVolumeToDeliver): self
    {
        if (null === $maxVolumeToDeliver) {
            unset($this->maxVolumeToDeliver);
            return $this;
        }
        $this->maxVolumeToDeliver = $maxVolumeToDeliver;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Free text formula administration, feeding instructions or additional
     * instructions or information.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getAdministrationInstruction(): null|FHIRString
    {
        return $this->administrationInstruction ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Free text formula administration, feeding instructions or additional
     * instructions or information.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $administrationInstruction
     * @return static
     */
    public function setAdministrationInstruction(null|string|FHIRStringPrimitive|FHIRString $administrationInstruction): self
    {
        if (null === $administrationInstruction) {
            unset($this->administrationInstruction);
            return $this;
        }
        if (!($administrationInstruction instanceof FHIRString)) {
            $administrationInstruction = new FHIRString(value: $administrationInstruction);
        }
        $this->administrationInstruction = $administrationInstruction;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRNutritionOrder\FHIRNutritionOrderEnteralFormula $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRNutritionOrder\FHIRNutritionOrderEnteralFormula
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRNutritionOrderEnteralFormula)) {
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
            } else if (self::FIELD_BASE_FORMULA_TYPE === $cen) {
                $type->setBaseFormulaType(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_BASE_FORMULA_PRODUCT_NAME === $cen) {
                $type->setBaseFormulaProductName(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ADDITIVE_TYPE === $cen) {
                $type->setAdditiveType(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ADDITIVE_PRODUCT_NAME === $cen) {
                $type->setAdditiveProductName(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CALORIC_DENSITY === $cen) {
                $type->setCaloricDensity(FHIRQuantity::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ROUTEOF_ADMINISTRATION === $cen) {
                $type->setRouteofAdministration(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ADMINISTRATION === $cen) {
                $type->addAdministration(FHIRNutritionOrderAdministration::xmlUnserialize($ce, $config));
            } else if (self::FIELD_MAX_VOLUME_TO_DELIVER === $cen) {
                $type->setMaxVolumeToDeliver(FHIRQuantity::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ADMINISTRATION_INSTRUCTION === $cen) {
                $type->setAdministrationInstruction(FHIRString::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_BASE_FORMULA_PRODUCT_NAME])) {
            if (isset($type->baseFormulaProductName)) {
                $type->baseFormulaProductName->setValue((string)$attributes[self::FIELD_BASE_FORMULA_PRODUCT_NAME]);
            } else {
                $type->setBaseFormulaProductName((string)$attributes[self::FIELD_BASE_FORMULA_PRODUCT_NAME]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_BASE_FORMULA_PRODUCT_NAME, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_ADDITIVE_PRODUCT_NAME])) {
            if (isset($type->additiveProductName)) {
                $type->additiveProductName->setValue((string)$attributes[self::FIELD_ADDITIVE_PRODUCT_NAME]);
            } else {
                $type->setAdditiveProductName((string)$attributes[self::FIELD_ADDITIVE_PRODUCT_NAME]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_ADDITIVE_PRODUCT_NAME, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_ADMINISTRATION_INSTRUCTION])) {
            if (isset($type->administrationInstruction)) {
                $type->administrationInstruction->setValue((string)$attributes[self::FIELD_ADMINISTRATION_INSTRUCTION]);
            } else {
                $type->setAdministrationInstruction((string)$attributes[self::FIELD_ADMINISTRATION_INSTRUCTION]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_ADMINISTRATION_INSTRUCTION, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->baseFormulaProductName) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_BASE_FORMULA_PRODUCT_NAME]) {
            $xw->writeAttribute(self::FIELD_BASE_FORMULA_PRODUCT_NAME, $this->baseFormulaProductName->_getValueAsString());
        }
        if (isset($this->additiveProductName) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_ADDITIVE_PRODUCT_NAME]) {
            $xw->writeAttribute(self::FIELD_ADDITIVE_PRODUCT_NAME, $this->additiveProductName->_getValueAsString());
        }
        if (isset($this->administrationInstruction) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_ADMINISTRATION_INSTRUCTION]) {
            $xw->writeAttribute(self::FIELD_ADMINISTRATION_INSTRUCTION, $this->administrationInstruction->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->baseFormulaType)) {
            $xw->startElement(self::FIELD_BASE_FORMULA_TYPE);
            $this->baseFormulaType->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->baseFormulaProductName)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_BASE_FORMULA_PRODUCT_NAME]
                || $this->baseFormulaProductName->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_BASE_FORMULA_PRODUCT_NAME);
            $this->baseFormulaProductName->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_BASE_FORMULA_PRODUCT_NAME]);
            $xw->endElement();
        }
        if (isset($this->additiveType)) {
            $xw->startElement(self::FIELD_ADDITIVE_TYPE);
            $this->additiveType->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->additiveProductName)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_ADDITIVE_PRODUCT_NAME]
                || $this->additiveProductName->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_ADDITIVE_PRODUCT_NAME);
            $this->additiveProductName->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_ADDITIVE_PRODUCT_NAME]);
            $xw->endElement();
        }
        if (isset($this->caloricDensity)) {
            $xw->startElement(self::FIELD_CALORIC_DENSITY);
            $this->caloricDensity->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->routeofAdministration)) {
            $xw->startElement(self::FIELD_ROUTEOF_ADMINISTRATION);
            $this->routeofAdministration->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->administration)) {
            foreach ($this->administration as $v) {
                $xw->startElement(self::FIELD_ADMINISTRATION);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->maxVolumeToDeliver)) {
            $xw->startElement(self::FIELD_MAX_VOLUME_TO_DELIVER);
            $this->maxVolumeToDeliver->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->administrationInstruction)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_ADMINISTRATION_INSTRUCTION]
                || $this->administrationInstruction->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_ADMINISTRATION_INSTRUCTION);
            $this->administrationInstruction->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_ADMINISTRATION_INSTRUCTION]);
            $xw->endElement();
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRNutritionOrder\FHIRNutritionOrderEnteralFormula $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRNutritionOrder\FHIRNutritionOrderEnteralFormula
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
        } else if (!($type instanceof FHIRNutritionOrderEnteralFormula)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->baseFormulaType) || property_exists($decoded, self::FIELD_BASE_FORMULA_TYPE)) {
            if (is_array($decoded->baseFormulaType)) {
                $type->setBaseFormulaType(FHIRCodeableConcept::jsonUnserialize(reset($decoded->baseFormulaType), $config));
            } else {
                $type->setBaseFormulaType(FHIRCodeableConcept::jsonUnserialize($decoded->baseFormulaType, $config));
            }
        }
        if (isset($decoded->baseFormulaProductName)
            || isset($decoded->_baseFormulaProductName)
            || property_exists($decoded, self::FIELD_BASE_FORMULA_PRODUCT_NAME)
            || property_exists($decoded, self::FIELD_BASE_FORMULA_PRODUCT_NAME_EXT)) {
            $v = $decoded->_baseFormulaProductName ?? new \stdClass();
            $v->value = $decoded->baseFormulaProductName ?? null;
            $type->setBaseFormulaProductName(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->additiveType) || property_exists($decoded, self::FIELD_ADDITIVE_TYPE)) {
            if (is_array($decoded->additiveType)) {
                $type->setAdditiveType(FHIRCodeableConcept::jsonUnserialize(reset($decoded->additiveType), $config));
            } else {
                $type->setAdditiveType(FHIRCodeableConcept::jsonUnserialize($decoded->additiveType, $config));
            }
        }
        if (isset($decoded->additiveProductName)
            || isset($decoded->_additiveProductName)
            || property_exists($decoded, self::FIELD_ADDITIVE_PRODUCT_NAME)
            || property_exists($decoded, self::FIELD_ADDITIVE_PRODUCT_NAME_EXT)) {
            $v = $decoded->_additiveProductName ?? new \stdClass();
            $v->value = $decoded->additiveProductName ?? null;
            $type->setAdditiveProductName(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->caloricDensity) || property_exists($decoded, self::FIELD_CALORIC_DENSITY)) {
            if (is_array($decoded->caloricDensity)) {
                $type->setCaloricDensity(FHIRQuantity::jsonUnserialize(reset($decoded->caloricDensity), $config));
            } else {
                $type->setCaloricDensity(FHIRQuantity::jsonUnserialize($decoded->caloricDensity, $config));
            }
        }
        if (isset($decoded->routeofAdministration) || property_exists($decoded, self::FIELD_ROUTEOF_ADMINISTRATION)) {
            if (is_array($decoded->routeofAdministration)) {
                $type->setRouteofAdministration(FHIRCodeableConcept::jsonUnserialize(reset($decoded->routeofAdministration), $config));
            } else {
                $type->setRouteofAdministration(FHIRCodeableConcept::jsonUnserialize($decoded->routeofAdministration, $config));
            }
        }
        if (isset($decoded->administration) || property_exists($decoded, self::FIELD_ADMINISTRATION)) {
            if (is_object($decoded->administration)) {
                $vals = [$decoded->administration];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_ADMINISTRATION, true);
            } else {
                $vals = $decoded->administration;
            }
            foreach($vals as $v) {
                $type->addAdministration(FHIRNutritionOrderAdministration::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->maxVolumeToDeliver) || property_exists($decoded, self::FIELD_MAX_VOLUME_TO_DELIVER)) {
            if (is_array($decoded->maxVolumeToDeliver)) {
                $type->setMaxVolumeToDeliver(FHIRQuantity::jsonUnserialize(reset($decoded->maxVolumeToDeliver), $config));
            } else {
                $type->setMaxVolumeToDeliver(FHIRQuantity::jsonUnserialize($decoded->maxVolumeToDeliver, $config));
            }
        }
        if (isset($decoded->administrationInstruction)
            || isset($decoded->_administrationInstruction)
            || property_exists($decoded, self::FIELD_ADMINISTRATION_INSTRUCTION)
            || property_exists($decoded, self::FIELD_ADMINISTRATION_INSTRUCTION_EXT)) {
            $v = $decoded->_administrationInstruction ?? new \stdClass();
            $v->value = $decoded->administrationInstruction ?? null;
            $type->setAdministrationInstruction(FHIRString::jsonUnserialize($v, $config));
        }
        return $type;
    }

    /**
     * @return \stdClass
     */
    public function jsonSerialize(): mixed
    {
        $out = parent::jsonSerialize();
        if (isset($this->baseFormulaType)) {
            $out->baseFormulaType = $this->baseFormulaType;
        }
        if (isset($this->baseFormulaProductName)) {
            if (null !== ($val = $this->baseFormulaProductName->getValue())) {
                $out->baseFormulaProductName = $val;
            }
            if ($this->baseFormulaProductName->_nonValueFieldDefined()) {
                $ext = $this->baseFormulaProductName->jsonSerialize();
                unset($ext->value);
                $out->_baseFormulaProductName = $ext;
            }
        }
        if (isset($this->additiveType)) {
            $out->additiveType = $this->additiveType;
        }
        if (isset($this->additiveProductName)) {
            if (null !== ($val = $this->additiveProductName->getValue())) {
                $out->additiveProductName = $val;
            }
            if ($this->additiveProductName->_nonValueFieldDefined()) {
                $ext = $this->additiveProductName->jsonSerialize();
                unset($ext->value);
                $out->_additiveProductName = $ext;
            }
        }
        if (isset($this->caloricDensity)) {
            $out->caloricDensity = $this->caloricDensity;
        }
        if (isset($this->routeofAdministration)) {
            $out->routeofAdministration = $this->routeofAdministration;
        }
        if (isset($this->administration) && [] !== $this->administration) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_ADMINISTRATION) && 1 === count($this->administration)) {
                $out->administration = $this->administration[0];
            } else {
                $out->administration = $this->administration;
            }
        }
        if (isset($this->maxVolumeToDeliver)) {
            $out->maxVolumeToDeliver = $this->maxVolumeToDeliver;
        }
        if (isset($this->administrationInstruction)) {
            if (null !== ($val = $this->administrationInstruction->getValue())) {
                $out->administrationInstruction = $val;
            }
            if ($this->administrationInstruction->_nonValueFieldDefined()) {
                $ext = $this->administrationInstruction->jsonSerialize();
                unset($ext->value);
                $out->_administrationInstruction = $ext;
            }
        }
        return $out;
    }
}
