<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProduct;

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
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * Detailed definition of a medicinal product, typically for uses other than direct
 * patient care (e.g. regulatory use).
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRMedicinalProductName extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_MEDICINAL_PRODUCT_DOT_NAME;

    /* class_default.php:56 */
    public const FIELD_PRODUCT_NAME = 'productName';
    public const FIELD_PRODUCT_NAME_EXT = '_productName';
    public const FIELD_NAME_PART = 'namePart';
    public const FIELD_COUNTRY_LANGUAGE = 'countryLanguage';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_PRODUCT_NAME => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_PRODUCT_NAME => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The full product name.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $productName;
    /**
     * Detailed definition of a medicinal product, typically for uses other than direct
     * patient care (e.g. regulatory use).
     *
     * Coding words or phrases of the name.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProduct\FHIRMedicinalProductNamePart>
     */
    #[FHIRMedicinalProductNamePart]
    protected array $namePart;
    /**
     * Detailed definition of a medicinal product, typically for uses other than direct
     * patient care (e.g. regulatory use).
     *
     * Country where the name applies.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProduct\FHIRMedicinalProductCountryLanguage>
     */
    #[FHIRMedicinalProductCountryLanguage]
    protected array $countryLanguage;

    /* constructor.php:61 */
    /**
     * FHIRMedicinalProductName Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $productName
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProduct\FHIRMedicinalProductNamePart> $namePart
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProduct\FHIRMedicinalProductCountryLanguage> $countryLanguage
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|string|FHIRStringPrimitive|FHIRString $productName = null,
                                null|iterable $namePart = null,
                                null|iterable $countryLanguage = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $productName) {
            $this->setProductName($productName);
        }
        if (null !== $namePart) {
            $this->setNamePart(...$namePart);
        }
        if (null !== $countryLanguage) {
            $this->setCountryLanguage(...$countryLanguage);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The full product name.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getProductName(): null|FHIRString
    {
        return $this->productName ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The full product name.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $productName
     * @return static
     */
    public function setProductName(null|string|FHIRStringPrimitive|FHIRString $productName): self
    {
        if (null === $productName) {
            unset($this->productName);
            return $this;
        }
        if (!($productName instanceof FHIRString)) {
            $productName = new FHIRString(value: $productName);
        }
        $this->productName = $productName;
        return $this;
    }

    /**
     * Detailed definition of a medicinal product, typically for uses other than direct
     * patient care (e.g. regulatory use).
     *
     * Coding words or phrases of the name.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProduct\FHIRMedicinalProductNamePart>
     */
    public function getNamePart(): array
    {
        return $this->namePart ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProduct\FHIRMedicinalProductNamePart>
     */
    public function getNamePartIterator(): iterable
    {
        if (!isset($this->namePart)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->namePart);
    }

    /**
     * Detailed definition of a medicinal product, typically for uses other than direct
     * patient care (e.g. regulatory use).
     *
     * Coding words or phrases of the name.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProduct\FHIRMedicinalProductNamePart $namePart
     * @return static
     */
    public function addNamePart(FHIRMedicinalProductNamePart $namePart): self
    {
        if (!isset($this->namePart)) {
            $this->namePart = [];
        }
        $this->namePart[] = $namePart;
        return $this;
    }

    /**
     * Detailed definition of a medicinal product, typically for uses other than direct
     * patient care (e.g. regulatory use).
     *
     * Coding words or phrases of the name.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProduct\FHIRMedicinalProductNamePart ...$namePart
     * @return static
     */
    public function setNamePart(FHIRMedicinalProductNamePart ...$namePart): self
    {
        if ([] === $namePart) {
            unset($this->namePart);
            return $this;
        }
        $this->namePart = $namePart;
        return $this;
    }

    /**
     * Detailed definition of a medicinal product, typically for uses other than direct
     * patient care (e.g. regulatory use).
     *
     * Country where the name applies.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProduct\FHIRMedicinalProductCountryLanguage>
     */
    public function getCountryLanguage(): array
    {
        return $this->countryLanguage ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProduct\FHIRMedicinalProductCountryLanguage>
     */
    public function getCountryLanguageIterator(): iterable
    {
        if (!isset($this->countryLanguage)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->countryLanguage);
    }

    /**
     * Detailed definition of a medicinal product, typically for uses other than direct
     * patient care (e.g. regulatory use).
     *
     * Country where the name applies.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProduct\FHIRMedicinalProductCountryLanguage $countryLanguage
     * @return static
     */
    public function addCountryLanguage(FHIRMedicinalProductCountryLanguage $countryLanguage): self
    {
        if (!isset($this->countryLanguage)) {
            $this->countryLanguage = [];
        }
        $this->countryLanguage[] = $countryLanguage;
        return $this;
    }

    /**
     * Detailed definition of a medicinal product, typically for uses other than direct
     * patient care (e.g. regulatory use).
     *
     * Country where the name applies.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProduct\FHIRMedicinalProductCountryLanguage ...$countryLanguage
     * @return static
     */
    public function setCountryLanguage(FHIRMedicinalProductCountryLanguage ...$countryLanguage): self
    {
        if ([] === $countryLanguage) {
            unset($this->countryLanguage);
            return $this;
        }
        $this->countryLanguage = $countryLanguage;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProduct\FHIRMedicinalProductName $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProduct\FHIRMedicinalProductName
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRMedicinalProductName)) {
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
            } else if (self::FIELD_PRODUCT_NAME === $cen) {
                $type->setProductName(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_NAME_PART === $cen) {
                $type->addNamePart(FHIRMedicinalProductNamePart::xmlUnserialize($ce, $config));
            } else if (self::FIELD_COUNTRY_LANGUAGE === $cen) {
                $type->addCountryLanguage(FHIRMedicinalProductCountryLanguage::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_PRODUCT_NAME])) {
            if (isset($type->productName)) {
                $type->productName->setValue((string)$attributes[self::FIELD_PRODUCT_NAME]);
            } else {
                $type->setProductName((string)$attributes[self::FIELD_PRODUCT_NAME]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_PRODUCT_NAME, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->productName) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_PRODUCT_NAME]) {
            $xw->writeAttribute(self::FIELD_PRODUCT_NAME, $this->productName->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->productName)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_PRODUCT_NAME]
                || $this->productName->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_PRODUCT_NAME);
            $this->productName->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_PRODUCT_NAME]);
            $xw->endElement();
        }
        if (isset($this->namePart)) {
            foreach ($this->namePart as $v) {
                $xw->startElement(self::FIELD_NAME_PART);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->countryLanguage)) {
            foreach ($this->countryLanguage as $v) {
                $xw->startElement(self::FIELD_COUNTRY_LANGUAGE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProduct\FHIRMedicinalProductName $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProduct\FHIRMedicinalProductName
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
        } else if (!($type instanceof FHIRMedicinalProductName)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->productName)
            || isset($decoded->_productName)
            || property_exists($decoded, self::FIELD_PRODUCT_NAME)
            || property_exists($decoded, self::FIELD_PRODUCT_NAME_EXT)) {
            $v = $decoded->_productName ?? new \stdClass();
            $v->value = $decoded->productName ?? null;
            $type->setProductName(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->namePart) || property_exists($decoded, self::FIELD_NAME_PART)) {
            if (is_object($decoded->namePart)) {
                $vals = [$decoded->namePart];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_NAME_PART, true);
            } else {
                $vals = $decoded->namePart;
            }
            foreach($vals as $v) {
                $type->addNamePart(FHIRMedicinalProductNamePart::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->countryLanguage) || property_exists($decoded, self::FIELD_COUNTRY_LANGUAGE)) {
            if (is_object($decoded->countryLanguage)) {
                $vals = [$decoded->countryLanguage];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_COUNTRY_LANGUAGE, true);
            } else {
                $vals = $decoded->countryLanguage;
            }
            foreach($vals as $v) {
                $type->addCountryLanguage(FHIRMedicinalProductCountryLanguage::jsonUnserialize($v, $config));
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
        if (isset($this->productName)) {
            if (null !== ($val = $this->productName->getValue())) {
                $out->productName = $val;
            }
            if ($this->productName->_nonValueFieldDefined()) {
                $ext = $this->productName->jsonSerialize();
                unset($ext->value);
                $out->_productName = $ext;
            }
        }
        if (isset($this->namePart) && [] !== $this->namePart) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_NAME_PART) && 1 === count($this->namePart)) {
                $out->namePart = $this->namePart[0];
            } else {
                $out->namePart = $this->namePart;
            }
        }
        if (isset($this->countryLanguage) && [] !== $this->countryLanguage) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_COUNTRY_LANGUAGE) && 1 === count($this->countryLanguage)) {
                $out->countryLanguage = $this->countryLanguage[0];
            } else {
                $out->countryLanguage = $this->countryLanguage;
            }
        }
        return $out;
    }
}
