<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification;

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
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * The detailed description of a substance, typically at a level beyond what is
 * used for prescribing.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRSubstanceSpecificationProperty extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_SUBSTANCE_SPECIFICATION_DOT_PROPERTY;

    /* class_default.php:56 */
    public const FIELD_CATEGORY = 'category';
    public const FIELD_CODE = 'code';
    public const FIELD_PARAMETERS = 'parameters';
    public const FIELD_PARAMETERS_EXT = '_parameters';
    public const FIELD_DEFINING_SUBSTANCE_REFERENCE = 'definingSubstanceReference';
    public const FIELD_DEFINING_SUBSTANCE_CODEABLE_CONCEPT = 'definingSubstanceCodeableConcept';
    public const FIELD_AMOUNT_QUANTITY = 'amountQuantity';
    public const FIELD_AMOUNT_STRING = 'amountString';
    public const FIELD_AMOUNT_STRING_EXT = '_amountString';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_PARAMETERS => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_AMOUNT_STRING => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A category for this property, e.g. Physical, Chemical, Enzymatic.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $category;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Property type e.g. viscosity, pH, isoelectric point.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $code;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Parameters that were used in the measurement of a property (e.g. for viscosity:
     * measured at 20C with a pH of 7.1).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $parameters;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A substance upon which a defining property depends (e.g. for solubility: in
     * water, in alcohol). (choose any one of definingSubstance*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $definingSubstanceReference;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A substance upon which a defining property depends (e.g. for solubility: in
     * water, in alcohol). (choose any one of definingSubstance*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $definingSubstanceCodeableConcept;
    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Quantitative value for this property. (choose any one of amount*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity
     */
    #[FHIRQuantity]
    protected FHIRQuantity $amountQuantity;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Quantitative value for this property. (choose any one of amount*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $amountString;

    /* constructor.php:61 */
    /**
     * FHIRSubstanceSpecificationProperty Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $category
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $code
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $parameters
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $definingSubstanceReference
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $definingSubstanceCodeableConcept
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity $amountQuantity
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $amountString
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|FHIRCodeableConcept $category = null,
                                null|FHIRCodeableConcept $code = null,
                                null|string|FHIRStringPrimitive|FHIRString $parameters = null,
                                null|FHIRReference $definingSubstanceReference = null,
                                null|FHIRCodeableConcept $definingSubstanceCodeableConcept = null,
                                null|FHIRQuantity $amountQuantity = null,
                                null|string|FHIRStringPrimitive|FHIRString $amountString = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $category) {
            $this->setCategory($category);
        }
        if (null !== $code) {
            $this->setCode($code);
        }
        if (null !== $parameters) {
            $this->setParameters($parameters);
        }
        if (null !== $definingSubstanceReference) {
            $this->setDefiningSubstanceReference($definingSubstanceReference);
        }
        if (null !== $definingSubstanceCodeableConcept) {
            $this->setDefiningSubstanceCodeableConcept($definingSubstanceCodeableConcept);
        }
        if (null !== $amountQuantity) {
            $this->setAmountQuantity($amountQuantity);
        }
        if (null !== $amountString) {
            $this->setAmountString($amountString);
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
     * A category for this property, e.g. Physical, Chemical, Enzymatic.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getCategory(): null|FHIRCodeableConcept
    {
        return $this->category ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A category for this property, e.g. Physical, Chemical, Enzymatic.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $category
     * @return static
     */
    public function setCategory(null|FHIRCodeableConcept $category): self
    {
        if (null === $category) {
            unset($this->category);
            return $this;
        }
        $this->category = $category;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Property type e.g. viscosity, pH, isoelectric point.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getCode(): null|FHIRCodeableConcept
    {
        return $this->code ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Property type e.g. viscosity, pH, isoelectric point.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $code
     * @return static
     */
    public function setCode(null|FHIRCodeableConcept $code): self
    {
        if (null === $code) {
            unset($this->code);
            return $this;
        }
        $this->code = $code;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Parameters that were used in the measurement of a property (e.g. for viscosity:
     * measured at 20C with a pH of 7.1).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getParameters(): null|FHIRString
    {
        return $this->parameters ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Parameters that were used in the measurement of a property (e.g. for viscosity:
     * measured at 20C with a pH of 7.1).
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $parameters
     * @return static
     */
    public function setParameters(null|string|FHIRStringPrimitive|FHIRString $parameters): self
    {
        if (null === $parameters) {
            unset($this->parameters);
            return $this;
        }
        if (!($parameters instanceof FHIRString)) {
            $parameters = new FHIRString(value: $parameters);
        }
        $this->parameters = $parameters;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A substance upon which a defining property depends (e.g. for solubility: in
     * water, in alcohol). (choose any one of definingSubstance*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getDefiningSubstanceReference(): null|FHIRReference
    {
        return $this->definingSubstanceReference ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A substance upon which a defining property depends (e.g. for solubility: in
     * water, in alcohol). (choose any one of definingSubstance*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $definingSubstanceReference
     * @return static
     */
    public function setDefiningSubstanceReference(null|FHIRReference $definingSubstanceReference): self
    {
        if (null === $definingSubstanceReference) {
            unset($this->definingSubstanceReference);
            return $this;
        }
        $this->definingSubstanceReference = $definingSubstanceReference;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A substance upon which a defining property depends (e.g. for solubility: in
     * water, in alcohol). (choose any one of definingSubstance*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getDefiningSubstanceCodeableConcept(): null|FHIRCodeableConcept
    {
        return $this->definingSubstanceCodeableConcept ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A substance upon which a defining property depends (e.g. for solubility: in
     * water, in alcohol). (choose any one of definingSubstance*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $definingSubstanceCodeableConcept
     * @return static
     */
    public function setDefiningSubstanceCodeableConcept(null|FHIRCodeableConcept $definingSubstanceCodeableConcept): self
    {
        if (null === $definingSubstanceCodeableConcept) {
            unset($this->definingSubstanceCodeableConcept);
            return $this;
        }
        $this->definingSubstanceCodeableConcept = $definingSubstanceCodeableConcept;
        return $this;
    }

    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Quantitative value for this property. (choose any one of amount*, but only one)
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
     * Quantitative value for this property. (choose any one of amount*, but only one)
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
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Quantitative value for this property. (choose any one of amount*, but only one)
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
     * Quantitative value for this property. (choose any one of amount*, but only one)
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

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationProperty $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationProperty
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRSubstanceSpecificationProperty)) {
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
            } else if (self::FIELD_CATEGORY === $cen) {
                $type->setCategory(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CODE === $cen) {
                $type->setCode(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PARAMETERS === $cen) {
                $type->setParameters(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DEFINING_SUBSTANCE_REFERENCE === $cen) {
                $type->setDefiningSubstanceReference(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DEFINING_SUBSTANCE_CODEABLE_CONCEPT === $cen) {
                $type->setDefiningSubstanceCodeableConcept(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_AMOUNT_QUANTITY === $cen) {
                $type->setAmountQuantity(FHIRQuantity::xmlUnserialize($ce, $config));
            } else if (self::FIELD_AMOUNT_STRING === $cen) {
                $type->setAmountString(FHIRString::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_PARAMETERS])) {
            if (isset($type->parameters)) {
                $type->parameters->setValue((string)$attributes[self::FIELD_PARAMETERS]);
            } else {
                $type->setParameters((string)$attributes[self::FIELD_PARAMETERS]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_PARAMETERS, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_AMOUNT_STRING])) {
            if (isset($type->amountString)) {
                $type->amountString->setValue((string)$attributes[self::FIELD_AMOUNT_STRING]);
            } else {
                $type->setAmountString((string)$attributes[self::FIELD_AMOUNT_STRING]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_AMOUNT_STRING, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->parameters) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_PARAMETERS]) {
            $xw->writeAttribute(self::FIELD_PARAMETERS, $this->parameters->_getValueAsString());
        }
        if (isset($this->amountString) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_AMOUNT_STRING]) {
            $xw->writeAttribute(self::FIELD_AMOUNT_STRING, $this->amountString->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->category)) {
            $xw->startElement(self::FIELD_CATEGORY);
            $this->category->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->code)) {
            $xw->startElement(self::FIELD_CODE);
            $this->code->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->parameters)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_PARAMETERS]
                || $this->parameters->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_PARAMETERS);
            $this->parameters->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_PARAMETERS]);
            $xw->endElement();
        }
        if (isset($this->definingSubstanceReference)) {
            $xw->startElement(self::FIELD_DEFINING_SUBSTANCE_REFERENCE);
            $this->definingSubstanceReference->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->definingSubstanceCodeableConcept)) {
            $xw->startElement(self::FIELD_DEFINING_SUBSTANCE_CODEABLE_CONCEPT);
            $this->definingSubstanceCodeableConcept->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->amountQuantity)) {
            $xw->startElement(self::FIELD_AMOUNT_QUANTITY);
            $this->amountQuantity->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->amountString)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_AMOUNT_STRING]
                || $this->amountString->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_AMOUNT_STRING);
            $this->amountString->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_AMOUNT_STRING]);
            $xw->endElement();
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationProperty $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationProperty
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
        } else if (!($type instanceof FHIRSubstanceSpecificationProperty)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->category) || property_exists($decoded, self::FIELD_CATEGORY)) {
            if (is_array($decoded->category)) {
                $type->setCategory(FHIRCodeableConcept::jsonUnserialize(reset($decoded->category), $config));
            } else {
                $type->setCategory(FHIRCodeableConcept::jsonUnserialize($decoded->category, $config));
            }
        }
        if (isset($decoded->code) || property_exists($decoded, self::FIELD_CODE)) {
            if (is_array($decoded->code)) {
                $type->setCode(FHIRCodeableConcept::jsonUnserialize(reset($decoded->code), $config));
            } else {
                $type->setCode(FHIRCodeableConcept::jsonUnserialize($decoded->code, $config));
            }
        }
        if (isset($decoded->parameters)
            || isset($decoded->_parameters)
            || property_exists($decoded, self::FIELD_PARAMETERS)
            || property_exists($decoded, self::FIELD_PARAMETERS_EXT)) {
            $v = $decoded->_parameters ?? new \stdClass();
            $v->value = $decoded->parameters ?? null;
            $type->setParameters(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->definingSubstanceReference) || property_exists($decoded, self::FIELD_DEFINING_SUBSTANCE_REFERENCE)) {
            if (is_array($decoded->definingSubstanceReference)) {
                $type->setDefiningSubstanceReference(FHIRReference::jsonUnserialize(reset($decoded->definingSubstanceReference), $config));
            } else {
                $type->setDefiningSubstanceReference(FHIRReference::jsonUnserialize($decoded->definingSubstanceReference, $config));
            }
        }
        if (isset($decoded->definingSubstanceCodeableConcept) || property_exists($decoded, self::FIELD_DEFINING_SUBSTANCE_CODEABLE_CONCEPT)) {
            if (is_array($decoded->definingSubstanceCodeableConcept)) {
                $type->setDefiningSubstanceCodeableConcept(FHIRCodeableConcept::jsonUnserialize(reset($decoded->definingSubstanceCodeableConcept), $config));
            } else {
                $type->setDefiningSubstanceCodeableConcept(FHIRCodeableConcept::jsonUnserialize($decoded->definingSubstanceCodeableConcept, $config));
            }
        }
        if (isset($decoded->amountQuantity) || property_exists($decoded, self::FIELD_AMOUNT_QUANTITY)) {
            if (is_array($decoded->amountQuantity)) {
                $type->setAmountQuantity(FHIRQuantity::jsonUnserialize(reset($decoded->amountQuantity), $config));
            } else {
                $type->setAmountQuantity(FHIRQuantity::jsonUnserialize($decoded->amountQuantity, $config));
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
        return $type;
    }

    /**
     * @return \stdClass
     */
    public function jsonSerialize(): mixed
    {
        $out = parent::jsonSerialize();
        if (isset($this->category)) {
            $out->category = $this->category;
        }
        if (isset($this->code)) {
            $out->code = $this->code;
        }
        if (isset($this->parameters)) {
            if (null !== ($val = $this->parameters->getValue())) {
                $out->parameters = $val;
            }
            if ($this->parameters->_nonValueFieldDefined()) {
                $ext = $this->parameters->jsonSerialize();
                unset($ext->value);
                $out->_parameters = $ext;
            }
        }
        if (isset($this->definingSubstanceReference)) {
            $out->definingSubstanceReference = $this->definingSubstanceReference;
        }
        if (isset($this->definingSubstanceCodeableConcept)) {
            $out->definingSubstanceCodeableConcept = $this->definingSubstanceCodeableConcept;
        }
        if (isset($this->amountQuantity)) {
            $out->amountQuantity = $this->amountQuantity;
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
        return $out;
    }
}
