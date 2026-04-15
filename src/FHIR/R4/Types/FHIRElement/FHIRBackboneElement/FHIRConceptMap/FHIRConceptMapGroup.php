<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRConceptMap;

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
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * A statement of relationships from one set of concepts to one or more other
 * concepts - either concepts in code systems, or data element/data element
 * concepts, or classes in class models.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRConceptMapGroup extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_CONCEPT_MAP_DOT_GROUP;

    /* class_default.php:56 */
    public const FIELD_SOURCE = 'source';
    public const FIELD_SOURCE_EXT = '_source';
    public const FIELD_SOURCE_VERSION = 'sourceVersion';
    public const FIELD_SOURCE_VERSION_EXT = '_sourceVersion';
    public const FIELD_TARGET = 'target';
    public const FIELD_TARGET_EXT = '_target';
    public const FIELD_TARGET_VERSION = 'targetVersion';
    public const FIELD_TARGET_VERSION_EXT = '_targetVersion';
    public const FIELD_ELEMENT = 'element';
    public const FIELD_UNMAPPED = 'unmapped';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_ELEMENT => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_SOURCE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_SOURCE_VERSION => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_TARGET => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_TARGET_VERSION => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * An absolute URI that identifies the source system where the concepts to be
     * mapped are defined.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri
     */
    #[FHIRUri]
    protected FHIRUri $source;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The specific version of the code system, as determined by the code system
     * authority.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $sourceVersion;
    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * An absolute URI that identifies the target system that the concepts will be
     * mapped to.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri
     */
    #[FHIRUri]
    protected FHIRUri $target;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The specific version of the code system, as determined by the code system
     * authority.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $targetVersion;
    /**
     * A statement of relationships from one set of concepts to one or more other
     * concepts - either concepts in code systems, or data element/data element
     * concepts, or classes in class models.
     *
     * Mappings for an individual concept in the source to one or more concepts in the
     * target.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRConceptMap\FHIRConceptMapElement>
     */
    #[FHIRConceptMapElement]
    protected array $element;
    /**
     * A statement of relationships from one set of concepts to one or more other
     * concepts - either concepts in code systems, or data element/data element
     * concepts, or classes in class models.
     *
     * What to do when there is no mapping for the source concept. "Unmapped" does not
     * include codes that are unmatched, and the unmapped element is ignored in a code
     * is specified to have equivalence = unmatched.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRConceptMap\FHIRConceptMapUnmapped
     */
    #[FHIRConceptMapUnmapped]
    protected FHIRConceptMapUnmapped $unmapped;

    /* constructor.php:61 */
    /**
     * FHIRConceptMapGroup Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $source
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $sourceVersion
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $target
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $targetVersion
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRConceptMap\FHIRConceptMapElement> $element
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRConceptMap\FHIRConceptMapUnmapped $unmapped
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|string|FHIRUriPrimitive|FHIRUri $source = null,
                                null|string|FHIRStringPrimitive|FHIRString $sourceVersion = null,
                                null|string|FHIRUriPrimitive|FHIRUri $target = null,
                                null|string|FHIRStringPrimitive|FHIRString $targetVersion = null,
                                null|iterable $element = null,
                                null|FHIRConceptMapUnmapped $unmapped = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $source) {
            $this->setSource($source);
        }
        if (null !== $sourceVersion) {
            $this->setSourceVersion($sourceVersion);
        }
        if (null !== $target) {
            $this->setTarget($target);
        }
        if (null !== $targetVersion) {
            $this->setTargetVersion($targetVersion);
        }
        if (null !== $element) {
            $this->setElement(...$element);
        }
        if (null !== $unmapped) {
            $this->setUnmapped($unmapped);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * An absolute URI that identifies the source system where the concepts to be
     * mapped are defined.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri
     */
    public function getSource(): null|FHIRUri
    {
        return $this->source ?? null;
    }

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * An absolute URI that identifies the source system where the concepts to be
     * mapped are defined.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $source
     * @return static
     */
    public function setSource(null|string|FHIRUriPrimitive|FHIRUri $source): self
    {
        if (null === $source) {
            unset($this->source);
            return $this;
        }
        if (!($source instanceof FHIRUri)) {
            $source = new FHIRUri(value: $source);
        }
        $this->source = $source;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The specific version of the code system, as determined by the code system
     * authority.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getSourceVersion(): null|FHIRString
    {
        return $this->sourceVersion ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The specific version of the code system, as determined by the code system
     * authority.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $sourceVersion
     * @return static
     */
    public function setSourceVersion(null|string|FHIRStringPrimitive|FHIRString $sourceVersion): self
    {
        if (null === $sourceVersion) {
            unset($this->sourceVersion);
            return $this;
        }
        if (!($sourceVersion instanceof FHIRString)) {
            $sourceVersion = new FHIRString(value: $sourceVersion);
        }
        $this->sourceVersion = $sourceVersion;
        return $this;
    }

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * An absolute URI that identifies the target system that the concepts will be
     * mapped to.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri
     */
    public function getTarget(): null|FHIRUri
    {
        return $this->target ?? null;
    }

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * An absolute URI that identifies the target system that the concepts will be
     * mapped to.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $target
     * @return static
     */
    public function setTarget(null|string|FHIRUriPrimitive|FHIRUri $target): self
    {
        if (null === $target) {
            unset($this->target);
            return $this;
        }
        if (!($target instanceof FHIRUri)) {
            $target = new FHIRUri(value: $target);
        }
        $this->target = $target;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The specific version of the code system, as determined by the code system
     * authority.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getTargetVersion(): null|FHIRString
    {
        return $this->targetVersion ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The specific version of the code system, as determined by the code system
     * authority.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $targetVersion
     * @return static
     */
    public function setTargetVersion(null|string|FHIRStringPrimitive|FHIRString $targetVersion): self
    {
        if (null === $targetVersion) {
            unset($this->targetVersion);
            return $this;
        }
        if (!($targetVersion instanceof FHIRString)) {
            $targetVersion = new FHIRString(value: $targetVersion);
        }
        $this->targetVersion = $targetVersion;
        return $this;
    }

    /**
     * A statement of relationships from one set of concepts to one or more other
     * concepts - either concepts in code systems, or data element/data element
     * concepts, or classes in class models.
     *
     * Mappings for an individual concept in the source to one or more concepts in the
     * target.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRConceptMap\FHIRConceptMapElement>
     */
    public function getElement(): array
    {
        return $this->element ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRConceptMap\FHIRConceptMapElement>
     */
    public function getElementIterator(): iterable
    {
        if (!isset($this->element)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->element);
    }

    /**
     * A statement of relationships from one set of concepts to one or more other
     * concepts - either concepts in code systems, or data element/data element
     * concepts, or classes in class models.
     *
     * Mappings for an individual concept in the source to one or more concepts in the
     * target.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRConceptMap\FHIRConceptMapElement $element
     * @return static
     */
    public function addElement(FHIRConceptMapElement $element): self
    {
        if (!isset($this->element)) {
            $this->element = [];
        }
        $this->element[] = $element;
        return $this;
    }

    /**
     * A statement of relationships from one set of concepts to one or more other
     * concepts - either concepts in code systems, or data element/data element
     * concepts, or classes in class models.
     *
     * Mappings for an individual concept in the source to one or more concepts in the
     * target.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRConceptMap\FHIRConceptMapElement ...$element
     * @return static
     */
    public function setElement(FHIRConceptMapElement ...$element): self
    {
        if ([] === $element) {
            unset($this->element);
            return $this;
        }
        $this->element = $element;
        return $this;
    }

    /**
     * A statement of relationships from one set of concepts to one or more other
     * concepts - either concepts in code systems, or data element/data element
     * concepts, or classes in class models.
     *
     * What to do when there is no mapping for the source concept. "Unmapped" does not
     * include codes that are unmatched, and the unmapped element is ignored in a code
     * is specified to have equivalence = unmatched.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRConceptMap\FHIRConceptMapUnmapped
     */
    public function getUnmapped(): null|FHIRConceptMapUnmapped
    {
        return $this->unmapped ?? null;
    }

    /**
     * A statement of relationships from one set of concepts to one or more other
     * concepts - either concepts in code systems, or data element/data element
     * concepts, or classes in class models.
     *
     * What to do when there is no mapping for the source concept. "Unmapped" does not
     * include codes that are unmatched, and the unmapped element is ignored in a code
     * is specified to have equivalence = unmatched.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRConceptMap\FHIRConceptMapUnmapped $unmapped
     * @return static
     */
    public function setUnmapped(null|FHIRConceptMapUnmapped $unmapped): self
    {
        if (null === $unmapped) {
            unset($this->unmapped);
            return $this;
        }
        $this->unmapped = $unmapped;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRConceptMap\FHIRConceptMapGroup $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRConceptMap\FHIRConceptMapGroup
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRConceptMapGroup)) {
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
            } else if (self::FIELD_SOURCE === $cen) {
                $type->setSource(FHIRUri::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SOURCE_VERSION === $cen) {
                $type->setSourceVersion(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TARGET === $cen) {
                $type->setTarget(FHIRUri::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TARGET_VERSION === $cen) {
                $type->setTargetVersion(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ELEMENT === $cen) {
                $type->addElement(FHIRConceptMapElement::xmlUnserialize($ce, $config));
            } else if (self::FIELD_UNMAPPED === $cen) {
                $type->setUnmapped(FHIRConceptMapUnmapped::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_SOURCE])) {
            if (isset($type->source)) {
                $type->source->setValue((string)$attributes[self::FIELD_SOURCE]);
            } else {
                $type->setSource((string)$attributes[self::FIELD_SOURCE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_SOURCE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_SOURCE_VERSION])) {
            if (isset($type->sourceVersion)) {
                $type->sourceVersion->setValue((string)$attributes[self::FIELD_SOURCE_VERSION]);
            } else {
                $type->setSourceVersion((string)$attributes[self::FIELD_SOURCE_VERSION]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_SOURCE_VERSION, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_TARGET])) {
            if (isset($type->target)) {
                $type->target->setValue((string)$attributes[self::FIELD_TARGET]);
            } else {
                $type->setTarget((string)$attributes[self::FIELD_TARGET]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_TARGET, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_TARGET_VERSION])) {
            if (isset($type->targetVersion)) {
                $type->targetVersion->setValue((string)$attributes[self::FIELD_TARGET_VERSION]);
            } else {
                $type->setTargetVersion((string)$attributes[self::FIELD_TARGET_VERSION]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_TARGET_VERSION, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->source) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_SOURCE]) {
            $xw->writeAttribute(self::FIELD_SOURCE, $this->source->_getValueAsString());
        }
        if (isset($this->sourceVersion) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_SOURCE_VERSION]) {
            $xw->writeAttribute(self::FIELD_SOURCE_VERSION, $this->sourceVersion->_getValueAsString());
        }
        if (isset($this->target) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_TARGET]) {
            $xw->writeAttribute(self::FIELD_TARGET, $this->target->_getValueAsString());
        }
        if (isset($this->targetVersion) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_TARGET_VERSION]) {
            $xw->writeAttribute(self::FIELD_TARGET_VERSION, $this->targetVersion->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->source)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_SOURCE]
                || $this->source->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_SOURCE);
            $this->source->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_SOURCE]);
            $xw->endElement();
        }
        if (isset($this->sourceVersion)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_SOURCE_VERSION]
                || $this->sourceVersion->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_SOURCE_VERSION);
            $this->sourceVersion->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_SOURCE_VERSION]);
            $xw->endElement();
        }
        if (isset($this->target)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_TARGET]
                || $this->target->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_TARGET);
            $this->target->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_TARGET]);
            $xw->endElement();
        }
        if (isset($this->targetVersion)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_TARGET_VERSION]
                || $this->targetVersion->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_TARGET_VERSION);
            $this->targetVersion->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_TARGET_VERSION]);
            $xw->endElement();
        }
        if (isset($this->element)) {
            foreach ($this->element as $v) {
                $xw->startElement(self::FIELD_ELEMENT);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->unmapped)) {
            $xw->startElement(self::FIELD_UNMAPPED);
            $this->unmapped->xmlSerialize($xw, $config);
            $xw->endElement();
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRConceptMap\FHIRConceptMapGroup $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRConceptMap\FHIRConceptMapGroup
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
        } else if (!($type instanceof FHIRConceptMapGroup)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->source)
            || isset($decoded->_source)
            || property_exists($decoded, self::FIELD_SOURCE)
            || property_exists($decoded, self::FIELD_SOURCE_EXT)) {
            $v = $decoded->_source ?? new \stdClass();
            $v->value = $decoded->source ?? null;
            $type->setSource(FHIRUri::jsonUnserialize($v, $config));
        }
        if (isset($decoded->sourceVersion)
            || isset($decoded->_sourceVersion)
            || property_exists($decoded, self::FIELD_SOURCE_VERSION)
            || property_exists($decoded, self::FIELD_SOURCE_VERSION_EXT)) {
            $v = $decoded->_sourceVersion ?? new \stdClass();
            $v->value = $decoded->sourceVersion ?? null;
            $type->setSourceVersion(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->target)
            || isset($decoded->_target)
            || property_exists($decoded, self::FIELD_TARGET)
            || property_exists($decoded, self::FIELD_TARGET_EXT)) {
            $v = $decoded->_target ?? new \stdClass();
            $v->value = $decoded->target ?? null;
            $type->setTarget(FHIRUri::jsonUnserialize($v, $config));
        }
        if (isset($decoded->targetVersion)
            || isset($decoded->_targetVersion)
            || property_exists($decoded, self::FIELD_TARGET_VERSION)
            || property_exists($decoded, self::FIELD_TARGET_VERSION_EXT)) {
            $v = $decoded->_targetVersion ?? new \stdClass();
            $v->value = $decoded->targetVersion ?? null;
            $type->setTargetVersion(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->element) || property_exists($decoded, self::FIELD_ELEMENT)) {
            if (is_object($decoded->element)) {
                $vals = [$decoded->element];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_ELEMENT, true);
            } else {
                $vals = $decoded->element;
            }
            foreach($vals as $v) {
                $type->addElement(FHIRConceptMapElement::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->unmapped) || property_exists($decoded, self::FIELD_UNMAPPED)) {
            if (is_array($decoded->unmapped)) {
                $type->setUnmapped(FHIRConceptMapUnmapped::jsonUnserialize(reset($decoded->unmapped), $config));
            } else {
                $type->setUnmapped(FHIRConceptMapUnmapped::jsonUnserialize($decoded->unmapped, $config));
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
        if (isset($this->source)) {
            if (null !== ($val = $this->source->getValue())) {
                $out->source = $val;
            }
            if ($this->source->_nonValueFieldDefined()) {
                $ext = $this->source->jsonSerialize();
                unset($ext->value);
                $out->_source = $ext;
            }
        }
        if (isset($this->sourceVersion)) {
            if (null !== ($val = $this->sourceVersion->getValue())) {
                $out->sourceVersion = $val;
            }
            if ($this->sourceVersion->_nonValueFieldDefined()) {
                $ext = $this->sourceVersion->jsonSerialize();
                unset($ext->value);
                $out->_sourceVersion = $ext;
            }
        }
        if (isset($this->target)) {
            if (null !== ($val = $this->target->getValue())) {
                $out->target = $val;
            }
            if ($this->target->_nonValueFieldDefined()) {
                $ext = $this->target->jsonSerialize();
                unset($ext->value);
                $out->_target = $ext;
            }
        }
        if (isset($this->targetVersion)) {
            if (null !== ($val = $this->targetVersion->getValue())) {
                $out->targetVersion = $val;
            }
            if ($this->targetVersion->_nonValueFieldDefined()) {
                $ext = $this->targetVersion->jsonSerialize();
                unset($ext->value);
                $out->_targetVersion = $ext;
            }
        }
        if (isset($this->element) && [] !== $this->element) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_ELEMENT) && 1 === count($this->element)) {
                $out->element = $this->element[0];
            } else {
                $out->element = $this->element;
            }
        }
        if (isset($this->unmapped)) {
            $out->unmapped = $this->unmapped;
        }
        return $out;
    }
}
