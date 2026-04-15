<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIROperationDefinition;

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
use OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIROperationParameterUseList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRSearchParamTypeList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIROperationParameterUse;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRSearchParamType;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * A formal computable definition of an operation (on the RESTful interface) or a
 * named query (using the search interaction).
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIROperationDefinitionParameter extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_OPERATION_DEFINITION_DOT_PARAMETER;

    /* class_default.php:56 */
    public const FIELD_NAME = 'name';
    public const FIELD_NAME_EXT = '_name';
    public const FIELD_USE = 'use';
    public const FIELD_USE_EXT = '_use';
    public const FIELD_MIN = 'min';
    public const FIELD_MIN_EXT = '_min';
    public const FIELD_MAX = 'max';
    public const FIELD_MAX_EXT = '_max';
    public const FIELD_DOCUMENTATION = 'documentation';
    public const FIELD_DOCUMENTATION_EXT = '_documentation';
    public const FIELD_TYPE = 'type';
    public const FIELD_TYPE_EXT = '_type';
    public const FIELD_TARGET_PROFILE = 'targetProfile';
    public const FIELD_TARGET_PROFILE_EXT = '_targetProfile';
    public const FIELD_SEARCH_TYPE = 'searchType';
    public const FIELD_SEARCH_TYPE_EXT = '_searchType';
    public const FIELD_BINDING = 'binding';
    public const FIELD_REFERENCED_FROM = 'referencedFrom';
    public const FIELD_PART = 'part';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_NAME => [
            MinOccursRule::NAME => 1,
        ],
        self::FIELD_USE => [
            MinOccursRule::NAME => 1,
        ],
        self::FIELD_MIN => [
            MinOccursRule::NAME => 1,
        ],
        self::FIELD_MAX => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_NAME => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_USE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_MIN => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_MAX => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_DOCUMENTATION => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_TYPE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_SEARCH_TYPE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * A string which has at least one character and no leading or trailing whitespace
     * and where there is no whitespace other than single spaces in the contents
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The name of used to identify the parameter.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode
     */
    #[FHIRCode]
    protected FHIRCode $name;
    /**
     * Whether an operation parameter is an input or an output parameter.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Whether this is an input or an output parameter.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIROperationParameterUse
     */
    #[FHIROperationParameterUse]
    protected FHIROperationParameterUse $use;
    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The minimum number of times this parameter SHALL appear in the request or
     * response.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger
     */
    #[FHIRInteger]
    protected FHIRInteger $min;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The maximum number of times this element is permitted to appear in the request
     * or response.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $max;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Describes the meaning or use of this parameter.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $documentation;
    /**
     * A string which has at least one character and no leading or trailing whitespace
     * and where there is no whitespace other than single spaces in the contents
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The type for this parameter.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode
     */
    #[FHIRCode]
    protected FHIRCode $type;
    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Used when the type is "Reference" or "canonical", and identifies a profile
     * structure or implementation Guide that applies to the target of the reference
     * this parameter refers to. If any profiles are specified, then the content must
     * conform to at least one of them. The URL can be a local reference - to a
     * contained StructureDefinition, or a reference to another StructureDefinition or
     * Implementation Guide by a canonical URL. When an implementation guide is
     * specified, the target resource SHALL conform to at least one profile defined in
     * the implementation guide.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical>
     */
    #[FHIRCanonical]
    protected array $targetProfile;
    /**
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * How the parameter is understood as a search parameter. This is only used if the
     * parameter type is 'string'.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRSearchParamType
     */
    #[FHIRSearchParamType]
    protected FHIRSearchParamType $searchType;
    /**
     * A formal computable definition of an operation (on the RESTful interface) or a
     * named query (using the search interaction).
     *
     * Binds to a value set if this parameter is coded (code, Coding, CodeableConcept).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIROperationDefinition\FHIROperationDefinitionBinding
     */
    #[FHIROperationDefinitionBinding]
    protected FHIROperationDefinitionBinding $binding;
    /**
     * A formal computable definition of an operation (on the RESTful interface) or a
     * named query (using the search interaction).
     *
     * Identifies other resource parameters within the operation invocation that are
     * expected to resolve to this resource.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIROperationDefinition\FHIROperationDefinitionReferencedFrom>
     */
    #[FHIROperationDefinitionReferencedFrom]
    protected array $referencedFrom;
    /**
     * A formal computable definition of an operation (on the RESTful interface) or a
     * named query (using the search interaction).
     *
     * The parts of a nested Parameter.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIROperationDefinition\FHIROperationDefinitionParameter>
     */
    #[FHIROperationDefinitionParameter]
    protected array $part;

    /* constructor.php:61 */
    /**
     * FHIROperationDefinitionParameter Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $name
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIROperationParameterUseList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIROperationParameterUse $use
     * @param null|string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger $min
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $max
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $documentation
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $type
     * @param null|iterable<string>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical> $targetProfile
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRSearchParamTypeList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRSearchParamType $searchType
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIROperationDefinition\FHIROperationDefinitionBinding $binding
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIROperationDefinition\FHIROperationDefinitionReferencedFrom> $referencedFrom
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIROperationDefinition\FHIROperationDefinitionParameter> $part
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|string|FHIRCodePrimitive|FHIRCode $name = null,
                                null|string|FHIROperationParameterUseList|FHIROperationParameterUse $use = null,
                                null|string|float|FHIRIntegerPrimitive|FHIRInteger $min = null,
                                null|string|FHIRStringPrimitive|FHIRString $max = null,
                                null|string|FHIRStringPrimitive|FHIRString $documentation = null,
                                null|string|FHIRCodePrimitive|FHIRCode $type = null,
                                null|iterable $targetProfile = null,
                                null|string|FHIRSearchParamTypeList|FHIRSearchParamType $searchType = null,
                                null|FHIROperationDefinitionBinding $binding = null,
                                null|iterable $referencedFrom = null,
                                null|iterable $part = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $name) {
            $this->setName($name);
        }
        if (null !== $use) {
            $this->setUse($use);
        }
        if (null !== $min) {
            $this->setMin($min);
        }
        if (null !== $max) {
            $this->setMax($max);
        }
        if (null !== $documentation) {
            $this->setDocumentation($documentation);
        }
        if (null !== $type) {
            $this->setType($type);
        }
        if (null !== $targetProfile) {
            $this->setTargetProfile(...$targetProfile);
        }
        if (null !== $searchType) {
            $this->setSearchType($searchType);
        }
        if (null !== $binding) {
            $this->setBinding($binding);
        }
        if (null !== $referencedFrom) {
            $this->setReferencedFrom(...$referencedFrom);
        }
        if (null !== $part) {
            $this->setPart(...$part);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * A string which has at least one character and no leading or trailing whitespace
     * and where there is no whitespace other than single spaces in the contents
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The name of used to identify the parameter.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode
     */
    public function getName(): null|FHIRCode
    {
        return $this->name ?? null;
    }

    /**
     * A string which has at least one character and no leading or trailing whitespace
     * and where there is no whitespace other than single spaces in the contents
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The name of used to identify the parameter.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $name
     * @return static
     */
    public function setName(null|string|FHIRCodePrimitive|FHIRCode $name): self
    {
        if (null === $name) {
            unset($this->name);
            return $this;
        }
        if (!($name instanceof FHIRCode)) {
            $name = new FHIRCode(value: $name);
        }
        $this->name = $name;
        return $this;
    }

    /**
     * Whether an operation parameter is an input or an output parameter.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Whether this is an input or an output parameter.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIROperationParameterUse
     */
    public function getUse(): null|FHIROperationParameterUse
    {
        return $this->use ?? null;
    }

    /**
     * Whether an operation parameter is an input or an output parameter.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Whether this is an input or an output parameter.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIROperationParameterUseList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIROperationParameterUse $use
     * @return static
     */
    public function setUse(null|string|FHIROperationParameterUseList|FHIROperationParameterUse $use): self
    {
        if (null === $use) {
            unset($this->use);
            return $this;
        }
        if (!($use instanceof FHIROperationParameterUse)) {
            $use = new FHIROperationParameterUse(value: $use);
        }
        $this->use = $use;
        return $this;
    }

    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The minimum number of times this parameter SHALL appear in the request or
     * response.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger
     */
    public function getMin(): null|FHIRInteger
    {
        return $this->min ?? null;
    }

    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The minimum number of times this parameter SHALL appear in the request or
     * response.
     *
     * @param null|string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger $min
     * @return static
     */
    public function setMin(null|string|float|FHIRIntegerPrimitive|FHIRInteger $min): self
    {
        if (null === $min) {
            unset($this->min);
            return $this;
        }
        if (!($min instanceof FHIRInteger)) {
            $min = new FHIRInteger(value: $min);
        }
        $this->min = $min;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The maximum number of times this element is permitted to appear in the request
     * or response.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getMax(): null|FHIRString
    {
        return $this->max ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The maximum number of times this element is permitted to appear in the request
     * or response.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $max
     * @return static
     */
    public function setMax(null|string|FHIRStringPrimitive|FHIRString $max): self
    {
        if (null === $max) {
            unset($this->max);
            return $this;
        }
        if (!($max instanceof FHIRString)) {
            $max = new FHIRString(value: $max);
        }
        $this->max = $max;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Describes the meaning or use of this parameter.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getDocumentation(): null|FHIRString
    {
        return $this->documentation ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Describes the meaning or use of this parameter.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $documentation
     * @return static
     */
    public function setDocumentation(null|string|FHIRStringPrimitive|FHIRString $documentation): self
    {
        if (null === $documentation) {
            unset($this->documentation);
            return $this;
        }
        if (!($documentation instanceof FHIRString)) {
            $documentation = new FHIRString(value: $documentation);
        }
        $this->documentation = $documentation;
        return $this;
    }

    /**
     * A string which has at least one character and no leading or trailing whitespace
     * and where there is no whitespace other than single spaces in the contents
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The type for this parameter.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode
     */
    public function getType(): null|FHIRCode
    {
        return $this->type ?? null;
    }

    /**
     * A string which has at least one character and no leading or trailing whitespace
     * and where there is no whitespace other than single spaces in the contents
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The type for this parameter.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $type
     * @return static
     */
    public function setType(null|string|FHIRCodePrimitive|FHIRCode $type): self
    {
        if (null === $type) {
            unset($this->type);
            return $this;
        }
        if (!($type instanceof FHIRCode)) {
            $type = new FHIRCode(value: $type);
        }
        $this->type = $type;
        return $this;
    }

    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Used when the type is "Reference" or "canonical", and identifies a profile
     * structure or implementation Guide that applies to the target of the reference
     * this parameter refers to. If any profiles are specified, then the content must
     * conform to at least one of them. The URL can be a local reference - to a
     * contained StructureDefinition, or a reference to another StructureDefinition or
     * Implementation Guide by a canonical URL. When an implementation guide is
     * specified, the target resource SHALL conform to at least one profile defined in
     * the implementation guide.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical>
     */
    public function getTargetProfile(): array
    {
        return $this->targetProfile ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical>
     */
    public function getTargetProfileIterator(): iterable
    {
        if (!isset($this->targetProfile)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->targetProfile);
    }

    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Used when the type is "Reference" or "canonical", and identifies a profile
     * structure or implementation Guide that applies to the target of the reference
     * this parameter refers to. If any profiles are specified, then the content must
     * conform to at least one of them. The URL can be a local reference - to a
     * contained StructureDefinition, or a reference to another StructureDefinition or
     * Implementation Guide by a canonical URL. When an implementation guide is
     * specified, the target resource SHALL conform to at least one profile defined in
     * the implementation guide.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical $targetProfile
     * @return static
     */
    public function addTargetProfile(string|FHIRCanonicalPrimitive|FHIRCanonical $targetProfile): self
    {
        if (!($targetProfile instanceof FHIRCanonical)) {
            $targetProfile = new FHIRCanonical(value: $targetProfile);
        }
        if (!isset($this->targetProfile)) {
            $this->targetProfile = [];
        }
        $this->targetProfile[] = $targetProfile;
        return $this;
    }

    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Used when the type is "Reference" or "canonical", and identifies a profile
     * structure or implementation Guide that applies to the target of the reference
     * this parameter refers to. If any profiles are specified, then the content must
     * conform to at least one of them. The URL can be a local reference - to a
     * contained StructureDefinition, or a reference to another StructureDefinition or
     * Implementation Guide by a canonical URL. When an implementation guide is
     * specified, the target resource SHALL conform to at least one profile defined in
     * the implementation guide.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical ...$targetProfile
     * @return static
     */
    public function setTargetProfile(string|FHIRCanonicalPrimitive|FHIRCanonical ...$targetProfile): self
    {
        if ([] === $targetProfile) {
            unset($this->targetProfile);
            return $this;
        }
        $this->targetProfile = [];
        foreach($targetProfile as $v) {
            if ($v instanceof FHIRCanonical) {
                $this->targetProfile[] = $v;
            } else {
                $this->targetProfile[] = new FHIRCanonical(value: $v);
            }
        }
        return $this;
    }

    /**
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * How the parameter is understood as a search parameter. This is only used if the
     * parameter type is 'string'.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRSearchParamType
     */
    public function getSearchType(): null|FHIRSearchParamType
    {
        return $this->searchType ?? null;
    }

    /**
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * How the parameter is understood as a search parameter. This is only used if the
     * parameter type is 'string'.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRSearchParamTypeList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRSearchParamType $searchType
     * @return static
     */
    public function setSearchType(null|string|FHIRSearchParamTypeList|FHIRSearchParamType $searchType): self
    {
        if (null === $searchType) {
            unset($this->searchType);
            return $this;
        }
        if (!($searchType instanceof FHIRSearchParamType)) {
            $searchType = new FHIRSearchParamType(value: $searchType);
        }
        $this->searchType = $searchType;
        return $this;
    }

    /**
     * A formal computable definition of an operation (on the RESTful interface) or a
     * named query (using the search interaction).
     *
     * Binds to a value set if this parameter is coded (code, Coding, CodeableConcept).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIROperationDefinition\FHIROperationDefinitionBinding
     */
    public function getBinding(): null|FHIROperationDefinitionBinding
    {
        return $this->binding ?? null;
    }

    /**
     * A formal computable definition of an operation (on the RESTful interface) or a
     * named query (using the search interaction).
     *
     * Binds to a value set if this parameter is coded (code, Coding, CodeableConcept).
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIROperationDefinition\FHIROperationDefinitionBinding $binding
     * @return static
     */
    public function setBinding(null|FHIROperationDefinitionBinding $binding): self
    {
        if (null === $binding) {
            unset($this->binding);
            return $this;
        }
        $this->binding = $binding;
        return $this;
    }

    /**
     * A formal computable definition of an operation (on the RESTful interface) or a
     * named query (using the search interaction).
     *
     * Identifies other resource parameters within the operation invocation that are
     * expected to resolve to this resource.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIROperationDefinition\FHIROperationDefinitionReferencedFrom>
     */
    public function getReferencedFrom(): array
    {
        return $this->referencedFrom ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIROperationDefinition\FHIROperationDefinitionReferencedFrom>
     */
    public function getReferencedFromIterator(): iterable
    {
        if (!isset($this->referencedFrom)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->referencedFrom);
    }

    /**
     * A formal computable definition of an operation (on the RESTful interface) or a
     * named query (using the search interaction).
     *
     * Identifies other resource parameters within the operation invocation that are
     * expected to resolve to this resource.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIROperationDefinition\FHIROperationDefinitionReferencedFrom $referencedFrom
     * @return static
     */
    public function addReferencedFrom(FHIROperationDefinitionReferencedFrom $referencedFrom): self
    {
        if (!isset($this->referencedFrom)) {
            $this->referencedFrom = [];
        }
        $this->referencedFrom[] = $referencedFrom;
        return $this;
    }

    /**
     * A formal computable definition of an operation (on the RESTful interface) or a
     * named query (using the search interaction).
     *
     * Identifies other resource parameters within the operation invocation that are
     * expected to resolve to this resource.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIROperationDefinition\FHIROperationDefinitionReferencedFrom ...$referencedFrom
     * @return static
     */
    public function setReferencedFrom(FHIROperationDefinitionReferencedFrom ...$referencedFrom): self
    {
        if ([] === $referencedFrom) {
            unset($this->referencedFrom);
            return $this;
        }
        $this->referencedFrom = $referencedFrom;
        return $this;
    }

    /**
     * A formal computable definition of an operation (on the RESTful interface) or a
     * named query (using the search interaction).
     *
     * The parts of a nested Parameter.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIROperationDefinition\FHIROperationDefinitionParameter>
     */
    public function getPart(): array
    {
        return $this->part ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIROperationDefinition\FHIROperationDefinitionParameter>
     */
    public function getPartIterator(): iterable
    {
        if (!isset($this->part)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->part);
    }

    /**
     * A formal computable definition of an operation (on the RESTful interface) or a
     * named query (using the search interaction).
     *
     * The parts of a nested Parameter.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIROperationDefinition\FHIROperationDefinitionParameter $part
     * @return static
     */
    public function addPart(FHIROperationDefinitionParameter $part): self
    {
        if (!isset($this->part)) {
            $this->part = [];
        }
        $this->part[] = $part;
        return $this;
    }

    /**
     * A formal computable definition of an operation (on the RESTful interface) or a
     * named query (using the search interaction).
     *
     * The parts of a nested Parameter.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIROperationDefinition\FHIROperationDefinitionParameter ...$part
     * @return static
     */
    public function setPart(FHIROperationDefinitionParameter ...$part): self
    {
        if ([] === $part) {
            unset($this->part);
            return $this;
        }
        $this->part = $part;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIROperationDefinition\FHIROperationDefinitionParameter $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIROperationDefinition\FHIROperationDefinitionParameter
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIROperationDefinitionParameter)) {
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
            } else if (self::FIELD_NAME === $cen) {
                $type->setName(FHIRCode::xmlUnserialize($ce, $config));
            } else if (self::FIELD_USE === $cen) {
                $type->setUse(FHIROperationParameterUse::xmlUnserialize($ce, $config));
            } else if (self::FIELD_MIN === $cen) {
                $type->setMin(FHIRInteger::xmlUnserialize($ce, $config));
            } else if (self::FIELD_MAX === $cen) {
                $type->setMax(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DOCUMENTATION === $cen) {
                $type->setDocumentation(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TYPE === $cen) {
                $type->setType(FHIRCode::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TARGET_PROFILE === $cen) {
                $type->addTargetProfile(FHIRCanonical::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SEARCH_TYPE === $cen) {
                $type->setSearchType(FHIRSearchParamType::xmlUnserialize($ce, $config));
            } else if (self::FIELD_BINDING === $cen) {
                $type->setBinding(FHIROperationDefinitionBinding::xmlUnserialize($ce, $config));
            } else if (self::FIELD_REFERENCED_FROM === $cen) {
                $type->addReferencedFrom(FHIROperationDefinitionReferencedFrom::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PART === $cen) {
                $type->addPart(FHIROperationDefinitionParameter::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_NAME])) {
            if (isset($type->name)) {
                $type->name->setValue((string)$attributes[self::FIELD_NAME]);
            } else {
                $type->setName((string)$attributes[self::FIELD_NAME]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_NAME, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_USE])) {
            if (isset($type->use)) {
                $type->use->setValue((string)$attributes[self::FIELD_USE]);
            } else {
                $type->setUse((string)$attributes[self::FIELD_USE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_USE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_MIN])) {
            if (isset($type->min)) {
                $type->min->setValue((string)$attributes[self::FIELD_MIN]);
            } else {
                $type->setMin((string)$attributes[self::FIELD_MIN]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_MIN, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_MAX])) {
            if (isset($type->max)) {
                $type->max->setValue((string)$attributes[self::FIELD_MAX]);
            } else {
                $type->setMax((string)$attributes[self::FIELD_MAX]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_MAX, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_DOCUMENTATION])) {
            if (isset($type->documentation)) {
                $type->documentation->setValue((string)$attributes[self::FIELD_DOCUMENTATION]);
            } else {
                $type->setDocumentation((string)$attributes[self::FIELD_DOCUMENTATION]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_DOCUMENTATION, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_TYPE])) {
            if (isset($type->type)) {
                $type->type->setValue((string)$attributes[self::FIELD_TYPE]);
            } else {
                $type->setType((string)$attributes[self::FIELD_TYPE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_TYPE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_SEARCH_TYPE])) {
            if (isset($type->searchType)) {
                $type->searchType->setValue((string)$attributes[self::FIELD_SEARCH_TYPE]);
            } else {
                $type->setSearchType((string)$attributes[self::FIELD_SEARCH_TYPE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_SEARCH_TYPE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->name) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_NAME]) {
            $xw->writeAttribute(self::FIELD_NAME, $this->name->_getValueAsString());
        }
        if (isset($this->use) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_USE]) {
            $xw->writeAttribute(self::FIELD_USE, $this->use->_getValueAsString());
        }
        if (isset($this->min) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_MIN]) {
            $xw->writeAttribute(self::FIELD_MIN, $this->min->_getValueAsString());
        }
        if (isset($this->max) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_MAX]) {
            $xw->writeAttribute(self::FIELD_MAX, $this->max->_getValueAsString());
        }
        if (isset($this->documentation) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_DOCUMENTATION]) {
            $xw->writeAttribute(self::FIELD_DOCUMENTATION, $this->documentation->_getValueAsString());
        }
        if (isset($this->type) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_TYPE]) {
            $xw->writeAttribute(self::FIELD_TYPE, $this->type->_getValueAsString());
        }
        if (isset($this->searchType) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_SEARCH_TYPE]) {
            $xw->writeAttribute(self::FIELD_SEARCH_TYPE, $this->searchType->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->name)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_NAME]
                || $this->name->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_NAME);
            $this->name->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_NAME]);
            $xw->endElement();
        }
        if (isset($this->use)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_USE]
                || $this->use->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_USE);
            $this->use->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_USE]);
            $xw->endElement();
        }
        if (isset($this->min)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_MIN]
                || $this->min->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_MIN);
            $this->min->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_MIN]);
            $xw->endElement();
        }
        if (isset($this->max)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_MAX]
                || $this->max->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_MAX);
            $this->max->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_MAX]);
            $xw->endElement();
        }
        if (isset($this->documentation)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_DOCUMENTATION]
                || $this->documentation->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_DOCUMENTATION);
            $this->documentation->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_DOCUMENTATION]);
            $xw->endElement();
        }
        if (isset($this->type)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_TYPE]
                || $this->type->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_TYPE);
            $this->type->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_TYPE]);
            $xw->endElement();
        }
        if (isset($this->targetProfile) && [] !== $this->targetProfile) {
            foreach($this->targetProfile as $v) {
                $xw->startElement(self::FIELD_TARGET_PROFILE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->searchType)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_SEARCH_TYPE]
                || $this->searchType->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_SEARCH_TYPE);
            $this->searchType->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_SEARCH_TYPE]);
            $xw->endElement();
        }
        if (isset($this->binding)) {
            $xw->startElement(self::FIELD_BINDING);
            $this->binding->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->referencedFrom)) {
            foreach ($this->referencedFrom as $v) {
                $xw->startElement(self::FIELD_REFERENCED_FROM);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->part)) {
            foreach ($this->part as $v) {
                $xw->startElement(self::FIELD_PART);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIROperationDefinition\FHIROperationDefinitionParameter $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIROperationDefinition\FHIROperationDefinitionParameter
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
        } else if (!($type instanceof FHIROperationDefinitionParameter)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->name)
            || isset($decoded->_name)
            || property_exists($decoded, self::FIELD_NAME)
            || property_exists($decoded, self::FIELD_NAME_EXT)) {
            $v = $decoded->_name ?? new \stdClass();
            $v->value = $decoded->name ?? null;
            $type->setName(FHIRCode::jsonUnserialize($v, $config));
        }
        if (isset($decoded->use)
            || isset($decoded->_use)
            || property_exists($decoded, self::FIELD_USE)
            || property_exists($decoded, self::FIELD_USE_EXT)) {
            $v = $decoded->_use ?? new \stdClass();
            $v->value = $decoded->use ?? null;
            $type->setUse(FHIROperationParameterUse::jsonUnserialize($v, $config));
        }
        if (isset($decoded->min)
            || isset($decoded->_min)
            || property_exists($decoded, self::FIELD_MIN)
            || property_exists($decoded, self::FIELD_MIN_EXT)) {
            $v = $decoded->_min ?? new \stdClass();
            $v->value = $decoded->min ?? null;
            $type->setMin(FHIRInteger::jsonUnserialize($v, $config));
        }
        if (isset($decoded->max)
            || isset($decoded->_max)
            || property_exists($decoded, self::FIELD_MAX)
            || property_exists($decoded, self::FIELD_MAX_EXT)) {
            $v = $decoded->_max ?? new \stdClass();
            $v->value = $decoded->max ?? null;
            $type->setMax(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->documentation)
            || isset($decoded->_documentation)
            || property_exists($decoded, self::FIELD_DOCUMENTATION)
            || property_exists($decoded, self::FIELD_DOCUMENTATION_EXT)) {
            $v = $decoded->_documentation ?? new \stdClass();
            $v->value = $decoded->documentation ?? null;
            $type->setDocumentation(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->type)
            || isset($decoded->_type)
            || property_exists($decoded, self::FIELD_TYPE)
            || property_exists($decoded, self::FIELD_TYPE_EXT)) {
            $v = $decoded->_type ?? new \stdClass();
            $v->value = $decoded->type ?? null;
            $type->setType(FHIRCode::jsonUnserialize($v, $config));
        }
        if (isset($decoded->targetProfile)
            || isset($decoded->_targetProfile)
            || property_exists($decoded, self::FIELD_TARGET_PROFILE)
            || property_exists($decoded, self::FIELD_TARGET_PROFILE_EXT)) {
            $vals = (array)($decoded->targetProfile ?? []);
            $exts = (array)($decoded->_targetProfile ?? []);
            $valCnt = count($vals);
            $extCnt = count($exts);
            if ($extCnt > $valCnt) {
                $valCnt = $extCnt;
            }
            for ($i = 0; $i < $valCnt; $i++) {
                $v = $exts[$i] ?? new \stdClass();
                $v->value = $vals[$i] ?? null;
                $type->addTargetProfile(FHIRCanonical::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->searchType)
            || isset($decoded->_searchType)
            || property_exists($decoded, self::FIELD_SEARCH_TYPE)
            || property_exists($decoded, self::FIELD_SEARCH_TYPE_EXT)) {
            $v = $decoded->_searchType ?? new \stdClass();
            $v->value = $decoded->searchType ?? null;
            $type->setSearchType(FHIRSearchParamType::jsonUnserialize($v, $config));
        }
        if (isset($decoded->binding) || property_exists($decoded, self::FIELD_BINDING)) {
            if (is_array($decoded->binding)) {
                $type->setBinding(FHIROperationDefinitionBinding::jsonUnserialize(reset($decoded->binding), $config));
            } else {
                $type->setBinding(FHIROperationDefinitionBinding::jsonUnserialize($decoded->binding, $config));
            }
        }
        if (isset($decoded->referencedFrom) || property_exists($decoded, self::FIELD_REFERENCED_FROM)) {
            if (is_object($decoded->referencedFrom)) {
                $vals = [$decoded->referencedFrom];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_REFERENCED_FROM, true);
            } else {
                $vals = $decoded->referencedFrom;
            }
            foreach($vals as $v) {
                $type->addReferencedFrom(FHIROperationDefinitionReferencedFrom::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->part) || property_exists($decoded, self::FIELD_PART)) {
            if (is_object($decoded->part)) {
                $vals = [$decoded->part];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_PART, true);
            } else {
                $vals = $decoded->part;
            }
            foreach($vals as $v) {
                $type->addPart(FHIROperationDefinitionParameter::jsonUnserialize($v, $config));
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
        if (isset($this->name)) {
            if (null !== ($val = $this->name->getValue())) {
                $out->name = $val;
            }
            if ($this->name->_nonValueFieldDefined()) {
                $ext = $this->name->jsonSerialize();
                unset($ext->value);
                $out->_name = $ext;
            }
        }
        if (isset($this->use)) {
            if (null !== ($val = $this->use->getValue())) {
                $out->use = $val;
            }
            if ($this->use->_nonValueFieldDefined()) {
                $ext = $this->use->jsonSerialize();
                unset($ext->value);
                $out->_use = $ext;
            }
        }
        if (isset($this->min)) {
            if (null !== ($val = $this->min->getValue())) {
                $out->min = $val;
            }
            if ($this->min->_nonValueFieldDefined()) {
                $ext = $this->min->jsonSerialize();
                unset($ext->value);
                $out->_min = $ext;
            }
        }
        if (isset($this->max)) {
            if (null !== ($val = $this->max->getValue())) {
                $out->max = $val;
            }
            if ($this->max->_nonValueFieldDefined()) {
                $ext = $this->max->jsonSerialize();
                unset($ext->value);
                $out->_max = $ext;
            }
        }
        if (isset($this->documentation)) {
            if (null !== ($val = $this->documentation->getValue())) {
                $out->documentation = $val;
            }
            if ($this->documentation->_nonValueFieldDefined()) {
                $ext = $this->documentation->jsonSerialize();
                unset($ext->value);
                $out->_documentation = $ext;
            }
        }
        if (isset($this->type)) {
            if (null !== ($val = $this->type->getValue())) {
                $out->type = $val;
            }
            if ($this->type->_nonValueFieldDefined()) {
                $ext = $this->type->jsonSerialize();
                unset($ext->value);
                $out->_type = $ext;
            }
        }
        if (isset($this->targetProfile) && [] !== $this->targetProfile) {
            $vals = [];
            $exts = [];
            $hasVals = false;
            $hasExts = false;
            foreach ($this->targetProfile as $v) {
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
                $out->targetProfile = $vals;
            }
            if ($hasExts) {
                $out->_targetProfile = $exts;
            }
        }
        if (isset($this->searchType)) {
            if (null !== ($val = $this->searchType->getValue())) {
                $out->searchType = $val;
            }
            if ($this->searchType->_nonValueFieldDefined()) {
                $ext = $this->searchType->jsonSerialize();
                unset($ext->value);
                $out->_searchType = $ext;
            }
        }
        if (isset($this->binding)) {
            $out->binding = $this->binding;
        }
        if (isset($this->referencedFrom) && [] !== $this->referencedFrom) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_REFERENCED_FROM) && 1 === count($this->referencedFrom)) {
                $out->referencedFrom = $this->referencedFrom[0];
            } else {
                $out->referencedFrom = $this->referencedFrom;
            }
        }
        if (isset($this->part) && [] !== $this->part) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_PART) && 1 === count($this->part)) {
                $out->part = $this->part[0];
            } else {
                $out->part = $this->part;
            }
        }
        return $out;
    }
}
