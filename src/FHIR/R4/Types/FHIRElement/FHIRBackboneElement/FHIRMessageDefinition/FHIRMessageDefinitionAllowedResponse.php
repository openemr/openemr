<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMessageDefinition;

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
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown;
use OpenEMR\FHIR\Versions\R4\Types\FHIRMarkdownPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * Defines the characteristics of a message that can be shared between systems,
 * including the type of event that initiates the message, the content to be
 * transmitted and what response(s), if any, are permitted.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRMessageDefinitionAllowedResponse extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_MESSAGE_DEFINITION_DOT_ALLOWED_RESPONSE;

    /* class_default.php:56 */
    public const FIELD_MESSAGE = 'message';
    public const FIELD_MESSAGE_EXT = '_message';
    public const FIELD_SITUATION = 'situation';
    public const FIELD_SITUATION_EXT = '_situation';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_MESSAGE => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_MESSAGE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_SITUATION => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * A reference to the message definition that must be adhered to by this supported
     * response.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical
     */
    #[FHIRCanonical]
    protected FHIRCanonical $message;
    /**
     * A string that may contain Github Flavored Markdown syntax for optional
     * processing by a mark down presentation engine
     * Systems are not required to have markdown support, so the text should be
     * readable without markdown processing. The markdown syntax is GFM - see
     * https://github.github.com/gfm/
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Provides a description of the circumstances in which this response should be
     * used (as opposed to one of the alternative responses).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown
     */
    #[FHIRMarkdown]
    protected FHIRMarkdown $situation;

    /* constructor.php:61 */
    /**
     * FHIRMessageDefinitionAllowedResponse Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical $message
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRMarkdownPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown $situation
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|string|FHIRCanonicalPrimitive|FHIRCanonical $message = null,
                                null|string|FHIRMarkdownPrimitive|FHIRMarkdown $situation = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $message) {
            $this->setMessage($message);
        }
        if (null !== $situation) {
            $this->setSituation($situation);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * A reference to the message definition that must be adhered to by this supported
     * response.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical
     */
    public function getMessage(): null|FHIRCanonical
    {
        return $this->message ?? null;
    }

    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * A reference to the message definition that must be adhered to by this supported
     * response.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical $message
     * @return static
     */
    public function setMessage(null|string|FHIRCanonicalPrimitive|FHIRCanonical $message): self
    {
        if (null === $message) {
            unset($this->message);
            return $this;
        }
        if (!($message instanceof FHIRCanonical)) {
            $message = new FHIRCanonical(value: $message);
        }
        $this->message = $message;
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
     * Provides a description of the circumstances in which this response should be
     * used (as opposed to one of the alternative responses).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown
     */
    public function getSituation(): null|FHIRMarkdown
    {
        return $this->situation ?? null;
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
     * Provides a description of the circumstances in which this response should be
     * used (as opposed to one of the alternative responses).
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRMarkdownPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown $situation
     * @return static
     */
    public function setSituation(null|string|FHIRMarkdownPrimitive|FHIRMarkdown $situation): self
    {
        if (null === $situation) {
            unset($this->situation);
            return $this;
        }
        if (!($situation instanceof FHIRMarkdown)) {
            $situation = new FHIRMarkdown(value: $situation);
        }
        $this->situation = $situation;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMessageDefinition\FHIRMessageDefinitionAllowedResponse $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMessageDefinition\FHIRMessageDefinitionAllowedResponse
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRMessageDefinitionAllowedResponse)) {
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
            } else if (self::FIELD_MESSAGE === $cen) {
                $type->setMessage(FHIRCanonical::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SITUATION === $cen) {
                $type->setSituation(FHIRMarkdown::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_MESSAGE])) {
            if (isset($type->message)) {
                $type->message->setValue((string)$attributes[self::FIELD_MESSAGE]);
            } else {
                $type->setMessage((string)$attributes[self::FIELD_MESSAGE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_MESSAGE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_SITUATION])) {
            if (isset($type->situation)) {
                $type->situation->setValue((string)$attributes[self::FIELD_SITUATION]);
            } else {
                $type->setSituation((string)$attributes[self::FIELD_SITUATION]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_SITUATION, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->message) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_MESSAGE]) {
            $xw->writeAttribute(self::FIELD_MESSAGE, $this->message->_getValueAsString());
        }
        if (isset($this->situation) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_SITUATION]) {
            $xw->writeAttribute(self::FIELD_SITUATION, $this->situation->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->message)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_MESSAGE]
                || $this->message->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_MESSAGE);
            $this->message->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_MESSAGE]);
            $xw->endElement();
        }
        if (isset($this->situation)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_SITUATION]
                || $this->situation->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_SITUATION);
            $this->situation->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_SITUATION]);
            $xw->endElement();
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMessageDefinition\FHIRMessageDefinitionAllowedResponse $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMessageDefinition\FHIRMessageDefinitionAllowedResponse
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
        } else if (!($type instanceof FHIRMessageDefinitionAllowedResponse)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->message)
            || isset($decoded->_message)
            || property_exists($decoded, self::FIELD_MESSAGE)
            || property_exists($decoded, self::FIELD_MESSAGE_EXT)) {
            $v = $decoded->_message ?? new \stdClass();
            $v->value = $decoded->message ?? null;
            $type->setMessage(FHIRCanonical::jsonUnserialize($v, $config));
        }
        if (isset($decoded->situation)
            || isset($decoded->_situation)
            || property_exists($decoded, self::FIELD_SITUATION)
            || property_exists($decoded, self::FIELD_SITUATION_EXT)) {
            $v = $decoded->_situation ?? new \stdClass();
            $v->value = $decoded->situation ?? null;
            $type->setSituation(FHIRMarkdown::jsonUnserialize($v, $config));
        }
        return $type;
    }

    /**
     * @return \stdClass
     */
    public function jsonSerialize(): mixed
    {
        $out = parent::jsonSerialize();
        if (isset($this->message)) {
            if (null !== ($val = $this->message->getValue())) {
                $out->message = $val;
            }
            if ($this->message->_nonValueFieldDefined()) {
                $ext = $this->message->jsonSerialize();
                unset($ext->value);
                $out->_message = $ext;
            }
        }
        if (isset($this->situation)) {
            if (null !== ($val = $this->situation->getValue())) {
                $out->situation = $val;
            }
            if ($this->situation->_nonValueFieldDefined()) {
                $ext = $this->situation->jsonSerialize();
                unset($ext->value);
                $out->_situation = $ext;
            }
        }
        return $out;
    }
}
