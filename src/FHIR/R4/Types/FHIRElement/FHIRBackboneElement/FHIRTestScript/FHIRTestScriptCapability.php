<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestScript;

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
use OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri;
use OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * A structured set of tests against a FHIR server or client implementation to
 * determine compliance against the FHIR specification.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRTestScriptCapability extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_TEST_SCRIPT_DOT_CAPABILITY;

    /* class_default.php:56 */
    public const FIELD_REQUIRED = 'required';
    public const FIELD_REQUIRED_EXT = '_required';
    public const FIELD_VALIDATED = 'validated';
    public const FIELD_VALIDATED_EXT = '_validated';
    public const FIELD_DESCRIPTION = 'description';
    public const FIELD_DESCRIPTION_EXT = '_description';
    public const FIELD_ORIGIN = 'origin';
    public const FIELD_ORIGIN_EXT = '_origin';
    public const FIELD_DESTINATION = 'destination';
    public const FIELD_DESTINATION_EXT = '_destination';
    public const FIELD_LINK = 'link';
    public const FIELD_LINK_EXT = '_link';
    public const FIELD_CAPABILITIES = 'capabilities';
    public const FIELD_CAPABILITIES_EXT = '_capabilities';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_REQUIRED => [
            MinOccursRule::NAME => 1,
        ],
        self::FIELD_VALIDATED => [
            MinOccursRule::NAME => 1,
        ],
        self::FIELD_CAPABILITIES => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_REQUIRED => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_VALIDATED => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_DESCRIPTION => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_DESTINATION => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_CAPABILITIES => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Whether or not the test execution will require the given capabilities of the
     * server in order for this test script to execute.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    #[FHIRBoolean]
    protected FHIRBoolean $required;
    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Whether or not the test execution will validate the given capabilities of the
     * server in order for this test script to execute.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    #[FHIRBoolean]
    protected FHIRBoolean $validated;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Description of the capabilities that this test script is requiring the server to
     * support.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $description;
    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Which origin server these requirements apply to.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger>
     */
    #[FHIRInteger]
    protected array $origin;
    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Which server these requirements apply to.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger
     */
    #[FHIRInteger]
    protected FHIRInteger $destination;
    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Links to the FHIR specification that describes this interaction and the
     * resources involved in more detail.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri>
     */
    #[FHIRUri]
    protected array $link;
    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Minimum capabilities required of server for test script to execute successfully.
     * If server does not meet at a minimum the referenced capability statement, then
     * all tests in this script are skipped.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical
     */
    #[FHIRCanonical]
    protected FHIRCanonical $capabilities;

    /* constructor.php:61 */
    /**
     * FHIRTestScriptCapability Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $required
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $validated
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $description
     * @param null|iterable<string>|iterable<float>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger> $origin
     * @param null|string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger $destination
     * @param null|iterable<string>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri> $link
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical $capabilities
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $required = null,
                                null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $validated = null,
                                null|string|FHIRStringPrimitive|FHIRString $description = null,
                                null|iterable $origin = null,
                                null|string|float|FHIRIntegerPrimitive|FHIRInteger $destination = null,
                                null|iterable $link = null,
                                null|string|FHIRCanonicalPrimitive|FHIRCanonical $capabilities = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $required) {
            $this->setRequired($required);
        }
        if (null !== $validated) {
            $this->setValidated($validated);
        }
        if (null !== $description) {
            $this->setDescription($description);
        }
        if (null !== $origin) {
            $this->setOrigin(...$origin);
        }
        if (null !== $destination) {
            $this->setDestination($destination);
        }
        if (null !== $link) {
            $this->setLink(...$link);
        }
        if (null !== $capabilities) {
            $this->setCapabilities($capabilities);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Whether or not the test execution will require the given capabilities of the
     * server in order for this test script to execute.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    public function getRequired(): null|FHIRBoolean
    {
        return $this->required ?? null;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Whether or not the test execution will require the given capabilities of the
     * server in order for this test script to execute.
     *
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $required
     * @return static
     */
    public function setRequired(null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $required): self
    {
        if (null === $required) {
            unset($this->required);
            return $this;
        }
        if (!($required instanceof FHIRBoolean)) {
            $required = new FHIRBoolean(value: $required);
        }
        $this->required = $required;
        return $this;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Whether or not the test execution will validate the given capabilities of the
     * server in order for this test script to execute.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    public function getValidated(): null|FHIRBoolean
    {
        return $this->validated ?? null;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Whether or not the test execution will validate the given capabilities of the
     * server in order for this test script to execute.
     *
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $validated
     * @return static
     */
    public function setValidated(null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $validated): self
    {
        if (null === $validated) {
            unset($this->validated);
            return $this;
        }
        if (!($validated instanceof FHIRBoolean)) {
            $validated = new FHIRBoolean(value: $validated);
        }
        $this->validated = $validated;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Description of the capabilities that this test script is requiring the server to
     * support.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getDescription(): null|FHIRString
    {
        return $this->description ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Description of the capabilities that this test script is requiring the server to
     * support.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $description
     * @return static
     */
    public function setDescription(null|string|FHIRStringPrimitive|FHIRString $description): self
    {
        if (null === $description) {
            unset($this->description);
            return $this;
        }
        if (!($description instanceof FHIRString)) {
            $description = new FHIRString(value: $description);
        }
        $this->description = $description;
        return $this;
    }

    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Which origin server these requirements apply to.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger>
     */
    public function getOrigin(): array
    {
        return $this->origin ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger>
     */
    public function getOriginIterator(): iterable
    {
        if (!isset($this->origin)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->origin);
    }

    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Which origin server these requirements apply to.
     *
     * @param string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger $origin
     * @return static
     */
    public function addOrigin(string|float|FHIRIntegerPrimitive|FHIRInteger $origin): self
    {
        if (!($origin instanceof FHIRInteger)) {
            $origin = new FHIRInteger(value: $origin);
        }
        if (!isset($this->origin)) {
            $this->origin = [];
        }
        $this->origin[] = $origin;
        return $this;
    }

    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Which origin server these requirements apply to.
     *
     * @param string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger ...$origin
     * @return static
     */
    public function setOrigin(string|float|FHIRIntegerPrimitive|FHIRInteger ...$origin): self
    {
        if ([] === $origin) {
            unset($this->origin);
            return $this;
        }
        $this->origin = [];
        foreach($origin as $v) {
            if ($v instanceof FHIRInteger) {
                $this->origin[] = $v;
            } else {
                $this->origin[] = new FHIRInteger(value: $v);
            }
        }
        return $this;
    }

    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Which server these requirements apply to.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger
     */
    public function getDestination(): null|FHIRInteger
    {
        return $this->destination ?? null;
    }

    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Which server these requirements apply to.
     *
     * @param null|string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger $destination
     * @return static
     */
    public function setDestination(null|string|float|FHIRIntegerPrimitive|FHIRInteger $destination): self
    {
        if (null === $destination) {
            unset($this->destination);
            return $this;
        }
        if (!($destination instanceof FHIRInteger)) {
            $destination = new FHIRInteger(value: $destination);
        }
        $this->destination = $destination;
        return $this;
    }

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Links to the FHIR specification that describes this interaction and the
     * resources involved in more detail.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri>
     */
    public function getLink(): array
    {
        return $this->link ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri>
     */
    public function getLinkIterator(): iterable
    {
        if (!isset($this->link)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->link);
    }

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Links to the FHIR specification that describes this interaction and the
     * resources involved in more detail.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $link
     * @return static
     */
    public function addLink(string|FHIRUriPrimitive|FHIRUri $link): self
    {
        if (!($link instanceof FHIRUri)) {
            $link = new FHIRUri(value: $link);
        }
        if (!isset($this->link)) {
            $this->link = [];
        }
        $this->link[] = $link;
        return $this;
    }

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Links to the FHIR specification that describes this interaction and the
     * resources involved in more detail.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri ...$link
     * @return static
     */
    public function setLink(string|FHIRUriPrimitive|FHIRUri ...$link): self
    {
        if ([] === $link) {
            unset($this->link);
            return $this;
        }
        $this->link = [];
        foreach($link as $v) {
            if ($v instanceof FHIRUri) {
                $this->link[] = $v;
            } else {
                $this->link[] = new FHIRUri(value: $v);
            }
        }
        return $this;
    }

    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Minimum capabilities required of server for test script to execute successfully.
     * If server does not meet at a minimum the referenced capability statement, then
     * all tests in this script are skipped.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical
     */
    public function getCapabilities(): null|FHIRCanonical
    {
        return $this->capabilities ?? null;
    }

    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Minimum capabilities required of server for test script to execute successfully.
     * If server does not meet at a minimum the referenced capability statement, then
     * all tests in this script are skipped.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical $capabilities
     * @return static
     */
    public function setCapabilities(null|string|FHIRCanonicalPrimitive|FHIRCanonical $capabilities): self
    {
        if (null === $capabilities) {
            unset($this->capabilities);
            return $this;
        }
        if (!($capabilities instanceof FHIRCanonical)) {
            $capabilities = new FHIRCanonical(value: $capabilities);
        }
        $this->capabilities = $capabilities;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestScript\FHIRTestScriptCapability $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestScript\FHIRTestScriptCapability
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRTestScriptCapability)) {
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
            } else if (self::FIELD_REQUIRED === $cen) {
                $type->setRequired(FHIRBoolean::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALIDATED === $cen) {
                $type->setValidated(FHIRBoolean::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DESCRIPTION === $cen) {
                $type->setDescription(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ORIGIN === $cen) {
                $type->addOrigin(FHIRInteger::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DESTINATION === $cen) {
                $type->setDestination(FHIRInteger::xmlUnserialize($ce, $config));
            } else if (self::FIELD_LINK === $cen) {
                $type->addLink(FHIRUri::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CAPABILITIES === $cen) {
                $type->setCapabilities(FHIRCanonical::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_REQUIRED])) {
            if (isset($type->required)) {
                $type->required->setValue((string)$attributes[self::FIELD_REQUIRED]);
            } else {
                $type->setRequired((string)$attributes[self::FIELD_REQUIRED]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_REQUIRED, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_VALIDATED])) {
            if (isset($type->validated)) {
                $type->validated->setValue((string)$attributes[self::FIELD_VALIDATED]);
            } else {
                $type->setValidated((string)$attributes[self::FIELD_VALIDATED]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_VALIDATED, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_DESCRIPTION])) {
            if (isset($type->description)) {
                $type->description->setValue((string)$attributes[self::FIELD_DESCRIPTION]);
            } else {
                $type->setDescription((string)$attributes[self::FIELD_DESCRIPTION]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_DESCRIPTION, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_DESTINATION])) {
            if (isset($type->destination)) {
                $type->destination->setValue((string)$attributes[self::FIELD_DESTINATION]);
            } else {
                $type->setDestination((string)$attributes[self::FIELD_DESTINATION]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_DESTINATION, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_CAPABILITIES])) {
            if (isset($type->capabilities)) {
                $type->capabilities->setValue((string)$attributes[self::FIELD_CAPABILITIES]);
            } else {
                $type->setCapabilities((string)$attributes[self::FIELD_CAPABILITIES]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_CAPABILITIES, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->required) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_REQUIRED]) {
            $xw->writeAttribute(self::FIELD_REQUIRED, $this->required->_getValueAsString());
        }
        if (isset($this->validated) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_VALIDATED]) {
            $xw->writeAttribute(self::FIELD_VALIDATED, $this->validated->_getValueAsString());
        }
        if (isset($this->description) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_DESCRIPTION]) {
            $xw->writeAttribute(self::FIELD_DESCRIPTION, $this->description->_getValueAsString());
        }
        if (isset($this->destination) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_DESTINATION]) {
            $xw->writeAttribute(self::FIELD_DESTINATION, $this->destination->_getValueAsString());
        }
        if (isset($this->capabilities) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_CAPABILITIES]) {
            $xw->writeAttribute(self::FIELD_CAPABILITIES, $this->capabilities->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->required)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_REQUIRED]
                || $this->required->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_REQUIRED);
            $this->required->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_REQUIRED]);
            $xw->endElement();
        }
        if (isset($this->validated)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_VALIDATED]
                || $this->validated->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_VALIDATED);
            $this->validated->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_VALIDATED]);
            $xw->endElement();
        }
        if (isset($this->description)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_DESCRIPTION]
                || $this->description->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_DESCRIPTION);
            $this->description->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_DESCRIPTION]);
            $xw->endElement();
        }
        if (isset($this->origin) && [] !== $this->origin) {
            foreach($this->origin as $v) {
                $xw->startElement(self::FIELD_ORIGIN);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->destination)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_DESTINATION]
                || $this->destination->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_DESTINATION);
            $this->destination->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_DESTINATION]);
            $xw->endElement();
        }
        if (isset($this->link) && [] !== $this->link) {
            foreach($this->link as $v) {
                $xw->startElement(self::FIELD_LINK);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->capabilities)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_CAPABILITIES]
                || $this->capabilities->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_CAPABILITIES);
            $this->capabilities->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_CAPABILITIES]);
            $xw->endElement();
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestScript\FHIRTestScriptCapability $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestScript\FHIRTestScriptCapability
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
        } else if (!($type instanceof FHIRTestScriptCapability)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->required)
            || isset($decoded->_required)
            || property_exists($decoded, self::FIELD_REQUIRED)
            || property_exists($decoded, self::FIELD_REQUIRED_EXT)) {
            $v = $decoded->_required ?? new \stdClass();
            $v->value = $decoded->required ?? null;
            $type->setRequired(FHIRBoolean::jsonUnserialize($v, $config));
        }
        if (isset($decoded->validated)
            || isset($decoded->_validated)
            || property_exists($decoded, self::FIELD_VALIDATED)
            || property_exists($decoded, self::FIELD_VALIDATED_EXT)) {
            $v = $decoded->_validated ?? new \stdClass();
            $v->value = $decoded->validated ?? null;
            $type->setValidated(FHIRBoolean::jsonUnserialize($v, $config));
        }
        if (isset($decoded->description)
            || isset($decoded->_description)
            || property_exists($decoded, self::FIELD_DESCRIPTION)
            || property_exists($decoded, self::FIELD_DESCRIPTION_EXT)) {
            $v = $decoded->_description ?? new \stdClass();
            $v->value = $decoded->description ?? null;
            $type->setDescription(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->origin)
            || isset($decoded->_origin)
            || property_exists($decoded, self::FIELD_ORIGIN)
            || property_exists($decoded, self::FIELD_ORIGIN_EXT)) {
            $vals = (array)($decoded->origin ?? []);
            $exts = (array)($decoded->_origin ?? []);
            $valCnt = count($vals);
            $extCnt = count($exts);
            if ($extCnt > $valCnt) {
                $valCnt = $extCnt;
            }
            for ($i = 0; $i < $valCnt; $i++) {
                $v = $exts[$i] ?? new \stdClass();
                $v->value = $vals[$i] ?? null;
                $type->addOrigin(FHIRInteger::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->destination)
            || isset($decoded->_destination)
            || property_exists($decoded, self::FIELD_DESTINATION)
            || property_exists($decoded, self::FIELD_DESTINATION_EXT)) {
            $v = $decoded->_destination ?? new \stdClass();
            $v->value = $decoded->destination ?? null;
            $type->setDestination(FHIRInteger::jsonUnserialize($v, $config));
        }
        if (isset($decoded->link)
            || isset($decoded->_link)
            || property_exists($decoded, self::FIELD_LINK)
            || property_exists($decoded, self::FIELD_LINK_EXT)) {
            $vals = (array)($decoded->link ?? []);
            $exts = (array)($decoded->_link ?? []);
            $valCnt = count($vals);
            $extCnt = count($exts);
            if ($extCnt > $valCnt) {
                $valCnt = $extCnt;
            }
            for ($i = 0; $i < $valCnt; $i++) {
                $v = $exts[$i] ?? new \stdClass();
                $v->value = $vals[$i] ?? null;
                $type->addLink(FHIRUri::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->capabilities)
            || isset($decoded->_capabilities)
            || property_exists($decoded, self::FIELD_CAPABILITIES)
            || property_exists($decoded, self::FIELD_CAPABILITIES_EXT)) {
            $v = $decoded->_capabilities ?? new \stdClass();
            $v->value = $decoded->capabilities ?? null;
            $type->setCapabilities(FHIRCanonical::jsonUnserialize($v, $config));
        }
        return $type;
    }

    /**
     * @return \stdClass
     */
    public function jsonSerialize(): mixed
    {
        $out = parent::jsonSerialize();
        if (isset($this->required)) {
            if (null !== ($val = $this->required->getValue())) {
                $out->required = $val;
            }
            if ($this->required->_nonValueFieldDefined()) {
                $ext = $this->required->jsonSerialize();
                unset($ext->value);
                $out->_required = $ext;
            }
        }
        if (isset($this->validated)) {
            if (null !== ($val = $this->validated->getValue())) {
                $out->validated = $val;
            }
            if ($this->validated->_nonValueFieldDefined()) {
                $ext = $this->validated->jsonSerialize();
                unset($ext->value);
                $out->_validated = $ext;
            }
        }
        if (isset($this->description)) {
            if (null !== ($val = $this->description->getValue())) {
                $out->description = $val;
            }
            if ($this->description->_nonValueFieldDefined()) {
                $ext = $this->description->jsonSerialize();
                unset($ext->value);
                $out->_description = $ext;
            }
        }
        if (isset($this->origin) && [] !== $this->origin) {
            $vals = [];
            $exts = [];
            $hasVals = false;
            $hasExts = false;
            foreach ($this->origin as $v) {
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
                $out->origin = $vals;
            }
            if ($hasExts) {
                $out->_origin = $exts;
            }
        }
        if (isset($this->destination)) {
            if (null !== ($val = $this->destination->getValue())) {
                $out->destination = $val;
            }
            if ($this->destination->_nonValueFieldDefined()) {
                $ext = $this->destination->jsonSerialize();
                unset($ext->value);
                $out->_destination = $ext;
            }
        }
        if (isset($this->link) && [] !== $this->link) {
            $vals = [];
            $exts = [];
            $hasVals = false;
            $hasExts = false;
            foreach ($this->link as $v) {
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
                $out->link = $vals;
            }
            if ($hasExts) {
                $out->_link = $exts;
            }
        }
        if (isset($this->capabilities)) {
            if (null !== ($val = $this->capabilities->getValue())) {
                $out->capabilities = $val;
            }
            if ($this->capabilities->_nonValueFieldDefined()) {
                $ext = $this->capabilities->jsonSerialize();
                unset($ext->value);
                $out->_capabilities = $ext;
            }
        }
        return $out;
    }
}
