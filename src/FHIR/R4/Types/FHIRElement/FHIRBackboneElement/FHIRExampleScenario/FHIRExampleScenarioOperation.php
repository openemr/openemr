<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExampleScenario;

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
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRMarkdownPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * Example of workflow instance.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRExampleScenarioOperation extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_EXAMPLE_SCENARIO_DOT_OPERATION;

    /* class_default.php:56 */
    public const FIELD_NUMBER = 'number';
    public const FIELD_NUMBER_EXT = '_number';
    public const FIELD_TYPE = 'type';
    public const FIELD_TYPE_EXT = '_type';
    public const FIELD_NAME = 'name';
    public const FIELD_NAME_EXT = '_name';
    public const FIELD_INITIATOR = 'initiator';
    public const FIELD_INITIATOR_EXT = '_initiator';
    public const FIELD_RECEIVER = 'receiver';
    public const FIELD_RECEIVER_EXT = '_receiver';
    public const FIELD_DESCRIPTION = 'description';
    public const FIELD_DESCRIPTION_EXT = '_description';
    public const FIELD_INITIATOR_ACTIVE = 'initiatorActive';
    public const FIELD_INITIATOR_ACTIVE_EXT = '_initiatorActive';
    public const FIELD_RECEIVER_ACTIVE = 'receiverActive';
    public const FIELD_RECEIVER_ACTIVE_EXT = '_receiverActive';
    public const FIELD_REQUEST = 'request';
    public const FIELD_RESPONSE = 'response';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_NUMBER => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_NUMBER => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_TYPE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_NAME => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_INITIATOR => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_RECEIVER => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_DESCRIPTION => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_INITIATOR_ACTIVE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_RECEIVER_ACTIVE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The sequential number of the interaction, e.g. 1.2.5.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $number;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The type of operation - CRUD.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $type;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The human-friendly name of the interaction.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $name;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Who starts the transaction.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $initiator;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Who receives the transaction.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $receiver;
    /**
     * A string that may contain Github Flavored Markdown syntax for optional
     * processing by a mark down presentation engine
     * Systems are not required to have markdown support, so the text should be
     * readable without markdown processing. The markdown syntax is GFM - see
     * https://github.github.com/gfm/
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * A comment to be inserted in the diagram.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown
     */
    #[FHIRMarkdown]
    protected FHIRMarkdown $description;
    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Whether the initiator is deactivated right after the transaction.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    #[FHIRBoolean]
    protected FHIRBoolean $initiatorActive;
    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Whether the receiver is deactivated right after the transaction.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    #[FHIRBoolean]
    protected FHIRBoolean $receiverActive;
    /**
     * Example of workflow instance.
     *
     * Each resource instance used by the initiator.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExampleScenario\FHIRExampleScenarioContainedInstance
     */
    #[FHIRExampleScenarioContainedInstance]
    protected FHIRExampleScenarioContainedInstance $request;
    /**
     * Example of workflow instance.
     *
     * Each resource instance used by the responder.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExampleScenario\FHIRExampleScenarioContainedInstance
     */
    #[FHIRExampleScenarioContainedInstance]
    protected FHIRExampleScenarioContainedInstance $response;

    /* constructor.php:61 */
    /**
     * FHIRExampleScenarioOperation Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $number
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $type
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $name
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $initiator
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $receiver
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRMarkdownPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown $description
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $initiatorActive
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $receiverActive
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExampleScenario\FHIRExampleScenarioContainedInstance $request
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExampleScenario\FHIRExampleScenarioContainedInstance $response
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|string|FHIRStringPrimitive|FHIRString $number = null,
                                null|string|FHIRStringPrimitive|FHIRString $type = null,
                                null|string|FHIRStringPrimitive|FHIRString $name = null,
                                null|string|FHIRStringPrimitive|FHIRString $initiator = null,
                                null|string|FHIRStringPrimitive|FHIRString $receiver = null,
                                null|string|FHIRMarkdownPrimitive|FHIRMarkdown $description = null,
                                null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $initiatorActive = null,
                                null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $receiverActive = null,
                                null|FHIRExampleScenarioContainedInstance $request = null,
                                null|FHIRExampleScenarioContainedInstance $response = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $number) {
            $this->setNumber($number);
        }
        if (null !== $type) {
            $this->setType($type);
        }
        if (null !== $name) {
            $this->setName($name);
        }
        if (null !== $initiator) {
            $this->setInitiator($initiator);
        }
        if (null !== $receiver) {
            $this->setReceiver($receiver);
        }
        if (null !== $description) {
            $this->setDescription($description);
        }
        if (null !== $initiatorActive) {
            $this->setInitiatorActive($initiatorActive);
        }
        if (null !== $receiverActive) {
            $this->setReceiverActive($receiverActive);
        }
        if (null !== $request) {
            $this->setRequest($request);
        }
        if (null !== $response) {
            $this->setResponse($response);
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
     * The sequential number of the interaction, e.g. 1.2.5.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getNumber(): null|FHIRString
    {
        return $this->number ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The sequential number of the interaction, e.g. 1.2.5.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $number
     * @return static
     */
    public function setNumber(null|string|FHIRStringPrimitive|FHIRString $number): self
    {
        if (null === $number) {
            unset($this->number);
            return $this;
        }
        if (!($number instanceof FHIRString)) {
            $number = new FHIRString(value: $number);
        }
        $this->number = $number;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The type of operation - CRUD.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getType(): null|FHIRString
    {
        return $this->type ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The type of operation - CRUD.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $type
     * @return static
     */
    public function setType(null|string|FHIRStringPrimitive|FHIRString $type): self
    {
        if (null === $type) {
            unset($this->type);
            return $this;
        }
        if (!($type instanceof FHIRString)) {
            $type = new FHIRString(value: $type);
        }
        $this->type = $type;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The human-friendly name of the interaction.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getName(): null|FHIRString
    {
        return $this->name ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The human-friendly name of the interaction.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $name
     * @return static
     */
    public function setName(null|string|FHIRStringPrimitive|FHIRString $name): self
    {
        if (null === $name) {
            unset($this->name);
            return $this;
        }
        if (!($name instanceof FHIRString)) {
            $name = new FHIRString(value: $name);
        }
        $this->name = $name;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Who starts the transaction.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getInitiator(): null|FHIRString
    {
        return $this->initiator ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Who starts the transaction.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $initiator
     * @return static
     */
    public function setInitiator(null|string|FHIRStringPrimitive|FHIRString $initiator): self
    {
        if (null === $initiator) {
            unset($this->initiator);
            return $this;
        }
        if (!($initiator instanceof FHIRString)) {
            $initiator = new FHIRString(value: $initiator);
        }
        $this->initiator = $initiator;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Who receives the transaction.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getReceiver(): null|FHIRString
    {
        return $this->receiver ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Who receives the transaction.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $receiver
     * @return static
     */
    public function setReceiver(null|string|FHIRStringPrimitive|FHIRString $receiver): self
    {
        if (null === $receiver) {
            unset($this->receiver);
            return $this;
        }
        if (!($receiver instanceof FHIRString)) {
            $receiver = new FHIRString(value: $receiver);
        }
        $this->receiver = $receiver;
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
     * A comment to be inserted in the diagram.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown
     */
    public function getDescription(): null|FHIRMarkdown
    {
        return $this->description ?? null;
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
     * A comment to be inserted in the diagram.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRMarkdownPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown $description
     * @return static
     */
    public function setDescription(null|string|FHIRMarkdownPrimitive|FHIRMarkdown $description): self
    {
        if (null === $description) {
            unset($this->description);
            return $this;
        }
        if (!($description instanceof FHIRMarkdown)) {
            $description = new FHIRMarkdown(value: $description);
        }
        $this->description = $description;
        return $this;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Whether the initiator is deactivated right after the transaction.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    public function getInitiatorActive(): null|FHIRBoolean
    {
        return $this->initiatorActive ?? null;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Whether the initiator is deactivated right after the transaction.
     *
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $initiatorActive
     * @return static
     */
    public function setInitiatorActive(null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $initiatorActive): self
    {
        if (null === $initiatorActive) {
            unset($this->initiatorActive);
            return $this;
        }
        if (!($initiatorActive instanceof FHIRBoolean)) {
            $initiatorActive = new FHIRBoolean(value: $initiatorActive);
        }
        $this->initiatorActive = $initiatorActive;
        return $this;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Whether the receiver is deactivated right after the transaction.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    public function getReceiverActive(): null|FHIRBoolean
    {
        return $this->receiverActive ?? null;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Whether the receiver is deactivated right after the transaction.
     *
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $receiverActive
     * @return static
     */
    public function setReceiverActive(null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $receiverActive): self
    {
        if (null === $receiverActive) {
            unset($this->receiverActive);
            return $this;
        }
        if (!($receiverActive instanceof FHIRBoolean)) {
            $receiverActive = new FHIRBoolean(value: $receiverActive);
        }
        $this->receiverActive = $receiverActive;
        return $this;
    }

    /**
     * Example of workflow instance.
     *
     * Each resource instance used by the initiator.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExampleScenario\FHIRExampleScenarioContainedInstance
     */
    public function getRequest(): null|FHIRExampleScenarioContainedInstance
    {
        return $this->request ?? null;
    }

    /**
     * Example of workflow instance.
     *
     * Each resource instance used by the initiator.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExampleScenario\FHIRExampleScenarioContainedInstance $request
     * @return static
     */
    public function setRequest(null|FHIRExampleScenarioContainedInstance $request): self
    {
        if (null === $request) {
            unset($this->request);
            return $this;
        }
        $this->request = $request;
        return $this;
    }

    /**
     * Example of workflow instance.
     *
     * Each resource instance used by the responder.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExampleScenario\FHIRExampleScenarioContainedInstance
     */
    public function getResponse(): null|FHIRExampleScenarioContainedInstance
    {
        return $this->response ?? null;
    }

    /**
     * Example of workflow instance.
     *
     * Each resource instance used by the responder.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExampleScenario\FHIRExampleScenarioContainedInstance $response
     * @return static
     */
    public function setResponse(null|FHIRExampleScenarioContainedInstance $response): self
    {
        if (null === $response) {
            unset($this->response);
            return $this;
        }
        $this->response = $response;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExampleScenario\FHIRExampleScenarioOperation $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExampleScenario\FHIRExampleScenarioOperation
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRExampleScenarioOperation)) {
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
            } else if (self::FIELD_NUMBER === $cen) {
                $type->setNumber(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TYPE === $cen) {
                $type->setType(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_NAME === $cen) {
                $type->setName(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_INITIATOR === $cen) {
                $type->setInitiator(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_RECEIVER === $cen) {
                $type->setReceiver(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DESCRIPTION === $cen) {
                $type->setDescription(FHIRMarkdown::xmlUnserialize($ce, $config));
            } else if (self::FIELD_INITIATOR_ACTIVE === $cen) {
                $type->setInitiatorActive(FHIRBoolean::xmlUnserialize($ce, $config));
            } else if (self::FIELD_RECEIVER_ACTIVE === $cen) {
                $type->setReceiverActive(FHIRBoolean::xmlUnserialize($ce, $config));
            } else if (self::FIELD_REQUEST === $cen) {
                $type->setRequest(FHIRExampleScenarioContainedInstance::xmlUnserialize($ce, $config));
            } else if (self::FIELD_RESPONSE === $cen) {
                $type->setResponse(FHIRExampleScenarioContainedInstance::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_NUMBER])) {
            if (isset($type->number)) {
                $type->number->setValue((string)$attributes[self::FIELD_NUMBER]);
            } else {
                $type->setNumber((string)$attributes[self::FIELD_NUMBER]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_NUMBER, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_TYPE])) {
            if (isset($type->type)) {
                $type->type->setValue((string)$attributes[self::FIELD_TYPE]);
            } else {
                $type->setType((string)$attributes[self::FIELD_TYPE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_TYPE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_NAME])) {
            if (isset($type->name)) {
                $type->name->setValue((string)$attributes[self::FIELD_NAME]);
            } else {
                $type->setName((string)$attributes[self::FIELD_NAME]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_NAME, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_INITIATOR])) {
            if (isset($type->initiator)) {
                $type->initiator->setValue((string)$attributes[self::FIELD_INITIATOR]);
            } else {
                $type->setInitiator((string)$attributes[self::FIELD_INITIATOR]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_INITIATOR, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_RECEIVER])) {
            if (isset($type->receiver)) {
                $type->receiver->setValue((string)$attributes[self::FIELD_RECEIVER]);
            } else {
                $type->setReceiver((string)$attributes[self::FIELD_RECEIVER]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_RECEIVER, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_DESCRIPTION])) {
            if (isset($type->description)) {
                $type->description->setValue((string)$attributes[self::FIELD_DESCRIPTION]);
            } else {
                $type->setDescription((string)$attributes[self::FIELD_DESCRIPTION]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_DESCRIPTION, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_INITIATOR_ACTIVE])) {
            if (isset($type->initiatorActive)) {
                $type->initiatorActive->setValue((string)$attributes[self::FIELD_INITIATOR_ACTIVE]);
            } else {
                $type->setInitiatorActive((string)$attributes[self::FIELD_INITIATOR_ACTIVE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_INITIATOR_ACTIVE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_RECEIVER_ACTIVE])) {
            if (isset($type->receiverActive)) {
                $type->receiverActive->setValue((string)$attributes[self::FIELD_RECEIVER_ACTIVE]);
            } else {
                $type->setReceiverActive((string)$attributes[self::FIELD_RECEIVER_ACTIVE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_RECEIVER_ACTIVE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->number) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_NUMBER]) {
            $xw->writeAttribute(self::FIELD_NUMBER, $this->number->_getValueAsString());
        }
        if (isset($this->type) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_TYPE]) {
            $xw->writeAttribute(self::FIELD_TYPE, $this->type->_getValueAsString());
        }
        if (isset($this->name) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_NAME]) {
            $xw->writeAttribute(self::FIELD_NAME, $this->name->_getValueAsString());
        }
        if (isset($this->initiator) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_INITIATOR]) {
            $xw->writeAttribute(self::FIELD_INITIATOR, $this->initiator->_getValueAsString());
        }
        if (isset($this->receiver) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_RECEIVER]) {
            $xw->writeAttribute(self::FIELD_RECEIVER, $this->receiver->_getValueAsString());
        }
        if (isset($this->description) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_DESCRIPTION]) {
            $xw->writeAttribute(self::FIELD_DESCRIPTION, $this->description->_getValueAsString());
        }
        if (isset($this->initiatorActive) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_INITIATOR_ACTIVE]) {
            $xw->writeAttribute(self::FIELD_INITIATOR_ACTIVE, $this->initiatorActive->_getValueAsString());
        }
        if (isset($this->receiverActive) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_RECEIVER_ACTIVE]) {
            $xw->writeAttribute(self::FIELD_RECEIVER_ACTIVE, $this->receiverActive->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->number)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_NUMBER]
                || $this->number->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_NUMBER);
            $this->number->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_NUMBER]);
            $xw->endElement();
        }
        if (isset($this->type)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_TYPE]
                || $this->type->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_TYPE);
            $this->type->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_TYPE]);
            $xw->endElement();
        }
        if (isset($this->name)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_NAME]
                || $this->name->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_NAME);
            $this->name->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_NAME]);
            $xw->endElement();
        }
        if (isset($this->initiator)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_INITIATOR]
                || $this->initiator->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_INITIATOR);
            $this->initiator->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_INITIATOR]);
            $xw->endElement();
        }
        if (isset($this->receiver)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_RECEIVER]
                || $this->receiver->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_RECEIVER);
            $this->receiver->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_RECEIVER]);
            $xw->endElement();
        }
        if (isset($this->description)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_DESCRIPTION]
                || $this->description->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_DESCRIPTION);
            $this->description->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_DESCRIPTION]);
            $xw->endElement();
        }
        if (isset($this->initiatorActive)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_INITIATOR_ACTIVE]
                || $this->initiatorActive->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_INITIATOR_ACTIVE);
            $this->initiatorActive->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_INITIATOR_ACTIVE]);
            $xw->endElement();
        }
        if (isset($this->receiverActive)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_RECEIVER_ACTIVE]
                || $this->receiverActive->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_RECEIVER_ACTIVE);
            $this->receiverActive->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_RECEIVER_ACTIVE]);
            $xw->endElement();
        }
        if (isset($this->request)) {
            $xw->startElement(self::FIELD_REQUEST);
            $this->request->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->response)) {
            $xw->startElement(self::FIELD_RESPONSE);
            $this->response->xmlSerialize($xw, $config);
            $xw->endElement();
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExampleScenario\FHIRExampleScenarioOperation $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExampleScenario\FHIRExampleScenarioOperation
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
        } else if (!($type instanceof FHIRExampleScenarioOperation)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->number)
            || isset($decoded->_number)
            || property_exists($decoded, self::FIELD_NUMBER)
            || property_exists($decoded, self::FIELD_NUMBER_EXT)) {
            $v = $decoded->_number ?? new \stdClass();
            $v->value = $decoded->number ?? null;
            $type->setNumber(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->type)
            || isset($decoded->_type)
            || property_exists($decoded, self::FIELD_TYPE)
            || property_exists($decoded, self::FIELD_TYPE_EXT)) {
            $v = $decoded->_type ?? new \stdClass();
            $v->value = $decoded->type ?? null;
            $type->setType(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->name)
            || isset($decoded->_name)
            || property_exists($decoded, self::FIELD_NAME)
            || property_exists($decoded, self::FIELD_NAME_EXT)) {
            $v = $decoded->_name ?? new \stdClass();
            $v->value = $decoded->name ?? null;
            $type->setName(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->initiator)
            || isset($decoded->_initiator)
            || property_exists($decoded, self::FIELD_INITIATOR)
            || property_exists($decoded, self::FIELD_INITIATOR_EXT)) {
            $v = $decoded->_initiator ?? new \stdClass();
            $v->value = $decoded->initiator ?? null;
            $type->setInitiator(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->receiver)
            || isset($decoded->_receiver)
            || property_exists($decoded, self::FIELD_RECEIVER)
            || property_exists($decoded, self::FIELD_RECEIVER_EXT)) {
            $v = $decoded->_receiver ?? new \stdClass();
            $v->value = $decoded->receiver ?? null;
            $type->setReceiver(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->description)
            || isset($decoded->_description)
            || property_exists($decoded, self::FIELD_DESCRIPTION)
            || property_exists($decoded, self::FIELD_DESCRIPTION_EXT)) {
            $v = $decoded->_description ?? new \stdClass();
            $v->value = $decoded->description ?? null;
            $type->setDescription(FHIRMarkdown::jsonUnserialize($v, $config));
        }
        if (isset($decoded->initiatorActive)
            || isset($decoded->_initiatorActive)
            || property_exists($decoded, self::FIELD_INITIATOR_ACTIVE)
            || property_exists($decoded, self::FIELD_INITIATOR_ACTIVE_EXT)) {
            $v = $decoded->_initiatorActive ?? new \stdClass();
            $v->value = $decoded->initiatorActive ?? null;
            $type->setInitiatorActive(FHIRBoolean::jsonUnserialize($v, $config));
        }
        if (isset($decoded->receiverActive)
            || isset($decoded->_receiverActive)
            || property_exists($decoded, self::FIELD_RECEIVER_ACTIVE)
            || property_exists($decoded, self::FIELD_RECEIVER_ACTIVE_EXT)) {
            $v = $decoded->_receiverActive ?? new \stdClass();
            $v->value = $decoded->receiverActive ?? null;
            $type->setReceiverActive(FHIRBoolean::jsonUnserialize($v, $config));
        }
        if (isset($decoded->request) || property_exists($decoded, self::FIELD_REQUEST)) {
            if (is_array($decoded->request)) {
                $type->setRequest(FHIRExampleScenarioContainedInstance::jsonUnserialize(reset($decoded->request), $config));
            } else {
                $type->setRequest(FHIRExampleScenarioContainedInstance::jsonUnserialize($decoded->request, $config));
            }
        }
        if (isset($decoded->response) || property_exists($decoded, self::FIELD_RESPONSE)) {
            if (is_array($decoded->response)) {
                $type->setResponse(FHIRExampleScenarioContainedInstance::jsonUnserialize(reset($decoded->response), $config));
            } else {
                $type->setResponse(FHIRExampleScenarioContainedInstance::jsonUnserialize($decoded->response, $config));
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
        if (isset($this->number)) {
            if (null !== ($val = $this->number->getValue())) {
                $out->number = $val;
            }
            if ($this->number->_nonValueFieldDefined()) {
                $ext = $this->number->jsonSerialize();
                unset($ext->value);
                $out->_number = $ext;
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
        if (isset($this->initiator)) {
            if (null !== ($val = $this->initiator->getValue())) {
                $out->initiator = $val;
            }
            if ($this->initiator->_nonValueFieldDefined()) {
                $ext = $this->initiator->jsonSerialize();
                unset($ext->value);
                $out->_initiator = $ext;
            }
        }
        if (isset($this->receiver)) {
            if (null !== ($val = $this->receiver->getValue())) {
                $out->receiver = $val;
            }
            if ($this->receiver->_nonValueFieldDefined()) {
                $ext = $this->receiver->jsonSerialize();
                unset($ext->value);
                $out->_receiver = $ext;
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
        if (isset($this->initiatorActive)) {
            if (null !== ($val = $this->initiatorActive->getValue())) {
                $out->initiatorActive = $val;
            }
            if ($this->initiatorActive->_nonValueFieldDefined()) {
                $ext = $this->initiatorActive->jsonSerialize();
                unset($ext->value);
                $out->_initiatorActive = $ext;
            }
        }
        if (isset($this->receiverActive)) {
            if (null !== ($val = $this->receiverActive->getValue())) {
                $out->receiverActive = $val;
            }
            if ($this->receiverActive->_nonValueFieldDefined()) {
                $ext = $this->receiverActive->jsonSerialize();
                unset($ext->value);
                $out->_receiverActive = $ext;
            }
        }
        if (isset($this->request)) {
            $out->request = $this->request;
        }
        if (isset($this->response)) {
            $out->response = $this->response;
        }
        return $out;
    }
}
