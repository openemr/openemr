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
use OpenEMR\FHIR\Validation\TypeValidationsTrait;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUnsignedInt;
use OpenEMR\FHIR\Versions\R4\Types\FHIRMarkdownPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRUnsignedIntPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * A Capability Statement documents a set of capabilities (behaviors) of a FHIR
 * Server for a particular version of FHIR that may be used as a statement of
 * actual server functionality or a statement of required or desired server
 * implementation.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRCapabilityStatementMessaging extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_CAPABILITY_STATEMENT_DOT_MESSAGING;

    /* class_default.php:56 */
    public const FIELD_ENDPOINT = 'endpoint';
    public const FIELD_RELIABLE_CACHE = 'reliableCache';
    public const FIELD_RELIABLE_CACHE_EXT = '_reliableCache';
    public const FIELD_DOCUMENTATION = 'documentation';
    public const FIELD_DOCUMENTATION_EXT = '_documentation';
    public const FIELD_SUPPORTED_MESSAGE = 'supportedMessage';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_RELIABLE_CACHE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_DOCUMENTATION => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * A Capability Statement documents a set of capabilities (behaviors) of a FHIR
     * Server for a particular version of FHIR that may be used as a statement of
     * actual server functionality or a statement of required or desired server
     * implementation.
     *
     * An endpoint (network accessible address) to which messages and/or replies are to
     * be sent.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementEndpoint>
     */
    #[FHIRCapabilityStatementEndpoint]
    protected array $endpoint;
    /**
     * An integer with a value that is not negative (e.g. >= 0)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Length if the receiver's reliable messaging cache in minutes (if a receiver) or
     * how long the cache length on the receiver should be (if a sender).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUnsignedInt
     */
    #[FHIRUnsignedInt]
    protected FHIRUnsignedInt $reliableCache;
    /**
     * A string that may contain Github Flavored Markdown syntax for optional
     * processing by a mark down presentation engine
     * Systems are not required to have markdown support, so the text should be
     * readable without markdown processing. The markdown syntax is GFM - see
     * https://github.github.com/gfm/
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Documentation about the system's messaging capabilities for this endpoint not
     * otherwise documented by the capability statement. For example, the process for
     * becoming an authorized messaging exchange partner.
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
     * References to message definitions for messages this system can send or receive.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementSupportedMessage>
     */
    #[FHIRCapabilityStatementSupportedMessage]
    protected array $supportedMessage;

    /* constructor.php:61 */
    /**
     * FHIRCapabilityStatementMessaging Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementEndpoint> $endpoint
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRUnsignedIntPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUnsignedInt $reliableCache
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRMarkdownPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown $documentation
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementSupportedMessage> $supportedMessage
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|iterable $endpoint = null,
                                null|string|int|float|FHIRUnsignedIntPrimitive|FHIRUnsignedInt $reliableCache = null,
                                null|string|FHIRMarkdownPrimitive|FHIRMarkdown $documentation = null,
                                null|iterable $supportedMessage = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $endpoint) {
            $this->setEndpoint(...$endpoint);
        }
        if (null !== $reliableCache) {
            $this->setReliableCache($reliableCache);
        }
        if (null !== $documentation) {
            $this->setDocumentation($documentation);
        }
        if (null !== $supportedMessage) {
            $this->setSupportedMessage(...$supportedMessage);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * A Capability Statement documents a set of capabilities (behaviors) of a FHIR
     * Server for a particular version of FHIR that may be used as a statement of
     * actual server functionality or a statement of required or desired server
     * implementation.
     *
     * An endpoint (network accessible address) to which messages and/or replies are to
     * be sent.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementEndpoint>
     */
    public function getEndpoint(): array
    {
        return $this->endpoint ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementEndpoint>
     */
    public function getEndpointIterator(): iterable
    {
        if (!isset($this->endpoint)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->endpoint);
    }

    /**
     * A Capability Statement documents a set of capabilities (behaviors) of a FHIR
     * Server for a particular version of FHIR that may be used as a statement of
     * actual server functionality or a statement of required or desired server
     * implementation.
     *
     * An endpoint (network accessible address) to which messages and/or replies are to
     * be sent.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementEndpoint $endpoint
     * @return static
     */
    public function addEndpoint(FHIRCapabilityStatementEndpoint $endpoint): self
    {
        if (!isset($this->endpoint)) {
            $this->endpoint = [];
        }
        $this->endpoint[] = $endpoint;
        return $this;
    }

    /**
     * A Capability Statement documents a set of capabilities (behaviors) of a FHIR
     * Server for a particular version of FHIR that may be used as a statement of
     * actual server functionality or a statement of required or desired server
     * implementation.
     *
     * An endpoint (network accessible address) to which messages and/or replies are to
     * be sent.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementEndpoint ...$endpoint
     * @return static
     */
    public function setEndpoint(FHIRCapabilityStatementEndpoint ...$endpoint): self
    {
        if ([] === $endpoint) {
            unset($this->endpoint);
            return $this;
        }
        $this->endpoint = $endpoint;
        return $this;
    }

    /**
     * An integer with a value that is not negative (e.g. >= 0)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Length if the receiver's reliable messaging cache in minutes (if a receiver) or
     * how long the cache length on the receiver should be (if a sender).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUnsignedInt
     */
    public function getReliableCache(): null|FHIRUnsignedInt
    {
        return $this->reliableCache ?? null;
    }

    /**
     * An integer with a value that is not negative (e.g. >= 0)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Length if the receiver's reliable messaging cache in minutes (if a receiver) or
     * how long the cache length on the receiver should be (if a sender).
     *
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRUnsignedIntPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUnsignedInt $reliableCache
     * @return static
     */
    public function setReliableCache(null|string|int|float|FHIRUnsignedIntPrimitive|FHIRUnsignedInt $reliableCache): self
    {
        if (null === $reliableCache) {
            unset($this->reliableCache);
            return $this;
        }
        if (!($reliableCache instanceof FHIRUnsignedInt)) {
            $reliableCache = new FHIRUnsignedInt(value: $reliableCache);
        }
        $this->reliableCache = $reliableCache;
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
     * Documentation about the system's messaging capabilities for this endpoint not
     * otherwise documented by the capability statement. For example, the process for
     * becoming an authorized messaging exchange partner.
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
     * Documentation about the system's messaging capabilities for this endpoint not
     * otherwise documented by the capability statement. For example, the process for
     * becoming an authorized messaging exchange partner.
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
     * References to message definitions for messages this system can send or receive.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementSupportedMessage>
     */
    public function getSupportedMessage(): array
    {
        return $this->supportedMessage ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementSupportedMessage>
     */
    public function getSupportedMessageIterator(): iterable
    {
        if (!isset($this->supportedMessage)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->supportedMessage);
    }

    /**
     * A Capability Statement documents a set of capabilities (behaviors) of a FHIR
     * Server for a particular version of FHIR that may be used as a statement of
     * actual server functionality or a statement of required or desired server
     * implementation.
     *
     * References to message definitions for messages this system can send or receive.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementSupportedMessage $supportedMessage
     * @return static
     */
    public function addSupportedMessage(FHIRCapabilityStatementSupportedMessage $supportedMessage): self
    {
        if (!isset($this->supportedMessage)) {
            $this->supportedMessage = [];
        }
        $this->supportedMessage[] = $supportedMessage;
        return $this;
    }

    /**
     * A Capability Statement documents a set of capabilities (behaviors) of a FHIR
     * Server for a particular version of FHIR that may be used as a statement of
     * actual server functionality or a statement of required or desired server
     * implementation.
     *
     * References to message definitions for messages this system can send or receive.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementSupportedMessage ...$supportedMessage
     * @return static
     */
    public function setSupportedMessage(FHIRCapabilityStatementSupportedMessage ...$supportedMessage): self
    {
        if ([] === $supportedMessage) {
            unset($this->supportedMessage);
            return $this;
        }
        $this->supportedMessage = $supportedMessage;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementMessaging $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementMessaging
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRCapabilityStatementMessaging)) {
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
            } else if (self::FIELD_ENDPOINT === $cen) {
                $type->addEndpoint(FHIRCapabilityStatementEndpoint::xmlUnserialize($ce, $config));
            } else if (self::FIELD_RELIABLE_CACHE === $cen) {
                $type->setReliableCache(FHIRUnsignedInt::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DOCUMENTATION === $cen) {
                $type->setDocumentation(FHIRMarkdown::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SUPPORTED_MESSAGE === $cen) {
                $type->addSupportedMessage(FHIRCapabilityStatementSupportedMessage::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_RELIABLE_CACHE])) {
            if (isset($type->reliableCache)) {
                $type->reliableCache->setValue((string)$attributes[self::FIELD_RELIABLE_CACHE]);
            } else {
                $type->setReliableCache((string)$attributes[self::FIELD_RELIABLE_CACHE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_RELIABLE_CACHE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->reliableCache) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_RELIABLE_CACHE]) {
            $xw->writeAttribute(self::FIELD_RELIABLE_CACHE, $this->reliableCache->_getValueAsString());
        }
        if (isset($this->documentation) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_DOCUMENTATION]) {
            $xw->writeAttribute(self::FIELD_DOCUMENTATION, $this->documentation->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->endpoint)) {
            foreach ($this->endpoint as $v) {
                $xw->startElement(self::FIELD_ENDPOINT);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->reliableCache)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_RELIABLE_CACHE]
                || $this->reliableCache->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_RELIABLE_CACHE);
            $this->reliableCache->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_RELIABLE_CACHE]);
            $xw->endElement();
        }
        if (isset($this->documentation)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_DOCUMENTATION]
                || $this->documentation->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_DOCUMENTATION);
            $this->documentation->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_DOCUMENTATION]);
            $xw->endElement();
        }
        if (isset($this->supportedMessage)) {
            foreach ($this->supportedMessage as $v) {
                $xw->startElement(self::FIELD_SUPPORTED_MESSAGE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementMessaging $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementMessaging
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
        } else if (!($type instanceof FHIRCapabilityStatementMessaging)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->endpoint) || property_exists($decoded, self::FIELD_ENDPOINT)) {
            if (is_object($decoded->endpoint)) {
                $vals = [$decoded->endpoint];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_ENDPOINT, true);
            } else {
                $vals = $decoded->endpoint;
            }
            foreach($vals as $v) {
                $type->addEndpoint(FHIRCapabilityStatementEndpoint::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->reliableCache)
            || isset($decoded->_reliableCache)
            || property_exists($decoded, self::FIELD_RELIABLE_CACHE)
            || property_exists($decoded, self::FIELD_RELIABLE_CACHE_EXT)) {
            $v = $decoded->_reliableCache ?? new \stdClass();
            $v->value = $decoded->reliableCache ?? null;
            $type->setReliableCache(FHIRUnsignedInt::jsonUnserialize($v, $config));
        }
        if (isset($decoded->documentation)
            || isset($decoded->_documentation)
            || property_exists($decoded, self::FIELD_DOCUMENTATION)
            || property_exists($decoded, self::FIELD_DOCUMENTATION_EXT)) {
            $v = $decoded->_documentation ?? new \stdClass();
            $v->value = $decoded->documentation ?? null;
            $type->setDocumentation(FHIRMarkdown::jsonUnserialize($v, $config));
        }
        if (isset($decoded->supportedMessage) || property_exists($decoded, self::FIELD_SUPPORTED_MESSAGE)) {
            if (is_object($decoded->supportedMessage)) {
                $vals = [$decoded->supportedMessage];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_SUPPORTED_MESSAGE, true);
            } else {
                $vals = $decoded->supportedMessage;
            }
            foreach($vals as $v) {
                $type->addSupportedMessage(FHIRCapabilityStatementSupportedMessage::jsonUnserialize($v, $config));
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
        if (isset($this->endpoint) && [] !== $this->endpoint) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_ENDPOINT) && 1 === count($this->endpoint)) {
                $out->endpoint = $this->endpoint[0];
            } else {
                $out->endpoint = $this->endpoint;
            }
        }
        if (isset($this->reliableCache)) {
            if (null !== ($val = $this->reliableCache->getValue())) {
                $out->reliableCache = $val;
            }
            if ($this->reliableCache->_nonValueFieldDefined()) {
                $ext = $this->reliableCache->jsonSerialize();
                unset($ext->value);
                $out->_reliableCache = $ext;
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
        if (isset($this->supportedMessage) && [] !== $this->supportedMessage) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_SUPPORTED_MESSAGE) && 1 === count($this->supportedMessage)) {
                $out->supportedMessage = $this->supportedMessage[0];
            } else {
                $out->supportedMessage = $this->supportedMessage;
            }
        }
        return $out;
    }
}
