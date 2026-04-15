<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTerminologyCapabilities;

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
use OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * A TerminologyCapabilities resource documents a set of capabilities (behaviors)
 * of a FHIR Terminology Server that may be used as a statement of actual server
 * functionality or a statement of required or desired server implementation.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRTerminologyCapabilitiesVersion extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_TERMINOLOGY_CAPABILITIES_DOT_VERSION;

    /* class_default.php:56 */
    public const FIELD_CODE = 'code';
    public const FIELD_CODE_EXT = '_code';
    public const FIELD_IS_DEFAULT = 'isDefault';
    public const FIELD_IS_DEFAULT_EXT = '_isDefault';
    public const FIELD_COMPOSITIONAL = 'compositional';
    public const FIELD_COMPOSITIONAL_EXT = '_compositional';
    public const FIELD_LANGUAGE = 'language';
    public const FIELD_LANGUAGE_EXT = '_language';
    public const FIELD_FILTER = 'filter';
    public const FIELD_PROPERTY = 'property';
    public const FIELD_PROPERTY_EXT = '_property';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_CODE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_IS_DEFAULT => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_COMPOSITIONAL => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * For version-less code systems, there should be a single version with no
     * identifier.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $code;
    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If this is the default version for this code system.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    #[FHIRBoolean]
    protected FHIRBoolean $isDefault;
    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If the compositional grammar defined by the code system is supported.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    #[FHIRBoolean]
    protected FHIRBoolean $compositional;
    /**
     * A string which has at least one character and no leading or trailing whitespace
     * and where there is no whitespace other than single spaces in the contents
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Language Displays supported.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode>
     */
    #[FHIRCode]
    protected array $language;
    /**
     * A TerminologyCapabilities resource documents a set of capabilities (behaviors)
     * of a FHIR Terminology Server that may be used as a statement of actual server
     * functionality or a statement of required or desired server implementation.
     *
     * Filter Properties supported.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTerminologyCapabilities\FHIRTerminologyCapabilitiesFilter>
     */
    #[FHIRTerminologyCapabilitiesFilter]
    protected array $filter;
    /**
     * A string which has at least one character and no leading or trailing whitespace
     * and where there is no whitespace other than single spaces in the contents
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Properties supported for $lookup.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode>
     */
    #[FHIRCode]
    protected array $property;

    /* constructor.php:61 */
    /**
     * FHIRTerminologyCapabilitiesVersion Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $code
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $isDefault
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $compositional
     * @param null|iterable<string>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode> $language
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTerminologyCapabilities\FHIRTerminologyCapabilitiesFilter> $filter
     * @param null|iterable<string>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode> $property
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|string|FHIRStringPrimitive|FHIRString $code = null,
                                null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $isDefault = null,
                                null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $compositional = null,
                                null|iterable $language = null,
                                null|iterable $filter = null,
                                null|iterable $property = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $code) {
            $this->setCode($code);
        }
        if (null !== $isDefault) {
            $this->setIsDefault($isDefault);
        }
        if (null !== $compositional) {
            $this->setCompositional($compositional);
        }
        if (null !== $language) {
            $this->setLanguage(...$language);
        }
        if (null !== $filter) {
            $this->setFilter(...$filter);
        }
        if (null !== $property) {
            $this->setProperty(...$property);
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
     * For version-less code systems, there should be a single version with no
     * identifier.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getCode(): null|FHIRString
    {
        return $this->code ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * For version-less code systems, there should be a single version with no
     * identifier.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $code
     * @return static
     */
    public function setCode(null|string|FHIRStringPrimitive|FHIRString $code): self
    {
        if (null === $code) {
            unset($this->code);
            return $this;
        }
        if (!($code instanceof FHIRString)) {
            $code = new FHIRString(value: $code);
        }
        $this->code = $code;
        return $this;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If this is the default version for this code system.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    public function getIsDefault(): null|FHIRBoolean
    {
        return $this->isDefault ?? null;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If this is the default version for this code system.
     *
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $isDefault
     * @return static
     */
    public function setIsDefault(null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $isDefault): self
    {
        if (null === $isDefault) {
            unset($this->isDefault);
            return $this;
        }
        if (!($isDefault instanceof FHIRBoolean)) {
            $isDefault = new FHIRBoolean(value: $isDefault);
        }
        $this->isDefault = $isDefault;
        return $this;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If the compositional grammar defined by the code system is supported.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    public function getCompositional(): null|FHIRBoolean
    {
        return $this->compositional ?? null;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If the compositional grammar defined by the code system is supported.
     *
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $compositional
     * @return static
     */
    public function setCompositional(null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $compositional): self
    {
        if (null === $compositional) {
            unset($this->compositional);
            return $this;
        }
        if (!($compositional instanceof FHIRBoolean)) {
            $compositional = new FHIRBoolean(value: $compositional);
        }
        $this->compositional = $compositional;
        return $this;
    }

    /**
     * A string which has at least one character and no leading or trailing whitespace
     * and where there is no whitespace other than single spaces in the contents
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Language Displays supported.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode>
     */
    public function getLanguage(): array
    {
        return $this->language ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode>
     */
    public function getLanguageIterator(): iterable
    {
        if (!isset($this->language)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->language);
    }

    /**
     * A string which has at least one character and no leading or trailing whitespace
     * and where there is no whitespace other than single spaces in the contents
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Language Displays supported.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $language
     * @return static
     */
    public function addLanguage(string|FHIRCodePrimitive|FHIRCode $language): self
    {
        if (!($language instanceof FHIRCode)) {
            $language = new FHIRCode(value: $language);
        }
        if (!isset($this->language)) {
            $this->language = [];
        }
        $this->language[] = $language;
        return $this;
    }

    /**
     * A string which has at least one character and no leading or trailing whitespace
     * and where there is no whitespace other than single spaces in the contents
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Language Displays supported.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode ...$language
     * @return static
     */
    public function setLanguage(string|FHIRCodePrimitive|FHIRCode ...$language): self
    {
        if ([] === $language) {
            unset($this->language);
            return $this;
        }
        $this->language = [];
        foreach($language as $v) {
            if ($v instanceof FHIRCode) {
                $this->language[] = $v;
            } else {
                $this->language[] = new FHIRCode(value: $v);
            }
        }
        return $this;
    }

    /**
     * A TerminologyCapabilities resource documents a set of capabilities (behaviors)
     * of a FHIR Terminology Server that may be used as a statement of actual server
     * functionality or a statement of required or desired server implementation.
     *
     * Filter Properties supported.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTerminologyCapabilities\FHIRTerminologyCapabilitiesFilter>
     */
    public function getFilter(): array
    {
        return $this->filter ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTerminologyCapabilities\FHIRTerminologyCapabilitiesFilter>
     */
    public function getFilterIterator(): iterable
    {
        if (!isset($this->filter)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->filter);
    }

    /**
     * A TerminologyCapabilities resource documents a set of capabilities (behaviors)
     * of a FHIR Terminology Server that may be used as a statement of actual server
     * functionality or a statement of required or desired server implementation.
     *
     * Filter Properties supported.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTerminologyCapabilities\FHIRTerminologyCapabilitiesFilter $filter
     * @return static
     */
    public function addFilter(FHIRTerminologyCapabilitiesFilter $filter): self
    {
        if (!isset($this->filter)) {
            $this->filter = [];
        }
        $this->filter[] = $filter;
        return $this;
    }

    /**
     * A TerminologyCapabilities resource documents a set of capabilities (behaviors)
     * of a FHIR Terminology Server that may be used as a statement of actual server
     * functionality or a statement of required or desired server implementation.
     *
     * Filter Properties supported.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTerminologyCapabilities\FHIRTerminologyCapabilitiesFilter ...$filter
     * @return static
     */
    public function setFilter(FHIRTerminologyCapabilitiesFilter ...$filter): self
    {
        if ([] === $filter) {
            unset($this->filter);
            return $this;
        }
        $this->filter = $filter;
        return $this;
    }

    /**
     * A string which has at least one character and no leading or trailing whitespace
     * and where there is no whitespace other than single spaces in the contents
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Properties supported for $lookup.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode>
     */
    public function getProperty(): array
    {
        return $this->property ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode>
     */
    public function getPropertyIterator(): iterable
    {
        if (!isset($this->property)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->property);
    }

    /**
     * A string which has at least one character and no leading or trailing whitespace
     * and where there is no whitespace other than single spaces in the contents
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Properties supported for $lookup.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $property
     * @return static
     */
    public function addProperty(string|FHIRCodePrimitive|FHIRCode $property): self
    {
        if (!($property instanceof FHIRCode)) {
            $property = new FHIRCode(value: $property);
        }
        if (!isset($this->property)) {
            $this->property = [];
        }
        $this->property[] = $property;
        return $this;
    }

    /**
     * A string which has at least one character and no leading or trailing whitespace
     * and where there is no whitespace other than single spaces in the contents
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Properties supported for $lookup.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode ...$property
     * @return static
     */
    public function setProperty(string|FHIRCodePrimitive|FHIRCode ...$property): self
    {
        if ([] === $property) {
            unset($this->property);
            return $this;
        }
        $this->property = [];
        foreach($property as $v) {
            if ($v instanceof FHIRCode) {
                $this->property[] = $v;
            } else {
                $this->property[] = new FHIRCode(value: $v);
            }
        }
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTerminologyCapabilities\FHIRTerminologyCapabilitiesVersion $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTerminologyCapabilities\FHIRTerminologyCapabilitiesVersion
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRTerminologyCapabilitiesVersion)) {
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
            } else if (self::FIELD_CODE === $cen) {
                $type->setCode(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_IS_DEFAULT === $cen) {
                $type->setIsDefault(FHIRBoolean::xmlUnserialize($ce, $config));
            } else if (self::FIELD_COMPOSITIONAL === $cen) {
                $type->setCompositional(FHIRBoolean::xmlUnserialize($ce, $config));
            } else if (self::FIELD_LANGUAGE === $cen) {
                $type->addLanguage(FHIRCode::xmlUnserialize($ce, $config));
            } else if (self::FIELD_FILTER === $cen) {
                $type->addFilter(FHIRTerminologyCapabilitiesFilter::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PROPERTY === $cen) {
                $type->addProperty(FHIRCode::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_CODE])) {
            if (isset($type->code)) {
                $type->code->setValue((string)$attributes[self::FIELD_CODE]);
            } else {
                $type->setCode((string)$attributes[self::FIELD_CODE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_CODE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_IS_DEFAULT])) {
            if (isset($type->isDefault)) {
                $type->isDefault->setValue((string)$attributes[self::FIELD_IS_DEFAULT]);
            } else {
                $type->setIsDefault((string)$attributes[self::FIELD_IS_DEFAULT]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_IS_DEFAULT, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_COMPOSITIONAL])) {
            if (isset($type->compositional)) {
                $type->compositional->setValue((string)$attributes[self::FIELD_COMPOSITIONAL]);
            } else {
                $type->setCompositional((string)$attributes[self::FIELD_COMPOSITIONAL]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_COMPOSITIONAL, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->code) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_CODE]) {
            $xw->writeAttribute(self::FIELD_CODE, $this->code->_getValueAsString());
        }
        if (isset($this->isDefault) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_IS_DEFAULT]) {
            $xw->writeAttribute(self::FIELD_IS_DEFAULT, $this->isDefault->_getValueAsString());
        }
        if (isset($this->compositional) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_COMPOSITIONAL]) {
            $xw->writeAttribute(self::FIELD_COMPOSITIONAL, $this->compositional->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->code)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_CODE]
                || $this->code->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_CODE);
            $this->code->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_CODE]);
            $xw->endElement();
        }
        if (isset($this->isDefault)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_IS_DEFAULT]
                || $this->isDefault->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_IS_DEFAULT);
            $this->isDefault->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_IS_DEFAULT]);
            $xw->endElement();
        }
        if (isset($this->compositional)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_COMPOSITIONAL]
                || $this->compositional->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_COMPOSITIONAL);
            $this->compositional->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_COMPOSITIONAL]);
            $xw->endElement();
        }
        if (isset($this->language) && [] !== $this->language) {
            foreach($this->language as $v) {
                $xw->startElement(self::FIELD_LANGUAGE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->filter)) {
            foreach ($this->filter as $v) {
                $xw->startElement(self::FIELD_FILTER);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->property) && [] !== $this->property) {
            foreach($this->property as $v) {
                $xw->startElement(self::FIELD_PROPERTY);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTerminologyCapabilities\FHIRTerminologyCapabilitiesVersion $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTerminologyCapabilities\FHIRTerminologyCapabilitiesVersion
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
        } else if (!($type instanceof FHIRTerminologyCapabilitiesVersion)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->code)
            || isset($decoded->_code)
            || property_exists($decoded, self::FIELD_CODE)
            || property_exists($decoded, self::FIELD_CODE_EXT)) {
            $v = $decoded->_code ?? new \stdClass();
            $v->value = $decoded->code ?? null;
            $type->setCode(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->isDefault)
            || isset($decoded->_isDefault)
            || property_exists($decoded, self::FIELD_IS_DEFAULT)
            || property_exists($decoded, self::FIELD_IS_DEFAULT_EXT)) {
            $v = $decoded->_isDefault ?? new \stdClass();
            $v->value = $decoded->isDefault ?? null;
            $type->setIsDefault(FHIRBoolean::jsonUnserialize($v, $config));
        }
        if (isset($decoded->compositional)
            || isset($decoded->_compositional)
            || property_exists($decoded, self::FIELD_COMPOSITIONAL)
            || property_exists($decoded, self::FIELD_COMPOSITIONAL_EXT)) {
            $v = $decoded->_compositional ?? new \stdClass();
            $v->value = $decoded->compositional ?? null;
            $type->setCompositional(FHIRBoolean::jsonUnserialize($v, $config));
        }
        if (isset($decoded->language)
            || isset($decoded->_language)
            || property_exists($decoded, self::FIELD_LANGUAGE)
            || property_exists($decoded, self::FIELD_LANGUAGE_EXT)) {
            $vals = (array)($decoded->language ?? []);
            $exts = (array)($decoded->_language ?? []);
            $valCnt = count($vals);
            $extCnt = count($exts);
            if ($extCnt > $valCnt) {
                $valCnt = $extCnt;
            }
            for ($i = 0; $i < $valCnt; $i++) {
                $v = $exts[$i] ?? new \stdClass();
                $v->value = $vals[$i] ?? null;
                $type->addLanguage(FHIRCode::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->filter) || property_exists($decoded, self::FIELD_FILTER)) {
            if (is_object($decoded->filter)) {
                $vals = [$decoded->filter];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_FILTER, true);
            } else {
                $vals = $decoded->filter;
            }
            foreach($vals as $v) {
                $type->addFilter(FHIRTerminologyCapabilitiesFilter::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->property)
            || isset($decoded->_property)
            || property_exists($decoded, self::FIELD_PROPERTY)
            || property_exists($decoded, self::FIELD_PROPERTY_EXT)) {
            $vals = (array)($decoded->property ?? []);
            $exts = (array)($decoded->_property ?? []);
            $valCnt = count($vals);
            $extCnt = count($exts);
            if ($extCnt > $valCnt) {
                $valCnt = $extCnt;
            }
            for ($i = 0; $i < $valCnt; $i++) {
                $v = $exts[$i] ?? new \stdClass();
                $v->value = $vals[$i] ?? null;
                $type->addProperty(FHIRCode::jsonUnserialize($v, $config));
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
        if (isset($this->code)) {
            if (null !== ($val = $this->code->getValue())) {
                $out->code = $val;
            }
            if ($this->code->_nonValueFieldDefined()) {
                $ext = $this->code->jsonSerialize();
                unset($ext->value);
                $out->_code = $ext;
            }
        }
        if (isset($this->isDefault)) {
            if (null !== ($val = $this->isDefault->getValue())) {
                $out->isDefault = $val;
            }
            if ($this->isDefault->_nonValueFieldDefined()) {
                $ext = $this->isDefault->jsonSerialize();
                unset($ext->value);
                $out->_isDefault = $ext;
            }
        }
        if (isset($this->compositional)) {
            if (null !== ($val = $this->compositional->getValue())) {
                $out->compositional = $val;
            }
            if ($this->compositional->_nonValueFieldDefined()) {
                $ext = $this->compositional->jsonSerialize();
                unset($ext->value);
                $out->_compositional = $ext;
            }
        }
        if (isset($this->language) && [] !== $this->language) {
            $vals = [];
            $exts = [];
            $hasVals = false;
            $hasExts = false;
            foreach ($this->language as $v) {
                $val = $v->getValue();
                if (null !== $val) {
                    $hasVals = true;
                    $vals[] = $val;
                } else {
                    $vals[] = null;
                }
                if ($v->_nonValueFieldDefined()) {
                    $hasExts = true;
                    $ext = $v->jsonSerialize();
                    unset($ext->value);
                    $exts[] = $ext;
                } else {
                    $exts[] = null;
                }
            }
            if ($hasVals) {
                $out->language = $vals;
            }
            if ($hasExts) {
                $out->_language = $exts;
            }
        }
        if (isset($this->filter) && [] !== $this->filter) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_FILTER) && 1 === count($this->filter)) {
                $out->filter = $this->filter[0];
            } else {
                $out->filter = $this->filter;
            }
        }
        if (isset($this->property) && [] !== $this->property) {
            $vals = [];
            $exts = [];
            $hasVals = false;
            $hasExts = false;
            foreach ($this->property as $v) {
                $val = $v->getValue();
                if (null !== $val) {
                    $hasVals = true;
                    $vals[] = $val;
                } else {
                    $vals[] = null;
                }
                if ($v->_nonValueFieldDefined()) {
                    $hasExts = true;
                    $ext = $v->jsonSerialize();
                    unset($ext->value);
                    $exts[] = $ext;
                } else {
                    $exts[] = null;
                }
            }
            if ($hasVals) {
                $out->property = $vals;
            }
            if ($hasExts) {
                $out->_property = $exts;
            }
        }
        return $out;
    }
}
