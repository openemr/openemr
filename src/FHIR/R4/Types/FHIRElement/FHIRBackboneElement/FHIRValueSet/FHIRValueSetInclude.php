<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRValueSet;

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
use OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * A ValueSet resource instance specifies a set of codes drawn from one or more
 * code systems, intended for use in a particular context. Value sets link between
 * [[[CodeSystem]]] definitions and their use in [coded
 * elements](terminologies.html).
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRValueSetInclude extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_VALUE_SET_DOT_INCLUDE;

    /* class_default.php:56 */
    public const FIELD_SYSTEM = 'system';
    public const FIELD_SYSTEM_EXT = '_system';
    public const FIELD_VERSION = 'version';
    public const FIELD_VERSION_EXT = '_version';
    public const FIELD_CONCEPT = 'concept';
    public const FIELD_FILTER = 'filter';
    public const FIELD_VALUE_SET = 'valueSet';
    public const FIELD_VALUE_SET_EXT = '_valueSet';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_SYSTEM => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_VERSION => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * An absolute URI which is the code system from which the selected codes come
     * from.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri
     */
    #[FHIRUri]
    protected FHIRUri $system;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The version of the code system that the codes are selected from, or the special
     * version '*' for all versions.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $version;
    /**
     * A ValueSet resource instance specifies a set of codes drawn from one or more
     * code systems, intended for use in a particular context. Value sets link between
     * [[[CodeSystem]]] definitions and their use in [coded
     * elements](terminologies.html).
     *
     * Specifies a concept to be included or excluded.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRValueSet\FHIRValueSetConcept>
     */
    #[FHIRValueSetConcept]
    protected array $concept;
    /**
     * A ValueSet resource instance specifies a set of codes drawn from one or more
     * code systems, intended for use in a particular context. Value sets link between
     * [[[CodeSystem]]] definitions and their use in [coded
     * elements](terminologies.html).
     *
     * Select concepts by specify a matching criterion based on the properties
     * (including relationships) defined by the system, or on filters defined by the
     * system. If multiple filters are specified, they SHALL all be true.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRValueSet\FHIRValueSetFilter>
     */
    #[FHIRValueSetFilter]
    protected array $filter;
    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Selects the concepts found in this value set (based on its value set
     * definition). This is an absolute URI that is a reference to ValueSet.url. If
     * multiple value sets are specified this includes the union of the contents of all
     * of the referenced value sets.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical>
     */
    #[FHIRCanonical]
    protected array $valueSet;

    /* constructor.php:61 */
    /**
     * FHIRValueSetInclude Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $system
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $version
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRValueSet\FHIRValueSetConcept> $concept
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRValueSet\FHIRValueSetFilter> $filter
     * @param null|iterable<string>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical> $valueSet
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|string|FHIRUriPrimitive|FHIRUri $system = null,
                                null|string|FHIRStringPrimitive|FHIRString $version = null,
                                null|iterable $concept = null,
                                null|iterable $filter = null,
                                null|iterable $valueSet = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $system) {
            $this->setSystem($system);
        }
        if (null !== $version) {
            $this->setVersion($version);
        }
        if (null !== $concept) {
            $this->setConcept(...$concept);
        }
        if (null !== $filter) {
            $this->setFilter(...$filter);
        }
        if (null !== $valueSet) {
            $this->setValueSet(...$valueSet);
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
     * An absolute URI which is the code system from which the selected codes come
     * from.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri
     */
    public function getSystem(): null|FHIRUri
    {
        return $this->system ?? null;
    }

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * An absolute URI which is the code system from which the selected codes come
     * from.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $system
     * @return static
     */
    public function setSystem(null|string|FHIRUriPrimitive|FHIRUri $system): self
    {
        if (null === $system) {
            unset($this->system);
            return $this;
        }
        if (!($system instanceof FHIRUri)) {
            $system = new FHIRUri(value: $system);
        }
        $this->system = $system;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The version of the code system that the codes are selected from, or the special
     * version '*' for all versions.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getVersion(): null|FHIRString
    {
        return $this->version ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The version of the code system that the codes are selected from, or the special
     * version '*' for all versions.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $version
     * @return static
     */
    public function setVersion(null|string|FHIRStringPrimitive|FHIRString $version): self
    {
        if (null === $version) {
            unset($this->version);
            return $this;
        }
        if (!($version instanceof FHIRString)) {
            $version = new FHIRString(value: $version);
        }
        $this->version = $version;
        return $this;
    }

    /**
     * A ValueSet resource instance specifies a set of codes drawn from one or more
     * code systems, intended for use in a particular context. Value sets link between
     * [[[CodeSystem]]] definitions and their use in [coded
     * elements](terminologies.html).
     *
     * Specifies a concept to be included or excluded.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRValueSet\FHIRValueSetConcept>
     */
    public function getConcept(): array
    {
        return $this->concept ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRValueSet\FHIRValueSetConcept>
     */
    public function getConceptIterator(): iterable
    {
        if (!isset($this->concept)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->concept);
    }

    /**
     * A ValueSet resource instance specifies a set of codes drawn from one or more
     * code systems, intended for use in a particular context. Value sets link between
     * [[[CodeSystem]]] definitions and their use in [coded
     * elements](terminologies.html).
     *
     * Specifies a concept to be included or excluded.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRValueSet\FHIRValueSetConcept $concept
     * @return static
     */
    public function addConcept(FHIRValueSetConcept $concept): self
    {
        if (!isset($this->concept)) {
            $this->concept = [];
        }
        $this->concept[] = $concept;
        return $this;
    }

    /**
     * A ValueSet resource instance specifies a set of codes drawn from one or more
     * code systems, intended for use in a particular context. Value sets link between
     * [[[CodeSystem]]] definitions and their use in [coded
     * elements](terminologies.html).
     *
     * Specifies a concept to be included or excluded.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRValueSet\FHIRValueSetConcept ...$concept
     * @return static
     */
    public function setConcept(FHIRValueSetConcept ...$concept): self
    {
        if ([] === $concept) {
            unset($this->concept);
            return $this;
        }
        $this->concept = $concept;
        return $this;
    }

    /**
     * A ValueSet resource instance specifies a set of codes drawn from one or more
     * code systems, intended for use in a particular context. Value sets link between
     * [[[CodeSystem]]] definitions and their use in [coded
     * elements](terminologies.html).
     *
     * Select concepts by specify a matching criterion based on the properties
     * (including relationships) defined by the system, or on filters defined by the
     * system. If multiple filters are specified, they SHALL all be true.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRValueSet\FHIRValueSetFilter>
     */
    public function getFilter(): array
    {
        return $this->filter ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRValueSet\FHIRValueSetFilter>
     */
    public function getFilterIterator(): iterable
    {
        if (!isset($this->filter)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->filter);
    }

    /**
     * A ValueSet resource instance specifies a set of codes drawn from one or more
     * code systems, intended for use in a particular context. Value sets link between
     * [[[CodeSystem]]] definitions and their use in [coded
     * elements](terminologies.html).
     *
     * Select concepts by specify a matching criterion based on the properties
     * (including relationships) defined by the system, or on filters defined by the
     * system. If multiple filters are specified, they SHALL all be true.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRValueSet\FHIRValueSetFilter $filter
     * @return static
     */
    public function addFilter(FHIRValueSetFilter $filter): self
    {
        if (!isset($this->filter)) {
            $this->filter = [];
        }
        $this->filter[] = $filter;
        return $this;
    }

    /**
     * A ValueSet resource instance specifies a set of codes drawn from one or more
     * code systems, intended for use in a particular context. Value sets link between
     * [[[CodeSystem]]] definitions and their use in [coded
     * elements](terminologies.html).
     *
     * Select concepts by specify a matching criterion based on the properties
     * (including relationships) defined by the system, or on filters defined by the
     * system. If multiple filters are specified, they SHALL all be true.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRValueSet\FHIRValueSetFilter ...$filter
     * @return static
     */
    public function setFilter(FHIRValueSetFilter ...$filter): self
    {
        if ([] === $filter) {
            unset($this->filter);
            return $this;
        }
        $this->filter = $filter;
        return $this;
    }

    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Selects the concepts found in this value set (based on its value set
     * definition). This is an absolute URI that is a reference to ValueSet.url. If
     * multiple value sets are specified this includes the union of the contents of all
     * of the referenced value sets.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical>
     */
    public function getValueSet(): array
    {
        return $this->valueSet ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical>
     */
    public function getValueSetIterator(): iterable
    {
        if (!isset($this->valueSet)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->valueSet);
    }

    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Selects the concepts found in this value set (based on its value set
     * definition). This is an absolute URI that is a reference to ValueSet.url. If
     * multiple value sets are specified this includes the union of the contents of all
     * of the referenced value sets.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical $valueSet
     * @return static
     */
    public function addValueSet(string|FHIRCanonicalPrimitive|FHIRCanonical $valueSet): self
    {
        if (!($valueSet instanceof FHIRCanonical)) {
            $valueSet = new FHIRCanonical(value: $valueSet);
        }
        if (!isset($this->valueSet)) {
            $this->valueSet = [];
        }
        $this->valueSet[] = $valueSet;
        return $this;
    }

    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Selects the concepts found in this value set (based on its value set
     * definition). This is an absolute URI that is a reference to ValueSet.url. If
     * multiple value sets are specified this includes the union of the contents of all
     * of the referenced value sets.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical ...$valueSet
     * @return static
     */
    public function setValueSet(string|FHIRCanonicalPrimitive|FHIRCanonical ...$valueSet): self
    {
        if ([] === $valueSet) {
            unset($this->valueSet);
            return $this;
        }
        $this->valueSet = [];
        foreach($valueSet as $v) {
            if ($v instanceof FHIRCanonical) {
                $this->valueSet[] = $v;
            } else {
                $this->valueSet[] = new FHIRCanonical(value: $v);
            }
        }
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRValueSet\FHIRValueSetInclude $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRValueSet\FHIRValueSetInclude
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRValueSetInclude)) {
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
            } else if (self::FIELD_SYSTEM === $cen) {
                $type->setSystem(FHIRUri::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VERSION === $cen) {
                $type->setVersion(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CONCEPT === $cen) {
                $type->addConcept(FHIRValueSetConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_FILTER === $cen) {
                $type->addFilter(FHIRValueSetFilter::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALUE_SET === $cen) {
                $type->addValueSet(FHIRCanonical::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_SYSTEM])) {
            if (isset($type->system)) {
                $type->system->setValue((string)$attributes[self::FIELD_SYSTEM]);
            } else {
                $type->setSystem((string)$attributes[self::FIELD_SYSTEM]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_SYSTEM, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_VERSION])) {
            if (isset($type->version)) {
                $type->version->setValue((string)$attributes[self::FIELD_VERSION]);
            } else {
                $type->setVersion((string)$attributes[self::FIELD_VERSION]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_VERSION, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->system) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_SYSTEM]) {
            $xw->writeAttribute(self::FIELD_SYSTEM, $this->system->_getValueAsString());
        }
        if (isset($this->version) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_VERSION]) {
            $xw->writeAttribute(self::FIELD_VERSION, $this->version->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->system)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_SYSTEM]
                || $this->system->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_SYSTEM);
            $this->system->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_SYSTEM]);
            $xw->endElement();
        }
        if (isset($this->version)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_VERSION]
                || $this->version->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_VERSION);
            $this->version->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_VERSION]);
            $xw->endElement();
        }
        if (isset($this->concept)) {
            foreach ($this->concept as $v) {
                $xw->startElement(self::FIELD_CONCEPT);
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
        if (isset($this->valueSet) && [] !== $this->valueSet) {
            foreach($this->valueSet as $v) {
                $xw->startElement(self::FIELD_VALUE_SET);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRValueSet\FHIRValueSetInclude $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRValueSet\FHIRValueSetInclude
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
        } else if (!($type instanceof FHIRValueSetInclude)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->system)
            || isset($decoded->_system)
            || property_exists($decoded, self::FIELD_SYSTEM)
            || property_exists($decoded, self::FIELD_SYSTEM_EXT)) {
            $v = $decoded->_system ?? new \stdClass();
            $v->value = $decoded->system ?? null;
            $type->setSystem(FHIRUri::jsonUnserialize($v, $config));
        }
        if (isset($decoded->version)
            || isset($decoded->_version)
            || property_exists($decoded, self::FIELD_VERSION)
            || property_exists($decoded, self::FIELD_VERSION_EXT)) {
            $v = $decoded->_version ?? new \stdClass();
            $v->value = $decoded->version ?? null;
            $type->setVersion(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->concept) || property_exists($decoded, self::FIELD_CONCEPT)) {
            if (is_object($decoded->concept)) {
                $vals = [$decoded->concept];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_CONCEPT, true);
            } else {
                $vals = $decoded->concept;
            }
            foreach($vals as $v) {
                $type->addConcept(FHIRValueSetConcept::jsonUnserialize($v, $config));
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
                $type->addFilter(FHIRValueSetFilter::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->valueSet)
            || isset($decoded->_valueSet)
            || property_exists($decoded, self::FIELD_VALUE_SET)
            || property_exists($decoded, self::FIELD_VALUE_SET_EXT)) {
            $vals = (array)($decoded->valueSet ?? []);
            $exts = (array)($decoded->_valueSet ?? []);
            $valCnt = count($vals);
            $extCnt = count($exts);
            if ($extCnt > $valCnt) {
                $valCnt = $extCnt;
            }
            for ($i = 0; $i < $valCnt; $i++) {
                $v = $exts[$i] ?? new \stdClass();
                $v->value = $vals[$i] ?? null;
                $type->addValueSet(FHIRCanonical::jsonUnserialize($v, $config));
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
        if (isset($this->system)) {
            if (null !== ($val = $this->system->getValue())) {
                $out->system = $val;
            }
            if ($this->system->_nonValueFieldDefined()) {
                $ext = $this->system->jsonSerialize();
                unset($ext->value);
                $out->_system = $ext;
            }
        }
        if (isset($this->version)) {
            if (null !== ($val = $this->version->getValue())) {
                $out->version = $val;
            }
            if ($this->version->_nonValueFieldDefined()) {
                $ext = $this->version->jsonSerialize();
                unset($ext->value);
                $out->_version = $ext;
            }
        }
        if (isset($this->concept) && [] !== $this->concept) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_CONCEPT) && 1 === count($this->concept)) {
                $out->concept = $this->concept[0];
            } else {
                $out->concept = $this->concept;
            }
        }
        if (isset($this->filter) && [] !== $this->filter) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_FILTER) && 1 === count($this->filter)) {
                $out->filter = $this->filter[0];
            } else {
                $out->filter = $this->filter;
            }
        }
        if (isset($this->valueSet) && [] !== $this->valueSet) {
            $vals = [];
            $exts = [];
            $hasVals = false;
            $hasExts = false;
            foreach ($this->valueSet as $v) {
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
                $out->valueSet = $vals;
            }
            if ($hasExts) {
                $out->_valueSet = $exts;
            }
        }
        return $out;
    }
}
