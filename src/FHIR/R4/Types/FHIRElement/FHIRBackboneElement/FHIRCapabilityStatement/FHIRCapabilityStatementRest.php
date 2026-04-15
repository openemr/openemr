<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement;

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
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRRestfulCapabilityModeList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRestfulCapabilityMode;
use OpenEMR\FHIR\Versions\R4\Types\FHIRMarkdownPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * A Capability Statement documents a set of capabilities (behaviors) of a FHIR
 * Server for a particular version of FHIR that may be used as a statement of
 * actual server functionality or a statement of required or desired server
 * implementation.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRCapabilityStatementRest extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_CAPABILITY_STATEMENT_DOT_REST;

    /* class_default.php:56 */
    public const FIELD_MODE = 'mode';
    public const FIELD_MODE_EXT = '_mode';
    public const FIELD_DOCUMENTATION = 'documentation';
    public const FIELD_DOCUMENTATION_EXT = '_documentation';
    public const FIELD_SECURITY = 'security';
    public const FIELD_RESOURCE = 'resource';
    public const FIELD_INTERACTION = 'interaction';
    public const FIELD_SEARCH_PARAM = 'searchParam';
    public const FIELD_OPERATION = 'operation';
    public const FIELD_COMPARTMENT = 'compartment';
    public const FIELD_COMPARTMENT_EXT = '_compartment';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_MODE => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_MODE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_DOCUMENTATION => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * The mode of a RESTful capability statement.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Identifies whether this portion of the statement is describing the ability to
     * initiate or receive restful operations.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRestfulCapabilityMode
     */
    #[FHIRRestfulCapabilityMode]
    protected FHIRRestfulCapabilityMode $mode;
    /**
     * A string that may contain Github Flavored Markdown syntax for optional
     * processing by a mark down presentation engine
     * Systems are not required to have markdown support, so the text should be
     * readable without markdown processing. The markdown syntax is GFM - see
     * https://github.github.com/gfm/
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Information about the system's restful capabilities that apply across all
     * applications, such as security.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown
     */
    #[FHIRMarkdown]
    protected FHIRMarkdown $documentation;
    /**
     * A Capability Statement documents a set of capabilities (behaviors) of a FHIR
     * Server for a particular version of FHIR that may be used as a statement of
     * actual server functionality or a statement of required or desired server
     * implementation.
     *
     * Information about security implementation from an interface perspective - what a
     * client needs to know.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementSecurity
     */
    #[FHIRCapabilityStatementSecurity]
    protected FHIRCapabilityStatementSecurity $security;
    /**
     * A Capability Statement documents a set of capabilities (behaviors) of a FHIR
     * Server for a particular version of FHIR that may be used as a statement of
     * actual server functionality or a statement of required or desired server
     * implementation.
     *
     * A specification of the restful capabilities of the solution for a specific
     * resource type.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementResource>
     */
    #[FHIRCapabilityStatementResource]
    protected array $resource;
    /**
     * A Capability Statement documents a set of capabilities (behaviors) of a FHIR
     * Server for a particular version of FHIR that may be used as a statement of
     * actual server functionality or a statement of required or desired server
     * implementation.
     *
     * A specification of restful operations supported by the system.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementInteraction1>
     */
    #[FHIRCapabilityStatementInteraction1]
    protected array $interaction;
    /**
     * A Capability Statement documents a set of capabilities (behaviors) of a FHIR
     * Server for a particular version of FHIR that may be used as a statement of
     * actual server functionality or a statement of required or desired server
     * implementation.
     *
     * Search parameters that are supported for searching all resources for
     * implementations to support and/or make use of - either references to ones
     * defined in the specification, or additional ones defined for/by the
     * implementation.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementSearchParam>
     */
    #[FHIRCapabilityStatementSearchParam]
    protected array $searchParam;
    /**
     * A Capability Statement documents a set of capabilities (behaviors) of a FHIR
     * Server for a particular version of FHIR that may be used as a statement of
     * actual server functionality or a statement of required or desired server
     * implementation.
     *
     * Definition of an operation or a named query together with its parameters and
     * their meaning and type.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementOperation>
     */
    #[FHIRCapabilityStatementOperation]
    protected array $operation;
    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * An absolute URI which is a reference to the definition of a compartment that the
     * system supports. The reference is to a CompartmentDefinition resource by its
     * canonical URL .
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical>
     */
    #[FHIRCanonical]
    protected array $compartment;

    /* constructor.php:61 */
    /**
     * FHIRCapabilityStatementRest Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRRestfulCapabilityModeList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRestfulCapabilityMode $mode
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRMarkdownPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown $documentation
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementSecurity $security
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementResource> $resource
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementInteraction1> $interaction
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementSearchParam> $searchParam
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementOperation> $operation
     * @param null|iterable<string>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical> $compartment
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|string|FHIRRestfulCapabilityModeList|FHIRRestfulCapabilityMode $mode = null,
                                null|string|FHIRMarkdownPrimitive|FHIRMarkdown $documentation = null,
                                null|FHIRCapabilityStatementSecurity $security = null,
                                null|iterable $resource = null,
                                null|iterable $interaction = null,
                                null|iterable $searchParam = null,
                                null|iterable $operation = null,
                                null|iterable $compartment = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $mode) {
            $this->setMode($mode);
        }
        if (null !== $documentation) {
            $this->setDocumentation($documentation);
        }
        if (null !== $security) {
            $this->setSecurity($security);
        }
        if (null !== $resource) {
            $this->setResource(...$resource);
        }
        if (null !== $interaction) {
            $this->setInteraction(...$interaction);
        }
        if (null !== $searchParam) {
            $this->setSearchParam(...$searchParam);
        }
        if (null !== $operation) {
            $this->setOperation(...$operation);
        }
        if (null !== $compartment) {
            $this->setCompartment(...$compartment);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * The mode of a RESTful capability statement.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Identifies whether this portion of the statement is describing the ability to
     * initiate or receive restful operations.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRestfulCapabilityMode
     */
    public function getMode(): null|FHIRRestfulCapabilityMode
    {
        return $this->mode ?? null;
    }

    /**
     * The mode of a RESTful capability statement.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Identifies whether this portion of the statement is describing the ability to
     * initiate or receive restful operations.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRRestfulCapabilityModeList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRestfulCapabilityMode $mode
     * @return static
     */
    public function setMode(null|string|FHIRRestfulCapabilityModeList|FHIRRestfulCapabilityMode $mode): self
    {
        if (null === $mode) {
            unset($this->mode);
            return $this;
        }
        if (!($mode instanceof FHIRRestfulCapabilityMode)) {
            $mode = new FHIRRestfulCapabilityMode(value: $mode);
        }
        $this->mode = $mode;
        return $this;
    }

    /**
     * A string that may contain Github Flavored Markdown syntax for optional
     * processing by a mark down presentation engine
     * Systems are not required to have markdown support, so the text should be
     * readable without markdown processing. The markdown syntax is GFM - see
     * https://github.github.com/gfm/
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Information about the system's restful capabilities that apply across all
     * applications, such as security.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown
     */
    public function getDocumentation(): null|FHIRMarkdown
    {
        return $this->documentation ?? null;
    }

    /**
     * A string that may contain Github Flavored Markdown syntax for optional
     * processing by a mark down presentation engine
     * Systems are not required to have markdown support, so the text should be
     * readable without markdown processing. The markdown syntax is GFM - see
     * https://github.github.com/gfm/
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Information about the system's restful capabilities that apply across all
     * applications, such as security.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRMarkdownPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown $documentation
     * @return static
     */
    public function setDocumentation(null|string|FHIRMarkdownPrimitive|FHIRMarkdown $documentation): self
    {
        if (null === $documentation) {
            unset($this->documentation);
            return $this;
        }
        if (!($documentation instanceof FHIRMarkdown)) {
            $documentation = new FHIRMarkdown(value: $documentation);
        }
        $this->documentation = $documentation;
        return $this;
    }

    /**
     * A Capability Statement documents a set of capabilities (behaviors) of a FHIR
     * Server for a particular version of FHIR that may be used as a statement of
     * actual server functionality or a statement of required or desired server
     * implementation.
     *
     * Information about security implementation from an interface perspective - what a
     * client needs to know.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementSecurity
     */
    public function getSecurity(): null|FHIRCapabilityStatementSecurity
    {
        return $this->security ?? null;
    }

    /**
     * A Capability Statement documents a set of capabilities (behaviors) of a FHIR
     * Server for a particular version of FHIR that may be used as a statement of
     * actual server functionality or a statement of required or desired server
     * implementation.
     *
     * Information about security implementation from an interface perspective - what a
     * client needs to know.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementSecurity $security
     * @return static
     */
    public function setSecurity(null|FHIRCapabilityStatementSecurity $security): self
    {
        if (null === $security) {
            unset($this->security);
            return $this;
        }
        $this->security = $security;
        return $this;
    }

    /**
     * A Capability Statement documents a set of capabilities (behaviors) of a FHIR
     * Server for a particular version of FHIR that may be used as a statement of
     * actual server functionality or a statement of required or desired server
     * implementation.
     *
     * A specification of the restful capabilities of the solution for a specific
     * resource type.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementResource>
     */
    public function getResource(): array
    {
        return $this->resource ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementResource>
     */
    public function getResourceIterator(): iterable
    {
        if (!isset($this->resource)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->resource);
    }

    /**
     * A Capability Statement documents a set of capabilities (behaviors) of a FHIR
     * Server for a particular version of FHIR that may be used as a statement of
     * actual server functionality or a statement of required or desired server
     * implementation.
     *
     * A specification of the restful capabilities of the solution for a specific
     * resource type.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementResource $resource
     * @return static
     */
    public function addResource(FHIRCapabilityStatementResource $resource): self
    {
        if (!isset($this->resource)) {
            $this->resource = [];
        }
        $this->resource[] = $resource;
        return $this;
    }

    /**
     * A Capability Statement documents a set of capabilities (behaviors) of a FHIR
     * Server for a particular version of FHIR that may be used as a statement of
     * actual server functionality or a statement of required or desired server
     * implementation.
     *
     * A specification of the restful capabilities of the solution for a specific
     * resource type.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementResource ...$resource
     * @return static
     */
    public function setResource(FHIRCapabilityStatementResource ...$resource): self
    {
        if ([] === $resource) {
            unset($this->resource);
            return $this;
        }
        $this->resource = $resource;
        return $this;
    }

    /**
     * A Capability Statement documents a set of capabilities (behaviors) of a FHIR
     * Server for a particular version of FHIR that may be used as a statement of
     * actual server functionality or a statement of required or desired server
     * implementation.
     *
     * A specification of restful operations supported by the system.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementInteraction1>
     */
    public function getInteraction(): array
    {
        return $this->interaction ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementInteraction1>
     */
    public function getInteractionIterator(): iterable
    {
        if (!isset($this->interaction)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->interaction);
    }

    /**
     * A Capability Statement documents a set of capabilities (behaviors) of a FHIR
     * Server for a particular version of FHIR that may be used as a statement of
     * actual server functionality or a statement of required or desired server
     * implementation.
     *
     * A specification of restful operations supported by the system.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementInteraction1 $interaction
     * @return static
     */
    public function addInteraction(FHIRCapabilityStatementInteraction1 $interaction): self
    {
        if (!isset($this->interaction)) {
            $this->interaction = [];
        }
        $this->interaction[] = $interaction;
        return $this;
    }

    /**
     * A Capability Statement documents a set of capabilities (behaviors) of a FHIR
     * Server for a particular version of FHIR that may be used as a statement of
     * actual server functionality or a statement of required or desired server
     * implementation.
     *
     * A specification of restful operations supported by the system.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementInteraction1 ...$interaction
     * @return static
     */
    public function setInteraction(FHIRCapabilityStatementInteraction1 ...$interaction): self
    {
        if ([] === $interaction) {
            unset($this->interaction);
            return $this;
        }
        $this->interaction = $interaction;
        return $this;
    }

    /**
     * A Capability Statement documents a set of capabilities (behaviors) of a FHIR
     * Server for a particular version of FHIR that may be used as a statement of
     * actual server functionality or a statement of required or desired server
     * implementation.
     *
     * Search parameters that are supported for searching all resources for
     * implementations to support and/or make use of - either references to ones
     * defined in the specification, or additional ones defined for/by the
     * implementation.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementSearchParam>
     */
    public function getSearchParam(): array
    {
        return $this->searchParam ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementSearchParam>
     */
    public function getSearchParamIterator(): iterable
    {
        if (!isset($this->searchParam)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->searchParam);
    }

    /**
     * A Capability Statement documents a set of capabilities (behaviors) of a FHIR
     * Server for a particular version of FHIR that may be used as a statement of
     * actual server functionality or a statement of required or desired server
     * implementation.
     *
     * Search parameters that are supported for searching all resources for
     * implementations to support and/or make use of - either references to ones
     * defined in the specification, or additional ones defined for/by the
     * implementation.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementSearchParam $searchParam
     * @return static
     */
    public function addSearchParam(FHIRCapabilityStatementSearchParam $searchParam): self
    {
        if (!isset($this->searchParam)) {
            $this->searchParam = [];
        }
        $this->searchParam[] = $searchParam;
        return $this;
    }

    /**
     * A Capability Statement documents a set of capabilities (behaviors) of a FHIR
     * Server for a particular version of FHIR that may be used as a statement of
     * actual server functionality or a statement of required or desired server
     * implementation.
     *
     * Search parameters that are supported for searching all resources for
     * implementations to support and/or make use of - either references to ones
     * defined in the specification, or additional ones defined for/by the
     * implementation.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementSearchParam ...$searchParam
     * @return static
     */
    public function setSearchParam(FHIRCapabilityStatementSearchParam ...$searchParam): self
    {
        if ([] === $searchParam) {
            unset($this->searchParam);
            return $this;
        }
        $this->searchParam = $searchParam;
        return $this;
    }

    /**
     * A Capability Statement documents a set of capabilities (behaviors) of a FHIR
     * Server for a particular version of FHIR that may be used as a statement of
     * actual server functionality or a statement of required or desired server
     * implementation.
     *
     * Definition of an operation or a named query together with its parameters and
     * their meaning and type.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementOperation>
     */
    public function getOperation(): array
    {
        return $this->operation ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementOperation>
     */
    public function getOperationIterator(): iterable
    {
        if (!isset($this->operation)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->operation);
    }

    /**
     * A Capability Statement documents a set of capabilities (behaviors) of a FHIR
     * Server for a particular version of FHIR that may be used as a statement of
     * actual server functionality or a statement of required or desired server
     * implementation.
     *
     * Definition of an operation or a named query together with its parameters and
     * their meaning and type.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementOperation $operation
     * @return static
     */
    public function addOperation(FHIRCapabilityStatementOperation $operation): self
    {
        if (!isset($this->operation)) {
            $this->operation = [];
        }
        $this->operation[] = $operation;
        return $this;
    }

    /**
     * A Capability Statement documents a set of capabilities (behaviors) of a FHIR
     * Server for a particular version of FHIR that may be used as a statement of
     * actual server functionality or a statement of required or desired server
     * implementation.
     *
     * Definition of an operation or a named query together with its parameters and
     * their meaning and type.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementOperation ...$operation
     * @return static
     */
    public function setOperation(FHIRCapabilityStatementOperation ...$operation): self
    {
        if ([] === $operation) {
            unset($this->operation);
            return $this;
        }
        $this->operation = $operation;
        return $this;
    }

    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * An absolute URI which is a reference to the definition of a compartment that the
     * system supports. The reference is to a CompartmentDefinition resource by its
     * canonical URL .
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical>
     */
    public function getCompartment(): array
    {
        return $this->compartment ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical>
     */
    public function getCompartmentIterator(): iterable
    {
        if (!isset($this->compartment)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->compartment);
    }

    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * An absolute URI which is a reference to the definition of a compartment that the
     * system supports. The reference is to a CompartmentDefinition resource by its
     * canonical URL .
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical $compartment
     * @return static
     */
    public function addCompartment(string|FHIRCanonicalPrimitive|FHIRCanonical $compartment): self
    {
        if (!($compartment instanceof FHIRCanonical)) {
            $compartment = new FHIRCanonical(value: $compartment);
        }
        if (!isset($this->compartment)) {
            $this->compartment = [];
        }
        $this->compartment[] = $compartment;
        return $this;
    }

    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * An absolute URI which is a reference to the definition of a compartment that the
     * system supports. The reference is to a CompartmentDefinition resource by its
     * canonical URL .
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical ...$compartment
     * @return static
     */
    public function setCompartment(string|FHIRCanonicalPrimitive|FHIRCanonical ...$compartment): self
    {
        if ([] === $compartment) {
            unset($this->compartment);
            return $this;
        }
        $this->compartment = [];
        foreach($compartment as $v) {
            if ($v instanceof FHIRCanonical) {
                $this->compartment[] = $v;
            } else {
                $this->compartment[] = new FHIRCanonical(value: $v);
            }
        }
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementRest $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementRest
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRCapabilityStatementRest)) {
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
            } else if (self::FIELD_MODE === $cen) {
                $type->setMode(FHIRRestfulCapabilityMode::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DOCUMENTATION === $cen) {
                $type->setDocumentation(FHIRMarkdown::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SECURITY === $cen) {
                $type->setSecurity(FHIRCapabilityStatementSecurity::xmlUnserialize($ce, $config));
            } else if (self::FIELD_RESOURCE === $cen) {
                $type->addResource(FHIRCapabilityStatementResource::xmlUnserialize($ce, $config));
            } else if (self::FIELD_INTERACTION === $cen) {
                $type->addInteraction(FHIRCapabilityStatementInteraction1::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SEARCH_PARAM === $cen) {
                $type->addSearchParam(FHIRCapabilityStatementSearchParam::xmlUnserialize($ce, $config));
            } else if (self::FIELD_OPERATION === $cen) {
                $type->addOperation(FHIRCapabilityStatementOperation::xmlUnserialize($ce, $config));
            } else if (self::FIELD_COMPARTMENT === $cen) {
                $type->addCompartment(FHIRCanonical::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_MODE])) {
            if (isset($type->mode)) {
                $type->mode->setValue((string)$attributes[self::FIELD_MODE]);
            } else {
                $type->setMode((string)$attributes[self::FIELD_MODE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_MODE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_DOCUMENTATION])) {
            if (isset($type->documentation)) {
                $type->documentation->setValue((string)$attributes[self::FIELD_DOCUMENTATION]);
            } else {
                $type->setDocumentation((string)$attributes[self::FIELD_DOCUMENTATION]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_DOCUMENTATION, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->mode) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_MODE]) {
            $xw->writeAttribute(self::FIELD_MODE, $this->mode->_getValueAsString());
        }
        if (isset($this->documentation) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_DOCUMENTATION]) {
            $xw->writeAttribute(self::FIELD_DOCUMENTATION, $this->documentation->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->mode)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_MODE]
                || $this->mode->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_MODE);
            $this->mode->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_MODE]);
            $xw->endElement();
        }
        if (isset($this->documentation)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_DOCUMENTATION]
                || $this->documentation->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_DOCUMENTATION);
            $this->documentation->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_DOCUMENTATION]);
            $xw->endElement();
        }
        if (isset($this->security)) {
            $xw->startElement(self::FIELD_SECURITY);
            $this->security->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->resource)) {
            foreach ($this->resource as $v) {
                $xw->startElement(self::FIELD_RESOURCE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->interaction)) {
            foreach ($this->interaction as $v) {
                $xw->startElement(self::FIELD_INTERACTION);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->searchParam)) {
            foreach ($this->searchParam as $v) {
                $xw->startElement(self::FIELD_SEARCH_PARAM);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->operation)) {
            foreach ($this->operation as $v) {
                $xw->startElement(self::FIELD_OPERATION);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->compartment) && [] !== $this->compartment) {
            foreach($this->compartment as $v) {
                $xw->startElement(self::FIELD_COMPARTMENT);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementRest $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementRest
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
        } else if (!($type instanceof FHIRCapabilityStatementRest)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->mode)
            || isset($decoded->_mode)
            || property_exists($decoded, self::FIELD_MODE)
            || property_exists($decoded, self::FIELD_MODE_EXT)) {
            $v = $decoded->_mode ?? new \stdClass();
            $v->value = $decoded->mode ?? null;
            $type->setMode(FHIRRestfulCapabilityMode::jsonUnserialize($v, $config));
        }
        if (isset($decoded->documentation)
            || isset($decoded->_documentation)
            || property_exists($decoded, self::FIELD_DOCUMENTATION)
            || property_exists($decoded, self::FIELD_DOCUMENTATION_EXT)) {
            $v = $decoded->_documentation ?? new \stdClass();
            $v->value = $decoded->documentation ?? null;
            $type->setDocumentation(FHIRMarkdown::jsonUnserialize($v, $config));
        }
        if (isset($decoded->security) || property_exists($decoded, self::FIELD_SECURITY)) {
            if (is_array($decoded->security)) {
                $type->setSecurity(FHIRCapabilityStatementSecurity::jsonUnserialize(reset($decoded->security), $config));
            } else {
                $type->setSecurity(FHIRCapabilityStatementSecurity::jsonUnserialize($decoded->security, $config));
            }
        }
        if (isset($decoded->resource) || property_exists($decoded, self::FIELD_RESOURCE)) {
            if (is_object($decoded->resource)) {
                $vals = [$decoded->resource];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_RESOURCE, true);
            } else {
                $vals = $decoded->resource;
            }
            foreach($vals as $v) {
                $type->addResource(FHIRCapabilityStatementResource::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->interaction) || property_exists($decoded, self::FIELD_INTERACTION)) {
            if (is_object($decoded->interaction)) {
                $vals = [$decoded->interaction];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_INTERACTION, true);
            } else {
                $vals = $decoded->interaction;
            }
            foreach($vals as $v) {
                $type->addInteraction(FHIRCapabilityStatementInteraction1::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->searchParam) || property_exists($decoded, self::FIELD_SEARCH_PARAM)) {
            if (is_object($decoded->searchParam)) {
                $vals = [$decoded->searchParam];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_SEARCH_PARAM, true);
            } else {
                $vals = $decoded->searchParam;
            }
            foreach($vals as $v) {
                $type->addSearchParam(FHIRCapabilityStatementSearchParam::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->operation) || property_exists($decoded, self::FIELD_OPERATION)) {
            if (is_object($decoded->operation)) {
                $vals = [$decoded->operation];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_OPERATION, true);
            } else {
                $vals = $decoded->operation;
            }
            foreach($vals as $v) {
                $type->addOperation(FHIRCapabilityStatementOperation::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->compartment)
            || isset($decoded->_compartment)
            || property_exists($decoded, self::FIELD_COMPARTMENT)
            || property_exists($decoded, self::FIELD_COMPARTMENT_EXT)) {
            $vals = (array)($decoded->compartment ?? []);
            $exts = (array)($decoded->_compartment ?? []);
            $valCnt = count($vals);
            $extCnt = count($exts);
            if ($extCnt > $valCnt) {
                $valCnt = $extCnt;
            }
            for ($i = 0; $i < $valCnt; $i++) {
                $v = $exts[$i] ?? new \stdClass();
                $v->value = $vals[$i] ?? null;
                $type->addCompartment(FHIRCanonical::jsonUnserialize($v, $config));
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
        if (isset($this->mode)) {
            if (null !== ($val = $this->mode->getValue())) {
                $out->mode = $val;
            }
            if ($this->mode->_nonValueFieldDefined()) {
                $ext = $this->mode->jsonSerialize();
                unset($ext->value);
                $out->_mode = $ext;
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
        if (isset($this->security)) {
            $out->security = $this->security;
        }
        if (isset($this->resource) && [] !== $this->resource) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_RESOURCE) && 1 === count($this->resource)) {
                $out->resource = $this->resource[0];
            } else {
                $out->resource = $this->resource;
            }
        }
        if (isset($this->interaction) && [] !== $this->interaction) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_INTERACTION) && 1 === count($this->interaction)) {
                $out->interaction = $this->interaction[0];
            } else {
                $out->interaction = $this->interaction;
            }
        }
        if (isset($this->searchParam) && [] !== $this->searchParam) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_SEARCH_PARAM) && 1 === count($this->searchParam)) {
                $out->searchParam = $this->searchParam[0];
            } else {
                $out->searchParam = $this->searchParam;
            }
        }
        if (isset($this->operation) && [] !== $this->operation) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_OPERATION) && 1 === count($this->operation)) {
                $out->operation = $this->operation[0];
            } else {
                $out->operation = $this->operation;
            }
        }
        if (isset($this->compartment) && [] !== $this->compartment) {
            $vals = [];
            $exts = [];
            $hasVals = false;
            $hasExts = false;
            foreach ($this->compartment as $v) {
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
                $out->compartment = $vals;
            }
            if ($hasExts) {
                $out->_compartment = $exts;
            }
        }
        return $out;
    }
}
